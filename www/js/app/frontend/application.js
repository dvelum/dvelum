Ext.ns('app');

/**
 * @event projectLoaded
 */
Ext.define('app.frontend.application',{
	extend: 'Ext.app.Application',
	name:'BackOffice',
	launch: function() {
		app.application = this;
	}
});
Ext.application('app.frontend.application');
