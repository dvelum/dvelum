var permissions = app.loader.getPermissions('Medialib');
if(permissions){
	app.__modules['Medialib'] = Ext.create('app.cls.ModuleWindow',{
		items:[
			Ext.create('app.medialibPanel',{
				title:appLang.MODULE_MEDIALIB,
				showType:'main',
				canEdit: permissions.edit,
				canDelete: permissions.delete
			})
		]
	});
}


