<?php
abstract class Externals_Installer
{
    protected $errors = [];
    /**
     * Run module post-install actions
     * @param Config_Abstract $applicationConfig
     * @param Config_Abstract $moduleConfig
     * @return boolean - success flag
     */
    abstract public function install(Config_Abstract $applicationConfig, Config_Abstract $moduleConfig);

    /**
     * Run module post-uninstall actions
     * @param Config_Abstract $applicationConfig
     * @param Config_Abstract $moduleConfig
     * @return boolean - success flag
     */
    abstract public function uninstall(Config_Abstract $applicationConfig, Config_Abstract $moduleConfig);

    /**
     * Get installation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}