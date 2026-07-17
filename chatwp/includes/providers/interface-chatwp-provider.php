<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Contract every ChatWP LLM provider must implement.
 *
 * A provider turns a normalized set of generation params into text, talking
 * to whatever API/protocol it wraps (OpenAI, Anthropic, Ollama, a generic
 * OpenAI-compatible endpoint, etc).
 */
interface ChatWP_Provider
{
    /**
     * Generate text from the given params.
     *
     * @param array $params {
     *     @type string $prompt             Required. The user prompt/message.
     *     @type string $system_prompt      Optional system/instructions prompt.
     *     @type string $model              Model name/id.
     *     @type float  $temperature        Sampling temperature.
     *     @type int    $max_tokens         Max tokens to generate.
     *     @type float  $frequency_penalty  Optional, not all providers support this.
     *     @type float  $presence_penalty   Optional, not all providers support this.
     * }
     * @return string|WP_Error Generated text, or WP_Error on failure.
     */
    public function generate(array $params);
}
