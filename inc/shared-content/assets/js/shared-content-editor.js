(function (wp) {
    if (!wp || !window.glenmarkSharedContent) {
        return;
    }

    const { createElement: el, useState } = wp.element;
    const { InnerBlocks } = wp.blockEditor;
    const { Button, Notice, SelectControl } = wp.components;
    const { PluginDocumentSettingPanel } = wp.editPost;
    const { registerPlugin } = wp.plugins;
    const blockName = 'glenmark/shared-content-snapshot';
    const config = window.glenmarkSharedContent;
    const labels = config.labels;

    wp.blocks.registerBlockType(blockName, {
        apiVersion: 2,
        title: 'Glenmark: sdílený obsah',
        description: 'Editovatelná kopie obsahu staženého z centrálního Content Hubu.',
        icon: 'admin-site-alt3',
        category: 'widgets',
        attributes: {
            sourceKey: { type: 'string' },
            sourceVersion: { type: 'string' },
            sourceTitle: { type: 'string' },
        },
        edit: function (props) {
            const title = props.attributes.sourceTitle || props.attributes.sourceKey || 'Glenmark';
            return el('div', { className: props.className }, [
                el('div', { key: 'label', className: 'gln-shared-content-editor-label' }, title),
                el(InnerBlocks, { key: 'inner-blocks' }),
            ]);
        },
        save: function (props) {
            return el('div', {
                className: props.className,
                'data-shared-content-key': props.attributes.sourceKey,
                'data-shared-content-version': props.attributes.sourceVersion,
            }, el(InnerBlocks.Content));
        },
    });

    function findSnapshot(blocks, key) {
        for (let index = 0; index < blocks.length; index += 1) {
            const block = blocks[index];
            if (block.name === blockName && block.attributes.sourceKey === key) {
                return block;
            }
            const nested = findSnapshot(block.innerBlocks || [], key);
            if (nested) {
                return nested;
            }
        }
        return null;
    }

    function collectSnapshots(blocks) {
        let snapshots = [];
        blocks.forEach(function (block) {
            if (block.name === blockName) {
                snapshots.push(block);
            }
            snapshots = snapshots.concat(collectSnapshots(block.innerBlocks || []));
        });
        return snapshots;
    }

    function SharedContentPanel() {
        const [items, setItems] = useState(config.items || []);
        const [selectedKey, setSelectedKey] = useState((config.items[0] || {}).key || '');
        const [notice, setNotice] = useState(null);
        const [isRefreshing, setIsRefreshing] = useState(false);
        const blocks = wp.data.useSelect(function (select) {
            return select('core/block-editor').getBlocks();
        }, []);
        const editorDispatch = wp.data.useDispatch('core/block-editor');

        function refreshItems() {
            setIsRefreshing(true);
            wp.apiFetch({
                url: config.refreshUrl,
                headers: { 'X-WP-Nonce': config.nonce },
            }).then(function (response) {
                const nextItems = response.items || [];
                setItems(nextItems);
                if (!nextItems.some(function (item) { return item.key === selectedKey; })) {
                    setSelectedKey((nextItems[0] || {}).key || '');
                }
                setNotice(nextItems.length ? null : { status: 'warning', message: labels.noItems });
                setIsRefreshing(false);
            }).catch(function () {
                setNotice({ status: 'error', message: labels.refreshFailed });
                setIsRefreshing(false);
            });
        }

        function insertSelectedItem() {
            const item = items.find(function (candidate) { return candidate.key === selectedKey; });
            if (!item || !item.snapshot_html) {
                setNotice({ status: 'warning', message: labels.noItems });
                return;
            }

            const previous = findSnapshot(blocks, item.key);
            if (previous && previous.attributes.sourceVersion === item.version) {
                setNotice({ status: 'info', message: labels.current + ': ' + item.title });
                return;
            }
            if (previous && !window.confirm(labels.confirmReplace)) {
                return;
            }

            const replacement = wp.blocks.createBlock(blockName, {
                sourceKey: item.key,
                sourceVersion: item.version,
                sourceTitle: item.title,
            }, wp.blocks.parse(item.snapshot_html));

            if (previous) {
                editorDispatch.replaceBlock(previous.clientId, [replacement]);
            } else {
                editorDispatch.insertBlocks([replacement]);
            }
            setNotice({ status: 'success', message: item.title + ' · ' + labels.current });
        }

        const itemOptions = [{ label: labels.selectItem, value: '' }].concat(items.map(function (item) {
            return { label: item.title + ' (' + item.key + ')', value: item.key };
        }));
        const snapshots = collectSnapshots(blocks);
        const statusRows = snapshots.map(function (block, index) {
            const current = items.find(function (item) { return item.key === block.attributes.sourceKey; });
            const isOutdated = current && current.version !== block.attributes.sourceVersion;
            const status = current ? (isOutdated ? labels.outdated : labels.current) : labels.outdated;
            return el('p', { key: block.clientId || index }, (block.attributes.sourceTitle || block.attributes.sourceKey) + ': ' + status);
        });

        return el(PluginDocumentSettingPanel, {
            name: 'glenmark-shared-content-panel',
            title: labels.panelTitle,
            className: 'glenmark-shared-content-panel',
        }, [
            el(SelectControl, {
                key: 'select',
                label: labels.selectItem,
                value: selectedKey,
                options: itemOptions,
                onChange: setSelectedKey,
            }),
            el(Button, {
                key: 'insert',
                variant: 'primary',
                disabled: !selectedKey || !items.length,
                onClick: insertSelectedItem,
            }, labels.insert),
            el(Button, {
                key: 'refresh',
                variant: 'secondary',
                isBusy: isRefreshing,
                disabled: isRefreshing,
                onClick: refreshItems,
                style: { marginTop: '8px' },
            }, labels.refresh),
            statusRows.length ? el('div', { key: 'statuses', className: 'gln-shared-content-statuses' }, statusRows) : null,
            notice ? el(Notice, {
                key: 'notice',
                status: notice.status,
                isDismissible: true,
                onRemove: function () { setNotice(null); },
            }, notice.message) : null,
        ]);
    }

    registerPlugin('glenmark-shared-content', {
        render: SharedContentPanel,
        icon: 'admin-site-alt3',
    });
}(window.wp));