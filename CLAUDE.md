# CLAUDE.md

Guidance for AI assistants working in this repository.

## Overview

ChatWP is a WordPress plugin that adds a sidebar widget which displays
LLM-generated text. Each widget instance picks its own **provider**
(OpenAI, Anthropic/Claude, a local Ollama install, or any other
OpenAI-compatible endpoint) and model, independent of other widget
instances. Provider credentials/endpoints (API keys, base URLs) are
configured once, site-wide, on the settings page.

There is no build system, package manager, or automated test suite. Treat
this as plain PHP written against the WordPress plugin API — no Composer,
no npm, no PHPUnit configured.

## Repository structure

| Path                                                    | Purpose                                                        |
|----------------------------------------------------------|-----------------------------------------------------------------|
| `chatwp/chatwp.php`                                     | Plugin bootstrap: header, constants, `require_once`s, hook registration. |
| `chatwp/includes/class-chatwp-widget.php`               | `ChatWP_Widget` (extends `WP_Widget`) — the widget itself.       |
| `chatwp/includes/class-chatwp-settings.php`             | Settings → ChatWP admin page (API keys, endpoint URLs).          |
| `chatwp/includes/class-chatwp-provider-factory.php`     | Maps a provider key (`openai`/`anthropic`/`ollama`/`custom`) to a provider instance. |
| `chatwp/includes/providers/interface-chatwp-provider.php` | `ChatWP_Provider` contract: `generate(array $params)`.         |
| `chatwp/includes/providers/class-chatwp-provider-base.php` | Shared `wp_remote_post` + JSON decode/error-handling plumbing. |
| `chatwp/includes/providers/class-chatwp-provider-openai-compatible.php` | Shared request/response logic for any provider that speaks the OpenAI Chat Completions wire format. |
| `chatwp/includes/providers/class-chatwp-provider-openai.php` | OpenAI, via the OpenAI-compatible base (`https://api.openai.com/v1`). |
| `chatwp/includes/providers/class-chatwp-provider-custom.php` | Generic self-hosted OpenAI-compatible endpoint (LM Studio, vLLM, text-generation-webui, ...), via the same base. |
| `chatwp/includes/providers/class-chatwp-provider-anthropic.php` | Anthropic Messages API (`https://api.anthropic.com/v1/messages`). |
| `chatwp/includes/providers/class-chatwp-provider-ollama.php` | Ollama's native chat API (`{base_url}/api/chat`), for local open-source models. |
| `chatwp/assets/admin-widget.js`                         | Enqueued on the widgets screen/Customizer; shows/hides provider-specific widget fields. |
| `ChatWP.zip`                                            | A zipped copy of `chatwp/`, offered in the README as an alternate download. Packaged manually — see "Distribution note" below. |
| `README.md`                                             | End-user install/usage docs and a parameter glossary.            |
| `LICENSE`                                               | MIT license.                                                     |

## Architecture

**Provider abstraction.** Every provider implements `ChatWP_Provider::generate(array $params)`,
which takes a normalized param set (`prompt`, `system_prompt`, `model`,
`temperature`, `max_tokens`, `frequency_penalty`, `presence_penalty`) and
returns generated text or a `WP_Error`. `ChatWP_Provider_Base` centralizes
the `wp_remote_post` call, HTTP-status handling, and JSON decoding so each
concrete provider only builds its own request body/headers and parses its
own response shape.

Because OpenAI and most self-hosted runtimes (LM Studio, vLLM,
text-generation-webui, etc) all expose the same `/chat/completions` wire
format, `ChatWP_Provider_OpenAI_Compatible` implements that shape once;
`ChatWP_Provider_OpenAI` and `ChatWP_Provider_Custom` just supply a base URL
and (optional) API key. Anthropic and Ollama have their own request/response
shapes and are implemented directly against `ChatWP_Provider_Base`.

`ChatWP_Provider_Factory::create($provider_key)` resolves a provider key to
an instance; `ChatWP_Provider_Factory::get_providers()` is the canonical
list of provider keys → human-readable labels, used to populate both the
widget's provider dropdown and to validate submitted widget settings.

**Widget** (`ChatWP_Widget`):
- `form()` renders title, provider dropdown, model, system prompt, prompt,
  temperature, max_tokens, and (OpenAI/custom-only) frequency/presence
  penalty fields. Provider-specific fields are marked with
  `class="chatwp-provider-only" data-providers="openai,custom"` so
  `assets/admin-widget.js` can show/hide them based on the selected provider.
- `update()` sanitizes input with `sanitize_text_field()` /
  `sanitize_textarea_field()`, and validates `provider` against
  `ChatWP_Provider_Factory::get_providers()` (falls back to `openai` if
  unrecognized).
- `widget()` resolves the provider via the factory, calls `generate()`, and
  renders the result inside `.chatwp-text`. On `WP_Error`, the message is
  only shown to users who `current_user_can('manage_options')` — anonymous
  visitors just see an empty widget, so API errors aren't leaked publicly.

**Settings** (`ChatWP_Settings`): registers one option per provider
credential/endpoint (`chatwp_openai_api_key`, `chatwp_anthropic_api_key`,
`chatwp_ollama_base_url`, `chatwp_custom_base_url`, `chatwp_custom_api_key`)
via the Settings API, rendered as two sections ("Hosted API Keys" and
"Local / Self-Hosted Models") under Settings → ChatWP. Widgets only store a
provider key, never credentials — credentials always come from these
site-wide options.

## Development environment

There's no build step. To run/test changes, copy or symlink the `chatwp/`
folder into a local WordPress install's `wp-content/plugins/` directory and
activate "ChatWP" from the Plugins screen. There is no linter, formatter, or
automated test suite wired into CI — verify changes manually inside
WordPress (activate the plugin, configure settings, add a widget for each
provider you touched).

For quick logic checks without a full WordPress install, `php -l` catches
syntax errors, and a throwaway script that stubs the handful of WordPress
functions each class calls (`get_option`, `wp_remote_post`,
`wp_remote_retrieve_*`, `is_wp_error`, `__`, etc. — plus `define('ABSPATH', ...)`,
since every file exits immediately if `ABSPATH` isn't defined) can exercise
provider request-building and response-parsing directly. This isn't a
committed test suite — there's no `tests/` directory — just a useful pattern
if you're validating provider changes.

## Conventions

- One class per file, filenames prefixed `class-chatwp-*.php` /
  `interface-chatwp-*.php`, matching WordPress plugin conventions.
- Every PHP file starts with `if (!defined('ABSPATH')) { exit; }` to block
  direct access outside WordPress.
- New providers: implement `ChatWP_Provider` (extend
  `ChatWP_Provider_OpenAI_Compatible` if the target speaks the OpenAI chat
  format; otherwise extend `ChatWP_Provider_Base` directly), then add a case
  to `ChatWP_Provider_Factory::create()` and an entry in `get_providers()`.
- Sanitize widget input with `sanitize_text_field()` / `sanitize_textarea_field()`
  in `update()`; escape output with `esc_attr()` / `esc_html()` /
  `esc_textarea()` in `form()`. Settings option sanitize callbacks:
  `sanitize_text_field` for keys, `esc_url_raw` for base URLs.
- Use `__()` / `esc_html_e()` for user-facing strings, text domain `'chatwp'`.
- Don't put provider credentials on the widget instance — they belong on the
  settings page as site-wide options, resolved by the provider classes via
  `get_option()`.

## Distribution note

The README tells users they can install from either the raw `chatwp/` folder
or `ChatWP.zip`. If you modify anything under `chatwp/`, remember `ChatWP.zip`
is a separate, manually-maintained copy — there is no script that
regenerates it, so update the zip too if you want the two install paths to
stay in sync (`cd chatwp && zip -r ../ChatWP.zip . -x '.*'` from the repo's
`chatwp/` directory, run from the parent so the zip contains a `chatwp/` root).

## Known quirks / history

- v1 of this plugin called OpenAI's legacy Completions API
  (`text-davinci-003`, since deprecated/retired) and cast `temperature`,
  `frequency_penalty`, and `presence_penalty` with `intval()` even though
  they're meant to be floats — a latent bug that silently truncated them to
  `0` or `1`. v2 (the current multi-provider architecture) moved to Chat
  Completions-style APIs across all providers and casts these params with
  `(float)`/`(int)` correctly in each provider class.
- Widget instances saved under v1 (bare `prompt`/`model`/`temperature`
  fields, no `provider` key) are **not** auto-migrated — `update()` defaults
  a missing/invalid `provider` to `'openai'`, but old widgets should be
  reopened and resaved to pick up the new fields cleanly.
