<?php

class ExpenseApplyForm extends CFormModel
{
	/* User Fields */
	protected $table_type=1;
	public $id;
	public $exp_code;
	public $employee_id;
	public $apply_date;
	public $audit_user;
	public $audit_json;
	public $current_num;
	public $current_username;
	public $city;
	public $apply_city;
	public $status_type=0;
	public $amt_money;
	public $payment_id;
	public $payment_type;
	public $payment_date;
	public $acc_id;
	public $remark;
	public $reject_note;
	public $lcu;
	public $luu;
	public $lcd;
	public $lud;

	public $finance_bool=false;

	public $infoDetail=array(
	    array(
            "id"=>"",
            "expId"=>"",
            "setId"=>"",
            "infoDate"=>"",
            "amtType"=>"",
            "infoRemark"=>"",
            "infoAmt"=>"",
            "infoJson"=>"[]",
            "uflag"=>"N",
        )
    );


    public $no_of_attm = array(
        'expen'=>0
    );
    public $docType = 'EXPEN';
    public $docMasterId = 0;
    public $files;
    public $removeFileId = 0;
    public $tableDetail=array(
        'trip_bool'=>0,//是否关联出差申请 0:不关联 1：关联
        'trip_id'=>null,//出差id
        'local_bool'=>0,//费用是否归属本地区 0：否 1：是
        'payment_condition'=>null,//付款条件
        'payment_company'=>null,//支付公司
    );
    protected $fileList=array(
        array("field_id"=>"trip_bool","field_type"=>"list","field_name"=>"trip bool","display"=>"none"),//是否关联出差申请
        array("field_id"=>"trip_id","field_type"=>"list","field_name"=>"trip id","display"=>"none"),//出差id
        array("field_id"=>"local_bool","field_type"=>"list","field_name"=>"local bool","display"=>"none"),//费用是否归属本地区
        array("field_id"=>"payment_condition","field_type"=>"list","field_name"=>"payment condition","display"=>"none"),//付款条件
        array("field_id"=>"payment_company","field_type"=>"list","field_name"=>"payment company","display"=>"none"),//支付公司
    );

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'city'=>Yii::t('give','City'),
            'exp_code'=>Yii::t('give','expense code'),
            'apply_date'=>Yii::t('give','apply date'),
            'employee_id'=>Yii::t('give','apply user'),
            'employee'=>Yii::t('give','apply user'),
            'department'=>Yii::t('give','department'),
            'position'=>Yii::t('give','position'),
            'city_name'=>Yii::t('give','City'),
            'amt_money'=>Yii::t('give','sum money'),
            'status_type'=>Yii::t('give','status type'),
            'remark'=>Yii::t('give','remark'),
            'reject_note'=>Yii::t('give','reject note'),
            'payment_id'=>Yii::t('give','Payment Account'),
            'acc_id'=>Yii::t('give','Payment Account'),
            'payment_type'=>Yii::t('give','Payment Type'),
            'payment_date'=>Yii::t('give','Payment Date'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,exp_code,tableDetail,employee_id,apply_date,city,status_type,amt_money,remark,reject_note','safe'),
			array('employee_id,apply_date','required'),
            array('employee_id','validateEmployee'),
            array('id','validateID'),
            array('tableDetail','validateDetail'),
            array('infoDetail','validateInfo'),
            array('status_type','validateStatus'),
            array('no_of_attm, docType, files, removeFileId, docMasterId','safe'),
        );
	}

	public function validateDetail($attribute, $params){
        if(!empty($this->tableDetail["trip_bool"])){
            if(empty($this->tableDetail["trip_id"])){
                $this->addError($attribute, "关联的出差申请不能为空");
                return false;
            }
            $suffix = Yii::app()->params['envSuffix'];
            $row = Yii::app()->db->createCommand()->select("id,trip_cause,trip_cost,trip_code")->from("hr{$suffix}.hr_employee_trip")
                ->where("id=:id and employee_id=:employee_id",array(
                    ":id"=>$this->tableDetail["trip_id"],":employee_id"=>$this->employee_id
                ))->queryRow();
            if(!$row){
                $this->addError($attribute, "出差申请id不存在，请刷新重试");
                return false;
            }
        }else{
            $this->tableDetail["trip_id"]=null;
        }
    }

    public function validateStatus($attribute, $params) {//验证是否有审核人
	    if($this->status_type!=2){
	        return true;
        }
        $this->audit_user=array();
        $this->audit_json=array();
        $this->current_username="";
        $auditRows = Yii::app()->db->createCommand()->select("a.*")
            ->from("acc_set_audit_info a")
            ->leftJoin("acc_set_audit b","a.set_id=b.id")
            ->where("b.employee_id=:id",array(":id"=>$this->employee_id))
            ->order("a.z_index asc")->queryAll();
        if($auditRows){
            foreach ($auditRows as $row){
                if(empty($row["amt_bool"])){//不限制金额
                    $this->audit_user[]=$row["audit_user"];
                    $this->audit_json[]=array("audit_user"=>$row["audit_user"],"audit_tag"=>$row["audit_tag"]);
                }else{//限制金额
                    $amtMin = floatval($row["amt_min"]);
                    $amtMax = floatval($row["amt_max"]);
                    if($this->amt_money>=$amtMin&&$this->amt_money<=$amtMax){
                        $this->audit_user[]=$row["audit_user"];
                        $this->audit_json[]=array("audit_user"=>$row["audit_user"],"audit_tag"=>$row["audit_tag"]);
                    }
                }
            }
            if(empty($this->audit_user)){
                $this->addError($attribute, "报销金额（{$this->amt_money}）异常，请与管理员联系");
                return false;
            }
            $this->current_username = $this->audit_user[0];
            $this->audit_user = implode(",",$this->audit_user);
            $this->audit_json = json_encode($this->audit_json);
        }else{
            $this->addError($attribute, "该员工没有指定审核人，请与管理员联系");
            return false;
        }
    }

    public function validateInfo($attribute, $params) {
        $updateList = array();
        $deleteList = array();
        $this->amt_money = 0;
        $typeTwoList = ExpenseFun::getAmtTypeTwo();
        $localSetID = ExpenseFun::getLocalSetIdToCity($this->city);
        foreach ($this->infoDetail as $list){
            if($this->tableDetail["local_bool"]==1){
                $list["setId"] = $localSetID;//如果费用是归属本地区,强制转换
            }
            $temp = array();
            if($list["uflag"]=="D"){
                $deleteList[] = $list;
            }else{
                if(!empty($list["infoAmt"])){
                    $list["infoAmt"] = is_numeric($list["infoAmt"])?round($list["infoAmt"],2):0;
                    $this->amt_money+=floatval($list["infoAmt"]);
                    foreach ($typeTwoList as $key=>$item){
                        $key = "".$key;
                        if(key_exists($key,$list)){
                            $temp[$key] = $list[$key];
                        }
                    }
                    $list["infoJson"] = json_encode($temp);
                    $updateList[]=$list;
                    if(empty($list["setId"])){
                        $this->addError($attribute, "费用归属不能为空");
                        break;
                    }
                    if(empty($list["infoDate"])){
                        $this->addError($attribute, "日期不能为空");
                        break;
                    }
                    if($list["amtType"]===""){
                        $this->addError($attribute, "费用类别不能为空");
                        break;
                    }
                }else{
                    $this->addError($attribute, "费用金额不能为空");
                }
            }
        }

        if(empty($updateList)){
            $this->addError($attribute, "报销明细不能为空");
            return false;
        }
        $this->infoDetail = array_merge($updateList,$deleteList);
    }

    public function validateEmployee($attribute, $params) {
        $id = $this->$attribute;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("id,city")->from("hr{$suffix}.hr_employee")
            ->where("id=:id",array(":id"=>$id))->queryRow();
        if($row){
            $this->employee_id = $id;
            $this->city = $row["city"];
            return true;
        }else{
            $this->addError($attribute, "员工不存在，请刷新重试");
            return false;
        }
    }

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $uid = Yii::app()->user->id;
        if($this->getScenario()!="new"){
            $row = Yii::app()->db->createCommand()->select("id,city")->from("acc_expense")
                ->where("id=:id and lcu='{$uid}' and table_type={$this->table_type}",array(":id"=>$id))->queryRow();
            if($row){
                $this->city = $row["city"];
            }else{
                $this->addError($attribute, "报销单不存在，请刷新重试");
                return false;
            }
        }
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
		$sql = "select *,docman$suffix.countdoc('expen',id) as expendoc from acc_expense where id='".$index."' and lcu='{$uid}' and table_type={$this->table_type}";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $index;
			$this->employee_id = $row['employee_id'];
			$this->exp_code = $row['exp_code'];
			$this->apply_date = General::toDate($row['apply_date']);
            $this->city = $row['city'];
            $this->apply_city = $row['apply_city'];
            $this->status_type = $row['status_type'];
            $this->amt_money = $row['amt_money'];
            $this->remark = $row['remark'];
            $this->reject_note = $row['reject_note'];
            $this->no_of_attm['expen'] = $row['expendoc'];
            $sql = "select * from acc_expense_info where exp_id='".$index."'";
            $infoRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($infoRows){
                $this->infoDetail=array();
                foreach ($infoRows as $infoRow){
                    $this->infoDetail[]=array(
                        "id"=>$infoRow["id"],
                        "expId"=>$infoRow["exp_id"],
                        "setId"=>$infoRow["set_id"],
                        "infoDate"=>General::toDate($infoRow["info_date"]),
                        "amtType"=>$infoRow["amt_type"],
                        "infoRemark"=>$infoRow["info_remark"],
                        "infoAmt"=>$infoRow["info_amt"],
                        "infoJson"=>$infoRow["info_json"],
                        "uflag"=>"N",
                    );
                }
            }

            if(!empty($this->fileList)){
                $tableDetailList = ExpenseFun::getExpenseTableDetailForID($index);
                foreach ($this->fileList as $detailRow){
                    if(key_exists($detailRow["field_id"],$tableDetailList)){
                        $this->tableDetail[$detailRow["field_id"]] = $tableDetailList[$detailRow["field_id"]]["field_value"];
                    }else{
                        $this->tableDetail[$detailRow["field_id"]] = "";
                    }
                }
            }
            return true;
		}else{
		    return false;
        }
	}

	public function retrievePrint($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
        $uid = Yii::app()->user->id;
		$sql = "select * from acc_expense where id='".$index."' and lcu='{$uid}' and status_type in (4,6,7,9) and table_type={$this->table_type}";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $index;
			$this->employee_id = $row['employee_id'];
			$this->exp_code = $row['exp_code'];
			$this->apply_date = General::toDate($row['apply_date']);
            $this->city = $row['city'];
            $this->apply_city = $row['apply_city'];
            $this->status_type = $row['status_type'];
            $this->amt_money = $row['amt_money'];
            $this->remark = $row['remark'];
            $this->reject_note = $row['reject_note'];
            $sql = "select * from acc_expense_info where exp_id='".$index."'";
            $infoRows = Yii::app()->db->createCommand($sql)->queryAll();
            if($infoRows){
                $this->infoDetail=array();
                foreach ($infoRows as $infoRow){
                    $this->infoDetail[]=array(
                        "id"=>$infoRow["id"],
                        "expId"=>$infoRow["exp_id"],
                        "setId"=>$infoRow["set_id"],
                        "infoDate"=>General::toDate($infoRow["info_date"]),
                        "amtType"=>$infoRow["amt_type"],
                        "infoRemark"=>$infoRow["info_remark"],
                        "infoAmt"=>$infoRow["info_amt"],
                        "infoJson"=>$infoRow["info_json"],
                        "uflag"=>"N",
                    );
                }
            }

            if(!empty($this->fileList)){
                $tableDetailList = ExpenseFun::getExpenseTableDetailForID($index);
                foreach ($this->fileList as $detailRow){
                    if(key_exists($detailRow["field_id"],$tableDetailList)){
                        $this->tableDetail[$detailRow["field_id"]] = $tableDetailList[$detailRow["field_id"]]["field_value"];
                    }
                }
            }
            return true;
		}else{
		    return false;
        }
	}

	public function printOne(){
        $pdf = new MyPDF2('L', 'mm', 'A4', true, 'UTF-8', false);
        $this->resetPDFConfig($pdf,$this);

        $pdf->AddPage();
        $this->setPDFTable($pdf,$this);
        //$pdf->writeHTML($html, true, false, false, false, '');

        ob_clean();
        $address=str_replace('/','-',$this->exp_code);
        $address.='.pdf';
        $pdf->Output($address, 'I');
        return $address;
    }

    protected function setPDFTable($pdf,$model){
	    //申请信息
        $amt_money_max = ExpenseFun::convertCurrency($model->amt_money);
        $tdTwoList = ExpenseFun::getAmtTypeTwo();
        $setNameList = ExpenseSetNameForm::getExpenseSetAllList();
        $amtTypeList = ExpenseFun::getAmtTypeOne();
        $tdCount = count($tdTwoList);
        $tableOneWidth=270;
        $tableTwoWidth=$tableOneWidth*2;
        $tableBoxWidth=$tableOneWidth+$tableTwoWidth+8;
        $employeeList = ExpenseFun::getEmployeeListForID($model->employee_id);
        $html=<<<EOF
<table border="0" width="{$tableOneWidth}px" cellspacing="0" cellpadding="0" style="line-height: 18px;">
<tr>
<th style="text-align: center;font-size:12px"><b>日常费用报销单</b></th>
</tr>
</table>
EOF;
        //申请人
        $html.=<<<EOF
<table border="0" width="{$tableOneWidth}px" cellspacing="0" cellpadding="0" style="line-height: 18px;border-bottom: 2px solid black;border-left: 2px solid black;">
<tr>
<th colspan="2" style="width:50%;background-color:#BFBFBF;border-left: 2px solid black;border-top: 2px solid black;border-right: 2px solid black;">&nbsp;<b>PART A:基本信息</b></th>
<th colspan="2" style="width:50%;border-bottom:2px solid black;">&nbsp;</th>
</tr>
<tr>
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;申请人</td><td style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;{$employeeList['employee']}</td>
<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;申请日期</td><td style="width:30%;border-top:1px solid black;border-right:2px solid black;">&nbsp;{$model->apply_date}</td>
</tr>
<tr>
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;部门</td><td style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;{$employeeList['department']}</td>
<td colspan="2" style="border-top:1px solid black;border-right:2px solid black;">&nbsp;</td>
</tr>
</table>
EOF;
        $html.="<p>&nbsp;</p>";
        $tableOneHeaderHtml="";
        $tableTwoHeaderHtml="";
        $tableInfoHtml="";
        $tableFooterList=array();
        $tableFooterHtml='<tr>';
        $tableFooterHtml.='<td colspan="5" style="width: 27.5%;text-align: right;border-top:1px solid black;border-right:1px solid black;border-left:2px solid black;"><b>人民币合计(RMB)</b>&nbsp;</td>';
        $tableFooterHtml.='<td style="width: 5.5%;text-align: right;border-top:1px solid black;"><b>'.$model->amt_money.'</b>&nbsp;</td>';
        $tableFooterHtml.='<td style="width:1%;border-left:2px solid black;border-right:1px solid black;">&nbsp;</td>';

        foreach (ExpenseFun::getAmtTypeOneEx() as $list){
            $style="border-top:1px solid black;border-right:1px solid black;background-color:#BFBFBF;";
            $style.="width:".$list["width"].";";
            $tableOneHeaderHtml.='<th style="'.$style.'" colspan="'.$list["colspan"].'">&nbsp;<b>'.$list["name"].'</b></th>';
        }
        foreach ($tdTwoList as $list){
            $list["name"] = $list["more"]?$list["name"]:"&nbsp;";
            $style="text-align:center;border-top:1px solid black;border-right:1px solid black;";
            $style.="width:".$list["width"].";";
            $tableTwoHeaderHtml.='<th style="'.$style.'"><b>'.$list["name"].'</b></th>';
        }
        if(!empty($this->infoDetail)){
            $style="border-top:1px solid black;border-right:1px solid black;";
            foreach ($this->infoDetail as $infoRow){
                $info_json = json_decode($infoRow["infoJson"],true);
                $tableInfoHtml.="<tr>";
                $tableInfoHtml.='<td style="border-left:2px solid black;'.$style.'text-align:center;">'.ExpenseFun::getKeyNameForList($setNameList,$infoRow["setId"])."</td>";
                $tableInfoHtml.='<td style="'.$style.'text-align:center;">'.$infoRow["infoDate"]."</td>";
                $tableInfoHtml.='<td style="'.$style.'text-align:center;">'.ExpenseFun::getKeyNameForList($amtTypeList,$infoRow["amtType"])."</td>";
                $tableInfoHtml.='<td style="'.$style.'text-align:center;">'.$infoRow["infoRemark"]."</td>";
                $tableInfoHtml.='<td style="'.$style.'text-align:right;">'.$infoRow["infoAmt"]."&nbsp;</td>";
                $tableInfoHtml.='<td style="width:1%;border-left:2px solid black;border-right:1px solid black;">&nbsp;</td>';
                foreach ($tdTwoList as $key=>$list){
                    if(!isset($tableFooterList[$key])){
                        $tableFooterList[$key]="-";
                    }
                    $value = key_exists($key,$info_json)?$info_json[$key]:"";
                    if(!empty($value)){
                        $tableFooterList[$key] = is_numeric($tableFooterList[$key])?($tableFooterList[$key]+$value):$value;
                    }
                    $width="width:".$list["width"].";";
                    $tableInfoHtml.='<td style="'.$style.$width.'text-align:right;">'.$value.'&nbsp;</td>';
                }
                $tableInfoHtml.="</tr>";
            }
        }

        foreach ($tdTwoList as $key=>$list){
            $value = $tableFooterList[$key];
            $style="border-top:1px solid black;border-right:1px solid black;border-bottom:1px solid black;";
            $style.="width:".$list["width"].";";
            $tableFooterHtml.='<td style="'.$style.'text-align:right;">'.$value.'&nbsp;</td>';
        }
        $tableFooterHtml.="</tr>";
        //报销明细
        $html.=<<<EOF
<table border="0" width="{$tableBoxWidth}px" cellspacing="0" cellpadding="0" style="line-height: 18px;">
<tr>
<th colspan="5" style="width:33%;">&nbsp;</th>
<th style="width:1%;border-right:1px solid black;">&nbsp;</th>
<th colspan="{$tdCount}" style="width:66%;border-top:1px solid black;border-right:1px solid black;">&nbsp;<b>费用明细：</b></th>
</tr>
<tr>
<th colspan="3" style="width:16.5%;background-color:#BFBFBF;border-left:2px solid black;border-top:2px solid black;border-right:2px solid black;">&nbsp;<b>PART B:报销明细</b></th>
<th style="width:16.5%;border-bottom:2px solid black;">&nbsp;</th>
<th style="width:1%;border-right:1px solid black;">&nbsp;</th>
{$tableOneHeaderHtml}
</tr>
<tr>
<th style="width:5.5%;text-align:center;border-left:2px solid black;border-top:1px solid black;border-right:1px solid black;"><b>费用归属</b></th>
<th style="width:5.5%;text-align:center;border-top:1px solid black;border-right:1px solid black;"><b>日期</b></th>
<th style="width:5.5%;text-align:center;border-top:1px solid black;border-right:1px solid black;"><b>费用类别</b></th>
<th style="width:11%;text-align:center;border-right:1px solid black;"><b>摘要</b></th>
<th style="width:5.5%;text-align:center;border-right:2px solid black;"><b>金额</b></th>
<th style="width:1%;border-right:1px solid black;">&nbsp;</th>
{$tableTwoHeaderHtml}
</tr>
{$tableInfoHtml}
{$tableFooterHtml}
<tr>
<th style="width:5.5%;border-top:1px solid black;border-right:1px solid black;border-bottom:2px solid black;border-left:2px solid black;"><b style="line-height:35px;">人民币大写</b></th>
<th colspan="4" style="font-size:15px;text-align:center;width:27.5%;border-top:1px solid black;border-right:2px solid black;border-bottom:2px solid black;"><b>{$amt_money_max}</b></th>
</tr>
</table>
EOF;
        $pdf->writeHTML($html, true, false, false, false, '');
        //审核人
        $html=<<<EOF
<table border="0" width="{$tableOneWidth}px" cellspacing="0" cellpadding="0" style="border-bottom: 2px solid black;border-left: 2px solid black;">
<tr style="line-height: 18px;">
<th colspan="2" style="background-color:#BFBFBF;border-left: 2px solid black;border-top: 2px solid black;border-right: 2px solid black;">&nbsp;<b>PART C:审批签字</b></th>
<th colspan="2" style="border-bottom:2px solid black;">&nbsp;</th>
</tr>
<tr style="line-height: 30px;">
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;申请人</td><td style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;</td>
<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;部门负责人</td><td style="width:30%;border-top:1px solid black;border-right:2px solid black;">&nbsp;</td>
</tr>
<tr style="line-height: 30px;">
<td style="border-top:1px solid black;border-right:1px solid black;width:16%">&nbsp;财务部</td><td style="width:34%;border-top:1px solid black;border-right:1px solid black;">&nbsp;</td>
<td style="border-top:1px solid black;border-right:1px solid black;width:20%">&nbsp;总经理</td><td style="width:30%;border-top:1px solid black;border-right:2px solid black;">&nbsp;</td>
</tr>
</table>
EOF;
        $y1=$pdf->GetY();
        $x1=$pdf->GetX()-1;
        $height = $y1<170?170:$y1;
        $pdf->writeHTMLCell(200, 27,$x1,$height, $html,0);

	    return $html;
    }
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDataForSql($connection);
            $this->saveDataForDetail($connection);
			$this->saveDataForInfo($connection);
            $this->updateDocman($connection,'EXPEN');
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

    protected function updateDocman(&$connection, $doctype) {
        if ($this->scenario=='new') {
            $docidx = strtolower($doctype);
            if ($this->docMasterId[$docidx] > 0) {
                $docman = new DocMan($doctype,$this->id,get_class($this));
                $docman->masterId = $this->docMasterId[$docidx];
                $docman->updateDocId($connection, $this->docMasterId[$docidx]);
            }
        }
    }

	protected function saveDataForDetail(&$connection){
        if(!empty($this->fileList)){
            foreach ($this->fileList as $list){
                $field_value = key_exists($list["field_id"],$this->tableDetail)?$this->tableDetail[$list["field_id"]]:null;
                $rs = Yii::app()->db->createCommand()->select("id,field_id")->from("acc_expense_detail")
                    ->where("exp_id=:exp_id and field_id=:field_id",array(
                        ':field_id'=>$list["field_id"],':exp_id'=>$this->id,
                    ))->queryRow();
                if($rs){
                    $connection->createCommand()->update('acc_expense_detail',array(
                        "field_value"=>$field_value,
                    ),"id=:id",array(':id'=>$rs["id"]));
                }else{
                    $connection->createCommand()->insert('acc_expense_detail',array(
                        "exp_id"=>$this->id,
                        "field_id"=>$list["field_id"],
                        "field_value"=>$field_value,
                    ));
                }
            }
        }
    }

	protected function saveDataForInfo(&$connection)
	{
        $uid = Yii::app()->user->id;
		switch ($this->scenario) {
            case 'delete':
                $connection->createCommand()->delete('acc_expense_history', 'exp_id=:id',array(":id"=>$this->id));
                $connection->createCommand()->delete('acc_expense_audit', 'exp_id=:id',array(":id"=>$this->id));
                $connection->createCommand()->delete('acc_expense_info', 'exp_id=:id',array(":id"=>$this->id));
                break;
            case 'new':
                foreach ($this->infoDetail as $list){
                    if(in_array($list["uflag"],array("N","Y"))){
                        $connection->createCommand()->insert("acc_expense_info", array(
                            "exp_id"=>$this->id,
                            "set_id"=>$list["setId"],
                            "info_date"=>$list["infoDate"],
                            "amt_type"=>$list["amtType"],
                            "info_remark"=>$list["infoRemark"],
                            "info_amt"=>$list["infoAmt"],
                            "info_json"=>key_exists("infoJson",$list)?$list["infoJson"]:"[]",
                        ));
                    }
                }
                break;
            case 'edit':
                foreach ($this->infoDetail as $list){
                    switch ($list["uflag"]){
                        case "D"://删除
                            $connection->createCommand()->delete('acc_expense_info', 'id=:id',array(":id"=>$list["id"]));
                            break;
                        case "Y"://修改
                            if(empty($list["id"])){
                                $connection->createCommand()->insert("acc_expense_info", array(
                                    "exp_id"=>$this->id,
                                    "set_id"=>$list["setId"],
                                    "info_date"=>$list["infoDate"],
                                    "amt_type"=>$list["amtType"],
                                    "info_remark"=>$list["infoRemark"],
                                    "info_amt"=>$list["infoAmt"],
                                    "info_json"=>key_exists("infoJson",$list)?$list["infoJson"]:"[]",
                                ));
                            }else{
                                $connection->createCommand()->update("acc_expense_info", array(
                                    "set_id"=>$list["setId"],
                                    "info_date"=>$list["infoDate"],
                                    "amt_type"=>$list["amtType"],
                                    "info_remark"=>$list["infoRemark"],
                                    "info_amt"=>$list["infoAmt"],
                                    "info_json"=>key_exists("infoJson",$list)?$list["infoJson"]:"[]",
                                ), "id=:id and exp_id={$this->id}", array(":id" =>$list["id"]));
                            }
                            break;
                    }
                }
                break;
        }

    }

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_expense where id = :id AND lcu=:lcu and table_type=:table_type";
				break;
			case 'new':
				$sql = "insert into acc_expense(
						employee_id,table_type,apply_date, city, apply_city, status_type, amt_money, remark, reject_note, lcu, lcd) values (
						:employee_id,:table_type,:apply_date, :city, :apply_city, :status_type, :amt_money, :remark, null, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update acc_expense set 
					apply_date = :apply_date, 
					status_type = :status_type,
					amt_money = :amt_money,
					remark = :remark,
					luu = :luu
					where id = :id and table_type=:table_type";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':employee_id')!==false)
			$command->bindParam(':employee_id',$this->employee_id,PDO::PARAM_INT);
		if (strpos($sql,':table_type')!==false)
			$command->bindParam(':table_type',$this->table_type,PDO::PARAM_INT);
		if (strpos($sql,':apply_date')!==false)
			$command->bindParam(':apply_date',$this->apply_date,PDO::PARAM_STR);
		if (strpos($sql,':remark')!==false)
			$command->bindParam(':remark',$this->remark,PDO::PARAM_STR);
		if (strpos($sql,':status_type')!==false)
			$command->bindParam(':status_type',$this->status_type,PDO::PARAM_INT);
		if (strpos($sql,':amt_money')!==false){
            $command->bindParam(':amt_money',$this->amt_money,PDO::PARAM_INT);
        }

		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':apply_city')!==false)
			$command->bindParam(':apply_city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcd')!==false){
            $date = date("Y-m-d H:i:s");
            $command->bindParam(':lcd',$date,PDO::PARAM_STR);
        }
		$command->execute();

        if ($this->scenario=='new'){
            $this->updateThisExpCode($connection);
        }

        $this->saveHistory($connection);
		return true;
	}

	protected function updateThisExpCode($connection){
        $this->id = Yii::app()->db->getLastInsertID();
        $this->exp_code = "OUT".(100000+$this->id);
        $connection->createCommand()->update("acc_expense", array(
            "exp_code"=>$this->exp_code,
        ), "id=:id", array(":id" =>$this->id));
    }

	protected function saveHistory($connection){
        if($this->status_type==2){
            $connection->createCommand()->update("acc_expense", array(
                "audit_user"=>$this->audit_user,
                "audit_json"=>$this->audit_json,
                "current_username"=>$this->current_username,
                "current_num"=>0,
            ), "id=:id", array(":id" =>$this->id));

            $connection->createCommand()->delete('acc_expense_audit', 'exp_id=:id',array(":id"=>$this->id));

            $history_text=array();
            $history_text[]="<span>报销申请，等待审核</span>";
            $history_text[]="<span>审核人：{$this->audit_user}</span>";
            $connection->createCommand()->insert("acc_expense_history", array(
                "exp_id"=>$this->id,
                "history_text"=>implode("<br/>",$history_text),
                "lcu"=>Yii::app()->user->id
            ));
        }
    }

	public function readonly(){
        return $this->getScenario()=='view'||!in_array($this->status_type,array(0,3));
    }

	public function getReadyForAcc(){
        return true;
    }

    //由於列表需要顯示附件數量，導致列表打開太慢，所以保存附件數量
    public function resetFileSum($id=0){
        $id = empty($id)||!is_numeric($id)?0:$id;
        if(!empty($id)){
            $suffix = Yii::app()->params['envSuffix'];
            $sql = "update acc_expense set
              exp_one_num=docman{$suffix}.countdoc('expen',{$id}),
              lud=lud
              WHERE id={$id}
            ";
            Yii::app()->db->createCommand($sql)->execute();
        }
    }

    //對pdf進行默認設置
    protected function resetPDFConfig(&$pdf,$model){
        $pdf->SetTitle($model->exp_code);
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetFont('stsongstdlight', '', 8);
        $t_margin= $pdf->getHeaderHeight()+2;
        $r_margin=5;
        $l_margin=5;
        $pdf->SetMargins($l_margin, $t_margin, $r_margin);
        $h_margin=15;
        $pdf->SetHeaderMargin($h_margin);
        $f_margin=2;
        $pdf->SetFooterMargin($f_margin);
        $b_margin=0;
        $pdf->SetAutoPageBreak(TRUE, $b_margin);
        $pdf->SetPrintHeader(false);
        $pdf->SetPrintFooter(false);
    }
}