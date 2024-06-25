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

        $arrPix = array("PA","PB","PC","PD","PE","P1","P2","P3","P4");
        $rtn = 0;
        foreach ($arrPix as $pix){
            $listTemp = $wf->getPendingRequestIdList('PAYROLL',$pix, Yii::app()->user->id);
            if(!empty($listTemp)){
                $listTemp=explode(',',$listTemp);
                $rtn+=count($listTemp);
            }
        }

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


    public static function countConsult() {
	    $model = new ConsultAuditList();
        $rtn=0;
	    if(ConsultApplyList::staffCompanyForUsername($model)){
            $rtn=$model->getCountConsult();
        }
        return $rtn;
    }

    public static function countExpenseApply() {
	    $model = new ExpenseApplyList();
        $rtn=$model->getCountConsult();
        return $rtn;
    }

    public static function countExpenseConfirm() {
	    $model = new ExpenseConfirmList();
        $rtn=$model->getCountConsult();
        return $rtn;
    }

    public static function countExpenseAudit() {
	    $model = new ExpenseAuditList();
        $rtn=$model->getCountConsult();
        return $rtn;
    }

    public static function countExpensePayment() {
	    $model = new ExpensePaymentList();
        $rtn=$model->getCountConsult();
        return $rtn;
    }

    public static function countRemitApply() {
	    $model = new RemitApplyList();
        $rtn=$model->getCountConsult();
        return $rtn;
    }

    public static function countRemitAudit() {
	    $model = new RemitAuditList();
        $rtn=$model->getCountConsult();
        return $rtn;
    }

    public static function countRemitPayment() {
	    $model = new RemitPaymentList();
        $rtn=$model->getCountConsult();
        return $rtn;
    }
}

?>