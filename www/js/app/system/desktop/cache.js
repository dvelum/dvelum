var permissions = app.loader.getPermissions('Cache');
if(permissions){
    app.__modules['Cache'] = Ext.create('app.cls.ModuleWindow',{
        items:[
            Ext.create('app.crud.cache.Main',{
                title:appLang.CACHE + ' :: ' + appLang.HOME,
                canEdit: permissions.edit,
                canDelete: permissions.delete,
                controllerUrl:app.createUrl([app.admin,'cache',''])
            })
        ]
    });
}