/*!
 * Component Column
 * Version 1.1
 * Copyright(c) 2011-2013 Skirtle's Den
 * License: http://skirtlesden.com/ux/component-column
 */
Ext.define('Skirtle.grid.column.Component', {
    alias: 'widget.componentcolumn',
    extend: 'Ext.grid.column.Column',
    requires: ['Skirtle.CTemplate'],

    // Whether or not to automatically resize the components when the column resizes
    autoWidthComponents: true,

    // Whether or not to destroy components when they are removed from the DOM
    componentGC: true,

    // Override the superclass - this must always be true or odd things happen, especially in IE
    hasCustomRenderer: true,

    // The estimated size of the cell frame. This is updated once there is a cell where it can be measured
    lastFrameWidth: 12,

    /* Defer durations for updating the component width when a column resizes. Required when a component has an animated
     * resize that causes the scrollbar to appear/disappear. Otherwise the animated component can end up the wrong size.
     *
     * For ExtJS 4.0 both delays are required. For 4.1 just having the 10ms delay seems to be sufficient.
     */
    widthUpdateDelay: [10, 400],

    constructor: function(cfg) {
        var me = this;

        me.callParent(arguments);

        // Array of component ids for both component queries and GC
        me.compIds = [];

        // We need a dataIndex, even if it doesn't correspond to a real field
        me.dataIndex = me.dataIndex || Ext.id(null, 'cc-dataIndex-');

        me.tpl = me.createTemplate(me.tpl);
        me.renderer = me.createRenderer(me.renderer);

        me.registerColumnListeners();
    },

    addRefOwner: function(child) {
        var me = this,
            fn = me.refOwnerFn || (me.refOwnerFn = function() {
                return me;
            });

        if (me.extVersion < 40200) {
            // Component queries for ancestors use getBubbleTarget in 4.1 ...
            child.getBubbleTarget = fn;
        }
        else {
            // ... and getRefOwner in 4.2+
            child.getRefOwner = fn;
        }
    },

    applyTemplate: function(data, value) {
        if (Ext.isDefined(value)) {
            data[this.dataIndex] = value;
        }

        return this.tpl.apply(data);
    },

    /* In IE setting the innerHTML will destroy the nodes for the previous content. If we try to reuse components it
     * will fail as their DOM nodes will have been torn apart. To defend against this we must remove the components
     * from the DOM just before the grid view is refreshed.
     */
    beforeViewRefresh: function() {
        if (Ext.isIE) {
            var ids = this.compIds,
                index = 0,
                len = ids.length,
                item,
                el,
                parentEl;

            for ( ; index < len ; index++) {
                if ((item = Ext.getCmp(ids[index])) && (el = item.getEl()) && (el = el.dom) && (parentEl = el.parentNode)) {
                    parentEl.removeChild(el);
                }
            }
        }
    },

    calculateFrameWidth: function(component) {
        var el = component.getEl(),
            parentDiv = el && el.parent(),
            // By default the TD has no padding but it is quite common to add some via a tdCls
            parentTd = parentDiv && parentDiv.parent();

        if (parentTd) {
            // Cache the frame width so that it can be used as a 'best guess' in cases where we don't have the elements
            return this.lastFrameWidth = parentDiv.getFrameWidth('lr') + parentTd.getFrameWidth('lr');
        }
    },

    createRenderer: function(renderer) {
        var me = this;

        return function(value, p, record) {
            var data = Ext.apply({}, record.data, record.getAssociatedData());

            if (renderer) {
                // Scope must be this, not me
                value = renderer.apply(this, arguments);
            }

            // Process the value even with no renderer defined as the record may contain a component config
            value = me.processValue(value);

            return me.applyTemplate(data, value);
        };
    },

    createTemplate: function(tpl) {
        return tpl && tpl.isTemplate
            ? tpl
            : Ext.create('Skirtle.CTemplate', tpl || ['{', this.dataIndex ,'}']);
    },

    destroyChild: function(child) {
        child.destroy();
    },

    getRefItems: function(deep) {
        var items = this.callParent([deep]),
            ids = this.compIds,
            index = 0,
            len = ids.length,
            item;

        for ( ; index < len ; index++) {
            if (item = Ext.getCmp(ids[index])) {
                items.push(item);

                if (deep && item.getRefItems) {
                    items.push.apply(items, item.getRefItems(true));
                }
            }
        }

        return items;
    },

    onChildAfterRender: function(child) {
        this.resizeChild(child);
    },

    onChildBoxReady: function(child) {
        // Pass false to avoid triggering deferred resize, the afterrender listener will already cover those cases
        this.resizeChild(child, false);
    },

    onChildDestroy: function(child) {
        Ext.Array.remove(this.compIds, child.getId());
    },

    onChildResize: function() {
        this.redoScrollbars();
    },

    onColumnResize: function(column) {
        column.resizeAll();
    },

    onColumnShow: function(column) {
        column.resizeAll();
    },

    // This is called in IE 6/7 as the components can still be seen even when a column is hidden
    onColumnVisibilityChange: function(column) {
        var items = column.getRefItems(),
            index = 0,
            length = items.length,
            visible = !column.isHidden();

        // In practice this probably won't help but it shouldn't hurt either
        Ext.suspendLayouts && Ext.suspendLayouts();

        for ( ; index < length ; ++index) {
            items[index].setVisible(visible);
        }

        Ext.resumeLayouts && Ext.resumeLayouts(true);
    },

    onDestroy: function() {
        Ext.destroy(this.getRefItems());

        this.callParent();
    },

    // Override
    onRender: function() {
        this.registerViewListeners();
        this.callParent(arguments);
    },

    // View has changed, may be a full refresh or just a single row
    onViewChange: function() {
        var me = this,
            tpl = me.tpl;

        // Batch the resizing of child components until after they've all been injected
        me.suspendResizing();

        if (tpl.isCTemplate) {
            // No need to wait for the polling, the sooner we inject the less painful it is
            tpl.injectComponents();

            // If the template picked up other components in the data we can just ignore them, they're not for us
            tpl.reset();
        }

        // A view change could mean scrollbar problems. Note this won't actually do anything till we call resumeResizing
        me.redoScrollbars();

        me.resumeResizing();
        
        me.performGC();
    },

    // Component GC, try to stop components leaking
    performGC: function() {
        var compIds = this.compIds,
            index = compIds.length - 1,
            comp,
            el;

        for ( ; index >= 0 ; --index) {
            // Could just assume that the component id is the el id but that seems risky
            comp = Ext.getCmp(compIds[index]);
            el = comp && comp.getEl();

            if (!el || (this.componentGC && (!el.dom || Ext.getDom(Ext.id(el)) !== el.dom))) {
                // The component is no longer in the DOM
                if (comp && !comp.isDestroyed) {
                    comp.destroy();
                }
            }
        }
    },

    processValue: function(value) {
        var me = this,
            compIds = me.compIds,
            id, initialWidth, dom, parent;

        if (Ext.isObject(value) && !value.isComponent && value.xtype) {
            // Do not default to a panel, not only would it be an odd default but it makes future enhancements trickier
            value = Ext.widget(value.xtype, value);
        }

        if (value && value.isComponent) {
            id = value.getId();

            // When the view is refreshed the renderer could return a component that's already in the list
            if (!Ext.Array.contains(compIds, id)) {
                compIds.push(id);
            }

            me.addRefOwner(value);
            me.registerListeners(value);

            if (value.rendered) {
                /* This is only necessary in IE because it is just another manifestation of the innerHTML problems.
                 * The problem occurs when a record value is changed and the components in that same row are being
                 * reused. The view doesn't go through a full refresh, instead it performs a quick update on just the
                 * one row. Unfortunately this nukes the existing components so we need to remove them first.
                 */
                if (Ext.isIE) {
                    // TODO: Should this be promoted to CTemplate?
                    dom = value.el.dom;
                    parent = dom.parentNode;

                    if (parent) {
                        if (me.extVersion === 40101) {
                            // Workaround for the bugs in Element.syncContent - p tag matches CTemplate.cTpl
                            Ext.core.DomHelper.insertBefore(dom, {tag: 'p'});
                        }

                        // TODO: Removing the element like this could fall foul of Element GC
                        parent.removeChild(dom);
                    }
                }
            }
            else if (me.autoWidthComponents) {
                /* Set the width to a 'best guess' before the component is rendered to ensure that the component's
                 * layout is using a configured width and not natural width. This avoids problems with 4.1.1 where
                 * subsequent calls to setWidth are ignored because it believes the width is already correct but only
                 * the outermost element is actually sized correctly. We could use an arbitrary width but instead we
                 * make a reasonable guess at what the actual width will be to try to avoid extra resizing.
                 */
                initialWidth = me.getWidth() - me.lastFrameWidth;

                // Impose a minimum width of 4, we really don't want negatives values or NaN slipping through
                initialWidth = initialWidth > 4 ? initialWidth : 4;

                value.setWidth(initialWidth);
            }

            // Part of the same IE 6/7 hack as onColumnVisibilityChange
            if ((Ext.isIE6 || Ext.isIE7) && me.isHidden()) {
                value.hide();
            }
        }

        return value;
    },

    redoScrollbars: function() {
        var me = this,
            grid = me.up('tablepanel');

        if (grid) {
            // The presence of a resizeQueue signifies that we are currently suspended
            if (me.resizeQueue) {
                me.redoScrollbarsRequired = true;
                return;
            }

            // After components are injected the need for a grid scrollbar may need redetermining
            if (me.extVersion < 40100) {
                // 4.0
                grid.invalidateScroller();
                grid.determineScrollbars();
            }
            else {
                // 4.1+
                grid.doLayout();
            }
        }
    },

    registerColumnListeners: function() {
        var me = this;

        if (me.autoWidthComponents) {
            // Need to resize children when the column resizes
            me.on('resize', me.onColumnResize);

            // Need to resize children when the column is shown as they can't be resized correctly while it is hidden
            me.on('show', me.onColumnShow);
        }

        if (Ext.isIE6 || Ext.isIE7) {
            me.on({
                hide: me.onColumnVisibilityChange,
                show: me.onColumnVisibilityChange
            });
        }
    },

    registerListeners: function(component) {
        var me = this;

        // Remove the component from the child list when it is destroyed
        component.on('destroy', me.onChildDestroy, me);

        if (me.autoWidthComponents) {
            // Need to resize children after render as some components (e.g. comboboxes) get it wrong otherwise
            component.on('afterrender', me.onChildAfterRender, me, {single: true});

            // With 4.1 boxready gives more reliable results than afterrender as it occurs after the initial sizing
            if (!me.extVersion < 40100) {
                component.on('boxready', me.onChildBoxReady, me, {single: true});
            }
        }

        // Need to redo scrollbars when a child resizes
        component.on('resize', me.onChildResize, me);
    },

    registerViewListeners: function() {
        var me = this,
            view = me.up('tablepanel').getView();

        me.mon(view, 'beforerefresh', me.beforeViewRefresh, me);
        me.mon(view, 'refresh', me.onViewChange, me);
        me.mon(view, 'itemupdate', me.onViewChange, me);
        me.mon(view, 'itemadd', me.onViewChange, me);
        me.mon(view, 'itemremove', me.onViewChange, me);
    },

    resizeAll: function() {
        var me = this;

        me.suspendResizing();
        me.resizeQueue = me.getRefItems();
        me.resumeResizing();
    },

    resizeChild: function(component, defer) {
        var me = this,
            frameWidth,
            newWidth,
            oldWidth,
            resizeQueue;

        if (me.resizingSuspended) {
            resizeQueue = me.resizeQueue;

            if (!Ext.Array.contains(resizeQueue, component)) {
                resizeQueue.push(component);
            }

            return;
        }

        frameWidth = me.calculateFrameWidth(component);

        // TODO: Should we destroy the component here if it doesn't have a parent element? Already picked up anyway?
        if (Ext.isNumber(frameWidth)) {
            newWidth = me.getWidth() - frameWidth;
            oldWidth = component.getWidth();

            // Returns true if a resize actually happened
            if (me.setChildWidth(component, newWidth, oldWidth)) {
                // Avoid an infinite resizing loop, deferring will only happen once
                if (defer !== false) {
                    // Do the sizing again after a delay. This is because child panel collapse animations undo our sizing
                    Ext.each(me.widthUpdateDelay, function(delay) {
                        Ext.defer(me.resizeChild, delay, me, [component, false]);
                    });
                }
            }
        }
    },

    resumeResizing: function() {
        var me = this,
            index = 0,
            resizeQueue = me.resizeQueue,
            len = resizeQueue.length;

        if (!--me.resizingSuspended) {
            for ( ; index < len ; ++index) {
                me.resizeChild(resizeQueue[index]);
            }

            me.resizeQueue = null;

            if (me.redoScrollbarsRequired) {
                me.redoScrollbars();
            }
        }
    },

    setChildWidth: function(component, newWidth, oldWidth) {
        if (oldWidth === newWidth) {
            return false;
        }

        component.setWidth(newWidth);

        return true;
    },

    suspendResizing: function() {
        var me = this;

        me.resizingSuspended = (me.resizingSuspended || 0) + 1;

        if (!me.resizeQueue) {
            me.resizeQueue = [];
        }
    }
}, function(cls) {
    var proto = cls.prototype,
        version = Ext.getVersion();

    // ExtJS version detection
    proto.extVersion = (version.getMajor() * 100 + version.getMinor()) * 100 + version.getPatch();

    // 4.1.1 initially reported its version as 4.1.0
    if (Ext.Element.prototype.syncContent && version.toString() === '4.1.0') {
        proto.extVersion = 40101;
    }
});