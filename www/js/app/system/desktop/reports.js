var permissions = app.loader.getPermissions('Reports');
if(permissions){
    var reportPanel =  Ext.create('app.crud.reports.Main',{
        canEdit: permissions.edit,
        canDelete: permissions.delete,
        controllerUrl:app.createUrl([app.admin,'reports',''])
    });
    app.__modules['Reports'] = Ext.create('app.cls.ModuleWindow',{
        title:appLang.REPORTS,
        items:[
            reportPanel
        ]
    });
    reportPanel.checkIsLoaded();
}