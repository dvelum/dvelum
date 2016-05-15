Ext.define('app.medialib.ItemField', {
	
	extend:'Ext.form.field.Display', 
	
	alias:'widget.medialibitemfield',
	
	isFormField :true,
	
	submitValue:true,
		
	selectButton:null,
	resetItemBtn:null,
	
	resourceType:'all',
	/**
     * @private
     * Override. Treat undefined and null values as equal to an empty string value.
     */
    isEqual: function(value1, value2) {
        return this.isEqualAsString(value1, value2);
    },    
    
    fieldSubTpl: [
                  '<div id="{id}">',
                  '<div id="{id}-icon"></div>', 
                  '<div id="{id}-description"></div>', 
                  '<div><div id="{id}-sibtn" style="float:left;"></div><div style="float:left;" id="{id}-ribtn"></div></div>', 
                  '</div>',
                  {
                      compiled: true,
                      disableFormats: true
                  }
              ],
    
    controllerUrl:null, 
    
	onRender:function(){
		this.callParent(arguments);
		this.controllerUrl = app.createUrl([app.admin ,app.medialibControllerName,'info']);
		
		this.selectButton = Ext.create('Ext.Button',{
			renderTo:this.getId() + '-inputEl-sibtn',
			iconCls:'editIcon2',
			scope:this,
			text:appLang.SELECT,
			handler:this.selectItem
		});
		
		this.resetItemBtn = Ext.create('Ext.Button',{
			renderTo:this.getId() + '-inputEl-ribtn',
			iconCls:'deleteIcon',
			scope:this,
			tooltip:appLang.RESET,
			handler:this.resetItem
		});
		
		this.imageField = Ext.create('app.ImageField',{
			renderTo:this.getId() + '-inputEl-icon'
		});
		
		this.descriptionField = Ext.create('Ext.form.field.Display',{
			renderTo:this.getId() + '-inputEl-description'
		});
	},
	resetItem:function(){
		this.setRawValue(0);
	},
	setRawValue: function(value)
	{
	    var me = this;
	    me.rawValue = value;

	    if (me.rendered) {
	    	this.loadInfo();
	    }else{
	    	me.on('render',me.loadInfo,me);
	    }
	    return value;
	},
	setValue:function(value){
		this.setRawValue(value);
	},
	getValue:function(){
		return this.rawValue;
	},
	/**
	 * Load item info
	 */
	loadInfo:function()
	{
		if(Ext.isEmpty(this.rawValue)){
			return;
		}

		Ext.Ajax.request({
			url: app.createUrl([app.admin,app.medialibControllerName,'info']),
			method: 'post',
			scope:this,
			params:{
				id: this.rawValue
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(response.success){
					this.setInfo(response.data);		 
				}
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
			}
		});
	},
	/**
	 * Set item info
	 * @param {object} data
	 *  exists  boolean -  item exists
	 *  type string - item type
	 *  icon string - item icon url
	 *  title string - item title
	 *  size string
	 */
	setInfo:function(data)
	{
		me = this;
		if(data.exists){
			this.imageField.setValue(data.icon);
			this.descriptionField.setValue(
					'<b>' +appLang.TITLE + ':</b> ' + data.title + '<br>' +
					'<b>' +appLang.SIZE + ':</b> ' + data.size + '<br>' +
					'<b>' +appLang.TYPE + ':</b> ' + data.type
			);
		}else{
			this.imageField.setValue(app.wwwRoot + 'i/system/empty.gif');
			this.descriptionField.setValue('');
		}
		//me.inputEl.dom.innerHTML = 'someValue';
	},
	/**
	 * Show medialibrary window
	 */
	selectItem:function(){
		 var win = Ext.create('app.selectMediaItemWindow',{
			  actionType:'selectId',
			  resourceType:this.resourceType
		  });
		  
		  win.on('itemSelected' , function(record){
			  if(Ext.isArray(record)){
				  record = record[0];
			  }
			  this.setValue(record.get('id'));
			  win.close();
		  },this);
		  win.show();
	}
});