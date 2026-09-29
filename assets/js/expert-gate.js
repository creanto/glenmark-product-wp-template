(function () {
    'use strict';

    function init() {
        var gate = document.querySelector('.wr-expert-gate');

        if (!gate) {
            return;
        }

        document.body.classList.add('wr-expert-gate-locked');

        var confirmButton = gate.querySelector('.wr-expert-gate__button--confirm');
        var declineButton = gate.querySelector('.wr-expert-gate__button--decline');

        function close() {
            gate.setAttribute('hidden', 'hidden');
            document.body.classList.remove('wr-expert-gate-locked');
        }

        if (confirmButton) {
            confirmButton.addEventListener('click', close);
            confirmButton.focus();
        }

        if (declineButton) {
            declineButton.addEventListener('click', function () {
                window.location.href = gate.getAttribute('data-decline-url') || '/';
            });
        }
    }

    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
