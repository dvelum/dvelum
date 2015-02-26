/**
 *
 * @event itemSelected
 * @param {Ext.data.Record} record
 *
 */
Ext.define('app.selectMediaItemWindow',{
	extend:'Ext.Window',
	/**
	 * @var {app.medialibPanel }
	 */
	medialibPanel:null,
	actionType:'selectId',
	resourceType:'all',

	constructor: function(config) {
		config = Ext.apply({
			modal: true,
	        layout:'fit',
	        title: appLang.MODULE_MEDIALIB + ' :: '+ appLang.EDIT_ITEM,
	        width: 750,
	        height: app.checkHeight(600),
	        closeAction: 'destroy',
	        resizable:true,
	        items:[],
	        maximizable:true
	    }, config || {});
		this.callParent(arguments);
	},
	selectItem:function()
	{

		switch(this.actionType){
			case 'selectId' :
				var sm = this.medialibPanel.dataGrid.getSelectionModel();

				if(!sm.hasSelection()){
					Ext.MessageBox.alert(appLang.MESSAGE,appLang.MSG_SELECT_RESOURCE);
					return;
				}

				var records = sm.getSelection();
				var goodType = true;

				Ext.each(records , function(rec){
					if(this.resourceType!='all' && this.resourceType != rec.get('type')){
						goodType = false;
					}
				},this);

				if(!goodType){
					Ext.MessageBox.alert(appLang.MESSAGE,appLang.SELECT_PLEASE+' '+this.resourceType + ' '+ appLang.RESOURCE);
					return;
				}

				this.fireEvent('itemSelected', records);
				break;
		}
	},
    initComponent : function(){

		this.medialibPanel = Ext.create('app.medialibPanel',{
			 actionType:this.actionType,
			 border:false,
			 checkRights:true
		});

		this.items=[this.medialibPanel];

		if(this.resourceType !='all'){
			this.medialibPanel.on('rightsChecked',function(){
				this.medialibPanel.srcTypeFilter.setValue(this.resourceType);
				this.medialibPanel.srcTypeFilter.disable();
				this.medialibPanel.dataStore.proxy.setExtraParam('filter[type]', this.resourceType);
				this.medialibPanel.dataStore.load();
			},this);
		}

		var me = this;

		this.buttons=[
		           {
		        	    text:appLang.SELECT,
		        	    scope:me,
		        	    handler:me.selectItem
		           },{
		        	   text:appLang.CLOSE,
		        	   scope:me,
		        	   handler:me.close
		           }
		];
		this.callParent(arguments);
	}
});