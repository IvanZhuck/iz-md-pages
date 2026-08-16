<?php

declare(strict_types=1);

namespace IZMDPages\Core\Template;

/**
 * Handles template rendering and variable extraction.
 */
class TemplateRenderer
{
    /**
     * Absolute path to the templates directory.
     */
    private string $templatesDir;

    /**
     * TemplateRenderer constructor.
     *
     * @param string|null $templatesDir Custom templates directory path or default plugin templates dir.
     */
    public function __construct(?string $templatesDir = null)
    {
        $this->templatesDir = $templatesDir !== null
            ? rtrim($templatesDir, '/\\') . '/'
            : dirname(__DIR__, 3) . '/templates/';
    }

    /**
     * Render a template file directly to output.
     *
     * @param string $template Relative path to template file.
     * @param array<string, mixed> $data Variables to extract into template scope.
     */
    public function render(string $template, array $data = []): void
    {
        $templateFile = $this->resolveTemplatePath($template);

        if (!file_exists($templateFile)) {
            return;
        }

        extract($data, EXTR_SKIP);
        include $templateFile;
    }

    /**
     * Render a template file and return its output as a string.
     *
     * @param string $template Relative path to template file.
     * @param array<string, mixed> $data Variables to extract into template scope.
     * @return string Rendered template content.
     */
    public function renderToString(string $template, array $data = []): string
    {
        ob_start();
        $this->render($template, $data);
        return (string) ob_get_clean();
    }

    /**
     * Resolve full absolute path for a relative template path.
     *
     * @param string $template Relative path to template file.
     * @return string Absolute file path.
     */
    private function resolveTemplatePath(string $template): string
    {
        return $this->templatesDir . ltrim($template, '/\\');
    }
}
