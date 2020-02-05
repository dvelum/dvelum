var permissions = app.loader.getPermissions('Orm');

if(permissions){

    app.crud.orm.canEdit = permissions.edit;
    app.crud.orm.canDelete = permissions.delete;

    app.crud.orm.dbConfigs = dbConfigsList;
    app.crud.orm.foreignKeys = useForeignKeys;

    app.__modules['Orm'] = Ext.create('app.cls.ModuleWindow',{
        title:appLang.MODULE_ORM,
        items:[
            Ext.create('app.crud.orm.Main',{
                controllerUrl:app.createUrl([app.admin,'orm',''])
            })
        ]
    });
}