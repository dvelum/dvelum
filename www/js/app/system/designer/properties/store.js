/**
 * Properties panel for Store object
 */
Ext.define('designer.properties.Store',{
	extend:'designer.properties.Panel',
	
	initComponent:function()
	{	
		this.tbar = [{
        	 iconCls:'fieldIcon',
        	 tooltip:desLang.fields,
        	 scope:this,
        	 handler:this.fieldsHandler
		},{
        	 iconCls:'proxyIcon',
        	 tooltip:desLang.proxy,
        	 scope:this,
        	 handler:this.proxyHandler
		}];
				
		this.mainConfigTitle = desLang.mainConfig;

		var returnDots = function(v){return '...';};
		
		this.sourceConfig = Ext.apply({			
			'proxy':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:this.proxyHandler,
							scope:this
						}
					}
				}),
				renderer:returnDots
			},
			'sorters':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:this.sortersHandler,
							scope:this
						}
					}
				}),
				renderer:returnDots
			},
			'filters':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:this.filtersHandler,
							scope:this
						}
					}
				}),
				renderer:returnDots
			},
			'groupField':{
				 editor:Ext.create('Ext.form.field.ComboBox',{		
						typeAhead: true,
					    triggerAction: 'all',
					    selectOnTab: true,
					    labelWidth:80,
					    forceSelection:false,
					    queryMode:'remote',
					    queryCaching:false,
					    displayField:'name',
					    valueField:'name',
						store:Ext.create('Ext.data.Store',{
								proxy: {
								        type: 'ajax',
								    	url:app.createUrl([designer.controllerUrl ,'store','listfields']),
								    	reader: {
								            type: 'json',
								            idProperty: 'name',
											rootProperty: 'data'
								        },
								        extraParams:{
								        	object:this.objectName
								        },
								        autoLoad:false
								},
								fields: [
								         {name:'name' ,  type:'string'},
								         {name:'type' ,  type:'string'}
								],
								autoLoad:true
							})
					})
			},
			'fields':{
				editor: Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:this.fieldsHandler,
							scope:this
						}
					}
				}),
				renderer:returnDots
			},
			'model':{
				editor:Ext.create('Ext.form.field.ComboBox',{		
					typeAhead: true,
				    triggerAction: 'all',
				    selectOnTab: true,
				    labelWidth:80,
				    forceSelection:false,
				    queryMode:'local',
				    displayField:'title',
				    valueField:'title',
					store:app.designer.getModelsStore()
				})
			}
			
			
			
		},this.sourceConfig);
		
		this.callParent();	
	},
	proxyHandler:function(){
		var win = Ext.create('designer.store.proxyWindow',{
			objectName:this.objectName,
			maximizable:true,
			modal:true,
			controllerUrl:app.createUrl([designer.controllerUrl ,'storesubproperty',''])
		});
		win.on('dataChanged',function(){
			this.fireEvent('dataSaved');
		},this);
		win.show();
	},
	sortersHandler:function(){	
		var source = this.dataGrid.getSource();
		var data = [];
		var fieldsSet = [];
		if(source.sorters.length){
			data = Ext.JSON.decode(source.sorters);
		}						
		var win = Ext.create('designer.store.sortersWindow',{
			objectName:this.objectName,
			controllerUrl:this.controllerUrl,
			initialData:data
		});
		win.on('dataChanged',this.dataGrid.setProperty,this.dataGrid);
		win.show();
	},
	filtersHandler:function(){
		var source = this.dataGrid.getSource();
		var data = [];
		var fieldsSet = [];
		if(source.filters.length){
			data = Ext.JSON.decode(source.filters);
		}
		if(source.fields.length){
			fieldsSet = Ext.JSON.decode(source.fields);
		}
		var win = Ext.create('designer.store.filtersWindow',{
			objectName:this.objectName,
			controllerUrl:this.controllerUrl,
			initialData:data
		});
		win.on('dataChanged',this.dataGrid.setProperty,this.dataGrid);
		win.show();
	},
	fieldsHandler:function(){
		var source = this.dataGrid.getSource();
		var data = [];
		if(source.fields.length){
			data = Ext.JSON.decode(source.fields);
		}
		var win = Ext.create('designer.store.fieldsWindow',{
			objectName:this.objectName,
			controllerUrl:this.controllerUrl,
			initialData:data
		});
		win.on('dataChanged',this.dataGrid.setProperty,this.dataGrid);
		win.show();
	}
});