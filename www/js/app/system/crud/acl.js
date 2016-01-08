Ext.onReady(function(){
	app.content.add(Ext.create('app.crud.acl.Main',{
		title:appLang.ACL + ' :: ' + appLang.HOME,
		canEdit:canEdit,
		canDelete:canDelete,
		controllerUrl:app.root
	}));
});