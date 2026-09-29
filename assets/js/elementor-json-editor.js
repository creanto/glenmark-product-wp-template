(function ($) {
    'use strict';

    var editorInstance = null;
    var currentPostId = null;
    var currentJson = '';
    var hasUnsavedChanges = false;
    var backupItems = [];

    function setStatus(message, type) {
        var $status = $('#webrev-editor-status');
        if (!$status.length) {
            return;
        }

        $status
            .removeClass('success error warning')
            .addClass(type || 'warning')
            .text(message);
    }

    function stripTrailingComma(text) {
        return text.replace(/,\s*([}\]])/g, '$1');
    }

    function getEditorValue() {
        if (!editorInstance || !editorInstance.codemirror) {
            return $('#webrev-elementor-json-editor').val() || '';
        }

        return editorInstance.codemirror.getValue();
    }

    function setEditorValue(value) {
        if (!editorInstance || !editorInstance.codemirror) {
            $('#webrev-elementor-json-editor').val(value);
            return;
        }

        editorInstance.codemirror.setValue(value);
        editorInstance.codemirror.clearHistory();
    }

    function markDirty() {
        hasUnsavedChanges = true;
        $('#webrev-save-json').prop('disabled', false);
        setStatus('Neuložené změny', 'warning');
    }

    function markClean() {
        hasUnsavedChanges = false;
        $('#webrev-save-json').prop('disabled', false);
        setStatus('Uložené', 'success');
    }

    function updateDirtyState() {
        var content = getEditorValue();
        hasUnsavedChanges = content !== currentJson;
        $('#webrev-save-json').prop('disabled', !hasUnsavedChanges);
    }

    function buildStructureTree(data, depth) {
        depth = depth || 0;
        if (!Array.isArray(data)) {
            return '<div class="wr-tree-empty">Žádná data</div>';
        }

        var html = '<ul class="wr-tree">';

        data.forEach(function (item) {
            if (!item || typeof item !== 'object') {
                return;
            }

            var id = item.id || 'unknown';
            var type = item.elType || item.widgetType || 'element';
            var label = type;
            var title = item.settings && item.settings.heading && item.settings.heading ? item.settings.heading : '';

            if (item.widgetType) {
                label = item.widgetType;
                if (item.settings && item.settings.text) {
                    title = item.settings.text;
                }
            }

            if (item.elType === 'widget' && item.widgetType === 'heading' && item.settings && item.settings.heading) {
                title = item.settings.heading;
            }

            if (item.elType === 'widget' && item.widgetType === 'button' && item.settings && item.settings.text) {
                title = item.settings.text;
            }

            if (item.elType === 'widget' && item.widgetType === 'image' && item.settings && item.settings.image && item.settings.image.url) {
                title = item.settings.image.url;
            }

            if (title) {
                label += ': ' + String(title).replace(/<[^>]*>/g, '').slice(0, 32);
            }

            html += '<li class="wr-tree-item" data-element-id="' + id + '">';
            html += '<button type="button" class="wr-tree-node">' + $('<div>').text(label + ' #' + id).html() + '</button>';

            if (Array.isArray(item.elements) && item.elements.length) {
                html += buildStructureTree(item.elements, depth + 1);
            }

            html += '</li>';
        });

        html += '</ul>';
        return html;
    }

    function findElementIdInJson(targetId) {
        var jsonText = getEditorValue();
        var index = jsonText.indexOf('"id": "' + targetId + '"');

        if (index === -1) {
            index = jsonText.indexOf('"id":' + targetId);
        }

        if (index === -1) {
            return false;
        }

        var editor = editorInstance && editorInstance.codemirror ? editorInstance.codemirror : null;
        if (!editor) {
            return false;
        }

        var line = jsonText.slice(0, index).split('\n').length;
        var ch = index - jsonText.lastIndexOf('\n', index) - 1;
        editor.focus();
        editor.setCursor(line - 1, ch);
        editor.scrollIntoView({line: line - 1, ch: ch}, 250);
        return true;
    }

    function formatJson() {
        try {
            var value = getEditorValue();
            if (!value.trim()) {
                return;
            }
            var parsed = JSON.parse(value);
            setEditorValue(JSON.stringify(parsed, null, 2));
            currentJson = getEditorValue();
            setStatus('JSON formátován', 'success');
            updateDirtyState();
        } catch (error) {
            showJsonError(error);
        }
    }

    function minifyJson() {
        try {
            var value = getEditorValue();
            if (!value.trim()) {
                return;
            }
            var parsed = JSON.parse(value);
            setEditorValue(JSON.stringify(parsed));
            currentJson = getEditorValue();
            setStatus('JSON minified', 'success');
            updateDirtyState();
        } catch (error) {
            showJsonError(error);
        }
    }

    function showJsonError(error) {
        var message = 'Neplatný JSON';
        if (error && error.message) {
            message = error.message;
        }

        var match = message.match(/position\s+(\d+)/i);
        var line = 1;
        var column = 1;
        if (match && match[1]) {
            var position = parseInt(match[1], 10);
            var text = getEditorValue();
            var prefix = text.slice(0, position);
            line = prefix.split('\n').length;
            var lastNewline = prefix.lastIndexOf('\n');
            column = lastNewline === -1 ? position + 1 : position - lastNewline;
            message = message + ' (řádek ' + line + ', sloupec ' + column + ')';
        }

        setStatus(message, 'error');
    }

    function updateStructureTree(data) {
        if (!data || !Array.isArray(data)) {
            data = [];
        }

        var html = buildStructureTree(data);
        $('#webrev-structure-tree').html(html);

        $('#webrev-structure-tree .wr-tree-node').on('click', function () {
            var id = $(this).closest('.wr-tree-item').data('elementId');
            if (id) {
                findElementIdInJson(String(id));
            }
        });
    }

    function handleDocumentResponse(payload) {
        currentPostId = payload.id;
        currentJson = JSON.stringify(payload.data || [], null, 2);

        $('#webrev-document-title').text(payload.title || '—');
        $('#webrev-document-id').text(payload.id || '—');
        $('#webrev-document-type').text(payload.post_type || '—');
        $('#webrev-document-status').text(payload.status_label || payload.status || '—');
        $('#webrev-document-elementor').text(payload.elementor ? 'ano' : 'ne');

        $('#webrev-open-page').attr('href', payload.frontend_url || '#');
        $('#webrev-open-elementor').attr('href', payload.editor_url || '#');

        setEditorValue(currentJson);
        updateStructureTree(payload.data || []);
        updateDirtyState();

        if (payload.data && payload.data.length === 0) {
            setStatus('Elementor data jsou prázdná nebo neexistují.', 'warning');
        } else {
            setStatus('Data načtena.', 'success');
        }
    }

    function fetchDocument(postId) {
        if (!postId) {
            return;
        }

        setStatus('Načítám Elementor JSON…', 'warning');

        $.ajax({
            url: webrevElementorJsonEditor.restRoot + '/elementor-documents/' + postId,
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', webrevElementorJsonEditor.nonce);
            },
            success: function (response) {
                handleDocumentResponse(response);
            },
            error: function (xhr) {
                var message = 'Nepodařilo se načíst data.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                setStatus(message, 'error');
            }
        });
    }

    function saveDocument() {
        if (!currentPostId) {
            setStatus('Vyberte nejdřív stránku.', 'warning');
            return;
        }

        var value = getEditorValue();

        try {
            JSON.parse(value);
        } catch (error) {
            showJsonError(error);
            return;
        }

        $.ajax({
            url: webrevElementorJsonEditor.restRoot + '/elementor-documents/' + currentPostId + '/save',
            method: 'POST',
            contentType: 'application/json',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', webrevElementorJsonEditor.nonce);
            },
            data: JSON.stringify({
                json: value
            }),
            success: function (response) {
                currentJson = value;
                markClean();
                setStatus(response && response.message ? response.message : 'Uloženo.', 'success');
                fetchDocument(currentPostId);
            },
            error: function (xhr) {
                var message = 'Uložení selhalo.';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                setStatus(message, 'error');
            }
        });
    }

    function restoreLastVersion() {
        if (!currentPostId) {
            setStatus('Vyberte stránku.', 'warning');
            return;
        }

        var newIndex = 0;
        $.ajax({
            url: webrevElementorJsonEditor.restRoot + '/elementor-documents/' + currentPostId + '/backups',
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', webrevElementorJsonEditor.nonce);
            },
            success: function (response) {
                var backups = response && response.backups ? response.backups : [];
                if (!backups.length) {
                    setStatus('Nemáte žádné zálohy.', 'warning');
                    return;
                }

                if (window.confirm('Obnovit předchozí verzi?')) {
                    $.ajax({
                        url: webrevElementorJsonEditor.restRoot + '/elementor-documents/' + currentPostId + '/restore',
                        method: 'POST',
                        contentType: 'application/json',
                        beforeSend: function (xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', webrevElementorJsonEditor.nonce);
                        },
                        data: JSON.stringify({ backup_index: newIndex }),
                        success: function (restoreResponse) {
                            setStatus(restoreResponse && restoreResponse.message ? restoreResponse.message : 'Verze obnovena.', 'success');
                            fetchDocument(currentPostId);
                        },
                        error: function (xhr) {
                            var message = 'Obnovení selhalo.';
                            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            setStatus(message, 'error');
                        }
                    });
                }
            },
            error: function (xhr) {
                setStatus('Nepodařilo se načíst zálohy.', 'error');
            }
        });
    }

    function initializeEditor() {
        var textarea = document.getElementById('webrev-elementor-json-editor');
        if (!textarea) {
            return;
        }

        editorInstance = wp.codeEditor.initialize(textarea, {
            codemirror: {
                mode: 'application/json',
                theme: 'default',
                lineNumbers: true,
                lineWrapping: false,
                tabSize: 2,
                indentUnit: 2,
                indentWithTabs: false,
                matchBrackets: true,
                autoCloseBrackets: true,
                styleActiveLine: true,
                foldGutter: true,
                gutters: ['CodeMirror-linenumbers'],
                extraKeys: {
                    Tab: function (cm) {
                        if (cm.somethingSelected()) {
                            cm.indentSelection('add');
                        } else {
                            cm.replaceSelection('  ');
                        }
                    },
                    'Ctrl-F': 'findPersistent',
                    'Cmd-F': 'findPersistent',
                    'Ctrl-H': function (cm) {
                        cm.execCommand('replace');
                    },
                    'Cmd-H': function (cm) {
                        cm.execCommand('replace');
                    }
                }
            }
        });

        $(textarea).on('input propertychange', function () {
            markDirty();
        });

        $('#webrev-save-json').on('click', saveDocument);
        $('#webrev-format-json').on('click', formatJson);
        $('#webrev-minify-json').on('click', minifyJson);
        $('#webrev-reload-json').on('click', function () {
            if (currentPostId) {
                fetchDocument(currentPostId);
            }
        });
        $('#webrev-search-json').on('click', function () {
            if (editorInstance && editorInstance.codemirror) {
                editorInstance.codemirror.execCommand('findPersistent');
            }
        });
        $('#webrev-search-replace-json').on('click', function () {
            if (editorInstance && editorInstance.codemirror) {
                editorInstance.codemirror.execCommand('replace');
            }
        });
        $('#webrev-restore-last-version').on('click', restoreLastVersion);
        $('#webrev-find-element-id').on('click', function () {
            var value = $('#webrev-element-id-input').val();
            if (value) {
                findElementIdInJson(String(value).trim());
            }
        });

        $(window).on('beforeunload', function (event) {
            if (hasUnsavedChanges) {
                event.preventDefault();
                event.returnValue = '';
            }
        });

        $(document).on('keydown', function (event) {
            var isMeta = event.metaKey || event.ctrlKey;
            var isS = event.key && event.key.toLowerCase() === 's';
            if (isMeta && isS) {
                event.preventDefault();
                saveDocument();
            }
        });

        function refreshPostSelect(searchValue) {
            var value = (searchValue || '').trim();

            if (value.length > 0 && value.length < 2) {
                return;
            }

            $.ajax({
                url: webrevElementorJsonEditor.restRoot + '/elementor-documents',
                method: 'GET',
                data: { search: value },
                beforeSend: function (xhr) {
                    xhr.setRequestHeader('X-WP-Nonce', webrevElementorJsonEditor.nonce);
                },
                success: function (response) {
                    var items = response && response.items ? response.items : [];
                    var $select = $('#webrev-elementor-post-select');
                    $select.empty();
                    $select.append('<option value="">Vyberte příspěvek</option>');
                    items.forEach(function (item) {
                        $select.append('<option value="' + item.id + '">' + item.title + ' (ID: ' + item.id + ', ' + item.post_type + ', ' + item.status_label + ')</option>');
                    });
                }
            });
        }

        $('#webrev-elementor-post-search').on('input', function () {
            refreshPostSelect($(this).val());
        });

        refreshPostSelect('');

        $('#webrev-elementor-post-select').on('change', function () {
            var value = $(this).val();
            if (value) {
                fetchDocument(value);
            }
        });
    }

    $(document).ready(function () {
        initializeEditor();
    });
})(jQuery);
