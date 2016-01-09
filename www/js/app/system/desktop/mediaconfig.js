var permissions = app.loader.getPermissions('Mediaconfig');
if(permissions){
	app.__modules['Mediaconfig'] = Ext.create('app.cls.ModuleWindow',{
		title:appLang.MODULE_MEDIACONFIG,
		items:[
			Ext.create('app.crud.mediaconfig.Main',{
				canEdit: permissions.edit,
				canDelete: permissions.delete,
				controllerUrl:app.createUrl([app.admin,'mediaconfig',''])
			})
		]
	});
}