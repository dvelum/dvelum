Ext.define('designer.paramsWindow',{
	extend:'designer.defaultsWindow',
	saveData:function(){
		var s ='';
		var items = {};
		this.dataStore.commitChanges();
		this.dataStore.each(function(item){
			if(item.get('key').length){
				items[item.get('key')] = item.get('value');
			}
		});
		this.fireEvent('dataChanged' ,  Ext.JSON.encode(items));	
		this.close();
	}
});