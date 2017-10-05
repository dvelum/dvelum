<?php
declare(strict_types=1);

namespace Dvelum\App\Console\Js;
use Dvelum\App\Console;
/**
 * Compile JS lang files & dictionaries
 */
class Lang extends Console\Action
{
    public function action() : bool
    {
        $langManager = new \Backend_Localization_Manager($this->appConfig);

        try{
            $langManager->compileLangFiles();
            return true;
        }catch (\Exception $e){
            echo $e->getMessage();
            return false;
        }
    }
}