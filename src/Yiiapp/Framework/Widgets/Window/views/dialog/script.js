App.f('yiiapp.framework.widgets.window.dialog', function (data, scope, widgets) {
    var $dialogContainer = $('<div/>'),
        $errorContainer = $('<div class="alert alert-block alert-error"/>'),
        window = widgets.window,
        listener = scope.createListener(),
        buttons = {},
        actionsSubscribers = {},
        subscribes = [];

    $errorContainer.css({display: 'none'});

    (function (params) {
        $dialogContainer.append($errorContainer);
        $dialogContainer.append(params.content);

        window.setContent($dialogContainer);
        window.onHide(function () {
            listener.trigger("hide");
        });

        _.each(params.actions, function (actionData, actionName) {
            if (!actionData) {
                return;
            }

            listener.subscribe("action." + actionName, function (params) {
                actionsSubscribers[actionName] = actionsSubscribers[actionName] || [];
                subscribes = subscribes || [];
                params = params || {
                    hide: true
                };

                var data = $dialogContainer.find("form").serializeArray()
                $errorContainer.hide();

                _.each(_.extend(actionsSubscribers[actionName], subscribes), function (f) {
                    if (f && typeof f == "function") {
                        var res = f(data);
                        if (typeof res != "undefined" && !res) {
                            params.hide = false;
                        }
                    }
                });

                if (params.hide) {
                    window.hide(function () {
                        listener.trigger("hide");
                    });
                }
            });

            $dialogContainer.on('submit', function (e) {
                e.preventDefault();
                if (actionData.submitAction) {
                    listener.trigger("action." + actionName);
                }
            });

            buttons[actionName] = (function () {
                var $button = window.addBtn(actionData.label, function () {
                    listener.trigger("action." + actionName);
                });

                if (actionData.class) {
                    $button.addClass(actionData.class);
                }

                return {
                    $container: $button,
                    setType: function (type) {
                        this.$container.removeClass().addClass("btn btn-" + type);
                        return this;
                    },
                    setLabel: function (label) {
                        this.$container.html(label);
                        return this;
                    }
                }
            }())

        });

        if (params.actions.close) {
            $(window.container).on("hiddenByBackdrop", function () {
                listener.trigger("action.close", {hide: false});
            });
        }
    }(data.dialog));

    return {
        onAction: function (actionName, callback) {
            if (typeof actionName === "function") {
                subscribes.push(actionName);
                return;
            }

            if (_.isUndefined(actionsSubscribers[actionName])) {
                actionsSubscribers[actionName] = [];
            }
            actionsSubscribers[actionName].push(callback);
        },

        setTitle: function (title) {
            window.setTitle(title);
        },

        show: function () {
            window.show(function () {
                listener.trigger("show");
            });
        },

        hide: function () {
            window.hide();
        },

        onHide: function (f) {
            listener.subscribe("hide", f);
        },

        setErrors: function (errors) {
            if (!_.isArray(errors)) {
                if (_.isObject(errors)) {
                    errors = _.toArray(errors);
                } else {
                    errors = [errors];
                }

            }

            $errorContainer.html('Необходимо исправить следующие ошибки:<br/>');

            var $ul = $("<ul>");
            $ul.append("<li>" + errors.join("</li><li>") + "</li>");

            $errorContainer.append($ul);
            $errorContainer.show();
        },

        getButton: function (actionName) {
            return buttons[actionName];
        },

        getContainer: function () {
            return $dialogContainer
        },

        setContent: function (content) {
            $dialogContainer.html(content);
            return this;
        },

        unsubscribeActions: function () {
            actionsSubscribers = {};
            subscribes = [];
        }
    };
});
