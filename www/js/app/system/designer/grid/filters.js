Ext.define('designer.grid.filters.Model',{
    extend:'Ext.data.Model',
    fields: [
        {name:'id' ,  type:'string'},
        {name:'dataIndex',type:'string'},
        {name:'type',type:'string'},
        {name:'active' , type:'boolean'}
    ],
    isProperty:'id'
});
/**
 *
 * @event dataSaved
 */
Ext.define('designer.grid.filters.Window',{
	extend:'Ext.Window',
	layout:'fit',
	width:700,
	height:500,
	objectName:null,
	title:desLang.filtersFeature,

	initComponent:function(){

		this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit:1});

		this.dataStore = Ext.create('Ext.data.Store',{
            model:'designer.grid.filters.Model',
			proxy: {
				type: 'ajax',
				url:app.createUrl([designer.controllerUrl ,'gridfilters','filterlist']),
				reader: {
					type: 'json',
					rootProperty:'data'
				},
				extraParams:{
					object:this.objectName
				}
			},
			autoLoad:true,
			sorters: [{
				property : 'dataIndex',
				direction: 'ASC'
			}]
		});

		this.properties = Ext.create('designer.properties.Panel',{
			controllerUrl: app.createUrl([designer.controllerUrl ,'gridfilters','']),
			objectName:this.objectName,
			width:380,
			autoLoad:false,
			listeners:{
				dataSaved:{
					fn:function(){
						this.fireEvent('dataSaved');
					},
					scope:this
				}
			},
			title:desLang.properties,
			mainConfigTitle:false,
			layout:'fit',
			showEvents:false,
			useTabs:false
		});


		this.itemsPanel = Ext.create('Ext.grid.Panel',{
			tbar:[{
				tooltip:desLang.addFilter,
				iconCls:'plusIcon',
				scope:this,
				handler:this.addFilter
			}],
			store:this.dataStore,
			region:'center',
			split:true,
			title:desLang.filters,
			columnLines:true,
			columns:[
				{
					text:desLang.dataIndex,
					dataIndex:'dataIndex',
					flex:1,
					editable:true,
					editor:{
						xtype: 'combobox',
						typeAhead: true,
						triggerAction: 'all',
						selectOnTab: true,
						forceSelection:true,
						valueField:'name',
						displayField:'name',
						queryMode:'local',
						store:Ext.create('Ext.data.Store', {
							model:'designer.model.fieldsModel',
							proxy: {
								type: 'ajax',
								url:app.createUrl([designer.controllerUrl ,'store','']) +  'listStoreFields',
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
							remoteSort: true,
							autoLoad: true,
							sorters: [{
								property : 'title',
								direction: 'DESC'
							}]
						}),
						listeners:{
							scope:this,
							select:function(combo, record){
								this.itemPropertiesPanel.dataGrid.setProperty('dataIndex',record.get('name'));
							}
						}
					}
				},{
					text:desLang.type,
					dataIndex:'type',
					editable:true,
					editor:{
						xtype: 'combobox',
						typeAhead: true,
						triggerAction: 'all',
						selectOnTab: true,
						forceSelection:true,
						store: [
							['string' , 'String'],
							['date','Date'],
							['datetime','Date time'],
							['boolean','Boolean'],
							['list','List'],
							['numeric','Numeric']
						],
						listeners:{
							scope:this,
							select:function(combo, record, eOpts){
								var filterId = this.itemPropertiesPanel.getExtraParam('id');
								Ext.Ajax.request({
									url:app.createUrl([designer.controllerUrl ,'gridfilters','changefiltertype']),
									method: 'post',
									params:{
										'object':this.objectName,
										'type':record.get('field1'),
										'filterid':filterId
									},
									scope:this,
									success: function(response, request) {
										response =  Ext.JSON.decode(response.responseText);
										if(response.success){
											this.itemPropertiesPanel.setExtraParams({'id':filterId});
											this.itemPropertiesPanel.loadProperties();
											this.itemPropertiesPanel.resetSerchField();
                                            record.commit();
										}else{
											Ext.Msg.alert(appLang.MESSAGE, response.msg);
											return false;
										}
									},
									failure: app.formFailure
								});
							}
						}
					}
				},{
					text:desLang.active,
					dataIndex:'active',
					renderer:app.checkboxRenderer,
					width:60,
					align:'center',
					editable:true,
					editor:{
						xtype:'checkbox',
						listeners:{
							scope:this,
							change:function(cb, value){
								this.itemPropertiesPanel.dataGrid.setProperty('active',value);
							}
						}
					}
				},{
					xtype:'actioncolumn',
					width:20,
					items:[
						{
							iconCls:'deleteIcon',
							scope:this,
							tooltip:desLang.remove,
							handler:this.removeFilter
						}
					]
				}
			],
			plugins:[this.cellEditing],
			region:'center',
			split:true
		});

		this.itemPropertiesPanel =  Ext.create('designer.properties.GridFilter',{
			controllerUrl: app.createUrl([designer.controllerUrl ,'gridfilter','']),
			eventsControllerUrl:app.createUrl([designer.controllerUrl ,'gridfilterevents','']),
			objectName:this.objectName,
			width:300,
			autoLoad:false,
			listeners:{
				dataSaved:{
					fn:function(){},
					scope:this
				}
			},
			layout:'fit',
			region:'east',
			showEvents:true,
			split:true,
			region:'east',
			split:true
		});


		this.callParent();
		this.add({
			xtype:'tabpanel',
			deferredRender:false,
			items:[
				this.properties,
				{
					xtype:'panel',
					layout:'border',
					title:desLang.filters,
					items:[
						this.itemsPanel,
						this.itemPropertiesPanel
					]
				}
			]
		});

		this.on('show',function(){app.checkSize(this);});

		this.itemsPanel.getSelectionModel().on('selectionchange',function(sm , data , opts){
			if(!sm.hasSelection()){
				this.itemPropertiesPanel.resetProperties();
				return;
			}
			this.itemPropertiesPanel.setExtraParams({'id':sm.getSelection()[0].get('id')});
			this.itemPropertiesPanel.loadProperties();
		},this);
	},
	/**
	 * Add column action
	 */
	addFilter:function(){

		Ext.MessageBox.prompt(appLang.MESSAGE , desLang.enterFilterId,function(btn , text){

			if(btn !='ok'){
				return;
			}

			Ext.Ajax.request({
				url:app.createUrl([designer.controllerUrl ,'gridfilters','addfilter']),
				method: 'post',
				params:{
					'id':text,
					'object':this.objectName
				},
				scope:this,
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						this.dataStore.load();
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
				},
				failure:function() {
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});
		},this);
	},
	/**
	 * Remove column action
	 * @param {Ext.grid.Panel }grid
	 * @param integer row
	 * @param integer col
	 */
	removeFilter:function(grid , row , col)
	{
		var record = grid.getStore().getAt(row);
		Ext.MessageBox.confirm(appLang.MESSAGE , desLang.remove + '?',function(btn){
			if(btn !='yes'){
				return;
			}

			Ext.Ajax.request({
				url:app.createUrl([designer.controllerUrl ,'gridfilters','removefilter']),
				method: 'post',
				params:{
					'id':record.get('id'),
					'object':this.objectName
				},
				scope:this,
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						grid.getStore().load();
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
				},
				failure:function() {
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});
		},this);
	}
});