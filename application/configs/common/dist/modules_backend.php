<?php return array (
    'Acl' =>
        array (
            'id' => 'Acl',
            'dev' => false,
            'active' => true,
            'class' => '\\Dvelum\\App\\Backend\\Acl\\Controller',
            'designer' => '',
            'in_menu' => true,
            'icon' => 'i/system/icons/acl.png',
        ),
    'Tasks' =>
        array (
            'id' => 'Tasks',
            'dev' => false,
            'active' => true,
            'class' => 'Backend_Tasks_Controller',
            'designer' => '',
            'in_menu' => true,
            'icon' => 'i/system/icons/tasks.png',
        ),
    'Orm' =>
        array (
            'id' => 'Orm',
            'dev' => true,
            'active' => true,
            'class' => '\\Dvelum\\App\\Backend\\Orm\\Controller',
            'designer' => '',
            'in_menu' => true,
            'icon' => 'i/system/icons/orm.png',
        ),
    'Error_Log' =>
        array (
            'id' => 'Error_Log',
            'dev' => false,
            'active' => true,
            'class' => '\\Dvelum\\App\\Backend\\Error\\Log\\Controller',
            'designer' => '/system/error_log.designer.dat',
            'in_menu' => true,
            'icon' => 'i/system/icons/error_log.png',
        ),

    'Logs' =>
        array (
            'dev' => false,
            'active' => true,
            'class' => '\\Dvelum\\App\\Backend\\Logs\\Controller',
            'designer' => '/system/historylog.designer.dat',
            'in_menu' => true,
            'icon' => 'i/system/icons/log.png',
            'id' => 'Logs',
        ),
    'Localization' =>
        array (
            'id' => 'Localization',
            'dev' => true,
            'active' => true,
            'class' => '\\Dvelum\\App\\Backend\\Localization\\Controller',
            'designer' => '/system/localization.designer.dat',
            'in_menu' => true,
            'icon' => 'i/system/icons/localize.png',
        ),
    'Modules' =>
        array (
            'id' => 'Modules',
            'dev' => true,
            'active' => true,
            'class' => '\\Dvelum\\App\\Backend\\Modules\\Controller',
            'designer' => '',
            'in_menu' => true,
            'icon' => 'i/system/icons/modules.png',
        ),
    'User' =>
        array (
            'id' => 'User',
            'dev' => false,
            'active' => true,
            'class' => '\\Dvelum\\App\\Backend\\User\\Controller',
            'designer' => '/system/users.designer.dat',
            'in_menu' => true,
            'icon' => 'i/system/icons/users.png',
        ),
    'Externals' =>
        array (
            'dev' => true,
            'active' => true,
            'class' => '\\Dvelum\\App\\Backend\\Externals\\Controller',
            'designer' => '/system/externals.designer.dat',
            'in_menu' => true,
            'icon' => 'i/system/icons/external_modules.png',
            'id' => 'Externals',
        ),
    'User_Auth' =>
        array (
            'dev' => false,
            'active' => true,
            'class' => 'Backend_User_Auth_Controller',
            'designer' => '/system/user_auth.designer.dat',
            'in_menu' => true,
            'icon' => 'i/system/icons/user_auth.png',
            'id' => 'User_Auth',
        ),
    'Settings' =>
        array (
            'dev' => false,
            'active' => true,
            'class' => 'Dvelum\\App\\Backend\\Settings\\Controller',
            'designer' => '/system/user_settings.designer.dat',
            'in_menu' => true,
            'icon' => 'i/system/icons/user_settings.png',
            'id' => 'Settings',
        ),
);