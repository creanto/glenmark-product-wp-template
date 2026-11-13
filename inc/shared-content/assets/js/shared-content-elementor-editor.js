(function () {
    const config = window.glenmarkSharedContentElementor;
    if (!config || !window.elementor || !window.elementor.hooks) {
        return;
    }

    let activeWidgetModel = null;

    elementor.hooks.addAction('panel/open_editor/widget/text-editor', function (panel, model) {
        activeWidgetModel = model;
    });

    function insertTextAtCursor(text) {
        const pageView = elementor.getPanelView ? elementor.getPanelView().getCurrentPageView() : null;
        const wrapper = pageView && pageView.el ? pageView.el.querySelector('.elementor-control-type-wysiwyg') : null;
        const textarea = wrapper ? wrapper.querySelector('textarea') : null;
        const editor = textarea && textarea.id && window.tinymce ? window.tinymce.get(textarea.id) : null;

        if (editor && !editor.isHidden()) {
            editor.focus();
            editor.execCommand('mceInsertContent', false, text);
            activeWidgetModel.setSetting('editor', editor.getContent());
            return;
        }

        if (textarea) {
            const value = textarea.value;
            const start = textarea.selectionStart || 0;
            const end = textarea.selectionEnd || 0;
            const next = value.slice(0, start) + text + value.slice(end);
            activeWidgetModel.setSetting('editor', next);
            window.requestAnimationFrame(function () {
                textarea.focus();
                textarea.setSelectionRange(start + text.length, start + text.length);
            });
            return;
        }

        activeWidgetModel.setSetting('editor', (activeWidgetModel.getSetting('editor') || '') + text);
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.glenmark-shared-field-insert');
        if (!button) {
            return;
        }

        event.preventDefault();
        if (!activeWidgetModel) {
            return;
        }

        const status = button.parentElement.querySelector('.glenmark-shared-field-insert-status');
        const selection = activeWidgetModel.getSetting('glenmark_shared_field_key') || '';
        const parts = selection.split('|');
        const key = parts[0] || '';
        const field = parts[1] || '';

        if (!key || !field) {
            status.textContent = config.labels.selectField;
            return;
        }

        const link = (config.linkFields || []).indexOf(field) !== -1 ? ' link="1"' : '';
        insertTextAtCursor('[gln_shared_content key="' + key + '" field="' + field + '"' + link + ']');
        status.textContent = config.labels.fieldInserted;
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