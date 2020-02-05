var permissions = app.loader.getPermissions('Tasks');
if(permissions){
    var tasksModule = Ext.create('app.crud.tasks.Main',{
        canEdit: permissions.edit,
        canDelete: permissions.delete,
        controllerUrl:app.createUrl([app.admin,'tasks',''])
    });

	app.__modules['Tasks'] = Ext.create('app.cls.ModuleWindow',{
        title:appLang.MODULE_BGTASKS,
		items:[
            tasksModule
		]
	});
    tasksModule.reloadInfo();
    setInterval(function(){tasksModule.reloadInfo();},3000);
}
