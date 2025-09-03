(function () {
    var Bar = function (options) {
        options = options || {};
        var defaults = {
            document: document,
            register: null,
        };
        this.settings = $.extend({}, defaults, options);
        this.document = this.settings.document || document;

        this.register = [];

        this.delimiter = function () {
            var el = this.document.createElement("span");
            el.className = "mw-bar-delimiter";
            return el;
        };

        this.create = function () {
            this.bar = this.document.createElement("div");
            this.bar.className = "mw-bar";
            let startX = 0;
            this.bar.addEventListener(
                "touchstart",
                function (e) {
                    startX = e.touches[0].pageX;
                    $(".live-edit-add-content-button-wrapper").html(1);
                },
                { passive: true }
            );
            this.bar.addEventListener("touchmove", (e) => {
                const touch = e.touches[0];
                const deltaX = touch.pageX - startX;
                this.bar.scrollLeft -= deltaX;
                startX = touch.pageX;
                $(".live-edit-add-content-button-wrapper").html(2);
            });
            this.element = mw.element(this.bar);
        };

        this.rows = [];

        this.createRow = function () {
            var row = this.document.createElement("div");
            row.className = "mw-bar-row";
            this.rows.push(row);
            this.bar.appendChild(row);
        };
        this.nativeElement = function (node) {
            if (!node) return;
            return node.node ? node.node : node;
        };

        this.add = function (what, row) {
            row = row || 0;
            if (!this.rows[row]) {
                return;
            }
            if (what === "|") {
                this.rows[row].appendChild(this.delimiter());
            } else if (typeof what === "function") {
                this.rows[row].appendChild(what().node);
            } else {
                var el = this.nativeElement(what);
                if (el.get) {
                    el = el.get(0);
                }
                if (el) {
                    el.classList.add("mw-bar-control-item");
                    this.rows[row].appendChild(el);
                }
            }
        };

        this.init = function () {
            this.create();
        };
        this.init();
    };
    mw.bar = function (options) {
        return new Bar(options);
    };
})();
