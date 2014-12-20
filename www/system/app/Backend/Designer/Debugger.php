<?php 
class Backend_Designer_Debugger extends Backend_Designer_Sub
{
    
    public function indexAction()
    {
        $project = $this->_getProject();
        $template = new Template();
        $template->project = $project;
        $template->disableCache();
        echo $template->render(Application::getTemplatesPath().'designer/project_debug.php');
    }
}