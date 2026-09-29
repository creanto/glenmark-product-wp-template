(function ($, customize) {
    'use strict';

    if (!customize) {
        return;
    }

    function getEditorId($textarea) {
        return $textarea.attr('id');
    }

    function syncTextarea($textarea, content) {
        $textarea.val(content).trigger('input').trigger('change');
    }

    function syncEditorContent(editor) {
        var $textarea = $('#' + editor.id);

        if (!$textarea.length) {
            return;
        }

        syncTextarea($textarea, editor.getContent());
    }

    function initializeEditor(textarea) {
        var $textarea = $(textarea);
        var editorId = getEditorId($textarea);

        if (!editorId || $textarea.data('webrevRichTextReady')) {
            return;
        }

        if (!window.wp || !window.wp.editor || !window.wp.editor.initialize) {
            return;
        }

        $textarea.data('webrevRichTextReady', true);

        window.wp.editor.initialize(editorId, {
            mediaButtons: false,
            tinymce: {
                wpautop: true,
                toolbar1: 'bold italic underline | bullist numlist | link unlink | undo redo',
                toolbar2: ''
            },
            quicktags: true
        });
    }

    $(document).on('input change', 'textarea[data-webrev-rich-text="1"]', function () {
        var settingId = $(this).data('customizeSettingLink');

        if (settingId && customize.has(settingId)) {
            customize(settingId).set(this.value);
        }
    });

    $(document).on('tinymce-editor-init', function (event, editor) {
        if (!$('#' + editor.id).is('textarea[data-webrev-rich-text="1"]')) {
            return;
        }

        editor.on('change keyup undo redo input paste', function () {
            syncEditorContent(editor);
        });
    });

    function initializeEditors() {
        $('textarea[data-webrev-rich-text="1"]').each(function () {
            initializeEditor(this);
        });
    }

    $(initializeEditors);
    customize.bind('ready', initializeEditors);
}(jQuery, window.wp && window.wp.customize));