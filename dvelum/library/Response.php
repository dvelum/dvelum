<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Copyright (C) 2011-2012  Kirill A Egorov
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * Application response class
 * @author Kirill A Egorov 2011
 */
class Response
{
	/**
	 * Error response for Extjs frontend
	 * @param string|boolean $msg
	 * @param array|boolean $errors
	 */
	static public function jsonError($msg = false , $errors = false)
	{
		echo json_encode(array(
				'success' => false , 
				'msg' => $msg , 
				'errors' => $errors
		));
		exit();
	}
	/**
	 * Success response for Extjs frontend
	 * @param array $data
	 * @param array $params
	 */
	static public function jsonSuccess($data = array() , array $params = array())
	{
		$result = array(
				'success' => true , 
				'data' => $data
		);
		if(! empty($params))
			foreach($params as $k => $v)
				$result[$k] = $v;
		
		echo json_encode($result);
		exit();
	}
	/**
	 * Response with data for ExtJS frontend
	 * @param array $data
	 */
	static public function jsonArray(array $data)
	{
		echo json_encode($data);
		exit();
	}
	/**
	 * Simple output
	 * @param string $html
	 */
	static public function put($html)
	{
		echo $html;
	}

	/**
	 * Send redirect header
	 * @param string $location
	 */
	static public function redirect($location)
	{
		header("Location: $location");
		exit();
	}

    /**
     * Send 404 Response code
     */
	static public function notFound()
	{
		header("HTTP/1.0 404 Not Found");
		exit();
	}
}