Ext.ns('app.import');

/**
 * Import application object
 * @abstract
 * @events firstRowIdentified
 *
 * @event firstRowIdentified
 * @param number
 *
 * @event importSuccess
 * @param result
 *
 * @event importError
 * @param string message
 * @param {array} errors
 *
 */
Ext.define('app.import.Panel',{
	extend: 'Ext.panel.Panel',
	/**
	 * Controller URL
	 * @param string controllerUrl
	 */
	controllerUrl:null,
	/**
	 * File upload form
	 * @param {Ext.form.FormPanel} uploadForm
	 */
	uploadForm:null,
	/**
	 * Import config form
	 * @param {Ext.form.FormPanel} importForm
	 */
	importForm:null,
	/**
	 * Import form container panel
	 * @param {Ext.Panel} impotrtFormContainer
	 */
	impotrtFormContainer:null,
	/**
	 * Container panel for import data grid
	 * @param {Ext.Panel} gridContainer
	 */
	gridContainer:null,
	/**
	 * Import data grid
	 * @param {Ext.grid.GridPanel} dataGrid
	 */
	dataGrid:null,
	/**
	 * Upload identify
	 * @param integer uploadid
	 */
	uploadid:null,
	/**
	 * Panel layout
	 */
	layout:'border',
	/**
	 * Columns which are expected in  import controller
	 * @param {Array} expectedColumns
	 */
	expectedColumns:[],
	/**
	 * Import types radiogroup items
	 */
	importTypes:[],
	/**
	 * Selected column group
	 */
	visibleGroup:false,
	/**
	 * Additional import form fields
	 * @property {Array}
	 */
	importFormFields:[],
	/**
	 * @property Ext.form.FieldSet,
	 */
	expectedColsContainer:false,
	/**
	 * @property Ext.form.FieldSet
	 */
	importFieldsContainer:false,
	/**
	 * first data row index
	 * @property integer
	 */
	_firstDataRow:1,
	/**
	 * Selected column group
	 */
	visibleGroup:false,
	/**
	 * Additional query params
	 * @propety {Object}
	 */
	extraParams:null,

	noIcon:false,
	noNotRequiredIcon:false,
	yesIcon:false,

	border:false,
	bodyBorder:false,
	items:[],
	/**
	 * Localisation object
	 * @property {object}
	 */
	lang:false,

	/**
	 * Columns which are expected in  import controller
	 * @param {Array} expectedColumns
	 */
	expectedColumns:null,

	initComponent:function(){

		this.importHelpText = this.lang.click_column;

		this.noIcon = app.wwwRoot+'i/system/no.png',
			this.noNotRequiredIcon = app.wwwRoot+'i/system/no-g.png',
			this.yesIcon = app.wwwRoot+'i/system/yes.gif',

			this.expectedColsContainer = Ext.create('Ext.form.FieldSet',{
				title:this.lang.expected_columns,
				defaults:{
					labelWidth:150
				},
				items:[]
			});

		this.importFieldsContainer = Ext.create('Ext.form.FieldSet',{
			items:[],
			hidden:true
		});

		this.uploadForm = Ext.create('Ext.form.Panel',{
			region:'north',
			padding:3,
			frame:true,
			labelWidth:1,
			autoHeight:true,
			buttonAlign:'left',
			items:[
				{
					xtype:'fieldcontainer',
					layout:'hbox',
					items:[
						{
							xtype: 'filefield',
							emptyText: appLang.SELECT_FILE,
							buttonText: appLang.SELECT_FILE,
							// allowBlank:false,
							buttonConfig: {
								iconCls: 'upload-icon'
							},
							width:345,
							name:'file',
							listeners:{
								change:{
									fn:this.uploadFile,
									scope:this
								}
							}
						},{
							xtype:'container',
							width:10
						},
						{
							value:this.lang.click_column,
							xtype:'displayfield',
							name:'importlabel',
							hidden:true
						}
					]
				}
			]
		});


		var formFields = this.initFormFields();
		formFields.push({
			xtype:'displayfield',
			hideLabel:true,
			value:'<i>'+this.lang.required_fields+'</i>'
		});

		this.importForm = Ext.create('Ext.form.Panel' , {
			fileUpload:false,
			frame:false,
			border:false,
			padding:2,
			bodyBorder:false,
			bodyCls:'formBody',
			fieldDefaults:{
				labelWidth:150
			},
			scrollable:true,
			hidden:true,
			defaults:{
				anchor:'98%'
			},
			title:this.lang.import_config,
			items:formFields,
			buttons:[
				{
					text:this.lang.import,
					iconCls:'uploadIcon',
					width:100,
					scale:'large',
					listeners:{
						click:{
							fn:this.importData,
							scope:this
						}
					}
				}
			]
		});

		this.gridContainer = Ext.create('Ext.Panel',{
			items:[],
			frame:false,
			layout:'fit',
			region:'center',
			bodyCls:'formBody',
			border:false
		});

		this.importFormContainer = Ext.create('Ext.Panel',{
			frame:false,
			layout:'fit',
			region:'west',
			width:350,
			bodyCls:'formBody',
			items:[this.importForm],
			border:true
		});

		this.items = [this.uploadForm , this.gridContainer, this.importFormContainer];


		this.callParent();
	},
	/**
	 * Initi import form fields
	 */
	initFormFields:function(){
		var formFields = [
			{
				xtype:'hidden',
				name:'uploadid'
			},{
				xtype:'displayfield',
				value:this.lang.click_first_row,
				hideLabel:true
			},{
				xtype:'numberfield',
				minValue: 1,
				allowDecimals:false,
				name:'first_row',
				fieldLabel:this.lang.first_data_row,
				value:1,
				width:40,
				allowBlank:false,
				listeners:{
					change:{
						fn:this.onFirstRowIdentified,
						scope:this
					}
				}
			}
		];

		/**
		 * Insert additional fields
		 */
		if(!Ext.isEmpty(this.importFormFields)){
			Ext.each(this.importFormFields , function(field){
				formFields.push(field);
			});
		}

		formFields.push(this.expectedColsContainer);

		return  formFields;
	},
	/**
	 * File upload action
	 * @returns void
	 */
	uploadFile:function(){
		var handle = this;
		this.uploadForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.SAVING,
			method:'post',
			timeout:600,
			params:this.extraParams,
			url:this.controllerUrl + 'upload/',
			success: function(form, action) {
				if(!action.result.success){
					handle.importForm.hide();
					Ext.Msg.alert(appLang.MSG, action.result.msg);
					return;
				} else{
					// handle.resetIdentify();
					handle.showRecords(action.result ,action.result.col_count , action.result.uploadId  , false);
				}
			},
			failure: app.formFailure
		});
	},
	/**
	 * Show uploaded records
	 * @param {Array} result
	 * @param integer colCount - columns count
	 * @param integer uploadId - uploaded file ID
	 * @param boolean isResults - true if it is 3 stage of upload
	 * @returns void
	 */
	showRecords:function(result , colCount , uploadId , isResults){
		var me = this;
		this.expectedColumns = result.expectedColumns;
		this.uploadid = uploadId;
		var data = result.data;

		var iForm = this.importForm.getForm();
		this.importFieldsContainer.removeAll();

		this.expectedColsContainer.removeAll();

		this.uploadForm.getForm().findField('importlabel').show();

		Ext.each(this.expectedColumns , function(item){
			item.menuText = item.text;
			if(item.required){
				item.menuText +=' *';
			}

			var ico = this.noNotRequiredIcon;
			if(item.required){
				ico = this.noIcon;
			}

			this.expectedColsContainer.add(
				{
					xtype:'fieldcontainer',
					layout: 'hbox',
					items:[
						{
							name:item.id ,
							xtype:'displayfield',
							hideLabel:true,
							value:'<img src="'+ico+'">',
							width:20
						},{
							xtype:'displayfield',
							value:item.menuText,
							hideLabel:true,
							flex:1
						}
					]
				}
			);
		},this);

		if(Ext.isEmpty(data)){
			return;
		}

		var dataColumns = [];
		var dataFields = [];
		var colRenderer = this.createColRenderer();

		for(var i=0;i<colCount;i++){

			var header ='';
			if(isResults){
				if(result.titles!=undefined && !Ext.isEmpty(result.titles[i])){
					header = result.titles[i];
				}
			}else{
				header = this.lang.column +' '+ (i+1);
			}

			dataColumns.push({
				header: header,
				dataIndex: 'col'+i,
				id:'col'+i,
				sortable: false,
				menuDisabled: true,
				width:140,
				renderer:colRenderer
			});

			dataFields.push({
				name:'col'+i,
				type:'string'
			});
		}

		this.topTBar = Ext.create('Ext.toolbar.Toolbar',{
			xtype: 'toolbar',
			dock: 'top',
			items: []
		});

		this.dataGrid = Ext.create('Ext.grid.Panel' , {
			scrollable: true,
			frame: false,
			loadMask:true,
			stripeRpws:true,
			columnLines:true,
			enableColumnResize:true,
			enableColumnMove : false,
			enableColumnHide : false,
			enableHdMenu:true,
			dockedItems: [this.topTBar],
			columns:dataColumns,
			invalidateScrollerOnRefresh: false,
			store:Ext.create('Ext.data.Store',{
				fields:dataFields,
				autoLoad:false,
				data:data
			})
		});

		this.dataGrid.getView().headerCt.on('headerclick',this.showHeaderMenu,this);

		if(isResults && result.success_records!=undefined){
			this.topTBar.add('Success records: '+result.success_records +'<br> ' +appLang.CANT_PARSE);
		}else{
			this.topTBar.hide();
			this.importFormContainer.show();


			if(this.importForm.getForm().findField('first_row')!=undefined){
				this.dataGrid.on('itemdblclick', function(view,record,item,index){
					this.importForm.getForm().findField('first_row').setValue((index+1));
				},this);
			}
		}

		this.gridContainer.removeAll();
		this.gridContainer.add(this.dataGrid);
		//this.gridContainer.syncSize();
		this.importForm.getForm().reset();

		if(!isResults)
		{
			this.importFormContainer.show();
			this.importForm.getForm().findField('uploadid').setValue(uploadId);

			this.resetIdentify();
			this.importForm.show();

		}else{
			this.importFormContainer.hide();
			this.gridContainer.show();
		}

		/**
		 *  Apply saved setings
		 */
		if(result.settings){
			this.applySettings(result.settings);
		}
		this.importFormContainer.updateLayout();
		this.updateLayout();

	},
	/**
	 * Create column renderer
	 */
	createColRenderer:function(){
		var me = this;
		/**
		 * Hilight first data row
		 * @param {object} value - The data value for the current cell
		 * @param {object} metaData -A collection of metadata about the current cell;
		 * can be used or modified by the renderer. Recognized properties are: tdCls, tdAttr, and style.
		 * @param {Ext.data.Model} record - The record for the current row
		 * @param integer rowIndex -The index of the current row
		 * @param integer colInde - The index of the current column
		 * @param {Ext.data.Store} - The data store
		 * @param {Ext.view.View} - The current view
		 * @return string
		 */
		var colRenderer = function(value , metaData , record , rowIndex , colIndex , store , view ){
			if(rowIndex == (me._firstDataRow -1 )){
				metaData.style = 'background-color:#29F505;display:block;color:#000;';
			}
			return value;
		};

		return  colRenderer;
	},
	/**
	 * Show column header context menu
	 */
	showHeaderMenu:function(ct,column,e) {
		e.stopEvent();
		var headerCt = this.dataGrid.getView().headerCt;
		var index = headerCt.getHeaderIndex(column);

		var menu = Ext.create('Ext.menu.Menu',{plain: true});

		menu.add({
			text: this.lang.column + ' ' + (index+1),
			componentCls: 'no-icon-menu',
			listeners:{
				click:{
					fn:function(btn){
						this.columnIdentified(column , index , -1);
					},
					scope:this
				}
			}
		});

		Ext.each(this.expectedColumns , function(record){
			/*
			 * If expected group is selected then fields from the other group will not be shown
			 */
			if(this.visibleGroup && !Ext.isEmpty(record.group) && record.group!=this.visibleGroup){
				return;
			}

			menu.add({
				text:record.menuText,
				menuAlign:'left',
				componentCls: 'no-icon-menu',
				showSeparator: false,
				listeners:{
					click:{
						fn:function(btn){
							this.columnIdentified(column , index , record.id);
						},
						scope:this
					}
				}
			});
		},this);
		menu.showAt(e.xy);
	},
	/**
	 * Set column identification
	 * @param {Ext.grid.Column} column
	 * @param integer colIndex
	 * @param string key
	 */
	columnIdentified:function(column, colIndex , key){
		var iForm = this.importForm.getForm();
		var abortedGroup = false;
		var selectedGroup = false;

		if(key==-1){
			Ext.each(this.expectedColumns , function(record){
				if(record.columnIndex == colIndex){
					record.columnIndex = -1;
					this.markIdentified(record , false);
					if(!Ext.isEmpty(record.group)){
						abortedGroup = record.group;
					}
				}

			},this);
			column.setText('Колонка '+ (colIndex+1));

		}else{

			Ext.each(this.expectedColumns , function(record){
				/*
				 * Disable selection on second time
				 */
//		 if(record.id == key && record.columnIndex == colIndex){
//		     this.markIdentified(record , false);
//		     column.setText('Колонка '+ (record.columnIndex+1));
//		     record.columnIndex = -1;
//		     return;
//
//		 }
				/*
				 * Unset other selection with same key
				 */
				if(record.id == key && record.columnIndex != colIndex && record.columnIndex !=-1){
					var col = this.dataGrid.getView().headerCt.getGridColumns()[record.columnIndex];
					col.setText('Колонка '+ (record.columnIndex+1));
					record.columnIndex = -1;
				}

				/*
				 * Unset previous column selection
				 */
				if(record.columnIndex == colIndex){
					record.columnIndex = -1;
					this.markIdentified(record , false);
					if(!Ext.isEmpty(record.group)){
						abortedGroup = record.group;
					}
				}

				/*
				 * Select column
				 */
				if(record.id == key){
					record.columnIndex = colIndex;
					column.setText('<div class="importSelected">&nbsp;'+record.text+'</div>');
					this.markIdentified(record , true);
					if(!Ext.isEmpty(record.group)){
						selectedGroup = record.group;
					}
				}
			},this);
		}
		/*
		 * Refresh expected fields form
		 */
		if(selectedGroup){
			this.showGroup(selectedGroup);
		}else{
			if(abortedGroup != false && !this.groupHasSelection(abortedGroup)){
				this.showGroup(false);
			}
		}
	},
	/**
	 * Reset column identification
	 */
	resetIdentify:function(){
		var iForm = this.importForm.getForm();
		this.visibleGroup = false;
		Ext.each(this.expectedColumns , function(record){
			record.columnIndex = -1;
			this.markIdentified(record , false);

			iForm.findField(record.id).show();
		},this);
	},
	/**
	 * Start data import
	 */
	importData:function(){

		var params = {};
		var error = false;

		if(Ext.isObject(this.extraParams)){
			params = Ext.apply(params , this.extraParams);
		}

		/**
		 * Validate fields
		 */
		Ext.each(this.expectedColumns , function(record){
			params['columns['+record.id+']'] = record.columnIndex;
			if(record.columnIndex == -1){
				if(record.required || this.visibleGroup == record.group){

					error = true;
				}
			}
		});

		if(error){
			Ext.Msg.alert(appLang.MESSAGE, appLang.FILL_FORM);
			return;
		}

		var handle = this;

		this.importForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.SAVING,
			method:'post',
			timeout:900,
			url:this.controllerUrl + 'import/',
			params:params,
			success: function(form, action) {
				if(!action.result.success){
					handle.fireEvent('importError' , action.result.msg, action.result.errors);
				} else{
					handle.fireEvent('importSuccess' , action.result.data);
				}
			},
			failure: function(form, action) {
				if (!action.result) {
					app.formFailure(arguments);
				} else {
					handle.fireEvent('importError' , action.result.msg, action.result.errors);
				}
			}
		});
	},
	onFirstRowIdentified:function(){
		var oldRowIndex = this._firstDataRow;
		this._firstDataRow =  this.getFirstRow();
		this.fireEvent('firstRowIdentified' , this._firstDataRow);

		var view = this.dataGrid.getView();

		// refresh only 2 rows
		view.refreshNode(oldRowIndex -1);
		view.refreshNode(this._firstDataRow - 1);

		// refresh view
		//this.dataGrid.getView().refresh();
		//this.dataGrid.getView().focusRow(this._firstDataRow-1);
	},
	/**
	 * Установить значек идентифицированной записи
	 * @param {object} record
	 * @param boolean status
	 */
	markIdentified: function(record , status){
		var formField = this.importForm.getForm().findField(record.id);
		if(status){
			formField.setValue('<img src="'+this.yesIcon+'" />');
		}else{
			if(!Ext.isEmpty(record.required) && record.required){
				formField.setValue('<img src="'+this.noIcon+'" />');
			}else{
				formField.setValue('<img src="'+this.noNotRequiredIcon+'" />');
			}
		}
		formField.show();
	},
	/**
	 * Set first data row
	 * @param integer number
	 */
	setFirstRow:function(number){
		this.importForm.getForm().findField('first_row').setValue(number);
	},
	/**
	 * Get first data row
	 */
	getFirstRow:function(){
		return this.importForm.getForm().findField('first_row').getValue();
	},
	/**
	 * apply column settings
	 * @param {Object} config
	 */
	applySettings:function(config){

		// first data row
		if(!Ext.isEmpty(config.first_row) && config.first_row >0){
			this.setFirstRow(config.first_row);
		}
		// columns
		if(!Ext.isEmpty(config.columns) && Ext.isObject(config.columns))
		{
			for(var i in config.columns)
			{
				//wrong index
				if(config.columns[i] < 0){
					continue;
				}
				// chek expected column key
				Ext.each(this.expectedColumns , function(record){
					if(record.id === i)
					{
						// check if column exists
						var colObjects = this.dataGrid.getView().headerCt.getGridColumns();
						if(!Ext.isEmpty(colObjects[config.columns[i]])){
							// identify column
							this.columnIdentified(colObjects[config.columns[i]], config.columns[i], i);
						}
					}
				}, this);
			}
		}
	},
	/**
	 * Refresh grid cells
	 * @property {array} record properties
	 */
	refreshRecordCells:function(){
		if(this.dataGrid == null){
			return;
		}
		this.dataGrid.getView().refresh();
	},
	/**
	 * @todo update implementation
	 * Show group of expected fields
	 * @param mixed integer / false  group id
	 * @return void
	 */
	showGroup: function(id){
		this.visibleGroup = id;
		var iForm = this.importForm.getForm();
		Ext.each(this.expectedColumns , function(record){
			if(!Ext.isEmpty(record.group)){
				if(id == false || record.group == id){
					iForm.findField(record.id).show();
				}else{
					iForm.findField(record.id).hide();
				}
			}
		},this);
	},
	/**
	 * @todo update implementation
	 * Check if group has selected fields
	 * @param integer id - group id
	 * return boolean
	 */
	groupHasSelection:function(id){
		var hasSelection = false;
		Ext.each(this.expectedColumns , function(record){
			if(!Ext.isEmpty(record.group) && record.columnIndex != -1){
				hasSelection = true;
			}
		},this);
		return hasSelection;
	}
});