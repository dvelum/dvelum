/**
 * Properties panel for Model object
 */
Ext.define('designer.properties.Model',{
	extend:'designer.properties.Panel',
	
	controllerUrl:null,	
	initComponent:function()
	{
		this.tbar = [{
			iconCls:'fieldIcon',
			tooltip:desLang.fields,
			scope:this,
			handler:function(){
				this.showColumnsWindow(0);
			}
		}];


		var returnDots = function(v){return '...';};
		this.sourceConfig = Ext.apply({	
			'fields':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:function(){
								this.showColumnsWindow(0);
							},
							scope:this
						}
					}
				}),
				renderer:returnDots
			},
			'associations':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:function(){
								this.showColumnsWindow(1);
							},
							scope:this
						}
					}
				}),
				renderer:returnDots
			},
			'validators':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:function(){
								this.showColumnsWindow(2);
							},
							scope:this
						}
					}
				}),
				renderer:returnDots
			}			
		},this.sourceConfig);	
		this.callParent();
	},
	
	showColumnsWindow:function(activeTab){
		var source = this.dataGrid.getSource();
		var associations = [];
		var fields = [];
		var validators = [];
		if(source.associations.length){
			associations = Ext.JSON.decode(source.associations);
		}
		if(source.fields.length){
			fields = Ext.JSON.decode(source.fields);
		}
		if(source.validators.length){
			validators = Ext.JSON.decode(source.validators);
		}
		var win = Ext.create('designer.model.configWindow',{
        	objectName : this.objectName,
        	controllerUrl:this.controllerUrl,
        	activeTab:activeTab,
        	initFields:fields,
        	initAssociations:associations,
        	initValidators:validators
        });
        win.on('dataChanged',function(fields, associations, validators){
        	//this.dataGrid.setProperty('fields', fields, true);
        	this.dataGrid.setProperty('associations', associations, true);
        	this.dataGrid.setProperty('validators', validators, true);
        },this);
        win.show();
	}
});