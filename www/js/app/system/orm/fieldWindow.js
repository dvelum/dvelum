/**
 * Edit window for ORM object field
 *
 * @event dataSaved
 */
Ext.define('app.crud.orm.FieldWindow', {
    extend: 'Ext.window.Window',
    objectName:null,
    fieldName:null,
    dataForm:null,
    objectList:null,
    uniqueData:null,
    isNew:true,
    closeAction:'destroy',

    dictionaryUrl:null,

    constructor: function(config) {
        config = Ext.apply({
            modal: true,
            layout:'fit',
            width: app.checkWidth(500),
            height:app.checkHeight(500),
            closeAction: 'destroy',
            maximizable:true
        }, config || {});
        this.callParent(arguments);
    },

    /**
     * @todo fix columns menu
     */
    initComponent:function(){

        this.fieldDictionaries = Ext.create('Ext.form.field.ComboBox',{
            name:'dictionary',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.DICTIONARY,
            queryMode:'local',
            displayField:'title',
            forceSelection:true,
            valueField:'id',
            store:Ext.create('Ext.data.Store',{
                model:'app.comboStringModel',
                autoLoad:true,
                proxy: {
                    type: 'ajax',
                    url:this.dictionaryUrl + 'list',
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        idProperty: 'id'
                    }
                },
                remoteSort:false,
                sorters: [{
                    property : 'title',
                    direction: 'ASC'
                }]
            }),
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldSetDefault = Ext.create('Ext.form.field.Checkbox',{
            name:'set_default',
            hidden:true,
            fieldLabel:appLang.SET_DEFAULT,
            listeners:{
                render:{fn:this.initTooltip,scope:this},
                change:{fn:this.setCheckDefault,scope:this}
            }
        });

        this.fieldDefaultNum = Ext.create('Ext.form.field.Number',{
            name:'db_default',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.DEFAULT,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldDefaultString = Ext.create('Ext.form.field.Text',{
            name:'db_default',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.DEFAULT,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldLen = Ext.create('Ext.form.field.Number',{
            name:'db_len',
            fieldLabel:appLang.DB_LENGTH,
            allowDecimals:false,
            disabled:true,
            hidden:true,
            value:1,
            minValue:1,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldScale = Ext.create('Ext.form.field.Number',{
            name:'db_scale',
            fieldLabel:appLang.DB_SCALE,
            allowDecimals:false,
            disabled:true,
            hidden:true,
            minValue:0,
            value:3,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldPrecision = Ext.create('Ext.form.field.Number',{
            name:'db_precision',
            fieldLabel:appLang.DB_PRECISION,
            allowDecimals:false,
            disabled:true,
            hidden:true,
            minValue:0,
            value:1,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldType = Ext.create('Ext.form.field.ComboBox',{
            xtype:'combo',
            name:'db_type',
            fieldLabel:appLang.DB_TYPE ,
            queryMode:'local',
            forceSelection:true,
            displayField:'title',
            valueField:'id',
            store:Ext.create('Ext.data.Store',{
                fields:[
                    {name:'id', type:'string'},
                    {name:'title', type:'string'},
                    {name:'group', type:'string'}
                ],
                remoteSort:false,
                sorters: [{
                    property : 'title',
                    direction: 'ASC'
                }]
            }),
            listeners:{
                select:function(field , value , options){
                    this.dbTypeSelected(field.getValue());
                },
                render:{fn:this.initTooltip,scope:this},
                scope:this
            }
        });

        this.validatorField = Ext.create('Ext.form.field.ComboBox',{
            name:'validator',
            fieldLabel:appLang.VALIDATOR,
            queryMode:'local',
            displayField:'title',
            forceSelection:true,
            valueField:'id',
            triggerAction:'all',
            triggers:{
                clear: {
                    cls: "x-form-clear-trigger",
                    tooltip:appLang.RESET,
                    handler:function(field){
                        field.reset();
                    }
                }
            },
            store:Ext.create('Ext.data.Store',{
                model:'app.comboStringModel',
                autoLoad:true,
                proxy: {
                    type: 'ajax',
                    url:app.crud.orm.Actions.listValidators,
                    reader: {
                        type: 'json',
                        rootProperty: 'data',
                        idProperty: 'id'
                    }
                },
                remoteSort:false,
                sorters: [{
                    property : 'title',
                    direction: 'ASC'
                }]
            }),
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.relationsType = this.fieldLinkType = Ext.create('Ext.form.RadioGroup', {
            fieldLabel:appLang.RELATIONSHIP_TYPE,
            columns: 1,
            hidden:true,
            items: [
                {
                    boxLabel:appLang.RELATIONSHIP_POLYMORPHIC,
                    name:'relations_type',
                    inputValue:'polymorphic',
                    checked:true,
                    listeners:{
                        render:{fn:this.initTooltip,scope:this}
                    }
                },{
                    boxLabel:appLang.RELATIONSHIP_MANY_TO_MANY,
                    name:'relations_type',
                    inputValue:'many_to_many',
                    listeners:{
                        render:{fn:this.initTooltip,scope:this}
                    }
                }
            ]
        });

        this.fieldLinkType = Ext.create('Ext.form.RadioGroup', {
            fieldLabel: appLang.LINK_TYPE,
            columns: 2,
            hidden:true,
            items: [
                {boxLabel: appLang.SINGLE_LINK, name: 'link_type',  inputValue: 'object', checked: true,
                    listeners:{
                        'change':{
                            fn:function( field, newValue, oldValue, options ){
                                if(newValue){
                                    this.processFields(
                                        [
                                            this.fieldDictionaries,
                                            this.relationsType
                                        ],[
                                            this.fieldObject,
                                            this.fieldRequired
                                        ]
                                    );
                                }
                            },
                            scope:this
                        },
                        render:{fn:this.initTooltip,scope:this}

                    }},
                {boxLabel: appLang.MULTI_LINK, name: 'link_type', inputValue: 'multi',
                    listeners:{
                        'change':{
                            fn:function( field, newValue, oldValue, options ){
                                if(newValue){
                                    this.processFields(
                                        [
                                            this.fieldDictionaries,
                                            this.fieldRequired,
                                        ],[
                                            this.fieldObject,
                                            this.relationsType
                                        ]
                                    );
                                }
                            },
                            scope:this
                        },
                        render:{fn:this.initTooltip,scope:this}
                    }},
                {
                    boxLabel: appLang.DICTIONARY,
                    name: 'link_type',
                    inputValue: 'dictionary',
                    listeners:{
                        'change':{
                            fn:function( field, newValue, oldValue, options ){
                                if(newValue){
                                    this.processFields(
                                        [
                                            this.fieldObject,
                                            this.relationsType
                                        ],[
                                            this.fieldDictionaries,
                                            this.fieldRequired
                                        ]
                                    );
                                }
                            },
                            scope:this
                        },
                        render:{fn:this.initTooltip,scope:this}
                    }
                }
            ]
        });

        this.fieldObject = Ext.create('Ext.form.field.ComboBox',{
            xtype:'combo',
            name:'object',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.OBJECT,
            queryMode:'local',
            valueField:'id',
            forceSelection:true,
            displayField:'title',
            store:Ext.create('Ext.data.Store',{
                model:'app.comboStringModel',
                data:this.objectList,
                remoteSort:false,
                proxy: {
                    type: 'ajax'
                },
                sorters: [{
                    property : 'title',
                    direction: 'ASC'
                }]
            }),
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldIsNull = Ext.create('Ext.form.field.Checkbox',{
            xtype:'checkbox',
            name:'db_isNull',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.IS_NULL ,
            listeners:{
                'change':{
                    fn:function(field, newValue, oldValue, options ){

                        if(newValue){
                            this.fieldRequired.setValue(0);
                            this.fieldRequired.hide();
                        }else{
                            this.fieldRequired.show();
                        }
                    },
                    scope:this
                },
                render:{fn:this.initTooltip,scope:this}

            }
        });

        this.fieldRequired = Ext.create('Ext.form.field.Checkbox',{
            xtype:'checkbox',
            name:'required',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.REQUIRED,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldUnsigned = Ext.create('Ext.form.field.Checkbox',{
            xtype:'checkbox',
            name:'db_unsigned',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.DB_UNSIGNED,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldAllowHtml = Ext.create('Ext.form.field.Checkbox',{
            xtype:'checkbox',
            name:'allow_html',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.ALLOW_HTML,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldIsSearch = Ext.create('Ext.form.field.Checkbox',{
            xtype:'checkbox',
            name:'is_search',
            disabled:true,
            hidden:true,
            fieldLabel:appLang.IS_SEARCH,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.fieldUnique = Ext.create('Ext.form.field.Text',{
            name:'unique',
            disabled:false,
            hidden:false,
            fieldLabel:appLang.UNIQUE_GROUP,
            listeners:{
                render:{fn:this.initTooltip,scope:this}
            }
        });

        this.dataForm = Ext.create('Ext.form.Panel',{
            bodyPadding:3,
            frame:false,
            bodyCls:'formBody',
            bodyBorder:false,
            border:false,
            scrollable:true,
            fieldDefaults: {
                labelWidth: 140,
                labelAlign:'right',
                anchor:'100%'
            },
            items:[
                {
                    xtype:'textfield',
                    name:'name',
                    fieldLabel:appLang.FIELD_NAME,
                    allowBlank:false,
                    vtype:'alphanum',
                    listeners:{
                        render:{fn:this.initTooltip,scope:this}
                    }
                },
                {
                    xtype:'textfield',
                    name:'title',
                    allowBlank:false,
                    fieldLabel:appLang.FIELD_TITLE,
                    listeners:{
                        render:{fn:this.initTooltip,scope:this}
                    }
                },
                this.fieldUnique,
                {
                    xtype: 'radiogroup',
                    columns:2,
                    fieldLabel: appLang.FIELD_TYPE,
                    items: [
                        /*{
                            boxLabel:'Integer',
                            name: 'type',
                            inputValue:'integer',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Big Integer',
                            name: 'type',
                            inputValue:'biginteger',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Floating',
                            name: 'type',
                            inputValue:'floating',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Decimal',
                            name: 'type',
                            inputValue:'decimal',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Blob',
                            name: 'type',
                            inputValue:'blob',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Char',
                            name: 'type',
                            inputValue:'char',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Varchar',
                            name: 'type',
                            inputValue:'varchar',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Text',
                            name: 'type',
                            inputValue:'text',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Boolean',
                            name: 'type',
                            inputValue:'boolean',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Date',
                            name: 'type',
                            inputValue:'date',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },{
                            boxLabel:'Time',
                            name: 'type',
                            inputValue:'time',
                            listeners:{
                                'change':{
                                    fn:this.onStdFieldSelected,
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}

                            }
                        },*/
                        {
                            boxLabel: appLang.FILED_STD,
                            name: 'type',
                            inputValue: '',
                            checked: true,
                            listeners:{
                                'change':{
                                    fn:function( field, newValue, oldValue, options ){
                                        if(newValue){
                                            this.processFields(
                                                [
                                                    this.fieldLinkType,
                                                    this.fieldObject
                                                ],[
                                                    this.fieldType
                                                ]
                                            );

                                            this.dbTypeSelected(this.fieldType.getValue());

                                        }
                                    },
                                    scope:this
                                }
                            }
                        },
                        {
                            boxLabel: appLang.LINK,
                            name: 'type',
                            inputValue: 'link',
                            listeners:{
                                'change':{
                                    fn:function( field, newValue, oldValue, options ){
                                        if(newValue){
                                            this.processFields(
                                                [
                                                    this.fieldSetDefault,
                                                    this.fieldDefaultString,
                                                    this.fieldDefaultNum,
                                                    this.fieldType,
                                                    this.fieldLen,
                                                    this.fieldScale,
                                                    this.fieldPrecision,
                                                    this.fieldUnsigned,
                                                    this.fieldAllowHtml,
                                                    this.fieldIsSearch,
                                                    this.fieldDictionaries,
                                                    this.validatorField,
                                                    this.fieldIsNull
                                                ],[
                                                    this.fieldLinkType,
                                                    this.fieldObject,
                                                    this.fieldRequired
                                                ]
                                            );
                                        }
                                    },
                                    scope:this
                                },
                                render:{fn:this.initTooltip,scope:this}
                            }
                        },{
                            boxLabel:appLang.ENCRYPTED_FIELD,
                            name: 'type',
                            inputValue: 'encrypted',
                            listeners:{
                                'change':{
                                    fn:function( field, newValue, oldValue, options ){
                                        if(newValue){
                                            this.processFields(
                                                [
                                                    this.fieldDefaultNum,
                                                    this.fieldType,
                                                    this.fieldLen,
                                                    this.fieldScale,
                                                    this.fieldPrecision,
                                                    this.fieldUnsigned,
                                                    this.fieldIsSearch,
                                                    this.fieldDictionaries,
                                                    this.fieldLinkType,
                                                    this.fieldObject,
                                                    this.fieldIsNull,
                                                    this.fieldAllowHtml
                                                ],[
                                                    this.fieldSetDefault,
                                                    this.fieldDefaultString,
                                                    this.validatorField,
                                                    this.fieldRequired
                                                ]
                                            );
                                        }
                                    },
                                    scope:this
                                }
                            }
                        }
                    ]
                },
                this.fieldLinkType,
                this.relationsType,
                this.fieldObject,
                this.fieldType,
                this.validatorField,
                this.fieldLen ,
                this.fieldScale,
                this.fieldPrecision,
                this.fieldSetDefault,
                this.fieldDefaultNum,
                this.fieldDefaultString,
                this.fieldIsNull,
                this.fieldUnsigned,
                this.fieldAllowHtml,
                this.fieldIsSearch,
                this.fieldDictionaries,
                this.fieldRequired
            ]
        });

        if(app.crud.orm.canEdit){
            this.buttons =[
                {
                    text:appLang.SAVE,
                    scope:this,
                    handler:this.saveAction
                },
                {
                    text:appLang.CANCEL,
                    scope:this,
                    handler:this.close
                }
            ];
        }

        this.items = [this.dataForm];

//		this.dbTypeSelected('boolean');
        if(!this.fieldName){
            this.isNew = true;
        }else{
            this.isNew = false;
        }
        if(this.objectName && this.fieldName){
            var handle = this;
            this.on('show' , function(){
                var params = Ext.apply({object:this.objectName,field:this.fieldName});
                this.mask(appLang.LOADING);
                this.dataForm.getForm().load({
                    url:app.crud.orm.Actions.loadObjField,
                    params:params,
                    scope:this,
                    //waitMsg:appLang.LOADING,
                    success: function(form, action){
                        if(!action.result.success){
                            Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                        }else{
                            if(action.result.data.type == 'link'){
                                if(action.result.data.link_config.link_type == "dictionary"){
                                    this.fieldDictionaries.setValue(action.result.data.link_config.object);
                                    this.processFields([],[this.fieldRequired ]);
                                }
                                if(action.result.data.link_config.link_type == 'object'){
                                    this.processFields([],[this.fieldRequired ]);
                                }

                            }else{
                                handle.dbTypeSelected(handle.fieldType.getValue());

                                if(!Ext.isEmpty(action.result.data.db_default)){
                                    this.fieldDefaultString.setValue(action.result.data.db_default);
                                }
                                if(action.result.data.db_default == false){
                                    if(Ext.isDefined(this.fieldDefaultString)){
                                        this.fieldDefaultString.reset();
                                    }
                                    if(Ext.isDefined(this.fieldDefaultNumber)){
                                        this.fieldDefaultNumber.reset();
                                    }
                                }
                            }
                        }
                        this.unmask();
                    },
                    failure:function(form, action){
                        this.unmask();
                        app.formFailure(form, action);
                    }
                });
            },this);
        }

        this.callParent(arguments);
    },
    onStdFieldSelected:function(field, newValue, oldValue, options){
        if(newValue){
            this.fieldType.getStore().filter('group',field.inputValue);
            this.processFields(
                [
                    this.fieldLinkType,
                    this.fieldObject
                ],[
                    this.fieldType
                ]
            );
            this.dbTypeSelected(this.fieldType.getValue());
        }
    },
    setTableEngine:function(engine){
        switch (engine) {
            case 'Memory':
                this.fillDbTypesStore({
                    'integer':app.orm.dataTypes.integer,
                    'floating':app.orm.dataTypes.floating,
                    'string':app.orm.dataTypes.string,
                    'date':app.orm.dataTypes.date,
                    'boolean':app.orm.dataTypes.boolean
                });
                break;
            default:
                this.fillDbTypesStore({
                    'integer':app.orm.dataTypes.integer,
                    'floating':app.orm.dataTypes.floating,
                    'string':app.orm.dataTypes.string,
                    'date':app.orm.dataTypes.date,
                    'boolean':app.orm.dataTypes.boolean,
                    'text':app.orm.dataTypes.text
                });
                break;
        }
    },
    fillDbTypesStore:function(arrayTypes){
        var data = [];
        Ext.Object.each(arrayTypes, function(index, types){
            Ext.each(types, function(type){
                data.push({id:type,title:type,group:index});
            });
        });
        this.fieldType.getStore().loadData(data,false);
    },
    saveAction:function(){
        var handle = this;

        this.dataForm.getForm().submit({
            clientValidation: true,
            waitMsg:appLang.SAVING,
            method:'post',
            url:app.crud.orm.Actions.saveObjField,
            params:{'objectName':this.objectName,'objectField':this.fieldName},
            success: function(form, action) {
                if(!action.result.success){
                    Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                } else{
                    handle.fireEvent('dataSaved');
                    handle.close();
                }
            },
            failure: app.formFailure
        });
    },
    /**
     * @param [Array] hide - fields to hide
     * @param [Array] show - fields to show
     * @returs void
     */
    processFields:function(hide , show){

        Ext.each(hide,function(item){
            item.disable();
            item.hide();
        });

        Ext.each(show,function(item){
            item.enable();
            item.show();
        });

        if(!this.fieldSetDefault.getValue()){
            this.fieldDefaultString.disable();
            this.fieldDefaultNum.disable();
        }
    },
    dbTypeSelected:function(value)
    {
        this.processFields([
            this.fieldDictionaries,
            this.fieldSetDefault,
            this.fieldDefaultString,
            this.fieldLen,
            this.fieldScale,
            this.fieldPrecision,
            this.fieldAllowHtml,
            this.fieldIsSearch
        ] , [
            this.fieldUnique,
            this.validatorField,
            this.fieldIsNull,
            this.fieldRequired,
            this.fieldUnsigned

        ]);

        if(value == 'boolean'){
            this.fieldDefaultNum.setMinValue(0);
            this.fieldDefaultNum.setMaxValue(1);
            this.processFields(
                [	this.validatorField,this.fieldUnsigned,
                    this.fieldRequired
                ],
                [this.fieldSetDefault,this.fieldDefaultNum]
            );
            return;
        }

        this.fieldDefaultNum.setMinValue(Number.NEGATIVE_INFINITY);
        this.fieldDefaultNum.setMaxValue(Number.MAX_VALUE);

        if(Ext.Array.indexOf(app.crud.orm.intTypes , value)!=-1){
            this.processFields(
                [],
                [this.fieldSetDefault,this.fieldDefaultNum]
            );
            return;
        }

        if(Ext.Array.indexOf(app.crud.orm.floatTypes, value)!=-1){
            this.processFields(
                [],
                [this.fieldSetDefault,this.fieldDefaultNum,this.fieldScale,this.fieldPrecision]
            );
            if(this.isNew){
                this.fieldScale.setValue(12);
                this.fieldPrecision.setValue(2);
            }
            return;
        }

        if(Ext.Array.indexOf(app.crud.orm.charTypes, value)!=-1){
            this.processFields(
                [this.fieldDefaultNum,this.fieldUnsigned],
                [this.fieldSetDefault,this.fieldIsSearch,this.fieldAllowHtml,this.fieldDefaultString,this.fieldLen]
            );

            if(this.isNew){
                this.fieldLen.setValue(255);
            }
            return;
        }

        if(Ext.Array.indexOf(app.crud.orm.textTypes, value)!=-1){
            this.processFields(
                [this.fieldSetDefault,this.fieldDefaultNum,this.fieldUnsigned,this.fieldDefaultString,this.fieldIsNull],
                [this.fieldIsSearch,this.fieldAllowHtml]
            );
            return;
        }

//		if(Ext.Array.indexOf(app.crud.orm.blobTypes, value)!=-1){
//			this.processFields(
//					[
//						this.fieldDefaultNum,this.fieldUnsigned,
//						this.fieldDefaultString,this.fieldRequired
//					],
//					[]
//			);
//			return;
//		}

        if(Ext.Array.indexOf(app.crud.orm.dateTypes, value)!=-1){
            this.processFields(
                [this.fieldDefaultNum,this.fieldUnsigned,this.fieldIsNull],
                []
            );
            return;
        }
    },
    /**
     * Show default value field
     */
    setCheckDefault:function(field , newValue, oldValue, eOpts){
        fieldType = this.fieldType.getValue();
        var defField;
        if(Ext.Array.indexOf(app.crud.orm.intTypes , fieldType)!=-1 || fieldType == 'boolean' || Ext.Array.indexOf(app.crud.orm.floatTypes, fieldType)!=-1){
            defField = this.fieldDefaultNum;
        }else{
            defField = this.fieldDefaultString;
        }
        if(newValue){
            defField.enable();
        }else{
            defField.disable();
        }
    },
    initTooltip:function(field){
        var name = field.name;
        var qTipName = 'qtip_field_' + name;

        if(Ext.isEmpty(ormTooltips[qTipName]))
            return;

        Ext.create('Ext.tip.ToolTip', {
            target:field.getEl(),
            html:ormTooltips[qTipName]
        });
    }
});