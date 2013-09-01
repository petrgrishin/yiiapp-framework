App.f("yiiapp.framework.widgets.table.layout", function (params, scope, widgets) {
    var itemListenerAdapter = scope.createListener();

    var container = $(document.getElementById(params.container));

    widgets.rows && _.each(widgets.rows, function (row) {
        row.listener.subscribe(function (eventName, data) {
            itemListenerAdapter.trigger(eventName, data);
            if (eventName == 'apply') {
                reload();
            }
        });
    });

    var reload = function () {
    };
    params.urls.context && (reload = (function (reloadCallback) {
        return function () {
            reloadCallback({}, function (context) {
                table.delegate(context);
            });
        };
    })(scope.createReload(params.urls.context, container)));

    var listener = scope.createListener();

    var table = {
            onSort: function (f) {
                listener.subscribe('sort', f);
            },
            onPager: function (f) {
                listener.subscribe('pager', f);
            },
            itemsActionsSubscribe: function (f) {
                itemListenerAdapter.subscribe(f);
            },
            _getItemsListener: function () {
                return itemListenerAdapter;
            },
            _getListener: function () {
                return listener;
            },
            delegate: function (table) {
                table._getListener().delegate(this._getListener());
                table._getItemsListener().delegate(this._getItemsListener());
            },
            reload: reload
        },
        toggleOrder = function () {
            var res = 'asc';
            params.sort.order == 'asc' && (res = 'desc');
            params.sort.order == 'desc' && (res = 'asc');
            return res;
        };

    var change = function (action, data) {
        listener.trigger(action, data);
        params.urls[action] && scope.load({
            url: params.urls[action],
            data: data,
            success: function (response) {
                reload();
            }
        });
    };

    var pagerChange = function (number, count) {
        var data = {number: number, count: count};
        change('pager', data);
    };

    var sortChange = function (by, order) {
        var data = {by: by, order: order};
        change('sort', data);
    };

    container
        .on("click", '.sortable', function (e) {
            e.preventDefault();
            var by = $(this).data('name'),
                order = (by != params.sort.by) ? "asc" : toggleOrder();
            sortChange(by, order);

        })
        .on("change", "." + params.pagerPageSizesClass + ' select', function (e) {
            e.preventDefault();
            var number = $(this).data("page"),
                count = $(this).val();
            pagerChange(number, count);
        })
        .on("click", "." + params.pagerClass + ' a', function (e) {
            e.preventDefault();
            var number = $(this).data("page"),
                count = $(this).data("count");
            pagerChange(number, count);
        });
    return table;
});