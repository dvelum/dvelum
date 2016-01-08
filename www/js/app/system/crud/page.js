Ext.onReady(function(){
	Ext.Ajax.request({
		url: app.root+ "themeslist",
		method: 'post',
		success: function(response, request) {
			response =  Ext.JSON.decode(response.responseText);
			if(response.success){
				app.crud.page.themes = response.data;
				var pagesPanel = Ext.create('app.crud.page.Panel',{
					title:appLang.MODULE_SITE_STRUCTURE,
					canEdit: canEdit,
					canPublish: canPublish,
					canDelete: canDelete,
                    controllerUrl:app.root
				});
				app.content.add(pagesPanel);
			}else{
				Ext.Msg.alert(appLang.MESSAGE, appLang.CANT_LOAD_THEMES);
			}
		},
		failure:function() {
			Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
		}
	});
});