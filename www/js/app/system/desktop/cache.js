var permissions = app.loader.getPermissions('Cache');
if(permissions){
    app.__modules['Cache'] = Ext.create('app.cls.ModuleWindow',{
        title:appLang.CACHE,
        items:[
            Ext.create('app.crud.cache.Main',{
                canEdit: permissions.edit,
                canDelete: permissions.delete,
                controllerUrl:app.createUrl([app.admin,'cache',''])
            })
        ]
    });
}