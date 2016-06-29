<?php

/**
 * Compile JS lang files & dictionaries
 */
class Console_Js_Lang extends Console_Action
{
    /**
     * (non-PHPdoc)
     * @see Console_Action::run()
     */
    public function run()
    {
        $langManager = new Backend_Localization_Manager($this->appConfig);

        try{
            $langManager->compileLangFiles();
            return true;
        }catch (Exception $e){
            echo $e->getMessage();
            return false;
        }
    }
}