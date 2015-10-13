<?php
class FileTest extends PHPUnit_Framework_TestCase
{
    public function testGetExt()
    {
        $this->assertEquals('.php', File::getExt('index.php'));
        $this->assertEquals('.jpeg', File::getExt('1.jpeg'));
        $this->assertEquals('.dat', File::getExt('data.dat'));
        $this->assertEquals('.binary' , File::getExt('.binary'));
        $this->assertEquals(false, File::getExt('binary'));
        $this->assertEquals('.php', File::getExt('Store/Session1.php'));
    }


    public function testFillEndSep()
    {
        $this->assertEquals('/path/to/file/', File::fillEndSep('/path/to/file'));
        $this->assertEquals('/path/to/file/', File::fillEndSep('/path/to/file/'));
    }


    public function testRmdirRecursive()
    {
        $dir = './temp/unit/'.date('Y').'/'.date('m').'/'.date('d');
        $this->assertTrue(mkdir($dir , 0777, true));
        $this->assertTrue((boolean)file_put_contents($dir.'/test.txt','test'));
        $this->assertTrue(File::rmdirRecursive('./temp/unit/'.date('Y'),true));
        $this->assertTrue(!file_exists('./temp/unit/'.date('Y')));
        File::rmdirRecursive('./temp/unit/',true);
    }
}