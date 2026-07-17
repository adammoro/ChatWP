<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Base for any provider that speaks the OpenAI Chat Completions wire format
 * (POST {base_url}/chat/completions, choices[0].message.content response).
 * This covers OpenAI itself and most self-hosted runtimes (LM Studio,
 * text-generation-webui, vLLM, etc) that expose an OpenAI-compatible API.
 */
abstract class ChatWP_Provider_OpenAI_Compatible extends ChatWP_Provider_Base
{
    /**
     * Base URL, no trailing slash, e.g. "https://api.openai.com/v1".
     */
    abstract protected function get_base_url();

    /**
     * Bearer token to send, or '' if the endpoint needs no auth.
     */
    protected function get_api_key()
    {
        return '';
    }

    public function generate(array $params)
    {
        $base_url = rtrim((string) $this->get_base_url(), '/');
        if (empty($base_url)) {
            return new WP_Error('chatwp_missing_config', __('ChatWP: no endpoint URL is configured for this provider.', 'chatwp'));
        }

        $messages = array();
        if (!empty($params['system_prompt'])) {
            $messages[] = array('role' => 'system', 'content' => $params['system_prompt']);
        }
        $messages[] = array('role' => 'user', 'content' => $params['prompt']);

        $body = array(
            'model' => $params['model'],
            'messages' => $messages,
            'temperature' => (float) $params['temperature'],
            'max_tokens' => (int) $params['max_tokens'],
        );
        if (isset($params['frequency_penalty']) && '' !== $params['frequency_penalty']) {
            $body['frequency_penalty'] = (float) $params['frequency_penalty'];
        }
        if (isset($params['presence_penalty']) && '' !== $params['presence_penalty']) {
            $body['presence_penalty'] = (float) $params['presence_penalty'];
        }

        $headers = array('Content-Type' => 'application/json');
        $api_key = $this->get_api_key();
        if (!empty($api_key)) {
            $headers['Authorization'] = 'Bearer ' . $api_key;
        }

        $data = $this->request($base_url . '/chat/completions', $headers, $body);
        if (is_wp_error($data)) {
            return $data;
        }

        if (!isset($data['choices'][0]['message']['content'])) {
            return new WP_Error('chatwp_unexpected_response', __('ChatWP: unexpected response shape from provider.', 'chatwp'));
        }

        return (string) $data['choices'][0]['message']['content'];
    }
}
