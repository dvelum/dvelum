<?php

/**
 * Class Console_Docs_Generate
 * Console Action
 * Generate new version of documentation
 */
class Console_Docs_Generate extends Console_Action
{
    /**
     * (non-PHPdoc)
     * @see Console_Action::run()
     */
    public function run()
    {
        if(!$this->appConfig->get('development')){
            echo 'Use development mode';
            return;
        }

        ini_set('memory_limit' , '256M');

        $sysDocsCfg = Config::storage()->get('sysdocs.php');
        $sysDocs = new Sysdocs_Generator($sysDocsCfg);
        $autoloaderCfg = Config::storage()->get('autoloader.php')->__toArray();
        $sysDocs->setAutoloaderPaths($autoloaderCfg['paths']);

        if(isset($this->params[0]) && $this->params[0]==='locale'){
            $sysDocs->migrateLocale();
        }else{
            $sysDocs->run();
        }
    }
}
