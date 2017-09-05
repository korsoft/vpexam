// WebcamJS v1.0.4
// Webcam library for capturing JPEG/PNG images in JavaScript
// Attempts getUserMedia, falls back to Flash
// Author: Joseph Huckaby: http://github.com/jhuckaby
// Based on JPEGCam: http://code.google.com/p/jpegcam/
// Copyright (c) 2012 - 2015 Joseph Huckaby
// Licensed under the MIT License

(function(window) {
    var Webcam = {
        version: '1.0.4',

        // Globals
        protocol: location.protocol.match(/https/i) ? 'https' : 'http',
        swfURL: '',         // URI to webcam.swf movie (defaults to the js location)
        loaded: false,      // True when webcam movie finishes loading
        live: false,        // True when webcam is initialized and ready to snap
        userMedia: true,    // True when getUserMedia is supported natively

        params: {
            width: 0,
            height: 0,
            destWidth: 0,           // Size of captured image
            destHeight: 0,          // These default to width/height
            imageFormat: 'jpeg',    // Image format (may be jpeg or png)
            jpegQuality: 90,        // JPEG image quality from 0 (worst) to 100 (best)
            forceFlash: false,      // Force flash mode
            flipHoriz: false,       // Flip image horiz (mirror mode)
            fps: 30,                // Camera frames per second
            uploadName: 'webcam',   // Name of file in upload post data
            constraints: null       // Custom user media constraints
        },

        hooks: {},   // Callback hook functions

        init: function() {
            // Initialize, check for getUserMedia support
            var self = this;

            navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
            window.URL = window.URL || window.webkitURL || window.mozURL || window.msURL;

            this.userMedia = this.userMedia && !!navigator.getUserMedia && !!window.URL;

            // Older versions of firefox (<21) apparently claim support but user media does not actually work
            if (navigator.userAgent.match(/Firefox\D+(\d+)/)) {
                if (parseInt(RegExp.$1, 10) < 21)
                    this.userMedia = null;
            }

            // Make sure media stream is closed when navigating away from page
            if (this.userMedia) {
                window.addEventListener('beforeunload', function(event) {
                    if (self.stream) {
                        self.stream.stop();
                        self.stream = null;
                    }
                });
            }
        },

        attach: function(elem) {
            // Create webcam preview and attach to DOM element
            // Pass in actual DOM reference, ID, or CSS selector
            if (typeof(elem) == 'string')
                elem = document.getElementById(elem) || document.querySelector(elem);
            if (!elem)
                return this.dispatch('error', "Could not locate DOM element to attach to.");
            this.container = elem;
            elem.innerHTML = '';    // Start with empty element

            // Insert "peg" so we can insert our preview canvas adjacent to it later on
            var peg = document.createElement('div');
            elem.appendChild(peg);
            this.peg = peg;

            // Set width/height if not already set
            if (!this.params.width)
                this.params.width = elem.offsetWidth;
            if (!this.params.height)
                this.params.height = elem.offsetHeight;

            // Set defaults for destWidth/destHeight if not set
            if (!this.params.destWidth)
                this.params.destWidth = this.params.width;
            if (!this.params.destHeight)
                this.params.destHeight = this.params.height;

            // If forceFlash is set, disable userMedia
            if (this.params.forceFlash)
                this.userMedia = null;

            // Check for default fps
            if (typeof this.parms.fps !== "number")
                this.params.fps = 30;

            // Adjust scale if destWidth or destHeight is different
            var scaleX = this.params.width / this.params.destWidth;
            var scaleY = this.params.height / this.params.destHeight;

            if (this.userMedia) {
                // Setup webcam video container
                var video = document.createElement('video');
                video.setAttribute('autoplay', 'autoplay');
                video.style.width = '' + this.params.destWidth + 'px';
                video.style.height = '' + this.params.destHeight + 'px';

                if ((scaleX != 1.0) || (scaleY != 1.0)) {
                    elem.style.overflow = 'hidden';
                    video.style.webkitTransformOrigin = '0px 0px';
                    video.style.mozTransformOrigin = '0px 0px';
                    video.style.msTransformOrigin = '0px 0px';
                    video.style.oTransformOrigin = '0px 0px';
                    video.style.transformOrigin = '0px 0px';
                    video.style.webkitTransform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                    video.style.mozTransform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                    video.style.msTransform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                    video.style.oTransform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                    video.style.transform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                }

                // Add video element to dom
                elem.appendChild(video);
                this.video = video;

                // Ask user for access to thier camera
                var self = this;
                navigator.getUserMedia({
                    "audio": false,
                    "video": this.params.constraints || {
                        mandatory: {
                            minWidth: this.params.destWidth,
                            minHeight: this.params.destHeight
                        }
                    }
                }, function(stream) {
                    // Got access, attach stream to video
                    video.src = window.URL.createObjectURL(stream) || stream;
                    Webcam.stream = stream;
                    Webcam.loaded = true;
                    Webcam.live = true;
                    Webcam.dispatch('load');
                    Webcam.dispatch('live');
                    Webcam.flip();
                }, function(err) {
                    return self.dispatch('error', "Could not access webcam.");
                });
            } else {
                // Flash callback
                window.Webcam = Webcam; // Needed for flash-to-js interface
                var div = document.createElement('div');
                div.innerHTML = this.getSWFHTML();
                elem.appendChild(div);
            }

            // Setup final crop for live preview
            if (this.params.cropWidth && this.params.cropHeight) {
                var scaledCropWidth = Math.floor(this.params.cropWidth * scaleX);
                var scaledCropHeight = Math.floor(this.params.cropHeight * scaleY);

                elem.style.width = '' + scaledCropWidth + 'px';
                elem.style.height = '' + scaledCropHeight + 'px';
                elem.style.overflow = 'hidden';

                elem.scrollLeft = Math.floor((this.params.width / 2) - (scaledCropWidth / 2));
                elem.scrollTop = Math.floor((this.params.height / 2) - (scaledCropHeight / 2))
            } else {
                // No crop, set size to desired
                elem.style.width = '' + this.params.width + 'px';
                elem.style.height = '' + this.params.height + 'px';
            }
        },

        reset: function() {
            // Shutdown camera, reset to potentially attach again
            if (this.previewActive)
                this.unfreeze();

            // Attempt to fix issue #64
            this.unflip();

            if (this.userMedia) {
                try {
                    this.stream.stop();
                } catch (e) {;}
                delete this.stream;
                delete this.video;
            }

            if (this.container) {
                this.container.innerHTML = '';
                delete this.container;
            }

            this.loaded = false;
            this.live = false;
        },

        set: function() {
            // Set one or more params
            // Variable argument list: 1 param = hash, 2 params = key, value
            if (arguments.length == 1) {
                for (var key in arguments[0])
                    this.params[key] = arguments[0][key];
            } else {
                this.params[arguments[0]] = arguments[1];
            }
        },

        on: function(name, callback) {
            // Set callback hook
            name = name.replace(/^on/i, '').toLowerCase();
            if (!this.hooks[name])
                this.hooks[name] = [];
            this.hooks[name].push(callback);
        },

        off: function(name, callback) {
            // Remove callback hook
            name = name.replace(/^on/i, '').toLowerCase();
            if (this.hooks[name]) {
                if (callback) {
                    // Remove one selected callback from list
                    var idx = this.hooks[name].indexOf(callback);
                    if (idx > -1)
                        this.hooks[name].splice(idx, 1);
                } else {
                    // No callback specified, so clear all
                    this.hooks[name] = [];
                }
            }
        },

        dispatch: function() {
            // Fire hook callback, passing optional value to it
            var name = arguments[0].replace(/^on/i, '').toLowerCase();
            var args = Array.prototype.slice.call(arguments, 1);

            if (this.hooks[name] && this.hooks[name].length) {
                for (var idx = 0, len = this.hooks[name].length; idx < len; idx++) {
                    var hook = this.hooks[name][idx];

                    if (typeof(hook) == 'function') {
                        // Callback is function reference, call directly
                        hook.apply(this, args);
                    } else if ((typeof(hook) == 'object') && (hook.length == 2)) {
                        // Callback is PHP-style object instance method
                        hook[0][hook[1]].apply(hook[0], args);
                    } else if (window[hook]) {
                        // Callback is global function name
                        window[hook].apply(window, args);
                    }
                } // loop
                return true;
            } else if (name == 'error') {
                // Default error handler if no custom one specified
                alert("Webcam.js Error: " + args[0]);
            }

            return false;   // No hook defined
        },

        setSWFLocation: function(url) {
            // Set location of SWF movie (defaults to webcam.swf in cwd)
            this.swfURL = url;
        },

        detectFlash: function() {
            // Return true if browser supports flash, false otherwise
            // Code snippet borrowed from: https://github.com/swfobject/swfobject
            var SHOCKWAVE_FLASH = "Shockwave Flash",
                SHOCKWAVE_FLASH_AX = "ShockwaveFlash.ShockwaveFlash",
                FLASH_MIME_TYPE = "application/x-shockwave-flash",
                win = window,
                nav = navigator,
                hasFlash = false;

            if (typeof nav.plugins !== "undefined" && typeof nav.plugins[SHOCKWAVE_FLASH] === "object") {
                var desc = nav.plugins[SHOCKWAVE_FLASH].description;
                if (desc && (typeof nav.mimeTypes !== "undefined" && nav.mimeTypes[FLASH_MIME_TYPE] && nav.mimeTypes[FLASH_MIME_TYPE].enabledPlugin))
                    hasFlash = true;
            } else if (typeof win.ActiveXObject !== "undefined") {
                try {
                    var ax = new ActiveXObject(SHOCKWAVE_FLASH_AX);
                    if (ax) {
                        var ver = ax.GetVariable("$version");
                        if (ver)
                            hasFlash = true;
                    }
                } catch (e) {;}
            }

            return hasFlash;
        },

        getSWFHTML: function() {
            // Return HTML for embedding flash based webcam capture movie
            var html = '';

            // Make sure we aren't running locally (flash doesn't work)
            if (location.protocol.match(/file/)) {
                this.dispatch('error', "Flash does not work from local disk.  Please run from a web server.");
                return '<h3 style="color:red">ERROR: the Webcam.js Flash fallback does not work from local disk.  Please run it from a web server.</h3>';
            }

            // Make sure we have flash
            if (!this.detectFlash()) {
                this.dispatch('error', "Adobe Flash Player not found.  Please install from get.adobe.com/flashplayer and try again.");
                return '<h3 style="color:red">ERROR: No Adobe Flash Player detected.  Webcam.js relies on Flash for browsers that do not support getUserMedia (like yours).</h3>';
            }

            // Set default swfURL if not explicitly set
            if (!this.swfURL) {
                // Find our script tag, and use that base URL
                var baseURL = '';
                var scripts = document.getElementsByTagName('script');
                for (var idx = 0, len = scripts.length; idx < len; idx++) {
                    var src = scripts[idx].getAttribute('src');
                    if (src && src.match(/\/webcam(\.min)?\.js/)) {
                        baseURL = src.replace(/\/webcam(\.min)?\.js.*$/, '');
                        idx = len;
                    }
                }
                if (baseURL)
                    this.swfURL = baseURL + '/webcam.swf';
                else
                    this.swfURL = 'webcam.swf';
            }

            // If this is the user's first visit, set flashvar so flash privacy settings panel is shown first
            if (window.localStorage && !localStorage.getItem('visited')) {
                this.params.newUser = 1;
                localStorage.setItem('visited', 1);
            }

            // Construct flashvars string
            var flashvars = '';
            for (var key in this.params) {
                if (flashvars)
                    flashvars += '&';
                flashvars += key + '=' + escape(this.params[key]);
            }

            // construct object/embed tag
            html += '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" type="application/x-shockwave-flash" codebase="'+this.protocol+'://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="'+this.params.width+'" height="'+this.params.height+'" id="webcam_movie_obj" align="middle"><param name="wmode" value="opaque" /><param name="allowScriptAccess" value="always" /><param name="allowFullScreen" value="false" /><param name="movie" value="'+this.swfURL+'" /><param name="loop" value="false" /><param name="menu" value="false" /><param name="quality" value="best" /><param name="bgcolor" value="#ffffff" /><param name="flashvars" value="'+flashvars+'"/><embed id="webcam_movie_embed" src="'+this.swfURL+'" wmode="opaque" loop="false" menu="false" quality="best" bgcolor="#ffffff" width="'+this.params.width+'" height="'+this.params.height+'" name="webcam_movie_embed" align="middle" allowScriptAccess="always" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" flashvars="'+flashvars+'"></embed></object>';

            return html;
        },

        getMovie: function() {
            // Get reference to movie object/embed in DOM
            if (!this.loaded)
                return this.dispatch('error', "Flash Movie is not loaded yet");
            var movie = document.getElementById('webcam_movie_obj');
            if (!movie || !movie._snap)
                movie = document.getElementById('webcam_movie_embed');
            if (!movie)
                this.dispatch('error', "Cannot locate Flash movie in DOM");
            return movie;
        },

        freeze: function() {
            // Show preview, freeze camera
            var self = this;
            var params = this.params;

            // Kill preview if already active
            if (this.previewActive)
                this.unfreeze();

            // Determine scale factor
            var scaleX = this.params.width / this.params.destWidth;
            var scaleY = this.params.height / this.params.destHeight;

            // Must unflip container as preview canvas will be pre-flipped
            this.unflip();

            // Calc final size of image
            var finalWidth = params.cropWidth || params.destWidth;
            var finalHeight = params.cropHeight || params.destHeight;

            // Create canvas for holding preview
            var previewCanvas = document.createElement('canvas');
            previewCanvas.width = finalWidth;
            previewCanvas.height = finalHeight;
            var previewContext = previewCanvas.getContext('2d');

            // Save for later use
            this.previewCanvas = previewCanvas;
            this.previewContext = previewContext;

            // Scale for preview size
            if ((scaleX != 1.0) || (scaleY != 1.0)) {
                previewCanvas.style.webkitTransformOrigin = '0px 0px';
                previewCanvas.style.mozTransformOrigin = '0px 0px';
                previewCanvas.style.msTransformOrigin = '0px 0px';
                previewCanvas.style.oTransformOrigin = '0px 0px';
                previewCanvas.style.transformOrigin = '0px 0px';
                previewCanvas.style.webkitTransform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                previewCanvas.style.mozTransform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                previewCanvas.style.msTransform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                previewCanvas.style.oTransform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
                previewCanvas.style.transform = 'scaleX(' + scaleX + ') scaleY(' + scaleY + ')';
            }

            // Take snapshot, but fire our own callback
            this.snap(function() {
                // Add preview image to dom, adjust for crop
                previewCanvas.style.position = 'relative';
                previewCanvas.style.left = '' + self.container.scrollLeft + 'px';
                previewCanvas.style.top = '' + self.container.scrollTop + 'px';

                self.container.insertBefore(previewCanvas, self.peg);
                self.container.style.overflow = 'hidden';

                // Set flag for user capture (use preview)
                self.previewActive = true;
            }, previewCanvas);
        },

        unfreeze: function() {
            // Cancel preview and resume live video feed
            if (this.previewActive) {
                // Remove preview canvas
                this.container.removeChild(this.previewCanvas);
                delete this.previewContext;
                delete this.previewCanvas;

                // Unflag
                this.previewActive = false;

                // Re-flip if we unflipped before
                this.flip();
            }
        },

        flip: function() {
            // Flip container horiz (mirror mode) if desired
            if (this.params.flipHoriz) {
                var sty = this.container.style;
                sty.webkitTransform = 'scaleX(-1)';
                sty.mozTransform = 'scaleX(-1)';
                sty.msTransform = 'scaleX(-1)';
                sty.oTransform = 'scaleX(-1)';
                sty.transform = 'scaleX(-1)';
                sty.filter = 'FlipH';
                sty.msFilter = 'FlipH';
            }
        },

        unflip: function() {
            // unflip container horiz (mirror mode) if desired
            if (this.params.flipHoriz) {
                var sty = this.container.style;
                sty.webkitTransform = 'scaleX(1)';
                sty.mozTransform = 'scaleX(1)';
                sty.msTransform = 'scaleX(1)';
                sty.oTransform = 'scaleX(1)';
                sty.transform = 'scaleX(1)';
                sty.filter = '';
                sty.msFilter = '';
            }
        },

        savePreview: function(userCallback, userCanvas) {
            // Save preview freeze and fire user callback
            var params = this.params;
            var canvas = this.previewCanvas;
            var context = this.previewContext;

            // Render to user canvas if desired
            if (userCanvas) {
                var userContext = userCanvas.getContext('2d');
                userContext.drawImage(canvas, 0, 0);
            }

            // Fire user callback if desired
            userCallback(
                userCanvas ? null : canvas.toDataURL('image/' + params.imageFormat, params.jpegQuality / 100),
                canvas,
                context
            );

            // Remove preview
            this.unfreeze();
        },

        snap: function(userCallback, userCanvas) {
            // Take snapshot and return image data uri
            var self = this;
            var params = this.params;

            if (!this.loaded)
                return this.dispatch('error', "Webcam is not loaded yet");
            if (!userCallback)
                return this.dispatch('error', "Please provide a callback function or canvas to snap()");

            // If we have an active preview freeze, use that
            if (this.previewActive) {
                this.savePreview(userCallback, userCanvas);
                return null;
            }

            // Create offscreen canvas element to hold pixels
            var canvas = document.createElement('canvas');
            canvas.width = this.params.destWidth;
            canvas.height = this.params.destHeight;
            var context = canvas.getContext('2d');

            // Flip canvas horizontally if desired
            if (this.params.flipHoriz) {
                context.translate(params.destWidth, 0);
                context.scale(-1, 1);
            }

            // Create inline function, called after image load (flash) or immediately (native)
            var func = function() {
                // Render image f needed (flash)
                if (this.src && this.width && this.height)
                    context.drawImage(this, 0, 0, params.destWidth, params.destHeight);

                // Crop if desired
                if (params.cropWidth && params.cropHeight) {
                    var cropCanvas = document.createElement('canvas');
                    cropCanvas.width = params.cropWidth;
                    cropCanvas.height = params.cropHeight;
                    var cropContext = cropCanvas.getContext('2d');

                    cropContext.drawImage(
                        canvas,
                        Math.floor((params.destWidth / 2) - (params.cropWidth / 2)),
                        Math.floor((params.destHeight / 2) - (params.cropHeight / 2)),
                        params.cropWidth,
                        params.cropHeight,
                        0,
                        0,
                        params.cropWidth,
                        params.cropHeight
                    );

                    // Swap canvases
                    context = cropContext;
                    canvas = cropCanvas;
                }

                // Render to user canvas if desired
                if (userCanvas) {
                    var userContext = userCanvas.getContext('2d');
                    userContext.drawImage(canvas, 0, 0);
                }

                // Fire user callback if desired
                userCallback(
                    userCanvas ? null : canvas.toDataURL('image/' + params.imageFormat, params.jpegQuality / 100),
                    canvas,
                    context
                );
            };

            // Grab image frame from userMedia or flash movie
            if (this.userMedia) {
                // Native implementation
                context.drawImage(this.video, 0, 0, this.params.destWidth, this.params.destHeight);

                // Fire callback right away
                func();
            } else {
                // Flash fallback
                var rawData = this.getMovie()._snap();

                // Render to image, fire callback when complete
                var img = new Image();
                img.onload = func;
                img.src = 'data:image/' + this.params.imageFormat + ';base64,' + rawData;
            }
            return null;
        },

        configure: function(panel) {
            // open flash configuration panel -- specify tab name:
            // "camera", "privacy", "default", "localStorage", "microphone", "settingsManager"
            if (!panel)
                panel = "camera";
            this.getMovie()._configure(panel);
        },

        flashNotify: function(type, msg) {
            // Receive notification from flash about event
            switch (type) {
                case 'flashLoadComplete':
                    // Movie loaded successfully
                    this.loaded = true;
                    this.dispatch('load');
                    break;

                case 'cameraLive':
                    // Camera is live and ready to snap
                    this.live = true;
                    this.dispatch('live');
                    this.flip();
                    break;

                case 'error':
                    // Flash error
                    this.dispatch('error', msg);
                    break;

                default:
                    // catch-all event, just in case
                    // console.log("webcam flash_notify: " + type + ": " + msg);
                    break;
            }
        },

        b64ToUint6: function(nChr) {
            // convert base64 encoded character to 6-bit integer
            // from: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Base64_encoding_and_decoding
            return nChr > 64 && nChr < 91 ? nChr - 65
                : nChr > 96 && nChr < 123 ? nChr - 71
                : nChr > 47 && nChr < 58 ? nChr + 4
                : nChr === 43 ? 62 : nChr === 47 ? 63 : 0;
        },

        base64DecToArr: function(sBase64, nBlocksSize) {
            // convert base64 encoded string to Uintarray
            // from: https://developer.mozilla.org/en-US/docs/Web/JavaScript/Base64_encoding_and_decoding
            var sB64Enc = sBase64.replace(/[^A-Za-z0-9\+\/]/g, ""), nInLen = sB64Enc.length,
                nOutLen = nBlocksSize ? Math.ceil((nInLen * 3 + 1 >> 2) / nBlocksSize) * nBlocksSize : nInLen * 3 + 1 >> 2,
                taBytes = new Uint8Array(nOutLen);

            for (var nMod3, nMod4, nUint24 = 0, nOutIdx = 0, nInIdx = 0; nInIdx < nInLen; nInIdx++) {
                nMod4 = nInIdx & 3;
                nUint24 |= this.b64ToUint6(sB64Enc.charCodeAt(nInIdx)) << 18 - 6 * nMod4;
                if (nMod4 === 3 || nInLen - nInIdx === 1) {
                    for (nMod3 = 0; nMod3 < 3 && nOutIdx < nOutLen; nMod3++, nOutIdx++) {
                        taBytes[nOutIdx] = nUint24 >>> (16 >>> nMod3 & 24) & 255;
                    }
                    nUint24 = 0;
                }
            }
            return taBytes;
        },

        upload: function(imageDataUri, targetUrl, callback) {
            // Submit image data to server using binary AJAX
            var formElemName = this.params.uploadName || 'webcam';

            // Detect image format from within imageDataUri
            var imageFmt = '';
            if (imageDataUri.match(/^data\:image\/(\w+)/))
                imageFmt = RegExp.$1;
            else
                throw "Cannot locate image format in Data URI";

            // Extract raw base64 data from Data URI
            var rawImageData = imageDataUri.replace(/^data\:image\/\w+\;base64\,/, '');

            // Construct use AJAX object
            var http = new XMLHttpRequest();
            http.open("POST", targetUrl, true);

            // Setup progress events
            if (http.upload && http.upload.addEventListener) {
                http.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        var progress = e.loaded / e.total;
                        Webcam.dispatch('uploadProgress', progress, e);
                    }
                }, false);
            }

            // Completion handler
            var self = this;
            http.onload = function() {
                if (callback)
                    callback.apply(self, [http.status, http.responseText, http.statusText]);
                Webcam.dispatch('uploadComplete', http.status, http.responseText, http.statusText);
            };

            // Create a blob and decode our base64 to binary
            var blob = new Blob([this.base64DecToArr(rawImageData)], { type: 'image/' + imageFmt });

            // Stuff into a form, so servers can easily receive it as a standard file upload
            var form = new FormData();
            form.append(formElemName, blob, formElemName + "." + imageFmt.replace(/e/, ''));

            // Send data to server
            http.send(form);
        }
    };

    Webcam.init();

    if (typeof define === 'function' && define.amd)
        define(function() { return Webcam; });
    else if (typeof module === 'object' && module.exports)
        module.exports = Webcam;
    else
        window.Webcam = Webcam;

}(window));