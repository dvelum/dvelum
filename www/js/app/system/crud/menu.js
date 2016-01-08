Ext.onReady(function(){

	app.crud.menu.pagesStore.load({
		url:app.root + 'pagelist'
	});

	var dataPanel = Ext.create('app.crud.menu.Panel',{
		title:appLang.MODULE_MENU,
		controllerUrl:app.root,
		canEdit:canEdit,
		canDelete:canDelete
	});

	app.content.add(dataPanel);
});
