<?php
class Backend_Designer_Sub_Code extends Backend_Designer_Sub
{
    /**
     * Get JS code for object
     */
    public function objectcodeAction()
    {
        $object = Request::post('object' , 'string' , '');
        $project = $this->_getProject();

        if(! $project->objectExists($object))
            Response::jsonError($this->_lang->get('WRONG_REQUEST'));

        $projectCfg = $project->getConfig();
        Ext_Code::setRunNamespace($projectCfg['runnamespace']);
        Ext_Code::setNamespace($projectCfg['namespace']);

        $templates = $this->_config->get('templates');
        $replaces = array(
            array(
                'tpl' => $templates['wwwroot'] ,
                'value' => $this->_configMain->get('wwwroot')
            ) ,
            array(
                'tpl' => $templates['adminpath'] ,
                'value' => $this->_configMain->get('adminPath')
            ) ,
            array(
                'tpl' => $templates['urldelimiter'] ,
                'value' => $this->_configMain->get('urlDelimiter')
            )
        );

        $code = $project->getObjectCode($object , $replaces);
        Response::jsonSuccess($code);
    }

    /**
     * Get JS code for project
     */
    public function projectcodeAction()
    {
        $project = $this->_getProject();
        $projectCfg = $project->getConfig();
        $templates = $this->_config->get('templates');
        $replaces = array(
            array(
                'tpl' => $templates['wwwroot'] ,
                'value' => $this->_configMain->get('wwwroot')
            ) ,
            array(
                'tpl' => $templates['adminpath'] ,
                'value' => $this->_configMain->get('adminPath')
            ) ,
            array(
                'tpl' => $templates['urldelimiter'] ,
                'value' => $this->_configMain->get('urlDelimiter')
            )
        );
        Ext_Code::setRunNamespace($projectCfg['runnamespace']);
        Ext_Code::setNamespace($projectCfg['namespace']);
        Response::jsonSuccess($project->getCode($replaces));
    }
}