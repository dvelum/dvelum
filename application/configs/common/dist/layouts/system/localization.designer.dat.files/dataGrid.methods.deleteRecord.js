var me = this;
var curDictionary = me.getStore().proxy.extraParams['dictionary']; 

Ext.Msg.confirm(appLang.CONFIRMATION, appLang.MSG_CONFIRM_DELETE + ' ' + record.get('key'), function(btn){		 
   if(btn == 'yes'){
       Ext.Ajax.request({
			url:app.createUrl([app.admin , 'localization' , 'removerecord']),
			method: 'post',
			params:{
			  'dictionary':curDictionary,
              'id':record.get('id')
			},
			scope:this,
	 		success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(!response.success){	 				
	 				me.getEl().unmask();
	 				Ext.Msg.alert(appLang.MESSAGE , response.msg);
	 			} else{
	 				me.getStore().remove(record);
	 			}
	 		},
	 		failure:app.ajaxFailure
		});
   }
}, this);