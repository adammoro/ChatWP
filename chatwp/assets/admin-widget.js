/**
 * Shows/hides provider-specific fields in the ChatWP widget form based on
 * the selected LLM provider. Uses a MutationObserver (rather than the
 * classic-only 'widget-added'/'widget-updated' events) so it also works
 * when the widget form is loaded into the block-based Widgets screen or
 * Customizer via AJAX.
 */
(function () {
    'use strict';

    function applyProviderVisibility(form) {
        var select = form.querySelector('.chatwp-provider-select');
        if (!select) {
            return;
        }
        var provider = select.value;

        form.querySelectorAll('[data-providers]').forEach(function (el) {
            var providers = el.getAttribute('data-providers').split(',');
            el.style.display = providers.indexOf(provider) !== -1 ? '' : 'none';
        });

        form.querySelectorAll('.chatwp-model-hint').forEach(function (el) {
            el.style.display = el.getAttribute('data-provider') === provider ? '' : 'none';
        });
    }

    function initForm(form) {
        if (form.dataset.chatwpInit) {
            return;
        }
        form.dataset.chatwpInit = '1';

        var select = form.querySelector('.chatwp-provider-select');
        if (select) {
            select.addEventListener('change', function () {
                applyProviderVisibility(form);
            });
        }
        applyProviderVisibility(form);
    }

    function scan(root) {
        (root || document).querySelectorAll('.chatwp-widget-form').forEach(initForm);
    }

    scan();

    var observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType !== 1) {
                    return;
                }
                if (node.matches && node.matches('.chatwp-widget-form')) {
                    initForm(node);
                } else if (node.querySelectorAll) {
                    scan(node);
                }
            });
        });
    });
    observer.observe(document.body, { childList: true, subtree: true });
})();
