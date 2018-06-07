var me = this;
var data = app.collectStoreData(this.getStore() , true);
var curDictionary = me.getStore().proxy.extraParams['dictionary']; 

Ext.Ajax.request({
    url:app.createUrl([app.admin , 'localization' , 'updaterecords']),
    method: 'post',
    params:{
      'dictionary':curDictionary,
      'data': Ext.JSON.encode(data)
    },
    scope:this,
    success: function(response, request) {
        response =  Ext.JSON.decode(response.responseText);
        if(!response.success){	 				
            me.getEl().unmask();
            Ext.Msg.alert(appLang.MESSAGE , response.msg);
        } else{
            me.getStore().commitChanges();
            me.hasChanges(false);
        }
    },
    failure:app.ajaxFailure
});