/* ══════════════════════════════════════════════════════════════════════════════════
   Pharma Tabulator bridge
   ─────────────────────────────────────────────────────────────────────────────────
   Lets every page build a Tabulator from the SAME server-side Yajra/DataTables
   endpoints (no controller changes needed):
     window.PharmaTabulator.server({ el, url, columns, ... })

   The bridge translates Tabulator's params (page / size / sort) into the
   DataTables request format Yajra already understands (start / length /
   search[value] / order[0][column] / order[0][dir]) and unwraps the Yajra
   response ({data, recordsFiltered}) into the shape Tabulator's remote
   pagination needs ({data, last_page}).

   For server-rendered HTML report tables use:
     window.PharmaTabulator.fromDom({ el, export: true, ... })
   ───────────────────────────────────────────────────────────────────────────────── */
(function (window, document) {
    "use strict";

    var registry = {};      // server table instances
    var localRegistry = {}; // local (fromDom) table instances
    var stickyObservers = {};

    function debounce(fn, wait) {
        var timer = null;
        return function () {
            var context = this, args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () { fn.apply(context, args); }, wait);
        };
    }

    function serializeParams(obj) {
        var parts = [];
        for (var key in obj) {
            if (Object.prototype.hasOwnProperty.call(obj, key)) {
                parts.push(encodeURIComponent(key) + "=" + encodeURIComponent(obj[key]));
            }
        }
        return parts.join("&");
    }

    function isMobileTableViewport() {
        return window.matchMedia && window.matchMedia("(max-width: 767.98px)").matches;
    }

    /* Build a Yajra/DataTables style request from Tabulator params */
    function makeDataTablesRequest(params, columns, searchValue) {
        var qs = {
            draw: 1,
            start: (params.page - 1) * params.size,
            length: params.size,
            "search[value]": searchValue || "",
            "search[regex]": "false"
        };

        columns.forEach(function (col, i) {
            qs["columns[" + i + "][data]"] = col.field;
            qs["columns[" + i + "][name]"] = col.field;
            qs["columns[" + i + "][searchable]"] = col.searchable === false ? "false" : "true";
            qs["columns[" + i + "][orderable]"] = col.headerSort === false ? "false" : "true";
            qs["columns[" + i + "][search][value]"] = "";
            qs["columns[" + i + "][search][regex]"] = "false";
        });

        if (params.sort && params.sort.length) {
            var s = params.sort[0];
            for (var i = 0; i < columns.length; i++) {
                if (columns[i].field === s.field) {
                    qs["order[0][column]"] = i;
                    qs["order[0][dir]"] = s.dir;
                    break;
                }
            }
        }

        return qs;
    }

    function normalizeColumns(opts) {
        var disableHeaderSort = isMobileTableViewport();
        return (opts.columns || []).map(function (col, i) {
            var c = Object.assign({}, col);
            if (!c.field) c.field = "field_" + (i + 1);
            if (disableHeaderSort) c.headerSort = false;
            if (!c.formatter) {
                c.formatter = function (cell) {
                    var text = document.createElement("span");
                    var value = cell.getValue();
                    var decoder = document.createElement("textarea");
                    decoder.innerHTML = value == null ? "" : String(value);
                    text.textContent = decoder.value;
                    return text;
                };
            }
            return c;
        });
    }

    function getEl(opts) {
        return typeof opts.el === "string" ? document.getElementById(opts.el) : opts.el;
    }

    /* Builds the small toolbar (Search + optional Export) that DataTables used to give */
    function buildSearchToolbar(opts, table, state) {
        var el = getEl(opts);
        if (!el || !el.parentNode) return;

        var existingToolbar = el.parentNode.querySelector('.pharma-table-toolbar[data-table-key="' + (opts.el || el.id || '') + '"]');
        if (existingToolbar) existingToolbar.remove();

        var input = document.createElement("input");
        input.type = "search";
        input.className = "form-control form-control-sm";
        input.placeholder = "Search...";
        input.setAttribute("aria-label", "Search");

        var label = document.createElement("label");
        label.appendChild(input);

        var filterWrap = document.createElement("div");
        filterWrap.className = "dataTables_filter";
        filterWrap.appendChild(label);

        var toolbar = document.createElement("div");
        toolbar.className = "pharma-table-toolbar";
        toolbar.setAttribute("data-table-key", opts.el || el.id || "");
        toolbar.appendChild(filterWrap);

        if (opts.export) {
            var csv = document.createElement("button");
            csv.type = "button";
            csv.className = "btn btn-sm btn-outline-secondary pharma-export-csv";
            csv.textContent = "Export Data";
            csv.addEventListener("click", function () {
                if (table && typeof table.download === "function") {
                    table.download("csv", (opts.filename || "report") + ".csv", { delimiter: ",", bom: true });
                }
            });
            toolbar.appendChild(csv);
        }

        el.parentNode.insertBefore(toolbar, el);

        var onInput = debounce(function () {
            state.search = input.value;
            table.setData();
        }, 350);
        input.addEventListener("input", onInput);
        return toolbar;
    }

    function clearStickyOffsets(key) {
        var registration = stickyObservers[key];
        if (!registration) return;
        if (registration.observer) registration.observer.disconnect();
        if (registration.onResize) window.removeEventListener("resize", registration.onResize);
        if (registration.onScroll) window.removeEventListener("scroll", registration.onScroll);
        delete stickyObservers[key];
    }

    function syncStickyOffsets(el, toolbar, key) {
        if (!el) return;
        clearStickyOffsets(key);

        var boundary = el.closest(".table-responsive") || el.parentElement || el;
        var stackStart = toolbar || el;
        var startOffset = stackStart.getBoundingClientRect().top - boundary.getBoundingClientRect().top;

        var update = function () {
            var footer = el.querySelector(".tabulator-footer");
            var header = el.querySelector(".tabulator-header");
            if (!header) return;

            var toolbarHeight = toolbar && toolbar.isConnected
                ? toolbar.getBoundingClientRect().height
                : 0;
            var footerHeight = footer ? footer.getBoundingClientRect().height : 0;
            var headerHeight = header.getBoundingClientRect().height;
            var stackHeight = toolbarHeight + footerHeight + headerHeight;
            var boundaryRect = boundary.getBoundingClientRect();
            var normalTop = boundaryRect.top + startOffset;
            var exitDistance = stackHeight + 72;
            var boundaryTop = boundaryRect.bottom - stackHeight - exitDistance;
            var stackTop = Math.min(Math.max(60, normalTop), boundaryTop);

            if (footer) {
                footer.style.top = (stackTop + toolbarHeight) + "px";
                header.style.top = (stackTop + toolbarHeight + footerHeight) + "px";
            } else {
                header.style.top = (stackTop + toolbarHeight) + "px";
            }

            if (toolbar) toolbar.style.top = stackTop + "px";
        };

        update();

        if (typeof window.ResizeObserver === "function") {
            var observer = new ResizeObserver(update);
            observer.observe(boundary);
            observer.observe(el);
            if (toolbar) observer.observe(toolbar);
            var footer = el.querySelector(".tabulator-footer");
            if (footer) observer.observe(footer);
            var header = el.querySelector(".tabulator-header");
            if (header) observer.observe(header);
            var onScroll = update;
            window.addEventListener("scroll", onScroll, { passive: true });
            stickyObservers[key] = { observer: observer, onScroll: onScroll };
        } else {
            window.addEventListener("resize", update);
            window.addEventListener("scroll", update, { passive: true });
            stickyObservers[key] = { onResize: update, onScroll: update };
        }
    }

    window.PharmaTabulator = {

        /* Server-side table backed by a Yajra/DataTables JSON endpoint */
        server: function (opts) {
            var el = getEl(opts);
            if (!el) return null;

            this.destroy(opts.el);

            var tableKey = typeof opts.el === "string" ? opts.el : (el.id || "server-table");
            var columns = normalizeColumns(opts);
            var state = { search: "" };
            var table;
            var toolbar = null;

            table = new Tabulator(el, {
                layout: opts.layout || "fitColumns",
                placeholder: opts.placeholder || "No records found",
                ajaxURL: opts.url,
                ajaxContentType: "form",
                pagination: true,
                paginationMode: "remote",
                paginationSize: opts.pageLength || 10,
                paginationSizeSelector: opts.paginationSizeSelector === false ? false : (opts.paginationSizeSelector || [5, 10, 25, 50, 100]),
                sortMode: "remote",
                headerSort: opts.headerSort !== false,
                selectable: false,
                columnMinWidth: opts.columnMinWidth || 40,
                maxHeight: opts.maxHeight || false,
                columns: columns,
                ajaxURLGenerator: function (url, config, params) {
                    var qs = makeDataTablesRequest(params, columns, state.search);
                    return url + (url.indexOf("?") === -1 ? "?" : "&") + serializeParams(qs);
                },
                ajaxResponse: function (url, params, response) {
                    var data = [];
                    var total = 0;
                    if (response && Array.isArray(response.data)) {
                        data = response.data;
                        total = parseInt(response.recordsFiltered, 10);
                        if (isNaN(total)) total = parseInt(response.recordsTotal, 10);
                        if (isNaN(total) || total < data.length) total = data.length;
                    }
                    return {
                        data: data,
                        last_page: Math.max(1, Math.ceil(total / (params.size || 10)))
                    };
                },
                renderComplete: function () {
                    if (typeof opts.renderComplete === "function") {
                        opts.renderComplete();
                    }
                }
            });

            if (opts.el && !registry[opts.el]) {
                registry[opts.el] = table;
            }

            if (opts.search !== false) {
                toolbar = buildSearchToolbar(opts, table, state);
            }
            syncStickyOffsets(el, toolbar, tableKey);

            return table;
        },

        /* Client-side table built from an existing server-rendered <table> */
        fromDom: function (opts) {
            var el = getEl(opts);
            if (!el) return null;

            var key = opts.key || (typeof opts.el === "string" ? opts.el : el.id || "local");
            if (localRegistry[key]) {
                clearStickyOffsets(key);
                try { localRegistry[key].destroy(); } catch (e) {}
                delete localRegistry[key];
            }

            var existingWrapper = el.parentNode && el.parentNode.querySelector('.tabulator-wrapper[data-table-key="' + key + '"]');
            if (existingWrapper) {
                existingWrapper.remove();
            }

            if (el.dataset.tabulatorConverted === 'true') {
                return localRegistry[key] || null;
            }

            var fields = opts.fields || [];
            var disableHeaderSort = isMobileTableViewport();
            var heads = el.querySelectorAll("thead th");
            var columns = [];
            for (var i = 0; i < heads.length; i++) {
                columns.push({
                    title: heads[i].textContent.replace(/\s+/g, " ").trim(),
                    field: fields[i] || "c" + i,
                    headerSort: !disableHeaderSort,
                    formatter: opts.preserveHtml ? "html" : undefined
                });
            }

            var rows = [];
            var bodyRows = el.querySelectorAll("tbody tr");
            for (var j = 0; j < bodyRows.length; j++) {
                var tr = bodyRows[j];
                var tds = tr.querySelectorAll("td");
                if (!tds.length) continue;
                var hasColspan = false;
                for (var c = 0; c < tds.length; c++) {
                    if (tds[c].hasAttribute("colspan")) { hasColspan = true; break; }
                }
                if (hasColspan) continue; // skip "No records" placeholder row
                var row = {};
                for (var k = 0; k < tds.length; k++) {
                    row[fields[k] || "c" + k] = opts.preserveHtml
                        ? tds[k].innerHTML
                        : tds[k].textContent.replace(/\s+/g, " ").trim();
                }
                rows.push(row);
            }

            var div = document.createElement("div");
            div.className = "tabulator-wrapper";
            div.setAttribute("data-table-key", key);
            el.parentNode.insertBefore(div, el);
            el.dataset.tabulatorConverted = 'true';
            el.style.display = "none";
            try {
                el.parentNode.removeChild(el);
            } catch (e) {}

            var table = new Tabulator(div, {
                layout: "fitColumns",
                placeholder: opts.placeholder || "No records found",
                data: rows,
                pagination: opts.pagination !== false,
                paginationSize: opts.pageLength || 10,
                paginationSizeSelector: [5, 10, 25, 50, 100],
                headerSort: !disableHeaderSort,
                selectable: false,
                columns: columns,
                maxHeight: opts.maxHeight || false
            });

            var toolbar = null;
            if (opts.export) {
                var toolbar = document.createElement("div");
                toolbar.className = "pharma-table-toolbar";
                toolbar.setAttribute("data-table-key", key);

                var csv = document.createElement("button");
                csv.type = "button";
                csv.className = "btn btn-sm btn-outline-secondary";
                csv.textContent = "Export Data";
                csv.addEventListener("click", function () {
                    if (table && typeof table.download === "function") {
                        table.download("csv", (opts.filename || "report") + ".csv", { delimiter: ",", bom: true });
                    }
                });
                toolbar.appendChild(csv);

                var print = document.createElement("button");
                print.type = "button";
                print.className = "btn btn-sm btn-outline-secondary ml-2";
                print.textContent = "Print";
                print.addEventListener("click", function () {
                    if (table && typeof table.print === "function") {
                        table.print(false, true);
                    }
                });
                toolbar.appendChild(print);

                div.parentNode.insertBefore(toolbar, div);
            }

            localRegistry[key] = table;
            syncStickyOffsets(div, toolbar, key);
            return table;
        },

        destroy: function (id) {
            clearStickyOffsets(id);
            if (registry[id]) {
                try { registry[id].destroy(); } catch (e) {}
                delete registry[id];
            }
            var target = document.getElementById(id);
            if (target) {
                delete target.dataset.tabulatorConverted;
                var wrapper = target.parentNode && target.parentNode.querySelector('.tabulator-wrapper[data-table-key="' + id + '"]');
                if (wrapper) wrapper.remove();
                target.style.display = "";
            }
        },

        get: function (id) {
            return registry[id] || null;
        },

        reload: function (id) {
            if (registry[id]) { try { registry[id].setData(); } catch (e) {} }
        },

        reloadAll: function () {
            var key;
            for (key in registry) {
                if (Object.prototype.hasOwnProperty.call(registry, key)) {
                    try { registry[key].setData(); } catch (e) {}
                }
            }
        },

        destroyAll: function () {
            var key;
            for (key in registry) {
                if (Object.prototype.hasOwnProperty.call(registry, key)) {
                    clearStickyOffsets(key);
                    try { registry[key].destroy(); } catch (e) {}
                }
            }
            registry = {};
        },

        destroyLocalAll: function () {
            var key;
            for (key in localRegistry) {
                if (Object.prototype.hasOwnProperty.call(localRegistry, key)) {
                    clearStickyOffsets(key);
                    try { localRegistry[key].destroy(); } catch (e) {}
                }
            }
            localRegistry = {};
        }
    };
})(window, document);