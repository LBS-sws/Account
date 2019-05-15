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
}

?>