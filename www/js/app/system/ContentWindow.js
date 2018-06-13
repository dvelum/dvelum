/**
 * Base class for editing objects under version control
 *
 * @event dataSaved
 *
 * @event dataLoaded
 * @param {object} result
 */
Ext.define('app.contentWindow',{
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
	 * @property {app.historyPanel}
	 */
	historyPanel:null,
	/**
	 * @property {app.revisionPanel}
	 */
	revisionPanel:null,
	/**
	 * @property integer
	 */
	dataItemId:null,
	/**
	 * @property {Ext.Toolbar.TextItem}
	 */
	tbarText:false,
	/**
	 * @property boolaen
	 */
	isFixed:false,
	/**
	 * @property integer Current data revision
	 */
	curVersion:0,
	/**
	 * @property boolean Data published flag
	 */
	isPublished:0,
	/**
	 * @property string controllerUrl
	 */
	controllerUrl:'',
	/**
	 * @property string ORM object name
	 */
	objectName:'',
	/**
	 * @property {Ext.tab.Panel} Data tabs
	 */
	contentTabs:false,
	/**
	 * @property {array} List of linked components
	 */
	linkedComponents:false,
	/**
	 * @property boolean Access rights for edit
	 */
	canEdit:false,
	/**
	 * @property boolean Access rights for delete
	 */
	canDelete:false,
	/**
	 * @property boolean Access rights for publishing
	 */
	canPublish:false,
	/**
	 * @property boolean
	 */
	modal:true,
	/**
	 * @property string Page preview url
	 */
	previewUrl:'',
	/**
	 * Has preview button
	 * @param config
	 */
	hasPreview:true,
	/**
	 * Hide revision & history panels
	 * @param boolean
	 */
	hideEastPanel:false,
	/**
	 * Start revision panel collapsed
	 * @param boolean
	 */
	eastPanelCollapsed:false,
	/**
	 * Revisions panel container
	 * @param Ext.Panel
	 */
	eastPanel:null,
	/**
	 * Use tabs layout
	 * @param boolean
	 */
	useTabs:true,
	/**
	 * Content panel for simple layout
	 * @param Ext.Panel
	 */
	contentPanel:false,
	/**
	 * Object primary key
	 */
	primaryKey:'id',
	/**
	 * Show top toolbar
	 * @param boolean
	 */
	showToolbar:true,
	/**
	 * Extra params for requests
	 * @property {Object}
	 */
	extraParams:null,
    /**
     * Auto publish changes
     */
	autoPublish:false,

	constructor: function(config) {

		config = Ext.apply({
			modal: true,
			layout:'border',
			width: app.checkWidth(config.width),
			height:app.checkHeight(config.height),
			closeAction: 'destroy',
			resizable:true,
			linkedComponents:[],
			items:[],
			fbar:[],
			fieldDefaults:{
				labelAlign: 'right',
				labelWidth: 150,
				anchor: '100%'
			},
			buttonsAlign:'right',
			maximizable:true,
			extraParams:{}
		}, config || {});

		this.callParent(arguments);

	},

	getContentFields:function(){

		if(!this.useTabs){
			this.contentPanel = Ext.create('Ext.Panel',{
				layout:'form',
				border:false,
				frame:false,
				bodyCls:'formBody',
				scrollable:true,
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

	initComponent : function()
	{
		this.itemsConfig = this.items;
		this.items = [];

		this.tbarText = Ext.create('Ext.toolbar.TextItem',{});

		this.publishBtn = Ext.create('Ext.Button',{
			text:appLang.PUBLISH,
			hidden:true,
			listeners:{
				click:{
					fn: function(){this.publish();},
					scope:this
				}
			}
		});

		this.unpublishBtn = Ext.create('Ext.Button',{
			text:appLang.UNPUBLISH,
			hidden:true,
			listeners:{
				click:{
					fn: function(){this.unpublish();},
					scope:this
				}
			}
		});

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

		this.previewBtn = Ext.create('Ext.Button',{
			text:appLang.PREVIEW,
			listeners:{
				click:{
					fn: function(){
						window.open(this.previewUrl+'?vers=' + this.curVersion);
					},
					scope:this
				}
			}
		});

		var bar = [this.tbarText,'-'];

		if(this.canPublish){
			bar.push(this.publishBtn);
			bar.push('-');
			bar.push(this.unpublishBtn);
		}

		if(this.hasPreview){
			bar.push('-',this.previewBtn);
		}

		bar.push('->');
		bar.push(this.deleteBtn);
		if(this.canDelete && this.dataItemId != 0){
			this.deleteBtn.show();
		}

		if(this.showToolbar){
			this.tbar = bar;
		}

		var revDataId = this.dataItemId;
		if(this.hideEastPanel){
			revDataId = false;
		}

		this.revisionPanel = Ext.create('app.revisionPanel',{
			dataId:revDataId,
			objectName:this.objectName,
			region:'north',
			split:true,
			height:300,
			title:appLang.VERSIONS
		});

		this.revisionPanel.on('dataSelected',function(record){
			this.loadData(this.editForm.getForm().findField(this.primaryKey).getValue() , record.get('version'));
		},this);

		this.historyPanel = Ext.create('app.historyPanel',{
			dataId:revDataId,
			objectName:this.objectName,
			region:'center',
			split:true,
			title:appLang.HISTORY
		});


		this.eastPanel = Ext.create('Ext.Panel',{
			region:'east',
			collapsible:true,
			collapsed:this.eastPanelCollapsed,
			hidden:false,
			width:300,
			split:true,
			layout:'border',
			items:[
				this.revisionPanel ,
				this.historyPanel
			],
			frame:false,
			border:false
		});
		/*
		 * backward compatibility
		 */
		this.rightPanel = this.eastPanel;

		this.contentTabs = Ext.create('Ext.tab.Panel',{
			plain:true,
			deferredRender :false,
			activeItem: 0,
			enableTabScroll:true,
			border:false,
			frame:false,
			style:{
				backgroundColor:'#E5E4E2'
			},
			defaults:{
				border:false,
				frame:true,
				layout:'anchor',
				bodyPadding:'3px',
				bodyCls:'formBody',
				anchor: '100%',
				fieldDefaults: {
					labelAlign: 'right',
					labelWidth: 160,
					anchor: '100%'
				}
			},
			items:this.itemsConfig
		});

		this.editForm = Ext.create('Ext.form.Panel', {
			region:'center',
			layout:'fit',
			frame:false,
			split:true,
			items:this.getContentFields(),
			fieldDefaults: this.fieldDefaults
		});

		this.editForm.add({
			fieldLabel:"id",
			name:this.primaryKey,
			xtype:"hidden"
		});

		this.saveBtn = Ext.create('Ext.Button',{
			text:appLang.SAVE_NEW_VERSION,
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

		this.callParent(arguments);

		var list = this.query('relatedgridpanel');

		if(list.length){
			Ext.each(list,function(item){
				this.registerLink(item);
			},this);
		}

		if(this.dataItemId){
			this.on('show',function(){
				this.loadData(this.dataItemId);
			},this);
		}

		this.on('show', function(){
            app.checkSize(this);
            Ext.WindowMgr.register(this);
            Ext.WindowMgr.bringToFront(this);
        }, this);


	},
	/**
	 * Submit form (save record)
	 */
	saveData: function()
	{
		var handle = this;
		var form = this.editForm.getForm();
		var params = Ext.apply({'d_object':this.objectName} , this.extraParams);

		if(!Ext.isEmpty(this.linkedComponents)){
			Ext.each(this.linkedComponents , function(cmp){
				Ext.apply(params , cmp.collectData());
			});
		}
		form.submit({
			clientValidation: true,
			waitMsg:appLang.SAVING,
			method:'post',
			url:this.controllerUrl + 'edit',
			params:params,
			success: function(form, action)
			{

				if(!action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
					return;
				}

				var dataId = action.result.data.id;

				if(handle.canDelete && dataId != 0){
					handle.deleteBtn.show();
				}

				handle.dataItemId = dataId;
				handle.editForm.getForm().findField(handle.primaryKey).setValue(dataId);

				if(!this.hideEastPanel){
					handle.revisionPanel.setRecordId(dataId);
					handle.revisionPanel.storeLoad();
					handle.historyPanel.setRecordId(dataId);
					handle.historyPanel.storeLoad();
				}
				handle.curVersion = action.result.data.version;
				handle.tbarText.setText(appLang.VERSION +' : ' + action.result.data.version );
				handle.previewUrl = action.result.data.staging_url;

				if(handle.canDelete){
					handle.deleteBtn.show();
				}
				handle.previewBtn.show();
				handle.publishBtn.show();

                if(handle.canPublish && handle.autoPublish){
                    handle.publish();
                }else{
                    handle.fireEvent('dataSaved');
                }
			},
			failure: app.formFailure
		});
	},
	/**
	 * Configure publish buttons
	 */
	setupPublishBtns:function(){

		if(this.canPublish){
			this.publishBtn.show();
			if(this.isPublished){
				this.unpublishBtn.show();
			}else{
				this.unpublishBtn.hide();
			}

		}else{
			this.publishBtn.hide();
			this.unpublishBtn.hide();
		}

		if(this.isFixed){
			this.unpublishBtn.hide();
		}

	},
	/**
	 * Load form data
	 * @param {integer} itemId - record id
	 * @param {integer} revision - record version
	 */
	loadData: function(itemId , revision)
	{
		var handle = this;
		var form = this.editForm.getForm();
        form.waitMsgTarget = this.getEl();
		form.load({
			waitMsg:appLang.LOADING,
			url:this.controllerUrl + 'loaddata',
			method:'post',
			params: Ext.apply({
				'id':itemId,
				'version':revision,
				'd_object':this.objectName
			},this.extraParams),
			success: function(form, action)
			{
				if(action.result.success)
				{
					var itemVers = action.result.data.version;
					handle.isFixed = action.result.data.is_fixed;

					//Window cfg
					handle.tbarText.setText(appLang.VERSION +' : ' + itemVers );
					handle.curVersion = itemVers;
					handle.isPublished = action.result.data.published;
					handle.setupPublishBtns();

					if(!Ext.isEmpty(handle.linkedComponents)){
						Ext.each(handle.linkedComponents , function(cmp){
							cmp.setData(action.result.data[cmp.fieldName]);
						});
					}
					handle.previewUrl = action.result.data.staging_url;
					handle.updateLayout();
					handle.fireEvent('dataLoaded' ,action.result);
				}
				else
				{
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
					handle.close();
				}
			},
			failure: app.formFailure
		});
	},
	/**
	 * Unpublish current version
	 */
	unpublish:function(){
		var handle = this;
		Ext.Ajax.request({
			url: this.controllerUrl + 'unpublish',
			method: 'post',
			waitMsg:appLang.SAVING,
			params: Ext.apply({
				'id':this.editForm.getForm().findField(this.primaryKey).getValue(),
				'vers':this.curVersion,
				'd_object':this.objectName
			},this.extraParams),
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					handle.isPublished = 0;
					handle.setupPublishBtns();
					if(!this.hideEastPanel){
						handle.historyPanel.storeLoad();
					}
					handle.fireEvent('dataSaved');
				}else{
					Ext.MessageBox.alert(appLang.MESSAGE,response.msg);
				}
			}
		});
	},
	/**
	 * Publish current version
	 */
	publish:function(){
		var handle = this;
		Ext.Ajax.request({
			url: this.controllerUrl +  'publish',
			waitMsg:appLang.SAVING,
			method: 'post',
			params: Ext.apply({
				'id':this.editForm.getForm().findField(this.primaryKey).getValue(),
				'vers':this.curVersion,
				'd_object':this.objectName
			},this.extraParams),
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					handle.isPublished = 1;
					handle.setupPublishBtns();
					if(!this.hideEastPanel){
						handle.historyPanel.storeLoad();
					}
					handle.fireEvent('dataSaved');
					handle.fireEvent('dataPublished');
				}else{
					Ext.MessageBox.alert(appLang.MESSAGE,response.msg);
				}
			}
		});

	},
	/**
	 * Delete record
	 */
	deleteItem : function(){
		var handle = this;
		Ext.Ajax.request({
			url: this.controllerUrl + 'delete',
			waitMsg:appLang.PROCESSING,
			method: 'post',
			params: Ext.apply({
				'id':this.editForm.getForm().findField(this.primaryKey).getValue(),
				'd_object':this.objectName
			}, this.extraParams),
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
	/**
	 * Add linked component
	 * @param cmp
	 */
	registerLink:function(cmp){
		this.linkedComponents.push(cmp);
	},
	/**
	 * Get form Panel
	 * @return Ext.form.Panel
	 */
	getForm:function(){
		return this.editForm;
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
        this.rightPanel = null;
	    var toDestroy  = [
	        this.rightPanel,
            this.eastPanel,
            this.historyPanel,
            this.revisionPanel,
            this.contentPanel,
            this.contentTabs
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