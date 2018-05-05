var store = this.getStore();
store.proxy.extraParams['id'] = userId;
store.loadPage(1);
this.childObjects.saveIndividualPermissionsBtn.disable();