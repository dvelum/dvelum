var userId = false;
if(record != false){
  userId = record.get('id');
}

var win = Ext.create('appUsersClasses.editWindow',{
  recordId: userId,
  controllerUrl:this.controllerUrl,
  listeners:{
    'dataSaved':{
      fn:function(){
        this.getStore().load();
      },
      scope:this
    }
  }
}).show();