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
		var editor = CodeMirror.fromTextArea(document.getElementById(this.editorId), {
				mode: "text/x-mysql",
				tabMode: "indent",
			    lineNumbers: false,
			    lineWrapping: true,
		        matchBrackets: true,
		        readOnly:this.readOnly,
		        extraKeys: {
		        	"Ctrl-Space": function(cm) {
		        		CodeMirror.simpleHint(cm, CodeMirror.javascriptHint);
		        	}
		        },
		        onCursorActivity: function() {
		            editor.setLineClass(hlLine, null);
		            hlLine = editor.setLineClass(editor.getCursor().line, "activeline");
		            editor.matchHighlight("CodeMirror-matchhighlight");
		        }
		});
		
		editor.setValue(this.sourceCode);
		
		var hlLine = editor.setLineClass(0, "activeline");
		
		this.codeMirror = editor;
		
		this.on('resize',function(){
			var fnc = function(){
				 var scroller = editor.getScrollerElement();
				 scroller.style.height = "100%";
	             scroller.style.width = "100%";
				 editor.refresh();
			};
			Ext.Function.defer(fnc, 1000);
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