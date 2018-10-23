<?php

use Dvelum\Config;

class Backend_Designer_Code
{
    /**
     * Get controller url
     * @param string $controllerName
     * @return string
     */
    static public function getControllerUrl($controllerName)
    {
        $frontConfig = Config::storage()->get('frontend.php');

        $appCfg = Config::storage()->get('main.php');
        $designerConfig = Config::storage()->get($appCfg->get('configs') . 'designer.php');
        $templates = $designerConfig->get('templates');

        if (!class_exists($controllerName)) {
            $namespaceController = str_replace('_','\\',$controllerName);
            if(class_exists($namespaceController)){
                $controllerName = $namespaceController;
            }else{
                return '';
            }
        }


        $reflector = new ReflectionClass($controllerName);

        if (!$reflector->isSubclassOf('\\Dvelum\\App\\Backend\\Controller') && !$reflector->isSubclassOf('Backend_Controller') && !$reflector->isSubclassOf('Frontend_Controller')) {
            return '';
        }

        $url = [];

        $manager = new Modules_Manager();

        if ($reflector->isSubclassOf('Backend_Controller') || $reflector->isSubclassOf('\\Dvelum\\App\\Backend\\Controller')) {
            $url[] = $templates['adminpath'];
            $url[] = $manager->getModuleName($controllerName);
        } elseif ($reflector->isSubclassOf('Frontend_Controller') || $reflector->isSubclassOf('\\Dvelum\\App\\Frontend\\Controller')) {
            $routerType = $frontConfig->get('router');
            if ($routerType == 'Module') {
                $module = self::_moduleByClass($controllerName);
                if ($module !== false) {
                    $urlcode = Model::factory('Page')->getCodeByModule($module);
                    if ($urlcode !== false) {
                        $url[] = $urlcode;
                    }
                }

            } elseif ($routerType == 'Path') {
                $paths = explode('_', str_replace(array('Frontend_'), '', $controllerName));
                $pathsCount = count($paths) - 1;
                if ($paths[$pathsCount] === 'Controller') {
                    $paths = array_slice($paths, 0, $pathsCount);
                }

                $url = array_merge($url, $paths);
            } elseif ($routerType == 'Config') {
                $urlCode = self::_moduleByClass($controllerName);
                if ($urlCode !== false) {
                    $url[] = $urlCode;
                }
            }
        }
        $url[] = '';
        Request::setConfigOption('urldelimiter', $templates['urldelimiter']);
        Request::setConfigOption('wwwroot', $templates['wwwroot']);

        $url = Request::url($url, false);
        Request::setConfigOption('urldelimiter', $appCfg['urlDelimiter']);
        Request::setConfigOption('wwwroot', $appCfg['wwwroot']);

        return $url;
    }

    /**
     * Get possible actions from Controller class.
     * Note! Code accelerator (eaccelerator, apc, xcache, etc ) should be disabled to get comment line.
     * Method returns only public methods that ends with "Action"
     * @param string $controllerName
     * @return array like array(
     *        array(
     *            'name' => action name without "Action" postfix
     *            'comment'=> doc comment
     *        )
     * )
     */
    static public function getPossibleActions($controllerName)
    {
        $manager = new Modules_Manager();
        $appCfg = Config::storage()->get('main.php');
        $designerConfig = Config::storage()->get($appCfg->get('configs') . 'designer.php');

        $templates = $designerConfig->get('templates');

        $reflector = new ReflectionClass($controllerName);

        if (!$reflector->isSubclassOf('\\Dvelum\\App\\Backend\\Controller') && !$reflector->isSubclassOf('Backend_Controller') && !$reflector->isSubclassOf('Frontend_Controller')) {
            return array();
        }

        $actions = array();
        $methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

        $url = array();

        if ($reflector->isSubclassOf('\\Dvelum\\App\\Backend\\Controller') || $reflector->isSubclassOf('Backend_Controller')) {
            $url[] = $templates['adminpath'];
            $url[] = $manager->getModuleName($controllerName);
        } elseif ($reflector->isSubclassOf('Frontend_Controller')) {

            if ($appCfg['frontend_router_type'] == 'module') {

                $module = self::_moduleByClass($controllerName);
                if ($module !== false) {
                    $urlcode = Model::factory('Page')->getCodeByModule($module);
                    if ($urlcode !== false) {
                        $url[] = $urlcode;
                    }
                }
            } elseif ($appCfg['frontend_router_type'] == 'path') {
                $paths = explode('_', str_replace(array('Frontend_'), '', $controllerName));
                $pathsCount = count($paths) - 1;

                if ($paths[$pathsCount] === 'Controller') {
                    $paths = array_slice($paths, 0, $pathsCount);
                }

                $url = array_merge($url, $paths);
            } elseif ($appCfg['frontend_router_type'] == 'config') {
                $urlCode = self::_moduleByClass($controllerName);
                if ($urlCode !== false) {
                    $url[] = $urlCode;
                }
            }
        }

        if (!empty($methods)) {
            Request::setDelimiter($templates['urldelimiter']);
            Request::setConfigOption('wwwRoot', $templates['wwwroot']);

            foreach ($methods as $method) {
                if (substr($method->name, -6) !== 'Action') {
                    continue;
                }

                $actionName = substr($method->name, 0, -6);
                $paths = $url;
                $paths[] = $actionName;

                $actions[] = array(
                    'name' => $actionName,
                    'code' => $method->name,
                    'url' => Request::url($paths, false),
                    'comment' => self::_clearDocSymbols($method->getDocComment())
                );
            }

            Request::setDelimiter($appCfg['urlDelimiter']);
            Request::setConfigOption('wwwRoot', $appCfg['wwwroot']);
        }
        return $actions;
    }

    static protected function _moduleByClass($class)
    {
        $modules = Config::storage()->get('main.php')->get('frontend_modules');
        if (!empty($modules)) {
            foreach ($modules as $k => $config) {
                if ($config['class'] === $class) {
                    return $k;
                }
            }
        }
        return false;
    }

    /**
     * Clear string from comment symbols
     * @param string $string
     */
    static protected function _clearDocSymbols($string)
    {
        return str_replace(array('/*', '*/', '*'), '', $string);
    }
}