 <?php
class SellComputeCommand extends CConsoleCommand {

    private $year_no;
    private $month_no;

    //每月25号24点至每月月底24点自动计算销售提成
	public function actionCompute() {
	    $computeDate = date("Y-m-d H:i:s");
	    //$computeDate = "2025-08-01 00:46:48";
        $dateD = date("j",strtotime($computeDate));//天数
        $dateH = date("G",strtotime($computeDate));//小时
        $endD = date("t",strtotime($computeDate));//月份的最后一天
        echo "open sellCompute_{$endD}_{$dateD}_{$dateH}\n";
        if($dateD>25||($dateD==25&&$dateH>=23)||($dateD==1&&$dateH<=0)){
            $sendBsBool = $dateD==1||($dateD==$endD&&$dateH>=23);
            $computeDate = $dateD==1?date("Y-m-d H:i:s",strtotime($computeDate." - 1 days")):$computeDate;
            $this->year_no = date("Y",strtotime($computeDate));//年份
            $this->month_no = date("n",strtotime($computeDate));//月份
            echo "start sellCompute_{$this->year_no}_{$this->month_no}\n";
            $computeModel = new SellComputeForm();
            $computeModel->auditAll($this->year_no,$this->month_no,0);
            echo "end sellCompute\n";
            echo "start salesGroupBelow_{$this->year_no}_{$this->month_no}\n";
            $salesGroupModel = new SalesGroupBelowForm();
            $salesGroupModel->resetAllList($this->year_no,$this->month_no,$sendBsBool);
            echo "end salesGroupBelow\n";

            if($sendBsBool){
                $this->actionAudit();
                $this->actionAppraisal();
                $this->actionPerformanceBonus();
            }
        }
	}

    //每月月底24点自动审核销售提成，并发送至北森系统
    public function actionAudit() {
        echo "start sellTable_{$this->year_no}_{$this->month_no}\n";
        $sellTableModel = new SellTableForm();
        $sellTableModel->auditTableAll($this->year_no,$this->month_no);
        echo "end sellTable\n";
	}

    //每月月底24点自动固定销售顾问绩效考核表，并发送至北森系统
    public function actionAppraisal() {
        echo "start appraisal_{$this->year_no}_{$this->month_no}\n";
        $appraisalModel = new AppraisalForm();
        $appraisalModel->year_no = $this->year_no;
        $appraisalModel->month_no = $this->month_no;
        $appraisalModel->systemBatchSave();
        echo "end appraisal\n";
	}

    //每月月底24点自动固定﻿季度绩效奖金，并发送至北森系统
    public function actionPerformanceBonus() {
        echo "start PerformanceBonus_{$this->year_no}_{$this->month_no}\n";
        $quarter_no = ceil($this->month_no/3);
        $minMonth = ($quarter_no-1)*3 + 1;
        $leaveTime = date("Y/m/01",strtotime("{$this->year_no}/{$minMonth}/01"));
        $suffix = Yii::app()->params['envSuffix'];
        $deptSqlList = "'".implode("','",PerformanceBonusList::$deptNameList)."'";
        $list=array();
        $rows = Yii::app()->db->createCommand()->select("b.id,b.code,b.name")
            ->from("acc_service_comm_hdr a")
            ->leftJoin("hr{$suffix}.hr_employee b","b.code=a.employee_code")
            ->leftJoin("hr{$suffix}.hr_dept c","b.position=c.id")
            ->where("c.name in ({$deptSqlList}) and (b.staff_status!='-1' or (b.staff_status='-1' and replace(b.leave_time,'-', '/')>='$leaveTime')) AND b.bs_staff_id is NOT null and a.year_no=:year and a.month_no=:month",array(
                ":year"=>$this->year_no,":month"=>$this->month_no
            ))->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[]=$row["id"];
                echo "staff:".$row["employee_name"]."({$row["employee_code"]})\n";
            }
            $bonusModel = new PerformanceBonusForm('edit');
            $bonusModel->year_no = $this->year_no;
            $bonusModel->month_no = $this->month_no;
            $bonusModel->batchSave($list,true);
        }
        echo "end appraisal\n";
	}

	//2025年8月7日14:39:49销售提成计算的装机金额未计算
	public function actionRestInstall(){
        $sellRows = Yii::app()->db->createCommand()->select("id,employee_code,employee_name")->from("acc_service_comm_hdr")
            ->where("year_no=:year and month_no=:month",array(":year"=>2025,":month"=>8))->queryAll();
        if($sellRows){
            $model= new SellComputeForm('view');
            foreach ($sellRows as $sellRow){
                echo "staff:".$sellRow["employee_name"]."({$sellRow["employee_code"]})";
                $bool = $model->retrieveData($sellRow["id"],false);
                if($bool){
                    $model->resetInstallSave();
                    //$this->resetInstallSave();//需要额外计算装机金额
                    echo " - Success!";
                }else{
                    echo " - Error!";
                }
                echo "<br>\n";
            }
        }
    }
}

?>