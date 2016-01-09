var permissions = app.loader.getPermissions('Modules');
if(permissions){
    app.__modules['Modules'] = Ext.create('app.cls.ModuleWindow',{
        title:appLang.MODULES,
        items:[
            Ext.create('app.crud.modules.Main',{
                canEdit: permissions.edit,
                canDelete: permissions.delete,
                controllerUrl:app.createUrl([app.admin,'modules',''])
            })
        ]
    });
}
