<?php
class Export_Adapter_Csv extends Export_Adapter_Abstract
{
	protected $_content = '';
	protected static $_delimiter = ';';
	protected static $_textDelimiter = '"';

	public function stream()
	{
		ob_end_clean();
		header('Pragma: public');
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past   
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Accept-Ranges: bytes');
		header('Cache-Control: no-store, no-cache, must-revalidate'); // HTTP/1.1
		header('Cache-Control: pre-check=0, post-check=0, max-age=0'); // HTTP/1.1 
		header("Pragma: no-cache");
		header("Expires: 0");
		header('Content-Transfer-Encoding: none');
		header("Content-Description: File Transfer");
		header("Content-Transfer-Encoding: binary");
		header('Content-Length: ' . strlen($this->_content));
		header('Content-Type: application/force-download; charset=utf-8');
		header('Content-Disposition: attachment; filename="' . $this->_file . '.csv"');
		
		echo $this->_content;
		exit();
	}

	/**
	 * Save file
	 * @param string $path destination directory
	 * @return boolean
	 */
	public function save($path)
	{
		return @file_put_contents($path . $this->_file . '.csv' , $this->_content);
	}

	/**
	 * Addrow
	 * @param array $data
	 */
	public function addRow(array $data)
	{
		$this->_content.= implode(self::$_delimiter , array_map(array('self' , 'wrapValue') , $data)) . "\n";
	}

	/**
	 * Set delimiter
	 * @param string $delimiter
	 */
	public function setDelimiter($delimiter)
	{
		self::$_delimiter = $delimiter;
	}
	/**
	 * Wrap string
	 * @param string $value
	 */
	static public function wrapValue($value)
	{
		if(is_string($value))
			return self::$_textDelimiter . str_replace(self::$_textDelimiter , "\\" . self::$_textDelimiter , $value) . self::$_textDelimiter;
		else
			return $value;
	}
}