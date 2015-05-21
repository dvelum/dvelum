<?php
$docRoot = dirname(dirname(dirname(__FILE__))) . '/www';
$_SERVER['DOCUMENT_ROOT'] = $docRoot;

/*
 * Connecting main configuration file
 */
$config = include_once __DIR__ . '/config.php';
$devObjectsPath = $config['object_configs'];
$config['object_configs'] = __DIR__ . '/../objects/';

// Copy objects
$d = opendir($devObjectsPath);
while($file = readdir($d)){
        if(is_file($devObjectsPath.$file))
                copy($devObjectsPath.$file, $config['object_configs'].$file);
}
closedir($d);

/*
 * Including Autoloader class
 */
require $config['docroot'].'/system/library/Autoloader.php';
/*
 * Setting autoloader config
 */
$autoloaderCfg = $config['autoloader'];
$autoloaderCfg['debug'] = $config['development'];
$autoloaderCfg['map'] = false;

$autoloaderCfg['paths'] =array(
        $docRoot . '/system/app',
        $docRoot . '/system/library',
);

$autoloader = new Autoloader($autoloaderCfg);
/**
 * Convert the data of main_config file
 * in to the general form of configuration
 * and save a reference for it (for convenience)
 * @var Config_Simple $appConfig
 */
$appConfig = Config::factory(Config::Simple, 'main');
$appConfig->setData($config);
Registry::set('main', $appConfig , 'config');
/**
 * Convert the data of main_config file
 * in to the general form of configuration
 * and save a reference for it (for convenience)
 * @var Config_Simple $appConfig
 */
/*
 * Starting the application
 */
$app = new Application($appConfig);
$app->setAutoloader($autoloader);
$app->init();

//  build objects
$objectFiles = File::scanFiles($config['object_configs'] , array('.php') , false , File::Files_Only);
foreach ($objectFiles as $file){
        $object = substr( basename($file),0,-4);
        echo 'build ' . $object . ' : ';
        $builder = new Db_Object_Builder($object);
        if($builder->build()){
                echo 'OK';
        }else{
                echo 'Error! ' . strip_tags(implode(', ', $builder->getErrors()));
        }
        echo "\n";
}

