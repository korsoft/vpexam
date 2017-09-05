(function($) {
    $(function() {
        $.widget("zpd.paging", {
            options: {
                limit: 5,
                rowDisplayStyle: 'block',
                activePage: 0,
                rows: []
            },

            _create: function() {
                var rows = $("tbody", this.element).children();
                this.options.rows = rows;
                this.options.rowDisplayStyle = rows.css('display');
                var nav = this._getNavBar();
                this.element.after(nav);
                this.showPage(0);
            },

            _getNavBar: function() {
                var rows = this.options.rows;
                var nav = $('<div>', {class: 'paging-nav'});
                for (var i = 0; i < Math.ceil(rows.length / this.options.limit); i++) {
                    this._on($('<a>', {
                        href: '#',
                        text: (i + 1),
                        "data-page": (i)
                    }).appendTo(nav), {click: "pageClickHandler"});
                }

                // Create previous link
                this._on($('<a>', {
                    href: '#',
                    text: '<<',
                    "data-direction": -1
                }).prependTo(nav), {click: "pageStepHandler"});

                // Create next link
                this._on($('<a>', {
                    href: '#',
                    text: '>>',
                    "data-direction": +1
                }).appendTo(nav), {click: "pageStepHandler"});
                return nav;
            },

            showPage: function(pageNum) {
                var num = pageNum * 1;  // It has to be numeric
                this.options.activePage = num;
                var rows = this.options.rows;
                var limit = this.options.limit;
                for (var i = 0; i < rows.length; i++) {
                    if (i >= limit * num && i < limit * (num + 1))
                        $(rows[i]).css('display', this.options.rowDisplayStyle);
                    else
                        $(rows[i]).css('display', 'none');
                }
            },

            pageClickHandler: function(event) {
                event.preventDefault();
                $(event.target).siblings().attr('class', "");
                $(event.target).attr('class', "selected-page");
                var pageNum = $(event.target).attr('data-page');
                this.showPage(pageNum);
            },

            pageStepHandler: function(event) {
                event.preventDefault();
                // Get the direction and ensure it's numeric
                var dir = $(event.targedt).attr('data-direction') * 1;
                var pageNum = this.options.activePage + dir;
                // If we're in limit, trigger the requested pages link
                if (pageNum >= 0 && pageNum < this.options.rows.length)
                    $("a[data-page=" + pageNum + "]", $(event.target).parent()).click();
            }
        });
    });
})(jQuery);