var permissions = app.loader.getPermissions('Acl');
if(permissions){
    app.__modules['Acl'] = Ext.create('app.cls.ModuleWindow',{
        items:[
            Ext.create('app.crud.acl.Main',{
                title:appLang.ACL + ' :: ' + appLang.HOME,
                canEdit: permissions.edit,
                canDelete: permissions.delete,
                controllerUrl:app.createUrl([app.admin,'acl',''])
            })
        ]
    });
}