Ext.ns('app.crud.externals');


Ext.define('app.crud.externals.Model', {
    extend: 'Ext.data.Model',
    fields: [
         {name:'id',type:'string'},
 	     {name:'title', type:'string'},
 	     {name:'description',type:'string'},
 	     {name:'author',type:'string'},
 	     {name:'version',type:'string'},
 	     {name:'active', type:'boolean'}	        	     
    ]
});

Ext.define('app.crud.externals.Main',{
    extend:'Ext.Panel',
	dataStore:null,
	controllersStore:null,
	dataGrid:null,
	searchField:null,
	saveButton:null,
	addButton:null,
	
	layout:'fit',
	
	canEdit:false,
	canDelete:false,
	controllerUrl:'',
	

	initComponent: function(){
		var me = this;

		
		this.cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {clicksToEdit: 1});
		this.dataStore = Ext.create('Ext.data.Store' , {
		    model:'app.crud.externals.Model',
		 	autoLoad:true,
		 	autoSave:false,
			proxy:{
				type: 'ajax',
				url: this.controllerUrl + 'list',
			    reader: {
		            type: 'json',
		            root: 'data',
		            idProperty: 'id'
		        },
		    	simpleSortMode: true
			},
			sorters: [{
                  property : 'title',
                  direction: 'ASC'
            }]
		});

		
	   var columns = [
	                  {
	                	text:appLang.INFO,
	                	flex:1,
	                    dataIndex:'id',
	                    xtype:'templatecolumn',
		        	    tpl: new Ext.XTemplate(
		        			   '<div style="white-space:normal;">',
		        			   '<span class="moduleTitle">{id} {title}</span><br>',
		        			   '<span class="moduleAuthor">'+ appLang.AUTHOR + ': {author}</span>',
		        			   '<span class="moduleVersion">'+ appLang.VERSION + ': {version}</span><br>',
		        			   '<div class="moduleDescription">{description}</div>',
		        			   '</div>'
		        	   )
	                   },{
							text:appLang.ACTIVE,
						    dataIndex: 'active',
						    width:60,
						    align:'center',
						    id:'active',
						    renderer:app.checkboxRenderer,
						    editor:{
						    	xtype:'checkbox'
						    },
						    editable:this.canEdit
						}     
	   ];
		
	   if(this.canEdit){
		   columns.push(
			 { 
			   xtype:'actioncolumn',
	    	   width:20,
			   items:[
			       {
			    	   iconCls:'deleteIcon',
			    	   tooltip:appLang.DELETE,
			    	   handler:function(grid , row , col){
			    		   var store = grid.getStore();
			    		   store.remove(store.getAt(row));
			    	   }
			       }
			 	]
	       });
	   }
	   
		
	   this.dataGrid = Ext.create('Ext.grid.Panel',{
				  store: this.dataStore,
				  viewConfig:{
					  stripeRows:true
				  },
				  frame: false,
			      loadMask:true,
				  columnLines: true,
				  autoScroll:true,
				  selModel: {
			          selType: 'cellmodel'
			      },
			      columns: columns,
			      plugins: [this.cellEditing],
			      tbar:[{
						iconCls:'saveIcon',
						hidden:!this.canEdit,
						text:appLang.SAVE,
						scope:this,
						handler:this.saveAction
			      }]
	
	   });
	   
	  this.items = [this.dataGrid];
	  this.callParent(arguments); 
   },
   saveAction:function(){
	   var valid = true;
	   var data = []; 
	   this.dataStore.each(function(record){
		  data.push({id:record.get('id'),active:record.get('active')});
	   },this);
	   
	   Ext.Ajax.request({
	 		url: app.root + "update",
	 		method: 'post',
	 		params:{
	 			'data':Ext.JSON.encode(data)
	 		},
	 		scope:this,
	        success: function(response, request) {
	 			response =  Ext.JSON.decode(response.responseText);
	 			if(response.success){
	 				this.dataStore.load();
	 			}else{
	 				Ext.Msg.alert(appLang.MESSAGE, response.msg);
	 			}	
	       },
	       failure:function() {
			   Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
	       }
	 	});
   }
});


Ext.onReady(function(){
	
	var dataPanel;
	
	if(!externalsEnabled)
	{
		dataPanel = Ext.create('Ext.panel.Panel',{
			title:'&nbsp;',
			html:'<div style="padding-top:50px;"><center><h1>'+appLang.MODULE_DISABLED+'</h1></center></div>'
		});
	}
	else
	{
	
		dataPanel = Ext.create('app.crud.externals.Main',{
			title:appLang.EXTERNAL_MODULES + ' :: ' + appLang.HOME,
			canEdit:canEdit,
			canDelete:canDelete,
			controllerUrl:app.root
		});
	}
	app.content.add(dataPanel);	
});
