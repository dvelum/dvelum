var grid = this;
  
Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_DELETE +' ' + group.get('title')+'?', function (btn) {
      if (btn != 'yes') {
          return
      }

      Ext.Ajax.request({
          url: '[%wroot%][%admp%][%-%]user[%-%]removegroup',
          method: 'post',
          params: {
              'id': group.get('id')
          },
          success: function (response, request) {
              response = Ext.JSON.decode(response.responseText);
              if (response.success) {
                 
                  grid.suspendEvents();
                  grid.getStore().load({
                    callback:function(){
                      grid.resumeEvents(); 
                      grid.fireEvent('groupDeleted');
                    }
                  },grid);
              } else {
                  Ext.Msg.alert(response.msg);
              }
          },
          failure: app.ajaxFailure
      });
});