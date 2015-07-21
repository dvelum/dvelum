var me = this;

var w = Ext.create('appDocClasses.textEditor',{
  dataValue: me.classInfo.properties[index].description,
  modal:true
});

w.on('dataSaved', function(text){
  
    Ext.Ajax.request({
      url:this.controllerUrl + 'setdescription',
      params:{
        hid:me.classInfo.properties[index].hid,
        text:text,
        object_id:me.classInfo.properties[index].object_id,
        object_class:'sysdocs_class_property'
      },
      method: 'post',
      success: function(response, request) {
          response =  Ext.JSON.decode(response.responseText);
          if(response.success){
              w.close();
              me.classInfo.properties[index].description = text;
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