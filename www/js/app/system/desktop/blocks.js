var permissions = app.loader.getPermissions('Blocks');
if(permissions){
    var blocksModuleUrl  = app.createUrl([app.admin,'blocks','']);
	app.__modules['Blocks'] = Ext.create('app.cls.ModuleWindow',{
        title:appLang.BLOCKS,
		items:[
			Ext.create('app.crud.blocks.Main',{
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
