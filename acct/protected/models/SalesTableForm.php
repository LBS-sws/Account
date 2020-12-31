<?php
class SalesTableForm extends CFormModel
{
	public $id;
	public $city;
	public $city_name;
    public $sale;
    public $year;
    public $month;
    public $examine;
    public $attributes;
	public $start_dt;
	public $group = array();
    public $ia;
    public $ia_c;
    public $ia_c_end;
    public $ia_end;
    public $ib;
    public $ib_c;
    public $ib_c_end;
    public $ib_end;
    public $ic;
    public $ic_c;
    public $ic_c_end;
    public $ic_end;
    public $y_ia;
    public $y_ia_c;
    public $y_ia_c_end;
    public $y_ia_end;
    public $y_ib;
    public $y_ib_c;
    public $y_ib_c_end;
    public $y_ib_end;
    public $y_ic;
    public $y_ic_c;
    public $y_ic_c_end;
    public $y_ic_end;
    public $amt_paid;//yue 焗雾
    public $amt_install;//年装机
    public $all_sale;//所有销售产品
    public $y_amt_paid;//年焗雾

    public $paper;//纸
    public $disinfectant;//消毒液
    public $purification;//空气净化
    public $chemical;//化学剂
    public $aromatherapy;//香薰
    public $pestcontrol;//虫控
    public $other;//其他
    public $y_paper;//纸

    public $abc_money;//IAIBIC营业额 A
    public $ia_royalty;//提成点数 B
    public $ib_royalty;//提成点数 C
    public $amt_paid_royalty;//提成点数 焗雾
    public $ic_royalty;//提成点数 租机
    public $xuyue_royalty;//提成点数 续约
    public $amt_install_royalty;//提成点数 装机
    public $sale_royalty;//提成点数 销售
    public $huaxueji_royalty;//提成点数 化学剂
    public $xuyuezhong_royalty;//提成点数 续约终止

    public $ia_money;//金额 B
    public $ib_money;//金额 C
    public $amt_paid_money;//金额 焗雾
    public $ic_money;//金额 租机
    public $xuyue_money;//金额 续约
    public $amt_install_money;//金额 装机
    public $sale_money;//金额 销售
    public $huaxueji_money;//金额 化学剂
    public $ia_end_money;//金额 B
    public $ib_end_money;//金额 C
    public $ic_end_money;//金额 租机
    public $xuyuezhong_money;//金额 续约终止
    public $add_money;//金额 新增的
    public $reduce_money;//金额 减少的
    public $all_money;//金额 总计
    public $supplement_money;//补充金额
    public $final_money;//最终金额
    public $detail = array(
        array('id'=>0,
            'date'=>'',
            'hdr_id'=>0,
            'customer'=>'',
            'type'=>'0',
            'information'=>'',
            'commission'=>'',
            'examine'=>'',
            'uflag'=>'N',
        ),
    );


    public $commission;
    public $ject_remark;
			
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'city'=>Yii::t('misc','City'),
			'city_name'=>Yii::t('misc','City'),
			'start_dt'=>Yii::t('service','Start Date'),
			'operator'=>Yii::t('service','Sign'),
			'sales_amount'=>Yii::t('service','Sales Amount'),
			'rate'=>Yii::t('service','Rate'),
			'name'=>Yii::t('service','Name'),
            'date'=>Yii::t('salestable','Date'),
            'customer'=>Yii::t('salestable','Customer'),
            'type'=>Yii::t('salestable','Type'),
            'information'=>Yii::t('salestable','Information'),
            'commission'=>Yii::t('salestable','Commission'),
            'examine'=>Yii::t('salestable','Examine'),
            'ject_remark'=>Yii::t('salestable','Ject Remark'),
//            'amt_install_royalty'=>Yii::t('service','Name'),
		);
	}

	public function rules()
	{
		return array(
			array('id, city_name,detail,customer,ject_remark','safe'),
			array('','required'),
			array('','validateDetailRecords'),
		);
	}

	public function validateDetailRecords($attribute, $params) {
		$rows = $this->$attribute;
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if ($row['uflag']=='Y') {
					if (!is_numeric($row['sales_amount']))
						$this->addError($attribute, Yii::t('service','Invalid amount').' '.$row['sales_amount']);
					if (!is_numeric($row['rate']))
						$this->addError($attribute, Yii::t('service','Invalid HY PC Rate').' '.$row['rate']);
					if (!is_numeric($row['name']))
						$this->addError($attribute, Yii::t('service','Invalid INV Rate').' '.$row['name']);
				}
			}
		}
	}
	
	public function retrieveData($index)
	{
        $suffix = Yii::app()->params['envSuffix'];
        $sql = "select a.*,b.* from acc_service_comm_hdr a
                 left outer join acc_service_comm_dtl b on a.id=b.hdr_id		
                where a.id=$index ";
        $salerow = Yii::app()->db->createCommand($sql)->queryRow();
        $a=$this->position($index);
        $city = $salerow['city'];
        $new_calc=$this->getCalc($index,$a);
        $start=$salerow['year_no'].'-'. $salerow['month_no'].'-01';
        $end=$salerow['year_no'].'-'. $salerow['month_no'].'-31';
        $a=$salerow['employee_name']." (".$salerow['employee_code'].")";
        $sql1 = "select * from swoper$suffix.swo_service where (commission!=' ' or commission!=0) and status_dt<='$end' and  status_dt>='$start' and (salesman='$a' or  othersalesman='$a')";
        $rows = Yii::app()->db->createCommand($sql1)->queryAll();
        $sql1 = "select * from acc_product where  service_hdr_id='$index'";
        $product = Yii::app()->db->createCommand($sql1)->queryRow();
        $this->sale=$salerow['employee_name'];
        $this->year=$salerow['year_no'];
        $this->month=$salerow['month_no'];
        $this->examine=$product['examine'];
        $this->ject_remark=$product['ject_remark'];
//        print_r('<pre>'); print_r($product);
//        exit();
        if (count($rows) > 0) {
            $this->group = array();
            foreach ($rows as $row) {
                $temp = array();
                if($row['paid_type']=='M'){
                    $amt_paid_a=$row['amt_paid'];//月金额
                    $amt_paid_year_a=$amt_paid_a*$row['ctrt_period'];
                }elseif ($row['paid_type']=='Y'){
                    $amt_paid_a=$row['amt_paid'];//月金额
                    $amt_paid_year_a= $amt_paid_a;
                }else{
                    $amt_paid_a=$row['amt_paid'];//月金额
                    $amt_paid_year_a=  $amt_paid_a;
                }
                if($row['status']=='A'){
                    if($row['paid_type']=='M'){
                        $amt_paid_a=$row['amt_paid']-$row['b4_amt_paid'];//月金额
                        $amt_paid_year_a=$amt_paid_a*$row['ctrt_period'];
                    }elseif ($row['paid_type']=='Y'){
                        $amt_paid_a=$row['amt_paid']-$row['b4_amt_paid'];//月金额
                        $amt_paid_year_a= $amt_paid_a;
                    }else{
                        $amt_paid_a=$row['amt_paid'];//月金额
                        $amt_paid_year_a=  $amt_paid_a;
                    }
                }

                if($row['cust_type_name']==59||$row['cust_type_name']==55||$row['cust_type_name']==57||$row['cust_type_name']==58||$row['cust_type']==6){
                    $temp['status_dt'] = General::toDate($row['status_dt']);//日期
                    $temp['company_name'] = $row['company_name'];//客户名称
                    $temp['ia'] = '';//IA费
                    $temp['ia_c'] = '';//续约IA费
                    $temp['ia_c_end'] = '';//终止续约IA费
                    $temp['ia_end'] = '';//终止IA费
                    $temp['ia_service'] = '';//IA次数月
                    $temp['ib'] = '';//IB费
                    $temp['ib_c'] = '';//续约IB费
                    $temp['ib_c_end'] = '';//终止续约IB费
                    $temp['ib_end'] = '';//终止IB费
                    $temp['ib_service'] = '';//IB次数月
                    $temp['ic'] = '';//IC费
                    $temp['ic_c'] = '';//续约IC费
                    $temp['ic_c_end'] = '';//终止续约IC费
                    $temp['ic_end'] = '';//终止IC费
                    $temp['amt_paid'] = $row['amt_paid'];//焗雾白蚁甲醛雾化
                    $temp['amt_install'] = $row['amt_install']>0?$row['amt_install']:'';//I装机费
                    $temp['paper'] = '';//纸
                    $temp['disinfectant'] = '';//消毒液
                    $temp['purification'] = '';//空气净化
                    $temp['chemical'] = '';//化学剂
                    $temp['aromatherapy'] = '';//香薰
                    $temp['pestcontrol'] = '';//虫控
                    $temp['other'] = '';//其他
                    $temp['othersalesman'] = $row['othersalesman'];//其他
                    $temp['paper_money'] = '';//纸提成
                    $temp['disinfectant_money'] = '';//消毒液提成
                    $temp['purification_money'] = '';//空气净化提成
                    $temp['chemical_money'] ='';//化学剂提成
                    $temp['aromatherapy_money'] = '';//香薰提成
                    $temp['pestcontrol_money'] = '';//虫控提成
                    $temp['other_money'] = '';//其他提成
//                    $temp['commission'] = '';//提成金额
                    //年金额
                    $temp['y_ia'] = '';//IA费
                    $temp['y_ia_c'] = '';//续约IA费
                    $temp['y_ia_c_end'] = '';//终止续约IA费
                    $temp['y_ia_end'] = '';//终止IA费
                    $temp['y_ib'] = '';//IB费
                    $temp['y_ib_c'] = '';//续约IB费
                    $temp['y_ib_c_end'] = '';//终止续约IB费
                    $temp['y_ib_end'] = '';//终止IB费
                    $temp['y_ic'] = '';//IC费
                    $temp['y_ic_c'] = '';//续约IC费
                    $temp['y_ic_c_end'] = '';//终止续约IC费
                    $temp['y_ic_end'] = '';//终止IC费
                    $temp['y_amt_paid'] = $amt_paid_year_a;//焗雾白蚁甲醛雾化
                    $temp['ia_money'] = '';//扣除IA提成
                    $temp['ib_money'] = '';//扣除IB提成
                    $temp['ic_money'] = '';//扣除IC提成
                }else{
                    if($row['cust_type']==1){
                        $temp['status_dt'] = General::toDate($row['status_dt']);//日期
                        $temp['company_name'] = $row['company_name'];//客户名称
                        $temp['ia'] = $row['commission']>0&&$row['status']!='C'&&$row['status']!='T'?$amt_paid_a:'';//IA费
                        $temp['ia_c'] = $row['commission']>0&&$row['status']=='C'?$amt_paid_a:'';//续约IA费
                        $temp['ia_c_end'] = $row['commission']<0&&$row['status']=='C'?$amt_paid_a:'';//终止续约IA费
                        if($row['status']=='T'){
                            $temp['ia_end'] = -$amt_paid_a;//终止IA费
                        }else{
                            $temp['ia_end'] = $row['commission']<0&&$row['status']!='C'?$amt_paid_a:'';//终止IA费
                        }
                        $temp['ia_service'] = $row['service'];//IA次数月
                        $temp['ib'] = '';//IB费
                        $temp['ib_c'] = '';//续约IB费
                        $temp['ib_c_end'] = '';//终止续约IB费
                        $temp['ib_end'] = '';//终止IB费
                        $temp['ib_service'] = '';//IB次数月
                        $temp['ic'] = '';//IC费
                        $temp['ic_c'] = '';//续约IC费
                        $temp['ic_c_end'] = '';//终止续约IC费
                        $temp['ic_end'] = '';//终止IC费
                        $temp['amt_paid'] = '';//焗雾白蚁甲醛雾化
                        $temp['amt_install'] = $row['amt_install']>0?$row['amt_install']:'';//I装机费
                        $temp['paper'] = '';//纸
                        $temp['disinfectant'] = '';//消毒液
                        $temp['purification'] = '';//空气净化
                        $temp['chemical'] = '';//化学剂
                        $temp['aromatherapy'] = '';//香薰
                        $temp['pestcontrol'] = '';//虫控
                        $temp['other'] = '';//其他
                        $temp['othersalesman'] = $row['othersalesman'];//其他
                        $temp['paper_money'] = '';//纸提成
                        $temp['disinfectant_money'] = '';//消毒液提成
                        $temp['purification_money'] = '';//空气净化提成
                        $temp['chemical_money'] ='';//化学剂提成
                        $temp['aromatherapy_money'] = '';//香薰提成
                        $temp['pestcontrol_money'] = '';//虫控提成
                        $temp['other_money'] = '';//其他提成
                     //   $temp['commission'] = $row['commission']<0?$row['commission']:$row['commission']*(empty($row['othersalesman'])?$row['royalty']:$row['royaltys']);//提成金额
                        //年金额
                        $temp['y_ia'] = $row['commission']>0&&$row['status']!='C'&&$row['status']!='T'?$amt_paid_year_a:'';//IA费
                        $temp['y_ia_c'] = $row['commission']>0&&$row['status']=='C'?$amt_paid_year_a:'';//续约IA费
                        $temp['y_ia_c_end'] = $row['commission']<0&&$row['status']=='C'?$amt_paid_year_a:'';//终止续约IA费
                        if($row['status']=='T'){
                            $temp['y_ia_end'] = -$amt_paid_year_a;//终止IA费
                        }else{
                            $temp['y_ia_end'] = $row['commission']<0&&$row['status']!='C'?$amt_paid_year_a:'';//终止IA费
                        }
                        $temp['y_ib'] = '';//IB费
                        $temp['y_ib_c'] = '';//续约IB费
                        $temp['y_ib_c_end'] = '';//终止续约IB费
                        $temp['y_ib_end'] = '';//终止IB费
                        $temp['y_ic'] = '';//IC费
                        $temp['y_ic_c'] = '';//续约IC费
                        $temp['y_ic_c_end'] = '';//终止续约IC费
                        $temp['y_ic_end'] = '';//终止IC费
                        $temp['y_amt_paid'] = '';//焗雾白蚁甲醛雾化
                        $temp['ia_money'] = $row['commission']<0?$row['commission']:'';//扣除IA提成
                        $temp['ib_money'] = '';//扣除IB提成
                        $temp['ic_money'] = '';//扣除IC提成
                    }elseif($row['cust_type']==2){
                        $temp['status_dt'] = General::toDate($row['status_dt']);//日期
                        $temp['company_name'] = $row['company_name'];//客户名称
                        $temp['ia'] = '';//IA费
                        $temp['ia_c'] = '';//续约IA费
                        $temp['ia_c_end'] = '';//终止续约IA费
                        $temp['ia_end'] = '';//终止IA费
                        $temp['ia_service'] = '';//IA次数月
                        $temp['ib'] = $row['commission']>0&&$row['status']!='C'&&$row['status']!='T'?$amt_paid_a:'';//IB费
                        $temp['ib_c'] = $row['commission']>0&&$row['status']=='C'?$amt_paid_a:'';//续约IB费
                        $temp['ib_c_end'] = $row['commission']<0&&$row['status']=='C'?$amt_paid_a:'';//终止续约IB费
                        if($row['status']=='T'){
                            $temp['ib_end'] = -$amt_paid_a;//终止IA费
                        }else{
                            $temp['ib_end'] = $row['commission']<0&&$row['status']!='C'?$amt_paid_a:'';//终止IB费
                        }
                        $temp['ib_service'] = $row['service'];//IB次数月
                        $temp['ic'] = '';//IC费
                        $temp['ic_c'] = '';//续约IC费
                        $temp['ic_c_end'] = '';//终止续约IC费
                        $temp['ic_end'] = '';//终止IC费
                        $temp['amt_paid'] = '';//焗雾白蚁甲醛雾化
                        $temp['amt_install'] = $row['amt_install']>0?$row['amt_install']:'';//I装机费
                        $temp['paper'] = '';//纸
                        $temp['disinfectant'] = '';//消毒液
                        $temp['purification'] = '';//空气净化
                        $temp['chemical'] = '';//化学剂
                        $temp['aromatherapy'] = '';//香薰
                        $temp['pestcontrol'] = '';//虫控
                        $temp['other'] = '';//其他
                        $temp['othersalesman'] = $row['othersalesman'];//其他
                        $temp['paper_money'] = '';//纸提成
                        $temp['disinfectant_money'] = '';//消毒液提成
                        $temp['purification_money'] = '';//空气净化提成
                        $temp['chemical_money'] ='';//化学剂提成
                        $temp['aromatherapy_money'] = '';//香薰提成
                        $temp['pestcontrol_money'] = '';//虫控提成
                        $temp['other_money'] = '';//其他提成
                      //  $temp['commission'] = $row['commission']<0?$row['commission']:$row['commission']*(empty($row['othersalesman'])?$row['royalty']:$row['royaltys']);//提成金额
                        //年金额
                        $temp['y_ia'] = '';//IA费
                        $temp['y_ia_c'] = '';//续约IA费
                        $temp['y_ia_c_end'] = '';//终止续约IA费
                        $temp['y_ia_end'] = '';//终止IA费
                        $temp['y_ib'] = $row['commission']>0&&$row['status']!='C'&&$row['status']!='T'?$amt_paid_year_a:'';//IB费
                        $temp['y_ib_c'] = $row['commission']>0&&$row['status']=='C'?$amt_paid_year_a:'';//续约IB费
                        $temp['y_ib_c_end'] = $row['commission']<0&&$row['status']=='C'?$amt_paid_year_a:'';//终止续约IB费
                        if($row['status']=='T'){
                            $temp['y_ib_end'] = -$amt_paid_year_a;//终止IA费
                        }else{
                            $temp['y_ib_end'] = $row['commission']<0&&$row['status']!='C'?$amt_paid_year_a:'';//终止IB费
                        }
                        $temp['y_ic'] = '';//IC费
                        $temp['y_ic_c'] = '';//续约IC费
                        $temp['y_ic_c_end'] = '';//终止续约IC费
                        $temp['y_ic_end'] = '';//终止IC费
                        $temp['y_amt_paid'] = '';//焗雾白蚁甲醛雾化
                        $temp['ia_money'] = '';//扣除IA提成
                        $temp['ib_money'] =$row['commission']<0?$row['commission']:'';//扣除IB提成
                        $temp['ic_money'] = '';//扣除IC提成
                    }elseif($row['cust_type']==3||$row['cust_type']==5){
                        $temp['status_dt'] = General::toDate($row['status_dt']);//日期
                        $temp['company_name'] = $row['company_name'];//客户名称
                        $temp['ia'] = '';//IA费
                        $temp['ia_c'] = '';//续约IA费
                        $temp['ia_c_end'] = '';//终止续约IA费
                        $temp['ia_end'] = '';//终止IA费
                        $temp['ia_service'] = '';//IA次数月
                        $temp['ib'] = '';//IB费
                        $temp['ib_c'] = '';//续约IB费
                        $temp['ib_c_end'] = '';//终止续约IB费
                        $temp['ib_end'] = '';//终止IB费
                        $temp['ib_service'] = '';//IB次数月
                        $temp['ic'] =$row['commission']>0&&$row['status']!='C'&&$row['status']!='T'?$amt_paid_a: '';//IC费
                        $temp['ic_c'] = $row['commission']>0&&$row['status']=='C'?$amt_paid_a:'';//续约IC费
                        $temp['ic_c_end'] = $row['commission']<0&&$row['status']=='C'?$amt_paid_a:'';//终止续约IC费
                        if($row['status']=='T'){
                            $temp['ic_end'] = -$amt_paid_a;//终止IA费
                        }else{
                            $temp['ic_end'] = $row['commission']<0&&$row['status']!='C'?$amt_paid_a:'';//终止IC费
                        }
                        $temp['amt_paid'] = '';//焗雾白蚁甲醛雾化
                        $temp['amt_install'] = $row['amt_install']>0?$row['amt_install']:'';//I装机费
                        $temp['paper'] = '';//纸
                        $temp['disinfectant'] = '';//消毒液
                        $temp['purification'] = '';//空气净化
                        $temp['chemical'] = '';//化学剂
                        $temp['aromatherapy'] = '';//香薰
                        $temp['pestcontrol'] = '';//虫控
                        $temp['other'] = '';//其他
                        $temp['othersalesman'] = $row['othersalesman'];//其他
                        $temp['paper_money'] = '';//纸提成
                        $temp['disinfectant_money'] = '';//消毒液提成
                        $temp['purification_money'] = '';//空气净化提成
                        $temp['chemical_money'] ='';//化学剂提成
                        $temp['aromatherapy_money'] = '';//香薰提成
                        $temp['pestcontrol_money'] = '';//虫控提成
                        $temp['other_money'] = '';//其他提成
                        //$temp['commission'] = $row['commission']<0?$row['commission']:$row['commission']*(empty($row['othersalesman'])?$row['royalty']:$row['royaltys']);//提成金额
                        //年金额
                        $temp['y_ia'] = '';//IA费
                        $temp['y_ia_c'] = '';//续约IA费
                        $temp['y_ia_c_end'] = '';//终止续约IA费
                        $temp['y_ia_end'] = '';//终止IA费
                        $temp['y_ib'] = '';//IB费
                        $temp['y_ib_c'] = '';//续约IB费
                        $temp['y_ib_c_end'] = '';//终止续约IB费
                        $temp['y_ib_end'] = '';//终止IB费
                        $temp['y_ic'] =$row['commission']>0&&$row['status']!='C'&&$row['status']!='T'?$amt_paid_year_a: '';//IC费
                        $temp['y_ic_c'] = $row['commission']>0&&$row['status']=='C'?$amt_paid_year_a:'';//续约IC费
                        $temp['y_ic_c_end'] = $row['commission']<0&&$row['status']=='C'?$amt_paid_year_a:'';//终止续约IC费
                        if($row['status']=='T'){
                            $temp['y_ic_end'] = -$amt_paid_year_a;//终止IA费
                        }else{
                            $temp['y_ic_end'] = $row['commission']<0&&$row['status']!='C'?$amt_paid_year_a:'';//终止IC费
                        }
                        $temp['y_amt_paid'] = '';//焗雾白蚁甲醛雾化
                        $temp['ia_money'] = '';//扣除IA提成
                        $temp['ib_money'] = '';//扣除IB提成
                        $temp['ic_money'] = $row['commission']<0?$row['commission']:'';//扣除IC提成
                    }
                }
                $this->group[] = $temp;
            }
        }
        //月金额
      //  print_r('<pre>'); print_r($this->group);exit();
        $this->ia=array_sum(array_map(create_function('$val', 'return $val["ia"];'), $this->group));
        $this->ia_c=array_sum(array_map(create_function('$val', 'return $val["ia_c"];'), $this->group));
        $this->ia_c_end=array_sum(array_map(create_function('$val', 'return $val["ia_c_end"];'), $this->group));
        $this->ia_end=array_sum(array_map(create_function('$val', 'return $val["ia_end"];'), $this->group));
        $this->ib=array_sum(array_map(create_function('$val', 'return $val["ib"];'), $this->group));
        $this->ib_c=array_sum(array_map(create_function('$val', 'return $val["ib_c"];'), $this->group));
        $this->ib_c_end=array_sum(array_map(create_function('$val', 'return $val["ib_c_end"];'), $this->group));
        $this->ib_end=array_sum(array_map(create_function('$val', 'return $val["ib_end"];'), $this->group));
        $this->ic=array_sum(array_map(create_function('$val', 'return $val["ic"];'), $this->group));
        $this->ic_c=array_sum(array_map(create_function('$val', 'return $val["ic_c"];'), $this->group));
        $this->ic_c_end=array_sum(array_map(create_function('$val', 'return $val["ic_c_end"];'), $this->group));
        $this->ic_end=array_sum(array_map(create_function('$val', 'return $val["ic_end"];'), $this->group));
//年金额
        $this->y_ia=array_sum(array_map(create_function('$val', 'return $val["y_ia"];'), $this->group));
        $this->y_ia_c=array_sum(array_map(create_function('$val', 'return $val["y_ia_c"];'), $this->group));
        $this->y_ia_c_end=array_sum(array_map(create_function('$val', 'return $val["y_ia_c_end"];'), $this->group));
        $this->y_ia_end=array_sum(array_map(create_function('$val', 'return $val["y_ia_end"];'), $this->group));
        $this->y_ib=array_sum(array_map(create_function('$val', 'return $val["y_ib"];'), $this->group));
        $this->y_ib_c=array_sum(array_map(create_function('$val', 'return $val["y_ib_c"];'), $this->group));
        $this->y_ib_c_end=array_sum(array_map(create_function('$val', 'return $val["y_ib_c_end"];'), $this->group));
        $this->y_ib_end=array_sum(array_map(create_function('$val', 'return $val["y_ib_end"];'), $this->group));
        $this->y_ic=array_sum(array_map(create_function('$val', 'return $val["y_ic"];'), $this->group));
        $this->y_ic_c=array_sum(array_map(create_function('$val', 'return $val["y_ic_c"];'), $this->group));
        $this->y_ic_c_end=array_sum(array_map(create_function('$val', 'return $val["y_ic_c_end"];'), $this->group));
        $this->y_ic_end=array_sum(array_map(create_function('$val', 'return $val["y_ic_end"];'), $this->group));
        $this->y_amt_paid=array_sum(array_map(create_function('$val', 'return $val["y_amt_paid"];'), $this->group));
        $this->abc_money=$this->y_ia+ $this->y_ib+$this->y_ic+$this->y_amt_paid;//iaibic营业额
        $sql_point="select * from sales$suffix.sal_integral where hdr_id='$index' ";
        $point = Yii::app()->db->createCommand($sql_point)->queryRow();
        if(empty($point)){
            $point['point']=0;
        }
        //来源于物流配送的销售的单
        $sql = "select b.log_dt,b.company_name,a.money,a.qty,c.description,c.sales_products,c.id from swoper$suffix.swo_logistic_dtl a
                left outer join swoper$suffix.swo_logistic b on b.id=a.log_id		
               	left outer join swoper$suffix.swo_task c on a.task=c.	id
                where b.log_dt<='$end' and  b.log_dt>='$start' and b.salesman='".$a."' and b.city ='$city' and a.money>0";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        //print_r('<pre>');print_r($rows);exit();
        if(count($rows)>0){
            foreach ($rows as $v){
                    $fuwu=$this->getAmount($city,$v['id'],$v['sales_products'],$start,$this->abc_money);//本单产品提成比例
                    $fuwu=$fuwu+$point['point'];
                    $temp['status_dt'] = General::toDate($v['log_dt']);//日期
                    $temp['company_name'] = $v['company_name'];//客户名称
                    $temp['ia'] = '';//IA费
                    $temp['ia_c'] = '';//续约IA费
                    $temp['ia_c_end'] = '';//终止续约IA费
                    $temp['ia_end'] = '';//终止IA费
                    $temp['ia_service'] = '';//IA次数月
                    $temp['ib'] = '';//IB费
                    $temp['ib_c'] = '';//续约IB费
                    $temp['ib_c_end'] = '';//终止续约IB费
                    $temp['ib_end'] = '';//终止IB费
                    $temp['ib_service'] = '';//IB次数月
                    $temp['ic'] ='';//IC费
                    $temp['ic_c'] = '';//续约IC费
                    $temp['ic_c_end'] = '';//终止续约IC费
                    $temp['ic_end'] = '';//终止IC费
                    $temp['amt_paid'] = '';//焗雾白蚁甲醛雾化
                    $temp['amt_install'] = '';//I装机费
                    $temp['paper'] = $v['sales_products']=='paper'? $v['money']*$v['qty']:'';//纸
                    $temp['disinfectant'] =$v['sales_products']=='disinfectant'? $v['money']*$v['qty']: '';//消毒液
                    $temp['purification'] =$v['sales_products']=='purification'? $v['money']*$v['qty']: '';//空气净化
                    $temp['chemical'] =$v['sales_products']=='chemical'? $v['money']*$v['qty']: '';//化学剂
                    $temp['aromatherapy'] = $v['sales_products']=='aromatherapy'? $v['money']*$v['qty']:'';//香薰
                    $temp['pestcontrol'] = $v['sales_products']=='pestcontrol'? $v['money']*$v['qty']:'';//虫控
                    $temp['other'] = $v['sales_products']=='other'? $v['money']*$v['qty']:'';//其他
                    $temp['paper_money'] = $v['sales_products']=='paper'? $v['money']*$fuwu*$v['qty']:'';//纸提成
                    $temp['disinfectant_money'] =$v['sales_products']=='disinfectant'? $v['money']*$fuwu*$v['qty']: '';//消毒液提成
                    $temp['purification_money'] =$v['sales_products']=='purification'? $v['money']*$fuwu*$v['qty']: '';//空气净化提成
                    $temp['chemical_money'] =$v['sales_products']=='chemical'? $v['money']*$fuwu*$v['qty']: '';//化学剂提成
                    $temp['aromatherapy_money'] = $v['sales_products']=='aromatherapy'? $v['money']*$fuwu*$v['qty']:'';//香薰提成
                    $temp['pestcontrol_money'] = $v['sales_products']=='pestcontrol'? $v['money']*$fuwu*$v['qty']:'';//虫控提成
                    $temp['other_money'] = $v['sales_products']=='other'? $v['money']*$fuwu*$v['qty']:'';//其他提成
                    $temp['othersalesman'] ='';//其他
                    //年金额
                    $temp['y_ia'] = '';//IA费
                    $temp['y_ia_c'] = '';//续约IA费
                    $temp['y_ia_c_end'] = '';//终止续约IA费
                    $temp['y_ia_end'] = '';//终止IA费
                    $temp['y_ib'] = '';//IB费
                    $temp['y_ib_c'] = '';//续约IB费
                    $temp['y_ib_c_end'] = '';//终止续约IB费
                    $temp['y_ib_end'] = '';//终止IB费
                    $temp['y_ic'] ='';//IC费
                    $temp['y_ic_c'] = '';//续约IC费
                    $temp['y_ic_c_end'] = '';//终止续约IC费
                    $temp['y_ic_end'] = '';//终止IC费
                    $temp['y_amt_paid'] = '';//焗雾白蚁甲醛雾化
                    $temp['ia_money'] = '';//扣除IA提成
                    $temp['ib_money'] = '';//扣除IB提成
                    $temp['ic_money'] = '';//扣除IC提成
                    $this->group[] = $temp;
            }
        }
        //产品
        $this->amt_paid=array_sum(array_map(create_function('$val', 'return $val["amt_paid"];'), $this->group));
        $this->amt_install=array_sum(array_map(create_function('$val', 'return $val["amt_install"];'), $this->group));
        $this->paper=array_sum(array_map(create_function('$val', 'return $val["paper"];'), $this->group));
        $this->disinfectant=array_sum(array_map(create_function('$val', 'return $val["disinfectant"];'), $this->group));
        $this->purification=array_sum(array_map(create_function('$val', 'return $val["purification"];'), $this->group));
        $this->chemical=array_sum(array_map(create_function('$val', 'return $val["chemical"];'), $this->group));
        $this->aromatherapy=array_sum(array_map(create_function('$val', 'return $val["aromatherapy"];'), $this->group));
        $this->pestcontrol=array_sum(array_map(create_function('$val', 'return $val["pestcontrol"];'), $this->group));
        $this->other=array_sum(array_map(create_function('$val', 'return $val["other"];'), $this->group));

        $ia_money=array_sum(array_map(create_function('$val', 'return $val["ia_money"];'), $this->group));
        $ib_money=array_sum(array_map(create_function('$val', 'return $val["ib_money"];'), $this->group));
        $ic_money=array_sum(array_map(create_function('$val', 'return $val["ic_money"];'), $this->group));

        $paper_money=array_sum(array_map(create_function('$val', 'return $val["paper_money"];'), $this->group));
        $disinfectant_money=array_sum(array_map(create_function('$val', 'return $val["disinfectant_money"];'), $this->group));
        $purification_money=array_sum(array_map(create_function('$val', 'return $val["purification_money"];'), $this->group));
        //$chemical_money=array_sum(array_map(create_function('$val', 'return $val["chemical_money"];'), $this->group));
        $aromatherapy_money=array_sum(array_map(create_function('$val', 'return $val["aromatherapy_money"];'), $this->group));
        $pestcontrol_money=array_sum(array_map(create_function('$val', 'return $val["pestcontrol_money"];'), $this->group));
        $other_money=array_sum(array_map(create_function('$val', 'return $val["other_money"];'), $this->group));
        // $this->commission=array_sum(array_map(create_function('$val', 'return $val["commission"];'), $this->group));
        $this->all_sale=$this->paper+$this->disinfectant+$this->purification+$this->chemical+$this->aromatherapy+$this->pestcontrol+$this->other;
        $this->ia_royalty=($new_calc+$point['point'])*100;//提成点数 B
        $this->ib_royalty=($new_calc+$point['point'])*100;//提成点数 C
        $this->amt_paid_royalty=($new_calc+$point['point'])*100;//提成点数 焗雾
        $this->ic_royalty=($new_calc+$point['point'])*100;//提成点数 租机
        $this->xuyue_royalty=1;//提成点数 续约
        $amt_install_royalty=$this->getAmount($city,'paper','paper',$start,$this->abc_money);//装机提成比例
        $this->amt_install_royalty=$amt_install_royalty+$point['point'];//提成点数 装机
        $this->sale_royalty="/";//提成点数 销售
        $this->huaxueji_royalty=(0.1+$point['point'])*100;//提成点数 化学剂
        $this->xuyuezhong_royalty=1;//提成点数 续约终止
        $this->ia_money=$this->y_ia*($new_calc+$point['point']);//金额 a
        $this->ib_money=$this->y_ib*($new_calc+$point['point']);//金额 b
        $this->amt_paid_money=$this->y_amt_paid*($new_calc+$point['point']);//金额 焗雾
        $this->ic_money=$this->y_ic*($new_calc+$point['point']);//金额 租机
        $this->xuyue_money= ($this->y_ia_c+ $this->y_ib_c+ $this->y_ic_c)*0.01;//金额 续约
        $this->amt_install_money=$this->amt_install*$amt_install_royalty;//金额 装机
        $this->sale_money=$paper_money+$disinfectant_money+$purification_money+$aromatherapy_money+$pestcontrol_money+$other_money;//金额 销售
        $this->huaxueji_money=$this->chemical*(0.1+$point['point']);//金额 化学剂
        $this->ia_end_money=$ia_money;//金额 B
        $this->ib_end_money=$ib_money;//金额 C
        $this->ic_end_money=$ic_money;//金额 租机
        $this->xuyuezhong_money=($this->y_ia_c_end+ $this->y_ib_c_end+ $this->y_ic_c_end)*0.01;//金额 续约终止
        $this->add_money=$this->ia_money+$this->ib_money+$this->amt_paid_money+$this->ic_money+$this->xuyue_money+$this->amt_install_money+$this->sale_money+$this->huaxueji_money;//金额 新增的
        $this->reduce_money=$this->ia_end_money+$this->ib_end_money+$this->ic_end_money+$this->xuyuezhong_money;//金额 减少的
        $this->all_money=$this->add_money+$this->reduce_money;//金额 总计
        $this->id=$index;

        $sql = "select * from acc_salestable where hdr_id=$index";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($rows) > 0) {
            $this->detail = array();
            foreach ($rows as $row) {
                $temp = array();
                $temp['id'] = $row['id'];
                $temp['hdr_id'] = $row['hdr_id'];
                $temp['customer'] = $row['customer'];
                $temp['type'] = $row['type'];
                $temp['information'] = $row['information'];
                $temp['date'] = General::toDate($row['date']);
                $temp['commission'] = $row['commission'];
                $temp['uflag'] = 'N';
                $temp['examine']=$product['examine'];
                $this->detail[] = $temp;
            }
        }
        $this->supplement_money=array_sum(array_map(create_function('$val', 'return $val["commission"];'), $this->detail));
        $this->final_money=$this->supplement_money+$this->add_money+$this->reduce_money;
        return true;
	}



	public function getCalc($index,$a){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select a.*,b.*,c.name as city_name ,d.group_type from acc_service_comm_hdr a
              left outer join acc_service_comm_dtl b on  b.hdr_id=a.id
              left outer join security$suffix.sec_city c on  a.city=c.code 
              left outer join hr$suffix.hr_employee d on  a.employee_code=d.code 
              where a.id='$index'
";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(!empty($records)){
            $city=Yii::app()->user->city();
            $date=$records['year_no']."/".$records['month_no'].'/'."01";
            $date1='2020/07/01';
            $employee=$this->getEmployee($records['employee_code'],$records['year_no'],$records['month_no']);
            // print_r($a);print_r($employee);
            if($records['city']=='CD'||$records['city']=='FS'||$records['city']=='NJ'||$records['city']=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1){
                $month=$records['month_no'];
                $year=$records['year_no'];
            }else{
                $month=$records['month_no']-1;
                $year=$records['year_no'];
                if($month==0){
                    $month=12;
                    $year=$records['year_no']-1;
                }
            }
            $sql="select employee_name from acc_service_comm_hdr where id=$index";
            $name = Yii::app()->db->createCommand($sql)->queryScalar();
            $sql1="select a.*, b.new_calc ,e.user_id from acc_service_comm_hdr a
              left outer join acc_service_comm_dtl b on  b.hdr_id=a.id
              left outer join hr$suffix.hr_employee d on  a.employee_code=d.code 
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id            
              where  a.year_no='$year' and  a.month_no='$month' and a.employee_name='$name' and d.city='".$records['city']."'
";
            $arr = Yii::app()->db->createCommand($sql1)->queryRow();
            print_r($sql1); print_r($arr);
            return $arr['new_calc'];
        }
	}

    public function position($index){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select * from hr$suffix.hr_employee a
            left outer join  acc_service_comm_hdr b on a.code=b.employee_code
            inner join hr$suffix.hr_dept c on a.position=c.id 
            where  b.id='$index' and (c.manager_type ='1' or c.manager_type ='2')
        ";
        $position = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($position)){
            $records=1;//不加入东成西就
        }else{
            $records=2;
        }
        return $records;
    }
    public  function getEmployee($employee,$year,$month){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select e.user_id from  hr$suffix.hr_employee d                  
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id
              where d.code='$employee'
";
        $records = Yii::app()->db->createCommand($sql)->queryScalar();
        $sql="select entry_time from hr$suffix.hr_employee where code= '".$employee."' ";
        $record = Yii::app()->db->createCommand($sql)->queryScalar();
        $timestraps=strtotime($record);
        $entry_time_year=date('Y',$timestraps);
        $entry_time_month=date('m',$timestraps);
        if($entry_time_year==$year&&$entry_time_month==$month){
            $sql1="select visit_dt from sales$suffix.sal_visit   where username='$records' order by visit_dt
";
            $record = Yii::app()->db->createCommand($sql1)->queryRow();
            $timestrap=strtotime($record['visit_dt']);
            $years=date('Y',$timestrap);
            $months=date('m',$timestrap);
            if($years==$year&&$months==$month){
                $a=1;
            }else{
                $a=2;
            }
        }else{
            $a=2;
        }
        return $a;
    }

    public  function getAmount($city, $cust_type,$sales_products, $start_dt, $sales_amt) {
        //城市，类别，时间，总金额
        $rtn = 0;
        if (!empty($city) && !empty($cust_type) && !empty($start_dt) && !empty($sales_amt)) {
            $suffix = Yii::app()->params['envSuffix'];
            //客户类别
            //  $sql = "select rpt_cat from swoper$suffix.swo_customer_type where id=$cust_type";
            //   $row = Yii::app()->db->createCommand($sql)->queryRow();
            //   if ($row!==false) {
            //  $type = $row['rpt_cat'];
            $sdate = General::toMyDate($start_dt);
            $sql = "select id from acc_product_rate_hdr where city='$city' and start_dt<'$sdate'   order by start_dt desc limit 1";
            $row = Yii::app()->db->createCommand($sql)->queryRow();
            if ($row!==false) {
                $id = $row['id'];
                $sql = "select id, rate from acc_product_rate_dtl
							where hdr_id='$id' and name='$cust_type' 
							order by sales_amount limit 1
						";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                if ($row!==false) {
                    $sql = "select id, rate from acc_product_rate_dtl
							where hdr_id='$id' and name='$cust_type' and ((sales_amount>=$sales_amt and operator='LE')
							or (sales_amount<$sales_amt and operator='GT'))
							order by sales_amount limit 1
						";
                    $row = Yii::app()->db->createCommand($sql)->queryRow();
                    if ($row!==false) {
                        $rtn =$row['rate'];
                    }
                }else{
                    $sql = "select id, rate from acc_product_rate_dtl
							where hdr_id='$id' and name='$sales_products' and ((sales_amount>=$sales_amt and operator='LE')
							or (sales_amount<$sales_amt and operator='GT'))
							order by sales_amount limit 1
						";
                    $row = Yii::app()->db->createCommand($sql)->queryRow();
                    if ($row!==false) {
                        $rtn =$row['rate'];
                    }
                }

            }
        }
        // }
//                        print_r('<pre>');
//                print_r($row);
        return $rtn;
    }

	public function saveData()
	{

		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveHeader($connection);
//			$this->saveDetail($connection);
            $this->saveSalestableDtl($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveHeader(&$connection)
	{
        $sql1="select * from acc_product where service_hdr_id='".$_POST['SalesTableForm']['id']."'";
        $a=$connection->createCommand($sql1)->queryAll();
        if(empty($a)){
            $sql = "insert into acc_product(
					final_money,service_hdr_id
						) values (
						:final_money,:service_hdr_id
						)
						";
        }else{
            $sql = "update acc_product set  
                                       					  
							final_money = :final_money 
						where service_hdr_id = :service_hdr_id
						";
        }

//		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$command=$connection->createCommand($sql);
//        if (strpos($sql,':amt_install_royalty')!==false)
//            $command->bindParam(':amt_install_royalty',$_POST['SalesTableForm']['amt_install_royalty'],PDO::PARAM_INT);
        if (strpos($sql,':final_money')!==false)
            $final_money=round($_POST['SalesTableForm']['final_money'], 2);
            $command->bindParam(':final_money',$final_money,PDO::PARAM_INT);
       // print_r($_POST['SalesTableForm']['id']);exit();
        if (strpos($sql,':service_hdr_id')!==false)
            $command->bindParam(':service_hdr_id',$_POST['SalesTableForm']['id'],PDO::PARAM_INT);
		$command->execute();
		$this->id=$_POST['SalesTableForm']['id'];
		return true;
	}

    protected function saveSalestableDtl(&$connection)
    {
        $city = Yii::app()->user->city();
        $uid = Yii::app()->user->id;
//        print_r('<pre>'); print_r($this->attributes['detail']);
        foreach ($this->attributes['detail'] as $row) {
            $sql = '';
            switch ($this->scenario) {
                case 'delete':
                    $sql = "delete from acc_salestable where hdr_id = :hdr_id ";
                    break;
                case 'new':
                    if ($row['uflag']=='Y') {
                        $sql = "insert into acc_salestable(
									hdr_id, customer, type, information, date,commission,
								   luu, lcu
								) values (
									:hdr_id, :customer, :type, :information, :date,:commission,
									 :luu, :lcu
								)";
                    }
                    break;
                case 'edit':
                    switch ($row['uflag']) {
                        case 'D':
                            $sql = "delete from acc_salestable where id = :id and city = :city";
                            break;
                        case 'Y':
                            $sql = ($row['id']==0)
                                ?
                                "insert into acc_salestable(
									hdr_id, customer, type, information, date,commission,
										 luu, lcu
									) values (
										:hdr_id, :customer, :type, :information, :date,:commission,
										 :luu, :lcu
									)
									"
                                :
                                "update acc_salestable set
										hdr_id = :hdr_id,
										customer = :customer, 
										type=:type,
										information = :information,
										date = :date,
										commission = :commission,
										luu = :luu 
									where id = :id and city = :city
									";
                            break;
                    }
                    break;
            }

            if ($sql != '') {
                $command=$connection->createCommand($sql);
                if (strpos($sql,':hdr_id')!==false)
                    $command->bindParam(':hdr_id',$this->id,PDO::PARAM_INT);
                if (strpos($sql,':customer')!==false) {
                    $command->bindParam(':customer',$row['customer'],PDO::PARAM_STR);
                }
                if (strpos($sql,':type')!==false) {
                    $command->bindParam(':type',$row['type'],PDO::PARAM_STR);
                }
                if (strpos($sql,':information')!==false) {
                    $command->bindParam(':information',$row['information'],PDO::PARAM_STR);
                }
                if (strpos($sql,':date')!==false) {
                    $dead = General::toMyDate($row['date']);
                    $command->bindParam(':date',$dead,PDO::PARAM_STR);
                }
                if (strpos($sql,':commission')!==false)
                    $command->bindParam(':commission',$row['commission'],PDO::PARAM_STR);
                if (strpos($sql,':luu')!==false)
                    $command->bindParam(':luu',$uid,PDO::PARAM_STR);
                if (strpos($sql,':lcu')!==false)
                    $command->bindParam(':lcu',$uid,PDO::PARAM_STR);
                $command->execute();
            }
        }
        return true;
    }



    public function saveExamine(){
	    $sql="update acc_product set  	examine = 'Y' where service_hdr_id = '".$_POST['SalesTableForm']['id']."'";
        $rows = Yii::app()->db->createCommand($sql)->execute();
        $this->id=$_POST['SalesTableForm']['id'];
        return true;
    }

    public function saveAudit(){
        $sql="update acc_product set  	examine = 'A' where service_hdr_id = '".$_POST['SalesTableForm']['id']."'";
        $rows = Yii::app()->db->createCommand($sql)->execute();
        $this->id=$_POST['SalesTableForm']['id'];
        return true;
    }
    public function saveReject(){

        $sql="update acc_product set  	examine = 'S' , ject_remark='".$this->attributes['ject_remark']."' where service_hdr_id = '".$_POST['SalesTableForm']['id']."' ";
        $rows = Yii::app()->db->createCommand($sql)->execute();
        $this->id=$_POST['SalesTableForm']['id'];
        return true;
    }
	public function isReadOnly() {
		return ($this->examine=='Y'||$this->examine=='A');
	}
}
