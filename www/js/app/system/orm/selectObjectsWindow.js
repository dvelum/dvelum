Ext.define('app.crud.orm.objectListModel', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'id', type: 'string'},
        {name: 'title',  type: 'string'},
    ]
});

Ext.define('app.crud.orm.selectObjectsWindow', {
    extend: 'Ext.window.Window',
    height: 300,
    width: 400,
    layout: 'fit',
    modal: true,
    title: appLang.FILTER_OBJECTS,

    initComponent: function(){
        var me = this;
        me.objSelectGrid = Ext.create('Ext.grid.Panel',{
            xtype:"grid",
            scrollable: true,
            store:Ext.create("Ext.data.Store",{
                model: 'app.crud.orm.objectListModel'
            }),
            columns: [
                { text: 'Title', dataIndex: 'title', flex: 1 },
            ],
            selModel:Ext.create("Ext.selection.CheckboxModel"),
        });
        me.fbar = [{
            text: appLang.APPLY,
            handler: me.selectDone,
            scope: this
        },{
            text: appLang.CANCEL,
            handler: me.close,
            scope: this
        }];
        me.dockedItems = [me.tBar];
        me.items = [me.objSelectGrid];
        me.callParent();
    },
    setData: function(items,showed){
    	this.objSelectGrid.getStore().setData(items);
        var sm = this.objSelectGrid.getSelectionModel();
		this.objSelectGrid.getStore().each(function(record){
			if(Ext.Array.contains(showed, record.get('id'))){
                sm.select(record, true);
			}
		});
    },
    selectDone: function(){
        var selected = this.objSelectGrid.getSelectionModel().getSelection();
        var data = [];
        Ext.each(selected,function(item){data.push(item.get('id'))});
        this.fireEventArgs('objectsSelected',[data]);
    }
});