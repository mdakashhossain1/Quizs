/**
 * Generic AJAX behavior for admin index tables: filter-form submits,
 * pagination clicks, and delete-form submits all happen in place, without a
 * full page reload. Opt-in per page via `data-ajax-filter` / `data-ajax-delete`
 * on forms and a `#ajax-table` container whose partial re-render replaces it
 * wholesale (see `resources/views/admin/*/index.blade.php` + `_table.blade.php`
 * pairs). Server-side, the same controller `index()` returns just that
 * partial when `$request->ajax()` is true — see e.g. CategoryController.
 */
(function () {
    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function swapTable(html) {
        var wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        var newTable = wrapper.firstElementChild;
        var oldTable = document.getElementById('ajax-table');
        if (!oldTable || !newTable) return;

        oldTable.replaceWith(newTable);

        var countLabel = document.getElementById('ajax-count-label');
        if (countLabel && newTable.dataset.countText) {
            countLabel.textContent = newTable.dataset.countText;
        }

        bindPaginationLinks();
    }

    function loadUrl(url, pushState) {
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) {
                if (!response.ok) throw new Error('Request failed');
                return response.text();
            })
            .then(function (html) {
                swapTable(html);
                if (pushState !== false) {
                    history.pushState({ ajaxTable: true }, '', url);
                }
            })
            .catch(function () {
                // Fall back to a real navigation rather than leaving the
                // table stuck if the AJAX request itself fails.
                window.location = url;
            });
    }

    function bindPaginationLinks() {
        var table = document.getElementById('ajax-table');
        if (!table) return;
        table.querySelectorAll('nav a[href]').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                loadUrl(link.getAttribute('href'));
            });
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;

        if (form.matches('form[data-ajax-filter]')) {
            e.preventDefault();
            var params = new URLSearchParams(new FormData(form)).toString();
            var url = form.getAttribute('action') + (params ? '?' + params : '');
            loadUrl(url);
            return;
        }

        if (form.matches('form[data-ajax-delete]')) {
            e.preventDefault();
            var message = form.getAttribute('data-confirm') || 'Are you sure?';
            if (!window.confirm(message)) return;

            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('Delete failed');
                    loadUrl(window.location.href, false);
                })
                .catch(function () {
                    alert('Delete failed. Please try again.');
                });
        }
    });

    window.addEventListener('popstate', function () {
        loadUrl(window.location.href, false);
    });

    document.addEventListener('DOMContentLoaded', bindPaginationLinks);
})();
