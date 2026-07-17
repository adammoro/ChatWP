<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Shared HTTP plumbing for providers. Concrete providers build a
 * provider-specific request body/headers and hand them to request(),
 * then parse the decoded JSON response themselves.
 */
abstract class ChatWP_Provider_Base implements ChatWP_Provider
{
    /**
     * POST JSON to $url and return the decoded response body.
     *
     * @return array|WP_Error Decoded JSON as an associative array, or WP_Error on failure.
     */
    protected function request($url, array $headers, array $body, $timeout = 60)
    {
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body' => wp_json_encode($body),
            'timeout' => $timeout,
            'httpversion' => '1.1',
            'user-agent' => 'ChatWP/2.0',
            'sslverify' => true,
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);
        $data = json_decode($raw_body, true);

        if ($status_code < 200 || $status_code >= 300) {
            $message = (is_array($data) && isset($data['error']['message']))
                ? $data['error']['message']
                : sprintf(
                    /* translators: %d: HTTP status code */
                    __('ChatWP: request failed with HTTP %d.', 'chatwp'),
                    $status_code
                );
            return new WP_Error('chatwp_http_error', $message, array('status' => $status_code, 'body' => $raw_body));
        }

        if (null === $data && '' !== trim($raw_body)) {
            return new WP_Error('chatwp_invalid_json', __('ChatWP: received an invalid JSON response.', 'chatwp'));
        }

        return $data;
    }
}
