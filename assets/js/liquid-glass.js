/**
 * Liquid Glass Effect
 * SVG filter + CSS layered glass + mouse tracking displacement
 * Inspired by https://github.com/shuding/liquid-glass
 *
 * Usage: Add `data-liquid-glass` to any element to apply the effect.
 * The element should have position: relative (or be a positioning context).
 * Compatible with glass-card, glass, navbar, and other elements.
 */

(function () {
    'use strict';

    var filterId = 'liquid_glass_filter';
    var mapId = 'liquid_glass_map';
    var initialized = false;
    var allTargets = [];
    var animId = null;

    // --- SVG filter helpers ---
    var mouseX = 0.5, mouseY = 0.5;
    var targetX = 0.5, targetY = 0.5;

    function len(x, y) { return Math.sqrt(x * x + y * y); }

    function smoothStep(a, b, t) {
        t = Math.max(0, Math.min(1, (t - a) / (b - a)));
        return t * t * (3 - 2 * t);
    }

    function rrectSDF(px, py, w, h, r) {
        var qx = Math.abs(px) - w + r;
        var qy = Math.abs(py) - h + r;
        return Math.min(Math.max(qx, qy), 0) + len(Math.max(qx, 0), Math.max(qy, 0)) - r;
    }

    // --- Setup global SVG filter ---
    function setupFilter() {
        if (document.getElementById(filterId)) return;

        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.id = 'liquid_glass_svg';
        svg.style.cssText = 'position:fixed;top:0;left:0;width:0;height:0;pointer-events:none;z-index:-1;';

        var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
        var filter = document.createElementNS('http://www.w3.org/2000/svg', 'filter');
        filter.id = filterId;
        filter.setAttribute('colorInterpolationFilters', 'sRGB');
        filter.setAttribute('x', '-20%');
        filter.setAttribute('y', '-20%');
        filter.setAttribute('width', '140%');
        filter.setAttribute('height', '140%');

        var feImg = document.createElementNS('http://www.w3.org/2000/svg', 'feImage');
        feImg.id = mapId;
        feImg.setAttribute('preserveAspectRatio', 'none');

        var feDisp = document.createElementNS('http://www.w3.org/2000/svg', 'feDisplacementMap');
        feDisp.setAttribute('in', 'SourceGraphic');
        feDisp.setAttribute('in2', mapId);
        feDisp.setAttribute('xChannelSelector', 'R');
        feDisp.setAttribute('yChannelSelector', 'G');
        feDisp.setAttribute('scale', '60');

        filter.appendChild(feImg);
        filter.appendChild(feDisp);
        defs.appendChild(filter);
        svg.appendChild(defs);
        document.body.appendChild(svg);
    }

    // --- Displacement map rendering ---
    var canvas = document.createElement('canvas');
    canvas.style.display = 'none';
    var ctx = canvas.getContext('2d');

    function renderMap() {
        var w = 128, h = 64;
        canvas.width = w;
        canvas.height = h;
        var data = new Uint8ClampedArray(w * h * 4);
        var maxScale = 0;
        var vals = [];

        for (var i = 0; i < data.length; i += 4) {
            var px = (i / 4) % w;
            var py = 0 | (i / 4 / w);
            var ux = px / w, uy = py / h;
            var ix = ux - 0.5, iy = uy - 0.5;
            var mx = (mouseX - 0.5) * 0.3;
            var my = (mouseY - 0.5) * 0.3;

            var d = rrectSDF(ix, iy, 0.45, 0.4, 0.5);
            var edge = smoothStep(0.5, 0, Math.abs(d) - 0.1);
            var dist = len(ix - mx * 1.5, iy - my * 1.5);
            var infl = Math.max(0, 1 - dist * 2.5);
            var disp = edge * 0.1 + infl * 0.06;

            var ox = ix + mx * disp;
            var oy = iy + my * disp;
            var ripple = Math.max(0, 1 - dist * 3) * 0.03;
            ox += (ix - mx) * ripple;
            oy += (iy - my) * ripple;

            var tx = (ox + 0.5) * w - px;
            var ty = (oy + 0.5) * h - py;
            maxScale = Math.max(maxScale, Math.abs(tx), Math.abs(ty));
            vals.push(tx, ty);
        }

        maxScale = Math.max(maxScale * 0.5, 1);
        var idx = 0;
        for (var j = 0; j < data.length; j += 4) {
            data[j] = ((vals[idx++] / maxScale) + 0.5) * 255;
            data[j + 1] = ((vals[idx++] / maxScale) + 0.5) * 255;
            data[j + 2] = 0;
            data[j + 3] = 255;
        }

        ctx.putImageData(new ImageData(data, w, h), 0, 0);

        var feImg = document.getElementById(mapId);
        var feDisp = document.querySelector('#' + filterId + ' feDisplacementMap');
        if (feImg) {
            feImg.setAttributeNS('http://www.w3.org/1999/xlink', 'href', canvas.toDataURL());
            feImg.setAttribute('width', '' + w);
            feImg.setAttribute('height', '' + h);
        }
        if (feDisp) feDisp.setAttribute('scale', '' + (maxScale * 0.8));
    }

    // --- Animation loop ---
    function loop() {
        var dx = targetX - mouseX;
        var dy = targetY - mouseY;
        mouseX += dx * 0.12;
        mouseY += dy * 0.12;
        if (Math.abs(dx) > 0.001 || Math.abs(dy) > 0.001) renderMap();
        animId = requestAnimationFrame(loop);
    }

    // --- Apply liquid glass layers to an element ---
    function applyTo(el) {
        if (el._lgDone) return;
        el._lgDone = true;
        allTargets.push(el);

        // Ensure element is a positioning context
        var pos = window.getComputedStyle(el).position;
        if (pos === 'static') el.style.position = 'relative';

        // Read border-radius from computed style or use default
        var r = window.getComputedStyle(el).borderRadius;
        // If multiple values (e.g. "10px 10px 0 0"), take the first
        r = r ? r.split(' ')[0] : '21px';
        var rv = parseInt(r) || 21;
        var ri = Math.max(rv - 5, 0);

        // Create glass layers as children.
        // We prepend in reverse order so the final DOM order is:
        // outer (0), cover (2), sharp (3), reflect (2), then original content.
        // With relative z-index (no explicit z-index on content),
        // later siblings render on top → glass behind content.
        var layers = [
            { name: 'reflect', z: 1, inset: '1px' },
            { name: 'sharp', z: 1, inset: '0' },
            { name: 'cover', z: 1, inset: '0' },
            { name: 'outer', z: 1, inset: '0' }
        ];
        layers.forEach(function (layer) {
            var div = document.createElement('div');
            div.className = 'liquid_glass-' + layer.name;
            div.style.cssText = [
                'position:absolute',
                'inset:' + layer.inset,
                'pointer-events:none',
                'z-index:' + layer.z,
                'border-radius:' + r,
                'overflow:hidden'
            ].join(';') + ';';

            if (layer.name === 'outer') {
                div.style.backdropFilter = 'url(#' + filterId + ')';
                div.style.webkitBackdropFilter = div.style.backdropFilter;
                // Mask: exclude inner area so filter only applies to edges
                var innerPad = Math.max(rv - 2, 1);
                div.style.maskImage = [
                    "url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"100%\" height=\"100%\"><rect x=\"0\" y=\"0\" width=\"100%\" height=\"100%\" rx=\"" + rv + "\" ry=\"" + rv + "\" fill=\"white\"/></svg>')",
                    "url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"100%\" height=\"100%\"><rect x=\"" + innerPad + "\" y=\"" + innerPad + "\" width=\"calc(100% - " + (innerPad * 2) + "px)\" height=\"calc(100% - " + (innerPad * 2) + "px)\" rx=\"" + ri + "\" ry=\"" + ri + "\" fill=\"white\"/></svg>')"
                ].join(', ');
                div.style.maskComposite = 'exclude';
                // Safari uses 'xor' for the exclude operation
                div.style.webkitMaskComposite = 'xor';
            }

            if (layer.name === 'cover') {
                div.style.background = 'rgba(255,255,255,0.08)';
                div.style.backdropFilter = 'blur(2px)';
                div.style.webkitBackdropFilter = div.style.backdropFilter;
            }

            if (layer.name === 'sharp') {
                div.style.boxShadow = 'inset 1px 1px 0 0 rgba(255,255,255,0.5), inset -1px -1px 0 0 rgba(255,255,255,0.6)';
            }

            if (layer.name === 'reflect') {
                div.style.boxShadow = 'inset 2px 2px 6px 2px rgba(255,255,255,0.2), inset -2px -2px 4px -1px rgba(255,255,255,0.2)';
            }

            el.insertBefore(div, el.firstChild);
        });
    }

    // --- Init ---
    function init() {
        if (initialized) return;
        initialized = true;

        setupFilter();
        document.querySelectorAll('[data-liquid-glass]').forEach(applyTo);

        document.addEventListener('mousemove', function (e) {
            targetX = e.clientX / window.innerWidth;
            targetY = e.clientY / window.innerHeight;
        });

        renderMap();
        animId = requestAnimationFrame(loop);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.initLiquidGlass = init;
})();