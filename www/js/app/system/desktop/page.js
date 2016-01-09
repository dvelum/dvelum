Ext.ns('app.crud.page');

app.crud.page.themes =[];

var permissions = app.loader.getPermissions('Page');

if(permissions){

    app.__modules['Page'] = Ext.create('app.cls.ModuleWindow',{
        title:appLang.MODULE_SITE_STRUCTURE,
        items:[
            Ext.create('app.crud.page.Panel',{
                canEdit: permissions.edit,
                canPublish: permissions.publish,
                canDelete: permissions.delete,
                controllerUrl:app.createUrl([app.admin,'page',''])
            })
        ]
    });

    Ext.Ajax.request({
        url: app.createUrl([app.admin,'page','themeslist']),
        method: 'post',
        success: function(response, request) {
            response =  Ext.JSON.decode(response.responseText);
            if(response.success){
                app.crud.page.themes = response.data;
            }else{
                Ext.Msg.alert(appLang.MESSAGE, appLang.CANT_LOAD_THEMES);
            }
        },
        failure:function() {
            Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
        }
    });
}
