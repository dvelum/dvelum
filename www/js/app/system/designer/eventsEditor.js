Ext.define('designer.eventsEditorModel',{
	extend:'Ext.data.Model',
	fields:[
		{name:'id', type:'integer'},
		{name:'object', type:'string'},
		{name:'event', type:'string'},
		{name:'params', type:'string'},
		{name:'is_local' , type:'boolean'}
	],
	idProperty:'id'
});

/**
 *
 * @event eventsUpdated
 *
 */
Ext.define('designer.eventsEditor',{
	extend:'Ext.grid.Panel',
	scrollable:true,
	controllerUrl:null,
	searchField:null,
	columnLines:true,
	viewConfig:{
		stripeRows: true,
		enableTextSelection: true
	},
	initComponent:function(){
		
		if(!this.controllerUrl.length){
			this.controllerUrl = app.createUrl([designer.controllerUrl ,'events','']);
		}

		this.store = Ext.create('Ext.data.Store',{
			model:'designer.eventsEditorModel',
			proxy: {
		        type: 'ajax',
		    	url:this.controllerUrl +  'list',
		        reader: {
		            type: 'json',
		            rootProperty: 'data',
		            idProperty: 'id'
		        },
			    simpleSortMode: true
		    },
		    groupField:'object',
	        remoteSort: false,
		    autoLoad: false,
		    sorters: [{
                property : 'object',
                direction: 'DESC'
            },{
                property : 'event',
                direction: 'DESC'
            }]
		});
		
		this.searchField = Ext.create('SearchPanel',{
			store:this.store,
			local:true,
			fieldNames:['object','event']
		});
		
		this.tbar =[
		            {
		            	iconCls:'refreshIcon',
		            	tooltip:desLang.refresh,
		            	scope:this,
		            	handler:function(){
		            		this.store.load();
		            	}
		            },
		            this.searchField
		 ];
			
		this.columns =[
		 {
			  xtype:'actioncolumn',
			  width:20,
			  items:[
			         {
			        	 iconCls:'editIcon',
			        	 handler:function(grid, rowIndex, colIndex){
			        		 var rec = grid.getStore().getAt(rowIndex);
			        		 this.editEvent(rec);
			        	 },
			        	 scope:this
			         }
			  ]
		   },{
			  text:desLang.object,
			  dataIndex:'object',
			  width:150 
		  },{
			  text:desLang.event,
			  dataIndex:'event',
			  width:150 
		  },{
			  text:desLang.params,
			  dataIndex:'params',
			  flex:1
		  },{
			  xtype:'actioncolumn',
			  width:20,
			  items:[
			         {
			        	 iconCls:'deleteIcon',
			        	 handler:function(grid, rowIndex, colIndex){
			        		 var rec = grid.getStore().getAt(rowIndex);
			        		 this.removeEvent(rec);
			        	 },
			        	 scope:this
			         }
			  ]
		  }  		               
		];
		
		
		this.features=[Ext.create('Ext.grid.feature.Grouping',{
			groupHeaderTpl: '{name} ({rows.length})',
			startCollapsed: 0,
			enableGroupingMenu: 1,
			hideGroupedHeader:0
		})];
		
		this.on('itemdblclick',function(view , record , number , event , options){
			this.editEvent(record);
		},this);
		
		this.callParent();
	},
	removeEvent:function(record){
		Ext.Ajax.request({
		 	url:this.controllerUrl +'removeevent',
		 	method: 'post',
		 	scope:this,
		 	params:{
		 		object:record.get('object'),
		 		event:record.get('event')
		 	},
		    success: function(response, request) {
		 		response =  Ext.JSON.decode(response.responseText);
		 		if(!response.success){	 			
		 			Ext.Msg.alert(appLang.MESSAGE,response.msg);
		 			return;
		 		}		 		
		 		designer.msg(appLang.MESSAGE , desLang.msg_listenerRemoved);
				this.getStore().remove(record);
				this.getStore().commitChanges();
				this.fireEvent('eventsUpdated');
		    },
		    failure:function() {
		       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
		    }
		 });
	},
	editEvent:function(record)
	{
		Ext.create('designer.eventsEditorWindow',{
			controllerUrl:this.controllerUrl,
			objectName:record.get('object'),
			eventName:record.get('event'),
			paramsString:record.get('params'),
			modal:false,
			listeners:{
				'codeSaved':{
					fn:function(){
						record.set('has_code',true);
						record.commit();
						this.fireEvent('eventsUpdated');
					},
					scope:this
				}
			}
		}).show();
	}
});
/**
 *
 * @event codeSaved
 *
 * @event eventUpdated
 */
Ext.define('designer.eventsEditorWindow',{
	extend:'Ext.Window',
	modal:true,
	width:800,
	height:600,
	layout:{
		type: 'vbox',
		align : 'stretch',
		pack  : 'start'
	},
	autoRender:true,
	maximizable:true,
	extraParams:null,
	closeAction:'destroy',
	controllerUrl:'',
	objectName:'',
	eventName:'',
	paramsString:'',
	editor:null,
	loadedConfig:null,
	bufferField:null,
	
	constructor:function(){
		this.extraParams = {};
		this.callParent(arguments);
	},
	
	initComponent:function(){
		
		this.extraParams['object'] = this.objectName;
		this.extraParams['event'] = this.eventName;
		
		this.title = this.objectName + ' on ' + this.eventName;
		
		this.saveButton = Ext.create('Ext.Button',{
			disabled:true,
			text:desLang.save,
			scope:this,
			handler:this.saveEvent
		});
		
		this.cancelButton = Ext.create('Ext.Button',{
			text:desLang.close,
			scope:this,
			handler:this.close
		});
		
		this.buttons = [this.saveButton , this.cancelButton];

		this.bufferField = Ext.create('Ext.form.field.Number',{
			fieldLabel:'Buffer, ms',
			labelWidth:90,
			labelAlign:'right',
			width:150
		});

		this.tbar = [
			'->',
			this.bufferField
		];

		this.dataForm  = Ext.create('Ext.form.Panel',{
			bodyPadding:5,
			bodyCls:'formBody',
			border:false,
			autoHeight:true,
			split:false,
			items:[
					{
						xtype:'fieldcontainer',
						layout: {
							type: 'hbox',
							pack: 'start',
							align: 'stretch'
						},
						height:22,
						items:[
						   {						   
							   xtype:'textfield',
							   name:'new_name',
							   flex:1,
							   fieldStyle:{
								   border:'none',
								   //   textAlign:'right',
								   background:'none',
								   backgroundColor:'#F4F4F4'
								   //borderBottom:'1px solid #000000'
							   }
						   },{						   
							   xtype:'displayfield',
							   value:' : <span style="color:#7F0055;font-weight:bold;">function</span>(  '
						   },{						   
							   xtype:'textfield',
							   name:'params',
							   flex:2,
							   fieldStyle:{
								   border:'none',
								   //   textAlign:'left',
								   background:'none',
								   backgroundColor:'#F4F4F4',
								   //borderBottom:'1px solid #000000',
								   color:'#5C3BFB'
							   }
						   },{						   
							   xtype:'displayfield',
							   value:'  )'
						   }
						]
					}
				]
		});
		
		//this.items = [this.centerPanel];
		this.callParent();
        this.on('show', function(){
            this.loadCode();
            app.checkSize(this);
            Ext.WindowMgr.register(this);
            Ext.WindowMgr.bringToFront(this);
        }, this);
	},
	
	loadCode:function(){
		var me = this;
		Ext.Ajax.request({
		 	url:this.controllerUrl +'eventcode',
		 	method: 'post',
		 	scope:this,
		 	params:this.extraParams,
		    success: function(response, request) {

		 		response =  Ext.JSON.decode(response.responseText);
		 		if(!response.success){	 			
		 			Ext.Msg.alert(appLang.MESSAGE,response.msg);
		 			return;
		 		}		 		
		 		
		 		this.loadedConfig = response.data;
		 		
		 		if(!Ext.isEmpty(response.data.is_local) && response.data.is_local){
					me.editor = Ext.create('designer.codeEditor',{
				       	readOnly:false,
				       	showSaveBtn:false,
						flex:1,
				       	sourceCode:response.data.code,
				       	headerText:'{',
				       	footerText:'}',
				       	extraKeys: {
				        	"Ctrl-Space": function(cm) {
				        		CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);
				        	},
				        	"Ctrl-S": function(cm) {me.saveEvent();},
				        	"Ctrl-Z": function(cm) {me.editor.undoAction();},
				        	"Ctrl-Y": function(cm) {me.editor.redoAction();},
				        	"Shift-Ctrl-Z": function(cm) {me.editor.redoAction();}
				        }
				     });
					
					var form = this.dataForm.getForm();
					form.findField('new_name').setValue(response.data['event']);
					form.findField('params').setValue(response.data['params']);

					this.add(this.dataForm);
		 		}else{
					me.editor = Ext.create('designer.codeEditor',{
				       	readOnly:false,
				       	showSaveBtn:false,
						flex:1,
				       	sourceCode:response.data.code,
				       	headerText:'<span style="color:blue;font-weight:bold">function</span> ( '+this.paramsString+' ) { ',
				       	footerText:'}',
				       	extraKeys: {
				        	"Ctrl-Space": function(cm) {
				        		CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);
				        	},
				        	"Ctrl-S": function(cm) {me.saveEvent();},
				        	"Ctrl-Z": function(cm) {me.editor.undoAction();},
				        	"Ctrl-Y": function(cm) {me.editor.redoAction();},
				        	"Shift-Ctrl-Z": function(cm) {me.editor.redoAction();}
				        }
				     });
		 		}

		 		if(!Ext.isEmpty(me.loadedConfig.buffer) && me.loadedConfig.buffer!==false){
					me.bufferField.setValue(me.loadedConfig.buffer);
				}

		 		this.add(me.editor);
		 		this.saveButton.enable();
		    },
		    failure:function() {
		       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
		    }
		 });
	},
	
	saveEvent:function(){
		var code = this.editor.getValue();
		
		var params = Ext.clone(this.extraParams);
		params['code'] = code;
		params['buffer'] = this.bufferField.getValue();

		if(this.loadedConfig.is_local){
			var form = this.dataForm.getForm();
			params['new_name'] = form.findField('new_name').getValue();
			params['params'] = form.findField('params').getValue();
		}
		
		Ext.Ajax.request({
		 	url:this.controllerUrl +'saveevent',
		 	method: 'post',
		 	scope:this,
		 	params:params,
		    success: function(response, request) {
		 		response =  Ext.JSON.decode(response.responseText);
		 		if(!response.success){	 			
		 			Ext.Msg.alert(appLang.MESSAGE,response.msg);
		 			return;
		 		}		
		 		designer.msg(appLang.MESSAGE , desLang.msg_codeSaved);
		 		
		 		if(this.loadedConfig.is_local){
		 			this.eventName = form.findField('new_name').getValue();
			 		this.extraParams['event'] = this.eventName;
		 			this.fireEvent('eventUpdated');
		 		}else{
		 			this.fireEvent('codeSaved');
		 		}
		    },
		    failure:function() {
		       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
		    }
		 });
		
	},
	destroy:function(){
		this.saveButton.destroy();
		this.bufferField.destroy();
		this.cancelButton.destroy();
		this.dataForm.destroy();
		this.extraParams = null;
		this.buttons = null;
		this.loadedConfig = null;
		this.callParent(arguments);
	}
});