Ext.onReady(function(){

    var appMain = Ext.create('app.crud.reports.Main',{
        title:appLang.REPORTS + ' :: ' + appLang.HOME,
        controllerUrl:app.root,
        canEdit:canEdit,
        canDelete:canDelete
    });

    app.content.add(appMain);
    appMain.checkIsLoaded();
});