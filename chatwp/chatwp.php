<?php
/*
Plugin Name: ChatWP
Description: A WordPress widget that displays LLM-generated text. Bring your own OpenAI or Anthropic API key, or point it at a local/open source model (Ollama or any OpenAI-compatible endpoint) — configured per widget instance.
Version: 2.0.0
Author: Adam Moro
Author URI: https://adammoro.com/
*/

if (!defined('ABSPATH')) {
    exit;
}

define('CHATWP_VERSION', '2.0.0');
define('CHATWP_PLUGIN_FILE', __FILE__);
define('CHATWP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CHATWP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once CHATWP_PLUGIN_DIR . 'includes/providers/interface-chatwp-provider.php';
require_once CHATWP_PLUGIN_DIR . 'includes/providers/class-chatwp-provider-base.php';
require_once CHATWP_PLUGIN_DIR . 'includes/providers/class-chatwp-provider-openai-compatible.php';
require_once CHATWP_PLUGIN_DIR . 'includes/providers/class-chatwp-provider-openai.php';
require_once CHATWP_PLUGIN_DIR . 'includes/providers/class-chatwp-provider-anthropic.php';
require_once CHATWP_PLUGIN_DIR . 'includes/providers/class-chatwp-provider-ollama.php';
require_once CHATWP_PLUGIN_DIR . 'includes/providers/class-chatwp-provider-custom.php';
require_once CHATWP_PLUGIN_DIR . 'includes/class-chatwp-provider-factory.php';
require_once CHATWP_PLUGIN_DIR . 'includes/class-chatwp-widget.php';
require_once CHATWP_PLUGIN_DIR . 'includes/class-chatwp-settings.php';

// Settings page (Settings → ChatWP): API keys and endpoint URLs for every provider.
new ChatWP_Settings();

// Register the ChatWP_Widget widget.
function register_chatwp_widget()
{
    register_widget('ChatWP_Widget');
}
add_action('widgets_init', 'register_chatwp_widget');

// Toggle provider-specific fields in the widget form (widgets screen + Customizer).
function chatwp_enqueue_admin_widget_script($hook)
{
    if (!in_array($hook, array('widgets.php', 'customize.php'), true)) {
        return;
    }
    wp_enqueue_script(
        'chatwp-admin-widget',
        CHATWP_PLUGIN_URL . 'assets/admin-widget.js',
        array(),
        CHATWP_VERSION,
        true
    );
}
add_action('admin_enqueue_scripts', 'chatwp_enqueue_admin_widget_script');
add_action('customize_controls_enqueue_scripts', function () {
    chatwp_enqueue_admin_widget_script('customize.php');
});
