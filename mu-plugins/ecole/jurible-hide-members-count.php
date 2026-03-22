<?php
/**
 * Plugin Name: Jurible — Masquer nombre apprenants
 * Description: Cache le compteur de membres/apprenants partout dans Fluent Community
 */

// CSS pour cacher visuellement le compteur
add_action('fluent_community/portal_head', function() {
    ?>
    <style id="jurible-hide-members">
    /* Hide members/students count on space cards and course cards */
    .fcom_space_counts,
    .fcom_space_card .fcom_space_counts,
    .fcom_course_card .fcom_space_counts {
        display: none !important;
    }
    </style>
    <script id="jurible-hide-members-js">
    (function() {
        function hideMembersCount() {
            // Target any element containing "apprenants" or "members" count text
            document.querySelectorAll('.fcom_space_card, .fcom_course_card, [class*=space_card], [class*=course_card]').forEach(function(card) {
                card.querySelectorAll('span, p, div, small').forEach(function(el) {
                    var text = (el.textContent || '').trim();
                    if (/\d+\s*(apprenants?|members?|étudiants?|inscrits?)/i.test(text)) {
                        el.style.display = 'none';
                    }
                });
            });
        }
        var observer = new MutationObserver(hideMembersCount);
        observer.observe(document.documentElement, {childList: true, subtree: true});
        hideMembersCount();
    })();
    </script>
    <?php
});

// Filter API response to remove members_count
add_filter('fluent_community/space_data', function($space) {
    if (isset($space['members_count'])) {
        $space['members_count'] = 0;
    }
    return $space;
});

add_filter('fluent_community/spaces', function($spaces) {
    if (is_array($spaces)) {
        foreach ($spaces as &$space) {
            if (isset($space['members_count'])) {
                $space['members_count'] = 0;
            }
        }
    }
    return $spaces;
});
