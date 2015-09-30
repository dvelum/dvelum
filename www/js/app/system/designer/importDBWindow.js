/**
 *
 */
Ext.ns('designer.importDBWindow.js');

/**
 *
 * @event select
 * @param {Array} field names
 * @param {String} connection name
 * @param {String} table name
 *
 */
Ext.define('designer.importDBWindow',{
	extend:'Ext.window.Window',

	controllerUrl:null,

	connectionField:null,
	tableField:null,

	dataForm:null,
	fieldsGrid:null,

	initComponent:function(){
		this.modal = true;
		this.width = 400;
		this.height = 450;
		this.layout = 'fit';

		this.controllerUrl = app.createUrl([designer.controllerUrl,'db','']);

		this.typeField = Ext.create('Ext.form.field.ComboBox',{
			typeAhead: false,
		    triggerAction: 'all',
		    forceSelection:true,
		    displayField:'title',
		    valueField:'id',
		    allowBlank:false,
		    fieldLabel:desLang.type,
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
    		  	select:function(field , record , options){
    		  		this.tableField.getStore().proxy.setExtraParam('type' , record.get('id'));
    		  		if(this.tableField.getStore().proxy.extraParams['connId']){
    		  			this.tableField.getStore().load();
    		  		}
    		  		this.fieldsGrid.getStore().removeAll();
    		  	},
    		  	scope:this
    	  	}
		});


		this.connectionField = Ext.create('Ext.form.field.ComboBox',{
    	  	fieldLabel:desLang.connection,
    	  	queryMode:'local',
    	  	typeAhead:true,
    	  	forceSelection:true,
    	  	displayField:'id',
    	  	valueField:'id',
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
    		  	select:function(field , record , options){
    		  		this.tableField.getStore().proxy.setExtraParam('connId' , record.get('id'));
    		  		if(this.tableField.getStore().proxy.extraParams['type']){
    		  			this.tableField.getStore().load();
    		  		}
    		  		this.fieldsGrid.getStore().removeAll();
    		  	},
    		  	scope:this
    	  	}
		});

		this.tableField = Ext.create('Ext.form.field.ComboBox',{
    	  	fieldLabel:desLang.table,
    	  	queryMode:'local',
    	  	forceSelection:true,
    	  	displayField:'title',
    	  	typeAhead:true,
    	  	valueField:'id',
    	  	store:Ext.create('Ext.data.Store',{
    		  	model:'app.comboStringModel',
		      	proxy: {
					type: 'ajax',
					url:app.createUrl([app.admin , 'orm' , 'connections','tablelist']),
					reader: {
			            type: 'json',
						rootProperty: 'data',
			            idProperty: 'id'
			        }
	  		    },
    	  		autoLoad:false,
  		      	sorters: [{
	                	property : 'title',
	                	direction: 'ASC'
		      	}]
    	  	}),
    	  	listeners:{
    		  	select:function(field , records , options){
    		  		this.fieldsGrid.getStore().proxy.setExtraParam('type' , this.typeField.getValue());
    		  		this.fieldsGrid.getStore().proxy.setExtraParam('connId', this.connectionField.getValue());
    		  		this.fieldsGrid.getStore().proxy.setExtraParam('table', field.getValue());
    		  		this.fieldsGrid.getStore().load();
    		  	},
    		  	scope:this
    	  	}
		});

		this.fieldsGrid = Ext.create('Ext.grid.Panel', {
			title:desLang.fields,
			height:300,
			scrollable:true,
			selModel:Ext.create('Ext.selection.CheckboxModel',{}),
			store:Ext.create('Ext.data.Store', {
				fields:[
				        {name:'name' , type:'string'},
				        {name:'type' , type:'string'}
				],
				proxy:{
					url:app.createUrl([app.admin , 'orm' , 'connections','fieldslist']),
					type:'ajax',
					reader:{
						type:'json',
						idProperty:'name',
						rootProperty:'data'
					}
				},
				autoLoad:false,
				sorters: [{
	                  property : 'name',
	                  direction: 'ASC'
	            }]
			}),
			columns:[{
        		text:desLang.name,
        	 	dataIndex:'name',
        	 	flex:1
         	},{
        	 	text:desLang.type,
        	 	dataIndex:'type',
        	 	flex:1
         	}]
		});

		this.dataForm = Ext.create('Ext.form.Panel',{
			border:false,
    		bodyPadding: 15,
    		bodyCls:'formBody',
		    layout: 'anchor',
		    defaults: {
		        anchor: '100%'
		    },
			items:[this.connectionField, this.typeField ,  this.tableField, this.fieldsGrid]
		});

		this.items = [this.dataForm];

		this.buttons = [{
			text:desLang.select,
			scope:this,
			handler:this.onSelect
		},{
			text:desLang.cancel,
			scope:this,
			handler:this.close
		}];

		this.callParent(arguments);
	},
	onSelect:function(){
		var fSm = this.fieldsGrid.getSelectionModel();

		if(!fSm.hasSelection() ){
			Ext.Msg.alert(appLang.MESSAGE, desLang.selectFields);
			return;
		}

		var selection =  fSm.getSelection();
		var names = [];
		Ext.each(selection,function(item , index){
			names.push(item.get('name'));
		},this);

		this.fireEvent('select' , names, this.connectionField.getSubmitValue(), this.tableField.getSubmitValue() , this.typeField.getSubmitValue());
		this.close();
	}
});
