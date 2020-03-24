<?php

class Counter {
	public static function countConfReq() {
		$rtn = 0;

		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list = $wf->getPendingRequestIdList('PAYMENT', 'PB', Yii::app()->user->id);
		$items = empty($list) ? array() : explode(',',$list);
		$rtn = count($items);

		return $rtn;
	}

	public static function countApprReq() {
		$rtn = 0;

		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list = $wf->getPendingRequestIdList('PAYMENT', 'PA', Yii::app()->user->id);
		$items = empty($list) ? array() : explode(',',$list);
		$rtn = count($items);

		return $rtn;
	}
	
	public static function countReimb() {
		$rtn = 0;
		
		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list1 = $wf->getPendingRequestIdList('PAYMENT', 'PR', Yii::app()->user->id);
		$items = empty($list1) ? array() : explode(',',$list1);
		$rtn = count($items);
		
		$list2 = $wf->getPendingRequestIdList('PAYMENT', 'QR', Yii::app()->user->id);
		$items = empty($list2) ? array() : explode(',',$list2);
		$rtn += count($items);
		
		return $rtn;
	}
	
	public static function countSign() {
		$rtn = 0;
		
		$wf = new WorkflowPayment;
		$wf->connection = Yii::app()->db;
		$list = $wf->getPendingRequestIdList('PAYMENT', 'PS', Yii::app()->user->id);
		$items = empty($list) ? array() : explode(',',$list);
		$rtn = count($items);
		
		return $rtn;
	}

	public static function countPayrollAppr() {
		$rtn = 0;
		$wf = new WorkflowPayroll;
		$wf->connection = Yii::app()->db;
		$list1 = $wf->getPendingRequestIdList('PAYROLL', 'PA', Yii::app()->user->id);
		$list2 = $wf->getPendingRequestIdList('PAYROLL', 'PB', Yii::app()->user->id);
		$items1 = empty($list1) ? array() : explode(',',$list1);
		$items2 = empty($list2) ? array() : explode(',',$list2);
		$rtn = count($items1) + count($items2);
		
		return $rtn;
	}

	public static function countPayroll() {
		$rtn = 0;
		$wf = new WorkflowPayroll;
		$wf->connection = Yii::app()->db;
		$list = $wf->getPendingRequestIdList('PAYROLL', 'PS', Yii::app()->user->id);
		$items = empty($list) ? array() : explode(',',$list);
		$rtn = count($items);
		
		return $rtn;
	}
}

?>