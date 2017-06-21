<?php

class ReportForm extends CFormModel
{
	public $id;
	public $name;
	public $start_dt;
	public $end_dt;
	public $format;
	public $uid;
	public $city;
	public $target_dt;
	public $fields;
	public $email;
	public $emailcc;
	public $touser;
	public $ccuser;
	public $year;
	public $month;
	public $type;
	public $form;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'start_dt'=>Yii::t('report','Start Date'),
			'end_dt'=>Yii::t('report','End Date'),
			'format'=>Yii::t('report','Output Format'),
			'target_dt'=>Yii::t('report','Date'),
			'email'=>Yii::t('report','Email'),
			'touser'=>Yii::t('report','Email To'),
			'emailcc'=>Yii::t('report','Email Cc'),
			'ccuser'=>Yii::t('report','Email Cc').'<br>('.Yii::t('dialog','Hold down <kbd>Ctrl</kbd> button to select multiple options').')',
			'year'=>Yii::t('report','Year'),
			'month'=>Yii::t('report','Month'),
			'city'=>Yii::t('report','City'),
			'cityx'=>Yii::t('report','City').'<br>('.Yii::t('dialog','Hold down <kbd>Ctrl</kbd> button to select multiple options').')',
			'type'=>Yii::t('report','Type').'<br>('.Yii::t('dialog','Hold down <kbd>Ctrl</kbd> button to select multiple options').')',
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('id, name, format, uid, city, fields, touser, ccuser, year, month, type, form','safe'),
			array('start_dt, end_dt, target_dt','date','allowEmpty'=>false,
				'format'=>array('yyyy/MM/dd','yyyy-MM-dd','yyyy/M/d','yyyy-M-d',),
			),
			array('email','validateEmail'),
			array('emailcc','validateEmailList'),
		);
	}
	
	public function validateEmail($attribute, $params){
		if ($this->format=='EMAIL') {
			$ev = new CEmailValidator();
			$ev->attributes = explode(',',$attribute);
			foreach ($params as $key=>$value) $ev->$key = $value;
			$ev->allowEmpty = false;
			$ev->validate($this);
		}
	}
	
	public function validateEmailList($attribute, $params){
		$ev = new CEmailValidator();
		$ev->attributes = explode(',',$attribute);
		foreach ($params as $key=>$value) $ev->$key = $value;
		$ev->allowEmpty = true;
		foreach ($ev->attributes as $field) {
			$rtn = true;
			$list = str_replace(',',';',$this->$field);
			$emails = explode(';',$list);
			if (is_array($emails)) {
				foreach ($emails as $email) {
					if (!empty($email)) $rtn = $ev->validateValue(trim($email));
					if (!$rtn) break;
				}
			} else {
				$rtn = (empty($list) || $ev->validateValue($list));
			}
			if (!$rtn) {
				$fldnames = $this->attributeLabels();
				$message = str_replace('{attribute}',$fldnames[$field],Yii::t('yii','{attribute} is not a valid email address.'));
				$this->addError($field,$message);
				break;
			}
		}
	}

	public function showField($name) {
		$a = explode(',',$this->fields);
		return empty($this->fields) || in_array($name, $a);
	}
}
