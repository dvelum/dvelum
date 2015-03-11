/**
 * Grid column editor properties
 *
 * @event editorRemoved
 */
Ext.define('designer.properties.GridEditor',{
	
	extend:'designer.properties.Field',	
	
	columnName:null,
	
	initComponent:function(){
		
		this.tbar.push({
			text:desLang.removeEditor,
			scope:this,
			handler:this.removeEditor,
			iconCls:'deleteIcon'
		});	
		
		this.setExtraParams({column:this.columnName});
		this.callParent(arguments);
	},
	/**
	 * Remove column editor
	 */
	removeEditor:function()
	{
		Ext.Ajax.request({
		 	url:this.controllerUrl + 'remove',
		 	method: 'post',
		 	scope:this,
		 	params:this.extraParams,
		    success: function(response, request) {
		 		response =  Ext.JSON.decode(response.responseText);
		 		if(response.success){
		 			this.dataGrid.setSource({});
		 			this.fireEvent('editorRemoved' , response);
		 		}else{
		 			Ext.Msg.alert(appLang.MESSAGE, response.msg);   
		 		}
		    },
		    failure:function() {
		       	Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);   
		    }
		 });
		this.fireEvent('editorRemoved');
		
	}
});