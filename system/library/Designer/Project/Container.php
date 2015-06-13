<?php
/**
 * Class Designer_Project_Container
 * System container for Designer_Project
 */
class Designer_Project_Container
{
    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getClass()
    {
        return 'Designer_Project_Container';
    }
}