<?php
/* Reimbursement Form */

class Report01Form extends CReportForm
{
	public $ref_no;
	
	protected function labelsEx() {
		return array(
				'ref_no'=>Yii::t('trans','Ref. No.'),
			);
	}
	
	protected function rulesEx() {
		return array(
				array('ref_no','safe'),
			);
	}
	
	protected function queueItemEx() {
		return array(
				'REF_NO'=>$this->ref_no,
			);
	}
	
	public function init() {
		$this->id = 'RptReimbursement';
		$this->name = Yii::t('report','Reimbursement Form');
		$this->format = 'PDF';
		$this->city = Yii::app()->user->city();
		$this->fields = 'start_dt,end_dt,ref_no';
		$this->end_dt = date("Y/m/d");
		$this->start_dt = date("Y", strtotime($this->end_dt)).'/'.date("m", strtotime($this->end_dt)).'/01';
	}
}
