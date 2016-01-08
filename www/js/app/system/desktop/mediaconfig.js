var permissions = app.loader.getPermissions('Mediaconfig');
if(permissions){
	app.__modules['Mediaconfig'] = Ext.create('app.cls.ModuleWindow',{
		items:[
			Ext.create('app.crud.mediaconfig.Main',{
                title:appLang.MODULE_MEDIACONFIG,
				canEdit: permissions.edit,
				canDelete: permissions.delete,
				controllerUrl:app.createUrl([app.admin,'mediaconfig',''])
			})
		]
	});
}