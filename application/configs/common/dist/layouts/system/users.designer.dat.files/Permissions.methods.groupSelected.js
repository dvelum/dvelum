this.getSelectionModel().deselectAll();
this.getStore().removeAll();

if(!record){
  this.setTitle('');
  this.childObjects.Permissions_saveBtn.disable();
}


this.setTitle('"' + record.get('title') + '" ' + appLang.GROUP_PERMISSIONS);
var store = this.getStore();

store.proxy.setExtraParam('group_id', record.get('id'));
store.proxy.setExtraParam('user_id', 0);

store.load();

this.childObjects.Permissions_saveBtn.enable();
