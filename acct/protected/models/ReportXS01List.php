<?php

class ReportXS01List extends CListPageModel
{
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'city_name'=>Yii::t('app','city'),
			'first_dt'=>Yii::t('app','first_dt'),
			'sign_dt'=>Yii::t('app','sign_dt'),
			'company_name'=>Yii::t('app','company_name'),
			'type_desc'=>Yii::t('app','type_desc'),
            'service'=>Yii::t('app','service'),
            'amt_paid'=>Yii::t('app','amt_paid'),
            'amt_install'=>Yii::t('app','amt_install'),
            'employee_code'=>Yii::t('app','employee_code'),
            'employee_name'=>Yii::t('app','employee_name'),
            'city'=>Yii::t('app','city'),
            'user_name'=>Yii::t('app','user_name'),
            'comm_total_amount'=>Yii::t('app','comm_total_amount'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city();
		$year=date('Y');
        $month=date('m')-1;
        $sql1 = "select a.id,a.employee_code,a.employee_name,a.city,b.user_name from acc_service_comm_hdr a,hr$suffix.hr_binding b                 
				where  a.year_no='$year'  and a.month_no='$month' and a.city='".$city."' and b.employee_name=a.employee_name
			";
		$sql2 = "select count(a.id)
			      from acc_service_comm_hdr a,hr$suffix.hr_binding b    
				  where  a.year_no='$year'  and a.month_no='$month' and a.city='".$city."' and b.employee_name=a.employee_name
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'employee_code':
					$clause .= General::getSqlConditionClause('a.employee_code',$svalue);
					break;
				case 'employee_name':
					$clause .= General::getSqlConditionClause('a.employee_name',$svalue);
					break;
				case 'city':
					$clause .= General::getSqlConditionClause('a.city',$svalue);
					break;
				case 'user_name':
					$clause .= General::getSqlConditionClause('b.user_name',$svalue);
					break;

			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			$order .= " order by ".$this->orderField." ";
			if ($this->orderType=='D') $order .= "desc ";
		}else{
		    $order ="order by a.id desc";
        }
		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		$sql = $sql1.$clause.$order;

		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$this->attr[] = array(
					'id'=>$record['id'],
					'employee_code'=>$record['employee_code'],
					'employee_name'=>$record['employee_name'],
					'city'=>$record['city'],
					'user_name'=>$record['user_name'],
					'comm_total_amount'=>0,
				);
			}
		}
//        print_r('<pre>');
//        print_r($records);
		$session = Yii::app()->session;
		$session['criteria_XS01'] = $this->getCriteria();
		return true;
	}


    public function editDataByPage($pageNum=1,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $start=date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        $end=date('Y-m-d', strtotime(date('Y-m-31') . ' -1 month'));
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper_w.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper_w.swo_customer_type c on a.cust_type=c.id 
			    inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman = concat_ws('',b.employee_code,b.employee_name) and a.status='A' and a.status_dt>='$start' and a.status_dt<='$end'	  
			";
        $sql2 = "select count(a.id)
				from swoper_w.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper_w.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman = concat_ws('',b.employee_code,b.employee_name) and a.status='A' and a.status_dt>='$start' and a.status_dt<='$end'
			";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'city_name':
                    $clause .= General::getSqlConditionClause('d.name',$svalue);
                    break;
                case 'company_name':
                    $clause .= General::getSqlConditionClause('a.company_name',$svalue);
                    break;
                case 'type_desc':
                    $clause .= General::getSqlConditionClause('c.description',$svalue);
                    break;
                case 'sign_dt':
                    $clause .= General::getSqlConditionClause('a.sign_dt',$svalue);
                    break;
                case 'service':
                    $clause .= General::getSqlConditionClause('a.service',$svalue);
                    break;
                case 'first_dt':
                    $clause .= General::getSqlConditionClause('a.first_dt',$svalue);
                    break;
                case 'amt_install':
                    $clause .= General::getSqlConditionClause('a.amt_install',$svalue);
                    break;
            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        }else{
            $order ="order by id desc";
        }

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $list = array();
        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*12;
                }
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>General::toDate($record['sign_dt']),   //签约时间
                    'first_dt'=>General::toDate($record['first_dt']), //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($sql);
        return true;
    }

    public function endDataByPage($pageNum=1,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $start=date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        $end=date('Y-m-d', strtotime(date('Y-m-31') . ' -1 month'));
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper_w.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper_w.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman = concat_ws('',b.employee_code,b.employee_name) and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'
			";
        $sql2 = "select count(a.id)
				from swoper_w.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper_w.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman = concat_ws('',b.employee_code,b.employee_name) and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'
			";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'city_name':
                    $clause .= General::getSqlConditionClause('d.name',$svalue);
                    break;
                case 'company_name':
                    $clause .= General::getSqlConditionClause('a.company_name',$svalue);
                    break;
                case 'type_desc':
                    $clause .= General::getSqlConditionClause('c.description',$svalue);
                    break;
                case 'sign_dt':
                    $clause .= General::getSqlConditionClause('a.sign_dt',$svalue);
                    break;
                case 'service':
                    $clause .= General::getSqlConditionClause('a.service',$svalue);
                    break;
                case 'first_dt':
                    $clause .= General::getSqlConditionClause('a.first_dt',$svalue);
                    break;
                case 'amt_install':
                    $clause .= General::getSqlConditionClause('a.amt_install',$svalue);
                    break;
            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        }else{
            $order ="order by id desc";
        }

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();

        $list = array();
        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*12;
                }
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>General::toDate($record['sign_dt']),   //签约时间
                    'first_dt'=>General::toDate($record['first_dt']), //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($this);
        return true;
    }

    public function newDataByPage($pageNum=1,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $start=date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        $end=date('Y-m-d', strtotime(date('Y-m-31') . ' -1 month'));
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper_w.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper_w.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman = concat_ws('',b.employee_code,b.employee_name) and a.status='N'  and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $sql2 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper_w.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper_w.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman = concat_ws('',b.employee_code,b.employee_name) and a.status='N' and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $clause = "";
        if (!empty($this->searchField) && !empty($this->searchValue)) {
            $svalue = str_replace("'","\'",$this->searchValue);
            switch ($this->searchField) {
                case 'city_name':
                    $clause .= General::getSqlConditionClause('d.name',$svalue);
                    break;
                case 'company_name':
                    $clause .= General::getSqlConditionClause('a.company_name',$svalue);
                    break;
                case 'type_desc':
                    $clause .= General::getSqlConditionClause('c.description',$svalue);
                    break;
                case 'sign_dt':
                    $clause .= General::getSqlConditionClause('a.sign_dt',$svalue);
                    break;
                case 'service':
                    $clause .= General::getSqlConditionClause('a.service',$svalue);
                    break;
                case 'first_dt':
                    $clause .= General::getSqlConditionClause('a.first_dt',$svalue);
                    break;
                case 'amt_install':
                    $clause .= General::getSqlConditionClause('a.amt_install',$svalue);
                    break;
            }
        }

        $order = "";
        if (!empty($this->orderField)) {
            $order .= " order by ".$this->orderField." ";
            if ($this->orderType=='D') $order .= "desc ";
        }else{
            $order ="order by id desc";
        }

        $sql = $sql2.$clause;
        $this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();

        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        $this->attr = array();
        $sqls = "select a.*,  c.description as type_desc, d.name as city_name					
				from acc_service_comm_copy a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper_w.swo_customer_type c on a.cust_type=c.id 			 
			  where a.hdr_id='$index'   and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $arr = Yii::app()->db->createCommand($sqls)->queryAll();
        if (count($arr) > 0) {
            foreach ($arr as $k=>$arrs) {
                if($arrs['paid_type']=='1'||$arrs['paid_type']=='Y'){
                    $a=$arrs['amt_paid'];
                }else{
                    $a=$arrs['amt_paid']*12;
                }
                $this->attr[] = array(
                    'id'=>$arrs['id'].'+',
                    'company_name'=>$arrs['company_name'],        //客户名称
                    'city_name'=>$arrs['city_name'],               //城市
                    'type_desc'=>$arrs['type_desc'],               //类别
                    'service'=>$arrs['service'],                    //服务频率
                    'sign_dt'=>General::toDate($arrs['sign_dt']),   //签约时间
                    'first_dt'=>General::toDate($arrs['first_dt']), //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$arrs['amt_install'],           //安装金额
                    'status_copy'=>0,           //是否计算
                );
            }
        }
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*12;
                }
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>General::toDate($record['sign_dt']),   //签约时间
                    'first_dt'=>General::toDate($record['first_dt']), //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'status_copy'=>$record['status_copy'],           //是否计算

                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($arr);
        return true;
    }

    public function copy(){
        $suffix = Yii::app()->params['envSuffix'];
        $start=date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        $end=date('Y-m-d', strtotime(date('Y-m-31') . ' -1 month'));
        $sql="select a.id from swoper_w.swo_service a
              inner join acc_service_comm_copy b on b.company_name=a.company_name
              where a.cust_type=b.cust_type and a.city=b.city and a.status_copy='0' and a.status_dt>='$start' and a.status_dt<='$end'
              ";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if(!empty($records)){
            foreach ($records as $record){
                $sql1="update swoper_w.swo_service set status_copy='1' where id='".$record['id']."'";
                $model = Yii::app()->db->createCommand($sql1)->execute();
            }
        }
    }

    public function newSale($id,$index){
        $city = Yii::app()->user->city();
	    $money=0;
        $money1=0;
        $zhuangji=0;
        $start_dt=date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        foreach ($id as $a){
            if(strstr($a,'+')){
                $a=rtrim($a,'+');
                $sql="select * from acc_service_comm_copy where id='$a'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*12;
                }
                if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                    $money+=$a;
                    $cust_type='fw';
                }elseif ($records['cust_type']=='4'){
                    $money1+=$a;
                    $cust_type1='inv';
                }
                $zhuangji+=$records['amt_install'];
            }else{
                $sql="select * from swoper_w.swo_service where id='$a'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*12;
                }
                if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                    $money+=$a;
                    $cust_type='fw';
                }elseif ($records['cust_type']=='4'){
                    $money1+=$a;
                    $cust_type1='inv';
                }
                $zhuangji+=$records['amt_install'];
            }
        }
        if(!empty($cust_type)){
            $fuwu=$this->getAmount($city,$cust_type,$start_dt,$money);//提成比例服务
            $fuwumoney=$money*$fuwu;
        }else{
            $fuwumoney=0;
        }
      if(!empty($cust_type1)){
          $inv=$this->getAmount($city,$cust_type1,$start_dt,$money1);//提成比例inv
          $invmoney=$money1*$inv;
      }else{
          $invmoney=0;
      }
        if(!empty($zhuangji)){
            $zhuangjimoney=$zhuangji*$inv;
        }else{
            $zhuangjimoney=0;
        }

      $salemoney=$fuwumoney+$invmoney+$zhuangjimoney;
        $sql = "insert into acc_service_comm_dtl(
					hdr_id, new_calc, new_amount
				) values (
					'".$index."','".$fuwu."','".$salemoney."'
				)";
        $record = Yii::app()->db->createCommand($sql)->execute();


//                print_r('<pre>');
//                print_r($zhuangji);
    }


    public function editSale($id,$index){
        $city = Yii::app()->user->city();
        $money=0;
        $money1=0;
        $zhuangji=0;
        $start_dt=date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        foreach ($id as $a){
                $sql="select * from swoper_w.swo_service where id='$a'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*12;
                }
                if($records['b4_paid_type']=='1'||$records['b4_paid_type']=='Y'){
                    $b=$records['b4_amt_paid'];
                }else{
                    $b=$records['b4_amt_paid']*12;
                }
                $zhuangji+=$records['amt_install'];
                $c=$a-$b;
                if($c>0){
                    if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                        $money+=$c;
                    }
                    $sql="select new_calc from  acc_service_comm_dtl where hdr_id='$index'";
                    $records = Yii::app()->db->createCommand($sql)->queryRow();
                    $fuwumoney=$money*$records['new_calc'];

                }else{
                    if(!empty($records['all_number'])){
                        $new=$a/$records['all_number'];
                        $old=$b/$records['all_number'];
                    }
                    if(!empty($records['surplus'])){
                        $m=($old-$new)*$records['surplus'];
                    }
                    $sql="select * from  swoper_w.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."'";
                    $records = Yii::app()->db->createCommand($sql)->queryRow();
                    $date=$records['first_dt'];
                    $timestrap=strtotime($date);
                    $year=date('Y',$timestrap);
                    $month=date('m',$timestrap);
                    $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws('',employee_code,employee_name)= '".$records['salesman']."' ";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                   if(!empty($m)){
                       $m=$m*$records2['new_calc'];
                       if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                           $money+=$m;
                       }
                   }

                }
            }
            if(empty($fuwumoney)){
                $fuwumoney=0;
            }
            if(empty($money)){
                $money=0;
            }
            if(empty($zhuangji)){
                $zhuangji=0;
            }
        $fuwumoney=$fuwumoney-$money+$zhuangji;
        $sql1="update acc_service_comm_dtl set edit_amount='$fuwumoney' where hdr_id='".$index."'";
        $model = Yii::app()->db->createCommand($sql1)->execute();

//                print_r('<pre>');
//                print_r($zhuangji);
    }

    public function endSale($id,$index){
        $city = Yii::app()->user->city();
        $money=0;
        $money1=0;
        $zhuangji=0;
        $start_dt=date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        foreach ($id as $a){
            $sql="select * from swoper_w.swo_service where id='$a'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                $a=$records['amt_paid'];
            }else{
                $a=$records['amt_paid']*12;
            }


                if(!empty($records['all_number'])){
                    $new=$a/$records['all_number'];

                }
                if(!empty($records['surplus'])){
                    $m=$new*$records['surplus'];
                }
                $sql="select * from  swoper_w.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$records['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws('',employee_code,employee_name)= '".$records['salesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                if(!empty($m)){
                    $m=$m*$records2['new_calc'];
                    if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                        $money+=$m;
                    }

            }
        }
        if(empty($money)){
            $money=0;
        }
        $sql1="update acc_service_comm_dtl set end_amount='$money' where hdr_id='".$index."'";
        $model = Yii::app()->db->createCommand($sql1)->execute();

//                print_r('<pre>');
//                print_r($zhuangji);
    }

    public  function getAmount($city, $cust_type, $start_dt, $sales_amt) {
        //城市，类别，时间，总金额
        $rtn = 0;
        if (!empty($city) && !empty($cust_type) && !empty($start_dt) && !empty($sales_amt)) {
            $suffix = Yii::app()->params['envSuffix'];
            $suffix = '_w';
            //客户类别
          //  $sql = "select rpt_cat from swoper_w.swo_customer_type where id=$cust_type";
         //   $row = Yii::app()->db->createCommand($sql)->queryRow();
         //   if ($row!==false) {
              //  $type = $row['rpt_cat'];
                $sdate = General::toMyDate($start_dt);
                $sql = "select id from acc_service_rate_hdr where city='$city' and start_dt<'$sdate'   order by start_dt desc limit 1";
                $row = Yii::app()->db->createCommand($sql)->queryRow();
                if ($row!==false) {
                    $id = $row['id'];
                    $sql = "select id, rate from acc_service_rate_dtl
							where hdr_id='$id' and name='$cust_type' and ((sales_amount>=$sales_amt and operator='LE')
							or (sales_amount<$sales_amt and operator='GT'))
							order by sales_amount limit 1
						";
                    $row = Yii::app()->db->createCommand($sql)->queryRow();
                    if ($row!==false) {
                        $rtn =$row['rate'];
                    }
               }
            }
       // }
//                        print_r('<pre>');
//                print_r($row);
        return $rtn;
    }
}