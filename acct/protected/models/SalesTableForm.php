<?php
class SalesTableForm extends CFormModel
{
	public $id;
	public $city;
	public $city_name;
    public $attributes;
	public $start_dt;
	public $detail = array();
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
    public $final_money;//最终金额


    public $commission;
			
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
//            'amt_install_royalty'=>Yii::t('service','Name'),
		);
	}

	public function rules()
	{
		return array(
			array('id, city_name,','safe'),
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
        $city = Yii::app()->user->city_allow();
        $sql = "select a.*,b.* from acc_service_comm_hdr a
                 left outer join acc_service_comm_dtl b on a.id=b.hdr_id		
                where a.id=$index ";
        $salerow = Yii::app()->db->createCommand($sql)->queryRow();
        $start=$salerow['year_no'].'-'. $salerow['month_no'].'-01';
        $end=$salerow['year_no'].'-'. $salerow['month_no'].'-31';
        $a=$salerow['employee_name']." (".$salerow['employee_code'].")";
        $sql1 = "select * from swoper$suffix.swo_service where commission!=' ' and status_dt<='$end' and  status_dt>='$start' and salesman='$a'";
        $rows = Yii::app()->db->createCommand($sql1)->queryAll();
        $sql1 = "select * from acc_product where  service_hdr_id='$index'";
        $product = Yii::app()->db->createCommand($sql1)->queryRow();
//        print_r('<pre>'); print_r($product);
//        exit();
        if (count($rows) > 0) {
            $this->detail = array();
            foreach ($rows as $row) {
                $temp = array();
                if($row['paid_type']=='M'){
                    $temp['amt_paid']=$row['amt_paid']-$row['b4_amt_paid'];//月金额
                    $temp['amt_paid_year']=$temp['amt_paid']*$row['ctrt_period'];
                }elseif ($row['paid_type']=='Y'){
                    $temp['amt_paid']=$row['amt_paid']-$row['b4_amt_paid'];//月金额
                    $temp['amt_paid_year']=  $temp['amt_paid'];
                }else{
                    $temp['amt_paid']=$row['amt_paid'];//月金额
                    $temp['amt_paid_year']=  $temp['amt_paid'];
                }

                if($row['cust_type_name']==32||$row['cust_type_name']==33||$row['cust_type_name']==30||$row['cust_type_name']==28){
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
                    $temp['y_amt_paid'] = $row['amt_paid_year'];//焗雾白蚁甲醛雾化
                    $temp['ia_money'] = '';//扣除IA提成
                    $temp['ib_money'] = '';//扣除IB提成
                    $temp['ic_money'] = '';//扣除IC提成
                }else{
                    if($row['cust_type']==1){
                        $temp['status_dt'] = General::toDate($row['status_dt']);//日期
                        $temp['company_name'] = $row['company_name'];//客户名称
                        $temp['ia'] = $row['commission']>0&&$row['status']!='C'?$temp['amt_paid']:'';//IA费
                        $temp['ia_c'] = $row['commission']>0&&$row['status']=='C'?$temp['amt_paid']:'';//续约IA费
                        $temp['ia_c_end'] = $row['commission']<0&&$row['status']=='C'?$temp['amt_paid']:'';//终止续约IA费
                        $temp['ia_end'] = $row['commission']<0&&$row['status']!='C'?$temp['amt_paid']:'';//终止IA费
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
                        $temp['y_ia'] = $row['commission']>0&&$row['status']!='C'?$temp['amt_paid_year']:'';//IA费
                        $temp['y_ia_c'] = $row['commission']>0&&$row['status']=='C'?$temp['amt_paid_year']:'';//续约IA费
                        $temp['y_ia_c_end'] = $row['commission']<0&&$row['status']=='C'?$temp['amt_paid_year']:'';//终止续约IA费
                        $temp['y_ia_end'] = $row['commission']<0&&$row['status']!='C'?$temp['amt_paid_year']:'';//终止IA费
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
                        $temp['ib'] = $row['commission']>0&&$row['status']!='C'?$temp['amt_paid']:'';//IB费
                        $temp['ib_c'] = $row['commission']>0&&$row['status']=='C'?$temp['amt_paid']:'';//续约IB费
                        $temp['ib_c_end'] = $row['commission']<0&&$row['status']=='C'?$temp['amt_paid']:'';//终止续约IB费
                        $temp['ib_end'] = $row['commission']<0&&$row['status']!='C'?$temp['amt_paid']:'';//终止IB费
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
                        $temp['y_ib'] = $row['commission']>0&&$row['status']!='C'?$temp['amt_paid_year']:'';//IB费
                        $temp['y_ib_c'] = $row['commission']>0&&$row['status']=='C'?$temp['amt_paid_year']:'';//续约IB费
                        $temp['y_ib_c_end'] = $row['commission']<0&&$row['status']=='C'?$temp['amt_paid_year']:'';//终止续约IB费
                        $temp['y_ib_end'] = $row['commission']<0&&$row['status']!='C'?$temp['amt_paid_year']:'';//终止IB费
                        $temp['y_ic'] = '';//IC费
                        $temp['y_ic_c'] = '';//续约IC费
                        $temp['y_ic_c_end'] = '';//终止续约IC费
                        $temp['y_ic_end'] = '';//终止IC费
                        $temp['y_amt_paid'] = '';//焗雾白蚁甲醛雾化
                        $temp['ia_money'] = '';//扣除IA提成
                        $temp['ib_money'] =$row['commission']<0?$row['commission']:'';//扣除IB提成
                        $temp['ic_money'] = '';//扣除IC提成
                    }elseif($row['cust_type']==3){
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
                        $temp['ic'] =$row['commission']>0&&$row['status']!='C'?$temp['amt_paid']: '';//IC费
                        $temp['ic_c'] = $row['commission']>0&&$row['status']=='C'?$temp['amt_paid']:'';//续约IC费
                        $temp['ic_c_end'] = $row['commission']<0&&$row['status']=='C'?$temp['amt_paid']:'';//终止续约IC费
                        $temp['ic_end'] = $row['commission']<0&&$row['status']!='C'?$temp['amt_paid']:'';//终止IC费
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
                        $temp['y_ic'] =$row['commission']>0&&$row['status']!='C'?$temp['amt_paid_year']: '';//IC费
                        $temp['y_ic_c'] = $row['commission']>0&&$row['status']=='C'?$temp['amt_paid_year']:'';//续约IC费
                        $temp['y_ic_c_end'] = $row['commission']<0&&$row['status']=='C'?$temp['amt_paid_year']:'';//终止续约IC费
                        $temp['y_ic_end'] = $row['commission']<0&&$row['status']!='C'?$temp['amt_paid_year']:'';//终止IC费
                        $temp['y_amt_paid'] = '';//焗雾白蚁甲醛雾化
                        $temp['ia_money'] = '';//扣除IA提成
                        $temp['ib_money'] = '';//扣除IB提成
                        $temp['ic_money'] = $row['commission']<0?$row['commission']:'';//扣除IC提成
                    }
                }
                $this->detail[] = $temp;
            }
        }
        //月金额
        $this->ia=array_sum(array_map(create_function('$val', 'return $val["ia"];'), $this->detail));
        $this->ia_c=array_sum(array_map(create_function('$val', 'return $val["ia_c"];'), $this->detail));
        $this->ia_c_end=array_sum(array_map(create_function('$val', 'return $val["ia_c_end"];'), $this->detail));
        $this->ia_end=array_sum(array_map(create_function('$val', 'return $val["ia_end"];'), $this->detail));
        $this->ib=array_sum(array_map(create_function('$val', 'return $val["ib"];'), $this->detail));
        $this->ib_c=array_sum(array_map(create_function('$val', 'return $val["ib_c"];'), $this->detail));
        $this->ib_c_end=array_sum(array_map(create_function('$val', 'return $val["ib_c_end"];'), $this->detail));
        $this->ib_end=array_sum(array_map(create_function('$val', 'return $val["ib_end"];'), $this->detail));
        $this->ic=array_sum(array_map(create_function('$val', 'return $val["ic"];'), $this->detail));
        $this->ic_c=array_sum(array_map(create_function('$val', 'return $val["ic_c"];'), $this->detail));
        $this->ic_c_end=array_sum(array_map(create_function('$val', 'return $val["ic_c_end"];'), $this->detail));
        $this->ic_end=array_sum(array_map(create_function('$val', 'return $val["ic_end"];'), $this->detail));
//年金额
        $this->y_ia=array_sum(array_map(create_function('$val', 'return $val["y_ia"];'), $this->detail));
        $this->y_ia_c=array_sum(array_map(create_function('$val', 'return $val["y_ia_c"];'), $this->detail));
        $this->y_ia_c_end=array_sum(array_map(create_function('$val', 'return $val["y_ia_c_end"];'), $this->detail));
        $this->y_ia_end=array_sum(array_map(create_function('$val', 'return $val["y_ia_end"];'), $this->detail));
        $this->y_ib=array_sum(array_map(create_function('$val', 'return $val["y_ib"];'), $this->detail));
        $this->y_ib_c=array_sum(array_map(create_function('$val', 'return $val["y_ib_c"];'), $this->detail));
        $this->y_ib_c_end=array_sum(array_map(create_function('$val', 'return $val["y_ib_c_end"];'), $this->detail));
        $this->y_ib_end=array_sum(array_map(create_function('$val', 'return $val["y_ib_end"];'), $this->detail));
        $this->y_ic=array_sum(array_map(create_function('$val', 'return $val["y_ic"];'), $this->detail));
        $this->y_ic_c=array_sum(array_map(create_function('$val', 'return $val["y_ic_c"];'), $this->detail));
        $this->y_ic_c_end=array_sum(array_map(create_function('$val', 'return $val["y_ic_c_end"];'), $this->detail));
        $this->y_ic_end=array_sum(array_map(create_function('$val', 'return $val["y_ic_end"];'), $this->detail));
        $this->y_amt_paid=array_sum(array_map(create_function('$val', 'return $val["y_amt_paid"];'), $this->detail));
        $this->abc_money=$this->y_ia+ $this->y_ib+$this->y_ic+$this->y_amt_paid;//iaibic营业额
        //来源于物流配送的销售的单
        $sql = "select b.log_dt,b.company_name,a.money,a.qty,c.description,c.sales_products,c.id from swoper$suffix.swo_logistic_dtl a
                left outer join swoper$suffix.swo_logistic b on b.id=a.log_id		
               	left outer join swoper$suffix.swo_task c on a.task=c.	id
                where b.log_dt<='$end' and  b.log_dt>='$start' and b.salesman='".$a."' and b.city=$city";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        if(count($rows)>0){
            foreach ($rows as $v){
                $fuwu=$this->getAmount($city,$v['id'],$v['sales_products'],$start,$this->abc_money);//本单产品提成比例
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
                $temp['paper'] = $v['sales_products']=='paper'? $v['money']:'';//纸
                $temp['disinfectant'] =$v['sales_products']=='disinfectant'? $v['money']: '';//消毒液
                $temp['purification'] =$v['sales_products']=='purification'? $v['money']: '';//空气净化
                $temp['chemical'] =$v['sales_products']=='chemical'? $v['money']: '';//化学剂
                $temp['aromatherapy'] = $v['sales_products']=='aromatherapy'? $v['money']:'';//香薰
                $temp['pestcontrol'] = $v['sales_products']=='pestcontrol'? $v['money']:'';//虫控
                $temp['other'] = $v['sales_products']=='other'? $v['money']:'';//其他
                $temp['paper_money'] = $v['sales_products']=='paper'? $v['money']*$fuwu:'';//纸提成
                $temp['disinfectant_money'] =$v['sales_products']=='disinfectant'? $v['money']*$fuwu: '';//消毒液提成
                $temp['purification_money'] =$v['sales_products']=='purification'? $v['money']*$fuwu: '';//空气净化提成
                $temp['chemical_money'] =$v['sales_products']=='chemical'? $v['money']*$fuwu: '';//化学剂提成
                $temp['aromatherapy_money'] = $v['sales_products']=='aromatherapy'? $v['money']*$fuwu:'';//香薰提成
                $temp['pestcontrol_money'] = $v['sales_products']=='pestcontrol'? $v['money']*$fuwu:'';//虫控提成
                $temp['other_money'] = $v['sales_products']=='other'? $v['money']*$fuwu:'';//其他提成
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
                $this->detail[] = $temp;
            }
        }
        //产品
        $this->amt_paid=array_sum(array_map(create_function('$val', 'return $val["amt_paid"];'), $this->detail));
        $this->amt_install=array_sum(array_map(create_function('$val', 'return $val["amt_install"];'), $this->detail));
        $this->paper=array_sum(array_map(create_function('$val', 'return $val["paper"];'), $this->detail));
        $this->disinfectant=array_sum(array_map(create_function('$val', 'return $val["disinfectant"];'), $this->detail));
        $this->purification=array_sum(array_map(create_function('$val', 'return $val["purification"];'), $this->detail));
        $this->chemical=array_sum(array_map(create_function('$val', 'return $val["chemical"];'), $this->detail));
        $this->aromatherapy=array_sum(array_map(create_function('$val', 'return $val["aromatherapy"];'), $this->detail));
        $this->pestcontrol=array_sum(array_map(create_function('$val', 'return $val["pestcontrol"];'), $this->detail));
        $this->other=array_sum(array_map(create_function('$val', 'return $val["other"];'), $this->detail));

        $ia_money=array_sum(array_map(create_function('$val', 'return $val["ia_money"];'), $this->detail));
        $ib_money=array_sum(array_map(create_function('$val', 'return $val["ib_money"];'), $this->detail));
        $ic_money=array_sum(array_map(create_function('$val', 'return $val["ic_money"];'), $this->detail));

        $paper_money=array_sum(array_map(create_function('$val', 'return $val["paper_money"];'), $this->detail));
        $disinfectant_money=array_sum(array_map(create_function('$val', 'return $val["disinfectant_money"];'), $this->detail));
        $purification_money=array_sum(array_map(create_function('$val', 'return $val["purification_money"];'), $this->detail));
        $chemical_money=array_sum(array_map(create_function('$val', 'return $val["chemical_money"];'), $this->detail));
        $aromatherapy_money=array_sum(array_map(create_function('$val', 'return $val["aromatherapy_money"];'), $this->detail));
        $pestcontrol_money=array_sum(array_map(create_function('$val', 'return $val["pestcontrol_money"];'), $this->detail));
        $other_money=array_sum(array_map(create_function('$val', 'return $val["other_money"];'), $this->detail));
        // $this->commission=array_sum(array_map(create_function('$val', 'return $val["commission"];'), $this->detail));
        $this->all_sale=$this->paper+$this->disinfectant+$this->purification+$this->chemical+$this->aromatherapy+$this->pestcontrol+$this->other;
        $this->ia_royalty=$salerow['new_calc']*100;//提成点数 B
        $this->ib_royalty=$salerow['new_calc']*100;//提成点数 C
        $this->amt_paid_royalty=$salerow['new_calc']*100;//提成点数 焗雾
        $this->ic_royalty=$salerow['new_calc']*100;//提成点数 租机
        $this->xuyue_royalty=1;//提成点数 续约
        $this->amt_install_royalty=$product['amt_install_royalty']*100;//提成点数 装机
        $this->sale_royalty="/";//提成点数 销售
        $this->huaxueji_royalty=10;//提成点数 化学剂
        $this->xuyuezhong_royalty=1;//提成点数 续约终止
        $this->ia_money=$this->y_ia*$salerow['new_calc'];//金额 a
        $this->ib_money=$this->y_ib*$salerow['new_calc'];//金额 b
        $this->amt_paid_money=$this->y_amt_paid*$salerow['new_calc'];//金额 焗雾
        $this->ic_money=$this->y_ic*$salerow['new_calc'];//金额 租机
        $this->xuyue_money= ($this->y_ia_c+ $this->y_ib_c+ $this->y_ic_c)*0.01;//金额 续约
        $this->amt_install_money=$this->amt_install*$product['amt_install_royalty'];//金额 装机
        $this->sale_money=$paper_money+$disinfectant_money+$purification_money+$aromatherapy_money+$pestcontrol_money+$other_money;//金额 销售
        $this->huaxueji_money=$chemical_money;//金额 化学剂
        $this->ia_end_money=$ia_money;//金额 B
        $this->ib_end_money=$ib_money;//金额 C
        $this->ic_end_money=$ic_money;//金额 租机
        $this->xuyuezhong_money=($this->y_ia_c_end+ $this->y_ib_c_end+ $this->y_ic_c_end)*0.01;//金额 续约终止
        $this->add_money=$this->ia_money+$this->ib_money+$this->amt_paid_money+$this->ic_money+$this->xuyue_money+$this->amt_install_money+$this->sale_money+$this->huaxueji_money;//金额 新增的
        $this->reduce_money=$this->ia_end_money+$this->ib_end_money+$this->ic_end_money+$this->xuyuezhong_money;//金额 减少的
        $this->all_money=$this->add_money+$this->reduce_money;//金额 总计
        $this->final_money=$product['final_money'];
        $this->id=$index;
        return true;
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
            $sql = "select id from acc_product_rate_hdr where city=$city and start_dt<'$sdate'   order by start_dt desc limit 1";
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
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveHeader(&$connection)
	{
        $sql1="select * from acc_product where service_hdr_id='".$_POST['SalesTableForm']['id']."'";
        $a=$connection->createCommand($sql1)->queryAll();
		if(empty($a)){
            $sql = "insert into acc_product(
						amt_install_royalty,final_money,service_hdr_id
						) values (
						:amt_install_royalty,:final_money,:service_hdr_id
						)
						";
        }else{
            $sql = "update acc_product set  
                            amt_install_royalty = :amt_install_royalty,                     					  
							final_money = :final_money 
						where service_hdr_id = :service_hdr_id
						";
        }
//		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$command=$connection->createCommand($sql);
        if (strpos($sql,':amt_install_royalty')!==false)
            $command->bindParam(':amt_install_royalty',$_POST['SalesTableForm']['amt_install_royalty'],PDO::PARAM_INT);
        if (strpos($sql,':final_money')!==false)
            $command->bindParam(':final_money',$_POST['SalesTableForm']['final_money'],PDO::PARAM_INT);
       // print_r($_POST['SalesTableForm']['id']);exit();
        if (strpos($sql,':service_hdr_id')!==false)
            $command->bindParam(':service_hdr_id',$_POST['SalesTableForm']['id'],PDO::PARAM_INT);
		$command->execute();
		$this->id=$_POST['SalesTableForm']['id'];
		return true;
	}

//	protected function saveDetail(&$connection)
//	{
//		$uid = Yii::app()->user->id;
//
//		foreach ($_POST['SRateForm']['detail'] as $row) {
//			$sql = '';
//			switch ($this->scenario) {
//				case 'delete':
//					$sql = "delete from acc_service_rate_dtl where hdr_id = :hdr_id";
//					break;
//				case 'new':
//					if ($row['uflag']=='Y') {
//						$sql = "insert into acc_service_rate_dtl(
//									hdr_id, operator, sales_amount, rate, name,
//									luu, lcu
//								) values (
//									:hdr_id, :operator, :sales_amount, :rate, :name,
//									:luu, :lcu
//								)";
//					}
//					break;
//				case 'edit':
//					switch ($row['uflag']) {
//						case 'D':
//							$sql = "delete from acc_service_rate_dtl where id = :id";
//							break;
//						case 'Y':
//							$sql = ($row['id']==0)
//									?
//									"insert into acc_service_rate_dtl(
//										hdr_id, operator, sales_amount, rate, name,
//										luu, lcu
//									) values (
//										:hdr_id, :operator, :sales_amount, :rate, :name,
//										:luu, :lcu
//									)"
//									:
//									"update acc_service_rate_dtl set
//										hdr_id = :hdr_id,
//										operator = :operator,
//										sales_amount = :sales_amount,
//										hy_pc_rate = :hy_pc_rate,
//										inv_rate = :inv_rate,
//										luu = :luu
//									where id = :id
//									";
//							break;
//					}
//					break;
//			}
//
//			if ($sql != '') {
////                print_r('<pre>');
////                print_r($sql);exit();
//				$command=$connection->createCommand($sql);
//				if (strpos($sql,':id')!==false)
//					$command->bindParam(':id',$row['id'],PDO::PARAM_INT);
//				if (strpos($sql,':hdr_id')!==false)
//					$command->bindParam(':hdr_id',$this->id,PDO::PARAM_INT);
//				if (strpos($sql,':operator')!==false)
//					$command->bindParam(':operator',$row['operator'],PDO::PARAM_STR);
//				if (strpos($sql,':sales_amount')!==false) {
//					$amt = General::toMyNumber($row['sales_amount']);
//					$command->bindParam(':sales_amount',$amt,PDO::PARAM_STR);
//				}
//				if (strpos($sql,':rate')!==false) {
//					$rate1 = General::toMyNumber($row['rate']);
//					$command->bindParam(':rate',$rate1,PDO::PARAM_STR);
//				}
//				if (strpos($sql,':name')!==false) {
//
//					$command->bindParam(':name',$row['name'],PDO::PARAM_STR);
//				}
//				if (strpos($sql,':luu')!==false)
//					$command->bindParam(':luu',$uid,PDO::PARAM_STR);
//				if (strpos($sql,':lcu')!==false)
//					$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
//				$command->execute();
//			}
//		}
//		return true;
//	}
	
	public function isReadOnly() {
		return ($this->scenario=='view');
	}
}
