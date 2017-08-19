<?php
use Dvelum\Config\ConfigInterface;

abstract class Externals_Installer
{
    protected $errors = [];
    /**
     * Run module post-install actions
     * @param ConfigInterface $applicationConfig
     * @param ConfigInterface $moduleConfig
     * @return boolean - success flag
     */
    abstract public function install(ConfigInterface $applicationConfig, ConfigInterface $moduleConfig);

    /**
     * Run module post-uninstall actions
     * @param ConfigInterface $applicationConfig
     * @param ConfigInterface $moduleConfig
     * @return boolean - success flag
     */
    abstract public function uninstall(ConfigInterface $applicationConfig, ConfigInterface $moduleConfig);

    /**
     * Get installation errors
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}