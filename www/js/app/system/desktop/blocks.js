var permissions = app.loader.getPermissions('Blocks');
if(permissions){

    var blocksModuleUrl  = app.createUrl([app.admin,'blocks','']);

	app.__modules['Blocks'] = Ext.create('app.cls.ModuleWindow',{
		items:[
			Ext.create('app.crud.blocks.Main',{
                title:appLang.BLOCKS + ' :: ' + appLang.HOME,
				canEdit: permissions.edit,
				canDelete: permissions.delete,
                canPublish: permissions.publish,
				controllerUrl:blocksModuleUrl
			})
		]
	});

    app.crud.blocks.ClassesStore.load({
        url:blocksModuleUrl + 'classlist'
    });

    app.crud.blocks.MenuStore.load({
        url:blocksModuleUrl + 'menulist'
    });
}
