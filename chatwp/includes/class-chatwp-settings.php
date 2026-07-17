<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Settings → ChatWP admin page: API keys and endpoint URLs for every
 * provider. Widget instances reference these by provider key; nothing
 * provider-specific is stored on the widget itself.
 */
class ChatWP_Settings
{
    const OPTION_GROUP = 'chatwp';
    const PAGE_SLUG = 'chatwp';

    public function __construct()
    {
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_menu', array($this, 'add_options_page'));
    }

    public function register_settings()
    {
        register_setting(self::OPTION_GROUP, 'chatwp_openai_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting(self::OPTION_GROUP, 'chatwp_anthropic_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));
        register_setting(self::OPTION_GROUP, 'chatwp_ollama_base_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => ChatWP_Provider_Ollama::DEFAULT_BASE_URL,
        ));
        register_setting(self::OPTION_GROUP, 'chatwp_custom_base_url', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ));
        register_setting(self::OPTION_GROUP, 'chatwp_custom_api_key', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        add_settings_section(
            'chatwp_hosted_apis',
            __('Hosted API Keys', 'chatwp'),
            function () {
                echo '<p>' . esc_html__('API keys for hosted providers. Only fill in the ones you plan to use.', 'chatwp') . '</p>';
            },
            self::PAGE_SLUG
        );
        add_settings_field('chatwp_openai_api_key', __('OpenAI API Key', 'chatwp'), array($this, 'render_password_field'), self::PAGE_SLUG, 'chatwp_hosted_apis', array('option' => 'chatwp_openai_api_key'));
        add_settings_field('chatwp_anthropic_api_key', __('Anthropic API Key', 'chatwp'), array($this, 'render_password_field'), self::PAGE_SLUG, 'chatwp_hosted_apis', array('option' => 'chatwp_anthropic_api_key'));

        add_settings_section(
            'chatwp_local_apis',
            __('Local / Self-Hosted Models', 'chatwp'),
            function () {
                echo '<p>' . esc_html__('Point ChatWP at an open source model running on your own infrastructure.', 'chatwp') . '</p>';
            },
            self::PAGE_SLUG
        );
        add_settings_field('chatwp_ollama_base_url', __('Ollama Base URL', 'chatwp'), array($this, 'render_text_field'), self::PAGE_SLUG, 'chatwp_local_apis', array(
            'option' => 'chatwp_ollama_base_url',
            'placeholder' => ChatWP_Provider_Ollama::DEFAULT_BASE_URL,
            'description' => __('Default address of a local Ollama install.', 'chatwp'),
        ));
        add_settings_field('chatwp_custom_base_url', __('Custom Endpoint Base URL', 'chatwp'), array($this, 'render_text_field'), self::PAGE_SLUG, 'chatwp_local_apis', array(
            'option' => 'chatwp_custom_base_url',
            'placeholder' => 'http://localhost:1234/v1',
            'description' => __('Any OpenAI-compatible /chat/completions endpoint (LM Studio, vLLM, text-generation-webui, etc).', 'chatwp'),
        ));
        add_settings_field('chatwp_custom_api_key', __('Custom Endpoint API Key', 'chatwp'), array($this, 'render_password_field'), self::PAGE_SLUG, 'chatwp_local_apis', array(
            'option' => 'chatwp_custom_api_key',
            'description' => __('Optional — leave blank if your endpoint doesn\'t require auth.', 'chatwp'),
        ));
    }

    public function render_password_field($args)
    {
        $value = get_option($args['option'], '');
        printf(
            '<input type="password" autocomplete="off" class="regular-text" name="%1$s" value="%2$s">',
            esc_attr($args['option']),
            esc_attr($value)
        );
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function render_text_field($args)
    {
        $value = get_option($args['option'], '');
        printf(
            '<input type="text" class="regular-text" name="%1$s" value="%2$s" placeholder="%3$s">',
            esc_attr($args['option']),
            esc_attr($value),
            esc_attr(isset($args['placeholder']) ? $args['placeholder'] : '')
        );
        if (!empty($args['description'])) {
            echo '<p class="description">' . esc_html($args['description']) . '</p>';
        }
    }

    public function add_options_page()
    {
        add_options_page(
            'ChatWP',
            'ChatWP',
            'manage_options',
            self::PAGE_SLUG,
            array($this, 'render_options_page')
        );
    }

    public function render_options_page()
    {
    ?>
        <div class="wrap">
            <h2><?php echo esc_html(get_admin_page_title()); ?></h2>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::OPTION_GROUP);
                do_settings_sections(self::PAGE_SLUG);
                submit_button();
                ?>
            </form>
        </div>
<?php
    }
}
