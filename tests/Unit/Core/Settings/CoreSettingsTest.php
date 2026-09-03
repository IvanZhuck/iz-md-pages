<?php

declare(strict_types=1);

namespace IZMDPages\Tests\Unit\Core\Settings;

use IZMDPages\Core\Settings\CoreSettings;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for CoreSettings.
 */
class CoreSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        global $wp_options, $wp_filter, $wp_post_types_storage;
        $wp_options = [];
        $wp_filter = [];
        $wp_post_types_storage = [
            'post' => new \WP_Post_Type('post', ['label' => 'Posts', 'public' => true]),
            'page' => new \WP_Post_Type('page', ['label' => 'Pages', 'public' => true]),
            'attachment' => new \WP_Post_Type('attachment', ['label' => 'Media', 'public' => true]),
        ];
    }

    protected function tearDown(): void
    {
        if (function_exists('remove_all_filters')) {
            remove_all_filters('iz_md_post_type_template_post');
            remove_all_filters('iz_md_post_type_template_page');
        }
        parent::tearDown();
    }

    public function testConstantsAreProperlyDefined(): void
    {
        $this->assertSame('iz_md_enabled_post_types', CoreSettings::OPTION_ENABLED_POST_TYPES);
        $this->assertSame('iz_md_url_suffix_type', CoreSettings::OPTION_URL_SUFFIX_TYPE);
        $this->assertSame('iz_md_enable_front_page', CoreSettings::OPTION_ENABLE_FRONT_PAGE);
        $this->assertSame('iz_md_templates', CoreSettings::OPTION_TEMPLATES);
        $this->assertSame('iz_md_header_template', CoreSettings::OPTION_HEADER_TEMPLATE);
        $this->assertSame('iz_md_footer_template', CoreSettings::OPTION_FOOTER_TEMPLATE);

        $this->assertSame(['post', 'page'], CoreSettings::DEFAULT_ENABLED_POST_TYPES);
        $this->assertSame('endpoint', CoreSettings::DEFAULT_SUFFIX_TYPE);
        $this->assertSame(1, CoreSettings::DEFAULT_ENABLE_FRONT_PAGE);
        $this->assertSame("# {%post_title%}\n\n{%post_content%}", CoreSettings::DEFAULT_TEMPLATE);
        $this->assertStringContainsString('IMPORTANT FOR LLMs', CoreSettings::DEFAULT_HEADER_TEMPLATE);
    }

    public function testGetPluginFilePathReturnsAbsolutePath(): void
    {
        $filePath = CoreSettings::getPluginFilePath();

        $this->assertStringEndsWith('iz-md-pages.php', $filePath);
        $this->assertFileExists($filePath);
    }

    public function testGetPluginDirReturnsTrailingSlashPath(): void
    {
        $dir = CoreSettings::getPluginDir();

        $this->assertStringEndsWith('/', $dir);
        $this->assertDirectoryExists($dir);
        $this->assertFileExists($dir . 'iz-md-pages.php');
    }

    public function testGetTemplatesDirReturnsTrailingSlashPath(): void
    {
        $dir = CoreSettings::getTemplatesDir();

        $this->assertStringEndsWith('/templates/', $dir);
        $this->assertDirectoryExists($dir);
    }

    public function testGetPluginBaseNameReturnsBasenameFormat(): void
    {
        $baseName = CoreSettings::getPluginBaseName();

        $this->assertSame('iz-md-pages/iz-md-pages.php', $baseName);
    }

    public function testGetTargetPostTypesExcludesAttachments(): void
    {
        $postTypes = CoreSettings::getTargetPostTypes();

        $this->assertArrayHasKey('post', $postTypes);
        $this->assertArrayHasKey('page', $postTypes);
        $this->assertArrayNotHasKey('attachment', $postTypes);
    }

    public function testGetEnabledPostTypesReturnsDefaultsWhenNotConfigured(): void
    {
        $this->assertSame(['post', 'page'], CoreSettings::getEnabledPostTypes());
    }

    public function testGetEnabledPostTypesReturnsConfiguredArray(): void
    {
        global $wp_options;
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = ['page', 'custom_book'];

        $this->assertSame(['page', 'custom_book'], CoreSettings::getEnabledPostTypes());
    }

    public function testIsPostTypeEnabled(): void
    {
        global $wp_options;
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = ['post'];

        $this->assertTrue(CoreSettings::isPostTypeEnabled('post'));
        $this->assertFalse(CoreSettings::isPostTypeEnabled('page'));
    }

    public function testGetUrlSuffixTypeReturnsDefaultWhenNotConfigured(): void
    {
        $this->assertSame('endpoint', CoreSettings::getUrlSuffixType());
    }

    public function testGetUrlSuffixTypeReturnsConfiguredOrFallback(): void
    {
        global $wp_options;

        $wp_options[CoreSettings::OPTION_URL_SUFFIX_TYPE] = 'query_var';
        $this->assertSame('query_var', CoreSettings::getUrlSuffixType());

        $wp_options[CoreSettings::OPTION_URL_SUFFIX_TYPE] = 'invalid_suffix';
        $this->assertSame('endpoint', CoreSettings::getUrlSuffixType());
    }

    public function testIsFrontPageEnabled(): void
    {
        global $wp_options;

        $this->assertTrue(CoreSettings::isFrontPageEnabled());

        $wp_options[CoreSettings::OPTION_ENABLE_FRONT_PAGE] = 0;
        $this->assertFalse(CoreSettings::isFrontPageEnabled());

        $wp_options[CoreSettings::OPTION_ENABLE_FRONT_PAGE] = 1;
        $this->assertTrue(CoreSettings::isFrontPageEnabled());
    }

    public function testGetHeaderTemplateReturnsDefaultWhenUnset(): void
    {
        $this->assertSame(CoreSettings::DEFAULT_HEADER_TEMPLATE, CoreSettings::getHeaderTemplate());
    }

    public function testGetHeaderTemplateReturnsConfigured(): void
    {
        global $wp_options;
        $wp_options[CoreSettings::OPTION_HEADER_TEMPLATE] = '# Custom Header';

        $this->assertSame('# Custom Header', CoreSettings::getHeaderTemplate());
    }

    public function testGetFooterTemplateReturnsEmptyWhenUnset(): void
    {
        $this->assertSame('', CoreSettings::getFooterTemplate());
    }

    public function testGetFooterTemplateReturnsConfigured(): void
    {
        global $wp_options;
        $wp_options[CoreSettings::OPTION_FOOTER_TEMPLATE] = '# Custom Footer';

        $this->assertSame('# Custom Footer', CoreSettings::getFooterTemplate());
    }

    public function testGetTemplateForPostTypeReturnsDefaultWhenUnset(): void
    {
        $this->assertSame(CoreSettings::DEFAULT_TEMPLATE, CoreSettings::getTemplateForPostType('post'));
    }

    public function testGetTemplateForPostTypeReturnsConfigured(): void
    {
        global $wp_options;
        $wp_options[CoreSettings::OPTION_TEMPLATES] = [
            'post' => '## Post: {%post_title%}',
        ];

        $this->assertSame('## Post: {%post_title%}', CoreSettings::getTemplateForPostType('post'));
    }

    public function testGetTemplateForPostTypeAppliesFilterHook(): void
    {
        add_filter('iz_md_post_type_template_post', function (string $template, string $postType): string {
            return '### Hook Override for ' . $postType;
        }, 10, 2);

        $this->assertSame('### Hook Override for post', CoreSettings::getTemplateForPostType('post'));
    }

    public function testGetEnabledPostTypesReturnsDefaultWhenOptionIsNotArray(): void
    {
        global $wp_options;
        $wp_options[CoreSettings::OPTION_ENABLED_POST_TYPES] = 'not_an_array';

        $this->assertSame(['post', 'page'], CoreSettings::getEnabledPostTypes());
    }

    public function testGetHeaderTemplateReturnsDefaultWhenOptionIsNotString(): void
    {
        global $wp_options;
        $wp_options[CoreSettings::OPTION_HEADER_TEMPLATE] = ['not_a_string'];

        $this->assertSame(CoreSettings::DEFAULT_HEADER_TEMPLATE, CoreSettings::getHeaderTemplate());
    }

    public function testGetFooterTemplateReturnsEmptyWhenOptionIsNotString(): void
    {
        global $wp_options;
        $wp_options[CoreSettings::OPTION_FOOTER_TEMPLATE] = ['not_a_string'];

        $this->assertSame('', CoreSettings::getFooterTemplate());
    }

    public function testGetTemplateForPostTypeReturnsDefaultWhenEmptyOrNonString(): void
    {
        global $wp_options;

        $wp_options[CoreSettings::OPTION_TEMPLATES] = [
            'post' => '',
            'page' => 12345,
        ];

        $this->assertSame(CoreSettings::DEFAULT_TEMPLATE, CoreSettings::getTemplateForPostType('post'));
        $this->assertSame(CoreSettings::DEFAULT_TEMPLATE, CoreSettings::getTemplateForPostType('page'));
        $this->assertSame(CoreSettings::DEFAULT_TEMPLATE, CoreSettings::getTemplateForPostType('unconfigured_type'));
    }

    public function testIsTemplateOverriddenDetectsFilterHook(): void
    {
        $this->assertFalse(CoreSettings::isTemplateOverridden('page'));

        add_filter('iz_md_post_type_template_page', function (string $template): string {
            return $template;
        });

        $this->assertTrue(CoreSettings::isTemplateOverridden('page'));
    }
}
