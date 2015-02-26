<?php
class Utils_Format
{
	/**
	 * Convert files list into Tree structure
	 * @param array $data
	 * @return Tree
	 */
	static public function fileListToTree(array $data)
	{
		$tree = new Tree();
		foreach($data as $k => $v)
		{
			$tmp = explode('/' , substr($v , 2));
			for($i = 0, $s = sizeof($tmp); $i < $s; $i++)
			{
				if($i == 0)
				{
					$id = $tmp[0];
					$par = 0;
				}
				else
				{
					$id = implode('/' , array_slice($tmp , 0 , $i + 1));
					$par = implode('/' , array_slice($tmp , 0 , $i));
				}
				$tree->addItem($id , $par , $tmp[$i]);
			}
		}
		return $tree;
	}
	
	/**
	 * Format time
	 * @param integer $difference
	 * @return string
	 */
	static public function formatTime($difference)
	{
		$days = floor($difference / 86400);
		$difference = $difference % 86400;
		$hours = floor($difference / 3600);
		$difference = $difference % 3600;
		$minutes = floor($difference / 60);
		$difference = $difference % 60;
		$seconds =  floor($difference);
		if($minutes == 60){
			$hours = $hours+1;
			$minutes = 0;
		}
		$s='';
	
		if($days >0)
			$s.= $days.' days ';
	
		$s.= str_pad($hours,2,'0',STR_PAD_LEFT).':'.str_pad($minutes,2,'0',STR_PAD_LEFT).':'.str_pad($seconds,2,'0',STR_PAD_LEFT);
		return $s;
	}
	
	/**
	 * Format file size in user friendly
	 * @param integer $size
	 * @return string
	 */
	static public function formatFileSize($size)
	{
		/*
		 * 1024 * 1024 * 1024  - Gb
		*/
		if($size > 1073741824)
			return number_format($size / 1073741824 , 1 ).' Gb';
		/*
		 * 1024 * 1024 - Mb
		*/
		if($size > 1048576)
			return number_format($size / 1048576 , 1 ).' Mb';
		/*
		 * 1024  - Kb
		*/
		if($size > 1024)
			return number_format($size / 1024 , 1 ).' Kb';
	
		return 	$size.' B';
	}
}