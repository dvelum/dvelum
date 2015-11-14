var changesStore = this.childObjects.changesGrid.getStore();
changesStore.proxy.setExtraParam('filter[id]' , record.get('id'));
changesStore.loadPage(1);