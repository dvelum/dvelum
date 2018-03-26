<?php
use Dvelum\View;
use Dvelum\Config;

class Backend_Designer_Debugger extends Backend_Designer_Sub
{
    public function indexAction()
    {
        $project = $this->_getProject();
        $template = View::factory();
        $template->project = $project;
        $template->disableCache();

        $designerConfig = Config::storage()->get('designer.php');
        // change theme
        $designerTheme = $designerConfig->get('application_theme');
        $page = Page::getInstance();
        $page->setTemplatesPath('system/' . $designerTheme. '/');

        echo $template->render($page->getTemplatesPath().'designer/project_debug.php');
    }
}