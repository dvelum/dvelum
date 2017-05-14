/**
 *
 * @event select - Fires when object is selected
 * @param string object name
 * @param {Array} field names
 */
Ext.define('designer.ormSelectorWindow',{
	extend:'Ext.Window',
	title:desLang.importOrm,
	width:700,
	height:500,
	objectsGrid:null,
	fieldsGrid:null,
	layout:'border',

	initComponent:function(){

		var store = Ext.create('Ext.data.Store',{
			fields:[
				{name:'name' , type:'string'},
				{name:'title' , type:'string'}
			],
			proxy:{
				url:app.createUrl([designer.controllerUrl ,'orm','list','']),
				type:'ajax',
				reader:{
					type:'json',
					idProperty:'name',
					rootProperty:'data'
				}
			},
			autoLoad:true,
			sorters: [{
				property : 'name',
				direction: 'ASC'
			}]
		});

		this.objectsGrid = Ext.create('Ext.grid.Panel',{
			split:true,
			title:desLang.objects,
			region:'center',
			columnLines:true,
			tbar:[
				'->',
				Ext.create('SearchPanel',{
					fieldNames:['name','title'],
					store:store,
					local:true
				})
			],
			store:store,
			columns:[
			         {
			        	 text:desLang.name,
			        	 dataIndex:'name',
			        	 width:100
			         },{
			        	 text:desLang.title,
			        	 flex:1,
			        	 dataIndex:'title'
			         }
			]
		});

		var sm = Ext.create('Ext.selection.CheckboxModel');
		this.fieldsGrid = Ext.create('Ext.grid.Panel',{
			split:true,
			title:desLang.fields,
			region:'east',
			selModel: sm,
			width:350,
			columnLines:true,
			store:Ext.create('Ext.data.Store',{
				fields:[
				        {name:'name' , type:'string'},
				        {name:'title', type:'string'},
				        {name:'type', type:'string'}
				],
				proxy:{
					url:app.createUrl([designer.controllerUrl ,'orm','fields','']),
					type:'ajax',
					extraParams:{
						object:''
					},
					reader:{
						type:'json',
						idProperty:'name',
						rootProperty:'data'
					}
				},
				autoLoad:false,
				sorters: [{
	                  property : 'name',
	                  direction: 'ASC'
	            }]
			}),
			columns:[
			         {
			        	 text:desLang.name,
			        	 dataIndex:'name',
			        	 width:100
			         },{
			        	 text:desLang.title,
			        	 flex:1,
			        	 dataIndex:'title'
			         },{
			        	 text:desLang.type,
			        	 flex:1,
			        	 dataIndex:'type'
			         }
			]
		});


		this.objectsGrid.getSelectionModel().on('selectionchange',function(sm){
			this.fieldsGrid.getStore().removeAll();
			if(sm.hasSelection()){
				this.fieldsGrid.getStore().proxy.setExtraParam('object', sm.getSelection()[0].get('name'));
				this.fieldsGrid.getStore().load();
			}
		},this);


		this.buttons = [
		      {
		    	  text:desLang.select,
		    	  scope:this,
		    	  handler:this.onSelect
		      },{
		    	  text:desLang.cancel,
		    	  scope:this,
		    	  handler:this.close
		      }
		];

		this.items = [this.objectsGrid , this.fieldsGrid];

		this.callParent();

        this.on('show',function(){
            app.checkSize(this);
        },this);
	},
	onSelect:function(){
		var oSm = this.objectsGrid.getSelectionModel();
		var fSm = this.fieldsGrid.getSelectionModel();

		if(!oSm.hasSelection() ||  !fSm.hasSelection() ){
			Ext.Msg.alert(appLang.MESSAGE, desLang.selectObjectAndFields);
			return;
		}

		var selection =  fSm.getSelection();
		var names = [];
		Ext.each(selection,function(item , index){
			names.push(item.get('name'));
		},this);

		this.fireEvent('select' , oSm.getSelection()[0].get('name') , names);
		this.close();
	}
});