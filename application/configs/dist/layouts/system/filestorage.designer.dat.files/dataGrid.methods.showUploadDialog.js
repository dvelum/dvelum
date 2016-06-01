/*
// simple fileuploader
Ext.create('appFilestorageClasses.fileUploadWindow',{
  listeners:{
    dataSaved:{
      fn:function(){
          this.getStore().loadPage(1);
      },
      scope:this
    }
  }
}).show();
*/

var win = Ext.create('app.filestorage.UploadWindow',{
  uploaderConfig:app.filestorageConfig,
  maxFileSize:app.maxFileSize,
  uploadUrl:"[%wroot%][%admp%][%-%]filestorage[%-%]upload",
});
win.show();

win.on('filesUploaded',function(data){
  this.getStore().loadPage(1);
  win.close();
},this);
