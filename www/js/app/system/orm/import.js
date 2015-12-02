Ext.ns('app.orm.import');

/**
 *
 * @event importComplete
 *
 */
Ext.define('app.orm.import.Window',{
	extend:'Ext.Window',
	dataGrid:null,
	controllerUrl:'',
	dbConfigs:null,
	modal:true,
	layout:'border',

	constructor: function(config) {
		config = Ext.apply({
	        title: appLang.ORM_IMPORT,
	        width: 700,
	        height:400,
			bodyCls:'formBody',
	        closeAction: 'destroy',
	        maximizable:true
	    }, config || {});

		this.callParent(arguments);
	},

	initComponent:function(){

		this.dataGrid = Ext.create('Ext.grid.Panel', {
			title:appLang.DB_TABLES,
			region:'center',
			scrollable:true,
			hideHeaders:true,
			columnLines:true,
			store:Ext.create('Ext.data.Store', {
				fields:[
				        {name:'name' , type:'string'}
				],
				proxy:{
					url:app.createUrl([app.admin , 'orm' , 'connections','externaltables']),
					type:'ajax',
					reader:{
						type:'json',
						idProperty:'name',
						rootProperty:'data'
					},
					extraParams:{
						'type':0,
						'connId':'default'
					}
				},
				autoLoad:false,
				sorters: [{
	                  property : 'name',
	                  direction: 'ASC'
	            }]
			}),
			columns:[{
        	 	dataIndex:'name',
        	 	flex:1
         	}]
		});

		this.typeField = Ext.create('Ext.form.field.ComboBox',{
			typeAhead: false,
		    triggerAction: 'all',
		    forceSelection:true,
		    displayField:'title',
		    valueField:'id',
		    queryMode:'local',
		    allowBlank:false,
		    fieldLabel:appLang.TYPE,
		    value:0,
			store:Ext.create('Ext.data.Store',{
					fields: [
					   {name:'id' ,  type:'integer'},
					   {name:'title' ,  type:'string'}
					],
					proxy: {
						type: 'ajax',
						url:app.createUrl([app.admin , 'orm' , 'connectiontypes']),
						reader: {
				            type: 'json',
							rootProperty: 'data',
				            idProperty: 'id'
				        }
		  		    },
	    	  		autoLoad:true,
	  		      	sorters: [{
		                	property : 'title',
		                	direction: 'ASC'
			      	}]
			}),
			listeners:{
    		  	select:function(field , records , options){
    		  		var store = this.dataGrid.getStore();
    		  		store.removeAll();
    		  		store.proxy.setExtraParam('type' , records.get('id'));
    		  		if(store.proxy.extraParams['connId']){
    		  			store.load();
    		  		}
    		  	},
    		  	scope:this
    	  	}
		});

		this.connectionField = Ext.create('Ext.form.field.ComboBox',{
    	  	fieldLabel:appLang.DB_CONNECTION,
    	  	queryMode:'local',
    	  	typeAhead:true,
    	  	forceSelection:true,
    	  	displayField:'id',
    	  	valueField:'id',
    	  	value:'default',
    	  	store:Ext.create('Ext.data.Store',{
    		  	model:'app.comboStringModel',
		      	proxy: {
					type: 'ajax',
					url:app.createUrl([app.admin , 'orm' , 'connectionslist']),
					reader: {
			            type: 'json',
						rootProperty: 'data',
			            idProperty: 'id'
			        }
	  		    },
    	  		autoLoad:true,
  		      	sorters: [{
	                	property : 'id',
	                	direction: 'ASC'
		      	}]
    	  	}),
    	  	listeners:{
    		  	select:function(field , records , options){
    		  		var store = this.dataGrid.getStore();
    		  		store.removeAll();
    		  		store.proxy.extraParams['connId'] = records.get('id');
    		  		if(store.proxy.extraParams['type']){
    		  			store.load();
    		  		}
    		  	},
    		  	scope:this
    	  	}
		});


		this.importForm = Ext.create('Ext.form.Panel',{
			frame:false,
			region:'north',
			margin:5,
			border:false,
			bodyCls:'formBody',
			height:70,
			fieldDefaults:{
				labelAlign:'right',
				labelWidth:120
			},
			items: [this.connectionField , this.typeField]
		});

		this.items = [this.importForm , this.dataGrid];
		this.buttons = [{
			text:appLang.DB_CONNECT_EXTERNAL,
			handler:this.importTable,
			scope:this
		}];

		this.callParent();
	},
	importTable:function(){

		var sm = this.dataGrid.getSelectionModel();
		if(!sm.hasSelection()){
			Ext.Msg.alert(appLang.MESSAGE , appLang.MSG_SELECT_TABLE_FOR_CONNECT);
			return;
		}
		var me = this;

		me.getEl().mask(appLang.LOADING);

		Ext.Ajax.request({
			url:app.createUrl([app.admin , 'orm' , 'connections','connectobject']),
			method: 'post',
			params:{
				type:this.typeField.getValue(),
				connId:this.connectionField.getValue(),
				table:sm.getSelection()[0].get('name')
			},
			scope:this,
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(!response.success){
	 				me.getEl().unmask();
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 			} else{
	 				me.getEl().unmask();
	 				me.fireEvent('importComplete');
	 				me.close();
	 			}
	 		},
	 		failure:function(){
	 			me.getEl().unmask();
	 			app.ajaxFailure(arguments);
	 		}
		});
	}
});
