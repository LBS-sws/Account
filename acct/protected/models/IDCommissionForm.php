<?php
/* Reimbursement Form */

class IDCommissionForm extends CReportForm
{
    //已使用的字段 （開始）
    public $id;
    public $year;
    public $month;
    public $type=0;//0:查询  1：计算
    public $city;
    public $employee_id;
    public $employee_code;
    public $employee_name;
    public $group_type;//組別
    public $new_amount;//新增生意額提成
    public $edit_amount;//更改生意額提成
    public $renewal_amount;//續約生意額提成
    public $sum_amount;//提成总金额
    public $new_money;//新增回款
    public $edit_money;//更改回款
    public $renewal_money;//續約回款
    public $updateList;//需要修改的id列表
    //已使用的字段 （結束）
    //下面的都是複製過來的不知道幹啥的
    public $staffs;
    public $status_dt;
    public $company_name;
    public $status;
    public $cust_type;
    public $service;
    public $product_id;
    public $amt_paid;
    public $paid_type;
    public $amt_install;
    public $staffs_desc;
    public $first_dt;
    public $sign_dt;
    public $all_number;
    public $surplus;
    public $new_calc;
    public $end_amount;
    public $all_amount;
    public $saleyear;
    public $othersalesman;
    public $salesman;
    public $performance_amount;
    public $out_money;
    public $performance;
    public $performanceedit_amount;
    public $performanceend_amount;
    public $performanceedit_money;
    public $ctrt_period;
    public $point;
    public $renewalend_amount;
    public $product_amount;
    public $service_reward;//服務獎勵點

    protected function labelsEx() {
        return array(
            'staffs'=>Yii::t('report','Staffs'),
            'city_name'=>Yii::t('app','city'),
            'first_dt'=>Yii::t('app','first_dt'),
            'sign_dt'=>Yii::t('app','sign_dt'),
            'company_name'=>Yii::t('app','company_name'),
            'type_desc'=>Yii::t('app','type_desc'),
            'service'=>Yii::t('app','service'),
            'amt_paid'=>Yii::t('app','amt_paid'),
            'amt_install'=>Yii::t('app','amt_install'),
            'cust_type'=>Yii::t('app','cust_type'),
            'all_number'=>Yii::t('app','Number'),
            'surplus'=>Yii::t('app','Surplus'),
            'othersalesman'=>Yii::t('app','Othersalesman'),
            'salesman'=>Yii::t('app','Salesman'),
            'ctrt_period'=>Yii::t('app','Ctrt_period'),
        );
    }

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array('id,updateList','safe'),
            array('files, removeFileId, docMasterId','safe'),
            array ('updateList','validateList',array('on'=>'save')),
        );
    }

    public function validateList($attribute, $params) {
        if(!empty($this->updateList)){
            foreach ($this->updateList as $value){
                if(!is_numeric($value)){
                    $this->addError($attribute,"您选择的内容有误，请刷新重试");
                }else{
                    $suffix = Yii::app()->params['envSuffix'];
                    $row = Yii::app()->db->createCommand()->select("id")->from("swoper{$suffix}.swo_serviceid_info")
                        ->where("id=$value")->queryRow();
                    if(!$row){
                        $this->addError($attribute,"您选择的内容不存在，请刷新重试");
                    }
                }
            }
        }
    }

    protected function rulesEx() {
        return array(
            array('staffs, staffs_desc','safe'),
        );
    }

    protected function queueItemEx() {
        return array(
            'STAFFS'=>$this->staffs,
            'STAFFSDESC'=>$this->staffs_desc,
        );
    }

    public function setYearAndMonth($year,$month){
        $month--;
        if($month<=0){
            $month = 12;
            $year--;
        }
        $this->year = $year;
        $this->month = $month;
    }

    public function init() {
        $this->id = 'RptFiveID';
        $this->name = "ID ".Yii::t('app','Five Steps');
        $this->format = 'EXCEL';
        $this->city =Yii::app()->user->city();
        $this->fields = 'start_dt,end_dt,staffs,staffs_desc';
        $this->start_dt = date("Y/m/d");
        $this->end_dt = date("Y/m/d");
        $this->first_dt = date("Y/m/d");
        $this->sign_dt = date("Y/m/d");
        $this->staffs = '';
        $this->date="";
        $this->month=date("m");
        $this->year=date("Y");
        $this->staffs_desc = Yii::t('misc','All');
    }

    //判斷該員工是否已經添加，如果沒添加則添加
    protected function addEmployee($index){
        $suffix = Yii::app()->params['envSuffix'];
        if(empty($index)){
            //增加
            $list = Yii::app()->db->createCommand()->select("id,sum_amount")->from("acc_serviceid_comm_hdr")
                ->where("year_no=:year_no and month_no=:month_no and employee_id=:employee_id",
                    array(":year_no"=>$this->year,":month_no"=>$this->month,":employee_id"=>$this->employee_id))
                ->queryRow();
            if($list){
                $this->id = $list["id"];
                $this->sum_amount = $list["sum_amount"];
            }else{
                $staffList = Yii::app()->db->createCommand()->select("city")->from("hr{$suffix}.hr_employee")
                    ->where("id=:id",array(":id"=>$this->employee_id))
                    ->queryRow();
                if($staffList){
                    $city = $staffList["city"];
                }else{
                    return false;//員工不存在，異常
                }
                Yii::app()->db->createCommand()->insert("acc_serviceid_comm_hdr",array(
                    "year_no"=>$this->year,
                    "month_no"=>$this->month,
                    "employee_id"=>$this->employee_id,
                    "city"=>$city,
                ));
                $this->id = Yii::app()->db->getLastInsertID();
            }
        }else{
            $this->id = $index;
        }
        return true;
    }

    //獲取ID服務某個字段的值
    protected function getIDCommissionInfoForName($file_name){
        $list = Yii::app()->db->createCommand()->select("file_value")->from("acc_serviceid_comm_dtl")
            ->where("file_name=:file_name and hdr_id=:hdr_id",
                array(":file_name"=>$file_name,":hdr_id"=>$this->id))
            ->queryRow();
        if($list){
            return floatval($list["file_value"]);
        }else{
            return 0;
        }
    }

    //翻譯員工組別 或者 獲取組別列表
    public static function getGroupType($group_type=0,$bool=false){
        $list = array(
            Yii::t("misc","none"),
            Yii::t("misc","group business"),
            Yii::t("misc","group repast")
        );
        if($bool){
            if(key_exists($group_type,$list)){
                return $list[$group_type];
            }else{
                return $group_type;
            }
        }else{
            return $list;
        }
    }

    //總頁
    public function retrieveData($index,$bool=false){
        $suffix = Yii::app()->params['envSuffix'];
        if(!$bool){ //沒有該員工時，生成該員工提成列表
            $bool = $this->addEmployee($index);
        }else{
            $this->id = $index;
        }
        if($bool){
            $row = Yii::app()->db->createCommand()
                ->select("a.id,a.year_no,a.month_no,a.employee_id,a.sum_amount,b.code,b.name,b.city,b.group_type")
                ->from("acc_serviceid_comm_hdr a")
                ->leftJoin("hr{$suffix}.hr_employee b","a.employee_id=b.id")
                ->where("a.id=:id",array(":id"=>$this->id))
                ->queryRow();
            if($row){
                $this->year = $row["year_no"];
                $this->month = $row["month_no"];
                $this->employee_id = $row["employee_id"];
                $this->employee_code = $row["code"];
                $this->employee_name = $row["name"];
                $this->city = $row["city"];
                $this->group_type = $row["group_type"];
                $this->sum_amount = floatval($row["sum_amount"]);
                $list = array("new_amount","edit_amount","renewal_amount","new_money","edit_money","renewal_money");

                foreach ($list as $file_name){
                    $this->$file_name = self::getIDCommissionInfoForName($file_name);
                }
                return true;
            }
        }
        return false;
    }

    //计算新增回款金额
    public function newSave(){

    }
}
