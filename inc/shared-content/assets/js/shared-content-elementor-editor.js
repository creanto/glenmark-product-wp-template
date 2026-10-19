(function () {
    const config = window.glenmarkSharedContentElementor;
    if (!config || !window.elementor || !window.elementor.hooks) {
        return;
    }

    let activeWidgetModel = null;

    elementor.hooks.addAction('panel/open_editor/widget/text-editor', function (panel, model) {
        activeWidgetModel = model;
    });

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.glenmark-shared-template-import');
        if (!button) {
            return;
        }

        event.preventDefault();
        if (!activeWidgetModel) {
            return;
        }

        const key = activeWidgetModel.getSetting('glenmark_shared_template_key');
        const status = button.parentElement.querySelector('.glenmark-shared-template-import-status');
        if (!key) {
            status.textContent = config.labels.selectTemplate;
            return;
        }

        const currentContent = activeWidgetModel.getSetting('editor');
        if (currentContent && !window.confirm(config.labels.confirmReplace)) {
            return;
        }

        button.disabled = true;
        status.textContent = config.labels.loading;

        fetch(config.refreshUrl, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
                'X-WP-Nonce': config.nonce,
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Shared content request failed');
                }
                return response.json();
            })
            .then(function (payload) {
                const item = (payload.items || []).find(function (candidate) {
                    return candidate.key === key;
                });
                if (!item || typeof item.snapshot_html !== 'string') {
                    throw new Error('Shared template is unavailable');
                }

                activeWidgetModel.setSetting('editor', item.snapshot_html);
                status.textContent = config.labels.inserted;
            })
            .catch(function () {
                status.textContent = config.labels.failed;
            })
            .finally(function () {
                button.disabled = false;
            });
    });
}());