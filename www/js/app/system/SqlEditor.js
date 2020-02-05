Ext.define('designer.sqlEditor',{
	extend:'Ext.Panel',
	layout:'fit',
	scrollable:false,
	codeMirror:null,
	codeMirrorInit:false,
	styleHtmlContent:true,
	editorId:null,
	sourceCode:'',
	readOnly:false,
//addEvents
	initComponent:function(){	
		this.editorId = 'SqlEditorCmp_' + this.id;	
		this.html='<textarea id="' + this.editorId + '" name="SqlEditorCmp" style="width:100%;height:100%"></textarea>';
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
			highlightSelectionMatches: {showToken: /\w/},
			onChange:function(){
				me.onChange();
			}
		});
		
		editor.setValue(this.sourceCode);
		

		this.codeMirror = editor;
		
		this.on('resize',function(){
            var me = this;
            me.codeMirror.setSize('100%',me.getEl().getHeight()-62);
            me.codeMirror.refresh();
            /*
			var fnc = function(){
				 var scroller = editor.getScrollerElement();
				 scroller.style.height = "100%";
	             scroller.style.width = "100%";
				 editor.refresh();
			};
			Ext.Function.defer(fnc, 1000);
			*/
		},this);
	},
	getSelectedRange:function() {
        return { from: this.codeMirror.getCursor(true), to: this.codeMirror.getCursor(false) };
    },
	getValue:function(){
		return this.codeMirror.getValue();
	},
	setValue:function(value){
		return this.codeMirror.setValue(value);
	}
});