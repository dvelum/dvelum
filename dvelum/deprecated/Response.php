<?php
/**
 * Application response class
 * @author Kirill A Egorov 2011
 * @deprecated
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
		header($_SERVER["SERVER_PROTOCOL"]."/1.0 404 Not Found");
		exit();
	}
}