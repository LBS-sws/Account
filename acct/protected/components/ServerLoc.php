<?php
class ServerLoc {
	public static function location() {
		$confFile = Yii::app()->basePath.'/config/location.php';
		if (file_exists($confFile)) {
			$items = require($confFile);
			return (isset($items[Yii::app()->language])) ? $items[Yii::app()->language] : $items['en'];
		} else {
			return 'Server: Unknown';
		}
	}
}
?>