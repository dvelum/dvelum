<?php return array (
    'bgtask_signal' =>
        array (
            'title' => 'Background Task Signal',
            'fields' =>
                array (
                    'pid' => 'Task PID',
                    'signal' => 'Signal',
                ),
        ),
    'bgtask' =>
        array (
            'title' => 'Background Task',
            'fields' =>
                array (
                    'status' => 'Status',
                    'title' => 'Title',
                    'parent' => 'Parent',
                    'op_total' => 'Operations Count',
                    'op_finished' => 'Operations Finished',
                    'memory' => 'Memory allocated',
                    'time_started' => 'Start Time',
                    'time_finished' => 'Finish Time',
                    'memory_peak' => 'Memory Peak usage',
                ),
        ),
    'blockmapping' =>
        array (
            'title' => 'Block mapping table',
            'fields' =>
                array (
                    'page_id' => 'Page',
                    'place' => 'Place code',
                    'block_id' => 'Block',
                    'order_no' => 'Sort order',
                ),
        ),
    'blocks' =>
        array (
            'title' => 'Blocks',
            'fields' =>
                array (
                    'title' => 'Title',
                    'text' => 'Block content',
                    'show_title' => 'Show title ?',
                    'is_system' => 'Is System ?',
                    'sys_name' => 'System name',
                    'params' => 'Block Params',
                    'is_menu' => 'Is menu block?',
                    'menu_id' => 'Menu',
                ),
        ),
    'comment' =>
        array (
            'title' => 'Comments',
            'fields' =>
                array (
                    'object_name' => 'Commented object name',
                    'object_id' => 'Commented object id',
                    'user_id' => 'Author',
                    'parent_id' => 'Parent Coment',
                    'text' => 'Comment text',
                    'is_hidden' => 'Is Hidden',
                    'date' => 'Date',
                    'checked' => 'Checked',
                ),
        ),
    'group' =>
        array (
            'title' => 'User Groups',
            'fields' =>
                array (
                    'title' => 'Title',
                    'system' => 'Is System',
                ),
        ),
    'historylog' =>
        array (
            'title' => 'History log',
            'fields' =>
                array (
                    'user_id' => 'User',
                    'date' => 'Date',
                    'record_id' => 'Record ID',
                    'type' => 'Operation ID',
                    'object' => 'Object',
                    'before' => 'Before',
                    'after' => 'After',
                ),
        ),
    'links' =>
        array (
            'title' => 'Association Table',
            'fields' =>
                array (
                    'src' => 'Source Object',
                    'src_id' => 'Source object ID',
                    'src_field' => 'Source Object field',
                    'target' => 'Target Object',
                    'target_id' => 'Target object ID',
                    'order' => 'Sorting order',
                ),
        ),
    'medialib' =>
        array (
            'title' => 'Media library',
            'fields' =>
                array (
                    'title' => 'Title',
                    'date' => 'Upload date',
                    'alttext' => 'Alternate text',
                    'caption' => 'Caption',
                    'description' => 'Description',
                    'size' => 'File size',
                    'user_id' => 'User',
                    'path' => 'File path',
                    'type' => 'Resource type',
                    'ext' => 'File Extension',
                    'modified' => 'Date modified',
                    'croped' => 'Is Croped',
                    'category' => 'Category',
                    'hash'=>'File hash'
                ),
        ),
    'menu_item' =>
        array (
            'title' => 'Menu Item',
            'fields' =>
                array (
                    'page_id' => 'Page',
                    'title' => 'Title',
                    'published' => 'Published',
                    'menu_id' => 'Menu ID',
                    'order' => 'Sorting order',
                    'parent_id' => 'Parent Item',
                    'tree_id' => 'Tree ID',
                    'link_type' => 'Link type',
                    'url' => 'URL',
                    'resource_id' => 'Resource Link',
                ),
        ),
    'menu' =>
        array (
            'title' => 'Menu',
            'fields' =>
                array (
                    'code' => 'Code',
                    'title' => 'Title',
                ),
        ),
    'online' =>
        array (
            'title' => 'Users Online',
            'fields' =>
                array (
                    'ssid' => 'Session Id hash',
                    'update_time' => 'Update time',
                    'user_id' => 'User Id',
                    'ids' => '',
                ),
        ),
    'page' =>
        array (
            'title' => 'Pages',
            'fields' =>
                array (
                    'is_fixed' => 'Is Fixed?',
                    'parent_id' => 'Parent Page',
                    'code' => 'Page Code',
                    'page_title' => 'Head title',
                    'menu_title' => 'Menu title',
                    'html_title' => 'Page Title',
                    'meta_keywords' => 'Meta Keyword',
                    'meta_description' => 'Meta Description',
                    'text' => 'Text',
                    'func_code' => 'Attached functionality module',
                    'show_blocks' => 'Show blocks?',
                    'in_site_map' => 'In site map?',
                    'order_no' => 'Sorting Order',
                    'blocks' => 'Blocks data',
                    'theme' => 'Theme',
                    'default_blocks' => 'Default blocks map',
                ),
        ),
    'permissions' =>
        array (
            'title' => 'Permissions',
            'fields' =>
                array (
                    'user_id' => 'User',
                    'group_id' => 'User Group',
                    'view' => 'Can View',
                    'edit' => 'Can edit',
                    'delete' => 'Can Delete',
                    'publish' => 'Can publish',
                    'only_own'=> 'Only own records',
                    'module' => 'Module',
                ),
        ),
    'user' =>
        array (
            'title' => 'Users',
            'fields' =>
                array (
                    'name' => 'User name',
                    'email' => 'Email',
                    'login' => 'User Login',
                    'pass' => 'User Password',
                    'enabled' => 'Is active?',
                    'admin' => 'Back-office user?',
                    'registration_date' => 'Registration date',
                    'confirmation_code' => 'Confirmation code',
                    'group_id' => 'User Group',
                    'confirmed' => 'Is confirmed?',
                    'avatar' => 'Avatar',
                    'registration_ip' => 'Registration IP',
                    'last_ip' => 'Last IP',
                    'confirmation_date' => 'Confirmation Date',
                ),
        ),
    'vc' =>
        array (
            'title' => 'Versions storage',
            'fields' =>
                array (
                    'date' => 'Date',
                    'record_id' => 'Record id',
                    'object_name' => 'Object name',
                    'data' => 'Data',
                    'user_id' => 'Created by',
                    'version' => 'Version',
                ),
        ),
    'mediacategory' =>
        array (
            'title' => 'Medialibrary category',
            'fields' =>
                array (
                    'title' => 'Title',
                    'parent_id' => 'Parent category',
                    'order_no' => 'Sorting order',
                ),
        ),
    'acl_simple' =>
        array (
            'title' => 'Simple ACL',
            'fields' =>
                array (
                    'user_id' => 'user_id',
                    'group_id' => 'group_id',
                    'view' => 'view',
                    'edit' => 'edit',
                    'delete' => 'delete',
                    'publish' => 'Can publish',
                    'object' => 'object',
                    'create' => 'create',
                ),
        ),
    'error_log' =>
        array (
            'title' => 'Error log',
            'fields' =>
                array (
                    'name' => 'Source name',
                    'message' => 'Message',
                    'date' => 'Date',
                    'level' => 'Log level',
                    'context' => 'Context'
                ),
        ),
    'user_auth' =>
        array (
            'title' => 'Auth settings',
            'fields' =>
                array (
                    'user' => 'User',
                    'type' => 'Auth type',
                    'config' => 'Configuration',
                ),
        ),
);