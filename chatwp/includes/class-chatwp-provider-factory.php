<?php
if (!defined('ABSPATH')) {
    exit;
}

class ChatWP_Provider_Factory
{
    /**
     * @return array<string,string> provider key => human-readable label
     */
    public static function get_providers()
    {
        return array(
            'openai' => __('OpenAI', 'chatwp'),
            'anthropic' => __('Anthropic (Claude)', 'chatwp'),
            'ollama' => __('Ollama (local/open source model)', 'chatwp'),
            'custom' => __('Custom OpenAI-compatible endpoint', 'chatwp'),
        );
    }

    /**
     * @return ChatWP_Provider
     */
    public static function create($provider_key)
    {
        switch ($provider_key) {
            case 'anthropic':
                return new ChatWP_Provider_Anthropic();
            case 'ollama':
                return new ChatWP_Provider_Ollama();
            case 'custom':
                return new ChatWP_Provider_Custom();
            case 'openai':
            default:
                return new ChatWP_Provider_OpenAI();
        }
    }
}
