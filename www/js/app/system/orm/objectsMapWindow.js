/**
 * DVelum project https://github.com/dvelum/dvelum , dvelum.net
 *
 * @author Nicolas Berezin 2012
 */

Ext.define('app.crud.orm.ObjectsMapWindow', {
    extend: 'Ext.window.Window',

    maximizable: true,
    controllerUrl: '',
    params: '',
    plain: true,
    layout: 'fit',
    scrollable: true,
    maximized: true,
    modal: true,
    width: app.checkWidth(700),
    height: app.checkHeight(500),
    title: appLang.SHOW_OBJECTS_MAP,
    uml: null,
    graph: null,
    paper: null,
    umlOptions: {renderOnResize: false, objWidth: 120},
    umlObjAttrs: {
        '.uml-state-body': {
            'stroke-width': 0.5, 'stroke': '#000', rx: 10, ry: 10,
            'fill': {
                type: 'linearGradient',
                stops: [
                    { offset: '0%', color: '#9BC7E3' },
                    { offset: '100%', color: '#5C95BE' }
                ],
                attrs: { x1: '0%', y1: '0%', x2: '0%', y2: '100%'}
            }
        },
        '.uml-state-separator': {
            'stroke-width': 1,
            stroke: '#000',
        },
        '.uml-state-name': {
            'font-size': 11,
            'font-family': 'Arial'
        },
        '.uml-state-events': {
            'font-size': 10,
            'font-family': 'Arial'
        }
    },
    umlData: null,
    drawEl: null,
    firstElement: null,
    allItems: {},
    allIsRendered: false,

    mapWidth:100,
    mapHeight:100,

    canEdit:false,


    initComponent: function(){
        // map element
        this.drawEl = Ext.create('Ext.Component', {
            html: '<div id="diagram"></div>',
            padding: 0,
            style: {
                color: '#000'
            }
        });

        // save button
        if(this.canEdit){
            this.tbar = [
                {
                    text:appLang.SAVE,
                    iconCls:'saveIcon',
                    handler:this.saveMap,
                    scope:this
                },{
                    text:appLang.FILTER_OBJECTS,
                    iconCls:'filterIcon',
                    handler:this.showSelectObjects,
                    scope:this
                }
            ];
        }

        this.items = [this.drawEl];

        this.callParent();
        this.uml = joint.shapes.uml;
        this.graph = new joint.dia.Graph();
        this.createUml();
    },

    /**
     * Select objects show window
     */
    showSelectObjects:function(){
        var me = this;
        var win = Ext.create('app.crud.orm.selectObjectsWindow',{});
        win.setData(app.crud.orm.getObjectsList(),Object.keys(this.umlData));
        win.on('objectsSelected',function(objects){
            me.params = {'objects[]': objects},
            me.loadData();
            win.close();
        });
        win.show();
    },

    /**
     * Create UML object
     */
    createUml: function(){
        if(this.umlOptions.renderOnResize){
            this.on('resize', function(){
                this.renderUML();
            },this);
        }
        this.loadData();
    },
    /**
     * Render Map
     */
    renderUML: function() {
        if(!this.paper)
            this.paper = new joint.dia.Paper({
                el: $('#diagram'),
                width: this.mapWidth,
                height: this.mapHeight,
                gridSize: 1,
                model: this.graph
            });

        /**
         * Create UML state elements
         */
        if(typeof(this.umlData) != 'undefined'){
            this.allItems = {};

            Ext.Object.each(this.umlData, function(index, item){
                var objectFields = [];
                for(var i = 0, len = item.fields.length; i < len; i++){
                    objectFields.push((i+1)+'. '+item.fields[i]);
                }
                var elemHeight = (10 * i) + 30;
                this.allItems[index] = this.createUmlState(item.position, this.umlOptions.objWidth, elemHeight, index, objectFields);
                this.allItems[index].isRendered = true;
                this.allItems[index].attr('rect/filter', {name: 'dropShadow', args: {dx: 2, dy: 2, blur: 3}});
            },this);

            this.graph.clear();
            _.each(this.allItems, function(c){
                this.graph.addCell(c);
            },this);
            this.allIsRendered = false;
            this.linkUmlStates();
        }
    },
    /**
     * Add Map Item
     * @param {object} position
     * @param integer itemWidth
     * @param integer itemHeight
     * @param string title
     * @param {object} objectFields
     */
    createUmlState: function(position, itemWidth, itemHeight, title, objectFields){
        var attrs = this.umlObjAttrs;
        attrs['.uml-state-body'].height = itemHeight;
        attrs['.uml-state-body'].width = itemWidth;

        return new this.uml.State({
            position: position,
            size: {width: itemWidth, height: itemHeight},
            name: title,
            events: objectFields,
            attrs: attrs
        });
    },

    /**
     * Add object references
     */
    linkUmlStates: function(){
        if(!this.allIsRendered)
            for(var umlItem in this.umlData){
            	this.allItems[umlItem].linksObj = {};
                for(var umlItemLink in this.umlData[umlItem].links){
                    var linkLabel = [];
                    for(var umlItemLinkLabel in this.umlData[umlItem].links[umlItemLink]){
                        if(
                            this.umlData[umlItem].links[umlItemLink][umlItemLinkLabel] == 'object'
                            || this.umlData[umlItem].links[umlItemLink][umlItemLinkLabel] == 'multi'
                        ){
                            linkLabel.push(umlItemLinkLabel);
                        }
                    }
                    if(typeof this.allItems[umlItemLink] != 'undefined'){
                        var link = new this.uml.Generalization({
                            source: this.allItems[umlItem],
                            target: this.allItems[umlItemLink],
                            labels: [{
                                position: 0.2, attrs: {
                                text:{
                                    text: linkLabel.join(', '),
                                    'font-size': 11
                                },
                            }}]
                        });
	                    if(typeof this.umlData[umlItem].savedlinks[umlItemLink] != 'undefined'){
							if(typeof this.umlData[umlItem].savedlinks[umlItemLink].vertices != 'undefined'){
	                    		var vertices = this.umlData[umlItem].savedlinks[umlItemLink].vertices;
								link.prop('vertices',vertices);
							}
	                    }
                        link.attr({
                            '.tool-remove': {'display': 'none'}, rect: {fill: '#fff'},
                            '.marker-target': { fill: 'black', stroke: 'black', d: 'M 10 0 L 0 5 L 10 10 z' }
                        });
                        if(umlItem == umlItemLink){
                            var obj = this.allItems[umlItem];
                            var objPos = obj.position();
                            var objSize = obj.prop('size');
                            link.prop({'vertices':[
                                {
                                    'x': objPos.x + objSize.width + 20,
                                    'y': objPos.y + Math.ceil(objSize.height / 2)
                                },
                                {
                                    'x': objPos.x + objSize.width + 20,
                                    'y': objPos.y + objSize.height + 20
                                },
                                {
                                    'x': objPos.x + Math.ceil(objSize.width / 2),
                                    'y': objPos.y + objSize.height + 20
                                }
                            ]});
                        }
                        link.addTo(this.graph);
                        this.allItems[umlItem].linksObj[umlItemLink] = link;
                    }
                }
            }

        this.allIsRendered = true;
    },
    /**
     * Load map data
     */
    loadData: function(){
        Ext.Ajax.request({
            url: this.controllerUrl + 'uml/loadMap',
            params: this.params,
            method: 'post',
            scope: this,
            success: function(response, request){
                response = Ext.JSON.decode(response.responseText);

                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }else{
                    this.umlData = response.data.items;
                    this.mapWidth = response.data.mapWidth;
                    this.mapHeight = response.data.mapHeight;
                    this.renderUML();
                }
            },
            failure: app.ajaxFailure
        });
    },
    /**
     * Save object coordinates
     */
    saveMap:function(){

        var map = {};
        var drawX = this.drawEl.getEl().getX();
        var drawY = this.drawEl.getEl().getY();

        Ext.Object.each(this.allItems, function(index, item){
            map[index] = item.get('position');
            map[index].links = {};
            Ext.Object.each(this.allItems[index].linksObj, function(target,link){
            	map[index].links[target] = {};
            	map[index].links[target]['vertices'] = link.prop('vertices');
            });
        },this);

        this.getEl().mask(appLang.SAVING);

        Ext.Ajax.request({
            url: this.controllerUrl + 'uml/saveMap',
            method: 'post',
            scope: this,
            params:{map:Ext.JSON.encode(map)},
            success: function(response, request){
                response = Ext.JSON.decode(response.responseText);
                if(!response.success){
                    Ext.Msg.alert(appLang.MESSAGE, response.msg);
                }
                this.getEl().unmask();
            },
            failure:function(){
                 this.getEl().unmask();
                 app.ajaxFailure(arguments);
            }
        });
    }
});