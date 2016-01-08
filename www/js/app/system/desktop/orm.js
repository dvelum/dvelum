var permissions = app.loader.getPermissions('Orm');

if(permissions){
    app.crud.orm.canEdit = permissions.edit;
    app.crud.orm.canDelete = permissions.delete;
    app.__modules['Orm'] = Ext.create('app.cls.ModuleWindow',{
        items:[
            Ext.create('app.crud.orm.Main',{
                title:appLang.MODULE_ORM,
                controllerUrl:app.createUrl([app.admin,'orm',''])
            })
        ]
    });
}