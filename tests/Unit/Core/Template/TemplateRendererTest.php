<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Core\Template;

use IZMDPages\Core\Template\TemplateRenderer;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for TemplateRenderer.
 */
class TemplateRendererTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir() . '/iz_md_pages_test_templates_' . uniqid('', true);
        if (!is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
        parent::tearDown();
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = scandir($dir);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $filePath = $dir . '/' . $file;
            if (is_dir($filePath)) {
                $this->removeDirectory($filePath);
            } else {
                unlink($filePath);
            }
        }

        rmdir($dir);
    }

    public function testConstructorAcceptsCustomTemplatesDirectory(): void
    {
        $renderer = new TemplateRenderer($this->tempDir);

        file_put_contents($this->tempDir . '/simple.php', '<?php echo "Hello, " . esc_html($name); ?>');

        $output = $renderer->renderToString('simple.php', ['name' => 'Alice']);
        $this->assertSame('Hello, Alice', $output);
    }

    public function testRenderOutputsTemplateDirectly(): void
    {
        $renderer = new TemplateRenderer($this->tempDir);

        file_put_contents($this->tempDir . '/direct.php', '<div><?php echo esc_html($title); ?></div>');

        ob_start();
        $renderer->render('direct.php', ['title' => 'My Title']);
        $output = ob_get_clean();

        $this->assertSame('<div>My Title</div>', $output);
    }

    public function testRenderToStringReturnsRenderedOutput(): void
    {
        $renderer = new TemplateRenderer($this->tempDir);

        file_put_contents($this->tempDir . '/view.php', '<h1><?php echo esc_html($heading); ?></h1><p><?php echo esc_html($message); ?></p>');

        $result = $renderer->renderToString('view.php', [
            'heading' => 'Welcome',
            'message' => 'This is a test message.',
        ]);

        $this->assertSame('<h1>Welcome</h1><p>This is a test message.</p>', $result);
    }

    public function testRenderDoesNothingWhenTemplateFileDoesNotExist(): void
    {
        $renderer = new TemplateRenderer($this->tempDir);

        ob_start();
        $renderer->render('non_existent_file.php', ['foo' => 'bar']);
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testRenderToStringReturnsEmptyStringForNonExistentTemplate(): void
    {
        $renderer = new TemplateRenderer($this->tempDir);

        $result = $renderer->renderToString('missing.php');
        $this->assertSame('', $result);
    }

    public function testResolveTemplatePathHandlesLeadingSlashes(): void
    {
        $renderer = new TemplateRenderer($this->tempDir);

        $subDir = $this->tempDir . '/nested';
        mkdir($subDir, 0777, true);
        file_put_contents($subDir . '/test.php', 'Nested output: <?php echo esc_html($val); ?>');

        $outputWithLeadingSlash = $renderer->renderToString('/nested/test.php', ['val' => '123']);
        $outputWithoutLeadingSlash = $renderer->renderToString('nested/test.php', ['val' => '123']);

        $this->assertSame('Nested output: 123', $outputWithLeadingSlash);
        $this->assertSame('Nested output: 123', $outputWithoutLeadingSlash);
    }

    public function testRenderPreservesInternalVariablesDueToExtrSkip(): void
    {
        $renderer = new TemplateRenderer($this->tempDir);

        // Attempting to pass $templateFile in $data should not overwrite the resolved $templateFile inside render()
        file_put_contents($this->tempDir . '/safe.php', 'OK: <?php echo esc_html($templateFile); ?>');

        $output = $renderer->renderToString('safe.php', ['templateFile' => 'malicious_override']);
        // Because EXTR_SKIP is used, $templateFile remains the original path resolved by TemplateRenderer
        $this->assertStringContainsString($this->tempDir . '/safe.php', $output);
    }

    public function testDefaultConstructorResolvesPluginTemplatesDirectory(): void
    {
        $renderer = new TemplateRenderer();

        // Check rendering of an actual plugin template file (e.g. overview.php)
        $output = $renderer->renderToString('admin/info/overview.php');
        $this->assertStringContainsString('id="iz-md-overview"', $output);
    }
}
