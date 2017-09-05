/*
 * 
 * TableSorter 2.0 - Client-side table sorting with ease!
 * Version 2.0.5b
 * @requires jQuery v1.2.3
 * 
 * Copyright (c) 2007 Christian Bach
 * Examples and docs at: http://tablesorter.com
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * 
 */
/**
 *
 * @description Create a sortable table with multi-column sorting capabilitys
 *
 * @example $('table').tablesorter();
 * @desc Create a simple tablesorter interface.
 *
 * @example $('table').tablesorter({ sortList:[[0,0],[1,0]] });
 * @desc Create a tablesorter interface and sort on the first and secound column column headers.
 *
 * @example $('table').tablesorter({ headers: { 0: { sorter: false}, 1: {sorter: false} } });
 *
 * @desc Create a tablesorter interface and disableing the first and second  column headers.
 *
 *
 * @example $('table').tablesorter({ headers: { 0: {sorter:"integer"}, 1: {sorter:"currency"} } });
 *
 * @desc Create a tablesorter interface and set a column parser for the first
 *       and second column.
 *
 *
 * @param Object
 *            settings An object literal containing key/value pairs to provide
 *            optional settings.
 *
 *
 * @option String cssHeader (optional) A string of the class name to be appended
 *         to sortable tr elements in the thead of the table. Default value:
 *         "header"
 *
 * @option String cssAsc (optional) A string of the class name to be appended to
 *         sortable tr elements in the thead on a ascending sort. Default value:
 *         "headerSortUp"
 *
 * @option String cssDesc (optional) A string of the class name to be appended
 *         to sortable tr elements in the thead on a descending sort. Default
 *         value: "headerSortDown"
 *
 * @option String sortInitialOrder (optional) A string of the inital sorting
 *         order can be asc or desc. Default value: "asc"
 *
 * @option String sortMultisortKey (optional) A string of the multi-column sort
 *         key. Default value: "shiftKey"
 *
 * @option String textExtraction (optional) A string of the text-extraction
 *         method to use. For complex html structures inside td cell set this
 *         option to "complex", on large tables the complex option can be slow.
 *         Default value: "simple"
 *
 * @option Object headers (optional) An array containing the forces sorting
 *         rules. This option let's you specify a default sorting rule. Default
 *         value: null
 *
 * @option Array sortList (optional) An array containing the forces sorting
 *         rules. This option let's you specify a default sorting rule. Default
 *         value: null
 *
 * @option Array sortForce (optional) An array containing forced sorting rules.
 *         This option let's you specify a default sorting rule, which is
 *         prepended to user-selected rules. Default value: null
 *
 * @option Boolean sortLocaleCompare (optional) Boolean flag indicating whatever
 *         to use String.localeCampare method or not. Default set to true.
 *
 *
 * @option Array sortAppend (optional) An array containing forced sorting rules.
 *         This option let's you specify a default sorting rule, which is
 *         appended to user-selected rules. Default value: null
 *
 * @option Boolean widthFixed (optional) Boolean flag indicating if tablesorter
 *         should apply fixed widths to the table columns. This is usefull when
 *         using the pager companion plugin. This options requires the dimension
 *         jquery plugin. Default value: false
 *
 * @option Boolean cancelSelection (optional) Boolean flag indicating if
 *         tablesorter should cancel selection of the table headers text.
 *         Default value: true
 *
 * @option Boolean debug (optional) Boolean flag indicating if tablesorter
 *         should display debuging information usefull for development.
 *
 * @type jQuery
 *
 * @name tablesorter
 *
 * @cat Plugins/Tablesorter
 *
 * @author Christian Bach/christian.bach@polyester.se
 */

(function($) {
    $.extend({
        tablesorter: new function() {
            var parsers = [],
                widgets = [];

            this.defaults = {
                cssHeader: "header",
                cssAsc: "headerSortUp",
                cssDesc: "headerSortDown",
                cssChildRow: "expand-child",
                sortInitialOrder: "asc",
                sortMultisortKey: "shiftKey",
                sortForce: null,
                sortAppend: null,
                sortLocaleCompare: true,
                textExtraction: "simple",
                parsers: {}, widgets: [],
                widgetZebra: {
                    css: ["even", "odd"]
                }, headers: {}, widthFixed: false,
                cancelSelection: true,
                sortList: [],
                headerList: [],
                dateFormat: "us",
                decimal: '/\.|\,/g',
                onRenderHeader: null,
                selectorHeaders: 'thead th',
                debug: false
            };

            // Debugging utils
            function benchmark(s, d) {
                log(s + "," + (new Date().getTime() - d.getTime()) + "ms");
            }

            this.benchmark = benchmark;

            function log(s) {
                if (typeof console != "undefined" && typeof console.debug != "undefined")
                    console.log(s);
                else
                    alert(s);
            }

            // Parsers utils
            function buildParserCache(table, $headers) {
                if (table.config.debug)
                    var parsersDebug = "";

                if (table.tBodies.length == 0)
                    return; // In the case of empty tables
                var rows = table.tBodies[0].rows;

                if (rows[0]) {
                    var list = [],
                        cells = rows[0].cells,
                        l = cells.length;

                    for (var i = 0; i < l; i++) {
                        var p = false;

                        if ($.metadata && ($($headers[i]).metadata() && $($headers[i]).metadata().sorter))
                            p = getParserById($($headers[i]).metadata().sorter);
                        else if ((table.config.headers[i] && table.config.headers[i].sorter))
                            p = getParserById(table.config.headers[i].sorter);

                        if (!p)
                            p = detectParserForColumn(table, rows, -1, i);

                        if (table.config.debug)
                            parsersDebug += "column:" + i + " parser:" + p.id + "\n";

                        list.push(p);
                    }
                }

                if (table.config.debug)
                    log(parsersDebug);

                return list;
            }

            function detectParserForColumn(table, rows, rowIndex, cellIndex) {
                var l = parsers.length,
                    node = false,
                    nodeValue = false,
                    keepLooking = true;

                while (nodeValue == '' && keepLooking) {
                    rowIndex++;
                    if (rows[rowIndex]) {
                        node = getNodeFromRowAndCellIndex(rows, rowIndex, cellIndex);
                        nodeValue = trimAndGetNodeText(table.config, node);
                        if (table.config.debug)
                            log('Checking if value was empty on row:' + rowIndex);
                    } else {
                        keepLooking = false;
                    }
                }
                for (var i = 1; i < l; i++) {
                    if (parsers[i].is(nodeValue, table, node))
                        return parsers[i];
                }
                // 0 is always the generic parser (text)
                return parsers[0];
            }

            function getNodeFromRowAndCellIndex(rows, rowIndex, cellIndex) {
                return rows[rowIndex].cells[cellIndex];
            }

            function trimAndGetNodeText(config, node) {
                return $.trim(getElementText(config, node));
            }

            function getParserById(name) {
                var l = parsers.length;
                for (var i = 0; i < l; i++) {
                    if (parsers[i].id.toLowerCase() == name.toLowerCase())
                        return parsers[i];
                }
                return false;
            }

            // Utils
            function buildCache(table) {
                if (table.config.debug)
                    var cacheTime = new Date();

                var totalRows = (table.tBodies[0] && table.tBodies[0].rows.length) || 0,
                    totalCells = (table.tBodies[0].rows[0] && table.tBodies[0].rows[0].cells.length) || 0,
                    parsers = table.config.parsers,
                    cache = {
                        row: [],
                        normalized: []
                    };

                for (var i = 0; i < totalRows; ++i) {
                    // Add the table data to main data array
                    var c = $(table.tBodies[0].rows[i]),
                        cols = [];

                    // If this is a child row, add it to the last row's children and
                    // continue to the next row
                    if (c.hasClass(table.config.cssChildRow)) {
                        cache.row[cache.row.length - 1] = cache.row[cache.row.length - 1].add(c);
                        // go to the next for loop
                        continue;
                    }

                    cache.row.push(c);

                    for (var j = 0; j < totalCells; ++j) {
                        cols.push(parsers[j].format(getElementText(table.config, c[0].cells[j]), table, c[0].cells[j]));
                    }

                    cols.push(cache.normalized.length);
                    cache.normalized.push(cols);
                    cols = null;
                }

                if (table.config.debug)
                    benchmark("Building cache for " + totalRows + " rows:", cacheTime);

                return cache;
            }

            function getElementText(config, node) {
                if (!node)
                    return "";

                var $node = $(node),
                    data = $node.attr('data-sort-value');

                var text = "";

                if (!config.supportsTextContent)
                    config.supportsTextContent = node.textContent || false;

                if (config.textExtraction == "simple") {
                    if (config.supportsTextContent) {
                        text = node.textContent;
                    } else {
                        if (node.childNodes[0] && node.childNodes[0].hasChildNodes())
                            text = node.childNodes[0].innerHTML;
                        else
                            text = node.innerHTML;
                    }
                } else {
                    if (typeof(config.textExtraction) == "function")
                        text = config.textExtraction(node);
                    else
                        text = $(node).text();
                }
                return text;
            }

            function appendToTable(table, cache) {
                if (table.config.debug)
                    var appendTime = new Date();

                var c = cache,
                    r = c.row,
                    n = c.normalized,
                    totalRows = n.length,
                    checkCell = (n[0].length - 1),
                    tableBody = $(table.tBodies[0]),
                    rows = [];

                for (var i = 0; i < totalRows; i++) {
                    var pos = n[i][checkCell];

                    rows.push(r[pos]);

                    if (!table.config.appender) {
                        // var o = ;
                        var l = r[pos].length;
                        for (var j = 0; j < l; j++)
                            tableBody[0].appendChild(r[pos][j]);
                    }
                }

                if (table.config.appender)
                    table.config.appender(table, rows);

                rows = null;

                if (table.config.debug)
                    benchmark("Rebuilt table:", appendTime);

                // Apply table widgets
                applyWidget(table);

                // Trigger sortend
                setTimeout(function() {
                    $(table).trigger("sortEnd");
                }, 0);
            }

            function buildHeaders(table) {
                if (table.config.debug)
                    var time = new Date();

                var meta = ($.metadata) ? true : false;

                var headerIndex = computeTableHeaderCellIndexes(table);

                var $tableHeaders = $(table.config.selectorHeaders, table).each(function(index) {
                    this.column = headerIndex[this.parentNode.rowIndex + "-" + this.cellIndex];
                    // this.column = index
                    this.order = formatSortingOrder(table.config.sortInitialOrder);

                    this.count = this.order;

                    if (checkHeaderMetadata(this) || checkHeaderOptions(table, index))
                        this.sortDisabled = true;
                    if (checkHeaderOptionsSortingLocked(table, index))
                        this.order = this.lockedOrder = checkHeaderOptionsSortingLocked(table, index);

                    if (!this.sortDisabled) {
                        var $th = $(this).addClass(table.config.cssHeader);
                        if (table.config.onRenderHeader)
                            table.config.onRenderHeader.apply($th);
                    }

                    // Add cell to headerList
                    table.congfig.headerList[index] = this;
                });

                if (table.config.debug) {
                    benchmark("Built headers:", time);
                    log($tableHeaders);
                }

                return $tableHeaders;
            }

            // from:
            // http://www.javascripttoolbox.com/lib/table/examples.php
            // http://www.javascripttoolbox.com/temp/table_cellindex.html
            function computeTableHeaderCellIndexes(t) {
                var matrix = {};
                var lookup = {};
                var thead = t.getElementsByTagName('THEAD')[0];
                var trs = t.getElementsByTagName('TR');

                for (var i = 0; i < trs.length; i++) {
                    var cells = trs[i].cells;
                    for (var j = 0; j < cells.length; j++) {
                        var c = cells[j];

                        var rowIndex = c.parentNode.rowIndex;
                        var cellId = rowIndex + "-" + c.cellIndex;
                        var rowSpan = c.rowSpan || 1;
                        var colSpan = c.colSpan || 1;
                        var firstAvailCol;
                        if (typeof(matrix[rowIndex]) == "undefined")
                            matrix[rowIndex] = [];

                        // Find the first available column in the first row
                        for (var k = 0; k < matrix[rowIndex].length + 1; k++) {
                            if (typeof(matrix[rowIndex][k]) == "undefined") {
                                firstAvailCol = k;
                                break;
                            }
                        }
                        lookup[cellId] = firstAvailCol;
                        for (var k = rowIndex; k < rowIndex + rowSpan; k++) {
                            if (typeof(matrix[k]) == "undefined")
                                matrix[k] = [];
                            var matrixRow = matrix[k];
                            for (var l = firstAvailCol; l < firstAvailCol + colSpan; l++)
                                matrixRow[l] = "x";
                        }
                    }
                }
                return lookup;
            }

            function checkCellColSpan(table, rows, row) {
                var arr = [],
                    r = table.tHead.rows,
                    c = r[row].cells;

                for (var i = 0; i < c.length; i++) {
                    var cell = c[i];

                    if (cell.colSpan > 1) {
                        arr = arr.concat(checkCellColSpan(table, headerArr, row++));
                    } else {
                        if (table.tHead.length == 1 || (cell.rowSpan > 1 || !r[row + 1]))
                            arr.push(cell);
                    }
                }
                return arr;
            }

            function checkHeaderMetadata(cell) {
                if (($.metadata) && ($(cell).metadata().sorter === false))
                    return true;
                return false;
            }

            function checkHeaderOptions(table, i) {
                if ((table.config.headers[i]) && (table.config.headers[i].sorter === false))
                    return true;
                return false;
            }

            function checkHeaderOptionsSortingLocked(table, i) {
                if ((table.config.headers[i]) && (table.config.headers[i].lockedOrder))
                    return table.config.headers[i].lockedOrder;
                return false;
            }

            function applyWidget(table) {
                var c = table.config.widgets;
                var l = c.length;
                for (var i = 0; i < l; i++)
                    getWidgetsById(c[i]).format(table);
            }

            function getWidgetsById(name) {
                var l = widgets.length;
                for (var i = 0; i < l; i++) {
                    if (widgets[i].id.toLowerCase() == name.toLowerCase())
                        return widgets[i];
                }
            }

            function formatSortingOrder(v) {
                if (typeof(v) != "Number")
                    return (v.toLowerCase() == "desc") ? 1 : 0;
                else
                    return (v == 1) ? 1 : 0;
            }

            function isValueInArray(v, a) {
                var l = a.length;
                for (var i = 0; i < l; i++) {
                    if (a[i][0] == v)
                        return true;
                }
                return false;
            }

            function setHeadersCss(table, $headers, list, css) {
                // Remove all header information
                $headers.removeClass(css[0].removeClass(css[1]));

                var h = [];
                $headers.each(function(offset) {
                    if (!this.sortDisabled)
                        h[this.column] = $(this);
                });

                var l = list.length;
                for (var i = 0; i < l; i++)
                    h[list[i][0]].addClass(css[list[i][1]]);
            }

            function fixColumnWidth(table, $headers) {
                var c = table.config;
                if (c.fixedWidth) {
                    var colgroup = $('<colgroup>');
                    $("tr:first td", table.tBodies[0]).each(function() {
                        colgroup.append($('<col>').css('width', $(this).width()));
                    });
                    $(table).prepend(colgroup);
                }
            }

            function updateHeaderSortCount(table, sortList) {
                var c = table.config,
                    l = sortList.length;
                for (var i = 0; i < l; i++) {
                    var s = sortList[i],
                        o = c.headerList[s[0]];
                    o.count = s[1];
                    o.count++;
                }
            }

            // Sorting methods
            var sortWrapper;

            function multisort(table, sortList, cache) {
                if (table.config.debug)
                    var sortTime = new Date();

                var dynamicExp = "sortWrapper = function(a,b) {",
                    l = sortList.length;

                // TODO: inline functions.
                for (var i = 0; i < l; i++) {
                    var c = sortList[i][0];
                    var order = sortList[i][1];
                    // var s = (getCachedSortType(table.config.parsers,c) == "text") ?
                    // ((order == 0) ? "sortText" : "sortTextDesc") : ((order == 0) ?
                    // "sortNumeric" : "sortNumericDesc");
                    // var s = (table.config.parsers[c].type == "text") ? ((order == 0)
                    // ? makeSortText(c) : makeSortTextDesc(c)) : ((order == 0) ?
                    // makeSortNumeric(c) : makeSortNumericDesc(c));
                    var s = (table.config.parsers[c].type == "text") ? ((order == 0) ? makeSortFunction("text", "asc", c) : makeSortFunction("text", "desc", c)) : ((order == 0) ? makeSortFunction("numeric", "asc", c) : makeSortFunction("numeric", "desc", c));
                    var e = "e" + i;

                    dynamicExp += "var " + e + " = " + s; // + "(a[" + c + "],b[" + c
                    // + "]); ";
                    dynamicExp += "if(" + e + ") { return " + e + "; } ";
                    dynamicExp += "else { ";
                }

                // if value is the same keep orignal order
                var orgOrderCol = cache.normalized[0].length - 1;
                dynamicExp += "return a[" + orgOrderCol + "]-b[" + orgOrderCol + "];";

                for (var i = 0; i < l; i++)
                    dynamicExp += "}; ";

                dynamicExp += "return 0; ";
                dynamicExp += "}; ";

                if (table.config.debug)
                    benchmark("Evaling expression:" + dynamicExp, new Date());

                eval(dynamicExp);

                cache.normalized.sort(sortWrapper);

                if (table.config.debug)
                    benchmark("Sorting on " + sortList.toString() + " and dir " + order + " time:", sortTime);

                return cache;
            }
;        }
    });
})(jQuery);