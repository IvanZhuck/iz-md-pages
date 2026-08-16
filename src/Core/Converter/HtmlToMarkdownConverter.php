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

        // Normalize multiple consecutive blank lines
        $result = preg_replace("/\n{3,}/", "\n\n", trim($markdown));
        return is_string($result) ? $result : trim($markdown);
    }

    /**
     * Recursively convert DOM nodes into Markdown.
     *
     * @param \DOMNode $node DOM node to process.
     * @return string Converted Markdown snippet.
     */
    private function convertNode(\DOMNode $node): string
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
                    $output .= ' **' . trim($innerText) . '** ';
                    break;

                case 'em':
                case 'i':
                    $output .= ' *' . trim($innerText) . '* ';
                    break;

                case 'del':
                case 's':
                case 'strike':
                    $output .= ' ~~' . trim($innerText) . '~~ ';
                    break;

                case 'a':
                    if ($child instanceof \DOMElement) {
                        $href = $child->getAttribute('href');
                        if (!empty($href)) {
                            $output .= '[' . trim($innerText) . '](' . esc_url($href) . ')';
                        } else {
                            $output .= $innerText;
                        }
                    } else {
                        $output .= $innerText;
                    }
                    break;

                case 'img':
                    if ($child instanceof \DOMElement) {
                        $src = $child->getAttribute('src');
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
                        $output .= ' `' . trim($innerText) . '` ';
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
                    $output .= $innerText;
                    break;
            }
        }

        return $output;
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
