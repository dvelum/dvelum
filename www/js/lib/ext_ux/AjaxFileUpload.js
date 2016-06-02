/**
 * DVelum file uploader
 */
Ext.ns('Ext.ux.form');
/**
 *
 *
 * @event filesSelected - files {Array}
 * @event filesUploaded
 * @event fileUploadProgress - fileIndex , uploaded , total
 * @event fileUploaded - fileIndex , result
 * @event fileUploadError - fileIndex , result
 * @event fileImageLoaded - file index , icon
 *
 */
Ext.define('Ext.ux.form.AjaxFileUploadField', {
    extend:'Ext.form.field.File',
    alias:'widget.ajaxfileuploadfield',
    buttomOnly:true,
    name:'myFile',
    fileQueue:false,
    defaultIcon:'',
    url:'',

    initComponent:function(){
        this.callParent(arguments);
        this.fileQueue = [];
    },

    onRender : function() {
        var me = this;
        this.callParent();
        this.fileInputEl.dom.multiple='multiple';
        this.fileInputEl.addListener('change' , function(evt , el ,o){
            me.onFilesSelected(el.files);
        },me);
    },

    onFilesSelected:function(files){
        var me = this;
        Ext.each(files , function(file , index){
            var curIndex = me.fileQueue.length;
            file.uploaded = false;
            file.uploadError = '';
            if (file.type.match(/image.*/)) {

                var reader = new FileReader();
                reader.onloadend = (function(mCurIndex) {
                    return function(e) {

                        var dataUrl = e.target.result;
                        var iframe = (function() {
                            var iframeId = "tmpFrame";
                            var tmpIframe = document.createElement("iframe");
                            tmpIframe.setAttribute("id", iframeId);
                            tmpIframe.setAttribute("name", iframeId);
                            tmpIframe.setAttribute("width", "0");
                            tmpIframe.setAttribute("height", "0");
                            tmpIframe.setAttribute("border", "0");
                            tmpIframe.setAttribute("style", "width: 0; height: 0; border: none;");
                            document.body.appendChild(tmpIframe);
                            window.frames[iframeId].name = iframeId;
                            return tmpIframe;
                        })();

                        var image = new Image();
                        image.onload = function() {
                            if(window.processImage){
                                var result = processImage(image, exif['Orientation']);
                            }
                            document.body.removeChild(iframe); /* IE10 issue workaround. */
                        };

                        image.src = dataUrl.replace('data:base64', 'data:image/jpeg;base64'); /* Android issue workaround. */
                        iframe.appendChild(image); /* IE10 issue workaround. */

                        me.fileQueue[mCurIndex].icon = e.target.result;
                        me.fireEvent('fileImageLoaded' , mCurIndex , e.target.result);
                    };
                })(curIndex);
                reader.readAsDataURL(file);


            }else{
                file.icon=me.defaultIcon;
            }
            me.fileQueue.push(file);
        },me);

        me.fireEvent('filesSelected' , files);
    },
    getFile:function(index){
        if(this.fileQueue[index]){
            return this.fileQueue[index];
        }else{
            return false;
        }
    },
    getFiles:function(){
        return this.fileQueue;
    },

    filesCount:function(){
        return this.fileQueue.length;
    },

    upload:function(){
        var me = this;

        if(!me.filesCount()){
            return;
        }

        Ext.each(me.fileQueue, function(item , index) {
            me.uploadFile(index);
        },me);
    },

    reset:function(){
        this.fileQueue = [];
    },

    onFileUploaded:function(fileIndex){

        var allDone = true;

        Ext.each(this.fileQueue , function(item , index){
            if(item && !item.uploaded){
                allDone = false;
            }
        },this);

        if(allDone){
            this.fireEvent('filesUploaded');
        }
    },
    supportFormData:function() {
        return !! window.FormData;
    },
    uploadFile:function(fileIndex)
    {
        var me = this;
        if(!me.fileQueue[fileIndex] || me.fileQueue[fileIndex].uploaded){
            return;
        }

        var file = me.fileQueue[fileIndex];
        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener("progress", function(e) {
            if (e.lengthComputable){
                me.fireEvent('fileUploadProgress', fileIndex , e.loaded , e.total);
            }
        },false);

        xhr.onreadystatechange = function(){
            if(this.readyState == 4){
                if(this.status == 200){
                    var result = Ext.JSON.decode(this.responseText);
                    if(result.success){
                        me.fileQueue[fileIndex].uploaded = true;
                        me.fileQueue[fileIndex].uploadResult = result;
                        me.fireEvent('fileUploadProgress', fileIndex , 100, 100);
                        me.fireEvent('fileUploaded' , fileIndex , result);
                        me.onFileUploaded(fileIndex);
                    }else{
                        me.fileQueue[fileIndex].uploaded = false;
                        if(result.msg){
                            me.fileQueue[fileIndex].uploadError = result.msg;
                        }else{
                            me.fileQueue[fileIndex].uploadError = appLang.MSG_LOST_CONNECTION;
                        }
                        me.fireEvent('fileUploadError', fileIndex , result);
                    }
                } else {
                    me.fireEvent('fileUploadError', fileIndex , {success:false});
                }
            }
        };

        // Upload using FormData
        if(this.supportFormData())
        {
            xhr.open("POST", me.url, true);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
            var formData = new FormData();
            formData.append(me.name, file);
            xhr.send(formData);
            return;
        }

        var reader = new FileReader();
        reader.onload = function() {
            xhr.open("POST", me.url);
            xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");

            var boundary = "____boundary_____";
            // Request Headers
            xhr.setRequestHeader("Content-Type", "multipart/form-data, boundary="+boundary);
            xhr.setRequestHeader("Cache-Control", "no-cache");
            // Request body
            var body = "--" + boundary + "\r\n";
            body += "Content-Disposition: form-data; name='" + me.name + "'; filename='" + unescape( encodeURIComponent(file.name)) + "'\r\n";
            body += "Content-Type: application/octet-stream\r\n\r\n";
            body += reader.result + "\r\n";
            body += "--" + boundary + "--";

            //Chrome bug fix
            if (!XMLHttpRequest.prototype.sendAsBinary) {
                XMLHttpRequest.prototype.sendAsBinary = function(datastr) {
                    function byteValue(x) {
                        return x.charCodeAt(0) & 0xff;
                    }
                    var ords = Array.prototype.map.call(datastr, byteValue);
                    var ui8a = new Uint8Array(ords);
                    this.send(ui8a.buffer);
                }
            }

            if(xhr.sendAsBinary) {
                // firefox only
                xhr.sendAsBinary(body);
            } else {
                // chrome (W3C)
                xhr.send(body);
            }
        };
        // Read file
        reader.readAsBinaryString(file);
    }
});