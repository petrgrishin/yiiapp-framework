App.f("yiiapp.framework.widgets.table.filter", function (params, scope, widgets) {
    var MAIN_PRESET_ID = 'main';
    var SEARCHABLE_CLASS_NAME = 'searchable';
    var CONTENT_BOX_CLASS_NAME = 'tab-box';
    var TABBABLE_CONTAINER_CLASS_NAME = 'tabbable';
    var COLLAPSED_CLASS_NAME = "collapsed";

    var $container = scope.$id(params.container);

    var newAddWindow = (function (widget) {
        var listener = scope.createListener();
        var currentCallback;
        var self = {
            show: function (callback, name) {
                currentCallback = callback || function () {
                };
                $inputs.filter('[name="name"]').val(name || '');
                widget.show();

                $(widget.content).find('form:first').on("submit.filter", function (e) {
                    e.preventDefault();
                    listener.trigger('apply');
                });
            },
            hide: function () {
                widget.hide();
                $(widget.content).find('form:first').off(".filter");
            },
            getData: function () {
                var res = {};
                $inputs.each(function () {
                    res[$(this).attr('name')] = $(this).val();
                });
                return res;
            }
        };


        var $inputs = $(widget.content).find(':input');
        widget.addBtn('Применить', function () {
            listener.trigger('apply');
        });
        widget.addBtn('Отмена', function () {
            widget.hide();
        });
        listener.subscribe('apply', function () {
            currentCallback && currentCallback(self.getData()) && self.hide();
        });
        return self;
    })(widgets.window);

    var Field = scope.Class({
        hidden: false,
        rangeTypes: [
            'textrange',
            'daterange'
        ],
        listTypes: [
            'list',
            'multilist'
        ],
        checkTypes: [
            'checkbox',
            'checkboxgroup'
        ],
        radioTypes: [
            'radio'
        ],
        hasManyInputsTypes: [
            'textrange',
            'daterange',
            'checkboxgroup',
            'multilist'
        ],

        hide: function () {
            this.hidden = true;
            this.$container.hide();
        },
        show: function () {
            this.hidden = false;
            this.$container.show();
        },
        isHidden: function () {
            return this.hidden;
        },
        val: function (value) {
            if (value !== undefined) {
                // TODO: отрефакторить эту кучу страшных условий
                if (this.isRange()) {
                    _.each(value, function (value, i) {
                        this.$input.eq(i).val(value);
                    }, this);
                } else if(this.isList()) {
                    var select = this.$input.data("select2");
                    if (select) {
                        this.$input.data("select2").val(value);
                    }
                } else if(this.isCheckbox() || this.isRadio()) {
                    _.each(this.$input, function (input, i) {
                        var $input = $(input);
                        $input.prop("checked", "");
                        if ($input.val() === value) {
                            $input.prop("checked", "checked");
                        }
                    }, this);
                } else {
                    this.$input.val(value);
                }

                return this;
            } else {
                if (this.isHidden()) {
                    return null;
                }

                if (this.isRange()) {
                    value = [];
                    var empty = true;
                    _.each(this.$input, function (input) {
                        var val = $(input).val();

                        if (val) {
                            empty = false;
                        }
                        val = val || '';

                        // сохраняем все значения, чтобы после загрузки загрузить из сессии в верной последовательности
                        value.push(val);
                    });
                    // сбрасываем данные, если не было ни одного значения
                    if (empty) {
                        return null;
                    }

                    return value;
                }

                if(this.isCheckbox()) {
                    value = [];

                    _.each(this.$input.filter(":checked"), function (input, i) {
                        var val = $(input).val();

                        if (val) {
                            value.push(val);
                        }
                    }, this);

                    return value;
                }

                if(this.isRadio()) {
                    return this.$input.filter(":checked").val();
                }

                return this.$input.val();
            }
        },

        isList: function () {
            if (_.indexOf(this.listTypes, this.data.type) !== -1) {
                return true;
            }
            return false;
        },
        isCheckbox: function () {
            if (_.indexOf(this.checkTypes, this.data.type) !== -1) {
                return true;
            }
            return false;
        },
        isRange: function () {
            if (_.indexOf(this.rangeTypes, this.data.type) !== -1) {
                return true;
            }
            return false;
        },
        isRadio: function () {
            if (_.indexOf(this.radioTypes, this.data.type) !== -1) {
                return true;
            }
            return false;
        },
        hasManyInputs: function () {
            if (_.indexOf(this.hasManyInputsTypes, this.data.type) !== -1) {
                return true;
            }
            return false;
        },
        onHideClick: function (f) {
            var self = this;
            this.$btn.click(function (e) {
                e.preventDefault();
                f(self.name);
            });
        },
        hideBlock: function (sign) {
            sign ? this.$btn.hide() : this.$btn.show();
        }
    }, function (container, data) {
        this.$container = $(container);
        this.data = data;
        this.name = data['name'];
        this.hasManyInputs() && (data['name'] = data['name'] + '[]');
        this.$input = this.$container.find('[name="' + data['name'] + '"]');
        this.$btn = this.$container.find('.btn');
    });

    var viewFieldsMenu = (function (params) {
        var CLASS_SHOW = 'icon-ok';
        var CLASS_HIDE = 'icon-hide';

        var self = {
            fields: {},
            hidesFunctions: [],
            hidesAllFunctions: [],
            showsFunctions: [],
            onHide: function (f) {
                this.hidesFunctions.push(f);
            },
            onShow: function (f) {
                this.showsFunctions.push(f);
            },
            onHideAll: function (f) {
                this.hidesAllFunctions.push(f);
            },
            isVisible: function (name) {
                var field = this.fields[name];
                if (!field) {
                    return;
                }
                return field.find('i:first').hasClass(CLASS_SHOW);
            },
            setAsHide: function (name) {
                var field = this.fields[name];
                if (!field) {
                    return;
                }
                field.find('i:first').addClass(CLASS_HIDE);
                field.find('i:first').removeClass(CLASS_SHOW);
            },
            setAsShow: function (name) {
                var field = this.fields[name];
                if (!field) {
                    return;
                }
                field.find('i:first').addClass(CLASS_SHOW);
                field.find('i:first').removeClass(CLASS_HIDE);
            },
            setAsDisable: function (name, sign) {
                if (sign) {
                    this.fields[name].parents('li:first').addClass('disabled');
                } else {
                    this.fields[name].parents('li:first').removeClass('disabled');
                }
            }

        };
        _.each(params.fields, function (id, name) {
            var field = scope.$id(id).click(function (e) {
                $(this).blur();
                e.preventDefault();

                if ($(this).parents('li:first').hasClass('disabled')) {
                    return;
                }
                var funcs = self.isVisible(name) ? self.hidesFunctions : self.showsFunctions;
                _.each(funcs, function (f) {
                    f(name);
                })
            });
            self.fields[name] = field;
        });

        scope.$id(params.showAll).click(function (e) {
            e.preventDefault();
            _.each(self.fields, function (obj, name) {
                _.each(self.showsFunctions, function (f) {
                    f(name);
                });
            });
        });
        scope.$id(params.hideAll).click(function (e) {
            e.preventDefault();
            _.each(self.hidesAllFunctions, function (f) {
                f(name);
            });
        });
        return self;
    })(params.viewFieldsMenu);

    var presetsMenu = (function (params) {
        var $delete = scope.$class(params.delete);
        var $rename = scope.$class(params.rename);
        var $save = scope.$class(params.save);
        var $saveAs = scope.$class(params.saveAs);
        var $add = scope.$class(params.add);
        var $cancel = scope.$class(params.cancel);
        var $search = scope.$class(params.search);
        var $searchForm = scope.$class(params.searchForm);

        return {
            onCancel: function (f) {
                $cancel.click(function (e) {
                    e.preventDefault();
                    f();
                });
            },
            onSearch: function (f) {
                $search.click(function (e) {
                    e.preventDefault();
                    f();
                });
                $searchForm.on("submit", function (e) {
                    e.preventDefault();
                    f();
                })
            },
            onDelete: function (f) {
                $delete.click(function (e) {
                    e.preventDefault();
                    f();
                });
            },
            onSave: function (f) {
                $save.click(function (e) {
                    e.preventDefault();
                    f();
                })
            },
            onAdd: function (f) {
                $add.click(function (e) {
                    e.preventDefault();
                    newAddWindow.show(f);
                })
            },
            onRename: function (f, fGetName) {
                $rename.click(function (e) {
                    e.preventDefault();
                    newAddWindow.show(f, fGetName ? fGetName() : '');
                })
            },
            onSaveAS: function (f) {
                $saveAs.click(function (e) {
                    e.preventDefault();
                    newAddWindow.show(f);
                });
            },
            hideDeleteButton: function () {
                $delete.hide();
            },
            hideRenameButton: function () {
                $rename.hide();
            },
            showDeleteButton: function () {
                $delete.show();
            },
            showRenameButton: function () {
                $rename.show();
            }
        };
    })(params.presetActions);

    var tabs = (function (params) {
        var $tabContainer = scope.$id(params.tabContainer);
        var $tabSelector = scope.$id(params.tabSelector);
        var $ulTabSelector = $tabSelector.siblings("ul:first");
        var choiceCallbacks = [];
        var moveChoiceCallbacks = [];
        var listener = scope.createListener();
        var tabs = {};

        listener.subscribe("choice", function (index) {
            _.each(choiceCallbacks, function (f) {
                f(index);
            });
        });
        listener.subscribe("moveChoice", function (index) {
            _.each(moveChoiceCallbacks, function (f) {
                f(index);
            });
        });

        self = {
            updateVisibleTabSelector: function () {
                if(_.size(tabs) > 1) {
                    $tabSelector.show();
                } else {
                    $tabSelector.hide();
                }
            },
            add: function (index, name, fixed) {
                var $tab = $('<li><a href="#" data-toggle="tab" data-index="' + index + '">' + name + '</a></li>');
                fixed = fixed || false;
                $tab.appendTo($tabContainer);


                $tab.click(function (e) {
                    e.preventDefault();
                    listener.trigger('choice', index);
                });

                var $tabSelector = $(null);
                if (fixed) {
                    $($tab, $tabSelector).addClass('fixed');
                } else {
                    $tabSelector = $tab.clone();
                    $tabSelector.click(function (e) {
                        e.preventDefault();
                        listener.trigger('moveChoice', index);
                    });
                    $tabSelector.appendTo($ulTabSelector);
                }

                tabs[index] = {
                    tab: $tab,
                    tabSelector: $tabSelector
                };
                this.updateVisibleTabSelector();
            },
            remove: function (index) {
                tabs[index].tab.remove();
                tabs[index].tabSelector.remove();

                delete tabs[index];
                this.updateVisibleTabSelector();
            },
            rename: function (index, name) {
                tabs[index].tab.find('a').text(name);
                tabs[index].tabSelector.find('a').text(name);
            },
            setup: function (data) {
                _.each(data, function (name, index) {
                    this.add(name, index);
                }, this);
            },
            clear: function () {
                $tabContainer.find('li').remove();
                $ulTabSelector.empty();
                tabs = {};
                this.updateVisibleTabSelector();
            },
            unActivateItems: function () {
                _.each(tabs, function (tabList) {
                    tabList.tab.removeClass('active');
                });
            },
            setActiveItem: function (index) {
                this.unActivateItems();
                tabs[index].tab.addClass('active');
            },
            setSearchable: function (index) {
                tabs[index].tab.find('a').addClass(SEARCHABLE_CLASS_NAME);
            },
            setAllUnsearchable: function () {
                _.each(tabs, function (tabParams) {
                    tabParams.tab.find('a').removeClass(SEARCHABLE_CLASS_NAME);
                }, this);
            },
            onChoice: function (f) {
                if (f && typeof f == "function") {
                    choiceCallbacks.push(f);
                }
            },
            onMoveChoice: function (f) {
                if (f && typeof f == "function") {
                    moveChoiceCallbacks.push(f);
                }
            }
        };

        return self;
    }(params.tabsParams));

    var self = {
        fields: {},
        presets: {},
        activePresetId: '',
        visibleFields: [],
        blockedFields: [],
        searchSubscribers: [],
        state: {
            saveRunned: false,
            params: [],
            search: false,
            isCollapsed: false
        },

        isCollapsed: function () {
            return this.state.isCollapsed;
        },
        collapseToggle: function () {
            scope.$class(TABBABLE_CONTAINER_CLASS_NAME).toggleClass(COLLAPSED_CLASS_NAME);
            this.state.isCollapsed = !this.state.isCollapsed;

            var preset = this.getActivePresetData();
            if (this.isCollapsed()) {
                tabs.unActivateItems();
            } else {
                tabs.setActiveItem(this.activePresetId);
            }
            this.saveState();
        },
        setStateAsSearch: function () {
            this.state.search = true;
        },
        setStateAsCancelSearch: function () {
            this.state.search = false;
        },
        addField: function (name, obj) {
            var self = this;
            obj.show();
            this.fields[name] = obj;


            obj.onHideClick(function (fName) {
                self.hideField(fName);
            });
        },
        hideAllFields: function (useSettings) {
            _.each(_.keys(this.fields).reverse(), function (name) {
                this.hideField(name, useSettings);
            }, this);
        },
        countVisibleFields: function () {
            return _.size(this.visibleFields);
        },
        hideField: function (fName, useSettings) {
            var obj = this.fields[fName];
            if (useSettings !== false && this.countVisibleFields() == 1) {
                return;
            }
            obj.hide();
            viewFieldsMenu.setAsHide(fName);
            if (useSettings !== false) {
                obj.val('');
                this.visibleFields = _.without(this.visibleFields, fName);
                self.updateCurrentPresetData();
            }
            if (this.countVisibleFields() == 1) {
                this.blockVisibleFields(true);
            }
        },
        getVisibleFieldsNames: function () {
            return this.visibleFields;
        },
        showField: function (fName, useVisible) {
            if (this.countVisibleFields() == 1) {
                this.blockVisibleFields(false);
            }
            var obj = this.fields[fName];
            obj.show();
            viewFieldsMenu.setAsShow(fName);
            if (useVisible !== false) {
                this.visibleFields.push(fName);
                this.visibleFields = _.uniq(this.visibleFields);
                self.updateCurrentPresetData();
            }
            // if begin as null
            if (this.countVisibleFields() == 1) {
                this.blockVisibleFields(true);
            }
        },
        blockField: function (name, sign) {
            this.fields[name].hideBlock(sign);
            viewFieldsMenu.setAsDisable(name, sign);
        },
        blockVisibleFields: function (sign) {
            _.each(this.visibleFields, function (name) {
                this.blockField(name, sign);
                if (sign) {
                    this.blockedFields.push(name);
                    this.blockedFields = _.uniq(this.blockedFields);
                } else {
                    this.blockedFields = _.without(this.blockedFields, name);
                }
            }, this);
        },
        unblockAllFields: function () {
            _.each(this.blockedFields, function (name) {
                this.blockField(name, false);
            }, this);
            this.blockedFields = [];
        },
        getCurrentData: function () {
            var res = {};
            _.each(this.fields, function (obj, name) {
                var val = obj.val();
                if (val) {
                    res[name] = obj.val();
                }
            }, this);
            return res;
        },
        clearCurrentData: function () {
            _.each(this.fields, function (obj) {
                obj.val('');
            });
        },
        setCurrentData: function (data) {
            this.clearCurrentData();
            _.each(data || {}, function (value, name) {
                var field = this.fields[name];
                if (!field) {
                    return;
                }
                field.val(value);
            }, this);
        },
        initPreset: function (id, data) {
            if (id == MAIN_PRESET_ID) {
                data.name = 'Фильтр';
            }

            var preset = {
                data: data
            };
            this.presets[id] = preset;

            tabs.add(id, data.name, id == MAIN_PRESET_ID);
            if (preset.data.searchable) {
                tabs.setSearchable(id);
            }
        },
        getActivePresetData: function () {
            return this.presets[this.activePresetId];
        },
        setActivePreset: function (id) {
            if (id == MAIN_PRESET_ID) {
                presetsMenu.hideDeleteButton();
                presetsMenu.hideRenameButton();
            } else {
                presetsMenu.showDeleteButton();
                presetsMenu.showRenameButton();
            }
            var cPreset = this.getActivePresetData();
            if (cPreset) {
                cPreset.data.data = this.getCurrentData();
                delete cPreset.data.active;
                if (cPreset.data.searchable) {
                    this.setContainerAsUnsearchable();
                }
            }

            this.activePresetId = id;

            this.setCurrentData(this.getActivePresetData().data.data || {});
            var preset = this.presets[id];
            if (!preset) {
                throw 'preset ' + id + ' not exists';
            }
            this.hideAllFields(false);
            tabs.setActiveItem(id);

            preset.data.active = true;
            if (preset.data.searchable) {
                this.setContainerAsSearchable();
            }

            this.visibleFields = [];
            this.unblockAllFields();
            _.each(preset.data.visibleFields || [_.chain(this.fields).keys().first().value()], function (name) {
                this.showField(name);
            }, this);

            if (this.isCollapsed()) {
                this.searchActivePreset();
            }

            this.saveState();
        },
        movePresetAsFirst: function (index) {
            var preset = this.presets[index];
            delete this.presets[index];
            var presets = this.presets;
            var mainPreset = this.presets[MAIN_PRESET_ID];
            this.presets = {};
            delete presets[MAIN_PRESET_ID], presets[index];

            tabs.clear();
            this.initPreset(MAIN_PRESET_ID, mainPreset.data);
            this.initPreset(0, preset.data);

            var activePresetIdSetup = false;
            _.each(presets, function (preset, id) {
                var setupId = parseInt(id) + 1;
                if (id == this.activePresetId && !activePresetIdSetup) {
                    this.activePresetId = setupId;
                    activePresetIdSetup = true;
                }
                this.initPreset(setupId, preset.data);
                delete preset.data;
                _.each(preset, function (value, name) {
                    this.presets[setupId][name] = value;
                }, this);
            }, this);
            this.setActivePreset(0);
        },
        renameActivePreset: function (name) {
            var preset = this.getActivePresetData();
            preset.data.name = name;
            tabs.rename(this.activePresetId, name);
            this.saveState();
        },
        removePreset: function (id) {
            var preset = this.presets[id];
            if (id == MAIN_PRESET_ID) {
                return;
            }
            if (id == this.activePresetId) {
                this.setActivePreset(MAIN_PRESET_ID);
            }
            tabs.remove(id);
            delete this.presets[id];
        },
        removeActivePreset: function () {
            this.removePreset(this.activePresetId);
            this.saveState();
        },
        updateCurrentPresetData: function () {
            var preset = this.getActivePresetData();
            preset.data.data = this.getCurrentData();
            if (!preset) {
                return;
            }
            preset.data.visibleFields = this.getVisibleFieldsNames();
        },
        saveActivePreset: function () {
            this.updateCurrentPresetData();
            this.saveState();
        },
        addPreset: function (name) {
            var id = _.size(this.presets);
            this.initPreset(id, {
                name: name,
                visibleFields: _.keys(this.fields)
            });
            this.setActivePreset(id);
        },
        saveAsActivePreset: function (name) {
            var preset = this.getActivePresetData();
            if (!preset) {
                return;
            }

            var presetData = _.extend({}, {
                visibleFields: this.getVisibleFieldsNames(),
                name: name,
                data: this.getCurrentData()
            });

            var id = _.size(this.presets);
            while (this.presets[id]) {
                id++;
            }

            this.initPreset(id, presetData);
            newAddWindow.hide();
            this.setActivePreset(id);
            this.saveState();
        },
        onSearch: function (f) {
            if (!f || f.constructor != Function) {
                return;
            }
            this.searchSubscribers.push(f);
        },
        search: function (data) {
            var Self = this;
            this.updateCurrentPresetData();
            this.saveState();
            $.ajax({
                url: scope.createRequest(params.urls.saveData).toString(),
                data: {
                    data: data || {}
                },
                success: function () {
                    _.each(Self.searchSubscribers, function (f) {
                        f();
                    });
                }
            });
        },
        _processingSavingSate: function () {
            var state = this.state;
            var Self = this;
            if (state.saveRunned || _.isEmpty(this.state.params)) {
                return;
            }
            state.saveRunned = true;
            var data = _.last(this.state.params);
            this.state.params = [];
            $.ajax({
                url: scope.createRequest(params.urls.saveOptions).toString(),
                data: {
                    data: data
                },
                success: function () {
                    state.saveRunned = false;
                    Self._processingSavingSate();
                }
            });
        },
        saveState: function () {
            this.state.params.push(_.clone(this.getOptions()));
            this._processingSavingSate();
        },
        cancelActivePreset: function () {
            this.setStateAsCancelSearch();
            var preset = this.getActivePresetData();
            delete preset.data.searchable
            this.search({});
            this.setActivePresetAsUnsearchable();
        },
        searchActivePreset: function () {
            this.setStateAsSearch();
            var preset = this.getActivePresetData();
            _.each(this.presets, function (preset) {
                delete preset.data.searchable;
            });
            this.search(this.getCurrentData());
            this.setActivePresetAsSearchable();
            preset.data.searchable = true;
            this.saveState();
        },
        setContainerAsUnsearchable: function () {
            $container.find('.' + CONTENT_BOX_CLASS_NAME).removeClass(SEARCHABLE_CLASS_NAME);
        },
        setContainerAsSearchable: function () {
            $container.find('.' + CONTENT_BOX_CLASS_NAME).addClass(SEARCHABLE_CLASS_NAME);
        },
        setUnsearchable: function () {
            tabs.setAllUnsearchable();
            _.each(this.presets, function (preset) {
                delete preset.data.searchable;
            });
            this.setContainerAsUnsearchable();
        },
        setActivePresetAsUnsearchable: function () {
            this.setUnsearchable();
        },
        setActivePresetAsSearchable: function () {
            this.setUnsearchable();
            tabs.setSearchable(this.activePresetId);
            this.setContainerAsSearchable();
        },
        getOptions: function () {
            var options = {
                presets: {
                    list: []
                },
                search: this.state.search ? 1 : 0
            };

            if (this.isCollapsed()) {
                options.collapsed = 1;
            }

            options.presets[MAIN_PRESET_ID] = this.presets[MAIN_PRESET_ID].data;

            _.each(this.presets, function (preset, key) {
                if (key != MAIN_PRESET_ID) {
                    options.presets.list.push(preset.data);
                }
            });

            return options;
        }
    };

    var init = function () {
        _.each(params.fields || {}, function (data, name) {
            data.name = name;
            self.addField(name, new Field($container.find('.field-' + name), data));
        });
        viewFieldsMenu.onHide(function (name) {
            self.hideField(name);
        });
        viewFieldsMenu.onShow(function (name) {
            self.showField(name);
        });
        viewFieldsMenu.onHideAll(function () {
            self.hideAllFields();
        });
        // presets init
        self.initPreset(MAIN_PRESET_ID, params.options.presets.main);
        var activePreset = MAIN_PRESET_ID;
        _.each(params.options.presets.list || {}, function (data, index) {
            var id = parseInt(index) + 1;
            self.initPreset(id, data);
            if (data.active) {
                activePreset = id;
            }
        });
        self.setActivePreset(activePreset);
        presetsMenu.onDelete(function () {
            self.removeActivePreset();
        });
        presetsMenu.onSave(function () {
            self.saveActivePreset();
        });
        presetsMenu.onSaveAS(function (data) {
            if (!data.name) {
                return false;
            }
            self.saveAsActivePreset(data.name);
            return true;
        });
        presetsMenu.onAdd(function (data) {
            if (!data.name) {
                return false;
            }
            self.addPreset(data.name);
            return true;
        });
        presetsMenu.onRename(function (data) {
            if (!data.name) {
                return false;
            }
            self.renameActivePreset(data.name);
            return true;
        }, function () {
            return self.getActivePresetData().data.name;
        });
        presetsMenu.onCancel(function () {
            self.cancelActivePreset();
        });
        presetsMenu.onSearch(function () {
            self.searchActivePreset();
        });

        scope.$id(params.toggler).on("click", function (e) {
            e.preventDefault();

            self.collapseToggle();
        });
        if (params.options.collapsed) {
            self.collapseToggle();
        }

        tabs.onChoice(function (index) {
            self.setActivePreset(index);
        });

        tabs.onMoveChoice(function (index) {
            self.movePresetAsFirst(index);
        });
    };
    init();
    return {
        onSearch: _.bind(self.onSearch, self)
    };
});
