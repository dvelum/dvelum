/**
 * Base class for editing objects without version control
 *
 * @event dataSaved
 *
 * @event dataLoaded
 * @param {object} result
 */
Ext.define('app.editWindow',{
	extend:'Ext.Window',
	/**
	 * @property {Ext.form.Panel}
	 */
	editForm:null,
	/**
	 * @property {Ext.Button}
	 */
	saveBtn:null,
	/**
	 * @property integer
	 */
	dataItemId:null,
    /**
	 * @property string
     */
	dataItemShard:null,
	/**
	 * @property {Ext.Toolbar.TextItem}
	 */
	tbarText:false,
	/**
	 * @property {app.historyPanel}
	 */
	historyPanel:null,
	/**
	 * @property string controllerUrl
	 */
	controllerUrl:'',
	/**
	 * @property string objectName
	 */
	objectName:'',
	/**
	 * @property boolean contentTabs
	 */
	linkedComponents:null,
	/**
	 * @property boolean canEdit
	 */
	canEdit:false,
	/**
	 * @property boolean canDelete
	 */
	canDelete:false,

	fieldDefaults:null,

	itemsConfig:null,

	eastPanel:null,
	/**
	 * @property boolean hideEastPanel
	 */
	hideEastPanel:false,
	/**
	 * @property boolean eastPanelCollapsed
	 */
	eastPanelCollapsed:false,
	/**
	 * @property boolean useTabs
	 */
	useTabs:true,
	/**
	 * Tab panel link
	 * @property {Ext.tab.Panel}
	 */
	contentTabs:false,
	/**
	 * Content Panel link
	 * @property {Ext.Panel}
	 */
	contentPanel:false,
	/**
	 * Show top toolbar
	 * @property boolean
	 */
	showToolbar:true,
	/**
	 * Object primary key
	 */
	primaryKey:'id',
    /**
	 * Object shard key
     */
	shardKey:'shard',
	/**
	 * Extra params for requests
	 * @property {Object}
	 */
	extraParams:null,

	editAction:'edit',
	loadAction:'loaddata',
	deleteAction:'delete',
	maximizable:true,

	constructor: function(config){
		config = Ext.apply({
			modal: true,
			layout:'border',
			width: app.checkWidth(config.width || 300),
			height:app.checkHeight(config.height || 300),
			closeAction: 'destroy',
			linkedComponents:[],
			resizable:true,
			fieldDefaults:{
				labelAlign: 'right',
				labelWidth: 150,
				anchor: '100%'
			},
			items:[],
			fbar:[],
			buttonsAlign:'right',
			extraParams:{}
		}, config || {});
		this.callParent(arguments);

	},
	/**
	 * Submit form
	 */
	saveData: function()
	{
		var form = this.editForm.getForm();

		var params = Ext.apply({'d_object':this.objectName} , this.extraParams);

		if(!Ext.isEmpty(this.linkedComponents)){
			Ext.each(this.linkedComponents , function(cmp){
				Ext.apply(params , cmp.collectData());
			});
		}
		var me = this;
		form.waitMsgTarget = me.getEl();
		form.submit({
			clientValidation: true,
			method:'post',
			url:this.controllerUrl + this.editAction,
			params:params,
			waitMsg:appLang.SAVING,
			success: function(form, action)
			{
				if(!action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
					return;
				}

				var dataId = action.result.data.id;
                var dataShard = null;

                if (!Ext.isEmpty(action.result.data.shard)) {
                    dataShard = action.result.data.shard
				}

				if(me.canDelete && dataId != 0){
					me.deleteBtn.show();
				}

				me.dataItemId = dataId;
                me.dataItemShard = dataShard;
				me.editForm.getForm().findField(me.primaryKey).setValue(dataId);
                me.editForm.getForm().findField(me.shardKey).setValue(dataShard);

				if(!me.hideEastPanel){
					me.historyPanel.setRecordId(dataId);
					me.historyPanel.storeLoad();
				}

				if(me.canDelete){
					me.deleteBtn.show();
				}

				me.fireEvent('dataSaved');
			},
			failure: app.formFailure
		});
	},
	/**
	 * Load form data
	 * @param {integer} itemId - record id
	 */
	loadData: function(itemId, shard)
	{
		var form = this.editForm.getForm();
		var me = this;
		form.waitMsgTarget = me.getEl();
		form.load({
			waitMsg:appLang.LOADING,
			url:this.controllerUrl + this.loadAction,
			method:'post',
			params: Ext.apply({
				'id':itemId,
				'd_object':this.objectName,
				'shard' : shard,
			},this.extraParams),
			success: function(form, action)
			{
				if(action.result.success)
				{
					if(!Ext.isEmpty(me.linkedComponents)){
						Ext.each(me.linkedComponents , function(cmp){
							cmp.setData(action.result.data[cmp.fieldName]);
						});
					}
					//  History list
					if(!me.hideEastPanel){
						me.historyPanel.setRecordId(itemId);
						me.historyPanel.storeLoad();
					}
					me.fireEvent('dataLoaded' ,action.result);
				}
				else
				{
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg).toFront();
					me.close();
				}
			},
			failure: app.formFailure
		});
	},
	/**
	 * Delete record
	 */
	deleteItem : function(){
		var handle = this;
		Ext.Ajax.request({
			url: this.controllerUrl + this.deleteAction,
			waitMsg:appLang.PROCESSING,
			method: 'post',
			params: Ext.apply({
				'id':this.editForm.getForm().findField(this.primaryKey).getValue(),
				'd_object':this.objectName,
				'shard':this.editForm.getForm().findField(this.shardKey).getValue(),
			},this.extraParams),
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					handle.fireEvent('dataSaved');
					handle.close();
				}else{
					Ext.MessageBox.alert(appLang.MESSAGE,response.msg);
				}
			}
		});
	},
	getContentFields:function(){

		if(this.itemsConfig){
			Ext.Object.each(this.itemsConfig, function(item){
				if(item.name && item.name === this.primaryKey){
					this.getForm().getForm().findField(this.primaryKey).disable();
				}
			},this);
		}
		if(!this.useTabs){
			this.contentPanel = Ext.create('Ext.Panel',{
				layout:'form',
				scrollable:true,
				border:false,
				frame:false,
				bodyCls:'formBody',
				items:this.itemsConfig,
				bodyPadding:'3px',
				defaults:{
					border:false,
					frame:true,
					layout:'anchor',
					bodyPadding:'3px',
					fieldDefaults: {
						labelAlign: 'right',
						anchor: '100%'
					},
					defaults:{
						labelWidth: 160
					}
				}
			});
			return this.contentPanel;
		}

		this.contentTabs = Ext.create('Ext.tab.Panel',{
			plain:true,
			deferredRender:false,
			activeItem: 0,
			enableTabScroll:true,
			border:false,
			frame:false,
            cls:'formBody',
			defaults:{
				border:false,
				frame:true,
				layout:'anchor',
				bodyPadding:'3px',
				fieldDefaults: {
					labelAlign: 'right',
					anchor: '100%'
				},
				defaults:{
					labelWidth: 160
				}
			},
			items:this.itemsConfig
		});

		return this.contentTabs;
	},
	/**
	 * Get form Panel
	 * @return Ext.form.Panel
	 */
	getForm:function(){
		return this.editForm;
	},
	initComponent : function()
	{
		this.itemsConfig = this.items;
		this.items = [];

		this.tbarText = Ext.create('Ext.toolbar.TextItem',{});

		this.deleteBtn = Ext.create('Ext.Button',{
			text:appLang.DELETE_ITEM,
			iconCls:'deleteIcon',
			hidden:true,
			listeners:{
				click:{
					fn: function(){this.deleteItem();},
					scope:this
				}
			}
		});

		var bar = ['&nbsp;' ,this.tbarText,'->',this.deleteBtn];

		if(this.canDelete && this.dataItemId != 0){
			this.deleteBtn.show();
		}

		var revDataId = this.dataItemId;
		if(this.hideEastPanel){
			revDataId = false;
		}

		this.historyPanel = Ext.create('app.historyPanel',{
			dataId:revDataId,
			objectName:this.objectName,
			region:'center',
			split:true,
			rowsOnPage:30
		});

		this.historyPanel.dataStore.proxy.setExtraParam('object' , this.objectName);

		this.eastPanel = Ext.create('Ext.Panel',{
			region:'east',
			title:appLang.HISTORY,
			collapsed:this.eastPanelCollapsed,
			width:300,
			split:true,
			layout:'border',
			items:[this.historyPanel],
			frame:false,
			collapsible:true,
			border:false
		});

		//compatibility
		this.rightPanel = this.eastPanel;

		if(this.showToolbar){
			this.tbar = bar;
		}

		this.editForm = Ext.create('Ext.form.Panel', {
			region:'center',
			layout:'fit',
			frame:false,
			split:true,
			items:[{
                fieldLabel:"id",
                name:this.primaryKey,
                xtype:"hidden"
            },{
                name:this.shardKey,
                xtype:'hidden'
            }],
			fieldDefaults: this.fieldDefaults
		});

        var formItems = this.getContentFields();

        if(formItems){
            this.editForm.add(formItems);
		}
		this.saveBtn = new Ext.Button({
			text:appLang.SAVE,
			listeners:{
				click:{
					fn:function(){ this.saveData();},
					scope:this
				}
			}
		});

		if(this.hideEastPanel){
			this.items = [this.editForm];
		}else{
			this.items = [this.editForm, this.eastPanel];
		}

		this.buttons = [];

		if(this.canEdit){
			this.buttons.push(this.saveBtn);
		}

		this.buttons.push({
			text:appLang.CLOSE,
			listeners:{
				'click' : {
					fn:function(){
						this.close();
					},
					scope:this
				}
			}
		});

		this.callParent();
		var list = this.query('relatedgridpanel');

		if(list.length){
			Ext.each(list,function(item){
				this.registerLink(item);
			},this);
		}

		if(this.dataItemId){
			this.on('show',function(){
				this.loadData(this.dataItemId, this.dataItemShard);
			},this);
		}

        this.on('show', function(){
            app.checkSize(this);
            Ext.WindowMgr.register(this);
            Ext.WindowMgr.bringToFront(this);
        }, this);

	},
	/**
	 * Add linked component
	 * @param cmp
	 */
	registerLink:function(cmp){
		this.linkedComponents.push(cmp);
	},
	/**
	 * Set request param
	 * @param {string} name
	 * @param {string} value
	 * @return void
	 */
	setExtraParam:function(name , value){
		this.extraParams[name] = value;
	},
    destroy:function(){
        var toDestroy  = [
            this.rightPanel,
            this.eastPanel,
            this.historyPanel,
            this.contentTabs,
            this.contentPanel
        ];

        Ext.Array.each(toDestroy, function (item) {
            if(item && item.destroy){
                item.destroy();
            }
        });

        Ext.Array.each(this.linkedComponents,function(item){
            if(item.destroy){
                item.destroy();
            }
        });

        toDestroy = null;
        this.callParent(arguments);
    }
});