
Ext.onReady(function(){
	var dataPanel = Ext.create('app.crud.modules.Main',{
		title:appLang.MODULES + ' :: ' + appLang.HOME,
		canEdit:canEdit,
		canDelete:canDelete,
		controllerUrl:app.root
	});
	app.content.add(dataPanel);
});
