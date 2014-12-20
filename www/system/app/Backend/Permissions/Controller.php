<?php
class Backend_Permissions_Controller extends Backend_Controller
{
    public function indexAction()
    {
        Response::jsonError(Lang::lang()->get('WRONG_REQUEST'));
    }

    public function listAction()
    {
        $user = User::getInstance();
        Response::jsonSuccess($user->getPermissions());
    }
}