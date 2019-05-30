Ext.onReady(function(){
	designer.controllerUrl = app.root + 'sub';
	app.designer = Ext.create('designer.application',{});
	app.content.add(app.designer);
});