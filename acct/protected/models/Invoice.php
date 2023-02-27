<?php
class Invoice {
	public static function getData($city, $start, $end, $customer='') {
		$rtn = array('message'=>'', 'data'=>array());
		
		$key = Yii::app()->params['unitedKey'];
		$root = Yii::app()->params['unitedRootURL'];
		$url = $root.'/remote/getInvoice.php';
		$data = array(
			"key"=>$key,
			"begin"=>$start,
			"end"=>$end,
			"city"=>$city
		);
		if (!empty($customer)) $data['customer'] = $customer;
		$data_string = json_encode($data);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type:application/json',
			'Content-Length:'.strlen($data_string),
 		));
		$out = curl_exec($ch);
		if ($out===false) {
			$rtn['message'] = curl_error($ch);
		} else {
			$json = json_decode($out);
			$rtn['data'] = json_decode($out, true);
			$rtn['message'] = self::getJsonError(json_last_error());
		}
		
		return $rtn;
	}
	
	public static function getInvData($start, $end, $city='') {
		$rtn = array('message'=>'', 'data'=>array());
		
		$key = Yii::app()->params['unitedKey'];
		$root = Yii::app()->params['unitedRootURL'];
		$url = $root.'/remote/getInvInvoice.php';
		$data = array(
			"key"=>$key,
			"begin"=>$start,
			"end"=>$end,
		);
		if (!empty($city)) $data['city'] = $city;
		$data_string = json_encode($data);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type:application/json',
			'Content-Length:'.strlen($data_string),
 		));
		$out = curl_exec($ch);
		if ($out===false) {
			$rtn['message'] = curl_error($ch);
		} else {
			$json = json_decode($out);
			$rtn['data'] = json_decode($out, true);
			$rtn['message'] = self::getJsonError(json_last_error());
		}
		
		return $rtn;
	}
	
	public static function getJsonError($error) {
		switch ($error) {
			case JSON_ERROR_NONE:
				return 'Success';
			case JSON_ERROR_DEPTH:
				return ' - Maximum stack depth exceeded';
			case JSON_ERROR_STATE_MISMATCH:
				return ' - Underflow or the modes mismatch';
			case JSON_ERROR_CTRL_CHAR:
				return ' - Unexpected control character found';
			case JSON_ERROR_SYNTAX:
				return ' - Syntax error, malformed JSON';
			case JSON_ERROR_UTF8:
				return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
			default:
				return' - Unknown error ('.$error.')';
		}
	}
}
?>