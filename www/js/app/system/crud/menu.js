Ext.onReady(function(){
	var dataPanel = Ext.create('app.crud.menu.Panel',{
		title:appLang.MODULE_MENU,
		controllerUrl:app.root,
		canEdit:app.permissions.canEdit("Menu"),
		canDelete:app.permissions.canDelete("Menu")
	});
	app.content.add(dataPanel);
});
