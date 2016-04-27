<?php
interface Externals_Installer{
    /**
     * Run module post-install actions
     * @param Config_Abstract $applicationConfig
     * @return boolean - success flag
     */
    public function run(Config_Abstract $applicationConfig);

    /**
     * Get installation errors
     * @return array
     */
    public function getErrors();
}