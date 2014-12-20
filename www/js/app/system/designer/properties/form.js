/**
 * Properties panel for Form object
 */
Ext.define('designer.properties.Form',{
	extend:'designer.properties.Panel',
	/**
	 * @var {designer.ormSelectorWindow}
	 */
	importWindow:null,
	
	initComponent:function()
	{
		this.tbar = [{
			 iconCls:'importOrmIcon',
        	 tooltip:desLang.importOrm,
        	 scope:this,
        	 handler:this.showImportFromOrm
		},{
			 iconCls:'importDbIcon',
        	 tooltip:desLang.importDb,
        	 scope:this,
        	 handler:this.showImportFromDb
		}];
		
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
	 * Show Db import window
	 */
	showImportFromDb:function(){
		this.importWindow = Ext.create('designer.importDBWindow',{
			listeners:{
				select:{
					fn:this.importDbFields,
					scope:this
				}
			}
		});
		this.importWindow.show();
	},
	/**
	 * Import selected fields
	 * @param string object
	 * @param {array} fields
	 */
	importFields:function(object , fields){
		this.importWindow.close();
		Ext.Ajax.request({
		 	url:app.createUrl([designer.controllerUrl ,'form','importfields']),
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
		 			this.fireEvent('objectsUpdated');
		 		}else{
		 			Ext.Msg.alert(appLang.MESSAGE, response.msg);  
		 		}
		    },
		    failure:function() {
		       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
		    }
		 });
	},
	/**
	 * Import selected Database fields
	 * @param {array} fields
	 * @param string connection
	 * @param string table
	 */
	importDbFields:function(fields , connection , table , contype){
		this.importWindow.close();
		Ext.Ajax.request({
		 	url:app.createUrl([designer.controllerUrl ,'form','importdbfields']),
		 	method: 'post',
		 	scope:this,
		 	params:{
		 		'object':this.objectName,
		 		'connection':connection,
		 		'table':table,
		 		'type':contype,
		 		'importfields[]':fields
		 	},
		    success: function(response, request) {
		 		response =  Ext.JSON.decode(response.responseText);
		 		if(response.success){
		 			this.fireEvent('objectsUpdated');
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