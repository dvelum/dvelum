
Ext.ns('Ext.ux.grid');

/**
 * @class Ext.ux.grid.GridSummary
 * @extends Ext.util.Observable
 * A GridPanel plugin that enables dynamic column calculations and a dynamically
 * updated total summary row.
 */
Ext.ux.grid.GridSummary = Ext.extend(Ext.util.Observable, {
   /**
    * @cfg {String} position
    * The position where the summary row should be rendered (defaults to 'top').
    * The only other supported value is 'bottom'.
    */
    /**
     * @cfg {Number} scrollBarWidth
     * Configurable scrollbar width (used only in the event the Ext.getScrollBarWidth() method is not available)
     */
    scrollBarWidth : 17,

    constructor : function(config){
        Ext.apply(this, config);
        Ext.ux.grid.GridSummary.superclass.constructor.call(this);
    },
    init : function(grid) {
        this.grid = grid;
        var v = this.view = grid.getView();

        // override GridView's onLayout() method
        v.onLayout = this.onLayout;

        // IE6/7 disappearing vertical scrollbar workaround
        if (Ext.isIE6 || Ext.isIE7) {
            if (!grid.events['viewready']) {
                // check for "viewready" event on GridPanel -- this event is only available in Ext 3.x,
                // so the plugin hotwires it in if it doesn't exist
                v.afterMethod('afterRender', function() {
                    this.grid.fireEvent('viewready', this.grid);
                }, this);
            }

            // a small (hacky) delay of ~10ms is required to prevent
            // the vertical scrollbar from disappearing in IE6/7
            grid.on('viewready', function() {
                this.toggleGridHScroll(false);
            }, this, { delay: 10 });
        } else {
            v.afterMethod('render', this.toggleGridHScroll, this);
        }

        v.afterMethod('render', this.refreshSummary, this);
        v.afterMethod('refresh', this.refreshSummary, this);
        v.afterMethod('onColumnWidthUpdated', this.doWidth, this);
        v.afterMethod('onAllColumnWidthsUpdated', this.doAllWidths, this);
        v.afterMethod('onColumnHiddenUpdated', this.doHidden, this);

        if (Ext.isGecko || Ext.isOpera) {
            // restore gridview's horizontal scroll position when store data is changed
            //
            // TODO -- when sorting a column in Opera, the summary row's horizontal scroll position is
            //         synced with the gridview, but is displaced 1 vertical scrollbar width to the right
            v.afterMethod('onDataChange', this.restoreGridHScroll, this);
        }

        grid.on({
            bodyscroll      : this.syncSummaryScroll,
            beforedestroy   : this.beforeDestroy,
            scope           : this
        });

        // update summary row on store's add/remove/clear/update events
        grid.store.on({
            add     : this.refreshSummary,
            remove  : this.refreshSummary,
            clear   : this.refreshSummary,
            update  : this.refreshSummary,
            scope   : this
        });

        if (!this.rowTpl) {
            this.rowTpl = new Ext.Template(
                '<div class="x-grid3-summary-row x-grid3-gridsummary-row-offset">',
                    '<table class="x-grid3-summary-table" border="0" cellspacing="0" cellpadding="0" style="{tstyle}">',
                        '<tbody><tr>{cells}</tr></tbody>',
                    '</table>',
                '</div>'
            );
            this.rowTpl.disableFormats = true;
        }
        this.rowTpl.compile();

        if (!this.cellTpl) {
            this.cellTpl = new Ext.Template(
                '<td class="x-grid3-col x-grid3-cell x-grid3-td-{id} {css}" style="{style}">',
                    '<div class="x-grid3-cell-inner x-grid3-col-{id}" unselectable="on" {attr}>{value}</div>',
                "</td>"
            );
            this.cellTpl.disableFormats = true;
        }
        this.cellTpl.compile();
    },

    /**
     * @private
     * @param {Object} rs
     * @param {Object} cm
     */
    calculate : function(rs, cm) {
        var data = {}, r, cfg = cm.config, cf,
            i, len, cname, j, jlen;
        for (i = 0, len = cfg.length; i < len; i++) {
            cf = cfg[i]; // get column's configuration
            cname = cf.dataIndex; // get column dataIndex

            // initialise grid summary row data for
            // the current column being worked on
            data[cname] = 0;

            if (cf.summaryType) {
                for (j = 0, jlen = rs.length; j < jlen; j++) {
                    r = rs[j]; // get a single Record
                    data[cname] = Ext.ux.grid.GridSummary.Calculations[cf.summaryType](r.get(cname), r, cname, data, j);
                }
            }
        }
        return data;
    },

    // private
    onLayout : function(vw, vh) { // note: this method is scoped to the GridView
        if (typeof(vh) != 'number') { // handles grid's height:'auto' config
            return;
        }

        if (!this.grid.getGridEl().hasClass('x-grid-hide-gridsummary')) {
            // readjust gridview's height only if grid summary row is visible
            this.scroller.setHeight(vh - this.summaryWrap.getHeight());
        }
    },

    // private
    syncScroll : function(refEl, scrollEl, currX, currY) {
        currX = currX || refEl.scrollLeft;
        currY = currY || refEl.scrollTop;

        if (this.oldX != currX) { // only adjust horizontal scroll when horizontal scroll is detected
            scrollEl.scrollLeft = currX;
            scrollEl.scrollLeft = currX; // second time for IE (1/2 the time first call fails. other browsers simply ignore repeated calls)
        }

        // remember current scroll position
        this.oldX = currX;
        this.oldY = currY;
    },

    // private
    syncSummaryScroll : function(currX, currY) {
        var v = this.view,
            y = this.oldY;

        if (
            // workaround for Gecko's horizontal-scroll reset bug
            // (see unresolved mozilla bug: https://bugzilla.mozilla.org/show_bug.cgi?id=386444
            // "using vertical scrollbar changes horizontal scroll position with overflow-x:hidden and overflow-y:scroll")
            Ext.isGecko     &&          // 1) <div>s with overflow-x:hidden have their DOM.scrollLeft property set to 0 when scrolling vertically
            currX === 0     &&          // 2) current x-ordinate is now zero
            this.oldX > 0   &&          // 3) gridview is not at x=0 ordinate
            (y !== currY || y === 0)    // 4) vertical scroll detected / vertical scrollbar is moved rapidly all the way to the top
        ) {
            this.restoreGridHScroll();
        } else {
            this.syncScroll(v.scroller.dom, v.summaryWrap.dom, currX, currY);
        }
    },

    // private
    restoreGridHScroll : function() {
        // restore gridview's original x-ordinate
        // (note: this causes an unvoidable flicker in the gridview)
        this.view.scroller.dom.scrollLeft = this.oldX || 0;
    },

    // private
    syncGridHScroll : function() {
        var v = this.view;

        this.syncScroll(v.summaryWrap.dom, v.scroller.dom);
    },

    // private
    doWidth : function(col, w, tw) {
        var s = this.getSummaryNode(),
            fc = s.dom.firstChild;

        fc.style.width = tw;
        fc.rows[0].childNodes[col].style.width = w;

        this.updateSummaryWidth();
    },

    // private
    doAllWidths : function(ws, tw) {
        var s = this.getSummaryNode(),
            fc = s.dom.firstChild,
            cells = fc.rows[0].childNodes,
            wlen = ws.length,
            j;

        fc.style.width = tw;

        for (j = 0; j < wlen; j++) {
            cells[j].style.width = ws[j];
        }

        this.updateSummaryWidth();
    },

    // private
    doHidden : function(col, hidden, tw) {
        var s = this.getSummaryNode(),
            fc = s.dom.firstChild,
            display = hidden ? 'none' : '';

        fc.style.width = tw;
        fc.rows[0].childNodes[col].style.display = display;

        this.updateSummaryWidth();
    },
    doSizeColumns : function() {
        var cm = this.grid.getColumnModel();
        var tw = cm.getTotalWidth();
        if (!(Ext.type(cm.getTotalLockedWidth) === false)) {
          tw = tw - cm.getTotalLockedWidth();
        }
        var clen = cm.getColumnCount();
        var ws = [];
        var i;
        var j = 0;
        for(i = 0; i < clen; i++){
          if (cm.getColumnById(cm.getColumnId(i)).locked === false) {
            ws[j++] = cm.getColumnWidth(i);
          }
        }
        this.doAllWidths(ws, tw);
      },
    // private
    getGridHeader : function() {
        if (!this.gridHeader) {
            this.gridHeader = this.view.mainHd.child('.x-grid3-header-offset');
        }

        return this.gridHeader;
    },

    // private
    updateSummaryWidth : function() {
        // all browsers add a 1 pixel space between the edges of the vert. and hori. scrollbars,
        // so subtract one from the grid header width before setting the summary row's width
// TODO: FIX. This gives the wrong width when using GroupingView and GroupSummary, don't know why
//            and haven't investigated it cause it works properly when this is commented out and
//            haven't found any undesirable side effect. 
//        this.getSummaryNode().setWidth(this.getGridHeader().getWidth() - 1);
    },

    renderSummary : function(o, cs, cm) {
        cs = cs || this.view.getColumnData();
        var cfg = cm.config,
            buf = [], c, p = {}, cf, last = cs.length-1;
        for(var i = 0, len = cs.length; i < len; i++){
            c = cs[i];
            cf = cfg[i];
            p.id = c.id;
            p.style = c.style;
            p.css = i == 0 ? 'x-grid3-cell-first ' : (i == last ? 'x-grid3-cell-last ' : '');
            if (cf.totalLabel) {
                p.value = cf.totalLabel;
            } else
            if (cf.summaryType || cf.summaryRenderer) {
                p.value = (cf.summaryRenderer || c.renderer).call(c.scope,o.data[c.name], p, o);
            } else {
                p.value = '';
            }
            if (p.value == undefined || p.value === "") {
                p.value = "&#160;";
            }
            buf[buf.length] = this.cellTpl.apply(p);
        }

        return this.rowTpl.apply({
            tstyle: 'width:' + this.view.getTotalWidth() + ';',
            cells: buf.join('')
        });
    },

    // private
    refreshSummary : function() {
        var g       = this.grid,
            ds      = g.store,
            cs      = this.view.getColumnData(),
            cm      = g.getColumnModel(),
            rs      = ds.getRange(),
            data    = this.calculate(rs, cm),
            buf     = this.renderSummary({data: data}, cs, cm);

        if (!this.view.summaryWrap) {
            this.view.summaryWrap = Ext.DomHelper[this.position=='bottom' ? 'insertAfter' : 'insertBefore'](this.view.scroller, {
                // IE6/7/8 style hacks:
                // - width:100% required for horizontal scroll to appear (all the time for IE6/7, only in GroupingView for IE8)
                // - explicit height required for summary row to appear (only for IE6/7, no effect in IE8)
                // - overflow-y:hidden required to hide vertical scrollbar in summary row (only for IE6/7, no effect in IE8)
                style   : 'overflow:auto;' + (Ext.isIE ? 'width:100%;overflow-y:hidden;height:' + ((Ext.getScrollBarWidth ? Ext.getScrollBarWidth() : this.scrollBarWidth) + 18 /* 18 = row-expander height */) + 'px;' : ''),
                tag     : 'div',
                cls     : 'x-grid3-gridsummary-row-inner'
            }, true);

            // synchronise GridView's and GridSummary's horizontal scroll
            this.view.summaryWrap.on('scroll', this.syncGridHScroll, this);
        }

        // update summary row data
        this.setSummaryNode(this.view.summaryWrap.update(buf).first());

        this.updateSummaryWidth();
    },

    // private
    toggleGridHScroll : function(allowHScroll) {
        // toggle GridView's horizontal scrollbar
        this.view.scroller[allowHScroll === undefined ? 'toggleClass' : allowHScroll ? 'removeClass' : 'addClass']('x-grid3-gridsummary-hide-hscroll');
    },

    /**
     * Toggle the display of the summary row on/off
     * @param {Boolean} visible <tt>true</tt> to show the summary, <tt>false</tt> to hide the summary.
     */
    toggleSummary : function(visible) {
        var el = this.grid.getGridEl(),
            v = this.view;

        if (el) {
            el[visible === undefined ? 'toggleClass' : visible ? 'removeClass' : 'addClass']('x-grid-hide-gridsummary');

            // toggle gridview's horizontal scrollbar
            this.toggleGridHScroll();

            // readjust gridview height
            v.layout();

            // sync summary row scroll position
            v.summaryWrap.dom.scrollLeft = v.scroller.dom.scrollLeft;
        }
    },

    // get summary row Element
    getSummaryNode : function() {
        return this.view.summary;
    },

    // private
    setSummaryNode : function(sn) {
        this.view.summary = sn;
    },

    // private
    beforeDestroy : function() {
        Ext.destroy(
            this.view.summary,
            this.view.summaryWrap
        );

        delete this.grid;
        delete this.view;
        delete this.gridHeader;
        delete this.oldX;
        delete this.oldY;
    }
});
Ext.reg('gridsummary', Ext.ux.grid.GridSummary);

/*
 * all Calculation methods are called on each Record in the Store
 * with the following 5 parameters:
 *
 * v - cell value
 * record - reference to the current Record
 * colName - column name (i.e. the ColumnModel's dataIndex)
 * data - the cumulative data for the current column + summaryType up to the current Record
 * rowIdx - current row index
 */
Ext.ux.grid.GridSummary.Calculations = {
    sum : function(v, record, colName, data, rowIdx) {
        return data[colName] + Ext.num(v, 0);
    },

    count : function(v, record, colName, data, rowIdx) {
        return rowIdx + 1;
    },

    max : function(v, record, colName, data, rowIdx) {
        return Math.max(Ext.num(v, 0), data[colName]);
    },

    min : function(v, record, colName, data, rowIdx) {
        return Math.min(Ext.num(v, 0), data[colName]);
    },

    average : function(v, record, colName, data, rowIdx) {
        var t = data[colName] + Ext.num(v, 0),
            count = record.store.getCount();

        return rowIdx == count - 1 ? (t / count) : t;
    }
};

