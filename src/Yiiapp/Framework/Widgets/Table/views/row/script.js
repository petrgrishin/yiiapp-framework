App.f('yiiapp.framework.widgets.table.row', function (params, scope) {
    var listener = scope.createListener();
    var $container = scope.$id(params.container);

    var id = params.id;

    if (params.click) {
        $container.click(function (e) {
            e.preventDefault();
            listener.trigger(params.click, id);
        });
    }

    if (params.dbClick) {
        $container.dblclick(function (e) {
            e.preventDefault();
            listener.trigger(params.dbClick, id);
        });
    }

    var fApply = _.bind(listener.trigger, listener, ['apply']);

    _.each(params.actions, function (actionParams, link) {
        scope.$id(link).click(function (e) {
            e.preventDefault();

            var doAction = function () {
                listener.trigger(actionParams['name'], id);
                actionParams.url && scope.load({
                    'url': actionParams.url,
                    'success': function (response) {
                        if (!_.isObject(response)) {
                            $('body:first').append(response);
                        } else {
                            if (response.apply) {
                                fApply();
                            } else {
                                scope.getNotifer().error(response.error);
                            }
                        }
                    },
                    'context': function (context) {
                        context.onApply && context.onApply(fApply);
                        !context.onApply && fApply();
                    }
                });
            };

            if (actionParams.confirm) {

                (function (params) {
                    var confirm = scope.confirm(params.text, doAction);

                    _.each(params.buttons, function (config, actionName) {
                        confirm
                            .getButton(actionName)
                            .setType(config.type)
                            .setLabel(config.label);
                    });

                    confirm.show();

                }(actionParams.confirm));

            } else {
                doAction();
            }

        });

    });

    return {
        id: id,
        listener: listener
    };
});