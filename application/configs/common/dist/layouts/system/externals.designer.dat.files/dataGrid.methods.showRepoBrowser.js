var win = Ext.create('appExternalsComponents.repoBrowserWindow',{
  repoStore:appExternalsApplication.repoStore,
  repoItemsStore:appExternalsApplication.repoItemsStore
});

win.on('downloaded',function(){
	this.getStore().load();
},this);

win.show();