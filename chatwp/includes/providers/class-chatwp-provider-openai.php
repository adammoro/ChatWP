<?php
if (!defined('ABSPATH')) {
    exit;
}

class ChatWP_Provider_OpenAI extends ChatWP_Provider_OpenAI_Compatible
{
    protected function get_base_url()
    {
        return 'https://api.openai.com/v1';
    }

    protected function get_api_key()
    {
        return (string) get_option('chatwp_openai_api_key', '');
    }

    public function generate(array $params)
    {
        if (empty($this->get_api_key())) {
            return new WP_Error('chatwp_missing_api_key', __('ChatWP: OpenAI API key is not configured. Add it under Settings → ChatWP.', 'chatwp'));
        }
        return parent::generate($params);
    }
}
