<?php return array (
  'qtip_object_rev_control' => 'Use data version control: allows to track document versions and updates; affects the autogeneration template; adds additional system fields to the database table.',
  'qtip_object_save_history' => 'Save revision history: allows to define the author and the kind of change.',
  'qtip_object_name' => 'Object name in Latin characters.<br>
Please note that the same name is assigned to the model working with this object data.
    Naming objects complies to the same rules as naming classes. The "_" sign denotes a nested directory.
    When naming objects, lower case and Latin alphabet should be used.
The name will be used in code to invoke various methods and create objects.  E.g.:<br>
$news = new Db_Object(‘News’);<br>
$newsModel = Model::factory(‘News’);
    ',
  'qtip_object_title' => 'Object title, any textual name of an object, e.g. a News item. Various localizations use various title storages, thus upon creating an object, it is possible to switch platform localization and add titles in the respective language; the changes will be saved, while the titles in the other locale will be kept unchanged. In case there is no title for the current locale, the value of the name field will be saved.',
  'qtip_object_table' => 'Name of the database table containing object data (to be filled without prefix if automatic prefix delegation is enabled).',
  'qtip_object_engine' => 'Database storage type: differ in data storage characteristics and opportunities. More information about storage types may be found in the official documentation on  MySQL /  MariaDB / Percona Server',
  'qtip_object_disable_keys' => 'Disable external keys support. The additional option disables using external keys for the object data table and has a higher priority than the config/main.php setting.',
  'qtip_object_link_title' => 'Field used as object title for external link: used in various components of Layout Designer, e.g. is displayed in the Data Edit form. ',
  'qtip_object_connection' => 'Connection to the database. Objects may use various independent database connections. Databases may be located on different servers. ',
  'qtip_object_primary_key' => 'Primary key: the name of the primary key column, which needs to be an integer, autoincrement field; it is advisable to name the id.',
  'qtip_object_readonly' => 'Read only mode; writing to the database table not allowed.',
  'qtip_object_locked' => 'Mode for locking changes; restricts changing the structure of a database table; useful for external databases where the structure is not allowed to be changed.',
  'qtip_object_use_db_prefix' => 'Use prefix for DB table name. ',
  'qtip_field_dictionary' => 'Dictionary linked to by the field.',
  'qtip_field_db_default' => 'Default value',
  'qtip_field_db_len' => 'String length for string fields. Please note that in case of integer types, this is the number of available characters to be extended by spaces on the left when the displayed value doesn’t fill the whole of the column width (in command line interface)',
  'qtip_field_db_scale' => 'String representation length for a real number, e.g. 4 for 10.01 (a characteristic of database field types).',
  'qtip_field_db_precision' => 'String representation length for the decimal part of a real number, e.g.  2 for 0.01.',
  'qtip_field_db_type' => 'Field type in a DB table. More information about field types may be found in the official documentation on  MySQL /  MariaDB / Percona Server.',
  'qtip_field_validator' => 'Additional data validator, which is a class implementing Validator_interface; is located in library/Validator',
  'qtip_field_link_type' => 'Link field type (link to an object / link to a list of objects / link to a dictionary)',
  'qtip_field_object' => 'Object linked to by the field',
  'qtip_field_db_isNull' => 'Field value may be NULL',
  'qtip_field_required' => 'Field is required',
  'qtip_field_db_unsigned' => 'Unsigned (or positive) field ',
  'qtip_field_allow_html' => 'Allow using html tags as values. If allowed, interface auto generator will use an extended text editor. ',
  'qtip_field_is_search' => 'Search field, which is included in model search request (LIKE %text%) and used by interface auto generator.',
  'qtip_field_unique' => 'String field; a  unique field index, which is used by ORM validator when storing data, whether there is a unique DB table index or not. If one and the same index is specified in several fields, ORM validator will be using a multicolumn unique index.',
  'qtip_field_name' => 'Field name (in Latin characters)',
  'qtip_field_title' => 'Field title, any text defining the field; is used by auto generator as a field label. Like an object name, it may be specified for each localization. ',
  'qtip_field_type' => 'Field type',
  'qtip_object_set_default' => 'Set default value',
  'qtip_object_use_acl' => 'Use access control list',
  'qtip_object_acl' => 'Access Controll List  adapter',
  'qtip_field_relations_type' => 'Type of relations <br>
Polymorphic relationship -  associations stored in a common ORM object Links <br>
Many to Many - create a separate associations object ORM',
  'qtip_object_sharding_key'=> 'Select ORM Object field'
);