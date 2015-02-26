Ext.ns('app.crud.user');

Ext.define('app.crud.user.ListModel', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'id' , type:'integer'},
		{name:'group_id', type:'integer'},
		{name:'name' , type:'string'},
		{name:'login' , type:'string'},
		{name:'email' , type:'string'},
		{name:'enabled' , type:'boolean'},
		{name:'admin' , type:'boolean'},
		{name:'group_title' , type:'string'},
		{name:'registration_date', dateFormat: "Y-m-d H:i:s"}
	]
});

Ext.define('app.crud.user.GroupModel', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'id' , type:'integer'},
		{name:'title' , type:'string'},
		{name:'system' , type:'boolean'}
	]
});

Ext.define('app.crud.user.PermissionsModel', {
	extend: 'Ext.data.Model',
	fields: [
		{name:'id'  , type:'integer'},
		{name:'user_id'  , type:'integer'},
		{name:'group_id' , type:'integer'},
		{name:'view', type:'boolean'},
		{name:'edit', type:'boolean'},
		{name:'delete', type:'boolean'},
		{name:'publish', type:'boolean'},
		{name:'module' , type: 'string'},
		{name:'rc', type:'boolean'}
	]
});

Ext.define('app.crud.user.Main',{
	extend:'Ext.tab.Panel',
	userList:null,
	groupsList:null,
	canEdit:false,
	canDelete:false,
	deferredRender:true,
	activeTab:0,

	usersPanel:null,
	groupsPanel:null,

	initComponent:function(){

		this.usersPanel = Ext.create('app.crud.user.List',{
			title:appLang.USERS,
			canEdit:this.canEdit,
			canDelete:this.canDelete
		});

		this.groupsPanel = Ext.create('app.crud.user.Groups',{
			title:appLang.GROUPS,
			canEdit:this.canEdit,
			canDelete:this.canDelete
		});

		this.items=[this.usersPanel , this.groupsPanel];
		this.callParent();
	}
});


Ext.define('app.crud.user.List',{
	extend:'Ext.Panel',
	layout:'fit',

	dataStore:null,
	dataGrid:null,
	searchField:null,
	canDelete:false,

	initComponent:function(){

		this.dataStore = Ext.create('Ext.data.Store', {
			model: 'app.crud.user.ListModel',
			proxy: {
				type: 'ajax',
				url: app.root + 'userlist',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'count',
					idProperty: 'id'
				},
				startParam:'pager[start]',
				limitParam:'pager[limit]',
				sortParam:'pager[sot]',
				directionParam:'pager[dir]',
				simpleSortMode: true
			},
			pageSize: 50,
			remoteSort: true,
			autoLoad: true,
			sorters: [{
				property : 'name',
				direction: 'DESC'
			}]
		});

		this.searchField =  Ext.create('SearchPanel',{
			store:this.dataStore,
			fieldNames:['login','name'],
			local:false
		});

		var me = this;

		this.dataGrid = Ext.create('Ext.grid.Panel' ,{
			store: this.dataStore,
			viewConfig:{
				stripeRows:true,
				enableTextSelection: true
			},
			loadMask:true,
			columnLines: true,
			autoscroll:true,
			frame: false,
			defaults:{
				sortable:true
			},
			tbar:[{
				text:appLang.ADD_USER,
				tooltip:appLang.ADD_USER,
				listeners:{
					click:{
						fn:function(){
							this.showEdit(0);
						},
						scope:this
					}
				}
			},'-', appLang.ACCOUNT_TYPE + ': ',
				{
					displayField:"title",
					valueField:'id',
					queryMode:"local",
					triggerAction:"all",
					allowBlank: true,
					value:'',
					width:150,
					emptyText:appLang.ALL,
					xtype:"combo",
					name:'admin',
					forceSelection:true,
					store:new Ext.data.Store({
						model:'app.comboStringModel',
						data:[
							{id:'' , title:appLang.ALL},
							{id:1 , title:appLang.BACKEND_USERS},
							{id:0 , title:appLang.FRONTEND_USERS}
						]
					}),
					listeners:{
						select:{
							fn:function(combo, record,  index ){
								this.dataStore.proxy.setExtraParam('filter['+combo.name+']' ,  combo.getValue());
								this.dataStore.load();
							},
							scope:this
						}
					}

				},'-',appLang.STATUS + ': ',
				{
					displayField:"title",
					valueField:'id',
					queryMode:"local",
					triggerAction:"all",
					allowBlank: true,
					value:'',
					xtype:"combo",
					emptyText:appLang.ALL,
					name:'enabled',
					forceSelection:true,
					store:new Ext.data.Store({
						model:'app.comboStringModel',
						data:[
							{id:'' ,title:appLang.ALL},
							{id:1 , title:appLang.ACTIVE},
							{id:0 , title:appLang.DISABLED}
						]
					}),
					width:100,
					listeners:{
						select:{
							fn:function(combo, record,  index ){
								this.dataStore.proxy.setExtraParam('filter['+combo.name+']', combo.getValue());
								this.dataStore.load();
							},
							scope:this
						}
					}
				},
				'->',this.searchField
			],
			clicksToEdit:1,
			columns: [
				{
					text:appLang.NAME,
					dataIndex: 'name',
					id:'name',
					width:200,
					flex:1,
					align:'left',
					sortable:true
				},{
					text:appLang.GROUP,
					dataIndex: 'group_title',
					width:200,
					align:'left'
				},{
					text:appLang.LOGIN,
					dataIndex:'login',
					align:'left',
					width:170,
					sortable:true
				},{
					text:appLang.EMAIL,
					dataIndex:'email',
					align:'left',
					width:170,
					sortable:true,
					enableKeyEvents:true
				},{
					text:appLang.ADMIN,
					dataIndex: 'admin',
					width:110,
					align:'center',
					sortable:true,
					renderer:app.checkboxRenderer
				},{
					text:appLang.ACTIVE,
					dataIndex: 'enabled',
					width:60,
					align:'center',
					id:'active',
					sortable:true,
					renderer:app.checkboxRenderer
				},{
					text:'',
					dataIndex:'id',
					itemId:'deleteColumn',
					width:40,
					align:'center',
					renderer:function(value, metaData, record, rowIndex, colIndex, store){
						if(!me.canDelete || record.get('login')=='root'){
							return '';
						}else{
							return '<img src="'+app.wwwRoot+'i/system/delete.gif" style="cursor:pointer;" title="'+appLang.DELETE_ITEM+'">';
						}
					}
				}
			],
			bbar: Ext.create('Ext.PagingToolbar', {
				store: this.dataStore,
				displayInfo: true
			})
		});

		this.dataGrid.on('cellclick', function(grid , item,  index, record, e, options){
			var cellId = grid.getHeaderCt().getHeaderAtIndex(index).itemId;
			if(cellId =='deleteColumn' && this.canDelete && record.get('login')!='root'){
				Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_REMOVE_USER +' "'+record.get('name')+'"?', function(btn){
					if(btn != 'yes'){
						return;
					}
					this.removeUser(record);
				},this);
			}
		},this);

		this.dataGrid.on('itemdblclick',function(view , record){
			this.showEdit(record.get('id'));
		} ,this);

		this.items = [this.dataGrid];

		this.callParent(arguments);
	},
	/**
	 * Remove user Action
	 * @param {Ext.dataRecord} id
	 * @return void
	 */
	removeUser:function(record){
		var handle = this;
		Ext.Ajax.request({
			url: app.root + 'removeuser',
			method: 'post',
			waitMsg:appLang.SAVING,
			params:{
				'id':record.get('id')
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					handle.dataStore.remove(record);
				}else{
					Ext.MessageBox.alert(appLang.MESSAGE,response.msg);
				}
			}
		});
	},
	showEdit:function(id){
		var win = Ext.create('app.crud.user.Window' , {
			recordId:id,
			listeners:{
				'dataSaved':{
					fn:function(){this.dataStore.load();},
					scope:this
				}
			}
		}).show();

	}
});

/**
 * validationCache
 * @property {Object}
 *
 * @event dataSaved
 */
app.crud.user.validationCacheUserName = {'id':'','val':''},

	Ext.define('app.crud.user.Window',{
		extend:'Ext.Window',
		/**
		 * Record form
		 * @property {Ext.form.FormPanel}
		 */
		dataForm:null,
		/**
		 * Additional request params
		 * @property {Object}
		 */
		extraParams:{},
		/**
		 * Record id
		 * @property integer
		 */
		recordId:0,
		/**
		 * Groups store
		 * @property {Ext.data.Store}
		 */
		goupsStore:null,

		constructor: function(config) {

			config = Ext.apply({
				modal: false,
				layout:'fit',
				width: 400,
				height: 370,
				resizable:false,
				plain:true,
				title:appLang.EDIT_USER
			},config || {});
			this.callParent(arguments);
		},
		initComponent:function(){

			this.groupsStore = Ext.create('Ext.data.Store',{
				autoLoad:true,
				model:'app.crud.user.GroupModel',
				proxy:{
					type:'ajax',
					url:app.root + 'grouplist',
					reader:{
						type:'json',
						root:'data'
					},
					simpleSortMode: true
				},
				root:'data',
				sorters: [{
					field: 'title',
					direction: 'ASC' // or 'DESC' (case sensitive for local sorting)
				}]
			});

			this.dataForm = new Ext.form.Panel({
				frame:false,
				bodyCls:'formBody',
				border:false,
				bodyBorder:false,
				bodyPadding:5,
				fieldDefaults:{
					anchor:"100%",
					labelAlign:'right',
					labelWidth:140
				},
				items:[{
					xtype:'hidden',
					name:'id',
					value:0
				},{
					xtype:'checkbox',
					name:'admin',
					fieldLabel:appLang.ADMIN_PANEL_ACCESS,
					value:0,
					inputValue:1,
					uncheckedValue:0,
					listeners: {
						change : {
							fn:this.checkAdmin,
							scope:this
						}
					}
				},{
					displayField:"title",
					queryMode:"remote",
					triggerAction:"all",
					valueField:"id",
					allowBlank: false,
					fieldLabel:appLang.GROUP,
					name:"group_id",
					xtype:"combo",
					hidden:true,
					disabled:true,
					forceSelection:true,
					store:this.groupsStore
				},{
					allowBlank: false,
					fieldLabel:appLang.NAME,
					name:"name",
					xtype:"textfield"
				},{
					allowBlank: false,
					fieldLabel:appLang.LOGIN,
					name:"login",
					xtype:"textfield",
					validateOnBlur:false,
					vtype:"alphanum",
					enableKeyEvents:true,
					listeners:{
						keyup : {
							fn: this.checkLogin,
							scope:this,
							buffer:400
						}
					}
				},{
					allowBlank: false,
					fieldLabel:appLang.EMAIL,
					name:"email",
					vtype:"email",
					xtype:"textfield",
					enableKeyEvents:true,
					listeners:{
						keyup : {
							fn: this.checkMail,
							scope:this,
							buffer:400
						}
					}

				},{
					fieldLabel:appLang.ENABLED,
					id:"confirmedField",
					name:"enabled",
					xtype:"checkbox",
					inputValue:1,
					uncheckedValue:0
				},{
					name: 'setpass',
					value: 1,
					readOnly:true,
					fieldLabel:appLang.CHANGE_PASSWORD,
					submitValue:false,
					checked:true,
					readOnly:true,
					xtype:'checkbox',
					listeners: {
						change : {
							fn:this.denyBlankPassword,
							scope:this,
							buffer:350
						}
					}
				},{
					fieldLabel:appLang.NEW_PASS,
					inputType:"password",
					name:"pass",
					xtype:"textfield",
					enableKeyEvents:true,
					allowBlank:false
				},{
					fieldLabel:appLang.PASS_CONFIRM,
					inputType:"password",
					name:"pass2",
					submitValue:false,
					xtype:"textfield",
					enableKeyEvents:true,
					vtype: 'password',
					initialPassField: 'pass',
					allowBlank:false
				}
				]
			});

			this.buttons =[
				{
					text:appLang.SAVE,
					scope:this,
					handler:this.saveAction
				},{
					text:appLang.CANCEL,
					scope:this,
					handler:this.close
				}
			];

			this.items = [this.dataForm];

			this.callParent();

			if(this.recordId){
				this.dataForm.getForm().load({
					scope:this,
					url:app.root + 'userload',
					params:{id:this.recordId},
					waitMsg:appLang.LOADING,
					success: function(form, action){
						this.setTitle(appLang.EDIT_USER + ': ' + form.findField('name').getValue());
						form.findField('setpass').setReadOnly(false);
						form.findField('setpass').setValue(0);
					},
					failure: app.formFailure
				});

				this.denyBlankPassword(null,false);
			}
		},
		/**
		 * Permit or prohibit be empty password field
		 * @param {Ext.form.field} field
		 * @param boolean bool
		 */
		denyBlankPassword:function(field, bool){
			var handle = this.dataForm.getForm();

			if(!bool){
				handle.findField('pass').disable();
				handle.findField('pass2').disable();
			} else {
				handle.findField('pass').enable();
				handle.findField('pass2').enable();
			}

			handle.findField('pass').allowBlank = !bool;
			handle.findField('pass2').allowBlank = !bool;
		},
		/**
		 * Save rule action
		 */
		saveAction:function(){
			this.dataForm.getForm().submit({
				clientValidation: true,
				waitMsg:appLang.SAVING,
				method:'post',
				url:app.root + 'usersave',
				scope:this,
				success: function(form, action) {
					this.fireEvent('dataSaved');
					this.close();
				},
				failure: app.formFailure
			});
		},
		/**
		 * Mark user as system
		 * @param {Ext.field.Checkbox} cmp - component
		 * @param boolean checked - status
		 * @returns void
		 */
		checkAdmin: function(cmp , checked){
			var groupField = this.dataForm.getForm().findField('group_id');
			if(checked){
				groupField.show();
				groupField.enable();
			}else{
				groupField.hide();
				groupField.disable();
				groupField.setValue('');
			}
		},
		/**
		 * check pass action
		 * @param {Ext.field.Checkbox} cmp - component
		 * @param boolean checked - status
		 * @returns void
		 */
		checkPass: function(cmp , checked){
			if(checked){
				this.dataForm.getForm().findField('pass').enable();
				this.dataForm.getForm().findField('pass2').enable();
			}else{
				this.dataForm.getForm().findField('pass').disable();
				this.dataForm.getForm().findField('pass2').disable();
			}
		},
		/**
		 * Validate unique Login
		 * @param {Ext.form.Field} field
		 * @param {Event} event
		 */
		checkLogin:function(field){
			var val = field.getValue();
			var e = field.up('form').getForm().findField('id').getValue();

			Ext.Ajax.request({
				url: app.root + "checklogin",
				method: 'post',
				params:{
					'id':e,
					'value':val
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						field.unsetActiveError();
						field.clearInvalid();
					}else{
						field.markInvalid(response.msg);
						field.setActiveError(response.msg);
					}
				},
				failure:app.ajaxFailure
			});
		},
		/**
		 * Validate unique email
		 * @param {Ext.form.Field} field
		 * @param {Event} event
		 */
		checkMail:function(field , event){
			var val = field.getValue();
			Ext.Ajax.request({
				url: app.root + "checkemail",
				method: 'post',
				params:{
					'id':this.dataForm.getForm().findField('id').getValue(),
					'value':val
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						field.unsetActiveError();
						field.clearInvalid();
					}else{
						field.markInvalid(response.msg);
						field.setActiveError(response.msg);
					}
				},
				failure:app.ajaxFailure
			});
		}
	});


/**
 * Permissions pannel allows to modify users and groups permissions
 *  {Ext.Panel}
 */
Ext.define('app.crud.user.Permissions',{
	extend:'Ext.Panel',
	layout:'fit',
	/**
	 * @var {Ext.grid.EditorGridPanel}
	 */
	dataGrid:null,
	/**
	 * @var {Ext.data.JsonStore}
	 */
	dataStore:null,

	initComponent:function(){

		this.dataStore = Ext.create('Ext.data.Store', {
			model: 'app.crud.user.PermissionsModel',
			proxy: {
				type: 'ajax',
				reader: {
					type: 'json',
					rootProperty: 'data',
					idProperty: 'id'
				},
				writer:{
					type:'json',
					writeAllFields:true,
					encode: true,
					listful:true,
					rootProperty:'data'
				},
				extraParams:{
					'user_id':0,
					'group_id':0
				},
				method:'post',
				url: app.root + 'permissions',
				simpleSortMode: true
			},
			sorters: [{
				property : 'module',
				direction: 'ASC'
			}]
		});

		var cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
			clicksToEdit: 1
		});


		this.saveBtn =  Ext.create('Ext.Button',{
			text:appLang.SAVE,
			iconCls:'saveIcon',
			handler:this.savePermissions,
			scope:this,
			tooltip:appLang.SAVE,
			hidden:!this.canEdit,
			disabled:true
		});

		this.dataGrid = Ext.create('Ext.grid.Panel', {
			store: this.dataStore,
			viewConfig:{
				stripeRows:true,
				enableTextSelection:true
			},
			frame: false,
			loadMask:true,
			columnLines: true,
			autoScroll:true,
			clicksToEdit:1,
			selModel: {
				selType: 'cellmodel'
			},
			tbar:[this.saveBtn],
			columns:[
				{
					text:appLang.MODULE,
					dataIndex:'module',
					align:'left',
					renderer:false,
					editable:false,
					id:'module',
					width:200
				},{
					text:appLang.ALL,
					dataIndex:'id',
					id:'all',
					width:30,
					scope:this,
					renderer:function(value, metaData, record, rowIndex, colIndex, store){
						var allChecked = this.checkPermissionsCol(record);
						if(allChecked)
							return '<img src="'+app.wwwRoot+'js/lib/extjs4/resources/themes/images/default/menu/checked.gif">';
						else
							return '<img src="'+app.wwwRoot+'js/lib/extjs4/resources/themes/images/default/menu/unchecked.gif">';
					}
				},{
					text:appLang.VIEW,
					dataIndex:'view',
					align:'center',
					renderer:app.checkboxRenderer,
					xtype:'checkcolumn'
				},{
					text:appLang.EDIT,
					dataIndex:'edit',
					align:'center',
					renderer:app.checkboxRenderer,
					xtype:'checkcolumn'
				},{
					text:appLang.DELETE,
					dataIndex:'delete',
					align:'center',
					renderer:app.checkboxRenderer,
					xtype:'checkcolumn'
				},{
					text:appLang.TO_PUBLISH,
					dataIndex:'publish',
					id:'publish',
					align:'center',
					xtype:'checkcolumn',
					renderer:function(value, metaData, record, rowIndex, colIndex, store){
						if(record.get('rc'))
							return app.checkboxRenderer(value, metaData, record, rowIndex, colIndex, store);
						else
							return '-';
					}
				}],
			plugins: [cellEditing]

		});
		this.dataGrid.on('cellclick',function(grid, cell, columnIndex, record , node , rowIndex , evt){
			var column = grid.getHeaderCt().getHeaderAtIndex(columnIndex).id;

			switch(column){
				case 'all':
					var allChecked = this.checkPermissionsCol(record);
					if (!allChecked){
						record.set('view' , true);
						record.set('edit' , true);
						record.set('delete' , true);

						if(record.get('rc')){
							record.set('publish' , true);
						}
					}else{
						record.set('view' , false);
						record.set('edit' , false);
						record.set('delete' , false);

						if(record.get('rc')){
							record.set('publish' , false);
						}
					}
					return false;
					break;
				case 'publish':
					if(!record.get('rc'))
						return false;
					break;
			}
		},this);

		this.items = [this.dataGrid];
		this.callParent(arguments);
	},

	checkPermissionsCol:function(record){
		var toCheck = ['view','edit','publish','delete'];
		var allChecked = true;
		Ext.each(toCheck , function(item){
			if(item !='publish'){
				if(!record.get(item)){
					allChecked = false;
				}
			}

			if(item=='publish' && record.get('rc')){
				if(!record.get(item)){
					allChecked = false;
				}
			}
		},this);
		return allChecked;
	},

	savePermissions: function(){
		var store = this.dataStore;

		var data = app.collectStoreData(this.dataStore);
		data = Ext.encode(data);
		Ext.Ajax.request({
			url:app.root + 'savepermissions',
			method: 'post',
			params:{
				'data':data,
				'user_id':store.proxy.extraParams['user_id'],
				'group_id':store.proxy.extraParams['group_id']
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					store.commitChanges();
				}else{
					Ext.Msg.alert(' ', response.msg);
				}
			},
			failure:app.ajaxFailure
		});
	}
});

Ext.define('app.crud.user.Groups',{
	extend:'Ext.Panel',
	dataGrid:null,
	dataStore:null,
	permissionsPanel:null,

	constructor: function(config) {
		config = Ext.apply({
			modal: true,
			layout: {
				type: 'hbox',
				pack: 'start',
				align: 'stretch'
			}
		}, config || {});

		this.callParent(arguments);
	},

	initComponent:function(){

		this.dataStore = Ext.create('Ext.data.Store', {
			model: 'app.crud.user.GroupModel',
			proxy: {
				type: 'ajax',
				url: app.root + 'grouplist',
				reader: {
					type: 'json',
					rootProperty: 'data',
					totalProperty: 'count',
					idProperty: 'id'
				},
				startParam:'pager[start]',
				limitParam:'pager[limit]',
				sortParam:'pager[sot]',
				dirParam:'pager[dir]',
				simpleSortMode: true
			},
			pageSize: 50,
			remoteSort: true,
			autoLoad: true,
			sorters: [{
				property : 'title',
				direction: 'ASC'
			}]
		});
		var me = this;

		this.dataGrid = Ext.create('Ext.grid.Panel',{
			store: this.dataStore,
			viewConfig:{
				stripeRows:true,
				enableTextSelection:true
			},
			loadMask:true,
			columnLines: true,
			autoScroll:true,
			frame: false,
			width:300,
			title:appLang.GROUPS,
			columns:[
				{
					title:appLang.NAME,
					dataIndex:'title',
					align:'left',
					flex:1
				},{
					header:appLang.SYSTEM,
					dataIndex:'system',
					align:'center',
					width:60,
					renderer:app.checkboxRenderer
				},{
					title:'',
					dataIndex:'id',
					id:'deleteColumn',
					width:30,
					align:'center',
					renderer:function(value, metaData, record, rowIndex, colIndex, store){
						if(!me.canDelete)
							return '';

						if(record.get('system')==true){
							return '';
						}else{
							return '<img src="'+app.wwwRoot+'i/system/delete.gif" style="cursor:pointer;" title="'+appLang.DELETE_ITEM+'">';
						}
					}
				}
			]
		});

		this.permissionsPanel =  new app.crud.user.Permissions({
			flex:1,
			frame:false,
			border:false,
			canEdit:this.canEdit
		});

		if(this.canEdit){
			this.tbar=[{
				text:appLang.ADD_GROUP,
				listeners:{
					click:{
						fn:this.addGroup,
						scope:this
					}
				}
			}];
			/**
			 * @todo fix event
			 */
			this.dataGrid.on('cellclick',function(grid, cell, columnIndex, record , node , rowIndex , evt){
				var column = grid.getHeaderCt().getHeaderAtIndex(columnIndex).id;
				if(column =='deleteColumn'){
					if(!record.get('system')){
						this.removeGroup(record.get('id'));
					}
				}else{
					this.permissionsPanel.saveBtn.enable();
					this.permissionsPanel.dataGrid.getSelectionModel().deselectAll();
					this.dataGrid.getSelectionModel().select(rowIndex , false);
					this.groupSelected(rowIndex , record);
				}
			},this);
		}

		this.items=[this.dataGrid,this.permissionsPanel];
		this.callParent(arguments);
	},

	groupSelected: function(rowIndex , record){
		var store = this.permissionsPanel.dataStore;
		this.permissionsPanel.setTitle('"'+record.get('title')+'" ' + appLang.GROUP_PERMISSIONS);
		store.proxy.setExtraParam('group_id' , record.get('id'));
		store.proxy.setExtraParam('user_id' ,  0);
		store.removeAll();
		store.load();
	},

	addGroup:function(){
		var handle = this;
		Ext.Msg.prompt(appLang.MESSAGE, appLang.MSG_ENTER_NEW_GROUP_NAME , function(btn , text){
			if(btn != 'ok' || !text.length){
				return
			}
			Ext.Ajax.request({
				url: app.root + "addgroup",
				method: 'post',
				params:{
					'name':text
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						handle.dataStore.load();
					}else{
						Ext.Msg.alert(response.msg);
					}
				},
				failure:app.ajaxFailure
			});

		});
	},

	removeGroup: function(id){
		var handle = this;
		Ext.Ajax.request({
			url: app.root + "removegroup",
			method: 'post',
			params:{
				'id':id
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					handle.dataStore.load();
				}else{
					Ext.Msg.alert(response.msg);
				}
			},
			failure:app.ajaxFailure
		});
	}
});

Ext.onReady(function(){
	app.content.add(Ext.create('app.crud.user.Main',{
		title:appLang.USERS + ' :: ' + appLang.HOME,
		canEdit:canEdit,
		canDelete:canDelete
	}));
});