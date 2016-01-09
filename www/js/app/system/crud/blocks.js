Ext.onReady(function(){

    app.crud.blocks.ClassesStore.load({
        url:app.root + 'classlist'
    });

    app.crud.blocks.MenuStore.load({
        url:app.root + 'menulist'
    });

    var dataPanel = Ext.create('app.crud.blocks.Main',{
        title:appLang.BLOCKS + ' :: ' + appLang.HOME,
        canEdit:canEdit,
        canDelete:canDelete,
        canPublish:canPublish,
        controllerUrl:app.root
    });
    app.content.add(dataPanel);
});