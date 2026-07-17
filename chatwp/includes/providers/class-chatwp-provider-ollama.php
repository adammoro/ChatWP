<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Native Ollama chat API (POST {base_url}/api/chat), for locally-hosted
 * open source models. Defaults to Ollama's default local address.
 */
class ChatWP_Provider_Ollama extends ChatWP_Provider_Base
{
    const DEFAULT_BASE_URL = 'http://localhost:11434';

    public function generate(array $params)
    {
        $base_url = rtrim((string) get_option('chatwp_ollama_base_url', self::DEFAULT_BASE_URL), '/');
        if (empty($base_url)) {
            return new WP_Error('chatwp_missing_config', __('ChatWP: no Ollama base URL is configured. Add one under Settings → ChatWP.', 'chatwp'));
        }

        $messages = array();
        if (!empty($params['system_prompt'])) {
            $messages[] = array('role' => 'system', 'content' => $params['system_prompt']);
        }
        $messages[] = array('role' => 'user', 'content' => $params['prompt']);

        $options = array('temperature' => (float) $params['temperature']);
        if (!empty($params['max_tokens'])) {
            $options['num_predict'] = (int) $params['max_tokens'];
        }

        $body = array(
            'model' => $params['model'],
            'messages' => $messages,
            'stream' => false,
            'options' => $options,
        );

        $headers = array('Content-Type' => 'application/json');

        // Local models can be slow, especially on CPU-only hardware.
        $data = $this->request($base_url . '/api/chat', $headers, $body, 120);
        if (is_wp_error($data)) {
            return $data;
        }

        if (!isset($data['message']['content'])) {
            return new WP_Error('chatwp_unexpected_response', __('ChatWP: unexpected response shape from Ollama.', 'chatwp'));
        }

        return (string) $data['message']['content'];
    }
}
