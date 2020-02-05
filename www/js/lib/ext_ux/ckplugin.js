Ext.define('Ext.ux.CKeditor', {
	extend : 'Ext.form.field.TextArea',
	alias : 'widget.ckeditor',
	initCkValue:false,
	initComponent : function() {
		this.callParent();
				
		this.on('afterrender', function(){			
			Ext.apply(this.CKConfig, {
				height : this.getHeight()
			});			
			this.editor = CKEDITOR.replace(this.inputEl.id, this.CKConfig);
			this.editorId = this.editor.id;
			if(this.initCkValue){
				this.setValue(this.initCkValue);
			}
		}, this);
		
		this.on('beforedestroy',function(){
			this.editor.destroy();
			this.editor = null;
		});
	},
	onRender : function(ct, position) {
		if (!this.el) {
			this.defaultAutoCreate = {
				tag : 'textarea',
				autocomplete : 'off'
			};
		}
		this.callParent(arguments);
	},
	setValue : function(value) {
		var me = this;
		this.callParent(arguments);
		if (this.editor) {
		  
		  Ext.Function.defer(function(){
		      me.editor.setData(value);
		  }, 800);
		}else{
			this.initCkValue = value;
		}
	},
	getRawValue : function() {
		if(this.editor){
			return this.editor.getData();
		}else{
			if(this.initCkValue)
				return this.initCkValue;
			else
				return '';
		}
	},
	getValue:function(){
		return this.getRawValue();
	},
	getEditor:function(){
		return this.editor;
	}
});


CKEDITOR.on('instanceReady', function(e) {
	var o = Ext.ComponentQuery.query('ckeditor[editorId="' + e.editor.id + '"]'),
	comp = o[0];
	e.editor.resize(comp.getWidth(), comp.getHeight());
	comp.on('resize', function(c, adjWidth, adjHeight) {
		c.editor.resize(adjWidth, adjHeight);
	});
});