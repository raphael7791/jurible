/**
 * Voir plus de commentaires - affiche 5 par 5
 */
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        var list = document.querySelector('.comments-section .wp-block-comment-template');
        if (!list) return;

        var items = list.querySelectorAll(':scope > li');
        if (items.length <= 5) return;

        var btn = document.createElement('button');
        btn.className = 'comments-show-more';
        btn.textContent = 'Voir plus de commentaires (' + (items.length - 5) + ')';
        list.parentNode.insertBefore(btn, list.nextSibling);

        var visible = 5;

        btn.addEventListener('click', function () {
            visible += 5;

            if (visible >= items.length) {
                list.classList.add('is-expanded');
                btn.remove();
            } else {
                for (var i = 0; i < visible; i++) {
                    items[i].style.display = 'flex';
                }
                btn.textContent = 'Voir plus de commentaires (' + (items.length - visible) + ')';
            }
        });
    });
})();
