/**
 * Catalogue Matieres - Tabs functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    const catalogueSections = document.querySelectorAll('.section-catalogue');

    catalogueSections.forEach(function(section) {
        const tabs = section.querySelectorAll('.section-catalogue__tab');
        const panels = section.querySelectorAll('.section-catalogue__panel');

        tabs.forEach(function(tab) {
            tab.addEventListener('click', function() {
                const targetPanel = this.dataset.tab;

                // Reset all tabs
                tabs.forEach(function(t) {
                    t.classList.remove('active');
                });

                // Activate clicked tab
                this.classList.add('active');

                // Reset all panels
                panels.forEach(function(p) {
                    p.classList.remove('active');
                });

                // Activate target panel
                const panel = section.querySelector('[data-panel="' + targetPanel + '"]');
                if (panel) {
                    panel.classList.add('active');
                }
            });
        });
    });
});
