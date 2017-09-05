/*global window, FileAPI, document */

(function(api, document, undef) {
    'use strict';

    var min = Math.min,
        round = Math.round,
        getCanvas = function() {
            return document.createElement('canvas');
        },
        support = false,
        exifOrientation = {
            8: 270,
            3: 180,
            6: 90,
            7: 270,
            4: 180,
            5: 90
        };

    try {
        support = getCanvas().toDataURL('image/png').indexOf('data:image/png') > -1;
    } catch (e) {}

    function Image(file) {
        if (file instanceof Image) {
            var img = new Image(file.file);
            api.extend(img.matrix, file.matrix);
            return img;
        } else if (!(this instanceof Image)) {
            return new Image(file);
        }

        this.file = file;
        this.size = file.size || 100;

        this.matrix = {
            sx: 0,
            sy: 0,
            sw: 0,
            sh: 0,
            dx: 0,
            dy: 0,
            dw: 0,
            dh: 0,
            resize: 0,
            deg: 0,
            quality: 1,
            filter: 0
        };
    }

    Image.prototype = {
        image: true,
        constructor: Image,

        set: function(attrs) {
            api.extend(this.matrix, attrs);
            return this;
        },

        crop: function(x, y, w, h) {
            if (w === undef) {
                w = x;
                h = y;
                x = y = 0;
            }
            return this.set({ sx: x, sy: y, sw: w, sh: h || w });
        },

        resize: function(w, h, strategy) {
            if (/min|max|height|width/.test(h)) {
                strategy = h;
                h = w;
            }

            return this.set({ dw: w, dh: h || w, resize: strategy });
        },

        preview: function(w, h) {
            return this.resize(w, h || w, 'preview');
        },

        rotate: function(deg) {
            return this.set({ deg: deg });
        },

        filter: function(filter) {
            return this.set({ filter: filter });
        },

        overlay: function(images) {
            return this.set({ overlay: images });
        },

        clone: function() {
            return new Image(this);
        },

        _load: function(image, fn) {
            var self = this;

            if (/img|video/i.test(image.nodeName)) {
                fn.call(self, null, image);
            } else {
                api.readAsImage(image, function(evt) {
                    fn.call(self, evt.type != 'load', evt.result);
                });
            }
        },

        _apply: function(image, fn) {
            var canvas = getCanvas(),
                m = this.getMatrix(image),
                ctx = canvas.getContext('2d'),
                width = image.videoWidth || image.width,
                height = image.videoHeight || image.height,
                deg = m.deg,
                dw = m.dw,
                dh = m.dh,
                w = width,
                h = height,
                filter = m.filter,
                copy,
                buffer = image,
                overlay = m.overlay,
                queue = api.queue(function() {
                    image.src = api.EMPTY_PNG;
                    fn(false, canvas);
                }),
                renderImageToCanvas = api.renderImageToCanvas;

            // Normalize angle
            deg = deg - Math.floor(deg / 360) * 360;

            // For `renderImageToCanvas`
            image._type = this.file.type;

            while (m.multipass && min(w / dw, h / dh) > 2) {
                w = (w / 2 + 0.5) | 0;
                h = (h / 2 + 0.5) | 0;

                copy = getCanvas();
                copy.width = w;
                copy.height = h;

                if (buffer !== image) {
                    renderImageToCanvas(copy, buffer, 0, 0, buffer.width, buffer.height, 0, 0, w, h);
                    buffer = copy;
                } else {
                    buffer = copy;
                    renderImageToCanvas(buffer, image, m.sx, m.sy, m.sw, m.sh, 0, 0, w, h);
                    m.sx = m.sy = m.sw = m.sh = 0;
                }
            }

            canvas.width = (deg % 180) ? dh : dw;
            canvas.height = (deg % 180) ? dw : dh;

            canvas.type = m.type;
            canvas.quality = m.quality;
        }
    }
});