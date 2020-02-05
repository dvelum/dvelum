Ext.onReady(function(){
	var dataPanel = Ext.create('app.crud.cache.Main',{
		title:appLang.CACHE + ' :: ' + appLang.HOME,
		canDelete:canDelete,
		controllerUrl:app.root
	});
	app.content.add(dataPanel);
});