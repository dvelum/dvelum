/**
 * Column renderer editor window
 * @event dataLoaded
 * @event dataSaved
 */
Ext.define('designer.grid.column.RendererWindow', {
    extend: 'Ext.Window',
    layout: 'fit',
    objectName : '',
    columnId: '',
    controllerUrl:'',
    width:400,
    height:500,
    maximizable:true,
    modal:true,
    initComponent:function(){
        var me = this;
        this.renderersStore = Ext.create('Ext.data.Store',{
            model:'app.comboStringModel',
            proxy: {
                type: 'ajax',
                url:this.controllerUrl + 'renderers',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    idProperty: 'id'
                },
                extraParams:{
                    object:this.objectName
                },
                simpleSortMode: true
            },
            remoteSort: false,
            autoLoad: true,
            sorters: [{
                property : 'title',
                direction: 'DESC'
            }]
        });

        this.dictionaryStore = Ext.create('Ext.data.Store',{
            model:'app.comboStringModel',
            proxy: {
                type: 'ajax',
                url:this.controllerUrl + 'dictionaries',
                reader: {
                    type: 'json',
                    rootProperty: 'data',
                    idProperty: 'id'
                },
                extraParams:{
                    object:this.objectName
                },
                simpleSortMode: true
            },
            remoteSort: false,
            autoLoad: true,
            sorters: [{
                property : 'title',
                direction: 'DESC'
            }]
        });

        this.callEditor = Ext.create('designer.codeEditor',{
            readOnly:false,
            showSaveBtn:false,
            hidden:true,
            flex:1,
            anchor:'100%',
            hideLabel:true,
            sourceCode:'',
            name:'call',
            extraKeys: {
                "Ctrl-Space": function(cm) {
                    CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);
                },
                "Ctrl-S": function(cm) {me.saveData();},
                "Ctrl-Z": function(cm) {me.callEditor.undoAction();},
                "Ctrl-Y": function(cm) {me.callEditor.redoAction();},
                "Shift-Ctrl-Z": function(cm) {me.callEditor.redoAction();}
            }
        });



        this.rendererAdapter = Ext.create('Ext.form.field.ComboBox',{
            typeAhead: true,
            triggerAction: 'all',
            selectOnTab: true,
            forceSelection:true,
            queryMode:'local',
            displayField:'title',
            valueField:'id',
            fieldLabel:desLang.renderer,
            store: this.renderersStore,
            hidden:true,
            name:'adapter'
        });

        this.dictionaryAdapter = Ext.create('Ext.form.field.ComboBox',{
            typeAhead: true,
            triggerAction: 'all',
            selectOnTab: true,
            forceSelection:true,
            queryMode:'local',
            displayField:'title',
            valueField:'id',
            fieldLabel:desLang.dictionary,
            store: this.dictionaryStore,
            hidden:true,
            name:'dictionary'
        });

        this.typeBox = Ext.create('Ext.form.field.ComboBox',{
            fieldLabel:desLang.rendererType,
            name:'type',
            forceSelection:true,
            displayField:'title',
            valueField:'id',
            allowBlank:false,
            store:Ext.create('Ext.data.Store',{
                model:'app.comboStringModel',
                data:[
                    {id:'adapter',title:desLang.adapter},
                    {id:'jscall',title:desLang.jsCall},
                    {id:'jscode',title:desLang.extRenderer},
                    {id:'dictionary', title:desLang.dictionary}
                ]
            }),
            queryMode:'local',
            listeners:{
                change:{
                    fn:this.onTypeSelected,
                    scope:this

                }
            }
        });

        this.editor = Ext.create('designer.codeEditor',{
            readOnly:false,
            showSaveBtn:false,
            hidden:true,
            flex:1,
            sourceCode:'',
            name:'code',
            headerText:'{',
            footerText:'}',
            extraKeys: {
                "Ctrl-Space": function(cm) {
                    CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);
                },
                "Ctrl-S": function(cm) {me.saveData();},
                "Ctrl-Z": function(cm) {me.editor.undoAction();},
                "Ctrl-Y": function(cm) {me.editor.redoAction();},
                "Shift-Ctrl-Z": function(cm) {me.editor.redoAction();}
            }
        });

        this.codeEditorHead = Ext.create('Ext.form.FieldContainer',{
            hidden:true,
            layout: {
                type: 'hbox',
                pack: 'start',
                align: 'stretch'
            },
            height:22,
            items:[
                {
                    xtype:'displayfield',
                    value:' <span style="color:#7F0055;font-weight:bold;">function</span>(&nbsp;'
                },{
                    xtype:'textfield',
                    name:'params',
                    flex:2,
                    readOnly:true,
                    fieldStyle:{
                        border:'none',
                        background:'none',
                        backgroundColor:'#F4F4F4',
                        color:'#5C3BFB'
                    },
                    value:' value, metaData, record, rowIndex, colIndex, store, view'
                },{
                    xtype:'displayfield',
                    value:'&nbsp;  )'
                }
            ]
        });

        this.dataForm = Ext.create('Ext.form.Panel',{
            bodyCls:'formBody',
            layout:{
                type: 'vbox',
                align : 'stretch',
                pack  : 'start'
            },
            bodyPadding:4,
            fieldDefaults:{
                labelWidth:90,
                labelAlign:'right'
            },
            items:[
                this.typeBox,
                this.rendererAdapter,
                this.dictionaryAdapter,
                this.callEditor,
                this.codeEditorHead,
                this.editor
            ]
        });
        this.items = [this.dataForm];

        this.buttons = [
            {
                text:desLang.save,
                scope:this,
                handler:this.saveData
            },
            {
                text:desLang.cancel,
                scope:this,
                handler:this.close
            }
        ];

        this.callParent();
        this.loadData();
    },
    onTypeSelected:function(combo,v){
        this.rendererAdapter.hide();
        this.codeEditorHead.hide();
        this.editor.hide();
        this.callEditor.hide();
        this.dictionaryAdapter.hide();
        switch(v){
            case 'dictionary':
                this.dictionaryAdapter.show();
                break;
            case 'jscall':
                this.callEditor.show();
                break;
            case 'jscode':
                this.codeEditorHead.show();
                this.editor.show();
                break;
            case 'adapter':
            default :
                this.rendererAdapter.show();
                break;
        }
    },
    loadData:function(){
        var form = this.dataForm.getForm();
        var me = this;
     //   form.waitMsgTarget = me.getEl();
        form.load({
            waitMsg:desLang.loading,
            url:this.controllerUrl + 'rendererload',
            method:'post',
            params: {
                'object':this.objectName,
                'column':this.columnId
            },
            success: function(form, action)
            {
                if(action.result.success) {
                    if(!Ext.isEmpty(action.result.data.call)){
                        me.callEditor.setValue(action.result.data.call);
                    }
                    if(!Ext.isEmpty(action.result.data.code)){
                        me.editor.setValue(action.result.data.code);
                    }
                    me.fireEvent('dataLoaded' ,action.result);
                } else {
                    Ext.Msg.alert(desLang.message, action.result.msg).toFront();
                    me.close();
                }
            },
            failure: app.formFailure
        });
    },
    saveData:function(){
        var me = this;
        var form = this.dataForm.getForm();

       // form.waitMsgTarget = me.getEl();
        form.submit({
            clientValidation: true,
            method:'post',
            url:this.controllerUrl + 'renderersave',
            params:{
                'object':this.objectName,
                'column':this.columnId,
                'code': this.editor.getValue(),
                'call': this.callEditor.getValue()
            },
            waitMsg:appLang.SAVING,
            success: function(form, action)
            {
                if(!action.result.success){
                    Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
                    return;
                }
                me.fireEvent('dataSaved');
                me.close();
            },
            failure: app.formFailure
        });
    }
});