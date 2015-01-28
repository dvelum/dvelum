Ext.ns('app');

Ext.define('app.frontend.application',{
	extend: 'Ext.app.Application',
	name:'BackOffice',
	launch: function() {
		app.application = this;
		this.addEvents('projectLoaded');
	}
});
Ext.application('app.frontend.application');
