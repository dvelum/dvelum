Ext.onReady(function() {

    app.crud.orm.canEdit = canEdit;
    app.crud.orm.canDelete = canDelete;

    var dataPanel = Ext.create('app.crud.orm.Main', {
        title: appLang.MODULE_ORM,
        controllerUrl: app.root
    });

    app.content.add(dataPanel);
});