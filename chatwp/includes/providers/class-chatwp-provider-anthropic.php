<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Anthropic Messages API (https://api.anthropic.com/v1/messages).
 */
class ChatWP_Provider_Anthropic extends ChatWP_Provider_Base
{
    const API_URL = 'https://api.anthropic.com/v1/messages';
    const API_VERSION = '2023-06-01';

    public function generate(array $params)
    {
        $api_key = (string) get_option('chatwp_anthropic_api_key', '');
        if (empty($api_key)) {
            return new WP_Error('chatwp_missing_api_key', __('ChatWP: Anthropic API key is not configured. Add it under Settings → ChatWP.', 'chatwp'));
        }

        $body = array(
            'model' => $params['model'],
            'max_tokens' => (int) $params['max_tokens'],
            'temperature' => (float) $params['temperature'],
            'messages' => array(
                array('role' => 'user', 'content' => $params['prompt']),
            ),
        );
        if (!empty($params['system_prompt'])) {
            $body['system'] = $params['system_prompt'];
        }

        $headers = array(
            'Content-Type' => 'application/json',
            'x-api-key' => $api_key,
            'anthropic-version' => self::API_VERSION,
        );

        $data = $this->request(self::API_URL, $headers, $body);
        if (is_wp_error($data)) {
            return $data;
        }

        if (empty($data['content']) || !is_array($data['content'])) {
            return new WP_Error('chatwp_unexpected_response', __('ChatWP: unexpected response shape from Anthropic.', 'chatwp'));
        }

        $text = '';
        foreach ($data['content'] as $block) {
            if (isset($block['type'], $block['text']) && 'text' === $block['type']) {
                $text .= $block['text'];
            }
        }

        return $text;
    }
}
