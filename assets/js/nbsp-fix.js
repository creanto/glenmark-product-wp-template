/**
 * Doplní tvrdé (nezalomitelné) mezery za jednopísmenné až třípísmenné
 * předložky/spojky v nadpisech (h1-h6) a odstavcích (p), aby nezůstávaly
 * osamocené na konci řádku (sirotci).
 */
(function () {
    'use strict';

    // Předložky a spojky (1-3 písmena), za kterými se nesmí zalamovat.
    var WORDS = [
        'k', 's', 'v', 'z', 'o', 'u', 'i', 'a',
        'ke', 'se', 've', 'ze', 'na', 'do', 'od', 'po', 'za', 'ku', 'či', 'že',
        'ale', 'pro', 'nad', 'pod', 'bez', 'ani', 'jak', 'aby', 'ani', 'než'
    ];

    var SELECTOR = 'h1, h2, h3, h4, h5, h6, p';

    // Regex: hranice slova + jedno z výše uvedených slov (case-insensitive) + běžná mezera.
    var WORD_REGEX = new RegExp(
        '(^|[\\s(])(' + WORDS.join('|') + ')( )(?=\\S)',
        'gi'
    );

    function fixTextNode(text) {
        return text.replace(WORD_REGEX, '$1$2\u00A0');
    }

    function shouldSkip(node) {
        var tag = node.nodeName;
        return tag === 'SCRIPT' || tag === 'STYLE' || tag === 'TEXTAREA' || tag === 'CODE' || tag === 'PRE';
    }

    function processElement(el) {
        if (el.dataset.nbspFixed === '1') {
            return;
        }

        var walker = document.createTreeWalker(el, NodeFilter.SHOW_TEXT, {
            acceptNode: function (node) {
                if (!node.nodeValue || !node.nodeValue.trim()) {
                    return NodeFilter.FILTER_REJECT;
                }
                if (node.parentNode && shouldSkip(node.parentNode)) {
                    return NodeFilter.FILTER_REJECT;
                }
                return NodeFilter.FILTER_ACCEPT;
            }
        });

        var textNodes = [];
        var current;
        while ((current = walker.nextNode())) {
            textNodes.push(current);
        }

        textNodes.forEach(function (node) {
            var fixed = fixTextNode(node.nodeValue);
            if (fixed !== node.nodeValue) {
                node.nodeValue = fixed;
            }
        });

        el.dataset.nbspFixed = '1';
    }

    function run(root) {
        var scope = root || document;
        var elements = scope.querySelectorAll ? scope.querySelectorAll(SELECTOR) : [];
        elements.forEach ? elements.forEach(processElement) : Array.prototype.forEach.call(elements, processElement);
    }

    function scheduleIdle(fn) {
        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(fn, { timeout: 500 });
        } else {
            setTimeout(fn, 50);
        }
    }

    function init() {
        run(document);

        // Sleduj i dynamicky vložený obsah (např. AJAX, Elementor lazy-load).
        // childList bez attributes/characterData, aby observer nereagoval na animace/třídy.
        if ('MutationObserver' in window) {
            var pendingNodes = [];

            var observer = new MutationObserver(function (mutations) {
                var hasNew = false;

                mutations.forEach(function (mutation) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType !== 1) {
                            return;
                        }
                        pendingNodes.push(node);
                        hasNew = true;
                    });
                });

                if (!hasNew) {
                    return;
                }

                scheduleIdle(function () {
                    var nodes = pendingNodes;
                    pendingNodes = [];
                    nodes.forEach(function (node) {
                        if (node.matches && node.matches(SELECTOR)) {
                            processElement(node);
                        }
                        run(node);
                    });
                });
            });

            observer.observe(document.body, { childList: true, subtree: true });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
