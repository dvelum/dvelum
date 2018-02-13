/**
 * Properties panel for Grid object
 */
Ext.define('designer.properties.Grid',{
	extend:'designer.properties.Panel',
	layout:'accordion',
	advancedForm: null,

	initComponent:function(){
		var me = this;
		this.mainConfigTitle = desLang.properties;

		this.tbar = [{
			iconCls:'gridIcon',
			tooltip:desLang.columns,
			scope:me,
			handler:me.showColumnsWindow
		}];

		this.sourceConfig = Ext.apply({
			'columns':{
				editor:Ext.create('Ext.form.field.Text',{
					listeners:{
						focus:{
							fn:this.showColumnsWindow,
							scope:this
						}
					}
				}),
				renderer:function(v){return '...';}
			}
		});


		this.advancedForm = Ext.create('Ext.form.Panel',{
			bodyCls:'formBody',
			title:desLang.advancedOptions,
			defaults:{
				margin:'3 3 3 3'
			},
			fieldDefaults:{
				labelWidth:150
			},
			frame:true,
			border:false,
			scrollable:true,
			items:[
				{
					xtype:'fieldset',
					title:desLang.grouping,
					checkboxName:'grouping',
					checkboxToggle:true,
					collapsed:true,
					items:[
						{
							name:'groupsummary',
							fieldLabel:desLang.useGroupSummary,
							xtype:'checkbox',
							value:1,
							uncheckedValue:0
						},{
							name:'startCollapsed',
							fieldLabel:desLang.startCollapsed,
							xtype:'checkbox',
							value:1,
							uncheckedValue:0
						},{
							name:'hideGroupedHeader',
							fieldLabel:desLang.hideGroupedHeader,
							xtype:'checkbox',
							value:1,
							uncheckedValue:0
						},{
							name:'enableGroupingMenu',
							fieldLabel:desLang.enableGroupingMenu,
							xtype:'checkbox',
							value:1,
							uncheckedValue:0
						},{
							name:'groupHeaderTpl',
							fieldLabel:'groupHeaderTpl',
							xtype:'textfield',
							anchor:'100%'
						},{
							name:'remoteRoot',
							fieldLabel:'remoteRoot',
							xtype:'textfield',
							anchor:'100%'
						}
					]
				},{
					xtype:'fieldset',
					checkboxName:'editable',
					title:desLang.editable,
					checkboxToggle:true,
					collapsed:true,
					items:[
						{
							fieldLabel:desLang.clicksToEdit,
							name:'clicksToEdit',
							xtype:'numberfield',
							value:1,
							minValue:1,
							maxValue:2,
							width:190,
							allowDecimals:false
						}
					]
				},{
					xtype:'fieldset',
					checkboxName:'rowexpander',
					title:desLang.rowExpander,
					checkboxToggle:true,
					collapsed:true,
					items:[
						{
							fieldLabel:desLang.expanderRowBodyTpl,
							name:'expander_rowbodytpl',
							xtype:'textfield',
							anchor:'100%'
						}
					]
				},{
					name:'summary',
					fieldLabel:desLang.useSummary,
					margin:'0 0 0 15',
					xtype:'checkbox'
				},{
					fieldLabel:desLang.paging,
					name:'paging',
					margin:'0 0 0 15',
					xtype:'checkbox'
				},{
					name:'checkboxSelection',
					fieldLabel:desLang.checkboxSelection,
					xtype:'checkbox',
					margin:'0 0 0 15'
				},{
					name:'numberedRows',
					fieldLabel:desLang.numberedRows,
					xtype:'checkbox',
					margin:'0 0 0 15'
				}/*,{
					name:'filtersFeature',
					iconCls:'filtersIcon',
					text:desLang.filtersFeature,
					xtype:'button',
					margin:'0 0 0 15',
					handler:this.showFiltersWindow,
					scope:this
				}*/

			],
			buttons:[
				{
					text:desLang.save,
					handler:this.saveAdvancedProperties,
					scope:this
				}
			]
		});

		this.callParent();

		this.add(this.advancedForm);

		this.advancedForm.load({
			url:app.createUrl([designer.controllerUrl ,'grid','loadadvanced']),
			params:{object:this.objectName}
		});
	},
	showFiltersWindow:function(){
		Ext.create('designer.grid.filters.Window',{
			objectName : this.objectName,
			listeners:{
				dataSaved:{
					fn:function(){
						this.fireEvent('dataSaved');
					},
					scope:this
				}
			}
		}).show();
	},
	showColumnsWindow:function(){
		var win = Ext.create('designer.grid.column.Window',{
			title:desLang.gridColumnsConfig,
			objectName : this.objectName
		});
		win.show();
	},
	saveAdvancedProperties:function(){
		var handle = this;
		this.advancedForm.getForm().submit({
			clientValidation: true,
			waitMsg: appLang.SAVING,
			method:'post',
			url:app.createUrl([designer.controllerUrl ,'grid','setadvanced']),
			params:{object:this.objectName},
			success: function(form, action) {
				if(!action.result.success){
					Ext.Msg.alert(appLang.MESSAGE, action.result.msg);
				} else{
					handle.fireEvent('dataSaved');
				}
			},
			failure: app.formFailure
		});
	},
    destroy:function(){
		// Ext.each(this.getLayoutItems(),function(item){
		//     console.log(item);
		// 	item.destroy();
		// });
        this.advancedForm.destroy();
        this.callParent(arguments);
    }
});