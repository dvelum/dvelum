<?php
class FilterTest extends PHPUnit_Framework_TestCase
{
	public function testFilterValue()
	{
		$this->assertEquals(Filter::filterValue('integer', 123) ,  (integer)123);
		$this->assertEquals(Filter::filterValue('float', 12.2) ,  (float)12.2);
		$this->assertEquals(Filter::filterValue('str', 333) ,  (string)333);
		$this->assertEquals(Filter::filterValue('cleaned_string', " <a href='test'>Test</a>") ,  '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;');
		$this->assertEquals(Filter::filterValue('email', 'cmd<03>@aa.ss') ,  'cmd03@aa.ss');
		$this->assertEquals(Filter::filterValue('raw', 'saa') ,  'saa');
		$this->assertEquals(Filter::filterValue('alphanum', 'pOl$1@_!;l') ,  'pOl1_l');
		$this->assertEquals(Filter::filterValue('alpha', 'pOl$1@_!;4l') ,  'pOll');
		$this->assertEquals(Filter::filterValue('somefilter', '11asdasd 2 d') ,  11);

		$this->assertEquals(Filter::filterValue('alphanum', '~!@#$%^&*()234admin@mail.ru') ,  '234adminmailru');
		$this->assertEquals(Filter::filterValue('login', '~!@#$%^&*()admin@mail.ru\,\'') ,  '@admin@mail.ru');

		$this->assertTrue(is_array(Filter::filterValue('array', 'asd')));

		Filter::setDelimiter('-');
		$this->assertEquals(Filter::filterValue('pagecode', 'p_Ol$ 1@_!;L') ,  'p_ol1_l');

		Filter::setDelimiter('_');
		$this->assertEquals(Filter::filterValue('pagecode', 'p-Ol$ 1@_!;L') ,  'p-ol1-l');

		Filter::setDelimiter('/');
	}
	public function testFilterString(){
		$this->assertEquals(Filter::filterString('  <b><?php echo "biber"; ?></b>what? ') , 'what?');
	}
}