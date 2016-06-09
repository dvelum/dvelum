var repo = grid.getStore().proxy.extraParams['repo'];
var code = grid.getStore().getAt(rowIndex).get('code');

this.downloadItem(repo, code, '');