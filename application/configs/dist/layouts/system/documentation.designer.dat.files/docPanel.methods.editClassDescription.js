var me = this;

var w = Ext.create('appDocClasses.htmlEditor',{
  dataValue: this.classInfo.description,
  modal:true
});

w.on('saveData', function(text){
  
    Ext.Ajax.request({
      url:this.controllerUrl + 'setdescription',
      params:{
        hid:me.classInfo.hid,
        text:text,
        object_id:me.classInfo.object_id,
        object_class:'sysdocs_class'
      },
      method: 'post',
      success: function(response, request) {
          response =  Ext.JSON.decode(response.responseText);
          if(response.success){
              w.close();
              me.classInfo.description = text;
              me.showInfo();
          }else{
              Ext.Msg.alert(appLang.MESSAGE,response.msg);  
          }
     },
     failure:function() {
            Ext.Msg.alert(appLang.MESSAGE,appLang.MSG_LOST_CONNECTION);   
     }
});

},this);


w.show();