Ext.ns('app');
app.viewFrame = null;

/**
 * @event launch'
 * @event projectLoaded
 */
Ext.application({
	name: 'ViewFrame',
	mainUrl:'',

	initComponent:function(){
		this.callParent();

	},

	launch:function()
	{
		app.application = this;
		app.viewFrame = Ext.create('Ext.container.Viewport', {
			layout : 'fit',
			items : [],
			renderTo : Ext.getBody()
		});

		app.externalCommandReceiver = Ext.create(
			'Ext.form.field.Text', {
				inputId : 'externalCommandReciver',
				value : '',
				hidden : true,
				listeners : {
					change : {
						fn : this.onComandRecieved,
						scope : this
					}
				},
				renderTo : Ext.getBody()
			});
		this.callParent();
		this.fireEvent('launch');
	},

	onComandRecieved:function(field , value){

		if(!value.length){
			return;
		}

		message = Ext.JSON.decode(value);
		if(message.command && message.params){
			this.runCommand(message.command , message.params);
		}
		app.externalCommandReceiver.setValue('');
	},

	runCommand:function(command , params)
	{
		switch(command){
			case 'showWindow':
				var win = Ext.create(applicationClassesNamespace + '.'+params.name,{
					objectName:params.name
				});
				win.show();
				win.on('resize',this.onWindowResize,this);
				break;
		}
	},
	/**
	 * Window size changed
	 * @param {Ext.Window} window
	 * @param integer width
	 * @param integer height
	 * @param {object} opts
	 */
	onWindowResize:function(window , width , height , opts)
	{
		var url = '/'+app.createUrl([this.mainUrl ,'window','changesize']);
		var me = this;
		Ext.Ajax.request({
			url:url,
			method: 'post',
			params:{
				'object':window.objectName,
				'width':width,
				'height':height
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					me.sendCommand({command:'windowSizeChanged',params:[]});
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	/**
	 * On grid column resize
	 * @param string objectName
	 * @param {Ext.grid.header.Container} ct
	 * @param {Ext.grid.column.Column} column
	 * @param integer width
	 * @param {Object} eOpts
	 */
	onGridColumnResize:function(objectName, ct, column, width, eOpts)
	{
		if(typeof column.flex !== 'undefined')
			return;

		var url = '/'+app.createUrl([this.mainUrl ,'gridcolumn','changesize']);
		var me = this;
		Ext.Ajax.request({
			url:url,
			method: 'post',
			params:{
				'object':objectName,
				'column':column.itemId,
				'width':width
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					me.sendCommand({command:'columnSizeChanged',params:{object:objectName}});
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	/**
	 * On Grid column move
	 * @param string objectName
	 * @param {Ext.grid.header.Container} ct
	 * @param {Ext.grid.column.Column} column
	 * @param integer fromIdx
	 * @param integer toIdx
	 * @param {object} eOpts
	 */
	onGridColumnMove:function(object , ct, column, fromIdx, toIdx, eOpts)
	{
		var url = '/'+app.createUrl([this.mainUrl ,'gridcolumn','move']);
		var me = this;
		Ext.Ajax.request({
			url:url,
			method: 'post',
			params:{
				'object':object,
				'column':column.itemId,
				'from':fromIdx,
				'to':toIdx
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					me.sendCommand({command:'columnMoved',params:{object:object}});
				}else{
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	/**
	 * Send command for layout frame
	 * @param {object} command -  {comand:'somestring','params':'mixed'}
	 */
	sendCommand:function(command){
		var frame = window.parent;
		var reciever = frame.document.getElementById('externalCommandReciver');
		reciever.value = Ext.JSON.encode(command);
		var o = document.createEvent('HTMLEvents');
		o.initEvent( 'change', false, false );
		reciever.dispatchEvent(o);
	}
});