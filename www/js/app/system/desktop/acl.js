var permissions = app.loader.getPermissions('Acl');
if(permissions){
    app.__modules['Acl'] = Ext.create('app.cls.ModuleWindow',{
        title:appLang.ACL,
        items:[
            Ext.create('app.crud.acl.Main',{
                canEdit: permissions.edit,
                canDelete: permissions.delete,
                controllerUrl:app.createUrl([app.admin,'acl',''])
            })
        ]
    });
}