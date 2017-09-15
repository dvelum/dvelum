Ext.define('designer.eventsModel',{
	extend:'Ext.data.Model',
	fields:[
		{name:'id', type:'integer'},
		{name:'object', type:'string'},
		{name:'event', type:'string'},
		{name:'params', type:'string'},
		{name:'has_code', type:'boolean'},
		{name:'is_local' , type:'boolean'}
	],
	idProperty:'event'
});

/**
 *
 * @event eventsUpdated
 */
Ext.define('designer.eventsPanel',{
	extend:'Ext.grid.Panel',
	objectName:'',
	controllerUrl:'',
	columnLines:true,
	searchField:null,
	addButton:null,
	canEditLocalEvents:false,

	autoLoadData:true,

	constructor:function(config){
		config = Ext.apply({
			extraParams:{}
		}, config || {});
		this.callParent(arguments);
	},

	initComponent:function()
	{
		if(!this.controllerUrl.length){
			this.controllerUrl = app.createUrl([designer.controllerUrl ,'events','']);
		}

		this.extraParams['object'] = this.objectName;

		this.store = Ext.create('Ext.data.Store',{
			model:'designer.eventsModel',
			proxy: {
				type: 'ajax',
				url:this.controllerUrl +  'objectevents',
				reader: {
					type: 'json',
					rootProperty: 'data'
				},
				extraParams:this.extraParams,
				simpleSortMode: true
			},
			remoteSort: false,
			autoLoad: this.autoLoadData,
			sorters: [{
				property : 'object',
				direction: 'DESC'
			},{
				property : 'event',
				direction: 'DESC'
			}]
		});

		this.addButton = Ext.create('Ext.Button',{
			hidden:!this.canEditLocalEvents,
			scope:this,
			handler:this.addLocalEvent,
			iconCls:'addIcon',
			text:desLang.addEvent
		});

		this.searchField = Ext.create('SearchPanel',{
			store:this.store,
			local:true,
			width:130,
			hideLabel:true,
			fieldNames:['event']
		});

		this.tbar = [this.addButton , this.searchField];

		this.columns = [
			{
				xtype:'actioncolumn',
				width:40,
				items:[
					{
						width:20,
						tooltip:desLang.editAction,
						scope:this,
						iconCls:'editIcon',
						handler:function(grid, rowIndex, colIndex){
							var rec = grid.getStore().getAt(rowIndex);
							this.editEvent(rec);
						}
					},
					{
						iconCls:'deleteIcon',
						tooltip:desLang.removeAction,
						handler:function(grid, rowIndex, colIndex){
							var rec = grid.getStore().getAt(rowIndex);
							this.removeEvent(rec);
						},
						width:20,
						scope:this
					}
				]
			},{
				dataIndex:'event',
				text:desLang.event,
				flex:1
			},{
				dataIndex:'has_code',
				width:60,
				align:'center',
				text:desLang.hasCode,
				renderer:function(value , metaData , record){
					if(record.get('has_code')){
						return '<img src="'+app.wwwRoot+'i/system/yes.gif" data-qtip="'+appLang.YES+'" width="14" height="14">';
					}else{
						return '';
					}

				}
			},{
				xtype:'actioncolumn',
				width:40,
				text:desLang.event,
				items:[
					{
						iconCls:'trashIcon',
						tooltip:desLang.removeEvent,
						handler:function(grid, rowIndex, colIndex){
							var rec = grid.getStore().getAt(rowIndex);
							if(rec.get('is_local')){
								this.removeLocalEvent(rec);
							}
						},
						width:20,
						scope:this,
						isDisabled: function(view, rowIndex, colIndex, item, record){
							return !record.get('is_local');
						}
					}
				]
			}
		];

		this.on('celldblclick', function(table,  td,  cellIndex,  record,  tr, rowIndex, e, eOpts ){
			this.editEvent(record);
		},this);

		this.callParent();
	},
	removeEvent:function(record){
		var params = Ext.clone(this.extraParams);

		params['event'] = record.get('event');

		Ext.Ajax.request({
			url:this.controllerUrl +'removeevent',
			method: 'post',
			scope:this,
			params:params,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(!response.success){
					Ext.Msg.alert(appLang.MESSAGE,response.msg);
					return;
				}
				designer.msg(appLang.MESSAGE , desLang.msg_listenerRemoved);
				record.set('has_code',false);
				record.commit();
				this.fireEvent('eventsUpdated');
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	removeLocalEvent:function(record){
		var params = Ext.clone(this.extraParams);

		params['event'] = record.get('event');

		Ext.Ajax.request({
			url:this.controllerUrl +'removeeventdescription',
			method: 'post',
			scope:this,
			params:params,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(!response.success){
					Ext.Msg.alert(appLang.MESSAGE,response.msg);
					return;
				}
				designer.msg(appLang.MESSAGE , desLang.msg_eventDescriptionRemoved);
				this.getStore().remove(record);
				this.fireEvent('eventsUpdated');
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	/**
	 * Can edit localEvents
	 * @param {boolean} value
	 */
	setCanEditLocalEvents:function(value){
		if(value === this.canEditLocalEvents ){
			// no changes
			return;
		}
		this.canEditLocalEvents = value;
		if(value){
			this.addButton.show();
		}else{
			this.addButton.hide();
		}

		this.updateLayout();
	},
	/**
	 * Edit event action
	 * @param {Ext.data.Record} record
	 */
	editEvent:function(record){
		Ext.create('designer.eventsEditorWindow',{
			controllerUrl:this.controllerUrl,
			objectName:this.objectName,
			eventName:record.get('event'),
			paramsString:record.get('params'),
			extraParams:this.extraParams,
			modal:true,
			listeners:{
				'codeSaved':{
					fn:function(){
						record.set('has_code',true);
						record.commit();
						this.fireEvent('eventsUpdated');
					},
					scope:this
				},
				'eventUpdated':{
					fn:function(){
						this.getStore().load();
						this.fireEvent('eventsUpdated');
					},
					scope:this
				}
			}
		}).show();
	},
	/**
	 * Show create event dialog
	 */
	addLocalEvent:function(){

		Ext.MessageBox.prompt(appLang.MESSAGE , desLang.enterEventName,function(btn , eventName){
			if(btn !=='ok'){
				return;
			}
			var params = Ext.clone(this.extraParams);
			params['event'] = eventName;

			var store = this.getStore();

			Ext.Ajax.request({
				url:this.controllerUrl + 'addlocalevent',
				method: 'post',
				scope:this,
				params:params,
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(!response.success){
						Ext.Msg.alert(appLang.MESSAGE,response.msg);
						return;
					}
					store.load({
						scope:this,
						callback:function(){
							var index = store.findExact('event' , eventName);
							if(index !==-1){
								this.editEvent(store.getAt(index));
							}
						}
					});
					this.fireEvent('eventsUpdated');
				},
				failure:function() {
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});
		},this);
	},
	/**
	 * Get search filter text
	 * @return string
	 */
	getSearchText:function(){
		return this.searchField.getValue();
	},
	/**
	 * Set search filter
	 * @param {string} text
	 */
	setSearchText:function(text){
        if(this.searchField){
            return this.searchField.getValue();
        }else{
            return '';
        }
	},
	destroy:function(){
	    this.addButton.destroy();
        this.store.destroy();
        this.searchField.destroy();
        this.callParent(arguments);
    }
});