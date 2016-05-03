<?php
abstract class Frontend_Controller_Authorised extends Frontend_Controller
{
  /**
   * Frontend configuration object
   * @var Config_Abstract
   */
  protected $_configFrontend;
  /**
   * User instance
   * @var User
   */
  protected $_user;
  
  public function __construct()
  {
    parent::__construct();
     
    $this->_configFrontend = Config::storage()->get('frontend.php');
    
    if(Request::get('logout' , 'boolean' , false)){
      User::getInstance()->logout();
      session_destroy();
      if(!Request::isAjax())
        Response::redirect(Request::url(array('index'),true));
    }
    $this->checkAuth();
  }
  
  /**
   * Check user permissions and authentication
   */
  public function checkAuth()
  {
    $user = User::getInstance();
    $uid = false;
  
    if($user->isAuthorized())
      $uid = $user->id;
  
    if(!$uid){
      if(Request::isAjax())
        Response::jsonError($this->_lang->MSG_AUTHORIZE);
      else
        $this->loginAction();
    }
    /*
     * Check CSRF token
    */
    if($this->_configFrontend->get('use_csrf_token') && Request::hasPost()){
      $csrf = new Security_Csrf();
      $csrf->setOptions(
              array(
                      'lifetime' => $this->_configFrontend->get('use_csrf_token_lifetime'),
                      'cleanupLimit' => $this->_configFrontend->get('use_csrf_token_garbage_limit')
              ));
  
      if(!$csrf->checkHeader() && !$csrf->checkPost())
        $this->_errorResponse($this->_lang->MSG_NEED_CSRF_TOKEN);
    }
    $this->_user = $user;
  }
  
  /**
   * Send JSON error message
   *
   * @return string
   */
  protected function _errorResponse($msg)
  {
    if(Request::isAjax())
      Response::jsonError($msg);
    else
      Response::redirect(Request::url(array('index'),true));
  }
  
  /**
   * Show login form
   */
  protected function loginAction()
  {
    $template = new Template();
    $template->set('wwwRoot' , $this->_configMain->get('wwwroot'));
    $template->resource = Resource::getInstance();
    Response::put($template->render('public/backoffice_login.php'));
    Application::close();
  }
}