<?php
return array(
    'dumpdir' => 'data/docs/',
    'chunk_size' => 500,
    'objects' => array(
        'sysdocs_file' => [
            'path',
            'isDir',
            'name',
            'vers',
            'hid',
            'parentId',
            'id'
        ],
        'sysdocs_class' => [
            'description',
            'itemType',
            'fileId',
            'parentId',
            'vers',
            'name',
            'namespace',
            'deprecated',
            'hid',
            'abstract',
            'fileHid',
            'implements',
            'extends',
            'id'
        ],
        'sysdocs_class_method' => [
            'classId',
            'name',
            'deprecated',
            'description',
            'throws',
            'hid',
            'abstract',
            'static',
            'visibility',
            'vers',
            'returnType',
            'classHid',
            'final',
            'inherited',
            'returnsReference',
            'id'
        ],
        'sysdocs_class_method_param' => [
            'methodId',
            'hid',
            'name',
            'vers',
            'index',
            'default',
            'isRef',
            'description',
            'methodHid',
            'optional',
            'id'
        ],
        'sysdocs_class_property' => [
            'deprecated',
            'hid',
            'vers',
            'name',
            'description',
            'const',
            'static',
            'visibility',
            'type',
            'classId',
            'constValue',
            'classHid',
            'inherited',
            'id'
        ],
        'sysdocs_localization' => [
            'lang',
            'field',
            'object_id',
            'value',
            'vers',
            'object_class',
            'hid',
            'id'
        ]
    )
);