/**
 * @event codeMirrorInit
 */
Ext.define('designer.codeEditor',{
	extend:'Ext.Panel',
	layout:'fit',
	autoRender:true,
	scrollable:false,
	codeMirror:null,
	codeMirrorInit:false,
	styleHtmlContent:true,
	editorId:null,
	sourceCode:'',
	readOnly:false,
	controllerUrl:null,
	saveBtn:null,
	showSaveBtn:true,
	btnUndo:null,
	btnRedo:null,
	collapsible:false,
	extraKeys:false,

	historyUndoState:{},

	headerText:'',
	footerText:'',

	initComponent:function()
	{
		this.saveBtn = Ext.create('Ext.Button',{
			tooltip:desLang.save,
			iconCls:'saveIcon',
			scope:this,
			disabled:true,
			hidden:(this.readOnly || !this.showSaveBtn),
			handler:this.saveCode
		});

		this.btnUndo = Ext.create('Ext.Button',{
			tooltip:desLang.undo,
			iconCls:'undoIcon',
			scope:this,
			disabled:true,
			handler:this.undoAction
		});
		this.btnRedo = Ext.create('Ext.Button',{
			tooltip:desLang.redo,
			iconCls:'redoIcon',
			scope:this,
			disabled:true,
			handler:this.redoAction
		});

		this.tbar = [
			this.saveBtn,'-',
			this.btnUndo,this.btnRedo,'-',
			/* {
			 tooltip:desLang.autoformatSelection,
			 icon:'i/system/designer/format-code.png',
			 handler:this.formatSelection,
			 scope:this
			 }*/
		];

		this.editorId = 'CodeEditorCmp_' + this.id;
		this.html=this.headerText+'<textarea id="' + this.editorId + '" name="codeEditorCmp" style="width:100%;height:100%"></textarea>'+this.footerText;

		this.callParent();
		this.on('afterrender',this.initEditor,this);

	},

	initEditor:function(){

		if(this.codeMirrorInit){
			return;
		}
		var me = this;
		var keymap;
		var hline;

		if(this.extraKeys){
			keymap = this.extraKeys;
		}else{
			keymap =  {
				"Ctrl-Space": function(cm) {
					CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);
				},
				"Ctrl-S": function(instance) {me.saveCode();},
				"Ctrl-Z": function(instance) {me.undoAction();},
				"Ctrl-Y": function(instance) {me.redoAction();},
				"Shift-Ctrl-Z": function(instance) {me.redoAction();}
			};
		}

		var editor = CodeMirror.fromTextArea(document.getElementById(this.editorId), {
			styleActiveLine: true,
			lineNumbers: true,
			lineWrapping: true,
			matchBrackets: true,
			readOnly:this.readOnly,
			extraKeys: keymap,
			highlightSelectionMatches: {showToken: /\w/}
		});
		editor.on('change',function(){me.onChange();});

		editor.setValue(this.sourceCode);

		this.codeMirror = editor;
		this.codeMirrorInit = true;
		this.on('resize', this.syncEditor, this);
		this.on('show' , this.syncEditor, this);
		this.updateLayout();
	},
	syncEditor:function(){
		var me = this;
		me.codeMirror.setSize('100%',me.getEl().getHeight()-62);
		me.codeMirror.refresh();
		/*cm.setSize
		var fnc = function(){
			var scroller = me.codeMirror.getScrollerElement();
			scroller.style.height = me.getEl().getHeight()  + 'px';
			scroller.style.width = me.getEl().getWidth() + 'px';
			me.codeMirror.refresh();
			me.updateLayout();
		};
		Ext.Function.createBuffered(fnc, 500)();
		*/
	},
	getSelectedRange:function() {
		return { from: this.codeMirror.getCursor(true), to: this.codeMirror.getCursor(false) };
	},
	formatSelection:function(){
		var range = this.getSelectedRange();

		if(range.from === range.to){
			return;
		}
		this.codeMirror.autoFormatRange(range.from, range.to);
	},
	getValue:function(){
		return this.codeMirror.getValue();
	},
	setValue:function(value){
		this.codeMirror.setValue(value);
	},
	saveCode:function(){
		this.getEl().mask(desLang.saving);
		Ext.Ajax.request({
			url:this.controllerUrl + 'save',
			method: 'post',
			scope:this,
			params:{
				code:this.codeMirror.getValue()
			},
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(!response.success){
					Ext.Msg.alert(appLang.MESSAGE,response.msg);
				}else{
					this.historyUndoState = this.codeMirror.historySize()['undo'];
					this.saveBtn.disable();
					designer.msg(appLang.MESSAGE, desLang.msg_scriptSaved);
				}
				this.getEl().unmask();
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				this.getEl().unmask();
			}
		});
	},
	loadCode:function(){

		this.getEl().mask(desLang.loading);
		Ext.Ajax.request({
			url:this.controllerUrl + 'load',
			method: 'post',
			scope:this,
			success: function(response, request) {
				response =  Ext.JSON.decode(response.responseText);
				if(!response.success)
				{
					this.disable();
					Ext.Msg.alert(appLang.MESSAGE,desLang.cantLoadActionJS +'.<br>'+response.msg);
				}else{
					this.codeMirror.setValue(response.data);
					this.syncEditor();
					this.historyUndoState = this.codeMirror.historySize()['undo'];
					this.onChange();
				}
				this.getEl().unmask();
			},
			failure:function() {
				Ext.Msg.alert(appLang.MESSAGE, appLang.MSG_LOST_CONNECTION);
				this.getEl().unmask();
			}
		});
	},
	onChange:function(){
		if(this.codeMirror != null){
			var history = this.codeMirror.historySize();

			this.saveBtn.enable();

			if(history.undo > 2){
				this.btnUndo.enable();
			}else{
				this.btnUndo.disable();
			}

			if(history.redo){
				this.btnRedo.enable();
			}else{
				this.btnRedo.disable();
			}
		}
	},
	undoAction:function(){
		if(this.codeMirror != null){
			this.codeMirror.undo();
		}
	},
	redoAction:function(){
		if(this.codeMirror != null){
			this.codeMirror.redo();
		}
	}
});