<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generic OpenAI-compatible endpoint, for self-hosted runtimes that expose
 * a /chat/completions route (LM Studio, vLLM, text-generation-webui, etc).
 * The base URL and (optional) API key are configured on the settings page.
 */
class ChatWP_Provider_Custom extends ChatWP_Provider_OpenAI_Compatible
{
    protected function get_base_url()
    {
        return (string) get_option('chatwp_custom_base_url', '');
    }

    protected function get_api_key()
    {
        return (string) get_option('chatwp_custom_api_key', '');
    }
}
