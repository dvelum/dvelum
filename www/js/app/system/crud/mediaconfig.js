Ext.onReady(function(){
	Ext.QuickTips.init();
	var dataPanel = Ext.create('app.crud.mediaconfig.Main',{
		title:appLang.MODULE_MEDIACONFIG,
		canEdit:canEdit,
		canDelete:canDelete,
		controllerUrl:app.root
	});
	app.content.add(dataPanel);
});