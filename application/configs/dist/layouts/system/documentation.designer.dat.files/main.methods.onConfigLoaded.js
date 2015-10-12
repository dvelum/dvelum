this.childObjects.langSelector.getStore().loadData(this.sysConfiguration.languages);
this.childObjects.langSelector.setValue(this.sysConfiguration.language);

this.childObjects.versionSelector.getStore().loadData(this.sysConfiguration.versions);
this.childObjects.versionSelector.setValue(this.sysConfiguration.version);

this.controllerUrl = app.createUrl([this.controllerUrl , this.sysConfiguration.language , this.sysConfiguration.version,'']);

var searchStore = this.childObjects.search.getStore();
searchStore.proxy.url = this.controllerUrl + 'search';

this.initApiTree();