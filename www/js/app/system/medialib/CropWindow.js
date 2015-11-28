
/**
 * Media library crop item  window
 * @extend {Ext.Window}
 *
 * @event dataSaved
 *
 */
Ext.define('app.medialib.CropWindow',{
	extend:Ext.Window,
	dataRec:null,
	coords:null,
	comboFld:null,
	jcrop:null,
	canEdit:false,
	canDelete:false,
	preview:null,
	centerRegion:null,

	constructor: function(config) {
		config = Ext.apply({
			modal: true,
			layout:'border',
			title: appLang.MODULE_MEDIALIB +' :: '+appLang.CROP_IMAGE,
			width: 600,
			height: 500,
			closeAction: 'destroy',
			resizable:true,
			maximizable:true,
			items:[],
			labelWidth:1,
			scrollable:true,
			tbar:[]
		}, config || {});
		this.callParent(arguments);
	},
	/**
	 * Select image type
	 * @param string type
	 */
	setType: function(type)
	{
		var imgPath = this.getImagePath(this.dataRec , type);
        $('#oldImage').attr('src' , imgPath);
        $('#oldContainer').css('width' , this.extList[type][0]+'px');
        $('#oldContainer').css('height' , this.extList[type][1]+'px');
		$('#cropContainer').css('width' , this.extList[type][0]+'px');
		$('#cropContainer').css('height' , this.extList[type][1]+'px');

		this.jcrop.release();
		this.jcrop.destroy();

		var handle = this;
		this.jcrop = $.Jcrop('#cropSrc' ,{
			onChange: function(crds){
				handle.showPreview(crds , handle);
			},
			onSelect: function(crds){
				handle.showPreview(crds , handle);
			},
			aspectRatio: (handle.extList[type][0] / handle.extList[type][1])
		});

        this.preview.updateLayout();
	},
	showPreview:function(cds , handle){

		if (parseInt(cds.w) <= 0){
			return;
		}

		handle.coords.x = cds.x;
		handle.coords.y = cds.y;
		handle.coords.w =cds.w;
		handle.coords.h = cds.h;

		var curImg = handle.comboFld.getValue();

		var rx = handle.extList[curImg][0] / cds.w;
		var ry = handle.extList[curImg][1] / cds.h;

		jQuery('#cropThumb').css({
			width: Math.round(rx * ($('#cropSrc').attr('width'))) + 'px',
			height: Math.round(ry * ($('#cropSrc').attr('height'))) + 'px',
			marginLeft: '-' + Math.round(rx * cds.x) + 'px',
			marginTop: '-' + Math.round(ry * cds.y) + 'px'
		});
	},
	crop:function(){
		var handle = this;
		Ext.Ajax.request({
			url: app.admin + app.delimiter + 'medialib' +  app.delimiter + 'crop',
			method: 'post',
			waitMsg:appLang.SAVING,
			params:{
				'type':this.comboFld.getValue(),
				'id':this.dataRec.get('id'),
				'x':this.coords.x,
				'y':this.coords.y,
				'w':this.coords.w,
				'h':this.coords.h
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){

					var curImg = handle.comboFld.getValue();
					var src = handle.getImagePath(handle.dataRec , curImg);
					$('#oldImage').attr('src' , src);
					handle.fireEvent('dataSaved');
				}else{
					Ext.MessageBox.alert(appLang.MESSAGE,response.msg);
				}
			}
		});
	},
	initComponent:function()
	{

		this.coords = {x:0,y:0,w:0,h:0};
		this.extList = app.mediaConfig.image.sizes;
		var imgSrc = this.dataRec.get('path');

		var cbItems = [];

		this.extList = app.mediaConfig.image.sizes;
		var lastSize = '';
        var firstSize ='';
		for (i in this.extList )
		{
			if(typeof i == 'function'){
				continue;
			}

            if(!firstSize.length){
                firstSize = i;
            }
			cbItems.push({
				title:i+ ' ('+this.extList[i][0]+'x'+this.extList[i][1]+')' ,
				id:i
			});

			lastSize = i;
		}



		this.comboFld = Ext.create('Ext.form.field.ComboBox',{
			remote:false,
			allowBlank:false,
			queryMode:"local",
			forceSelection:true,
			triggerAction:"all",
			valueField:"id",
			displayField:'title',
			value:firstSize,
			store:Ext.create('Ext.data.Store',{
				model:'app.comboStringModel',
				data:cbItems
			})
		});

		var north = Ext.create('Ext.Panel',{
			layout:'fit',
			title:appLang.IMAGE_SIZE,
			region:'north',
			items:[this.comboFld]
		});


		var curImg = this.comboFld.getValue();
		this.preview = Ext.create('Ext.Panel',{
			layout:'vbox',
			region:'center',
			bodyCls:'formBody',
			title:appLang.PREVIEW,
            autoScroll:true,
			defaults:{
				border:false,
				bodyCls:'formBody'
			},
			items:[
				{html:appLang.OLD_IMAGE, width:100},
				{bodyCls:'formBody',html:'<div id="oldContainer" style="width:'+this.extList[curImg][0]+'px;height:'+this.extList[curImg][1]+'px;overflow:hidden;"><img src="'+this.getImagePath(this.dataRec , curImg)+'" id="oldImage"  style="border:1px solid #000" /></div>'},
				{html:appLang.NEW_IMAGE, width:100},
				{html:'<div id="cropContainer" style="border:1px solid #000000; width:'+this.extList[curImg][0]+'px;height:'+this.extList[curImg][1]+'px;overflow:hidden;"><img src="'+imgSrc+'" id="cropThumb"  style="border:1px solid #000" /></div>'}
			]

		});

		this.centerRegion = Ext.create('Ext.Panel',{
			region:'center',
			scrollable:true,
			xtype:'panel',
			frame:true,
			html:'<img src="'+imgSrc+'" id="cropSrc"  style="border:1px solid #000" />'
		});

		this.items=[
			this.centerRegion ,
			{
				region:'east',
				xtype:'panel',
				split:true,
				frame:false,
				layout:'border',
				width:255,
				items:[
					north ,
					this.preview
				]
			}
		];

		this.buttons=[
			{
				text:appLang.CROP,
				listeners:{
					click:{
						fn:function(){

							if( this.coords.w < 0){
								Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_SELECT_CROP_REGION);
								return false;
							}
							this.crop();
						},
						scope:this
					}
				}
			},
			{
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


		this.comboFld.on('select' , function(combo , value , options){
			this.setType(combo.getValue());
		},this);

		this.on('show',function(){

			var handle = this;

			setTimeout(function(){
				handle.jcrop = $.Jcrop('#cropSrc' ,{
					onChange: function(crds){
						handle.showPreview(crds , handle);
					},
					onSelect: function(crds){
						handle.showPreview(crds , handle);
					},
					aspectRatio: (handle.extList[curImg][0] / handle.extList[curImg][1])
				});
			} , 1000);

		},this);

		this.callParent(arguments);

		this.on('show',function(){app.checkSize(this);});
	},
	/**
	 * Create image url by image data and type
	 * @param {app.medialibModel} imageRecord
	 * @param string type
	 * @return string
	 */
	getImagePath: function(imageRecord , type){
		var date = new Date();
		return imageRecord.get('srcpath')+'-'+type+imageRecord.get('ext')+'?d='+Ext.Date.format(date,'ymdhis');
	}
});

