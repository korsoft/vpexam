/*global window, FileAPI, Uint8Array */

(function(window, api) {
    "use strict";

    var noop = function() {},
        document = window.document,

        XHR = function(options) {
            this.uid = api.uid();
            this.xhr = {
                abort: noop,
                getResponseHeader: noop,
                getAllResponseHeaders: noop
            };
            this.options = options;
        },

        _xhrResponsePostfix = { '': 1, XML: 1, Text: 1, Body: 1 };

    XHR.prototype = {
        status: 0,
        statusText: '',
        constructor: XHR,

        getResponseHeader: function(name) {
            return this.xhr.getResponseHeader(name);
        },

        getAllResponseHeaders: function() {
            return this.xhr.getAllResponseHeaders() || {};
        },

        end: function(status, statusText) {
            var _this = this, options = _this.options;

            _this.end = _this.abort = noop;
            _this.status = status;

            if (statusText) {
                _this.statusText = statusText;
            }

            api.log('xhr.end:', status, statusText);
            options.complete(status == 200 || status == 201 ? false : _this.statusText || 'unknown', _this);

            if (_this.xhr && _this.xhr.node) {
                setTimeout(function() {
                    var node = _this.xhr.node;
                    try {
                        node.parentNode.removeChild(node);
                    } catch (e) {}
                    try {
                        delete window[_this.uid];
                    } catch (e) {}
                    window[_this.uid] = _this.xhr.node = null;
                }, 9);
            }
        },

        abort: function() {
            this.end(0, 'abort');

            if (this.xhr) {
                this.xhr.aborted = true;
                this.xhr.abort();
            }
        },

        send: function(FormData) {
            var _this = this, options = this.options;

            FormData.toData(function(data) {
                if (data instanceof Error) {
                    _this.end(0, data.message);
                } else {
                    // Start uploading
                    options.upload(options, _this);
                    _this._send.call(_this, options, data);
                }
            }, options);
        },

        _send: function(options, data) {
            var _this = this, xhr, uid = _this.uid, onLoadFnName = _this.uid + "Load", url = options.url;

            api.log('XHR._send:', data);

            if (!options.cache) {
                // No cache
                url += (~url.indexOf('?') ? '&' : '?') + api.uid();
            }

            if (data.nodeName) {
                var jsonp = options.jsonp;

                // Prepare callback in GET
                url = url.replace(/([a-z]+)=(\?)/i, '$1=' + uid);

                // Legacy
                options.upload(options, _this);

                var onPostMessage = function(evt) {
                    if (~url.indexOf(evt.origin)) {
                        try {
                            var result = api.parseJSON(evt.data);
                            if (result.id == uid) {
                                complete(result.status, result.statusText, result.response);
                            }
                        } catch (err) {
                            complete(0, err.message);
                        }
                    }
                },

                    // jsonp-callback
                    complete = window[uid] = function(status, statusText, response) {
                        _this.readyState = 4;
                        _this.responseText = response;
                        _this.end(status, statusText);

                        api.event.off(window, 'message', onPostMessage);
                        window[uid] = xhr = transport = window[onLoadFnName] = null;
                    }
                ;

                _this.xhr.abort = function() {
                    try {
                        if (transport.stop) {
                            transport.stop();
                        } else if (transport.contentWindow.stop) {
                            transport.contentWindow.stop();
                        } else {
                            transport.contentWindow.document.execCommand('Stop');
                        }
                    } catch (er) {}
                    complete(0, "abort");
                };

                api.event.on(window, 'message', onPostMessage);

                window[onLoadFnName] = function() {
                    try {
                        var win = transport.contentWindow,
                            doc = win.document,
                            result = win.result || api.parseJSON(doc.body.innerHTML);
                    } catch (e) {
                        api.log('[transport.onload]', e);
                    }
                };

                xhr = document.createElement('div');
                xhr.innerHTML = '<form target="'+ uid +'" action="'+ url +'" method="POST" enctype="multipart/form-data" style="position: absolute; top: -1000px; overflow: hidden; width: 1px; height: 1px;">'
                                + '<iframe name="'+ uid +'" src="javascript:false;" onload="window.' + onLoadFnName + ' && ' + onLoadFnName + '();"></iframe>'
                                + (jsonp && (options.url.indexOf('=?') < 0) ? '<input value="'+ uid +'" name="'+jsonp+'" type="hidden"/>' : '')
                                + '</form>';

                // Get form-data & transport
                var form = xhr.getElementsByTagName('form')[0],
                    transport = xhr.getElementsByTagName('iframe')[0];

                form.appendChild(data);

                api.log(form.parentNode.innerHTML);

                // Append to DOM
                document.body.appendChild(xhr);

                // Keep a reference to node-transport
                _this.xhr.node = xhr;

                // Send
                _this.readyState = 2;   // loaded
                try {
                    form.submit();
                } catch (err) {
                    api.log('iframe.error: ' + err);
                }
                form = null;
            } else {
                // Clean URL
                url = url.replace(/([a-z]+)=(\?)&?/i, '');

                // html5
                if (this.xhr && this.xhr.aborted) {
                    api.log("Error: already aborted");
                    return;
                }
                xhr = _this.xhr = api.getXHR();

                if (data.params) {
                    url += (url.indexOf('?') < 0 ? "?" : "&") + data.params.join("&");
                }

                xhr.open('POST', url, true);

                if (api.withCredentials) {
                    xhr.withCredentials = "true";
                }

                if (!options.headers || !options.headers['X-Requested-With']) {
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                }

                api.each(options.headers, function(val, key) {
                    xhr.setRequestHeader(key, val);
                });
            }
        }
    }
})(window, FileAPI);