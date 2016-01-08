var permissions = app.loader.getPermissions('Menu');
if(permissions){
	app.__modules['Menu'] = Ext.create('app.cls.ModuleWindow',{
		items:[
			Ext.create('app.crud.menu.Panel',{
            title:appLang.MODULE_MENU,
				canEdit: permissions.edit,
				canDelete: permissions.delete,
				controllerUrl:app.createUrl([app.admin,'menu',''])
			})
		]
	});

    app.crud.menu.pagesStore.load({
        url:app.createUrl([app.admin,'menu','']) + 'pagelist'
    });
}
