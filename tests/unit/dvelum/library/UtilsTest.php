<?php
use PHPUnit\Framework\TestCase;
use Dvelum\Utils;

class UtilsTest extends TestCase
{
	public function testRekey()
	{
		$data = array(
			array('id'=>11,'text'=>1),
			array('id'=>12,'text'=>2),
			array('id'=>13,'text'=>3)
		);
		
		$result = Utils::rekey('id', $data);
		
		$this->assertEquals(array(
			11 => array('id'=>11,'text'=>1),
			12 => array('id'=>12,'text'=>2),
			13 => array('id'=>13,'text'=>3)
		), $result);
	}
	
	
	public function testCollectData(){
		$data = array(
			array('id'=>11,'text'=>1),
			array('id'=>12,'text'=>2),
			array('id'=>13,'text'=>3)
		);
		
		$result = Utils::collectData('id','text' , $data);
		
		$this->assertEquals(array(
			11 => 1,
			12 => 2,
			13 => 3
		), $result);
	}
	
	public function testFetchCol(){
		$data = array(
			array('id'=>11,'text'=>1),
			array('id'=>12,'text'=>2),
			array('id'=>13,'text'=>3)
		);
		
		$result = Utils::fetchCol('text', $data);
		$this->assertEquals(array(
			1,
			2,
			3
		), $result);
		
	}
	
	public function testGroupByKey()
	{
		
		$data = array(
			array('id'=>11,'text'=>1 ,'group'=>1),
			array('id'=>12,'text'=>2 ,'group'=>7),
			array('id'=>13,'text'=>3 ,'group'=>7)
		);
		
		$result = Utils::groupByKey('group', $data);
		
		$this->assertEquals(array(
			1 => array(
					array('id'=>11,'text'=>1 ,'group'=>1),
			),
			7 => array(
				array('id'=>12,'text'=>2 ,'group'=>7),
				array('id'=>13,'text'=>3 ,'group'=>7)
			)
		), $result);

		$this->assertEquals([], Utils::groupByKey('id',[]));
	}

	public function testClassFromPath()
	{	
		$this->assertEquals('Store_Session1' , Utils::classFromPath('store/session1.php'));
		$this->assertEquals('Store_Session2' , Utils::classFromPath('/Store/Session2.php'));
		$this->assertEquals('Store_Session3' , Utils::classFromPath('./Store/Session3.php'));
		$this->assertEquals('Store_Session4' , Utils::classFromPath('../Store/Session4.php'));	
		$this->assertEquals(false , Utils::classFromPath('Store/Session/'));
	}

    public function testRoundUp()
    {
        $this->assertEquals(12.13 , Utils::roundUp(12.123, 2));
        $this->assertEquals(12.1 , Utils::roundUp(12.003, 1));
        $this->assertEquals(12.124 , Utils::roundUp(12.1234, 3));
        $this->assertEquals(12.123 , Utils::roundUp(12.123, 3));
    }

    public function  testSortByField()
    {
        $data = [
            ['code'=>'banana'],
            ['code'=>'apple'],
            ['code'=>'apple'],
            ['code'=> 'dog']
        ];
        $result = Utils::sortByField($data,'code');
        $this->assertEquals('apple' , $result[0]['code']);
        $this->assertEquals('apple' , $result[1]['code']);
        $this->assertEquals('banana' , $result[2]['code']);
        $this->assertEquals('dog' , $result[3]['code']);
    }

    public function  testSortByProperty()
    {
        $a = new \stdClass();
        $a->code = 'banana';

        $b = new \stdClass();
        $b->code = 'apple';

        $c = new \stdClass();
        $c->code = 'apple';

        $d = new \stdClass();
        $d->code = 'dog';

        $data = [
           $a,$b,$c,$d
        ];

        $result = Utils::sortByProperty($data,'code');
        $this->assertEquals('apple' , $result[0]->code);
        $this->assertEquals('apple' , $result[1]->code);
        $this->assertEquals('banana' , $result[2]->code);
        $this->assertEquals('dog' , $result[3]->code);
    }

    public function testRandomString()
    {
        $string1 = Utils::getRandomString(5);
        $string2 = Utils::getRandomString(5);
        $this->assertTrue(is_string($string1));
        $this->assertEquals(5 , strlen($string1));
        $this->assertFalse($string1 === $string2);
    }

    public function testListIntegers()
    {
        $data = [1,2,3,4,5,6,9];
        $this->assertEquals('1,2,3,4,5,6,9', Utils::listIntegers($data));
    }
}
