/**
 * A window for view and restores backups
 */
Ext.define('app.crud.orm.backupModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name:'title',  type:'string'}
    ]
});
 
Ext.define('app.crud.orm.restoreBackupWindow', {
	extend:'Ext.window.Window',
	curBackupItem:null,
	dataGrid:null,
	buttonRestore:null,
	
	constructor:function(config){
		config = Ext.apply({
			modal: false,
			layout:'fit',
			title:appLang.BACKUP_RESTORE,
			width: app.checkWidth(350),
			height:app.checkHeight(400),
			closeAction: 'destroy',
			maximizable:true
		}, config || {});
		this.callParent(arguments);
	},
	
	initComponent:function(){
		
		this.dataGrid = Ext.create('Ext.grid.Panel',{
			
			store: Ext.create('Ext.data.Store', {
				model:'app.crud.orm.backupModel',
				proxy:{
					type:'ajax',
					url:app.crud.orm.Actions.listBackups,
					reader: {
			            type: 'json',
			            root: 'data',
			            idProperty: 'title'
			        },
			        simpleSortMode: true
				},
			    autoLoad: true,
			    sorters: [
			              {
			                  property : 'title',
			                  direction: 'DESC'
			              }
			    ]
			}),
			frame: false,
            loadMask:true,
		    columnLines: true,
		    autoscroll:true,
		    bodyBorder:false,
			border:false,
			columns: [{
				text:appLang.TITLE,
				dataIndex:'title',
				flex:1
			},{
            	xtype:'actioncolumn',
            	align:'center',
            	width:20,
            	items:[{
            		tooltip:appLang.DELETE_BACKUP,
            		iconCls:'deleteIcon',
            		width:16,
            		iconCls:'buttonIcon',
            		scope:this,
            		handler:this.deleteBackup
            	}]
            }],
			listeners:{
		    	'select':{
		    		fn:function(rowModel , record , number , options){
		    			 this.curBackupItem = record.get('title');
		    			 this.buttonRestore.enable();
		    		},
		    		scope:this
		    	}
		    }
		});
		
		this.items = [this.dataGrid];
		
		this.buttonRestore = Ext.create('Ext.button.Button', {
			text:appLang.RESTORE,
			disabled:true,
			scope:this,
			handler:this.restoreBackUp
		});
		
		this.buttons = [this.buttonRestore];
		
		this.addEvents(
	            /**
	             * @event backupRestored
	             */
	           'backupRestored'
	    );
		
		this.callParent(arguments);
	},
	deleteBackup:function(grid, rowIndex, colIndex){
		var name = grid.getStore().getAt(rowIndex).get('title');
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_REMOVE_BACKUP+' '+name+'?', function(btn){
			if(btn != 'yes'){
				return;
			}
			
			Ext.Ajax.request({
				url: app.crud.orm.Actions.removeBackUp,
				method: 'post',
				scope:this,
				params:{
					name:name
				},
				success: function(response, request) {
					response =  Ext.JSON.decode(response.responseText);
					if(response.success){
						this.dataGrid.getStore().load();
					}else{
						Ext.Msg.alert(appLang.MESSAGE, response.msg);
					}
				},
				failure:function() {
					Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				}
			});
		},this);
		this.buttonRestore.disable();
	},
	restoreBackUp:function(){
		Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_RESTORE+' '+this.curBackupItem+'?', function(btn){
			if(btn != 'yes'){
				return;
			}
			
			var sql = 0;
			Ext.Msg.confirm(appLang.CONFIRM, appLang.MSG_CONFIRM_RESTORE_SQL, function(btn){
				if(btn == 'yes'){
					sql = 1;
				}
				
				this.getEl().mask(appLang.MSG_RESTORING_BACKUP);
				Ext.Ajax.request({
					url: app.crud.orm.Actions.restoreBackup,
					method: 'post',
					scope:this,
					params:{
						sql:sql,
						name:this.curBackupItem
					},
					success: function(response, request) {
						response =  Ext.JSON.decode(response.responseText);
						if(response.success){
							this.fireEvent('backupRestored');		 
						}else{
							Ext.Msg.alert(appLang.MESSAGE, response.msg);
						}
						this.getEl().unmask();
						this.close();
					},
					failure:function() {
						Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
						this.getEl().unmask();
					}
				});
			},this);
		},this);
	}
});