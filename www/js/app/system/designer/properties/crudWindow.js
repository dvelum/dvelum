/**
 * Properties panel for Window object
 */
Ext.define('designer.properties.CrudWindow',{
	extend:'designer.properties.Window',
	
	initComponent:function()
	{
		var me = this;
		
		if(!this.tbar){
			this.tbar = [];
		}
		
		this.tbar.push({
			 iconCls:'importOrmIcon',
        	 tooltip:desLang.importOrm,
        	 scope:this,
        	 handler:this.showImportFromOrm
		});

		this.callParent();	
	},
	/**
	 * Show Orm import window
	 */
	showImportFromOrm:function(){
		this.importWindow = Ext.create('designer.ormSelectorWindow',{
			listeners:{
				select:{
					fn:this.importFields,
					scope:this
				}
			}
		});
		this.importWindow.show();
	},	
	/**
	 * Import selected fields
	 * @param {string} object
	 * @param {array} fields
	 */
	importFields:function(object , fields){
		this.importWindow.close();
		Ext.Ajax.request({
		 	url:app.createUrl([designer.controllerUrl ,'crudwindow','importfields']),
		 	method: 'post',
		 	scope:this,
		 	params:{
		 		'object':this.objectName,
		 		'importobject':object,
		 		'importfields[]':fields
		 	},
		    success: function(response, request) {
		 		response =  Ext.JSON.decode(response.responseText);
		 		if(response.success){
		 			//this.fireEvent('objectsUpdated');
		 			this.application.onChange();
		 		}else{
		 			Ext.Msg.alert(appLang.MESSAGE, response.msg);  
		 		}
		    },
		    failure:function() {
		       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
		    }
		 });
	}
});