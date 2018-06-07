var store  = this.getStore();
store.proxy.extraParams['dictionary'] = name;
store.load();
this.childObjects.addRecord.enable();