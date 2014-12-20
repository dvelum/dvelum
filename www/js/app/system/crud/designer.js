Ext.onReady(function(){
	app.menu.hide();
	designer.controllerUrl = app.root + 'sub';
	app.designer = Ext.create('designer.application',{});
	app.content.add(app.designer);
});