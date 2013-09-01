/**
 * Created by dsxack on 8/23/13.
 */

App.f("yiiapp.framework.widgets.bootstrap.box", function (params, scope) {
    var listener = scope.createListener(),
        $actionsContainer = scope.$id(params.actionsContainer);

    $("body").append($actionsContainer);

    var fApply = _.bind(listener.trigger, listener, ['apply']);

    _.each(params.actions, function (actionParams, link) {
        scope.$id(link).click(function (e) {
            e.preventDefault();

            var doAction = function () {
                actionParams.url && scope.load({
                    'url': actionParams.url,
                    'success': function (response) {
                        if (!_.isObject(response)) {
                            $actionsContainer.html(response);
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
        onApply: function (f) {
            listener.subscribe('apply', f);
        }
    }
});