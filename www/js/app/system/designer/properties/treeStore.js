/**
 * Properties panel for Store object
 */
Ext.define('designer.properties.TreeStore',{
	extend:'designer.properties.Store',
	
	initComponent:function()
	{
		var returnDots = function(v){return '...';};

		this.sourceConfig = Ext.apply({
			'root':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:this.rootHandler,
							scope:this
						}
					}
				}),
				renderer:returnDots
			}
		},this.sourceConfig);
			
		this.callParent();
	},
	
	rootHandler:function(){
		var source = this.dataGrid.getSource();
		var defaultData = {text:'',expanded:false,id:''};
		
		var data = {};
		
		if(source.root.length){
			data = Ext.JSON.decode(source.root);
		}
	
		var data = Ext.apply(defaultData , data);

		var win = Ext.create('designer.store.rootWindow',{
			objectName:this.objectName,
			controllerUrl:this.controllerUrl,
			initialData:data
		});
		win.on('dataSaved',function(obj){this.dataGrid.setProperty('root', Ext.JSON.encode(obj));},this);
		win.show();
	}
});