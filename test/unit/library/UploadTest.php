<?php
class UploadTest extends PHPUnit_Framework_TestCase
{
    public function testCreateDirs()
    {
        $rootPath = '../';
        $subPath = '/uploads/0/1/2/3';

        $this->assertTrue(Upload::createDirs($rootPath , $subPath));
        $this->assertTrue(file_exists('../uploads/0/1/2/3'));
        $this->assertTrue(is_dir('../uploads/0/1/2/3'));
        File::rmdirRecursive('../uploads/', true);
    }
}