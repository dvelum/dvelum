Ext.onReady(function(){
	Ext.QuickTips.init();
	var module = Ext.create('app.crud.tasks.Main',{
		title:appLang.MODULE_BGTASKS,
		controllerUrl:app.root,
		canEdit:canEdit,
		canDelete:canDelete
	});
	app.content.add(module);
	module.reloadInfo();
	setInterval(function(){module.reloadInfo();},3000);
});