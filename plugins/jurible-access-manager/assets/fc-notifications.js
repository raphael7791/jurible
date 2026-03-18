(function () {
    'use strict';

    // Only run on FC frame-template pages (not the full Vue portal)
    if (!document.querySelector('.fcom_wp_frame')) return;
    if (document.getElementById('fluent_com_portal')) return;

    var config = window.jamFcNotif;
    if (!config) return;

    var bellHolder = document.querySelector('.fcom_notification_holder');
    if (!bellHolder) return;

    var bellLink = bellHolder.querySelector('a');
    if (!bellLink) return;

    var popover = null;
    var isOpen = false;

    bellLink.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (isOpen) {
            close();
        } else {
            open();
        }
    });

    function open() {
        if (popover) popover.remove();

        popover = document.createElement('div');
        popover.className = 'jam-notif-popover';
        popover.innerHTML = '<div class="jam-notif-loading">Chargement…</div>';
        bellHolder.style.position = 'relative';
        bellHolder.appendChild(popover);
        isOpen = true;

        fetchNotifications();

        setTimeout(function () {
            document.addEventListener('click', onOutside);
        }, 10);
    }

    function close() {
        if (popover) popover.remove();
        popover = null;
        isOpen = false;
        document.removeEventListener('click', onOutside);
    }

    function onOutside(e) {
        if (popover && !popover.contains(e.target) && !bellLink.contains(e.target)) {
            close();
        }
    }

    function fetchNotifications() {
        fetch(config.restUrl + '/notifications/unread', {
            credentials: 'same-origin',
            headers: { 'X-WP-Nonce': config.nonce }
        })
            .then(function (r) { return r.json(); })
            .then(function (data) { render(data); })
            .catch(function () {
                if (popover) popover.innerHTML = '<div class="jam-notif-empty">Erreur de chargement</div>';
            });
    }

    function render(data) {
        if (!popover) return;

        var notifs = data.notifications || [];
        var count = data.unread_count || 0;
        var html = '';

        // Header
        html += '<div class="jam-notif-header">';
        html += '<span class="jam-notif-title">Notifications' + (count > 0 ? ' (' + count + ')' : '') + '</span>';
        if (count > 0) {
            html += '<button class="jam-notif-mark-all">Tout marquer lu</button>';
        }
        html += '</div>';

        // List
        if (notifs.length === 0) {
            html += '<div class="jam-notif-empty">Aucune nouvelle notification</div>';
        } else {
            html += '<div class="jam-notif-list">';
            var max = Math.min(notifs.length, 15);
            for (var i = 0; i < max; i++) {
                var n = notifs[i];
                var avatar = (n.xprofile && n.xprofile.avatar) || '';
                var content = n.content || '';
                var time = timeAgo(n.updated_at || n.created_at);

                html += '<div class="jam-notif-item" data-id="' + n.id + '">';
                if (avatar) {
                    html += '<img class="jam-notif-avatar" src="' + esc(avatar) + '" alt="" loading="lazy">';
                }
                html += '<div class="jam-notif-body">';
                html += '<div class="jam-notif-content">' + content + '</div>';
                html += '<div class="jam-notif-time">' + time + '</div>';
                html += '</div>';
                html += '</div>';
            }
            html += '</div>';
        }

        // Footer
        html += '<div class="jam-notif-footer">';
        html += '<a href="' + esc(bellLink.href) + '">Voir toutes les notifications</a>';
        html += '</div>';

        popover.innerHTML = html;

        // Mark all read
        var markBtn = popover.querySelector('.jam-notif-mark-all');
        if (markBtn) {
            markBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                markAllRead();
            });
        }

        // Click on item → go to notifications page
        var items = popover.querySelectorAll('.jam-notif-item');
        for (var j = 0; j < items.length; j++) {
            items[j].addEventListener('click', function () {
                var id = this.getAttribute('data-id');
                markRead(id);
                window.location.href = bellLink.href;
            });
        }
    }

    function markAllRead() {
        fetch(config.restUrl + '/notifications/mark-all-read', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-WP-Nonce': config.nonce,
                'Content-Type': 'application/json'
            }
        }).then(function () {
            var badge = bellHolder.querySelector('.fcomc_unread_badge');
            if (badge) badge.remove();
            close();
        });
    }

    function markRead(id) {
        fetch(config.restUrl + '/notifications/mark-read/' + id, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-WP-Nonce': config.nonce,
                'Content-Type': 'application/json'
            }
        });
    }

    function timeAgo(str) {
        if (!str) return '';
        var diff = Math.floor((Date.now() - new Date(str).getTime()) / 1000);
        if (diff < 60) return "à l'instant";
        if (diff < 3600) return Math.floor(diff / 60) + ' min';
        if (diff < 86400) return Math.floor(diff / 3600) + ' h';
        if (diff < 2592000) return Math.floor(diff / 86400) + ' j';
        return Math.floor(diff / 2592000) + ' mois';
    }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }
})();
