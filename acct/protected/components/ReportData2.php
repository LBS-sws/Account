<?php
class ReportData2 {
	public $criteria;
		public $data;
	
	protected function fieldExist($name) {
		return (strpos($this->criteria->fields,$name)!==false);
	}
	
	public function getSelectString() {		$rtn = '';		if (isset($this->criteria)) {
			if ($this->fieldExist('start_dt') || $this->fieldExist('end_dt') || $this->fieldExist('target_dt')) {				$rtn = Yii::t('report','Date').': ';
				if ($this->fieldExist('target_dt'))
					$rtn .= General::toDate($this->criteria->target_dt);
				else
					$rtn .= General::toDate($this->criteria->start_dt).' - '.General::toDate($this->criteria->end_dt);
				
			}
		}		return $rtn;
	}
	
	public function getReportName() {		return (isset($this->criteria) ? Yii::t('report',$this->criteria->name) : Yii::t('report','Nil'));	}
		public function getReportId() {		return (isset($this->criteria) ? $this->criteria->id : Yii::t('report','Nil'));	}		public function getFieldList() {		$rtn = array();		$fields = $this->fields();		foreach ($fields as $key=>$field) {			$rtn[] = $key;		}		return $rtn;	}		public function getLabelList() {
		return $this->getItemList('label');
	}

	public function getWidthList() {
		return $this->getItemList('width');
	}

	public function getAlignList() {
		return $this->getItemList('align');
	}

	protected function getItemList($item) {
		$rtn = array();
		$fields = $this->fields();
		foreach ($fields as $key=>$field) {
			$rtn[] = $field[$item];
		}
		return $rtn;
	}

	public function getLabel($field) {		$fields = $this->fields();		return (array_key_exists($fields,$field) ? $fields[$field]['label'] : $field);	}		public function getWidth($field) {		$fields = $this->fields();		return (array_key_exists($fields,$field) ? $fields[$field]['width'] : 0);	}		public function getAlign($field) {
		$fields = $this->fields();
		return (array_key_exists($fields,$field) ? $fields[$field]['align'] : 'L');
	}

	public function header_structure() {
		return array();
	}
	
	public function line_group() {
		return array();
	}

	public function fields() {		return array();	}	
	public function groups() {
		return array();
	}
	
	public function subsections() {
		return array();
	}
	
	public function report_structure() {
		return array();
	}
		public function retrieveData() {		return;	}}
?>