(function () {
    const elements = document.querySelectorAll('[data-shared-content-refresh]');

    elements.forEach(function (element) {
        const endpoint = element.getAttribute('data-shared-content-refresh');
        if (!endpoint) {
            return;
        }

        fetch(endpoint, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json' },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Shared content refresh failed');
                }
                return response.json();
            })
            .then(function (payload) {
                if (payload && typeof payload.content === 'string') {
                    element.innerHTML = payload.content;
                }
            })
            .catch(function () {
                // Keep the server-rendered fallback when the Hub is unavailable.
            });
    });
}());