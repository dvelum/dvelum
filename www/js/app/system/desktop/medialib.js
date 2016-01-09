var permissions = app.loader.getPermissions('Medialib');
if(permissions){
	app.__modules['Medialib'] = Ext.create('app.cls.ModuleWindow',{
		title:appLang.MODULE_MEDIALIB,
		items:[
			Ext.create('app.medialibPanel',{
				showType:'main',
				canEdit: permissions.edit,
				canDelete: permissions.delete
			})
		]
	});
}


