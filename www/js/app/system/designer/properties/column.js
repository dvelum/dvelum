/**
 * Properties panel for Grid object
 */
Ext.define('designer.properties.GridColumn',{
	extend:'designer.properties.Panel',

	autoLoadData:false,
	
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
		
		var summaryEditor = Ext.create('Ext.form.field.ComboBox',{		
			typeAhead: true,
		    triggerAction: 'all',
		    selectOnTab: true,
		    labelWidth:80,
		    forceSelection:true,
		    queryMode:'local',
		    displayField:'title',
		    valueField:'id',
			store: this.renderersStore
		});
		
		var rendererEditor = Ext.create('Ext.form.field.ComboBox',{		
			typeAhead: true,
		    triggerAction: 'all',
		    selectOnTab: true,
		    labelWidth:80,
		    forceSelection:true,
		    queryMode:'local',
		    displayField:'title',
		    valueField:'id',
			store: this.renderersStore
		});
		
		this.sourceConfig = Ext.apply({		
			'summaryType':{
				editor: Ext.create('Ext.form.field.ComboBox',{
					typeAhead: true,
				    triggerAction: 'all',
				    selectOnTab: true,
				    labelWidth:80,
				    forceSelection:true,
				    queryMode:'local',
				    store: [
				        ['count' , 'count'],
				        ['sum', 'sum'],
				        ['min','min'],
				        ['max','max'],
				        ['average','average']
				    ]
				}),
				renderer:function(v){
					if(Ext.isEmpty(v)){
						return '...';
					}else{
						return v;
					}
				}
			},
			'summaryRenderer':{
				editor:summaryEditor,
				renderer:app.comboBoxRenderer(summaryEditor)
			},
			'renderer':{
				editor:rendererEditor,
				renderer:app.comboBoxRenderer(rendererEditor)
			},
			'items':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:me.showItemsWindow,
							scope:me
						}
					}
				}),
				renderer:function(v){return '...';}
			}
		} , this.sourceConfig );
		
		this.callParent();		
	},
	showItemsWindow:function()
	{			
		Ext.create('designer.grid.column.ActionsWindow',{
			title:desLang.items,
	    	objectName : this.objectName,
	    	columnId: this.extraParams.id,
	    	controllerUrl:this.controllerUrl
	    }).show();		
	}
});    
