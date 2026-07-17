# ChatWP WordPress Plugin #

A WordPress plugin that displays LLM-generated text in a sidebar widget. Each
widget instance picks its own provider and model — OpenAI, Anthropic
(Claude), a local Ollama install, or any other self-hosted model that speaks
the OpenAI-compatible chat API (LM Studio, vLLM, text-generation-webui, etc).

## Usage ##

1. Download the `chatwp` folder (or `ChatWP.zip`, which contains the same
   folder), upload it to your `wp-content/plugins` directory, and activate
   the ChatWP plugin from the Plugins screen.

2. Go to **Settings → ChatWP** and fill in the credentials for whichever
   provider(s) you plan to use:
   - **OpenAI API Key** — for OpenAI models.
   - **Anthropic API Key** — for Claude models.
   - **Ollama Base URL** — defaults to `http://localhost:11434`, the default
     address of a local [Ollama](https://ollama.com) install. Pull a model
     first (e.g. `ollama pull llama3.1`).
   - **Custom Endpoint Base URL** (+ optional API key) — for any other
     OpenAI-compatible `/chat/completions` endpoint, such as LM Studio,
     vLLM, or text-generation-webui.

   You only need to configure the providers you actually plan to use.

3. Go to **Appearance → Widgets**, add a "ChatWP Widget", and configure it:
   - **LLM Provider** — pick OpenAI, Anthropic, Ollama, or Custom Endpoint.
   - **Model** — the model name/id understood by that provider (e.g.
     `gpt-4o-mini`, `claude-sonnet-4-5`, `llama3.1`).
   - **System Prompt** (optional) and **Prompt** — what gets sent to the model.
   - **Temperature**, **Max Tokens**, and (for OpenAI/custom endpoints)
     **Frequency Penalty** / **Presence Penalty**.

   Add as many widget instances as you like, each with its own provider,
   model, and prompt.

## Generation parameters ##

- **model**: The provider-specific model name/id to use.
- **prompt**: The user message sent to the model.
- **system_prompt**: Optional system/instructions message.
- **temperature**: Controls the randomness/creativity of the output.
- **max_tokens**: Caps the length of the generated response.
- **frequency_penalty** / **presence_penalty**: Only sent to OpenAI and
  custom OpenAI-compatible endpoints — Anthropic's and Ollama's chat APIs
  don't support these.
