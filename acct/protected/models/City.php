<?php
class City extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'security'.Yii::app()->params['envSuffix'].'.sec_city';
	}
	
	public function getAncestor($code) {
		$rtn = array();
		$row = $this->findByPk($code);
		if ($row!==null) {
			if (!empty($row->region) && $row->region!==null && $row->region!=$code){
				$rtn[] = $row->region;
				$ancestor = $this->getAncestor($row->region);
				if (!empty($ancestor)) $rtn = array_merge($rtn,$ancestor);
			}
		}
		return $rtn;
	}
	
	public function getAncestorList($code) {
		$rtn = '';
		$cities = $this->getAncestor($code);
		if (!empty($cities)) {
			foreach ($cities as $city) {
				$rtn .= ($rtn=='') ? "'$city'" : ",'$city'";
			}
		}
		return $rtn;
	}

	public function getAncestorInChargeList($code) {
		$rtn = array();
		$list = $this->getAncestorList($code);
		$rows = $this->findAll(array("condition"=>"code in ($list)"));
		if (!empty($rows)) {
			foreach ($rows as $row) {
				if (!empty($row->incharge)) $rtn[] = $row->incharge;
			}
		}
		return $rtn;
	}
	
	public function getDescendant($code) {
		$rtn = array();
		$rows = $this->findAll(array("condition"=>"region='$code'"));
		if (!empty($rows)) {
			foreach ($rows as $row) {
				$rtn[] = $row->code;
				$descendant = $this->getDescendant($row->code);
				if (!empty($descendant)) $rtn = array_merge($rtn,$descendant);
			}
		}
		return $rtn;
	}
	
	public function getDescendantList($code) {
		$rtn = '';
		$cities = $this->getDescendant($code);
		if (!empty($cities)) {
			foreach ($cities as $city) {
				$rtn .= ($rtn=='') ? "'$city'" : ",'$city'";
			}
		}
		return $rtn;
	}
	
	public function isNoDescendant($code) {
		return !$this->exists("region='$code'");
	}
	
	public function getCurrency($code) {
		$table = 'security'.Yii::app()->params['envSuffix'].'.sec_city_info';		
		$sql = "select field_value from $table where code='$code' and field_id='currency'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		return ($row!==false) ? $row['field_value'] : '';
	}
}
