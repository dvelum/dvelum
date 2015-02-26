
/**
 * Media library item edit window
 * @extend {Ext.Window}
 *
 * @event dataSaved
 *
 */
Ext.define('app.medialib.EditWindow',{
	extend:'Ext.Window',
	/**
	 * @var {Ext.form.FormPanel}
	 */
	editForm:null,
	/**
	 * @var integer
	 */
	recordId:0,
	inited:0,
	mainGridId:'',
	viewFormId:'',
	
	cropButton:null,
	dataRec:null,
	showType:'select',
	
	constructor: function(config) {
		config = Ext.apply({
			modal: true,
	        layout:'fit',
	        title: appLang.MODULE_MEDIALIB +' :: ' + appLang.EDIT_ITEM,
	        width: 550,
	        height: 525,       
	        closeAction: 'destroy',
	        resizable:false
	    }, config || {});
		
		this.callParent(arguments);
	},
	
	loadData: function(){
		
		if(!this.recordId){
			return;
		}
		var handle  = this;
		this.editForm.getForm().load({
			url:  app.admin + app.delimiter + 'medialib' +  app.delimiter + 'getitem',
			method: 'post',
	 		params:{
	 			'id':this.recordId
	 		},
	 		success:function(form , action){
	 			if(!action.result.success){
					return;
				}
	 			if(action.result.data.type =="image"){
					handle.cropButton.show();
				}	
	 		}
		});
	},
	
	saveData:function(){
		var handle = this;
		this.editForm.getForm().submit({
			clientValidation: true,
			waitTitle:appLang.SAVING,
			method:'post',
			url: app.admin + app.delimiter + 'medialib' +  app.delimiter + 'update',
			success: function(form, action) {	
   		 		if(action.result.success){
   		 			handle.fireEvent('dataSaved');
   		 		}else{
   		 			Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
   		 		}	
   	        },
   	        failure: app.formFailure
		});
	},
	
	/**
	 * {Ext.data.Record}
	 */
	setData:function (rec){
		this.dataRec = rec;
		this.editForm.getForm().loadRecord(this.dataRec);
	},
	
	initComponent : function(){
		
		this.cropButton = Ext.create('Ext.Button',{
			fieldLabel:appLang.CROP,
			text:appLang.CROP,
			hidden:true,
			anchor:false,
			width:70,
			listeners:{
				'click':{
					fn:function(){
						var win = Ext.create('app.medialib.CropWindow',{
							dataRec:this.dataRec
						});
						
						win.on('dataSaved' , function(){
							if(!Ext.isEmpty(this.mainGridId)){
								Ext.getCmp(this.mainGridId).getStore().load();
							}
							var date = new Date();
							if(!Ext.isEmpty(this.viewFormId)){
								Ext.getCmp(this.viewFormId).getForm().findField('thumbnail').setValue(this.dataRec.get('thumbnail')+'?'+Ext.Date.format(date,'Ymdhis'));
							}
							this.editForm.getForm().findField('thumbnail').setValue(this.dataRec.get('thumbnail')+'?'+ Ext.Date.format(date,'Ymdhis'));
						},this);
						win.show();
						win.maximize();
					},
					scope:this
				}
			}
		});
		
		this.editForm = Ext.create('Ext.form.Panel' ,{
			bodyPadding:5,
			border:false,
			bodyCls:'formBody',
			frame:false,
			fieldDefaults:{
				labelAlign:'right',
				labelWidth:100
			},
			defaults:{
				anchor:'100%'
			},
			items: [{
					fieldLabel:appLang.ID,
					name:"id",
					xtype:"hidden"
				},
				{
					xtype:'imagefield',
					fieldLabel:appLang.THUMBNAIL,
					name:'thumbnail',
					anchor:false,
					value:""
				},{
					xtype:'fieldcontainer',
					combineErrors: false,
					msgTarget: 'under',
					hideLabel: false,
					layout: {
					        type: 'hbox',
					        defaultMargins: {top: 0, right: 10, bottom: 0, left: 0}
					},
					items:[
					        {width:94 , xtype:'label'},
							this.cropButton      
					]
				}/*,{
					xtype:'objectlinkfield',
					controllerUrl:app.createUrl([app.admin,'mediacategory','']),
					objectName:'mediacategory',
					fieldLabel:appLang.MEDIA_CATEGORY,
					name:'category'
				}*/,
				{
					allowBlank: false,
					fieldLabel:appLang.TITLE,
					name:"title",
					xtype:"textfield"
				},{
					fieldLabel:appLang.ALTER_TEXT,
					name:"alttext",
					xtype:"textfield"
				},{
					fieldLabel:appLang.CAPTION,
					name:"caption",
					xtype:'htmleditor',
					enableAlignments:true,
					enableColors:true,
					enableFont:true,
					enableFontSize :true,
					enableFormat:true,
					enableLinks:false,
					enableLists:false, 
					enableSourceEdit:false,
					height:120
				},{
					fieldLabel:appLang.DESCRIPTION,
					name:"description",
					xtype:"textarea",
					height:120
				}]		
		}); 
		
		this.items = [this.editForm];
		
		this.buttons=[
		     {
		    	 text:appLang.SAVE,
		    	 listeners:{
		    		 click:{
		    			 fn:function(){
		    				 this.saveData();
		    			 },
		    			 scope:this
		    		 }
		    	 }
		     } , {
		    	 text:appLang.CLOSE,
		    	 listeners:{
		    		 click:{
		    			 fn:function(){
		    				 this.close();
		    			 },
		    			 scope:this
		    		 }
		    	 }
		     }      
		 ];
		
	 	this.callParent(arguments);
        this.loadData(); 
        this.on('show',function(){app.checkSize(this);});
	}
});