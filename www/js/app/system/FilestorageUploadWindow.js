Ext.ns('app.filestorage');

Ext.define('app.filestorage.fileModel',{
    extend: 'Ext.data.Model',
    fields: [
        {name:'id',type:'integer'},
        {name:'name',type:'string'},
        {name:'size',type:'string'},
        {name:'date', type:'date', format:'Y-m-d H:i:s'},
        {name:'ext',type:'string'},
        {name:'icon', type:'string'}
    ]
});

/**
 * FileStorage uploader window
 *
 * Settings:
 * uploaderConfig - accepted files (from storage config):
 * {
 *      image:{
 *          title:'Images',
 *          extensions:['.jpg','.png']
 *      },
 *      file:{
 *          title:'Files',
 *          extensions:['.doc','.zip']
 *      }
 * }
 * uploadUrl - file upload action url
 * maxFileSize -Maximum allowed size for uploaded files. (string)
 *
 *
 * @author Kirill Egorov 2016
 * @extend Ext.Window
 *
 * @event filesUploaded
 *
 * @event fileUploaded
 * @param {Array} data
 */
Ext.define('app.filestorage.UploadWindow', {
    extend: 'Ext.Window',

    contentPanel: null,

    uploadUrl: null,

    selectedCategory: 0,
    /**
     * @var string max upload file size
     */
    maxFileSize: '',
    /**
     * @var {Object} file storage media config
     */
    uploaderConfig: null,

    uploadedRecords: null,
    /**
     * field name
     */
    fieldName:'file',
    layout: 'border',

    constructor: function (config) {

        config = Ext.apply({
            cls: 'upload_window',
            modal: true,
            title: appLang.FILE_UPLOAD,
            width: 420,
            height: 500,
            closeAction: 'destroy',
            resizable: false,
            items: []
        }, config || {});

        this.callParent(arguments);
    },

    initComponent: function () {
        var me = this;
        me.uploadedRecords = [];
        var accExtString = '<div><b>' + appLang.MAX_UPLOAD_FILE_SIZE + '</b><br> ' + this.maxFileSize + '<br>';

        for (i in me.uploaderConfig) {
            if (Ext.isEmpty(i.title)) {
                accExtString += '<b>' + me.uploaderConfig[i].title + '</b><br> ';
                var cnt = 0;
                var len = me.uploaderConfig[i].extensions.length;
                Ext.each(me.uploaderConfig[i].extensions, function (extName) {
                    if (cnt < (len - 1)) {
                        accExtString += extName + ', ';
                    } else {
                        accExtString += extName;
                    }
                    cnt++;
                });
                accExtString += '<br>';
            }
        }
        accExtString += '</div>';


        this.multipleUploadedGrid = Ext.create('Ext.grid.Panel', {
            region: 'center',
            store: Ext.create('Ext.data.Store', {
                autoLoad: false,
                idProperty: 'id',
                fields: [
                    {name: 'id', type: 'integer'},
                    {name: 'icon', type: 'string'},
                    {name: 'progress', type: 'float'},
                    {name: 'name', type: 'string'},
                    {name: 'uploaded', type: 'boolean'},
                    {name: 'uploadError', type: 'string'}

                ]
            }),
            viewConfig: {
                stripeRows: true
            },
            frame: false,
            loadMask: true,
            columnLines: false,
            autoScroll: true,
            columns: [
                {
                    text: appLang.ICON,
                    dataIndex: 'icon',
                    align: 'center',
                    xtype: 'templatecolumn',
                    tpl: new Ext.XTemplate(
                        '<div style="white-space:normal;">',
                        '<img src="{icon}" alt="[icon]" style="border:1px solid #000000;" height="32"/>',
                        '</div>'
                    ),
                    width: 80
                }, {
                    text: appLang.NAME,
                    dataIndex: 'name',
                    flex: 1,
                    renderer: function (v, m, r) {
                        if (r.get('uploadError').length) {
                            v += '<br><span style="color:red;">' + r.get('uploadError') + '</span>';
                        }
                        return v;
                    }
                }, {
                    text: appLang.PROGRESS,
                    dataIndex: 'progress',
                    width: 100,
                    renderer: app.progressRenderer
                }
            ]
        });

        this.ajaxUploadField = Ext.create('Ext.ux.form.AjaxFileUploadField', {
            emptyText: appLang.SELECT_FILE,
            buttonText: appLang.MULTIPLE_FILE_UPLOAD,
            buttonOnly: true,
            defaultIcon: app.wwwRoot + 'i/unknown.png',
            name:this.fieldName,
            url: this.uploadUrl,
            buttonConfig: {
                iconCls: 'upload-icon'
            },
            listeners: {
                'filesSelected': {
                    fn: me.onMFilesSelected,
                    scope: this
                },
                'fileUploaded': {
                    fn: me.onMFileUploaded,
                    scope: this
                },
                'fileUploadProgress': {
                    fn: me.onMFilesUploadProgress,
                    scope: this
                },
                'fileUploadError': {
                    fn: me.onMFilesUploadError,
                    scope: this
                },
                'fileImageLoaded': {
                    fn: me.onMFilesImageLoaded,
                    scope: this
                },
                'filesUploaded': {
                    fn: me.onMFilesUploaded,
                    scope: this
                }
            }
        });

        var linkLabel = Ext.create('Ext.form.Label', {
            text: appLang.ACCEPTED_FORMATS,
            style: {
                textDecoration: 'underline',
                padding: '5px',
                fontSize: '10px',
                color: '#3F1BF6',
                cursor: 'pointer'
            },
            listeners: {
                afterrender: {
                    fn: function (cmp) {
                        cmp.getEl().on('click', function () {
                            Ext.Msg.alert(appLang.ACCEPTED_FORMATS, accExtString);
                        }, me);
                    },
                    scope: this
                }
            }
        });

        this.mClearButton = Ext.create('Ext.Button', {
            text: appLang.CLEAR,
            disabled: true,
            listeners: {
                'click': {
                    fn: function () {
                        this.resetUploadedRecords();
                    },
                    scope: this
                }
            }
        });
        this.mUploadButton = Ext.create('Ext.Button', {
            text: appLang.UPLOAD,
            disabled: true,
            listeners: {
                'click': {
                    fn: function () {
                        this.ajaxUploadField.upload();
                    },
                    scope: this
                }
            }
        });

        this.multipleUpload = Ext.create('Ext.Panel', {
            region: 'north',
            fileUpload: true,
            padding: 5,
            height: 80,
            frame: true,
            border: false,
            fieldDefaults: {
                anchor: "100%",
                hideLabel: true
            },
            layout: 'hbox',
            items: [
                this.ajaxUploadField,
                {
                    xtype: 'label',
                    flex: 1
                },
                linkLabel
            ],
            buttons: [ this.mUploadButton, this.mClearButton]
        });

        this.items = [this.multipleUpload, this.multipleUploadedGrid];
        this.callParent();
    },
    onMFilesImageLoaded: function (index, icon) {
        var store = this.multipleUploadedGrid.getStore();
        var rIndex = store.findExact('id', index);
        if (index != -1) {
            var rec = store.getAt(rIndex);
            rec.set('icon', icon);
            rec.commit();
        }
    },
    onMFilesSelected: function (files) {
        var me = this;
        var data = [];
        if (this.ajaxUploadField.filesCount()) {
            Ext.each(this.ajaxUploadField.getFiles(), function (file, index) {
                var progress;
                file.uploaded ? progress = 100 : progress = 0;
                data.push({
                    id: index,
                    name: file.name,
                    icon: file.icon,
                    progress: progress,
                    uploaded: file.uploaded,
                    uploadError: file.uploadError
                });
            }, me);
        }
        this.multipleUploadedGrid.getStore().loadData(data);
        if (!Ext.isEmpty(data)) {
            this.mClearButton.enable();
            this.mUploadButton.enable();
        } else {
            this.mClearButton.disable();
            this.mUploadButton.disable();
        }
    },

    onMFileUploaded: function (index, result) {
        var store = this.multipleUploadedGrid.getStore();
        var rIndex = store.findExact('id', index);
        if (index != -1) {
            var rec = store.getAt(rIndex);
            rec.set('uploaded', 1);
            rec.commit();
            this.uploadedRecords.push(result.data);
            this.fireEvent('fileUploaded', result.data);
        }
    },
    onMFilesUploadProgress: function (index, uploaded, total) {
        var store = this.multipleUploadedGrid.getStore();
        var rIndex = store.findExact('id', index);
        if (index != -1) {
            var rec = store.getAt(rIndex);
            rec.set('progress', (uploaded * 100) / total);
            rec.commit();
        }
    },
    onMFilesUploadError: function (index, result) {
        var file = this.ajaxUploadField.getFile(index);
        if (!file) {
            return;
        }
        var store = this.multipleUploadedGrid.getStore();
        var rIndex = store.findExact('id', index);
        if (index != -1) {
            var rec = store.getAt(rIndex);
            rec.set('uploadError', file.uploadError);
            rec.set('progress', 99);
            rec.commit();
        }
    },
    onMFilesUploaded: function () {
        this.fireEvent('filesUploaded');
    },
    /**
     * Simple upload form submit
     */
    simpleUploadStart: function () {
        var handle = this;
        this.simpleUpload.getForm().submit({
            clientValidation: true,
            url: this.uploadUrl,
            waitMsg: appLang.UPLOADING,
            success: function (form, responce) {
                handle.simpleUpload.getForm().reset();
                handle.fireEvent('fileUploaded', responce.result.data);
                handle.fireEvent('filesUploaded');
            },
            failure: app.formFailure
        });
    },
    getUploadedRecords: function () {
        return this.uploadedRecords;
    },
    resetUploadedRecords: function () {
        this.ajaxUploadField.reset();
        this.multipleUploadedGrid.getStore().removeAll();
        this.uploadedRecords = [];
    }
});
