var repo = grid.getStore().proxy.extraParams['repo'];
var record = grid.getStore().getAt(rowIndex)
this.downloadItem(repo,record.get('code'), record.get('number'));