/**
 * Author Petr Grishin <petr.grishin@grishini.ru>
 */

(function () {

    var windowManager = {
        windows: {},
        stack: []
    };

    var WIN_CLOSE = 'close';
    var WIN_APPLY = 'apply';
    var WIN_HIDE = 'onhide';

    _.extend(windowManager, {
        createWindow: function (name) {
            var window = {
                windowManager: windowManager,

                title: '',
                content: '',
                btns: []
            };

            windowManager.windows[name] = window;

            return window;
        },

        addStack: function (modal) {
            windowManager.stack.push(modal);

            var z = 2000;
            _.each(windowManager.stack, function (value) {
                value.$backdrop.css('zIndex', z++);
                value.$element.css('zIndex', z++);
            });
        },
        removeStack: function () {
            windowManager.stack.pop();
        }


    });


    App.f('yiiapp.framework.widgets.window.constructor', function (data, scope) {

        var window = windowManager.createWindow(data.container);

        var listener = scope.createListener();

        window.container = $(document.getElementById(data.container));

        if(window.container.length < 1){
            throw 'Not found `window` container';
        }

        _.extend(window, {
            show: function (callback) {
                listener.trigger('show', {callback: callback});
            },
            close: function () {
                this.hide();
                listener.trigger(WIN_CLOSE);
            },
            hide: function (callback) {
                listener.trigger('hide', {callback: callback});
            },
            apply: function () {
                listener.trigger(WIN_APPLY);
            },
            setTitle: function (value) {
                window.title = value;
            },

            setContent: function (value) {
                window.content = $(value);
            },
            getContent: function () {
                return window.content;
            },
            addBtn: function (name, callback, link) {
                var tag = link ? '<a>' : '<button>';
                var btn = $(tag)
                    .addClass('btn')
                    .html(name);

                link && btn.attr('href', link);

                window.btns.push([btn, callback || function () {
                }]);
                return btn;
            },
            setBtns: function (btns) {
                this.clearBtns();
                _.each(btns, function (f, name) {
                    this.addBtn(name, f);
                }, this);
            },
            clearBtns: function () {
                this.btns = [];
            },
            onApply: function (f) {
                listener.subscribe(WIN_APPLY, f);
            },
            onClose: function (f) {
                listener.subscribe(WIN_CLOSE, f);
            },
            onHide: function (f) {
                listener.subscribe(WIN_HIDE, f);
            }
        });

        var footer = window.container.find('.modal-footer');

        listener.subscribe('show', function (params) {

            window.container.find('.title').html(window.title);
            $(window.content).show();
            window.container.find('.modal-body').empty().append(window.content);

            footer.empty();
            _.each(window.btns, function (ar) {
                var obj = ar[0], callback = ar[1];
                obj.off('.window');
                callback && obj.on("click.window", function () {
                    callback(this, listener, window.container);
                });
                footer.append(obj);
            });

            $(window.container).one("shown", function () {
                if (params.hasOwnProperty('callback') && typeof params.callback == "function") {
                    params.callback(window);
                }
            });
            window.container.modal({'manager': windowManager});
        });

        listener.subscribe('hide', function () {
            window.container.modal('hide');
        });

        $(window.container).on("hidden", function () {
            listener.trigger(WIN_HIDE);
        });

        data.title && window.setTitle(data.title);
        if (data.content) {
            var content = $(data.content).appendTo('body:first').hide()[0];
            window.setContent(content);
        }

        // кнопки по умолчанию
        if (data.btns) {
            var btns = {};
            _.each(data.btns, function (value, index) {
                if (_.contains(['apply', 'close'], value)) {
                    return;
                }
                btns[index] = value;
            });

            _.each(btns, function (params, name) {
                var f = function () {
                };
                var btn = window.addBtn(name, f, params.link);
                delete params.link;
                btn.attr(params);
            });
            _.contains(data.btns, 'apply') && window.addBtn('Применить', function () {
                window['apply']();
            });
            _.contains(data.btns, 'close') && window.addBtn('Закрыть', function () {
                window.close();
            });
        }
        return window;
    });
})();
