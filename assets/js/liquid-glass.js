/**
 * Liquid Glass Effect - SVG Filter-based implementation
 * Adapted from https://github.com/shuding/liquid-glass (Shu Ding)
 * 
 * Applies Apple-style liquid glass refraction effect to any DOM element.
 * Features: displacement refraction, mouse tracking, elastic feel
 */

(function () {
    'use strict';

    // --- Utility functions ---
    function smoothStep(a, b, t) {
        t = Math.max(0, Math.min(1, (t - a) / (b - a)));
        return t * t * (3 - 2 * t);
    }

    function length(x, y) {
        return Math.sqrt(x * x + y * y);
    }

    function roundedRectSDF(px, py, w, h, r) {
        var qx = Math.abs(px) - w + r;
        var qy = Math.abs(py) - h + r;
        return Math.min(Math.max(qx, qy), 0) + length(Math.max(qx, 0), Math.max(qy, 0)) - r;
    }

    function generateId() {
        return 'lg-' + Math.random().toString(36).substr(2, 9);
    }

    function texture(x, y) {
        return { x: x, y: y };
    }

    // --- Main animation loop ---
    var allInstances = [];
    var animFrameId = null;

    function startAnimationLoop() {
        if (animFrameId) return;
        var lastTime = 0;
        function loop(time) {
            var dt = Math.min((time - lastTime) / 1000, 0.05);
            lastTime = time;
            var active = false;
            for (var i = 0; i < allInstances.length; i++) {
                var inst = allInstances[i];
                if (inst.active) {
                    inst.update(dt);
                    active = true;
                }
            }
            if (active) {
                animFrameId = requestAnimationFrame(loop);
            } else {
                animFrameId = null;
            }
        }
        animFrameId = requestAnimationFrame(loop);
    }

    // --- Liquid Glass Instance ---
    function LiquidGlass(el, options) {
        this.el = el;
        this.options = options || {};
        this.id = generateId();
        this.active = true;

        this.width = el.offsetWidth || 100;
        this.height = el.offsetHeight || 100;
        this.dpi = 1; // Use 1x for performance
        this.mouse = { x: 0.5, y: 0.5 };
        this.targetMouse = { x: 0.5, y: 0.5 };
        this.elasticity = this.options.elasticity || 0.12;
        this.displacementScale = this.options.displacementScale || 80;
        this.blurAmount = this.options.blurAmount || 0.25;

        this.setupFilter();
        this.setupMouseTracking();
        this.setupPositionTracking();

        // Re-render on resize
        this.resizeObserver = new ResizeObserver(this.refresh.bind(this));
        this.resizeObserver.observe(el);

        allInstances.push(this);
        startAnimationLoop();

        // Initial shader render
        this.renderShader();
    }

    LiquidGlass.prototype.setupFilter = function () {
        var el = this.el;
        var id = this.id;

        // Find or create the shared SVG container
        var svgContainer = document.getElementById('liquid-glass-svg');
        if (!svgContainer) {
            svgContainer = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svgContainer.setAttribute('id', 'liquid-glass-svg');
            svgContainer.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svgContainer.style.cssText = 'position:fixed;top:0;left:0;width:0;height:0;pointer-events:none;z-index:-1;';
            document.body.appendChild(svgContainer);
            this.svgContainer = svgContainer;
        }
        this.svgContainer = svgContainer;

        // Ensure defs exists
        var defs = svgContainer.querySelector('defs');
        if (!defs) {
            defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
            svgContainer.appendChild(defs);
        }

        // Get element position for filter coordinates
        var rect = el.getBoundingClientRect();
        var filterX = rect.left + window.scrollX;
        var filterY = rect.top + window.scrollY;

        this.filterEl = document.createElementNS('http://www.w3.org/2000/svg', 'filter');
        this.filterEl.setAttribute('id', id + '_filter');
        this.filterEl.setAttribute('filterUnits', 'userSpaceOnUse');
        this.filterEl.setAttribute('colorInterpolationFilters', 'sRGB');
        this.filterEl.setAttribute('x', filterX.toString());
        this.filterEl.setAttribute('y', filterY.toString());
        this.filterEl.setAttribute('width', this.width.toString());
        this.filterEl.setAttribute('height', this.height.toString());

        this.feImage = document.createElementNS('http://www.w3.org/2000/svg', 'feImage');
        this.feImage.setAttribute('id', id + '_map');
        this.feImage.setAttribute('width', this.width.toString());
        this.feImage.setAttribute('height', this.height.toString());
        this.feImage.setAttribute('preserveAspectRatio', 'none');

        this.feDisplacementMap = document.createElementNS('http://www.w3.org/2000/svg', 'feDisplacementMap');
        this.feDisplacementMap.setAttribute('in', 'SourceGraphic');
        this.feDisplacementMap.setAttribute('in2', id + '_map');
        this.feDisplacementMap.setAttribute('xChannelSelector', 'R');
        this.feDisplacementMap.setAttribute('yChannelSelector', 'G');
        this.feDisplacementMap.setAttribute('scale', this.displacementScale.toString());

        // Gaussian blur for the glass frosted effect
        this.feGaussianBlur = document.createElementNS('http://www.w3.org/2000/svg', 'feGaussianBlur');
        this.feGaussianBlur.setAttribute('in', 'SourceGraphic');
        this.feGaussianBlur.setAttribute('stdDeviation', this.blurAmount.toString());
        this.feGaussianBlur.setAttribute('result', 'blur');

        this.filterEl.appendChild(this.feGaussianBlur);
        this.filterEl.appendChild(this.feImage);
        this.filterEl.appendChild(this.feDisplacementMap);
        defs.appendChild(this.filterEl);

        // Create canvas for displacement map
        this.canvas = document.createElement('canvas');
        this.canvas.width = this.width * this.dpi;
        this.canvas.height = this.height * this.dpi;
        this.canvas.style.display = 'none';
        this.ctx = this.canvas.getContext('2d');

        // Apply filter to element
        var filterValue = 'url(#' + id + '_filter)';
        el.style.backdropFilter = filterValue + ' blur(' + this.blurAmount + 'px) contrast(1.05) saturate(1.1)';
        el.style.webkitBackdropFilter = el.style.backdropFilter;
    };

    LiquidGlass.prototype.setupMouseTracking = function () {
        var self = this;
        this._onMouseMove = function (e) {
            var rect = self.el.getBoundingClientRect();
            var mx = (e.clientX - rect.left) / rect.width;
            var my = (e.clientY - rect.top) / rect.height;
            self.targetMouse.x = Math.max(0, Math.min(1, mx));
            self.targetMouse.y = Math.max(0, Math.min(1, my));
        };
        document.addEventListener('mousemove', this._onMouseMove);
    };

    LiquidGlass.prototype.setupPositionTracking = function () {
        var self = this;
        this._onScroll = function () {
            self.updateFilterPosition();
        };
        this._onScrollTick = 0;
        // Throttled scroll handler
        var scrollHandler = function () {
            var now = Date.now();
            if (now - self._onScrollTick > 100) {
                self._onScrollTick = now;
                self.updateFilterPosition();
            }
        };
        window.addEventListener('scroll', scrollHandler, { passive: true });
        this._scrollHandler = scrollHandler;
    };

    LiquidGlass.prototype.updateFilterPosition = function () {
        var rect = this.el.getBoundingClientRect();
        var filterX = rect.left + window.scrollX;
        var filterY = rect.top + window.scrollY;
        this.filterEl.setAttribute('x', filterX.toString());
        this.filterEl.setAttribute('y', filterY.toString());
    };

    LiquidGlass.prototype.update = function (dt) {
        // Spring physics for elastic mouse tracking
        var dx = this.targetMouse.x - this.mouse.x;
        var dy = this.targetMouse.y - this.mouse.y;
        this.mouse.x += dx * Math.min(this.elasticity * 60 * dt, 1);
        this.mouse.y += dy * Math.min(this.elasticity * 60 * dt, 1);

        // Only re-render if mouse moved significantly
        if (Math.abs(dx) > 0.001 || Math.abs(dy) > 0.001) {
            this.renderShader();
        }
    };

    LiquidGlass.prototype.renderShader = function () {
        var w = this.canvas.width;
        var h = this.canvas.height;
        var data = new Uint8ClampedArray(w * h * 4);
        var maxScale = 0;
        var rawValues = [];

        var pos, dx, dy, uv, frag;

        for (var i = 0; i < data.length; i += 4) {
            var px = (i / 4) % w;
            var py = Math.floor(i / 4 / w);
            var ux = px / w;
            var uy = py / h;

            // --- Fragment shader ---
            // Center coordinates
            var ix = ux - 0.5;
            var iy = uy - 0.5;

            // Normalized mouse offset from center
            var mx = (this.mouse.x - 0.5) * 0.3;
            var my = (this.mouse.y - 0.5) * 0.3;

            // Rounded rect SDF for edge displacement
            var d = roundedRectSDF(ix, iy, 0.48, 0.48, 0.6);
            var edgeFactor = smoothStep(0.6, 0, Math.abs(d) - 0.1);

            // Mouse proximity influence
            var distToMouse = length(ix - mx * 1.5, iy - my * 1.5);
            var mouseInfluence = Math.max(0, 1 - distToMouse * 2.5);

            // Combined displacement
            var disp = edgeFactor * 0.08 + mouseInfluence * 0.04;

            // Distorted coordinates
            var ox = ix + mx * disp * 0.5;
            var oy = iy + my * disp * 0.5;

            // Liquid ripple effect around mouse
            var ripple = Math.max(0, 1 - distToMouse * 3) * 0.02;
            ox += (ix - mx) * ripple;
            oy += (iy - my) * ripple;

            frag = texture(ox + 0.5, oy + 0.5);

            dx = frag.x * w - px;
            dy = frag.y * h - py;
            maxScale = Math.max(maxScale, Math.abs(dx), Math.abs(dy));
            rawValues.push(dx, dy);
        }

        maxScale *= 0.5;
        if (maxScale < 1) maxScale = 1;

        var idx = 0;
        for (var j = 0; j < data.length; j += 4) {
            var r = rawValues[idx++] / maxScale + 0.5;
            var g = rawValues[idx++] / maxScale + 0.5;
            data[j] = r * 255;
            data[j + 1] = g * 255;
            data[j + 2] = 0;
            data[j + 3] = 255;
        }

        this.ctx.putImageData(new ImageData(data, w, h), 0, 0);

        // Update SVG filter displacement map
        this.feImage.setAttributeNS('http://www.w3.org/1999/xlink', 'href', this.canvas.toDataURL());
        var scale = maxScale / this.dpi * this.displacementScale * 0.01;
        this.feDisplacementMap.setAttribute('scale', scale.toString());
    };

    LiquidGlass.prototype.refresh = function () {
        this.width = this.el.offsetWidth || 100;
        this.height = this.el.offsetHeight || 100;

        // Update canvas size
        this.canvas.width = this.width * this.dpi;
        this.canvas.height = this.height * this.dpi;

        // Update filter dimensions
        var rect = this.el.getBoundingClientRect();
        var filterX = rect.left + window.scrollX;
        var filterY = rect.top + window.scrollY;
        this.filterEl.setAttribute('x', filterX.toString());
        this.filterEl.setAttribute('y', filterY.toString());
        this.filterEl.setAttribute('width', this.width.toString());
        this.filterEl.setAttribute('height', this.height.toString());
        this.feImage.setAttribute('width', this.width.toString());
        this.feImage.setAttribute('height', this.height.toString());

        this.renderShader();
    };

    LiquidGlass.prototype.destroy = function () {
        this.active = false;
        this.resizeObserver.disconnect();
        document.removeEventListener('mousemove', this._onMouseMove);
        if (this._scrollHandler) {
            window.removeEventListener('scroll', this._scrollHandler);
        }

        if (this.filterEl && this.filterEl.parentNode) {
            this.filterEl.parentNode.removeChild(this.filterEl);
        }

        var idx = allInstances.indexOf(this);
        if (idx !== -1) allInstances.splice(idx, 1);

        this.el.style.backdropFilter = '';
        this.el.style.webkitBackdropFilter = '';
    };

    // --- Auto-initialize on DOM elements with [data-liquid-glass] ---
    function init() {
        document.querySelectorAll('[data-liquid-glass]').forEach(function (el) {
            var opts = {};
            if (el.dataset.liquidElasticity) opts.elasticity = parseFloat(el.dataset.liquidElasticity);
            if (el.dataset.liquidScale) opts.displacementScale = parseFloat(el.dataset.liquidScale);
            if (!el._liquidGlass) {
                el._liquidGlass = new LiquidGlass(el, opts);
            }
        });
    }

    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for manual use
    window.LiquidGlass = LiquidGlass;
    window.initLiquidGlass = init;

    console.log('[Liquid Glass] Effect loaded. Add data-liquid-glass attribute to any element to enable.');
})();