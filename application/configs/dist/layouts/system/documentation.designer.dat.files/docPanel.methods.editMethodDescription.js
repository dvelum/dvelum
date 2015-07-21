var me = this;

var w = Ext.create('appDocClasses.textEditor',{
  dataValue: me.classInfo.methods[index].description,
  modal:true
});

w.on('dataSaved', function(text){
  
    Ext.Ajax.request({
      url:this.controllerUrl + 'setdescription',
      params:{
        hid:me.classInfo.methods[index].hid,
        text:text,
        object_id:me.classInfo.methods[index].object_id,
        object_class:'sysdocs_class_method'
      },
      method: 'post',
      success: function(response, request) {
          response =  Ext.JSON.decode(response.responseText);
          if(response.success){
              w.close();
              me.classInfo.methods[index].description = text;
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