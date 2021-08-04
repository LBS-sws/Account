<?php

class ReportXS01SList extends CListPageModel
{
    public $noOfItem=0;
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
	
	public function retrieveDataByPage($pageNum=1,$year,$month)
	{
//        print_r('<pre>');
//        print_r($month);
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city();
        $month=$month-1;
        if($month==0){
            $month=12;
            $year=$year-1;
        }
        $sql1 = "select a.*,c.name,d.new_amount,d.edit_amount,d.end_amount,d.performance_amount,d.performanceedit_amount,d.performanceend_amount,d.renewal_amount,d.renewalend_amount,e.name as cityname from acc_service_comm_hdr a
                 inner join  hr$suffix.hr_employee b  on b.code=a.employee_code
                 inner join  hr$suffix.hr_dept c on b.position=c.id      
                 inner join security$suffix.sec_city e on a.city=e.code 		  
                 left outer join  acc_service_comm_dtl d on a.id=d.hdr_id            
			     where  a.year_no='$year'  and a.month_no='$month' and a.city='".$city."' and b.city='$city'
			";
		$sql2 = "select count(a.id) from acc_service_comm_hdr a
			      inner join  hr$suffix.hr_employee b  on b.code=a.employee_code
                 inner join  hr$suffix.hr_dept c on b.position=c.id   
                  inner join security$suffix.sec_city e on a.city=e.code 		   
                  left outer join  acc_service_comm_dtl d on a.id=d.hdr_id          
			     where  a.year_no='$year'  and a.month_no='$month' and a.city='".$city."' and b.city='$city'
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
					$clause .= General::getSqlConditionClause('e.name',$svalue);
					break;
				case 'user_name':
					$clause .= General::getSqlConditionClause('c.name',$svalue);
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
                $str=str_replace('(','',$record['employee_code']);
                $str=str_replace(')','',$str);
                $arr=$record['new_amount']+$record['edit_amount']+$record['end_amount']+$record['performance_amount']+$record['performanceedit_amount']+$record['performanceend_amount']+$record['renewal_amount']+$record['renewalend_amount'];
				$this->attr[] = array(
					'id'=>$record['id'],
					'employee_code'=>$str,
					'employee_name'=>$record['employee_name'],
					'city'=>$record['cityname'],
                    'time'=>$record['year_no']."/".$record['month_no'],
					'user_name'=>$record['name'],
					'comm_total_amount'=>$arr,
                    'year'=>$year,
                    'month'=>$month,

				);
			}
		}
//        print_r('<pre>');
//        print_r($sql1);
		$session = Yii::app()->session;
		$session['criteria_XS01'] = $this->getCriteria();
		return true;
	}


    public function editDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
			    inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='A' and a.status_dt>='$start' and a.status_dt<='$end'	  
			";
        $sql2 = "select count(a.id)
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='A' and a.status_dt>='$start' and a.status_dt<='$end'
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
                    $a=$record['amt_paid']*$record['ctrt_period'];
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
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royalty'],           //提成比例
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($sql);
        return true;
    }

    public function endDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'
			";
        $sql2 = "select count(a.id)
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'
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
                    $a=$record['amt_paid']*$record['ctrt_period'];
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
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royalty'],           //提成比例
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($this);
        return true;
    }

    public function performanceDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='N' and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $sql2 = "select count(a.id)
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='N' and a.first_dt>='$start' and a.first_dt<='$end'
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
        $sqls = "select a.*,  c.description as type_desc, d.name as city_name					
				from acc_service_comm_copy a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			 
			  where a.othersalesman='".$name['name']."'   and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $arr = Yii::app()->db->createCommand($sqls)->queryAll();
        $sql3="select performance from acc_service_comm_hdr where  id='$index'";
        $color = Yii::app()->db->createCommand($sql3)->queryRow();

        if (count($arr) > 0) {
            foreach ($arr as $k=>$arrs) {
                if($arrs['paid_type']=='1'||$arrs['paid_type']=='Y'){
                    $a=$arrs['amt_paid'];
                }else{
                    $a=$arrs['amt_paid']*$arrs['ctrt_period'];
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
                    'othersalesman'=>$arrs['othersalesman'],           //跨区业务员
                    'color'=>$color['performance'],
                );
            }
        }
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*$record['ctrt_period'];
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
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'color'=>$color['performance'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($sql);
        return true;
    }

    public function performanceeditDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
			    inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='A' and a.status_dt>='$start' and a.status_dt<='$end'	  
			";
        $sql2 = "select count(a.id)
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='A' and a.status_dt>='$start' and a.status_dt<='$end'
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
                    $a=$record['amt_paid']*$record['ctrt_period'];
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
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royaltys'],           //提成比例
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($sql);
        return true;
    }

    public function performanceendDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'
			";
        $sql2 = "select count(a.id)
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'
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
                    $a=$record['amt_paid']*$record['ctrt_period'];
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
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royaltys'],           //提成比例
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($this);
        return true;
    }

    public function newDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='N'  and a.first_dt>='$start' and a.first_dt<='$end' and a.cust_type!='9'
			";
        $sql2 = "select count(a.id)			
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
		    	where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='N'  and a.first_dt>='$start' and a.first_dt<='$end' and a.cust_type!='9'
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
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			 
			  where a.hdr_id='$index'   and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $arr = Yii::app()->db->createCommand($sqls)->queryAll();
        if (count($arr) > 0) {
            foreach ($arr as $k=>$arrs) {
                if($arrs['paid_type']=='1'||$arrs['paid_type']=='Y'){
                    $a=$arrs['amt_paid'];
                }else{
                    $a=$arrs['amt_paid']*$arrs['ctrt_period'];
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
                    'othersalesman'=>$arrs['othersalesman'],           //跨区业务员
                );
            }
        }
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*$record['ctrt_period'];
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
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($this);
        return true;
    }

    public function copy(){
        $suffix = Yii::app()->params['envSuffix'];
        $start=date('Y-m-d', strtotime(date('Y-m-01') . ' -1 month'));
        $end=date('Y-m-d', strtotime(date('Y-m-31') . ' -1 month'));
        $sql="select a.id from swoper$suffix.swo_service a
              inner join acc_service_comm_copy b on b.company_name=a.company_name
              where a.cust_type=b.cust_type and a.city=b.city and a.status_copy='0' and a.status_dt>='$start' and a.status_dt<='$end'
              ";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if(!empty($records)){
            foreach ($records as $record){
                $sql1="update swoper$suffix.swo_service set status_copy='1' where id='".$record['id']."'";
                $model = Yii::app()->db->createCommand($sql1)->execute();
            }
        }
    }
//清空不需要计算的值
    public function clearNewServiceCommission($index){
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()
            ->select('year_no,month_no,employee_code,employee_name')
            ->from("acc_service_comm_hdr")
            ->where('id=:id',array(":id"=>$index))->queryRow();
        if($row){
            $strtime = strtotime($row["year_no"]."/".$row["month_no"]."/01");
            $startDate = date("Y-m-01",$strtime);
            $endDate = date("Y-m-31",$strtime);
            $salesman = $row["employee_name"]." (".$row["employee_code"].")";
            Yii::app()->db->createCommand()->update("acc_service_comm_copy",array(
                'commission'=>null
            ),"status = 'N' and first_dt between '$startDate' and '$endDate' and salesman='$salesman'");
            Yii::app()->db->createCommand()->update("swoper$suffix.swo_service",array(
                'commission'=>null
            ),"status = 'N' and first_dt between '$startDate' and '$endDate' and salesman='$salesman'");
        }
    }

    public function newSale($id,$year,$month,$index){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
	    $money=0;
        $money1=0;
        $reward = 0;//服务奖励点
        $zhuangji=0;
        $moneys=0;
        $start_dt=$year."-".$month."-01";
        //清空不需要计算的值
        $this->clearNewServiceCommission($index);

        foreach ($id as $ai){

            if(strstr($ai,'+')){
                $ai=rtrim($ai,'+');
                $sql="select * from acc_service_comm_copy where id='$ai'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                if(empty($spanning)){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index)));
                }

                if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                    $money+=$a;
                    $cust_type='fw';
                    if(!empty($records['othersalesman'])){
                        $commission=$a*$spanning;
                        $moneys+=$commission;
                    }else{
                        $commission=$a;
                        $moneys+=$commission;
                    }
                    //判断新增
                    if($commission<=0){
                        $commission = $records['amt_paid']*$records['ctrt_period'];
                    }
                    $sqlct="update acc_service_comm_copy set commission='".$commission."'  where id='$ai'";
                    $model = Yii::app()->db->createCommand($sqlct)->execute();
                }elseif ($records['cust_type']=='4'){
                    $money1+=$a;
                    $cust_type1='inv';
                }
             //   $zhuangji+=$records['amt_install'];
            }else{
                $sql="select * from swoper$suffix.swo_service where id='$ai'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();

                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    if(empty($records['ctrt_period'])){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index)));
                    }
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                if(empty($spanning)){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index)));
                }

                if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                    $money+=$a;
                    $cust_type='fw';
                    if(!empty($records['othersalesman'])){
                        $commission=$a*$spanning;
                        $moneys+=$commission;
                    }else{
                        $commission=$a;
                        $moneys+=$commission;
                    }
                    if($commission<=0){
                        $commission = $records['amt_paid']*$records['ctrt_period'];
                    }
                    $sqlct="update swoper$suffix.swo_service set commission='".$commission."'  where id='$ai'";
                    $model = Yii::app()->db->createCommand($sqlct)->execute();
                }elseif ($records['cust_type']=='4'){
                    $money1+=$a;
                    $cust_type1='inv';
                }
           //     $zhuangji+=$records['amt_install'];
            }
        }

        if(!empty($cust_type)){
            $sql_edit_money="select edit_money from acc_service_comm_dtl where hdr_id='$index'";
            $records_edit_money = Yii::app()->db->createCommand($sql_edit_money)->queryRow();
            if(!empty($records_edit_money)){
                $money_all=$money+$records_edit_money['edit_money'];
            }else{
                $money_all=$money;
            }
            $reward = ReportXS01Form::serviceReward('','',$year."/".$month,$records['salesman']);//服务奖励点
            $fuwu=$this->getAmount($city,$cust_type,$start_dt,$money_all);//提成比例服务
            $point=$this->getPoint($year,$month,$index);//积分激励点      print_r($months);
            $records['salesman']=str_replace('(','',$records['salesman']);
            $records['salesman']=str_replace(')','',$records['salesman']);
            $new_employee=$this->getEmployee($records['salesman'],$year,$month);
            //新增判断当月是否入职月
            if($new_employee==1){
                $employee_code = $records['salesman'];
                $sql_r="select e.user_id from  hr$suffix.hr_employee d                  
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id
              where concat_ws(' ',d.name,d.code)='$employee_code'
";
                $records_r = Yii::app()->db->createCommand($sql_r)->queryScalar();
                $sql1_r="select visit_dt from sales$suffix.sal_visit   where username='$records_r' order by visit_dt
";
                $record_r = Yii::app()->db->createCommand($sql1_r)->queryRow();
                $timestrap=strtotime($record_r['visit_dt']);
                $year_rz=date('Y',$timestrap);
                $month_rz=date('m',$timestrap);
                $day_rz=intval(date('d',$timestrap));//第一条记录不是一号则当月不计算激励点
                if($year_rz==intval($year)&&$month_rz==intval($month)&&$day_rz!=1){
                    $point=0;
                }
            }
            $a=$this->position($index);
            if($a==1){
                $point=0;
            }
            $fuwus=$fuwu+$point+$reward;
            $fuwumoney=$moneys*$fuwus;

        }else{
            if(empty($cust_type)){
                Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index)));
            }
        }

      if(!empty($cust_type1)){
          $inv=$this->getAmount($city,$cust_type1,$start_dt,$money1);//提成比例inv

          $invmoney=$money1*$inv;
      }else{
          $invmoney=0;
      }
//        if(!empty($zhuangji)&&!empty($inv)){
//            $zhuangjimoney=$zhuangji*$inv;
//        }else{
//            $zhuangjimoney=0;
//        }
        //判断是否计算
        $citys = Yii::app()->user->city();
        $sql3="select * from sales$suffix.sal_performance where city='$citys' and year='$year'  and month='$month'";
        $sum = Yii::app()->db->createCommand($sql3)->queryRow();

        if(empty($sum)){$sum=0;}
        if(empty($money)){$money=0;}
        if($sum['sum']<=count($id)||$sum['sums']<=$money){
            $color=1; //计算
        }else{
            $color=2;//不计算
        }

        $salemoney=$fuwumoney+$invmoney;

        $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($records)){
            $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, new_calc, new_amount,new_money
				) values (
					'".$index."','".$fuwu."','".$salemoney."','".$money."'
				)";
        }else{
            $sql1="update acc_service_comm_dtl set new_calc='$fuwu' , new_amount='".$salemoney."',new_money='".$money."' where hdr_id='$index'";
        }
//print_r($sql1);exit();
        $sql2="update acc_service_comm_hdr set performance='$color'  where id='$index'";
        $records = Yii::app()->db->createCommand($sql2)->execute();
        $record = Yii::app()->db->createCommand($sql1)->execute();


//                print_r('<pre>');
//                print_r($zhuangji);
    }


    public function editSale($id,$index,$royalty,$years,$months){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $money=0;
        $reward=0;
        $money1=0;//更改減少
        $moneys=0;//更改增多
        $start_dt=$years."-".$months."-01";
      //  $zhuangji=0;
        foreach ($id as $ai){
                $sql="select * from swoper$suffix.swo_service where id='$ai'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                $reward = ReportXS01Form::serviceReward('','',$years."/".$months,$records['salesman']);//服务奖励点
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                if($records['b4_paid_type']=='1'||$records['b4_paid_type']=='Y'){
                    $b=$records['b4_amt_paid'];
                }else{
                    $b=$records['b4_amt_paid']*$records['ctrt_period'];
                }
             //   $zhuangji+=$records['amt_install'];
                $c=$a-$b;
                if($c>0){//大於零更改新增   小於零更改減少
//                    $sql="select new_calc from  acc_service_comm_dtl where hdr_id='$index'";
//                    $record = Yii::app()->db->createCommand($sql)->queryRow();
                   // $fuwumoney=$c*$record['new_calc'];
                    $spanning=$this->getRoyalty($index,$city,$years,$months,$records['othersalesman']);
                    if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                        $cust_type='fw';
                        $moneys+=$c;
                        if(!empty($records['othersalesman'])){
                            $commission=$c*$spanning;
                            $money+=$commission;
                        }else{
                            $commission=$c;
                            $money+=$commission;
                        }
                        $sqlct="update swoper$suffix.swo_service set commission='".$commission."'  where id='$ai'";
                        $model = Yii::app()->db->createCommand($sqlct)->execute();
                    }
                }else{
                    if($records['all_number']!=NULL){
                        if($records['all_number']==0){
                            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                            Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/edit',array('year'=>$years,'month'=>$months,'index'=>$index)));
                        }
                        $new=$a/$records['all_number'];
                        $old=$b/$records['all_number'];
                    }
                    if($records['surplus']!=NULL){
                        $m=($new-$old)*$records['surplus'];
                    }else{
                        $m=0;
                    }
                    //当初提成比例
//                    $records['company_name']=str_replace(' ','',$records['company_name']);
                    $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N' and salesman='".$records['salesman']."' order by status_dt desc";
                    $recordss = Yii::app()->db->createCommand($sql)->queryRow();
                    $date=$recordss['first_dt'];
                    $timestrap=strtotime($date);
                    $year=date('Y',$timestrap);
                    $month=date('m',$timestrap);
                    $salesman = $records['salesman'];
                    $records['salesman']=str_replace('(','',$records['salesman']);
                    $records['salesman']=str_replace(')','',$records['salesman']);
                    $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['salesman']."' ";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    ReportXS01SList::resetYearAndMonth($year,$month,$recordss['city'],$records1['id']);//需要计算服务单有没有东成西就
                    $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['salesman']."' ";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
//                    print_r($sql); print_r($recordss);exit();
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                    $point=$this->getPoint($year,$month,$index);//积分激励点
                    $reward = ReportXS01Form::serviceReward('','',$year."/".$month,$salesman);//服务奖励点

                    $records2['new_calc'] = $records2['new_calc']<=0?0.05:$records2['new_calc'];
                    $fuwu_last=$point+$records2['new_calc']+$reward;
                   if(isset($m)){
                       if(!empty($records2)){
                           $m=$m*$fuwu_last;
                           if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                               $cust_type='fw';
                               if(!empty($records['othersalesman'])){
                                   $commission=$m*$spanning;
                                   $money1+=$commission;
                               }else{
                                   $commission=$m;
                                   $money1+=$commission;
                               }
                               $sqlct="update swoper$suffix.swo_service set royalty='".$fuwu_last."',commission='".$commission."'  where id='$ai'";
                               $model = Yii::app()->db->createCommand($sqlct)->execute();
                           }
                       }else{
                           $m=$m*$royalty[$ai];
                           if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                               $cust_type='fw';
                               if(!empty($records['othersalesman'])){
                                   $commission=$m*$spanning;
                                   $money1+=$commission;
                               }else{
                                   $commission=$m;
                                   $money1+=$commission;
                               }
                           }
                           $sqlct="update swoper$suffix.swo_service set royalty='".$royalty[$ai]."',commission='".$commission."'  where id='$ai'";
                           $model = Yii::app()->db->createCommand($sqlct)->execute();
                       }
                   }
                }
            }
            if(empty($money1)){
                $money1=0;
            }
            if(empty($money)){
                $money=0;
            }
        if(empty($moneys)){
            $moneys=0;
        }
        $sql_new_money="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records_new_money = Yii::app()->db->createCommand($sql_new_money)->queryRow();
        if(!empty($records_new_money)){
            if(!empty($records_new_money['new_calc'])&&$records_new_money['new_calc']!=0){
                $new_m=$records_new_money['new_money'];
            }else{
                $new_moneyss=0;
                $new_m=0;
            }
            $new_money=$moneys+$new_m;
        }else{
            $new_money=$moneys;
        }
        $fuwu=$this->getAmount($city,$cust_type,$start_dt,$new_money);//提成比例服务
        //die();
        $point=$this->getPoint($years,$months,$index);//积分激励点
        $fuwus=$fuwu+$point+$reward;
//            if(empty($zhuangji)){
//                $zhuangji=0;
//            }
        $money=$money*$fuwus;//更改新增提成
        $fuwumoney=$money+$money1;//更改总和
        //新增补充修改
        if(!empty($records_new_money['new_calc'])&&$records_new_money['new_calc']>0){
            $new_moneyss=$records_new_money['new_amount']/ $records_new_money['new_calc'];
          $new_amount=$new_moneyss*$fuwu;
          $sql_new="update acc_service_comm_dtl set new_amount='$new_amount',new_calc='$fuwu' where hdr_id='$index'";
          $model = Yii::app()->db->createCommand($sql_new)->execute();
        }
        $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($records)){
            $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, edit_amount,edit_money
				) values (
					'".$index."','".$fuwumoney."' ,'".$moneys."'
				)";
        }else{
            $sql1="update acc_service_comm_dtl set edit_amount='$fuwumoney' ,edit_money='$moneys' where hdr_id='$index'";
        }
        $model = Yii::app()->db->createCommand($sql1)->execute();

    }

    //东成西就需要重新计算年月
    public static function resetYearAndMonth(&$year,&$month,$city,$index){
        if(strtotime("$year-$month-01")>=strtotime("2021-06-01")){
            return;//超過2021-06-01不加入东成西就
        }
        $date=$year."/".$month.'/'."01";
        $date1='2020/07/01';
        $employee=CommissionController::getEmployee($index,$year,$month);
        $a=CommissionController::position($index);
        if($city=='CD'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1||(($city=='FS'||$city=='NJ')&&strtotime($date)<strtotime('2021/02/01'))){
            //当月
            return;
        }else{
            //上个月
            $month=$month-1;
            if($month==0){
                $month=12;
                $year=$year-1;
            }
        }
    }

    public function endSale($id,$index,$royalty,$years,$months){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $money=array();
      //  $zhuangji=0;
        foreach ($id as $ai){
            $mons=array();
            $sql="select * from swoper$suffix.swo_service where id='$ai'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if($records['all_number_edit0']==0&&$records['surplus_edit0']==0){
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                if($records['all_number']!=NULL){
                    if($records['all_number']==0){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding')." service_id:$ai" );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/end',array('year'=>$years,'month'=>$months,'index'=>$index)));
                    }
                    $new=$a/$records['all_number'];
                }
                if($records['surplus']!=NULL){
                    $m=$new*$records['surplus'];
                }else{
                    $m=0;
                }
                $records['company_name']=str_replace("'","''",$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N' and salesman='".$records['salesman']."' and sign_dt='".$records['sign_dt']."' order by status_dt desc";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$records['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);
                $salesman = $records['salesman'];
                $records['salesman']=str_replace('(','',$records['salesman']);
                $records['salesman']=str_replace(')','',$records['salesman']);
                if(empty($records['city'])){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding')." error:not find new");
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/end',array('year'=>$years,'month'=>$months,'index'=>$index)));
                }
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['salesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                ReportXS01SList::resetYearAndMonth($year,$month,$records['city'],$records1['id']);//需要计算服务单有没有东成西就
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['salesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                $point=$this->getPoint($year,$month,$index);//积分激励点
                $reward = ReportXS01Form::serviceReward('','',$year."/".$month,$salesman);//服务奖励点
                $records2['new_calc'] = $records2['new_calc']<=0?0.05:$records2['new_calc'];
                $fuwu_last=$point+$records2['new_calc']+$reward;
                if(isset($m)){
                    if(!empty($records2)){
                        $m=$m*$fuwu_last;
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            if(!empty($records['othersalesman'])){
                                $commission=$m*$spanning;
                                $money[]=round($commission,2);
                            }else{
                                $commission=$m;
                                $money[]=round($commission,2);
                            }
                            $sqlct="update swoper$suffix.swo_service set royalty='".$fuwu_last."',commission='-".$commission."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }
                    }else{
                        $m=$m*$royalty[$ai];
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            if(!empty($records['othersalesman'])){
                                $commission=$m*$spanning;
                                $money[]=round($commission,2);
                            }else{
                                $commission=$m;
                                $money[]=round($commission,2);
                            }
                        }
                        $sqlct="update swoper$suffix.swo_service set royalty='".$royalty[$ai]."',commission='-".$commission."'  where id='$ai'";
                        $model = Yii::app()->db->createCommand($sqlct)->execute();
                    }
                }
            }else{
                $records['company_name']=str_replace("'","''",$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='A' and salesman='".$records['salesman']."' order by status_dt desc";//更改
                $record = Yii::app()->db->createCommand($sql)->queryAll();
                $royaltys=array();
                for ($i=0;$i<count($record);$i++){
                    $sqlct="select royalty from swoper$suffix.swo_service  where id='".$record[$i]['id']."'";
                    $model = Yii::app()->db->createCommand($sqlct)->queryRow();
                    if(empty($model)){
                        $model['royalty']=0;
                    }
                    $royaltys[$i]=$model['royalty'];
                    $date=$record[$i]['first_dt'];
                    $timestrap=strtotime($date);
                    $year=date('Y',$timestrap);
                    $month=date('m',$timestrap);
                    if($record[$i]['b4_paid_type']=='1'||$record[$i]['b4_paid_type']=='Y'){
                        $a=$record[$i]['b4_amt_paid'];
                    }else{
                        $a=$record[$i]['b4_amt_paid']*$record[$i]['ctrt_period'];
                    }
                    if($records['all_number']!=NULL){
                        if($records['all_number']==0){
                            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding')." error:all_number is 0");
                            Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/end',array('year'=>$years,'month'=>$months,'index'=>$index)));
                        }
                        $new=$a/$records['all_number'];
                    }
                    if($records['surplus']!=NULL){
                        $m=$new*$records['surplus']; //新单价*新次

                    }else{
                        $m=0;
                    }
                    if($i!=0){
                        $m=0;
                    }

                    if(!isset($m)){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding')." error:m" );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/end',array('year'=>$years,'month'=>$months,'index'=>$index)));
                    }
                    $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                    if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                        if(!empty($records['othersalesman'])){
                            $mon=$m*$spanning;
                        }else{
                            $mon=$m;
                        }
                    }
                    if($record[$i]['paid_type']=='1'||$record[$i]['paid_type']=='Y'){
                        $b=$record[$i]['amt_paid'];
                    }else{
                        $b=$record[$i]['amt_paid']*$record[$i]['ctrt_period'];
                    }
                        $b=$b-$a;
                        $all_number='all_number_edit'.$i;
                        $surplus='surplus_edit'.$i;
                    if($b>0){
                        if($records[$all_number]!=NULL){
                            if($records['all_number']==0){
                                Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding')." error:all_number is 0" );
                                Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/end',array('year'=>$years,'month'=>$months,'index'=>$index)));
                            }
                            $news=$b/$records[$all_number];
                        }
                        if($records[$surplus]!=NULL){
                            $g=$news*$records[$surplus]; //更改新增单价*更改新增次
                        }else{
                            $g=0;
                        }
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            if(!empty($records['othersalesman'])){
                                $moneys=$g*$spanning;
                            }else{
                                $moneys=$g;
                            }
                        }
                        $mons[]=$mon+$moneys;
                    }
                }

                sort($royaltys);
                if($royaltys[0]==0){
                    $royaltyes=$royalty[$ai];
                }else{
                    $royaltyes=$royaltys[0];
                }
                $mons_sun=array_sum($mons);
                $money[]=round($mons_sun*$royaltyes,2);
                $commission=$mons_sun*$royaltyes;
                $sqlct="update swoper$suffix.swo_service set royalty='".$royaltyes."',commission='-".$commission."' where id='$ai'";
                $model = Yii::app()->db->createCommand($sqlct)->execute();
            }
        }

        $money=array_sum($money);
        $money=-$money;
        if(empty($money)){
            $money=0;
        }
        $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($records)){
            $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, end_amount
				) values (
					'".$index."','".$money."'
				)";
        }else{
            $sql1="update acc_service_comm_dtl set end_amount='$money'  where hdr_id='$index'";
        }
        $model = Yii::app()->db->createCommand($sql1)->execute();
//        print_r('<pre>');
//        print_r($sql1);  exit();

    }

    public function performanceSale($id,$year,$month,$index){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $money=0;
        $reward =0;
        $money1=0;
   //     $zhuangji=0;
        $moneys=0;
        $start_dt=$year."-".$month."-01";
        foreach ($id as $ai){
            if(strstr($ai,'+')){
                $ai=rtrim($ai,'+');
                $sql="select * from acc_service_comm_copy where id='$ai'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                $reward = ReportXS01Form::serviceReward('','',$year."/".$month,$records['othersalesman']);//服务奖励点
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                $sql3="select performance from acc_service_comm_hdr where  id='$index'";
                $color = Yii::app()->db->createCommand($sql3)->queryRow();
                if($color['performance']==1) {
                    $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                    $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                    $sql1 = "select * from acc_service_comm_hdr where year_no='" . $year . "' and month_no='" . $month . "' and city='" . $records['city'] . "' and  concat_ws(' ',employee_name,employee_code)= '" . $records['othersalesman'] . "'";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    $sql2 = "select new_calc from  acc_service_comm_dtl where hdr_id='" . $records1['id'] . "'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $point=$this->getPoint($year,$month,$index);//积分激励点
                    $records2['new_calc'] = $records2['new_calc']<=0?0.05:$records2['new_calc'];
                    $fuwu_last=$point+$records2['new_calc']+$reward;
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    if (!empty($a)) {
                        $moneys += $a * $otherspanning;
                        if ($records['cust_type'] == '1' || $records['cust_type'] == '2' || $records['cust_type'] == '3' || $records['cust_type'] == '5' || $records['cust_type'] == '6' || $records['cust_type'] == '7') {
                            $commission=$a * $otherspanning;
                            $comm= $commission* $fuwu_last;
                            $money += $comm;
                            $sqlct="update acc_service_comm_copy set  other_commission='".$commission."',royaltys='$fuwu_last'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }
                    }
                   // $zhuangji += $records['amt_install'];
                }else{
                    $target="update  acc_service_comm_copy set target='1' where id='$ai'";
                    $model = Yii::app()->db->createCommand($target)->execute();
                }
            }else{
                $sql="select * from swoper$suffix.swo_service where id='$ai'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                $sql3="select performance from acc_service_comm_hdr where  id='$index'";
                $color = Yii::app()->db->createCommand($sql3)->queryRow();
                if($color['performance']==1) {
                    $reward = ReportXS01Form::serviceReward('','',$year."/".$month,$records['othersalesman']);//服务奖励点
                    $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                    $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                    $sql1 = "select * from acc_service_comm_hdr where year_no='" . $year . "' and month_no='" . $month . "' and city='" . $records['city'] . "' and  concat_ws(' ',employee_name,employee_code)= '" . $records['othersalesman'] . "' ";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    $sql2 = "select new_calc from  acc_service_comm_dtl where hdr_id='" . $records1['id'] . "'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $point=$this->getPoint($year,$month,$index);//积分激励点
                    //最低提成点为5%
                    if ($records2['new_calc']<=0){
                        $records2['new_calc'] = 0.05;
                    }
                    $fuwu_last=$point+$records2['new_calc']+$reward;
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    if (!empty($a)) {
                        $moneys += $a * $otherspanning;
                        if ($records['cust_type'] == '1' || $records['cust_type'] == '2' || $records['cust_type'] == '3' || $records['cust_type'] == '5' || $records['cust_type'] == '6' || $records['cust_type'] == '7') {
                            $commission=$a * $otherspanning;
                            $comm= $commission* $fuwu_last;
                            $money += $comm;
                            $sqlct="update swoper$suffix.swo_service set  other_commission='".$commission."',royaltys='$fuwu_last'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }
                    }
                  //  $zhuangji += $records['amt_install'];
                }else{
                    $target="update  swoper$suffix.swo_service set target='1' where id='$ai'";
                    $model = Yii::app()->db->createCommand($target)->execute();
                }
            }
        }

        if(empty($money)){
            $money=0;
        }
        if(empty($moneys)){
            $moneys=0;
        }
        $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($records)){
            $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, performance_amount,out_money
				) values (
					'".$index."','".$money."','".$moneys."'
				)";
        }else{
            $sql1="update acc_service_comm_dtl set performance_amount='$money' ,out_money='$moneys'  where hdr_id='$index'";
        }
        $model = Yii::app()->db->createCommand($sql1)->execute();


//                print_r('<pre>');
//                print_r($zhuangji);
    }

    public function performanceeditSale($id,$year,$month,$index,$royalty){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $reward=0;
        $money=0;
        $money1=0;
        $moneys=0;
     //   $zhuangji=0;
        foreach ($id as $ai){
            $sql="select * from swoper$suffix.swo_service where id='$ai'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                $a=$records['amt_paid'];
            }else{
                $a=$records['amt_paid']*$records['ctrt_period'];
            }
            if($records['b4_paid_type']=='1'||$records['b4_paid_type']=='Y'){
                $b=$records['b4_amt_paid'];
            }else{
                $b=$records['b4_amt_paid']*$records['ctrt_period'];
            }
        //    $zhuangji+=$records['amt_install'];
            $c=$a-$b;
            if($c>0){
                $sql3="select performance from acc_service_comm_hdr where  id='$index'";
                $color = Yii::app()->db->createCommand($sql3)->queryRow();
                if($color['performance']==1){
                    $reward = ReportXS01Form::serviceReward('','',$year."/".$month,$records['othersalesman']);//服务奖励点
                    $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                    $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                    $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    $point=$this->getPoint($year,$month,$index);//积分激励点
                    $records2['new_calc'] = $records2['new_calc']<=0?0.05:$records2['new_calc'];
                    $fuwu_last=$point+$records2['new_calc']+$reward;
                    $fuwumoney=$c*$fuwu_last;
                    if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                        $money+=$fuwumoney*$otherspanning;
                        $commission=$c*$otherspanning;
                        $moneys+=$c*$otherspanning;
                        $sqlct="update swoper$suffix.swo_service set  other_commission='".$commission."',royaltys='$fuwu_last'  where id='$ai'";
                        $model = Yii::app()->db->createCommand($sqlct)->execute();
                    }
                }else{
                    $target="update  swoper$suffix.swo_service set target='1' where id='$ai'";
                    $model = Yii::app()->db->createCommand($target)->execute();
                }
            }else{
                if($records['all_number']!=NULL){
                    if($records['all_number']==0){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceedit',array('year'=>$year,'month'=>$month,'index'=>$index)));
                    }
                    $new=$a/$records['all_number'];
                    $old=$b/$records['all_number'];
                }
                if($records['surplus']!=NULL){
                    $m=($new-$old)*$records['surplus'];
                }else{
                    $m=0;
                }
//                $records['company_name']=str_replace(' ','',$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N' and salesman='".$records['salesman']."' order by status_dt desc";
                $recordss = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$recordss['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);

                $othersalesman = $records['othersalesman'];
                $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                ReportXS01SList::resetYearAndMonth($year,$month,$recordss['city'],$records1['id']);//需要计算服务单有没有东成西就
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                if($records1['performance']==1){
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    if(isset($m)){
                        if($records2['new_calc']!=0&&empty(!$records2['new_calc'])){
                            $reward = ReportXS01Form::serviceReward('','',$year."/".$month,$othersalesman);//服务奖励点
                            $point=$this->getPoint($year,$month,$index);//积分激励点
                            $fuwu_last=$point+$records2['new_calc']+$reward;
                            $m=$m*$fuwu_last;
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money1+=$m*$otherspanning;
                                $commission=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$fuwu_last."', other_commission='".$commission."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }else{
                            $m=$m*$royalty[$ai];
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money1+=$m*$otherspanning;
                                $commission=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$royalty[$ai]."', other_commission='".$commission."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }

                    }
                }
            }
        }

        if(empty($money1)){
            $money1=0;
        }
        if(empty($money)){
            $money=0;
        }
        if(empty($moneys)){
            $moneys=0;
        }
//        if(empty($zhuangji)){
//            $zhuangji=0;
//        }
        $fuwumoney=$money+$money1;
        $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($records)){
            $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, performanceedit_amount,performanceedit_money
				) values (
					'".$index."','".$fuwumoney."' ,'".$moneys."'
				)";
        }else{
            $sql1="update acc_service_comm_dtl set performanceedit_amount='$fuwumoney' ,performanceedit_money='$moneys' where hdr_id='$index'";
        }
        $model = Yii::app()->db->createCommand($sql1)->execute();
    }

    public function performanceendSale($id,$index,$royalty,$years,$months){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $money=array();
     //   $zhuangji=0;
        foreach ($id as $ai){
            $mons=array();
            $sql="select * from swoper$suffix.swo_service where id='$ai'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if($records['all_number_edit0']==0&&$records['surplus_edit0']==0){
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                if($records['all_number']!=NULL){
                    if($records['all_number']==0){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceend',array('year'=>$years,'month'=>$months,'index'=>$index)));
                    }
                    $new=$a/$records['all_number'];
                }
                if($records['surplus']!=NULL){
                    $m=$new*$records['surplus'];
                }else{
                    $m=0;
                }
//                $records['company_name']=str_replace(' ','',$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N' and salesman='".$records['salesman']."' and sign_dt='".$records['sign_dt']."' order by status_dt desc";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if(empty($records)){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceedit',array('year'=>$years,'month'=>$months,'index'=>$index)));
                }
                $date=$records['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);
                $othersalesman=$records['othersalesman'];
                $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                ReportXS01SList::resetYearAndMonth($year,$month,$records['city'],$records1['id']);//需要计算服务单有没有东成西就
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                $reward = ReportXS01Form::serviceReward('','',$year."/".$month,$othersalesman);//服务奖励点
                if($records1['performance']==1){
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";//当初提成比例
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    if(isset($m)){
                        if($records2['new_calc']!=0&&!empty($records2['new_calc'])){
                            $point=$this->getPoint($year,$month,$index);//积分激励点
                            $fuwu_last=$point+$records2['new_calc']+$reward;
                            $m=$m*$fuwu_last;
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money[]=round($m*$otherspanning,2);
                                $commission=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$fuwu_last."', other_commission='-".$commission."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }else{
                            $m=$m*$royalty[$ai];
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money[]=round($m*$otherspanning,2);
                                $commission=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$royalty[$ai]."', other_commission='-".$commission."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }
                    }
                }
            }else{
//                $records['company_name']=str_replace(' ','',$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='A' and salesman='".$records['salesman']."' order by status_dt desc ";//更改
                $record = Yii::app()->db->createCommand($sql)->queryAll();
                for ($i=0;$i<count($record);$i++){
                    $sqlct="select royaltys from swoper$suffix.swo_service  where id='".$record[$i]['id']."'";
                    $model_royaltys = Yii::app()->db->createCommand($sqlct)->queryRow(); //更改时输入的提成比例
                    $date=$record[$i]['status_dt'];
                    $timestrap=strtotime($date);
                    $year=date('Y',$timestrap);
                    $month=date('m',$timestrap);
                    $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                    $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                    $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    $records_edit = Yii::app()->db->createCommand($sql1)->queryRow();
                        if($record[$i]['b4_paid_type']=='1'||$record[$i]['b4_paid_type']=='Y'){
                            $a=$record[$i]['b4_amt_paid'];
                        }else{
                            $a=$record[$i]['b4_amt_paid']*$record[$i]['ctrt_period'];
                        }

                    if($records['all_number']!=NULL){
                        if($records['all_number']==0){
                            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                            Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceend',array('year'=>$years,'month'=>$months,'index'=>$index)));
                        }
                            $new=$a/$records['all_number'];
                        }
                    if($records['surplus']!=NULL){
                            $m=$new*$records['surplus']; //新单价*新次
                        }else{
                        $m=0;
                    }
//                        $records[$i]['company_name']=str_replace(' ','',$records[$i]['company_name']);
                        $sqls="select * from  swoper$suffix.swo_service where company_name='".$record[$i]['company_name']."' and cust_type='".$record[$i]['cust_type']."' and status='N' and salesman='".$record[$i]['salesman']."' order by status_dt desc";
                        $arr = Yii::app()->db->createCommand($sqls)->queryRow();
                        $date=$arr['first_dt'];
                        $timestrap=strtotime($date);
                        $year=date('Y',$timestrap);
                        $month=date('m',$timestrap);
                        $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                        $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                        $sqlss="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                        $records1 = Yii::app()->db->createCommand($sqlss)->queryRow();
                        if($records1['performance']==1){
                            $royaltys[]=$arr['royaltys'];
                        }
                        if($i!=0||$records1['performance']!=1){
                            $m=0;
                        }
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            if(!empty($records['othersalesman'])){
                                $mon=$m*$otherspanning;
                            }else{
                                $mon=$m;
                            }
                        }
                        if($record[$i]['paid_type']=='1'||$record[$i]['paid_type']=='Y'){
                            $b=$record[$i]['amt_paid'];
                        }else{
                            $b=$record[$i]['amt_paid']*$record[$i]['ctrt_period'];
                        }
                        $b=$b-$a;
                        $all_number='all_number_edit'.$i;
                        $surplus='surplus_edit'.$i;

                    if($records_edit['performance']==1){
                        if(empty($model_royaltys)){
                            $model_royaltys['royaltys']=0;
                        }
                        $royaltys[]=$model_royaltys['royaltys'];
                        if($b>0){
                            if($records[$all_number]!=NULL){
                                if($records['all_number']==0){
                                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceend',array('year'=>$years,'month'=>$months,'index'=>$index)));
                                }
                                $news=$b/$records[$all_number];
                            }
                            if($records[$surplus]!=NULL){
                                $g=$news*$records[$surplus]; //更改新增单价*更改新增次
                            }else{
                                $g=0;
                            }
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                if(!empty($records['othersalesman'])){
                                    $moneys=$g*$otherspanning;
                                }else{
                                    $moneys=$g;
                                }
                            }
                        }
                        $mons[]=$mon+$moneys;
                    }
                }
                if(empty($mons)){
                    $mons_sum=0;
                }else{
                    $mons_sum=array_sum($mons);
                }
                if(!empty($royaltys)){
                    sort($royaltys);
                    if($royaltys[0]==0){
                        $royaltyes=$royalty[$ai];
                    }else{
                        $royaltyes=$royaltys[0];
                    }
                }else{
                    $royaltyes=0;
                }
//                print_r('<pre>');
//                print_r($arr['royalty']);
//                exit();
                $money[]=round($mons_sum*$royaltyes,2);
                $commission=$mons_sum*$royaltyes;
                $sqlct="update swoper$suffix.swo_service set royaltys='".$royaltyes."', other_commission='-".$commission."'  where id='$ai'";
                $model = Yii::app()->db->createCommand($sqlct)->execute();
            }
        }
        $money=array_sum($money);
        $money=-$money;
        if(empty($money)){
            $money=0;
        }
        $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($records)){
            $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, performanceend_amount
				) values (
					'".$index."','".$money."'
				)";
        }else{
            $sql1="update acc_service_comm_dtl set performanceend_amount='$money'  where hdr_id='$index'";
        }
        $model = Yii::app()->db->createCommand($sql1)->execute();
//        print_r('<pre>');
//        print_r($sql1);  exit();

    }

    public  function getAmount($city, $cust_type, $start_dt, $sales_amt) {
        //城市，类别，时间，总金额
        $rtn = 0;
        if (!empty($city) && !empty($cust_type) && !empty($start_dt) && !empty($sales_amt)) {
            $suffix = Yii::app()->params['envSuffix'];
            $suffix = '_w';
            //客户类别
          //  $sql = "select rpt_cat from swoper$suffix.swo_customer_type where id=$cust_type";
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

    public function getPoint($year,$month,$id){
        $city = Yii::app()->user->city();
        $sql="select employee_name from acc_service_comm_hdr where id=$id";
        $name = Yii::app()->db->createCommand($sql)->queryScalar();
        $suffix = Yii::app()->params['envSuffix'];
        $sql1="select a.*,d.entry_time, b.new_calc ,e.user_id from acc_service_comm_hdr a
              left outer join acc_service_comm_dtl b on  b.hdr_id=a.id
              left outer join hr$suffix.hr_employee d on  a.employee_code=d.code 
               left outer join hr$suffix.hr_binding e on  d.id=e.employee_id          
              where  a.year_no='$year' and  a.month_no='$month' and a.employee_name='$name' and d.city='$city'
";
        $arr = Yii::app()->db->createCommand($sql1)->queryRow();
        $sql_point="select * from sales$suffix.sal_integral where year='$year' and month='$month' and username='".$arr['user_id']."' and city='$city'";
        $point = Yii::app()->db->createCommand($sql_point)->queryRow();

        if(strtotime($arr["entry_time"]."+ 1 month")>=strtotime("$year-$month-01")){//判斷是否新入職(大概查詢)
            $sql_c="select visit_dt from sales$suffix.sal_visit   where username='".$arr['user_id']."'  order by visit_dt ";
            $record = Yii::app()->db->createCommand($sql_c)->queryRow();
            $timestrap=strtotime($record['visit_dt']);
            $year_rz=intval(date('Y',$timestrap));
            $month_rz=intval(date('m',$timestrap));
            $day_rz=intval(date('d',$timestrap));//第一条记录不是一号则当月不计算激励点
            if($year_rz==intval($year)&&$month_rz==intval($month)&&$day_rz!=1){
                $point['point']=0;
            }
        }
        return $point['point'];
    }

    public function getRoyalty($index,$city,$year,$month,$ohersaleman){
        //按什么比例计算跨区提成
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select a.group_type from hr$suffix.hr_employee a
                    left outer join  acc_service_comm_hdr b on b.employee_code=a.code
                    where b.id='$index'
                ";
        $records = Yii::app()->db->createCommand($sql)->queryScalar();
        $span="select * from sales$suffix.sal_performance where city='$city' and year='$year' and month='$month'";
        $spanning = Yii::app()->db->createCommand($span)->queryRow();
        if($records==0){
            if(empty($spanning['spanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['spanning'];
            }
        }
        if($records==1){
            if(empty($spanning['business_spanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['business_spanning'];
            }
        }
        if($records==2){
            if(empty($spanning['restaurant_spanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['restaurant_spanning'];
            }
        }
        if(!empty($ohersaleman)){
            $ohersaleman=str_replace('(','',$ohersaleman);
            $ohersaleman=str_replace(')','',$ohersaleman);
            $sql1="select group_type from hr$suffix.hr_employee where  concat_ws(' ',name,code)= '".$ohersaleman."' ";
            $record = Yii::app()->db->createCommand($sql1)->queryScalar();
            if($record!=$records){
                $proportion=0.5;
            }
        }
        return $proportion;
    }

    public function getOtherRoyalty($index,$city,$year,$month,$ohersaleman){
        //按什么比例计算被跨区提成
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select a.group_type from hr$suffix.hr_employee a
                    left outer join  acc_service_comm_hdr b on b.employee_code=a.code
                    where b.id='$index'
                ";
        $records = Yii::app()->db->createCommand($sql)->queryScalar();
        $span="select * from sales$suffix.sal_performance where city='$city' and year='$year' and month='$month'";
        $spanning = Yii::app()->db->createCommand($span)->queryRow();
        if($records==0){
            if(empty($spanning['otherspanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['otherspanning'];
            }
        }
        if($records==1){
            if(empty($spanning['business_otherspanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['business_otherspanning'];
            }
        }
        if($records==2){
            if(empty($spanning['restaurant_otherspanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['restaurant_otherspanning'];
            }
        }
        if(!empty($ohersaleman)){
            $ohersaleman=str_replace('(','',$ohersaleman);
            $ohersaleman=str_replace(')','',$ohersaleman);
            $sql1="select group_type from hr$suffix.hr_employee where  concat_ws(' ',name,code)= '".$ohersaleman."' ";
            $record = Yii::app()->db->createCommand($sql1)->queryScalar();
            if($record!=$records){
                $proportion=0.5;
            }
        }
        return $proportion;
    }

    public  function getEmployee($employee,$year,$month){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select e.user_id from  hr$suffix.hr_employee d                  
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id
              where concat_ws(' ',d.name,d.code)='$employee'
";
        $records = Yii::app()->db->createCommand($sql)->queryScalar();
        $sql1="select visit_dt from sales$suffix.sal_visit   where username='$records' order by visit_dt
";
        $record = Yii::app()->db->createCommand($sql1)->queryRow();
        $timestrap=strtotime($record['visit_dt']);
        $years=date('Y',$timestrap);
        $months=date('m',$timestrap);
//        print_r($sql); print_r($sql1);
        if(date('d',$timestrap)=='01'){
            if($years==$year&&$months==$month){
                $a=1;//不加入东成西就
            }else{
                $a=2;
            }
         //   print_r('--'); print_r($a);print_r($month);exit();
        }else{
            $next=$months+1;
            if($next==13){
                $next=1;
                $years=$years+1;
            }
            if(($years==$year&&$months==$month)||($years==$year&&$next==$month)){
                $a=1;//不加入东成西就
            }else{
                $a=2;
            }
        }
        if(strtotime("$year-$month-01")>=strtotime("2021-06-01")){
            $a = 1;//超過2021-06-01不加入东成西就
        }
        return $a;
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
            $records=1;
        }else{
            $records=2;
        }
        return $records;
    }
}
