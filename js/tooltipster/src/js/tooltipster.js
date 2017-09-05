// This file will be UMDified by a build task.

var defaults = {
        animation: 'fade',
        animationDuration: 350,
        content: null,
        contentAsHTML: false,
        contentCloning: false,
        debug: true,
        delay: 300,
        delayTouch: [300, 500],
        functionInit: null,
        functionBefore: null,
        functionReady: null,
        functionAfter: null,
        functionFormat: null,
        IEmin: 6,
        interactive: false,
        multiple: false,
        // must be 'body' for now, or an element positioned at (0, 0)
        // in the document, typically like the very top views of an app.
        parent: 'body',
        plugins: ['sideTip'],
        repositionOnScroll: false,
        restoration: 'none',
        selfDestruction: true,
        theme: [],
        timer: 0,
        trackerInterval: 500,
        trackOrigin: false,
        trackTooltip: false,
        trigger: 'hover',
        triggerClose: {
            click: false,
            mouseleave: false,
            originClick: false,
            scroll: false,
            tap: false,
            touchleave: false
        },
        triggerOpen: {
            click: false,
            mouseenter: false,
            tap: false,
            touchstart: false
        },
        updateAnimation: 'rotate',
        zIndex: 9999999
    },
    // we'll avoid using the 'window' global as a good practice but npm's
    // jquery@<2.1.0 package actually requires a 'window' global, so not sure
    // it's useful at all
    win = (typeof window != 'undefined') ? window : null,
    // env will be proxied by the core for plugins to have access its properties
    env = {
        // detect if this device can trigger touch events. Better have a false
        // positive (unused listeners, that's ok) than a false negative.
        // https://github.com/Modernizr/Modernizr/blob/master/feature-detects/touchevents.js
        // http://stackoverflow.com/questions/4817029/whats-the-best-way-to-detect-a-touch-screen-device-using-javascript
        hasTouchCapability: !!(win && ('ontouchstart' in win || (win.DocumentTouch && win.document instanceof win.DocumentTouch) || win.navigator.maxTouchPoints)),
        hasTransitions: transitionSupport(),
        IE: false,
        // don't set manually, it will be updated by a build task after the manifest
        semVer: '',
        window: win
    },
    core = function() {
        // core variables

        // the core emitters
        this.__$emitterPrivate = $({});
        this.__$emitterPublic = $({});
        this.__instancesLatestArr = [];
        // collects plugin constructors
        this.__plugins = {};
        // proxy env variables for plugins who might use them
        this._env = env;
    };

// core methods
core.prototype = {
    /**
     * A function to proxy the public methods of an object onto another
     *
     * @param {object} constructor The constructor to bridge
     * @param {object} obj The object that will get new methods (an instance or the core)
     * @param {string} pluginName A plugin name for the console log message
     * @return {core}
     * @private
     */
    __bridge: function(constructor, obj, pluginName) {
        // if it's not already bridged
        if (!obj[pluginName]) {
            var fn = function() {};
            fn.prototype = constructor;

            var pluginInstance = new fn();

            // the _init method has to exist in instance constructors but might be missing
            // in core constructors
            if (pluginInstance.__init)
                pluginInstance.__init(obj);

            $.each(constructor, function(methodName, fn) {
                // don't proxy "private" methods, only "protected" and public ones
                if (methodName.indexOf('__') != 0) {
                    // if the method does not exist yet
                    if (!obj[methodName]) {
                        obj[methodName] = function() {
                            return pluginInstance[methodName].apply(pluginInstance, Array.prototype.slice.apply(arguments));
                        };

                        // remember to which plugin this method corresponds (several plugins may
                        // have methods of the same name, we need to be sure)
                        obj[methodName].bridged = pluginInstance;
                    } else if (defaults.debug) {
                        console.log('The '+ methodName +' method of the '+ pluginName + ' plugin conflicts with another plugin or native methods');
                    }
                }
            });
            obj[pluginName] = pluginInstance;
        }

        return this;
    },

    /**
     * For mockup in Node env if need be, for testing purposes
     *
     * @return {core}
     * @private
     */
    __setWindow: function(window) {
        env.window = window;
        return this;
    },

    /**
     * Returns a ruler, a tool to help measure the size of a tooltip under
     * various settings. Meant for plugins
     *
     * @see Ruler
     * @return {object} A Ruler instance
     * @protected
     */
    _getRuler: function($tooltip) {
        return new Ruler($tooltip);
    },

    /**
     * For internal use by plugins, if needed
     *
     * @return {core}
     * @protected
     */
    _off: function() {
        this.__$emitterPrivate.off.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
        return this;
    },

    /**
     * For internal use by plugins, if needed
     *
     * @return {core}
     * @protected
     */
    _on: function() {
        this.__$emitterPrivate.on.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
        return this;
    },

    /**
     * For internal use by plugins, if needed
     *
     * @return {core}
     * @protected
     */
    _one: function() {
        this.__$emitterPrivate.one.apply(this.__$emitterPrivate, Array.prototype.slice.apply(arguments));
        return this;
    },

    /**
     * Returns (getter) or adds (setter) a plugin
     *
     * @param {string|object} plugin Provide a string (in the full form
     * "namespace.name") to use as as getter, an object to use as a setter
     * @return {object|core}
     * @protected
     */
    _plugin: function(plugin) {
        var self = this;

        // getter
        if (typeof plugin == 'string') {
            var pluginName = plugin,
                p = null;

            // if the namespace is provided, it's easy to search
            if (pluginName.indexOf('.') > 0) {
                p = self.__plugins[pluginName];
            } else {
                // otherwise, return the first name that matches
                $.each(self.__plugins, function(i, plugin) {
                    if (plugin.name.substring(plugin.name.length - pluginName.length - 1) == '.' + pluginName) {
                        p = plugin;
                        return false;
                    }
                });
            }

            return p;
        } else {
            // force namespaces
            if (plugin.name.indexOf('.') < 0)
                throw new Error('Plugins must be namespaced');

            self.__plugins[plugin.name] = plugin;

            // if the plugin has core features
            if (plugin.core) {
                // bridge non-private methods onto the core to allow new core methods
                self.__bridge(plugin.core, self, plugin.name);
            }

            return this;
        }
    },

    /**
     * Trigger events on the core emitters
     *
     * @returns {core}
     * @protected
     */
    _trigger: function() {
        var args = Array.prototype.slice.apply(arguments);

        if (typeof args[0] == 'string')
            args[0] = { type: args[0] };

        // note: the order of emitters matters
        this.__$emitterPrivate.trigger.apply(this.__$emitterPrivate, args);
        this.__$emitterPublic.trigger.apply(this.__$emitterPublic, args);

        return this;
    },

    /**
     * Returns instances of all tooltips in the page or an a given element
     *
     * @param {string|HTML object collection} selector optional Use this
     * parameter to restrict the set of objects that will be inspected
     * for the retrieval of instances. By default, all instances in the
     * page are returned.
     * @return {array} An array of instance objects
     * @public
     */
    instances: function(selector) {
        var instances = [],
            sel = selector || '.tooltipstered';

        $(sel).each(function() {
            var $this = $(this),
                ns = $this.data('tooltipster-ns');

            if (ns) {
                $.each(ns, function(i, namespace) {
                    instances.push($this.data(namespace));
                });
            }
        });

        return instances;
    },

    /**
     * Returns the Tooltipster objects generated by the last initializing call
     *
     * @return {array} An array of instance objects
     * @public
     */
    instancesLatest: function() {
        return this.__instancesLatestArr;
    },

    /**
     * For public use only, not to be used by plugins (use ::_off() instead)
     *
     * @return {core}
     * @public
     */
    off: function() {
        this.__$emitterPublic.off.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
        return this;
    },

    /**
     * For public use only, not to be used by plugins (use ::_on() instead)
     *
     * @return {core}
     * @public
     */
    on: function() {
        this.__$emitterPublic.on.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
        return this;
    },

    /**
     * For public use only, not to be used by plugins (use ::_one() instead)
     *
     * @return {core}
     * @public
     */
    one: function() {
        this.__$emitterPublic.one.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
        return this;
    },

    /**
     * Returns all HTML elements which have one or more tooltips
     *
     * @param {string} selector optional Use this to restrict the results
     * to the descendants of an element
     * @return {array} An array of HTML elements
     * @public
     */
    origins: function(selector) {
        var sel = selector ? selector + ' ' : '';

        return $(sel + '.tooltipstered').toArray();
    },

    /**
     * Change default options for all future instances
     *
     * @param {object} d The options that should be made defaults
     * @return {core}
     * @public
     */
    setDefaults: function(d) {
        $.extend(defaults, d);
        return this;
    },

    /**
     * For users to trigger their handlers on the public emitter
     *
     * @returns {core}
     * @public
     */
    triggerHandler: function() {
        this.__$emitterPublic.triggerHandler.apply(this.__$emitterPublic, Array.prototype.slice.apply(arguments));
        return this;
    }
};

// $.tooltipster will be used to call core methods
$.tooltipster = new core();

// the Tooltipster instance class (mind the capital T)
$.Tooltipster = function(element, options) {
    // list of instance variables

    // stack of custom callbacks provided as parameters to API methods
    this.__callbacks = {
        close: [],
        open: []
    };

    // the schedule time of DOM removal
    this.__closingTime;
    // this will be the user content shown in the tooltip. A capital "C" is used
    // because there is also a method called content()
    this.__Content;
    // for the size tracker
    this.__contentBcr;
    // to disable the tooltip once the destruction has begun
    this.__destroyed = false;
    this.__destroying = false;
    // we can't emit directly on the instance because if a method with the same
    // name as the event exists, it will be called by jQuery. Se we use a plain
    // object as emitter. This emitter is for internal use by plugins,
    // if needed.
    this.__$emitterPrivate = $({});
    // this emitter is for the user to listen to events without risking to mess
    // with our internal listeners
    this.__$emitterPublic = $({});
    this.__enabled = true;
    // the reference to the gc interval
    this.__garbageCollector;
    // various position and size data recomputed before each repositioning
    this.__Geometry;
    // the tooltip position, saved after each repositioning by a plugin
    this.__lastPosition;
    // a unique namespace per instance
    this.__namespace = 'tooltipster-'+ Math.round(Math.random()*1000000);
    this.__options;
    // will be used to support origins in scrollable areas
    this.__$originParents;
    this.__pointerIsOverOrigin = false;
    // to remove themes if needed
    this.__previousThemes = [];
    // the state can be either: appearing, stable, disappearing, closed
    this.__state = 'closed';
    // timeout references
    this.__timeouts = {
        close: [],
        open: null
    };
    // store touch events to be able to detect emulated mouse events
    this.__touchEvents = [];
    // the reference to the tracker interval
    this.__tracker = null;
    // the element to which this tooltip is associated
    this._$origin;
    // this will be the tooltip element (jQuery wrapped HTML element).
    // It's the job of a plugin to create it and append it to the DOM
    this._$tooltip;

    // launch
    this.__init(element, options);
};

$.Tooltipster.prototype = {
    /**
     * @param origin
     * @param options
     * @private
     */
    __init: function(origin, options) {
        var self = this;

        self._$origin = $(origin);
        self.__options = $.extend(true, {}, defaults, options);

        // some options may need to be reformatted
        self.__optionsFormat();

        // don't run on old IE if asked no to
        if (!env.IE || env.IE >= self.__options.IEmin) {
            // note: the content is null (empty) by default and can stay that
            // way if the plugin remains initialized but not fed any content. The
            // tooltip will just not appear.

            // let's save the initial value of the title attribute for later
            // restoration if need be.
            var initialTitle = null;

            // it will already have been saved in case of multiple tooltips
            if (self._$origin.data('tooltipster-initialTitle') === undefined) {
                initialTitle = self._$origin.attr('title');

                // we do not want initialTitle to be "undefined" because
                // of how jQuery's .data() method works
                if (initialTitle === undefined)
                    initialTitle = null;

                self._$origin.data('tooltipster-initialTitle', initialTitle);
            }

            // If content is provided in the options, it has precedence over the
            // title attribute.
            // Note: an empty string is considered content, only 'null' represents
            // the absence of content.
            // Also, an existing title="" attribute will result in an empty string
            // content
            if (self.__options.content !== null) {
                self.__contentSet(self.__options.content);
            } else {
                var selector = self._$origin.attr('data-tooltip-content'), $el;

                if (selector)
                    $el = $(selector);

                if ($el && $el[0])
                    self.__contentSet($el.first());
                else
                    self.__contentSet(initialTitle);
            }

            self._$origin
                // strip the title off of the element to prevent the default tooltips
                // from popping up
                .removeAttr('title')
                // to be able to find all instances on the page later (upon window
                // events in particular)
                .addClass('tooltipstered');

            // set listeners on the origin
            self.__prepareOrigin();

            // set the garbage collector
            self.__prepareGC();

            // init plugins
            $.each(self.__options.plugins, function(i, pluginName) {
                self._plug(pluginName);
            });

            // to detect swiping
            if (env.hasTouchCapability) {
                $('body').on('touchmove.'+ self.__namespace +'-triggerOpen', function(event) {
                    self._touchRecordEvent(event);
                });
            }

            self
            // prepare the tooltip when it gets created. This event must
            // be fired by a plugin
                ._on('created', function() {
                    self.__prepareTooltip();
                })
                // save position information when it's sent by a plugin
                ._on('repositioned', function(e) {
                    self.__lastPosition = e.position;
                });
        } else {
            self.__options.disabled = true;
        }
    },

    /**
     * Insert the content into the appropriate HTML element of the tooltip
     *
     * @returns {self}
     * @private
     */
    __contentInsert: function() {
        var self = this,
            $el = self._$tooltip.find('.tooltipster-content'),
            formattedContent = self.__Content,
            format = function(content) {
                formattedContent = content;
            };

        self._trigger({
            type: 'format',
            content: self.__Content,
            format: format
        });

        if (self.__options.functionFormat) {
            formattedContent = self.__options.functionFormat.call(
                self,
                self,
                { origin: self._$origin[0] },
                self.__Content
            );
        }

        if (typeof formattedContent === 'string' && !self.__options.contentAsHTML)
            $el.text(formattedContent);
        else
            $el.empty().append(formattedContent);

        return self;
    },

    /**
     * Save the content, cloning it beforehand if need be
     *
     * @param content
     * @returns {self}
     * @private
     */
    __contentSet: function(content) {

        // clone if asked. Cloning the object makes sure that each instance has its
        // own version of the content (in case a same object were provided for several
        // instances)
        // reminder: typeof null === object
        if (content instanceof $ && this.__options.contentCloning)
            content = content.clone(true);

        this.__Content = content;

        this._trigger({
            type: 'updated',
            content: content
        });

        return this;
    },

    /**
     * Error message about a method call made after destruction
     *
     * @private
     */
    __destroyError: function() {
        throw new Error('This tooltip has been destroyed and cannot execute your method call.');
    },

    /**
     * Gather all information about dimensions and available space,
     * called before every repositioning
     *
     * @private
     * @returns {object}
     */
    __geometry: function() {
        var self = this,
            $target = self._$origin,
            originIsArea = self._$origin.is('area');

        // if this._$origin is a map area, the target we'll need
        // the dimensions of is actually the image using the map,
        // not the area itself
        if (originIsArea) {
            var mapName = self._$origin.parent().attr('name');

            $target = $('img[usemap="#' + mapName + '"]');
        }

        var bcr = $target[0].getBoundingClientRect(),
            $document = $(env.window.document),
            $window = $(env.window),
            $parent = $target,
            // some useful properties of important elements
            geo = {
                // available space for the tooltip, see down below
                available: {
                    document: null,
                    window: null
                },
                document: {
                    size: {
                        height: $document.height(),
                        width: $document.width()
                    }
                },
                window: {
                    scroll: {
                        // the second ones are for IE compatibility
                        left: env.window.scrollX || env.window.document.documentElement.scrollLeft,
                        top: env.window.scrollY || env.window.document.documentElement.scrollTop
                    },
                    size: {
                        height: $window.height(),
                        width: $window.width()
                    }
                },
                origin: {
                    // the origin has a fixed lineage if itself or one of its
                    // ancestors has a fixed position
                    fixedLineage: false,
                    // relative to the document
                    offset: {},
                    size: {
                        height: bcr.bottom - bcr.top,
                        width: bcr.right - bcr.left
                    },
                    usemapImage: originIsArea ? $target[0] : null,
                    // relative to the window
                    windowOffset: {
                        bottom: bcr.bottom,
                        left: bcr.left,
                        right: bcr.right,
                        top: bcr.top
                    }
                }
            },
            geoFixed = false;

        // if the element is a map area, some properties may need
        // to be recalculated
        if (originIsArea) {
            var shape = self._$origin.attr('shape'),
                coords = self._$origin.attr('coords');

            if (coords) {
                coords = coords.split(',');

                $.map(coords, function(val, i) {
                    coords[i] = parseInt(val);
                });
            }

            // if the image itself is the area, nothing more to do
            if (shape != 'default') {
                switch (shape) {
                    case 'circle':

                        var circleCenterLeft = coords[0],
                            circleCenterTop = coords[1],
                            circleRadius = coords[2],
                            areaTopOffset = circleCenterTop - circleRadius,
                            areaLeftOffset = circleCenterLeft - circleRadius;

                        geo.origin.size.height = circleRadius * 2;
                        geo.origin.size.width = geo.origin.size.height;

                        geo.origin.windowOffset.left += areaLeftOffset;
                        geo.origin.windowOffset.top += areaTopOffset;

                        break;

                    case 'rect':

                        var areaLeft = coords[0],
                            areaTop = coords[1],
                            areaRight = coords[2],
                            areaBottom = coords[3];

                        geo.origin.size.height = areaBottom - areaTop;
                        geo.origin.size.width = areaRight - areaLeft;

                        geo.origin.windowOffset.left += areaLeft;
                        geo.origin.windowOffset.top += areaTop;

                        break;

                    case 'poly':
                        var areaSmallestX = 0,
                            areaSmallestY = 0,
                            areaGreatestX = 0,
                            areaGreatestY = 0,
                            arrayAlternate = 'even';

                        for (var i = 0; i < coords.length; i++) {
                            var areaNumber = coords[i];

                            if (arrayAlternate == 'even') {
                                if (areaNumber > areaGreatestX) {
                                    areaGreatestX = areaNumber;

                                    if (i === 0)
                                        areaSmallestX = areaGreatestX;
                                }

                                if (areaNumber < areaSmallestX)
                                    areaSmallestX = areaNumber;

                                arrayAlternate = 'odd';
                            } else {
                                if (areaNumber > areaGreatestY) {
                                    areaGreatestY = areaNumber;

                                    if (i == 1)
                                        areaSmallestY = areaGreatestY;
                                }

                                if (areaNumber < areaSmallestY)
                                    areaSmallestY = areaNumber;

                                arrayAlternate = 'even';
                            }
                        }

                        geo.origin.size.height = areaGreatestY - areaSmallestY;
                        geo.origin.size.width = areaGreatestX - areaSmallestX;

                        geo.origin.windowOffset.left += areaSmallestX;
                        geo.origin.windowOffset.top += areaSmallestY;

                        break;
                }
            }
        }

        // user callback through an event
        var edit = function(r) {
            geo.origin.size.height = r.height,
                geo.origin.windowOffset.left = r.left,
                geo.origin.windowOffset.top = r.top,
                geo.origin.size.width = r.width;
        };

        self._trigger({
            type: 'geometry',
            edit: edit,
            geometry: {
                height: geo.origin.size.height,
                left: geo.origin.windowOffset.left,
                top: geo.origin.windowOffset.top,
                width: geo.origin.size.width
            }
        });

        // calculate the remaining properties with what we got

        geo.origin.windowOffset.right = geo.origin.windowOffset.left + geo.origin.size.width;
        geo.origin.windowOffset.bottom = geo.origin.windowOffset.top + geo.origin.size.height;

        geo.origin.offset.left = geo.origin.windowOffset.left + geo.window.scroll.left;
        geo.origin.offset.top = geo.origin.windowOffset.top + geo.window.scroll.top;
        geo.origin.offset.bottom = geo.origin.offset.top + geo.origin.size.height;
        geo.origin.offset.right = geo.origin.offset.left + geo.origin.size.width;

        // the space that is available to display the tooltip relatively to the document
        geo.available.document = {
            bottom: {
                height: geo.document.size.height - geo.origin.offset.bottom,
                width: geo.document.size.width
            },
            left: {
                height: geo.document.size.height,
                width: geo.origin.offset.left
            },
            right: {
                height: geo.document.size.height,
                width: geo.document.size.width - geo.origin.offset.right
            },
            top: {
                height: geo.origin.offset.top,
                width: geo.document.size.width
            }
        };

        // the space that is available to display the tooltip relatively to the viewport
        // (the resulting values may be negative if the origin overflows the viewport)
        geo.available.window = {
            bottom: {
                // the inner max is here to make sure the available height is no bigger
                // than the viewport height (when the origin is off screen at the top).
                // The outer max just makes sure that the height is not negative (when
                // the origin overflows at the bottom).
                height: Math.max(geo.window.size.height - Math.max(geo.origin.windowOffset.bottom, 0), 0),
                width: geo.window.size.width
            },
            left: {
                height: geo.window.size.height,
                width: Math.max(geo.origin.windowOffset.left, 0)
            },
            right: {
                height: geo.window.size.height,
                width: Math.max(geo.window.size.width - Math.max(geo.origin.windowOffset.right, 0), 0)
            },
            top: {
                height: Math.max(geo.origin.windowOffset.top, 0),
                width: geo.window.size.width
            }
        };

        while ($parent[0].tagName.toLowerCase() != 'html') {
            if ($parent.css('position') == 'fixed') {
                geo.origin.fixedLineage = true;
                break;
            }

            $parent = $parent.parent();
        }

        return geo;
    },

    /**
     * Some options may need to be formated before being used
     *
     * @returns {self}
     * @private
     */
    __optionsFormat: function() {
        if (typeof this.__options.animationDuration == 'number')
            this.__options.animationDuration = [this.__options.animationDuration, this.__options.animationDuration];

        if (typeof this.__options.delay == 'number')
            this.__options.delay = [this.__options.delay, this.__options.delay];

        if (typeof this.__options.delayTouch == 'number')
            this.__options.delayTouch = [this.__options.delayTouch, this.__options.delayTouch];

        if (typeof this.__options.theme == 'string')
            this.__options.theme = [this.__options.theme];

        // determine the future parent
        if (typeof this.__options.parent == 'string')
            this.__options.parent = $(this.__options.parent);

        if (this.__options.trigger == 'hover') {
            this.__options.triggerOpen = {
                mouseenter: true,
                touchstart: true
            };

            this.__options.triggerClose = {
                mouseleave: true,
                originClick: true,
                touchleave: true
            };
        } else if (this.__options.trigger == 'click') {
            this.__options.triggerOpen = {
                click: true,
                tap: true
            };

            this.__options.triggerClose = {
                click: true,
                tap: true
            };
        }

        // for the plugins
        this._trigger('options');

        return this;
    },

    /**
     * Schedules or cancels the garbage collector task
     *
     * @returns {self}
     * @private
     */
    __prepareGC: function() {
        var self = this;

        // in case the selfDestruction option has been changed by a method call
        if (self.__options.selfDestruction) {
            // the GC task
            self.__garbageCollector = setInterval(function() {
                var now = new Date().getTime();

                // forget the old events
                self.__touchEvents = $.grep(self.__touchEvents, function(event, i) {
                    // 1 minute
                    return now - event.time > 60000;
                });

                // auto-detect if the origin is gone
                if (!bodyContains(self._$origin))
                    self.destroy();
            }, 20000);
        } else {
            clearInterval(self.__garbageCollector);
        }

        return self;
    },

    /**
     * Sets listeners on the origin if the open triggers require them.
     * Unlike the listeners set at opening time, these ones
     * remain even when the tooltip is closed. It has been made a
     * separate method so it can be called when the triggers are
     * changed in the options. Closing is handled in _open()
     * because of the bindings that may be needed on the tooltip
     * itself
     *
     * @returns {self}
     * @private
     */
    __prepareOrigin: function() {
        var self = this;

        // in case we're resetting the triggers
        self._$origin.off('.'+ self.__namespace +'-triggerOpen');

        // if the device is touch capable, even if only mouse triggers
        // are asked, we need to listen to touch events to know if the mouse
        // events are actually emulated (so we can ignore them)
        if (env.hasTouchCapability) {
            self._$origin.on(
                'touchstart.'+ self.__namespace +'-triggerOpen ' +
                'touchend.'+ self.__namespace +'-triggerOpen ' +
                'touchcancel.'+ self.__namespace +'-triggerOpen',
                function(event) {
                    self._touchRecordEvent(event);
                }
            );
        }

        // mouse click and touch tap work the same way
        if (self.__options.triggerOpen.click || (self.__options.triggerOpen.tap && env.hasTouchCapability)) {
            var eventNames = '';
            if (self.__options.triggerOpen.click)
                eventNames += 'click.'+ self.__namespace +'-triggerOpen ';
            if (self.__options.triggerOpen.tap && env.hasTouchCapability)
                eventNames += 'touchend.'+ self.__namespace +'-triggerOpen';

            self._$origin.on(eventNames, function(event) {
                if (self._touchIsMeaningfulEvent(event))
                    self._open(event);
            });
        }

        // mouseenter and touch start work the same way
        if (self.__options.triggerOpen.mouseenter || (self.__options.triggerOpen.touchstart && env.hasTouchCapability)) {
            var eventNames = '';
            if (self.__options.triggerOpen.mouseenter)
                eventNames += 'mouseenter.'+ self.__namespace +'-triggerOpen ';
            if (self.__options.triggerOpen.touchstart && env.hasTouchCapability)
                eventNames += 'touchstart.'+ self.__namespace +'-triggerOpen';

            self._$origin.on(eventNames, function(event) {
                if (self._touchIsTouchEvent(event) || !self._touchIsEmulatedEvent(event)) {
                    self.__pointerIsOverOrigin = true;
                    self._openShortly(event);
                }
            });
        }

        // info for the mouseleave/touchleave close triggers when they use a delay
        if (self.__options.triggerClose.mouseleave || (self.__options.triggerClose.touchleave && env.hasTouchCapability)) {
            var eventNames = '';
            if (self.__options.triggerClose.mouseleave)
                eventNames += 'mouseleave.'+ self.__namespace +'-triggerOpen ';
            if (self.__options.triggerClose.touchleave && env.hasTouchCapability)
                eventNames += 'touchend.'+ self.__namespace +'-triggerOpen touchcancel.'+ self.__namespace +'-triggerOpen';

            self._$origin.on(eventNames, function(event) {
                if (self._touchIsMeaningfulEvent(event))
                    self.__pointerIsOverOrigin = false;
            });
        }

        return self;
    },

    /**
     * Do the things that need to be done only once after the tooltip
     * HTML element it has been created. It has been made a separate
     * method so it can be called when options are changed. Remember
     * that the tooltip may actually exist in the DOM before it is
     * opened, and present after it has been closed: it's the display
     * plugin that takes care of handling it.
     *
     * @returns {self}
     * @private
     */
    __prepareTooltip: function() {
        var self = this,
            p = self.__options.interactive ? 'auto' : '';

        // this will be useful to know quickly if the tooltip is in
        // the DOM or not
        self._$tooltip.attr('id', self.__namespace).css({
            // pointer events
            'pointer-events': p,
            zIndex: self.__options.zIndex
        });

        // theme
        // remove the old ones and add the new ones
        $.each(self.__previousThemes, function(i, theme) {
            self._$tooltip.removeClass(theme);
        });
        $.each(self.__options.theme, function(i, theme) {
            self._$tooltip.addClass(theme);
        });

        self.__previousThemes = $.merge([], self.__options.theme);

        return self;
    },

    /**
     * Handles the scroll on any of the parents of the origin (when the
     * tooltip is open)
     *
     * @param {object} event
     * @returns {self}
     * @private
     */
    __scrollHandler: function(event) {
        var self = this;

        if (self.__options.triggerClose.scroll) {
            self._close(event);
        } else {
            // if the scroll happened on the window
            if (event.target === env.window.document) {
                // if the origin has a fixed lineage, window scroll will have no
                // effect on its position nor on the position of the tooltip
                if (!self.__Geometry.origin.fixedLineage) {
                    // we don't need to do anything unless repositionOnScroll is true
                    // because the tooltip will already have moved with the window
                    // (and of course with the origin)
                    if (self.__options.repositionOnScroll)
                        self.reposition(event);
                }
            } else {
                // if the scroll happened on another parent of the tooltip, it means
                // that it's in a scrollable area and now needs to have its position
                // adjusted or recomputed, depending ont the repositionOnScroll
                // option. Also, if the origin is partly hidden due to a parent that
                // hides its overflow, we'll just hide (not close) the tooltip.

                var g = self.__geometry(),
                    overflows = false;

                // a fixed position origin is not affected by the overflow hiding
                // of a parent
                if (self._$origin.css('position') != 'fixed') {
                    self.__$originParents.each(function(i, el) {
                        var $el = $(el),
                            overflowX = $el.css('overflow-x'),
                            overflowY = $el.css('overflow-y');

                        if (overflowX != 'visible' || overflowY != 'visible') {
                            var bcr = el.getBoundingClientRect();

                            if (overflowX != 'visible') {
                                if (g.origin.windowOffset.left < bcr.left || g.origin.windowOffset.right > bcr.right) {
                                    overflows = true;
                                    return false;
                                }
                            }

                            if (overflowY != 'visible') {
                                if (g.origin.windowOffset.top < bcr.top || g.origin.windowOffset.bottom > bcr.bottom) {
                                    overflows = true;
                                    return false;
                                }
                            }
                        }

                        // no need to go further if fixed, for the same reason as above
                        if ($el.css('position') == 'fixed')
                            return false;
                    });
                }

                if (overflows) {
                    self._$tooltip.css('visibility', 'hidden');
                } else {
                    self._$tooltip.css('visibility', 'visible');

                    // reposition
                    if (self.__options.repositionOnScroll) {
                        self.reposition(event);
                    } else {
                        // or just adjust offset

                        // we have to use offset and not windowOffset because this way,
                        // only the scroll distance of the scrollable areas are taken into
                        // account (the scrolltop value of the main window must be
                        // ignored since the tooltip already moves with it)
                        var offsetLeft = g.origin.offset.left - self.__Geometry.origin.offset.left,
                            offsetTop = g.origin.offset.top - self.__Geometry.origin.offset.top;

                        // add the offset to the position initially computed by the display plugin
                        self._$tooltip.css({
                            left: self.__lastPosition.coord.left + offsetLeft,
                            top: self.__lastPosition.coord.top + offsetTop
                        });
                    }
                }
            }

            self._trigger({
                type: 'scroll',
                event: event
            });
        }

        return self;
    },

    /**
     * Changes the state of the tooltip
     *
     * @param {string} state
     * @returns {self}
     * @private
     */
    __stateSet: function(state) {
        this.__state = state;

        this._trigger({
            type: 'state',
            state: state
        });

        return this;
    },

    /**
     * Clear appearance timeouts
     *
     * @returns {self}
     * @private
     */
    __timeoutsClear: function() {
        // there is only one possible open timeout: the delayed opening
        // when the mouseenter/touchstart open triggers are used
        clearTimeout(this.__timeouts.open);
        this.__timeouts.open = null;

        // ... but several close timeouts: the delayed closing when the
        // mouseleave close trigger is used and the timer option
        $.each(this.__timeouts.close, function(i, timeout) {
            clearTimeout(timeout);
        });
        this.__timeouts.close = [];

        return this;
    }
};