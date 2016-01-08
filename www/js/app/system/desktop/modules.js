var permissions = app.loader.getPermissions('Modules');
if(permissions){
    app.__modules['Modules'] = Ext.create('app.cls.ModuleWindow',{
        items:[
            Ext.create('app.crud.modules.Main',{
                title:appLang.MODULES + ' :: ' + appLang.HOME,
                canEdit: permissions.edit,
                canDelete: permissions.delete,
                controllerUrl:app.createUrl([app.admin,'modules',''])
            })
        ]
    });
}
