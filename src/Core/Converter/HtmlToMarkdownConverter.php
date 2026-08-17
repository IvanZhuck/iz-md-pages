<?php

declare(strict_types=1);

namespace IZMDPages\Core\Converter;

/**
 * Converts HTML content into Markdown syntax.
 */
class HtmlToMarkdownConverter
{
    /**
     * Convert an HTML string to Markdown.
     *
     * @param string $html Source HTML content.
     * @return string Markdown output.
     */
    public function convert(string $html): string
    {
        if (empty(trim($html))) {
            return '';
        }

        $internalErrors = libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        // Use XML encoding prefix to preserve UTF-8 characters (including Cyrillic)
        $encodedHtml = '<?xml encoding="UTF-8"><div id="md-root-wrapper">' . $html . '</div>';
        $dom->loadHTML($encodedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        $wrapper = $dom->getElementById('md-root-wrapper');
        if (!$wrapper) {
            return trim(strip_tags($html));
        }

        $markdown = $this->convertNode($wrapper);

        $result = trim($markdown);

        // Normalize multiple consecutive blank lines
        $result = (string) preg_replace("/\n{3,}/", "\n\n", $result);

        // Collapse multiple consecutive spaces within each line
        // while preserving Markdown line-break (two trailing spaces before \n)
        $result = (string) preg_replace('/(?<=\S) {2,}(?=\S)/', ' ', $result);

        return $this->stripLeadingWhitespace($result);
    }

    /**
     * Recursively convert DOM nodes into Markdown.
     *
     * Each HTML element is passed through the `iz_md_pages_convert_tag` filter
     * before the built-in conversion logic runs. Returning a non-null string
     * from the filter replaces the default conversion for that tag entirely.
     *
     * Filter signature:
     *   apply_filters('iz_md_pages_convert_tag', null, string $tagName, \DOMElement $element, string $innerText)
     *
     * @param \DOMNode $node DOM node to process.
     * @return string Converted Markdown snippet.
     */
    protected function convertNode(\DOMNode $node): string
    {
        $output = '';

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $output .= (string) $child->nodeValue;
                continue;
            }

            if ($child->nodeType !== XML_ELEMENT_NODE) {
                continue;
            }

            $tagName = strtolower($child->nodeName);
            $innerText = $this->convertNode($child);

            /**
             * Allows overriding the Markdown conversion of any HTML tag.
             *
             * Return a non-null string to replace the default conversion
             * for this tag. Return null to fall through to the built-in logic.
             *
             * @param string|null  $override  Markdown override (null = use default).
             * @param string       $tagName   Lowercase HTML tag name (e.g. 'p', 'h1', 'div').
             * @param \DOMElement  $element   The DOM element being converted.
             * @param string       $innerText Already-converted inner content of the element.
             */
            $override = apply_filters(
                'iz_md_pages_convert_tag',
                null,
                $tagName,
                $child,
                $innerText
            );

            if (is_string($override)) {
                $output .= $override;
                continue;
            }

            switch ($tagName) {
                case 'h1':
                    $output .= "\n\n# " . trim($innerText) . "\n\n";
                    break;
                case 'h2':
                    $output .= "\n\n## " . trim($innerText) . "\n\n";
                    break;
                case 'h3':
                    $output .= "\n\n### " . trim($innerText) . "\n\n";
                    break;
                case 'h4':
                    $output .= "\n\n#### " . trim($innerText) . "\n\n";
                    break;
                case 'h5':
                    $output .= "\n\n##### " . trim($innerText) . "\n\n";
                    break;
                case 'h6':
                    $output .= "\n\n###### " . trim($innerText) . "\n\n";
                    break;

                case 'p':
                    $output .= "\n\n" . trim($innerText) . "\n\n";
                    break;

                case 'strong':
                case 'b':
                    $output .= '**' . trim($innerText) . '**';
                    break;

                case 'em':
                case 'i':
                    $output .= '*' . trim($innerText) . '*';
                    break;

                case 'del':
                case 's':
                case 'strike':
                    $output .= '~~' . trim($innerText) . '~~';
                    break;

                case 'a':
                    if ($child instanceof \DOMElement) {
                        $href = $this->resolveUrl($child->getAttribute('href'));
                        $linkText = trim($innerText);

                        if (!empty($href) && ($linkText === '' || $linkText === $href)) {
                            $output .= '<' . esc_url($href) . '>';
                        } elseif (!empty($href)) {
                            $output .= '[' . $linkText . '](' . esc_url($href) . ')';
                        } else {
                            $output .= $innerText;
                        }
                    } else {
                        $output .= $innerText;
                    }
                    break;

                case 'img':
                    if ($child instanceof \DOMElement) {
                        $src = $this->resolveUrl($child->getAttribute('src'));
                        $alt = $child->getAttribute('alt');
                        if (!empty($src)) {
                            $output .= '![' . $alt . '](' . esc_url($src) . ')';
                        }
                    }
                    break;

                case 'blockquote':
                    $lines = explode("\n", trim($innerText));
                    $quoted = array_map(
                        function (string $line): string {
                            return '> ' . $line;
                        },
                        $lines
                    );
                    $output .= "\n\n" . implode("\n", $quoted) . "\n\n";
                    break;

                case 'ul':
                    if ($child instanceof \DOMElement) {
                        $output .= "\n\n" . $this->convertList($child, false) . "\n\n";
                    }
                    break;

                case 'ol':
                    if ($child instanceof \DOMElement) {
                        $output .= "\n\n" . $this->convertList($child, true) . "\n\n";
                    }
                    break;

                case 'code':
                    if ($child->parentNode && strtolower($child->parentNode->nodeName) === 'pre') {
                        $output .= $innerText;
                    } else {
                        $output .= '`' . trim($innerText) . '`';
                    }
                    break;

                case 'pre':
                    $output .= "\n\n```\n" . trim($innerText) . "\n```\n\n";
                    break;

                case 'br':
                    $output .= "  \n";
                    break;

                case 'hr':
                    $output .= "\n\n---\n\n";
                    break;

                default:
                    $output .= trim($innerText);
                    break;
            }
        }

        return $output;
    }

    /**
     * Strip leading whitespace from each line while preserving
     * indentation inside fenced code blocks (``` ... ```).
     *
     * @param string $text Markdown text to process.
     * @return string Text with leading whitespace removed.
     */
    private function stripLeadingWhitespace(string $text): string
    {
        $lines = explode("\n", $text);
        $inCodeBlock = false;
        $cleaned = [];

        foreach ($lines as $line) {
            if (preg_match('/^```/', ltrim($line))) {
                $inCodeBlock = !$inCodeBlock;
                $cleaned[] = ltrim($line);
                continue;
            }

            $cleaned[] = $inCodeBlock ? $line : ltrim($line);
        }

        return implode("\n", $cleaned);
    }

    /**
     * Resolve a relative URL to an absolute one using the site's home URL.
     *
     * Leaves absolute URLs, protocol-relative URLs (//), fragment-only
     * links (#), and non-HTTP schemes (mailto:, tel:, etc.) untouched.
     *
     * @param string $url URL to resolve.
     * @return string Absolute URL.
     */
    private function resolveUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '';
        }

        // Already absolute, protocol-relative, or non-HTTP scheme
        if (preg_match('#^(https?://|//|[a-z][a-z0-9+\-.]*:)#i', $url)) {
            return $url;
        }

        $baseUrl = home_url();

        // Fragment-only link — prepend base URL
        if (strncmp($url, '#', 1) === 0) {
            return rtrim($baseUrl, '/') . '/' . $url;
        }

        // Absolute path — prepend origin only
        if (strncmp($url, '/', 1) === 0) {
            $parsed = wp_parse_url($baseUrl);
            $origin = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

            if (!empty($parsed['port'])) {
                $origin .= ':' . $parsed['port'];
            }

            return $origin . $url;
        }

        // Relative path — append to home URL
        return rtrim($baseUrl, '/') . '/' . $url;
    }

    /**
     * Convert ordered (ol) and unordered (ul) list elements.
     *
     * @param \DOMElement $listNode List DOM element.
     * @param bool $isOrdered Flag indicating whether list is ordered.
     * @return string Converted list Markdown.
     */
    private function convertList(\DOMElement $listNode, bool $isOrdered): string
    {
        $items = [];
        $index = 1;

        foreach ($listNode->childNodes as $child) {
            if ($child->nodeType === XML_ELEMENT_NODE && strtolower($child->nodeName) === 'li') {
                $itemText = trim($this->convertNode($child));
                if ($isOrdered) {
                    $items[] = $index . '. ' . $itemText;
                    $index++;
                } else {
                    $items[] = '- ' . $itemText;
                }
            }
        }

        return implode("\n", $items);
    }
}
