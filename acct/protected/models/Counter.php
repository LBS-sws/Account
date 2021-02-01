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
		$list3 = $wf->getPendingRequestIdList('PAYROLL', 'PC', Yii::app()->user->id);
		$list4 = $wf->getPendingRequestIdList('PAYROLL', 'P1', Yii::app()->user->id);
		$list5 = $wf->getPendingRequestIdList('PAYROLL', 'P2', Yii::app()->user->id);
		$items1 = empty($list1) ? array() : explode(',',$list1);
		$items2 = empty($list2) ? array() : explode(',',$list2);
		$items3 = empty($list3) ? array() : explode(',',$list3);
		$items4 = empty($list4) ? array() : explode(',',$list4);
		$items5 = empty($list5) ? array() : explode(',',$list5);
		$rtn = count($items1) + count($items2) + count($items3) + count($items4) + count($items5);
		
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


    public static function countSalesTable() {
        $rtn = 0;
        $city = Yii::app()->user->city_allow();
        if(Yii::app()->user->validFunction('CN12')){
            $sql="select * from acc_product where city in ($city) and examine='Y'";
            $rows = Yii::app()->db->createCommand($sql)->queryAll();
        }
        empty($rows)?$rtn=0:$rtn=count($rows);
//        $wf = new WorkflowPayroll;
//        $wf->connection = Yii::app()->db;
//        $list = $wf->getPendingRequestIdList('PAYROLL', 'PS', Yii::app()->user->id);
//        $items = empty($list) ? array() : explode(',',$list);
//        $rtn = count($items);
        return $rtn;
    }
}

?>