<?php
class Currency
{
	protected static function lists() {
		return array(
			array('code'=>'RMB','name'=>Yii::t('currency','RMB'),'sign'=>'fa-cny'),
			array('code'=>'RM','name'=>Yii::t('currency','RM'),'sign'=>'fa-dollar'),
			array('code'=>'HKD','name'=>Yii::t('currency','HKD'),'sign'=>'fa-dollar'),
			array('code'=>'USD','name'=>Yii::t('currency','USD'),'sign'=>'fa-dollar'),
		);
	}

	public static function getDropDownList() {
		$rtn = array();
		$lists = self::lists();
		foreach ($lists as $row) {
			$rtn[$row['code']] = $row['name'];
		}
		return $rtn;
	}
	
	public static function getName($code) {
		$rtn = '';
		$lists = self::lists();
		foreach ($lists as $row) {
			if ($row['code']==$code) {
				$rtn = $row['name'];
				break;
			}
		}
		return $rtn;
	}

	public static function getSign($code) {
		$rtn = 'fa-money';
		$lists = self::lists();
		foreach ($lists as $row) {
			if ($row['code']==$code) {
				$rtn = $row['sign'];
				break;
			}
		}
		return $rtn;
	}
}
