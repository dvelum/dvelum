Ext.ns('app.crud.videoblock');

app.crud.videoblock.canEdit = false;
app.crud.videoblock.canPublish = false;
app.crud.videoblock.canDelete = false;

Ext.define('app.crud.videoblock.ListModel', {
    extend: 'Ext.data.Model',
    fields: [
     	{name:'id' , type:'integer'},
        {name:'page_id' , type:'string'},  
        {name:'page' , type:'string'},  
        {name:'page_title' , type:'string'},  
        {name:'date_created', type:'date' , dateFormat:'Y-m-d H:i:s'},
        {name:'date_updated',type:'date' , dateFormat:'Y-m-d H:i:s'},
        {name:'user',type:'string' },
        {name:'updater',type:'string'},
        {name:'published' , type:'boolean'},
        {name:'published_version' , type:'integer'},
        {name:'last_version' , type:'integer'}
    ]
});

Ext.define('app.crud.videoblock.Main',{ 
		extend:'Ext.Panel' ,
	   /**
	    * @var {Ext.grid.Panel}
	    */
	   dataGrid:null,
	   /**
	    * @var {Ext.data.Store}
	    */
	   dataStore:null,
	   /**
	    * @var {searchBar}
	    */
	   searchField: null,
	   /**
	    * @var {Ext.Button}
	    */
	   addItemBtn: null,
	   
	   constructor: function(config) {
			
			config = Ext.apply({
				layout:'fit'
		    }, config || {});		
			this.callParent(arguments);
	   },
	   
	   initComponent:function(){
		   
		   this.dataStore = Ext.create('Ext.data.Store', {
			    model: 'app.crud.videoblock.ListModel',
			    proxy: {
			        type: 'ajax',
			    	url:app.root +  'list',
			        reader: {
			            type: 'json',
			            root: 'data',
			            totalProperty: 'count',
			            idProperty: 'id'
			        },
			        startParam:'pager[start]',
			        limitParam:'pager[limit]',
			        sortParam:'pager[sort]',
			        directionParam:'pager[dir]',
				    simpleSortMode: true
			    },
			    pageSize: 50,
		        remoteSort: true,
			    autoLoad: true,
			    sorters: [{
	                  property : 'page_id',
	                  direction: 'DESC'
	            }]
			});

			this.addItemBtn = new Ext.Button({
				 text:appLang.ADD_ITEM,
				 hidden:!app.crud.videoblock.canEdit
			});
			
			this.addItemBtn.on('click' , function(){
				 this.showBlockEdit(0);
			} , this);
	
			this.dataGrid = new Ext.grid.GridPanel({
					store: this.dataStore,
					viewConfig:{
				 		stripeRows:false
				 	},
		            frame: false,
		            loadMask:true,
				    columnLines: true,
				    autoscroll:true,
				    tbar:[this.addItemBtn],
				    columns: [
							{
								id: 'published',
							    sortable: true,
							    text:appLang.STATUS,
							    dataIndex: 'published',
							    width:50,
							    align:'center',
							    renderer:app.publishRenderer 
							}, {
								text:appLang.VERSIONS_HEADER,
								dataIndex:'id',
								align:'center',
								width:150,
					        	renderer:app.versionRenderer
				             },{
							    sortable: true,
							    text:appLang.PAGE_CODE,
							    dataIndex: 'page'
							},{
								id: 'page_title',
							    sortable: true,
							    text:appLang.PAGE_TITLE,
							    dataIndex: 'page_title',
							    sortable:false
							},{
								text:appLang.CREATED_BY,
					        	dataIndex:'date',
					        	sortable: true,
					        	width:210,
					        	renderer:app.creatorRenderer
					          },{
					        	text:appLang.UPDATED_BY,
					        	dataIndex:'update_date',
					        	sortable: true,
						        width:210,
						        renderer:app.updaterRenderer
					          }
				    ],
				    bbar: Ext.create('Ext.PagingToolbar', {
			            store: this.dataStore,
			            displayInfo: true,
			            displayMsg: 'Displaying records {0} - {1} of {2}',
			            emptyMsg:applang.NO_RECORDS_TO_DISPLAY
			        }),
			        listeners : {
				    	'itemdblclick':{
				    		fn:function(view , record , number , event , options){
				    			 this.showBlockEdit(record.get('id'));
				    		},
				    		scope:this
				    	}
					}
			});
			this.items = [this.dataGrid];
			this.callParent(arguments);
	   },
	   /**
	    * Show item edit window
	    * @param integer id
	    */
	   showBlockEdit: function(id){
			var win = new app.crud.videoblock.Window({
				dataItemId:id,
				canDelete:app.crud.videoblock.canDelete,
				canEdit:app.crud.videoblock.canEdit,
				canPublish:app.crud.videoblock.canPublish
			});
			win.on('dataSaved' , function(){
				this.dataStore.load();
			}, this);		
			win.show();
			if(id){
				win.loadData(id , false);
			}	
		}	
});

Ext.define('app.crud.videoblock.Window',{
	   extend:'app.contentWindow',
	   textPanel:null,
	   relatedResources:null,
	   pagesStore:null,
	   constructor: function(config) {		
			config = Ext.apply({
		        title: appLang.VIDEOBLOCK + ' :: ' + appLang.EDIT_ITEM,
		        width: 720,
		        height:640,
		        objectName:'videoblock',
		        controllerUrl:app.root
		    }, config || {});		
			
			this.callParent(arguments);
			
			this.relatedResources = Ext.create('app.relatedGridPanel',{				
				dataId:this.dataItemId,
				title:appLang.RELATED_RESOURCES,
				fieldName:'resources'
			});
			
			
			this.pagesStore = Ext.create('Ext.data.Store', {
			    model: 'app.comboModel',
			    proxy: {
			        type: 'ajax',
			        url: app.root +  'pagelist',
			        reader: {
			            type: 'json',
			            root: 'data',
			            idProperty: 'id'
			        },
				    simpleSortMode: true
			    },
		        remoteSort: false,
			    autoLoad: true,
			    sorters: [{
	                  property : 'title',
	                  direction: 'DESC'
	            }]
			});
			
			this.relatedResources.on('addItemCall' , this.addRelatedResources,this);
			
			this.contentTabs.getComponent(0).add([
				 {
					displayField:"text",
					remote:true,
					allowBlank:false,					
					queryMode:"remote",
					forceSelection:true,
					triggerAction:"all",
					valueField:"id",
					allowBlank: false,
					fieldLabel:appLang.PAGE,
					name:"page_id",
					displayField:'title',
					valueField:'id',
					store:this.pagesStore ,
					xtype:"combo"
				}
			 ]);
				
			this.contentTabs.add(this.relatedResources);
			this.linkedComponents = [this.relatedResources];
	   },
	   addRelatedResources:function(){
		   var win = new app.selectMediaItemWindow({resourceType:'video'});   
		   win.on('itemSelected' , function(rec){	   
			   var newRec = Ext.ModelManager.create({
				    id: rec.get('id'),
	                deleted: 0,
	                title: rec.get('title'),
	                published:1
               }, 'app.relatedGridModel'); 
			this.relatedResources.dataStore.insert(0, newRec);
         
		   },this);
		  	win.show();	  	
	  }
});


Ext.onReady(function(){ 
	Ext.QuickTips.init();
	app.crud.videoblock.canEdit = canEdit;
	app.crud.videoblock.canPublish = canPublish;
	app.crud.videoblock.canDelete = canDelete;
	var dataPanel = Ext.create('app.crud.videoblock.Main',{
		title:appLang.VIDEOBLOCK + ' :: ' + appLang.HOME
	});
	app.content.add(dataPanel);
});