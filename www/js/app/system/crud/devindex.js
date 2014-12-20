
Ext.onReady(function(){ 
	var pagesPanel = Ext.create('Ext.Panel',{
			title:appLang.HOME,
			bodyPadding:5,
			layout:'fit',
			loader: {
		        url: app.createUrl([app.admin,'index','devinfo']),
		        autoLoad:true
		    }
	});
	app.content.add(pagesPanel);
});