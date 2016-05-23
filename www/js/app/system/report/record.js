/**
 *
 * @event dataChanged
 *
 */
Ext.define('app.report.RecordField',{
    extend:'Ext.panel.Panel',
    alias:'widget.reportfield',

    itemsLoaded:false,
    
    valueSelected:false,
    valueTitle:null,
    valueAlias:null,
    valueIsLink:false,
    valueSelectSub:false,
    valueField:null,
    vauleObject:null,
    valueSubObject:null,
    valueSubObjectTtile:null,
    valuePartId:null,
    
    fieldPanel:null,
    childPanel:null,
    fieldSelected:null,
    fieldTitle:null,
    fieldAlias:null,
    fieldAddSub:null,
    
    controllerUrl:'',
    
    initComponent:function(){
        
        this.bodyPadding = 5;
        //this.bodyCls = 'formBody';
        this.border = false;
        
        this.fieldSelected = Ext.create('Ext.form.field.Checkbox' , {
            inputValue:1,
            uncheckedValue:0,
            width:30,
            checked:this.valueSelected,
            listeners:{
                change:{
                    fn:function( field,  newValue,  oldValue, eOpts ){
                        this.saveField('select' , newValue);
                    },
                    scope:this,
                    buffer:500
                }
            }
        });
        
        this.fieldTitle = Ext.create('Ext.form.field.Text',{
            value:this.valueTitle,
            listeners:{
                change:{
                    fn:function( field,  newValue,  oldValue, eOpts ){
                        this.saveField('title' , newValue);
                    },
                    scope:this,
                    buffer:500
                }
            }
        });
        
        this.fieldAlias = Ext.create('Ext.form.field.Text',{
            value:this.valueAlias,
            listeners:{
                change:{
                    fn:function( field,  newValue,  oldValue, eOpts ){
                        this.saveField('alias' , newValue);
                    },
                    scope:this,
                    buffer:500
                }
            }
        });
            
        if(!this.valueIsLink){
            this.fieldAddSub = {xtype:'displayfield', width:30};
        }else{
            this.fieldAddSub = Ext.create('Ext.button.Button',{
                text:appLang.SUB,
                width:40,
                disabled:this.valueSelectSub,
                scope:this,
                handler:this.selectSub
            });
        }
        

        this.fieldPanel = Ext.create('Ext.panel.Panel',{
            layout:'column',
            autoHeight:true,
            border:false,
            items:[
                      this.fieldSelected,
                      {xtype:'panel', items:[this.fieldAddSub], width:50,border:false},
                      {xtype:'displayfield', value:'<b>' + this.valueField + '</b>', width:150},
                      {xtype:'displayfield', value:appLang.ALIAS, width:65}, 
                      this.fieldAlias,
                      {xtype:'displayfield',width:20},
                      {xtype:'displayfield',value:appLang.TITLE, width:60},
                      this.fieldTitle			       
            ]
        });
        
        
        this.items = [this.fieldPanel];
        
        if(this.valueIsLink){			
            this.childPanel = Ext.create('app.FieldSet' , {
                collapsible:true,
                collapsed:true,
                title:this.valueSubObjectTtile + ' ('+this.valueSubObject+')',
                items:[],
                bodyBorder:false,
                autoHeight:true,
                hidden:!this.valueSelectSub,
                padding:5,
                listeners:{
                    'beforeexpand':{
                        fn:function(cmp){
                            if(!this.itemsLoaded){
                                this.loadPart();
                            }
                        },
                        scope:this
                    }				
                }
            });			
            this.items.push(this.childPanel);
        }
        
        this.callParent(arguments);
    },
    loadPart:function(){
        var me = this;
        me.getEl().mask(appLang.LOADING);
        Ext.Ajax.request({
                url: me.controllerUrl + "loadpart",
                method: 'post',
                params:{
                    object:me.valueSubObject,
                    parentpart:me.valuePartId,
                    parentfield:me.valueField
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success)
                    {
                        Ext.suspendLayouts();
                        me.childPanel.removeAll(); 
                        me.childPanel.add({
                            xtype:'joinselector',
                            controllerUrl: me.controllerUrl,
                            value:response.data.objectcfg.join,
                            parentPart:me.valuePartId,
                            object:me.valueSubObject,
                            parentField:me.valueField,
                            childField:response.data.objectcfg.childField,
                            listeners:{
                                partRemoved:{
                                    fn:me.partRemoved,
                                    scope:me
                                },
                                dataChanged:{
                                    fn:me.dataChanged,
                                    scope:me
                                }
                            }
                        });

                        me.childPanel.setTitle(response.data.objectcfg.title + ' ('+response.data.objectcfg.object+')');
                        me.childPanel.add(response.data.items);
                        me.itemsLoaded = true;		 				 
                        Ext.resumeLayouts(true);
                    }else{
                        Ext.Msg.alert(appLang.MESSAGE,response.msg);   	
                    }
                    me.getEl().unmask();
               },
               failure:function() {
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
                    me.getEl().unmask();
               }
         });
    },
    dataChanged:function(){
        this.up('form').fireEvent('dataChanged');
    },
    partRemoved:function()
    {	
        this.childPanel.removeAll();
        this.childPanel.hide();
        this.fieldAddSub.enable();
        this.itemsLoaded = false;
        this.dataChanged();
    },
    selectSub: function(){
        var me = this;
        Ext.Ajax.request({
            url: me.controllerUrl + "addpart",
            method: 'post',
            params:{
                subobject : me.valueSubObject,
                partid: me.valuePartId,
                objectfield:me.valueField
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){			
                    if(!Ext.isEmpty(response.limit, false) && response.limit){
                        me.childPanel.hide();
                        me.fieldAddSub.enable();
                        Ext.Msg.alert(appLang.MESSAGE,response.msg); 
                    }else{	 				
                        me.childPanel.show();
                        me.childPanel.expand();
                        me.fieldAddSub.disable();
                    }
                        
                }else{
                    Ext.Msg.alert(appLang.MESSAGE,response.msg);   	
                }
           },
           failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   	
           }
     });
    },
    saveField:function(field , value){
        var me = this;
        Ext.Ajax.request({
            url:me.controllerUrl + "savefield",
            method: 'post',
            scope:this,
            params:{
                part : this.valuePartId,
                value:value,
                field:this.valueField,
                fieldoption:field
            },
            success: function(response, request) {
                response =  Ext.JSON.decode(response.responseText);
                if(response.success){			
                    this.dataChanged();
                }else{
                    Ext.Msg.alert(appLang.MESSAGE,response.msg);   	
                }
           },
           failure:function() {
                Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   	
           }
        });
    }
});
/**
 * @event partRemoved
 * @event dataChanged
 *
 */
Ext.define('app.report.RecordJoinSelector',{
    extend:'Ext.panel.Panel',
    alias:'widget.joinselector',
    
    object:null,
    parentPart:null,
    parentField:null,
    childField:null,
    value:null,
    autoHeight:true,
    deleteButton:null,

    initComponent:function(){
        this.layout = 'column';
        this.bodyPadding = 3;
        this.bodyCls = 'formBody';
        this.columns = 3;
        this.height = 28;
        
        this.dataCombo = Ext.create('Ext.form.field.ComboBox',{
          displayField:'title',
          valueField:'id',
          queryMode:'local',
          value:this.value,
          forceSelection:true,
          store:Ext.create('Ext.data.Store', {
              model:'app.comboModel',
              data:[
                    {id:1, title:appLang.LEFT},
                    {id:2 ,title:appLang.RIGHT},
                    {id:3 ,title:appLang.INNER}
              ]
          }),
          listeners:{
              change:{
                  fn:this.joinSelected,
                  scope:this
              }
          }
        });
                
        this.deleteButton = Ext.create('Ext.Button',{
            tooltip:appLang.REMOVE_JOIN,
            iconCls:'deleteIcon',
            handler:this.removePart,
            scope:this
        });
        
        this.tbar = [this.deleteButton , appLang.JOIN_TYPE, this.dataCombo];
        this.callParent(arguments);
    },
    joinSelected:function(){
        var me = this;
        Ext.Ajax.request({
                url: me.controllerUrl+ "setjoin",
                method: 'post',
                params:
                {
                    object:me.object,
                    parentpart:me.parentPart,
                    parentfield:me.parentField,
                    childfield:me.childField,
                    jointype:this.dataCombo.getValue()
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        me.fireEvent('dataChanged');
                    }else{
                        Ext.Msg.alert(appLang.MESSAGE,response.msg);   	
                    }
               },
               failure:function() {
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
               }
         });
    },
    removePart:function(){
        var me = this;
        Ext.Ajax.request({
                url: me.controllerUrl+ "deselectsub",
                method: 'post',
                params:
                {
                    object:me.object,
                    parentpart:me.parentPart,
                    parentfield:me.parentField
                },
                success: function(response, request) {
                    response =  Ext.JSON.decode(response.responseText);
                    if(response.success){
                        me.fireEvent('partRemoved');
                    }else{
                        Ext.Msg.alert(appLang.MESSAGE,response.msg);   	
                    }
               },
               failure:function() {
                    Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
               }
         });
    }
});