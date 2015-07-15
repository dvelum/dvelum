var me = this;
Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE + ' ' + appLang.FILE + ' "' +record.get('name') + '" ?' , function(btn){
    if(btn != 'yes'){
        return false;
    }
    Ext.Ajax.request({
       url:'[%wroot%][%admp%][%-%]filestorage[%-%]delete[%-%]',
       method: 'post',
       waitMsg: appLang.SAVING,
       params: {
           'id': record.get('id')
       },
       success: function (response, request) {
          response = Ext.JSON.decode(response.responseText);
          if (response.success) {
              me.getStore().remove(record);
          } else {
              Ext.MessageBox.alert(appLang.MESSAGE, response.msg);
          }
        }
      });
},this);

