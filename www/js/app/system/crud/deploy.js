Ext.onReady(function(){
	
	var dataPanel = Ext.create('app.crud.orm.deploy.Application',{
		title:appLang.MODULE_DEPLOY+' :: ' + appLang.HOME,
		controllerUrl:app.root,
		canEdit:canEdit,
		canDelete:canDelete
	});
	
	app.content.add(dataPanel);	
});