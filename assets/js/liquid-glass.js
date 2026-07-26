/**
 * Liquid Glass Effect — iOS 18 style refractive glass
 *
 * SVG displacement map + CSS backdrop-blur + mouse/touch tracking
 * Creates a refractive edge distortion that follows the cursor.
 *
 * Usage: Add `data-liquid-glass` to any element, or apply via JS.
 *   <div data-liquid-glass data-lg-elasticity="0.12" data-lg-scale="50">...</div>
 *
 * Attributes:
 *   data-lg-elasticity  — mouse tracking responsiveness (0–1, default 0.10)
 *   data-lg-scale       — displacement intensity (pixels, default 50)
 */
(function () {
    'use strict';

    /* ─── Configuration ─── */
    var FILTER_ID = 'lg-filter';
    var MAP_ID = 'lg-map';

    // Canvas resolution for the displacement map (keep modest for performance)
    var MAP_W = 192;
    var MAP_H = 96;

    /* ─── State ─── */
    var mouseX = 0.5, mouseY = 0.5;   // smoothed position
    var rawX = 0.5, rawY = 0.5;       // raw input position
    var targets = [];
    var initialized = false;
    var animId = null;

    /* ─── DOM refs (set once) ─── */
    var svgFilter = null;
    var feImg = null;
    var feDisp = null;

    /* ─── Canvas for displacement map ─── */
    var canvas = document.createElement('canvas');
    canvas.style.display = 'none';
    var ctx = canvas.getContext('2d');

    /* ─── Math helpers ─── */
    function len(x, y) { return Math.sqrt(x * x + y * y); }

    function clamp(v, min, max) { return Math.max(min, Math.min(max, v)); }

    function smoothstep(edge0, edge1, x) {
        var t = clamp((x - edge0) / (edge1 - edge0), 0, 1);
        return t * t * (3 - 2 * t);
    }

    // Signed distance function for a rounded rectangle
    // (px, py) are coordinates relative to center, (w, h) are half-dimensions, r is corner radius
    function rrectSDF(px, py, w, h, r) {
        var qx = Math.abs(px) - w + r;
        var qy = Math.abs(py) - h + r;
        return Math.min(Math.max(qx, qy), 0) + len(Math.max(qx, 0), Math.max(qy, 0)) - r;
    }

    /* ─── 1. Setup SVG filter ─── */
    function setupFilter() {
        if (document.getElementById(FILTER_ID)) return;

        var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.id = 'lg-svg';
        svg.style.cssText = 'position:fixed;top:0;left:0;width:0;height:0;pointer-events:none;z-index:-1;';

        var defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');

        var filter = document.createElementNS('http://www.w3.org/2000/svg', 'filter');
        filter.id = FILTER_ID;
        filter.setAttribute('color-interpolation-filters', 'sRGB');
        // Expand bounds so the displacement doesn't clip
        filter.setAttribute('x', '-50%');
        filter.setAttribute('y', '-50%');
        filter.setAttribute('width', '200%');
        filter.setAttribute('height', '200%');

        // Displacement source image (the canvas data URL)
        feImg = document.createElementNS('http://www.w3.org/2000/svg', 'feImage');
        feImg.id = MAP_ID;
        feImg.setAttribute('preserveAspectRatio', 'none');
        // Use standard 'href' (xlink:href is deprecated)
        feImg.setAttribute('href', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');
        feImg.setAttribute('width', '100%');
        feImg.setAttribute('height', '100%');

        // Displacement map fe
        feDisp = document.createElementNS('http://www.w3.org/2000/svg', 'feDisplacementMap');
        feDisp.setAttribute('in', 'SourceGraphic');
        feDisp.setAttribute('in2', MAP_ID);
        feDisp.setAttribute('xChannelSelector', 'R');
        feDisp.setAttribute('yChannelSelector', 'G');
        feDisp.setAttribute('scale', '40');

        filter.appendChild(feImg);
        filter.appendChild(feDisp);
        defs.appendChild(filter);
        svg.appendChild(defs);
        document.body.prepend(svg);
    }

    /* ─── 2. Render displacement map ─── */
    function renderMap() {
        var w = MAP_W, h = MAP_H;
        canvas.width = w;
        canvas.height = h;

        // Mouse delta (normalized -1..1)
        var mx = (mouseX - 0.5) * 2;
        var my = (mouseY - 0.5) * 2;

        // Store displacement vectors
        var vals = new Float32Array(w * h * 2);
        var maxVal = 0;

        var i, x, y, ux, uy, ix, iy, d, edgeWeight, dx, dy, dist, infl, ox, oy, ripple, rx, ry, tx, ty;

        for (y = 0; y < h; y++) {
            for (x = 0; x < w; x++) {
                i = (y * w + x) * 2;
                ux = x / w;
                uy = y / h;
                ix = ux - 0.5;
                iy = uy - 0.5;

                // Edge SDF — creates the "rim" of the glass
                d = rrectSDF(ix, iy, 0.45, 0.4, 0.5);
                edgeWeight = smoothstep(0.4, 0, Math.abs(d) - 0.04);

                // Mouse influence: pull displacement toward cursor
                dx = ix - mx * 0.35;
                dy = iy - my * 0.35;
                dist = len(dx, dy);
                infl = Math.max(0, 1 - dist * 2.2);

                // Combined displacement strength
                var strength = edgeWeight * 0.12 + infl * 0.06;

                ox = ix + mx * strength;
                oy = iy + my * strength;

                // Subtle ripple near the cursor
                ripple = Math.max(0, 1 - dist * 3) * 0.035;
                rx = ox + (ix - mx * 0.5) * ripple;
                ry = oy + (iy - my * 0.5) * ripple;

                tx = (rx + 0.5) * w - x;
                ty = (ry + 0.5) * h - y;
                vals[i] = tx;
                vals[i + 1] = ty;
                maxVal = Math.max(maxVal, Math.abs(tx), Math.abs(ty));
            }
        }

        // Normalize and pack into RGBA
        var scale = Math.max(maxVal, 0.5) * 0.6;
        var data = new Uint8ClampedArray(w * h * 4);
        var idx;
        for (i = 0; i < w * h; i++) {
            idx = i * 4;
            data[idx] = ((vals[i * 2] / scale) + 0.5) * 255;
            data[idx + 1] = ((vals[i * 2 + 1] / scale) + 0.5) * 255;
            data[idx + 2] = 128;
            data[idx + 3] = 255;
        }

        ctx.putImageData(new ImageData(data, w, h), 0, 0);

        // Feed the map into the SVG filter
        if (feImg) {
            feImg.setAttribute('href', canvas.toDataURL());
        }
        if (feDisp) {
            feDisp.setAttribute('scale', String(Math.round(scale * 60)));
        }
    }

    /* ─── 3. Animation loop ─── */
    function loop() {
        var dx = rawX - mouseX;
        var dy = rawY - mouseY;
        mouseX += dx * 0.10;
        mouseY += dy * 0.10;
        if (Math.abs(dx) > 0.0005 || Math.abs(dy) > 0.0005) {
            renderMap();
        }
        animId = requestAnimationFrame(loop);
    }

    /* ─── 4. Apply layers to a target element ─── */
    function applyTo(el) {
        if (el._lgApplied) return;
        el._lgApplied = true;
        targets.push(el);

        // Ensure positioning context
        if (getComputedStyle(el).position === 'static') {
            el.style.position = 'relative';
        }

        // Read border-radius from computed style
        var rStyle = getComputedStyle(el).borderRadius;
        var radius = parseInt(rStyle) || 18;
        radius = Math.max(radius, 4);
        var innerPad = Math.max(radius - 3, 2);

        // Build the edge-only mask SVG inline
        var maskSvgOuter = 'data:image/svg+xml;charset=utf-8,' +
            encodeURIComponent(
                '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">' +
                '<rect x="0" y="0" width="100%" height="100%" rx="' + radius + '" ry="' + radius + '" fill="white"/>' +
                '</svg>'
            );
        var maskSvgInner = 'data:image/svg+xml;charset=utf-8,' +
            encodeURIComponent(
                '<svg xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">' +
                '<rect x="' + innerPad + '" y="' + innerPad + '" width="calc(100% - ' + (innerPad * 2) + 'px)" height="calc(100% - ' + (innerPad * 2) + 'px)" rx="' + (radius - innerPad) + '" ry="' + (radius - innerPad) + '" fill="white"/>' +
                '</svg>'
            );

        // Layers in DOM order (prepended in reverse so final order is: outer, cover, sharp, reflect, content)
        var layers = [
            { name: 'reflect', z: 1, inset: '2px' },
            { name: 'sharp', z: 1, inset: '0' },
            { name: 'cover', z: 1, inset: '0' },
            { name: 'outer', z: 1, inset: '0' }
        ];

        var i, layer, div, styleText;
        for (i = 0; i < layers.length; i++) {
            layer = layers[i];
            div = document.createElement('div');
            div.className = 'lg-' + layer.name;

            styleText = [
                'position:absolute',
                'inset:' + layer.inset,
                'pointer-events:none',
                'z-index:' + layer.z,
                'border-radius:' + radius + 'px',
                'overflow:hidden'
            ].join(';') + ';';

            switch (layer.name) {
                case 'outer':
                    // Apply the SVG displacement filter on the backdrop
                    styleText += 'backdrop-filter:url(#' + FILTER_ID + ');';
                    styleText += '-webkit-backdrop-filter:url(#' + FILTER_ID + ');';
                    // Edge-only mask: outer shape minus inner shape
                    styleText += 'mask-image:url(' + maskSvgOuter + '),url(' + maskSvgInner + ');';
                    styleText += '-webkit-mask-image:url(' + maskSvgOuter + '),url(' + maskSvgInner + ');';
                    styleText += 'mask-composite:exclude;';
                    styleText += '-webkit-mask-composite:xor;';
                    break;

                case 'cover':
                    // Frosted glass background
                    styleText += 'background:rgba(255,255,255,0.55);';
                    styleText += 'backdrop-filter:blur(24px);';
                    styleText += '-webkit-backdrop-filter:blur(24px);';
                    break;

                case 'sharp':
                    // Crisp edge highlight
                    styleText += 'box-shadow:inset 0 0 0 1px rgba(255,255,255,0.5),inset 0 0 0 0.5px rgba(255,255,255,0.3);';
                    break;

                case 'reflect':
                    // Soft inner glow / reflection
                    styleText += 'box-shadow:inset 2px 2px 10px 2px rgba(255,255,255,0.12),inset -2px -2px 6px -1px rgba(255,255,255,0.08);';
                    break;
            }

            div.style.cssText = styleText;
            el.prepend(div);
        }
    }

    /* ─── 5. Initialization ─── */
    function init() {
        if (initialized) return;
        initialized = true;

        setupFilter();

        // Apply to elements with data-liquid-glass attribute
        document.querySelectorAll('[data-liquid-glass]').forEach(applyTo);

        // Apply to elements that should always have the effect
        document.querySelectorAll('.sidebar-card, .gallery-item, .upload-modal-content').forEach(function (el) {
            if (!el._lgApplied) applyTo(el);
        });

        // Mouse tracking
        document.addEventListener('mousemove', function (e) {
            rawX = e.clientX / window.innerWidth;
            rawY = e.clientY / window.innerHeight;
        });

        // Touch support (mobile)
        document.addEventListener('touchmove', function (e) {
            var touch = e.touches[0];
            if (touch) {
                rawX = touch.clientX / window.innerWidth;
                rawY = touch.clientY / window.innerHeight;
            }
        }, { passive: true });

        // Render initial neutral map, then start the loop
        renderMap();
        loop();
    }

    // Auto-start
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for manual re-init (e.g. after AJAX content load)
    window.initLiquidGlass = function () {
        document.querySelectorAll('[data-liquid-glass]:not(._lgApplied)').forEach(applyTo);
    };
})();