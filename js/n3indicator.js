/**
 * N3 Indicator — GLPI 11.0.x
 * Colores dinámicos por grupo desde configuración
 */
(function () {
    'use strict';

    var DEFAULT_COLOR  = '#d63031';
    var bannerInserted = false;
    var colorMapLoaded = false;

    function hexToRgba(hex, alpha) {
        var r = parseInt(hex.slice(1,3), 16);
        var g = parseInt(hex.slice(3,5), 16);
        var b = parseInt(hex.slice(5,7), 16);
        return 'rgba(' + r + ',' + g + ',' + b + ',' + alpha + ')';
    }

    function loadColorMap(callback) {
        if (colorMapLoaded) { callback(); return; }

        if (window.N3_COLOR_MAP && window.N3_COLOR_MAP.length > 0) {
            colorMapLoaded = true;
            callback();
            return;
        }

        fetch('/plugins/n3indicator/front/config.php?n3action=colormap')
        .then(function(r) { return r.ok ? r.json() : null; })
        .then(function(data) {
            if (data && Array.isArray(data)) window.N3_COLOR_MAP = data;
            colorMapLoaded = true;
            callback();
        })
        .catch(function() { colorMapLoaded = true; callback(); });
    }

    function insertN3Banner() {
        if (bannerInserted) return;
        var script = document.querySelector('script[data-n3ticket]');
        if (!script) return;
        var container = document.getElementById('itil-object-container');
        if (!container) return;
        bannerInserted = true;

        var color = script.getAttribute('data-n3color') || DEFAULT_COLOR;

        var banner = document.createElement('div');
        banner.id  = 'n3indicator-banner';
        banner.className = 'n3indicator-banner';
        banner.setAttribute('role', 'alert');
        banner.style.borderLeftColor = color;
        banner.style.background = 'linear-gradient(135deg, ' + hexToRgba(color, 0.05) + ' 0%, ' + hexToRgba(color, 0.12) + ' 100%)';
        banner.innerHTML =
            '<span class="n3indicator-icon">🔴</span>' +
            '<div class="n3indicator-text">' +
                '<strong>Ticket N3 — URCIT SSMSO</strong>' +
                '<span class="n3indicator-subtitle">Derivado a soporte Nivel 3</span>' +
            '</div>' +
            '<span class="n3indicator-badge" style="background:' + color + '">N3</span>';

        container.insertBefore(banner, container.firstChild);
    }

    function getColorForRow(rowText) {
        var colorMap = window.N3_COLOR_MAP || [];
        var text = rowText.toLowerCase();
        for (var i = 0; i < colorMap.length; i++) {
            if (text.indexOf(colorMap[i].keyword) !== -1) return colorMap[i].color;
        }
        var defaults = ['infraestructura ssmso','redes y comunicaciones ssmso','ciberseguridad ssmso','urcit ssmso'];
        for (var j = 0; j < defaults.length; j++) {
            if (text.indexOf(defaults[j]) !== -1) return DEFAULT_COLOR;
        }
        return null;
    }

    function processRows() {
        document.querySelectorAll('table.search-results tbody tr').forEach(function(row) {
            if (row.dataset.n3done) return;
            var color = getColorForRow(row.textContent);
            if (!color) return;

            row.dataset.n3done = '1';

            // Solo badge — sin colorear la fila completa
            var link = row.querySelector('a[href]');
            if (link && !link.querySelector('.n3indicator-list-badge')) {
                var badge = document.createElement('span');
                badge.className = 'n3indicator-list-badge';
                badge.textContent = 'N3';
                badge.style.background = color;
                link.insertBefore(badge, link.firstChild);
            }
        });
    }

    function init() {
        insertN3Banner();
        loadColorMap(processRows);
    }

    var ticks = 0;
    var loop = setInterval(function() {
        init();
        ticks++;
        if (ticks >= 60) clearInterval(loop);
    }, 500);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    var observer = new MutationObserver(init);
    observer.observe(document.body, { childList: true, subtree: true });

})();
