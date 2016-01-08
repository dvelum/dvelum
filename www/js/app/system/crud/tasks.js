Ext.onReady(function(){
	Ext.QuickTips.init();
	var module = Ext.create('app.crud.tasks.Main',{
		title:appLang.MODULE_BGTASKS,
		canEdit:canEdit
	});
	app.content.add(module);
	module.reloadInfo();
	setInterval(function(){module.reloadInfo();},3000);
});