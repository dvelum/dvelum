<?php
class Install_Controller {
	/**
	 * Document root
	 * @var string
	 */
	protected $_docRoot;

	/**
	 * Template
	 * @var Template
	 */
	protected $_template;

	protected $_dictionary;

	protected $_lang;
	protected $_phpExt;

	protected $_session;
	protected $_wwwRoot;

	public function __construct()
	{
	  $this->_session = Store::factory(Store::Session , 'install');
	  $this->_docRoot = DVELUM_ROOT . '/';
	  $this->_wwwRoot = '/';
	  $docRoot = $_SERVER['DOCUMENT_ROOT'];

	  if($docRoot[(strlen($docRoot)-1)]==='/')
	      $docRoot = substr($docRoot, 0,-1);

	  $uri = $_SERVER['REQUEST_URI'];
	  $parts = explode('/', $uri);
	  for($i=1;$i<sizeof($parts);$i++)
	  {
	    if($parts[$i]==='install'){
	      break;
	    }
	    $this->_wwwRoot.=$parts[$i].'/';
	  }
	}

	public function run()
	{
		$action = Request::post('action', 'string', false);
		$lang = $this->_session->get('lang');

		if(!empty($lang))
			$this->_lang = $lang;
		else
			$this->_lang = 'en';

		$this->_dictionary = require './lang/'.$this->_lang.'.php';

		if($action !== false && method_exists($this, $action . 'Action'))
	 		$this->_action = strtolower($action) . 'Action';
	 	else
	 		$this->_action = 'indexAction';
	 	$this->{$this->_action}();
	}

	public function setlangAction()
	{
		$lang = Request::post('lang', 'string', false);

		if(empty($lang))
			Response::jsonError();

		$this->_session->set('lang', $lang);
		Response::jsonSuccess();
	}

	public function indexAction()
	{
		$this->_template = new Template();
		$this->_template->url = './index.php';
		$this->_template->lang = $this->_lang;
		$this->_template->dictionary = $this->_dictionary;
		$this->_template->wwwRoot = $this->_wwwRoot;

		if($this->_lang == 'ru')
		  $this->_template->license = file_get_contents('./templates/gpl-3.0_ru.txt');
		else
		  $this->_template->license = file_get_contents('./templates/gpl-3.0_en.txt');

		echo $this->_template->render('./templates/install.php');
	}

	protected function _checkWritable($path, $required, $msg){
		$data = array();
		$data['title'] = $this->_dictionary['WRITE_PERMISSIONS'] . ' ' . $path;
		@chmod($this->_docRoot . $path, 0775);
		if(is_writable($this->_docRoot . $path)){
			$data['success'] = true;
		}else{
			$data['success'] = !$required;
			$data['error'] = $msg;
		}
		return $data;
	}

	protected function _checkExtention($extention, $required, $msg = false){
		$data = array();
		$data['title'] = $this->_dictionary['LIBRARY_CHECK'] . ' ' . $extention;
		if (in_array($extention, $this->_phpExt, true)) {
			$data['success'] = true;
		} else {
			$data['success'] = !$required;
			$data['error'] = $msg ? $msg : $this->_dictionary['LIBRARY_NOT_EXISTS'];
		}
		return $data;
	}

	public function firstcheckAction() {
		$data = array();
		$this->_phpExt = get_loaded_extensions();

		/**
		 * Check for php version
		 */
		$data['items'][0]['title'] = $this->_dictionary['PHP_V'] . ' >= 5.3.0';
		if (version_compare(PHP_VERSION, '5.3.0', '<')) {
			$data['items'][0]['success'] = false;
			$data['items'][0]['error'] = $this->_dictionary['UR_PHP_V'] . ' ' . PHP_VERSION;
		} else
			$data['items'][0]['success'] = true;

		$extentions = array(
			array(
				'name'=>'memcache',
				'accessType'=>'allowed',
				'msg'=>$this->_dictionary['PERFORMANCE_WARNING']
			),
			array(
				'name'=>'mysqli',
				'accessType'=>'required',
				'msg'=>false
			),
			array(
				'name'=>'gd',
				'accessType'=>'required',
				'msg'=>false
			),
			array(
				'name'=>'curl',
				'accessType'=>'allowed',
				'msg'=>$this->_dictionary['DEPLOY_WARNING']
			),
			array(
				'name'=>'zip',
				'accessType'=>'allowed',
				'msg'=>$this->_dictionary['WARNING']
			),
			array(
				'name' => 'mcrypt',
				'accessType'=>'allowed',
				'msg'=>$this->_dictionary['WARNING']
			),
		);

		$writablePaths = array(
			array(
				'path'=>'system/config/reports',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/packages',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config/objects/',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config/dictionary/'.$this->_lang.'/',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config/layouts',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config',
				'accessType'=>'required'
			),
			array(
				'path'=>'js/lang',
				'accessType'=>'required'
			),
			array(
				'path'=>'js/cache',
				'accessType'=>'required'
			),
			array(
				'path'=>'js/app/actions',
				'accessType'=>'required'
			),
			array(
				'path'=>'js/syscache',
				'accessType'=>'required'
			),
			array(
				'path'=>'media',
				'accessType'=>'required'
			),
			array(
				'path'=>'.backups',
				'accessType'=>'required'
			),
            array(
                'path'=>'.tmp',
                'accessType'=>'required'
            ),
			array(
				'path'=>'.log',
				'accessType'=>'required'
			),
			array(
				'path'=>'../deploy',
				'accessType'=>'allowed'
			),
			array(
				'path'=>'install',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config/main.php',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config/db/dev/default.php',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config/db/prod/default.php',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config/db/dev/error.php',
				'accessType'=>'required'
			),
			array(
				'path'=>'system/config/db/prod/error.php',
				'accessType'=>'required'
			)
		);

		foreach ($extentions as $v){
			switch ($v['accessType']){
				case 'required':
					$data['items'][] = $this->_checkExtention($v['name'], true, $v['msg']);
					break;
				case 'allowed':
					$data['items'][] = $this->_checkExtention($v['name'], false, $v['msg']);
					break;
			}
		}

		foreach ($writablePaths as $v){
			switch ($v['accessType']){
				case 'required':
					$data['items'][] = $this->_checkWritable($v['path'], true, $this->_dictionary['RECORDING_IS_PROHIBITED']);
					break;
				case 'allowed':
					$data['items'][] = $this->_checkWritable($v['path'], false, $this->_dictionary['RECORDING_IS_PROHIBITED_OK']);
					break;
			}
		}

		$data['info'] = $this->_dictionary['WARNING_ABOUT_RIGHTS'];

		Response::jsonSuccess($data);
	}
	public function dbcheckAction() {
		$host = Request::post('host', 'str', '');
		$port = Request::post('port', 'int', 0);
		$prefix = Request::post('prefix', 'str', '');

		$installDocs = Request::post('install_docs' , 'boolean' , false);
		$this->_session->set('install_docs' , $installDocs);

		$params = array(
		    'host'           => $host,
		    'username'       => Request::post('username', 'str', false),
		    'password'       => Request::post('password', 'str', false),
		    'dbname'         => Request::post('dbname', 'str', false),
			'adapter'  => 'Mysqli',
			'adapterNamespace' => 'Db_Adapter'
		);

		if ($port != 0)
			$params['port'] = $port;

		$flag = false;
		if ($params['host'] && $params['username'] && $params['dbname'])
			try {
				$zendDb = Zend_Db::factory('Mysqli', $params);
				$zendDb->getServerVersion();
				$data['success'] = true;
				$data['msg'] = $this->_dictionary['SUCCESS_DB_CHECK'];

				$flag = true;

			} catch (Exception $e) {
				$data['success'] = false;
				$data['msg'] = $this->_dictionary['FAILURE_DB_CHECK'];
			}
		else {
			$data['success'] = false;
			$data['msg'] = $this->_dictionary['REQUIRED_DB_SETTINGS'];
		}

		if ($flag)
			try {

				$configs = array(
					$this->_docRoot . 'system/config/db/prod/default.php',
					$this->_docRoot . 'system/config/db/dev/default.php',
					$this->_docRoot . 'system/config/db/prod/error.php',
					$this->_docRoot . 'system/config/db/dev/error.php',
				);

				foreach($configs as $path)
				{
					if (Config_File_Array::create($path) === false)
						throw new Exception();

					$config = Config::factory(Config::File_Array, $path);
					$config->setData($params);
					$config->set('charset', 'UTF8');
					$config->set('prefix', $prefix);
					if (!$config->save())
						throw new Exception();
				}

			} catch (Exception $e) {
				$data['success'] = false;
				$data['msg'] = $this->_dictionary['CONNECTION_SAVE_FAIL'];
			}

		Response::jsonSuccess($data);
	}
	/**
	 * Create Database tables
	 */
	public function createtablesAction()
	{
		$mainCfgPath = $this->_docRoot . 'system/config/main.php';
		$config = include $mainCfgPath;
		$inlineConfig = Config::factory(Config::Simple, 'main');
		$inlineConfig->setData($config);

		$app = new Application($inlineConfig);
		$app->init();

		$zendDb = Model::getGlobalDbConnection();

		$installDocs = $this->_session->get('install_docs');


		$config = Config::factory(Config::File_Array, $this->_docRoot . 'install/cfg/cfg.php')->__toArray();

		if($installDocs){
			try{
				$dbConfig = $zendDb->getConfig();
				$cmd = 'mysql -h'.escapeshellarg($dbConfig['host']).' -P '.escapeshellarg($dbConfig['port']).' -u' . escapeshellarg($dbConfig['username']) . ' -p' . escapeshellarg($dbConfig['password']) . ' -D' . escapeshellarg($dbConfig['dbname']).' < '. escapeshellarg($this->_docRoot.$config['docs_sql']);

				if(system($cmd) === false)
					throw new Exception('Cannot exec shell command: '.$cmd);

			}catch (Exception $e){
				Response::jsonError($this->_dictionary['INSTALL_DOCS_ERROR'] .' '. $e->getMessage());
			}
		}

		$paths = File::scanFiles($this->_docRoot . $config['configsPath'], array('.php'),false, File::Files_Only);

		foreach ($paths as &$v)
			$v = substr(basename($v), 0, -4);

		unset($v);

		$buildErrors = array();

		if(!empty($paths))
		{
			foreach ($paths as $v)
			{
				$dbObjectBuilder = new Db_Object_Builder($v);
				if(!$dbObjectBuilder->build())
					$buildErrors[] = $v;
			}
		}

		if(!empty($buildErrors))
			Response::jsonError($this->_dictionary['BUILD_ERR'] . ' ' . implode(', ', $buildErrors));
		else
			Response::jsonSuccess('', array('msg'=>$this->_dictionary['DB_DONE']));
	}

	public function setuserpassAction()
	{
		$pass = Request::post('pass', 'str', '');
		$passConfirm = Request::post('pass_confirm', 'str', '');
		$lang = Request::post('lang', 'string', 'en');
		$timezone = Request::post('timezone', 'string', '');
		$email = Request::post('adm_email', 'string', '');
		$adminpath = strtolower(Request::post('adminpath', 'string', ''));
		$user = Request::post('user',  'str', '');


		$errors = array();

		if(!strlen($user))
		  $errors[] = $this->_dictionary['INVALID_USERNAME'];

		if(empty($pass) || empty($passConfirm) || $pass != $passConfirm)
			$errors[] = $this->_dictionary['PASS_MISMATCH'];

		$timezones = timezone_identifiers_list();
		if(empty($timezone) || !in_array($timezone, $timezones, true))
			$errors[] = $this->_dictionary['TIMEZOME_REQUIRED'];

		if(!Validator_Email::validate($email))
			$errors[] = $this->_dictionary['INVALID_EMAIL'];

		if(!Validator_Alphanum::validate($adminpath)  || is_dir($this->_docRoot .'system/app/Backend/'.ucfirst($adminpath)))
			$errors[] = $this->_dictionary['INVALID_ADMINPATH'];

		if(!empty($errors))
			Response::jsonError(implode(', ', $errors));


		$salt = Utils::getRandomString(4) . '_' . Utils::getRandomString(4);

		$mainCfgPath = $this->_docRoot . 'system/config/main.php';
		$encConfigPath = $this->_docRoot . 'system/config/objects/enc/config.php';
		$config = include $mainCfgPath;
		$inlineConfig = Config::factory(Config::Simple, 'main');
		$inlineConfig->setData($config);

		$app = new Application($inlineConfig);
		$app->init();




		$mainCfg = '<?php
$docRoot = DVELUM_ROOT;

$language = \'#lang#\';

return array(
		\'docroot\' => $docRoot ,
		/*
		 * Development mode
		 * 0 - production
		 * 1 - development
		 * 2 - test (development mode + test DB)
		 */
		\'development\' =>1,
		/*
		 * Development version (used by use_orm_build_log)
		 */
		\'development_version\'=>\'0.1\',
		/*
		 * Write SQL commands when updating Database structure.
		 * It can help to determine if there have been performed any rename operations.
		 * Please note that renaming operations in ORM interface causes loss of data
		 * during server synchronization, so it\'s better to use SQL log.
		 */
		\'use_orm_build_log\'=>true,
		/*
		 * ORM SQL logs path
		 */
		\'orm_log_path\'=>$docRoot.\'/.log/orm/\',
		/*
		 * Background tasks log path
		 */
		\'task_log_path\'=>$docRoot.\'/.log/task/\',
		/*
		 * ORM system object used as links storage
		 */
		\'orm_links_object\'=>\'Links\',
		/*
		 * ORM system object used as history storage
		 */
		\'orm_history_object\'=>\'Historylog\',
		/*
		 * File uploads path
		 */
		\'uploads\' => $docRoot . \'/media/\' ,
		/*
		 * Admin panel URL
		 * For safety reasons adminPath may be changed, however,
		 * keep in mind that IDE builds full paths in the current version,
		 * thus, they would have to be manually updated in the projects.
		 */
		\'adminPath\' => \'#adminpath#\' ,
		/*
		 * Templates directory
		 */
		\'templates\' => $docRoot . \'/templates/\' ,
		/*
		 * Url paths delimiter  "_" , "-" or "/"
		 */
		\'urlDelimiter\' => \'/\',
		\'urlExtension\' => \'.html\' ,
		/*
		 * System language
		 * Please note. Changing the language will switch ORM storage settings.
		 */
		\'language\' => $language ,
		\'system\' => $docRoot . \'/system/\',
		\'lang_path\' => $docRoot . \'/system/lang/\' ,
		\'js_lang_path\' => $docRoot. \'/js/lang/\',
		\'salt\' => \'#salt#\' ,
		\'timezone\' => \'#timezone#\' ,
		\'jsCacheUrl\' => \'js/cache/\' ,
		\'jsCachePath\' => $docRoot . \'/js/cache/\' ,

		\'jsCacheSysUrl\' => \'js/syscache/\',
		\'jsCacheSysPath\' => \'./js/syscache/\',
		 /*
		  * Сlear the object version history when deleting an object.
		  * The recommended setting is “false”.  Thus, even though the object has been deleted,
		  * it can be restored from the previous control system revision.
		  * If set to "true", the object and its history will be  totally removed. However,
		  * this allows you to get rid of redundant records in the database.
		  */
		\'vc_clear_on_delete\' => false,
		/*
		 * Main directory for config files
		 */
		\'configs\' => $docRoot . \'/system/config/\' ,  // configs path
		/*
		 * ORM configs directory
		 */
		\'object_configs\' => $docRoot . \'/system/config/objects/\' ,
		/*
		 * Report configs directory
		 */
		\'report_configs\' => $docRoot . \'/system/config/reports/\' ,
		/*
		 * Modules directory
		 */
		\'modules\'=> $docRoot . \'/system/config/modules/\',
		/*
		 * Backend modules config file
		 */
		\'backend_modules\'=> $docRoot . \'/system/config/modules/\'.$language.\'/backend_modules.php\',
		/*
		 * Backend controllers path
		 */
		\'backend_controllers\'=>$docRoot . \'/system/app/Backend/\',
		/*
		 * Frontend controllers path
		 */
		\'frontend_controllers\'=>$docRoot . \'/system/app/Frontend/\',
		/*
		 * Frontend modules config file
		 */
		\'frontend_modules\'=>$docRoot . \'/system/config/modules/\'.$language.\'/frontend_modules.php\',
		/*
		 * Application path
		 */
		\'application_path\'=>$docRoot . \'/system/app/\',
		/*
		 * Blocks path
		 */
		\'blocks\'=>$docRoot . \'/system/app/Block/\',
		 /*
		  * Dictionary configs directory depending on localization
		  */
		\'dictionary\'=>$docRoot . \'/system/config/dictionary/\'.$language.\'/\',
		/*
		 * Dictionary directory
		 */
		\'dictionary_folder\'=>$docRoot . \'/system/config/dictionary/\',
		 /*
		  * Backups directory
		  */
		\'backups\' => $docRoot . \'/.backups/\' ,
		\'tmp\' => $docRoot . \'/.tmp/\' ,
		\'mysqlExecPath\' => \'mysql\',
		\'mysqlDumpExecPath\' => \'mysqldump\',
		/*
		 * the type of frontend router with two possible values:
		 * \'module\' — using tree-like page structure  (‘Pages’ section of the administrative panel);
		 * \'path\' — the router based on the file structure of client controllers.
		 */
		\'frontend_router_type\'=>\'module\',// \'module\',\'path\',\'config\'
		/*
		 * Use memcached
		 */
		\'use_cache\' => false,
		/*
		 * Hard caching time (without validation) for frondend , seconds
		 */
		\'frontend_hardcache\'=>30,
		\'themes\' => $docRoot . \'/templates/public/\' ,
		\'usersOnline\' => false, //Collect users online info,
		// Autoloader config
		\'autoloader\' => array(
			 // Paths for autoloading
			 \'paths\'=> array(
			    \'./system/rewrite\',
				\'./system/app\',
				\'./system/library\',
			 ),
		  /*
		   * Use class map
		   */
		  \'useMap\'=>true,
			 /*
			  *	Use precompiled code packages
			  *	requires useMap property to be set to true
			  */
			 \'usePackages\' => false,
		   // Use class map (Reduce IO load during autoload)
			 // Class map file path (string / false)
			 \'map\' => $docRoot . \'/system/config/class_map.php\',
			 // Class map file path (with packages)
			 \'mapPackaged\'=> $docRoot . \'/system/config/class_map_packaged.php\',
			 // Packages config path
			 \'packagesConfig\'=>	$docRoot . \'/system/config/packages.php\',
		),
		/*
		 * Stop the site with message "Essential maintenance in progress. Please check back later."
		 */
		\'maintenance\' => false,
		/*
		 * Show debug panel (development mode)
		 */
		\'debug_panel\'=> false,
		/*
		 * HTML WYSIWYG Editor
		 * default  - ckeditor
		 */
		\'html_editor\' =>\'ckeditor\',
		/*
		 * Use the console command to compile the file system map
		 * (accelerates the compilation process; works only on Linux systems;
		 * execution of the system function should be allowed).
		 */
		\'deploy_use_console\'=>false,
		/*
		 *  Use hard cache expiration time defined in frontend_hardcache for caching blocks;
		 *  allows to reduce the cache time of dynamic blocks;
		 *  is used if there are not enough triggers for cache invalidation
		 */
		\'blockmanager_use_hardcache_time\'=>false,
		/*
		 * Use foreign keys
		 */
		\'foreign_keys\' => false,
		/*
		 * Allow external modules
		 */
		\'allow_externals\' => false,
		/*
		 * www root
		 */
		\'wwwroot\' =>\'#wwwroot#\',
		/*
		 * External modules path (Experimental)
		 */
		\'external_modules\' => \'./system/external/\',
		/*
		 * Log Db_Object errors
		 */
		\'db_object_error_log\' =>true,
		\'db_object_error_log_path\'=>$docRoot.\'/.log/error/db_object.error.log\',
		/*
		* Get real rows count for innodb tables (COUNT(*))
		* Set it "false" for large data volumes
		*/
		\'orm_innodb_real_rows_count\'=>false,
		/*
		* Directories for storing data base connection settings as per the system mode
		*/
		\'db_configs\' => array(
		      /* key as development mode code */
		      0 => array(
			      \'title\'=>\'PRODUCTION\',
			      \'dir\'=> $docRoot . \'/system/config/db/prod/\'
		      ),
		      1 => array(
			      \'title\'=>\'DEVELOPMENT\',
			      \'dir\'=> $docRoot . \'/system/config/db/dev/\'
		      ),
		      2=> array(
			      \'title\'=>\'TEST\',
			      \'dir\'=> $docRoot . \'/system/config/db/test/\'
		      ),
		),
		/*
         * Check modification time for template file. Invalidate cache
         */
        \'template_check_mtime\' => true,
    	/*
    	 * ORM system object used as version storage
    	 */
    	\'orm_version_object\' => \'Vc\',
		/*
         * Db_Object for error log 
         */
        \'erorr_log_object\'=>\'error_log\'
);';
		$mainCfg = str_replace(	array(
									'#salt#',
									'#timezone#',
									'#lang#',
									'#adminpath#',
		                            '#wwwroot#'
								),
								array(
									$salt,
									$timezone,
									$lang,
									$adminpath,
								    $this->_wwwRoot,
								), $mainCfg);


		if(!@file_put_contents($mainCfgPath, $mainCfg))
			Response::jsonError($this->_dictionary['CANT_WRITE_FS']);

		$key = md5(uniqid(md5(time())));
		$encConfig = '
		<?php
			return array(
				\'key\'=>\''.$key.'\',
				\'iv_field\'=>\'enc_key\'
			);
		';
		if(!@file_put_contents($encConfigPath ,$encConfig ))
			Response::jsonError($this->_dictionary['CANT_WRITE_FS'] . ' '.$encConfigPath);

		Utils::setSalt($salt);
		$mainCfgPath = $this->_docRoot . 'system/config/main.php';
		$config = include $mainCfgPath;
		$inlineConfig = Config::factory(Config::Simple, 'main');
		$inlineConfig->setData($config);
		Registry::set('main', $inlineConfig, 'config');

		if(!$this->_prepareRecords($pass, $email, $user))
			Response::jsonError($this->_dictionary['CANT_WRITE_TO_DB']);

		ob_start();
		File::rmdirRecursive($this->_docRoot . 'install', true);
		ob_end_clean();

		Response::jsonSuccess(array('link'=>Registry::get('main' , 'config')->get('adminPath')));
	}

	protected function _prepareRecords($adminPass , $adminEmail, $adminName)
	{
		try
		{
            $toCleanModels = array(
                Model::factory('User'),
                Model::factory('Group'),
                Model::factory('Permissions'),
                Model::factory('Page')
            );

            foreach ($toCleanModels as $model)
                $model->getDbConnection()->delete($model->table());

			// Add group
			$group = new Db_Object('Group');
			$group->setValues(array(
					'title'=>$this->_dictionary['ADMINISTRATORS'] ,
					'system'=>true
			));
			$group->save(true, false);
			$groupId = $group->getId();

			// Add user
			$user = new Db_Object('user');

			$user->setValues(array(
					'name' =>'Admin',
					'email' => $adminEmail,
					'login' => $adminName,
					'pass' => Utils::hash($adminPass),
					'enabled' => true,
					'admin' => true,
					'registration_date' => date('Y-m-d H:i:s'),
					'confirmation_code' => md5(date('Y-m-d H:i:s')),
					'group_id' => $groupId,
					'confirmed' => true,
					'avatar' => '',
					'registration_ip' => $_SERVER['REMOTE_ADDR'],
					'last_ip' => $_SERVER['REMOTE_ADDR'],
					'confirmation_date' =>date('Y-m-d H:i:s')
				)
			);
			$userId = $user->save(false, false);
			if(!$userId)
				return false;

			// Add permissions
			$permissionsModel = Model::factory('Permissions');
			$modulesManager = new Backend_Modules_Manager();
			$modules = $modulesManager->getList();

			foreach ($modules as $name=>$config)
				if(!$permissionsModel->setGroupPermissions($groupId , $name , true, true , true , true))
					return false;

			$u = User::getInstance();
			$u->setId($userId);
			$u->setAuthorized();

			// Add index Page
			$page = new Db_Object('Page');
			$page->setValues(array(
					'code'=>'index',
					'is_fixed'=>1,
					'html_title'=>'Index',
					'menu_title'=>'Index',
					'page_title'=>'Index',
					'meta_keywords'=>'',
					'meta_description'=>'',
					'parent_id'=>null,
					'text' =>'[Index page content]',
					'func_code'=>'',
					'order_no' => 1,
					'show_blocks'=>true,
					'published'=>true,
					'published_version'=>0,
					'editor_id'=>$userId,
					'date_created'=>date('Y-m-d H:i:s'),
					'date_updated'=>date('Y-m-d H:i:s'),
					'author_id'=>$userId,
					'blocks'=>'',
					'theme'=>'default',
					'date_published'=>date('Y-m-d H:i:s'),
					'in_site_map'=>true,
					'default_blocks'=>true
			));
			if(!$page->save(true, false))
				return false;

			//404 Page
			$page = new Db_Object('Page');
			$page->setValues(array(
			    'code'=>'404',
			    'is_fixed'=>1,
			    'html_title'=>'Error 404. Page not found',
			    'menu_title'=>'404',
			    'page_title'=>'We cannot find the page you are looking for.',
			    'meta_keywords'=>'',
			    'meta_description'=>'',
			    'parent_id'=>null,
			    'text' =>'We cannot find the page you are looking for.',
			    'func_code'=>'',
			    'order_no' => 2,
			    'show_blocks'=>true,
			    'published'=>true,
			    'published_version'=>0,
			    'editor_id'=>$userId,
			    'date_created'=>date('Y-m-d H:i:s'),
			    'date_updated'=>date('Y-m-d H:i:s'),
			    'author_id'=>$userId,
			    'blocks'=>'',
			    'theme'=>'default',
			    'date_published'=>date('Y-m-d H:i:s'),
			    'in_site_map'=>false,
			    'default_blocks'=>true
			));
			if(!$page->save(true, false))
			  return false;

			//API Page
			$page = new Db_Object('Page');
			$page->setValues(array(
			        'code'=>'api',
			        'is_fixed'=>1,
			        'html_title'=>'API [System]',
			        'menu_title'=>'API',
			        'page_title'=>'API [System]',
			        'meta_keywords'=>'',
			        'meta_description'=>'',
			        'parent_id'=>null,
			        'text' =>'',
			        'func_code'=>'api',
			        'order_no' => 3,
			        'show_blocks'=>false,
			        'published'=>true,
			        'published_version'=>0,
			        'editor_id'=>$userId,
			        'date_created'=>date('Y-m-d H:i:s'),
			        'date_updated'=>date('Y-m-d H:i:s'),
			        'author_id'=>$userId,
			        'blocks'=>'',
			        'theme'=>'default',
			        'date_published'=>date('Y-m-d H:i:s'),
			        'in_site_map'=>false,
			        'default_blocks'=>false
			));

			if(!$page->save(true, false))
			    return false;

			return true;

		} catch (Exception $e){
			return false;
		}
	}
}
