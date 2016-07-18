/**
 * DVelum project http://code.google.com/p/dvelum/ , dvelum.net
 *
 * @author Nicolas Berezin 2012
 */

Ext.define('app.crud.orm.ObjectsMapWindow', {
	extend: 'Ext.window.Window',

	maximizable: true,
	controllerUrl: '',
	plain: true,
	layout: 'fit',
	scrollable: true,
	maximized: true,
	modal: true,
	width: app.checkWidth(700),
	height: app.checkHeight(500),
	title: appLang.SHOW_OBJECTS_MAP,
	uml: null,
	umlDefaultOptions: {
		placeholder: 'diagram',
		stateColorFill: '90-#333-#5C95BE:1-#9BC7E3',
		renderOnResize: false
	},
	umlOptions: {},
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
		if(this.canEdit)
		{
				this.tbar = [
				  {
					  text:appLang.SAVE,
					  iconCls:'saveIcon',
					  handler:this.saveMap,
					  scope:this
				  }          
				];
		}
		
		this.items = [this.drawEl];
		
		this.callParent();
		this.createUml();
	},

	/**
	 * Create UML object
	 */
	createUml: function() {

		this.umlOptions = Ext.apply(this.umlDefaultOptions, this.umlOptions);

		if (this.umlOptions.renderOnResize) {
			this.on('resize', function() {
				this.renderUML();
			}, this);
		}
		this.uml = Joint.dia.uml;
		this.loadData();
	},
	/**
	 * Render Map
	 */
	renderUML: function() {
		/**
		 * Create UML state elements
		 */
		if (typeof(this.umlData) != 'undefined') {
			this.allItems = {};

			var itemWidth = 120,	
				paperWidth = this.mapWidth,
				paperHeight = this.mapHeight;
				
						
			/**
			 * Set UML viewport
			 */
			Joint.paper(this.umlOptions.placeholder, paperWidth, paperHeight);
			
			Ext.Object.each(this.umlData , function(index , item){
								
				var objectFields = [];
				for (var i = 0, len = item.fields.length; i < len; i++) {
					objectFields.push((i+1));
					objectFields.push(item.fields[i]);
				}
				var elemHeight = (12 * i) + 30;		
				this.allItems[index] = this.createUmlState(item.position, itemWidth, elemHeight, index, objectFields);
				this.allItems[index].isRendered = true;					
			},this);
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
	createUmlState: function(position, itemWidth, itemHeight, title, objectFields) {
		return this.uml.State.create({
			rect: {
				x: position.x,
				y: position.y,
				width: itemWidth,
				height: itemHeight
			},
			label: title,
			attrs: {
				fill: this.umlOptions.stateColorFill
			},
			actions: {
				inner: objectFields
			}
		}).toggleGhosting();
	},
	
    /**
     * Add object references
     */
	linkUmlStates: function() {
		if (!this.allIsRendered)
			for (var umlItem in this.umlData) {
				for (var umlItemLink in this.umlData[umlItem].links) {
					var linkLabel = [];
					for (var umlItemLinkLabel in this.umlData[umlItem].links[umlItemLink]) {
						if (this.umlData[umlItem].links[umlItemLink][umlItemLinkLabel] == 'object' || this.umlData[umlItem].links[umlItemLink][umlItemLinkLabel] == 'multi') {
							linkLabel.push(umlItemLinkLabel);
						}
					}
					if (typeof this.allItems[umlItemLink] != 'undefined') this.allItems[umlItem].joint(this.allItems[umlItemLink], this.uml.arrow).label(linkLabel.join(', ')); //.register(this.allItems)
				}
			}

		this.allIsRendered = true;
	},
	/**
	 * Load map data
	 */
	loadData: function() {
		Ext.Ajax.request({
			url: this.controllerUrl + 'getumldata',
			method: 'post',
			scope: this,
			success: function(response, request) {
				response = Ext.JSON.decode(response.responseText);

				if (!response.success) {
					Ext.Msg.alert(appLang.MESSAGE, response.msg);
				} else {
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
			var x,y;

			x = item.properties.rect.x + item.properties.dx;
			y = item.properties.rect.y + item.properties.dy;
			
			map[index] = {'x':x,'y':y};
		},this);
		
		this.getEl().mask(appLang.SAVING);
		
		Ext.Ajax.request({
			url: this.controllerUrl + 'saveumlmap',
			method: 'post',
			scope: this,
			params:{map:Ext.JSON.encode(map)},
			success: function(response, request) {				
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