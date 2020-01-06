<?php

class ReportXS01List extends CListPageModel
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
        $sql1 = "select a.*,c.name,d.new_amount,d.edit_amount,d.end_amount,d.performance_amount,d.performanceedit_amount,d.performanceend_amount,e.name as cityname from acc_service_comm_hdr a
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
                $arr=$record['new_amount']+$record['edit_amount']+$record['end_amount']+$record['performance_amount']+$record['performanceedit_amount']+$record['performanceend_amount'];
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
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='N' and a.status_dt>='$start' and a.status_dt<='$end'
			";
        $sql2 = "select count(a.id)
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='N' and a.status_dt>='$start' and a.status_dt<='$end'
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
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='N'  and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $sql2 = "select count(a.id)			
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='N' and a.first_dt>='$start' and a.first_dt<='$end'
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

    public function newSale($id,$year,$month,$index){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
	    $money=0;
        $money1=0;
        $zhuangji=0;
        $moneys=0;
        $start_dt=$year."-".$month."-01";
        foreach ($id as $a){
            if(strstr($a,'+')){
                $a=rtrim($a,'+');
                $sql="select * from acc_service_comm_copy where id='$a'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                    $money+=$a;
                    $cust_type='fw';
                    if(!empty($records['othersalesman'])){
                        $moneys+=$a*$spanning;
                    }else{
                        $moneys+=$a;
                    }
                }elseif ($records['cust_type']=='4'){
                    $money1+=$a;
                    $cust_type1='inv';
                }
             //   $zhuangji+=$records['amt_install'];
            }else{
                $sql="select * from swoper$suffix.swo_service where id='$a'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                    $a=$records['amt_paid'];
                }else{
                    $a=$records['amt_paid']*$records['ctrt_period'];
                }
                $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                    $money+=$a;
                    $cust_type='fw';
                    if(!empty($records['othersalesman'])){
                        $moneys+=$a*$spanning;
                    }else{
                        $moneys+=$a;
                    }
                }elseif ($records['cust_type']=='4'){
                    $money1+=$a;
                    $cust_type1='inv';
                }
           //     $zhuangji+=$records['amt_install'];
            }
        }
        if(!empty($cust_type)){
            $fuwu=$this->getAmount($city,$cust_type,$start_dt,$money);//提成比例服务
            $fuwumoney=$moneys*$fuwu;
        }else{
            $fuwumoney=0;
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
        $money1=0;
        $moneys=0;
      //  $zhuangji=0;
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
             //   $zhuangji+=$records['amt_install'];
                $c=$a-$b;
                if($c>0){
                    $sql="select new_calc from  acc_service_comm_dtl where hdr_id='$index'";
                    $record = Yii::app()->db->createCommand($sql)->queryRow();
                    $fuwumoney=$c*$record['new_calc'];
                    $spanning=$this->getRoyalty($index,$city,$years,$months,$records['othersalesman']);
                    if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                        $moneys+=$c;
                        if(!empty($records['othersalesman'])){
                            $money+=$fuwumoney*$spanning;
                        }else{
                            $money+=$fuwumoney;
                        }
                    }
                }else{
                    if($records['all_number']!=NULL){
                        $new=$a/$records['all_number'];
                        $old=$b/$records['all_number'];
                    }
                    if($records['surplus']!=NULL){
                        $m=($new-$old)*$records['surplus'];
                    }else{
                        $m=0;
                    }
                    //当初提成比例
                    $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N'";
                    $recordss = Yii::app()->db->createCommand($sql)->queryRow();
                    $date=$recordss['first_dt'];
                    $timestrap=strtotime($date);
                    $year=date('Y',$timestrap);
                    $month=date('m',$timestrap);
                    $records['salesman']=str_replace('(','',$records['salesman']);
                    $records['salesman']=str_replace(')','',$records['salesman']);
                    $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['salesman']."' ";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                   if(!empty($m)){
                       if(!empty($records2)){
                           $m=$m*$records2['new_calc'];
                           if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                               if(!empty($records['othersalesman'])){
                                   $money1+=$m*$spanning;
                               }else{
                                   $money1+=$m;
                               }
                               $sqlct="update swoper$suffix.swo_service set royalty='".$records2['new_calc']."'  where id='$ai'";
                               $model = Yii::app()->db->createCommand($sqlct)->execute();
                           }
                       }else{
                           $m=$m*$royalty[$ai];
                           if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                               if(!empty($records['othersalesman'])){
                                   $money1+=$m*$spanning;
                               }else{
                                   $money1+=$m;
                               }
                           }
                           $sqlct="update swoper$suffix.swo_service set royalty='".$royalty[$ai]."'  where id='$ai'";
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
//            if(empty($zhuangji)){
//                $zhuangji=0;
//            }
        $fuwumoney=$money+$money1;
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

    public function endSale($id,$index,$royalty){
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
                    $new=$a/$records['all_number'];
                }
                if($records['surplus']!=NULL){
                    $m=$new*$records['surplus'];
                }else{
                    $m=0;
                }

                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$records['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);
                $records['salesman']=str_replace('(','',$records['salesman']);
                $records['salesman']=str_replace(')','',$records['salesman']);
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['salesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                if(!empty($m)){
                    if(!empty($records2)){
                        $m=$m*$records2['new_calc'];
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            if(!empty($records['othersalesman'])){
                                $money[]=$m*$spanning;
                            }else{
                                $money[]=$m;
                            }
                            $sqlct="update swoper$suffix.swo_service set royalty='".$records2['new_calc']."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }
                    }else{
                        $m=$m*$royalty[$ai];
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            if(!empty($records['othersalesman'])){
                                $money[]=$m*$spanning;
                            }else{
                                $money[]=$m;
                            }
                        }
                        $sqlct="update swoper$suffix.swo_service set royalty='".$royalty[$ai]."'  where id='$ai'";
                        $model = Yii::app()->db->createCommand($sqlct)->execute();
                    }
                }
            }else{
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='A' order by status_dt ";//更改
                $record = Yii::app()->db->createCommand($sql)->queryAll();
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
                $money[]=$mons_sun*$royaltyes;
                $sqlct="update swoper$suffix.swo_service set royalty='".$royaltyes."'  where id='$ai'";
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
        $money1=0;
   //     $zhuangji=0;
        $moneys=0;
        $start_dt=$year."-".$month."-01";
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
                $sql3="select performance from acc_service_comm_hdr where  id='$index'";
                $color = Yii::app()->db->createCommand($sql3)->queryRow();
                if($color['performance']==1) {
                    $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                    $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                    $sql1 = "select * from acc_service_comm_hdr where year_no='" . $year . "' and month_no='" . $month . "' and city='" . $records['city'] . "' and  concat_ws(' ',employee_name,employee_code)= '" . $records['othersalesman'] . "'";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    $sql2 = "select new_calc from  acc_service_comm_dtl where hdr_id='" . $records1['id'] . "'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['othersalesman']);
                    if (!empty($a)) {
                        $moneys += $a * $otherspanning;
                        $a = $a * $records2['new_calc'];
                        if ($records['cust_type'] == '1' || $records['cust_type'] == '2' || $records['cust_type'] == '3' || $records['cust_type'] == '5' || $records['cust_type'] == '6' || $records['cust_type'] == '7') {
                            $money += $a * $otherspanning;
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
                    $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                    $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                    $sql1 = "select * from acc_service_comm_hdr where year_no='" . $year . "' and month_no='" . $month . "' and city='" . $records['city'] . "' and  concat_ws(' ',employee_name,employee_code)= '" . $records['othersalesman'] . "' ";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    $sql2 = "select new_calc from  acc_service_comm_dtl where hdr_id='" . $records1['id'] . "'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['othersalesman']);
                    if (!empty($a)) {
                        $moneys += $a * $otherspanning;
                        $a = $a * $records2['new_calc'];
                        if ($records['cust_type'] == '1' || $records['cust_type'] == '2' || $records['cust_type'] == '3' || $records['cust_type'] == '5' || $records['cust_type'] == '6' || $records['cust_type'] == '7') {
                            $money += $a * $otherspanning;
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
                    $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                    $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                    $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                    $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['othersalesman']);
                    $fuwumoney=$c*$records2['new_calc'];
                    if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                        $money+=$fuwumoney*$otherspanning;
                        $moneys+=$c*$otherspanning;
                    }
                }else{
                    $target="update  swoper$suffix.swo_service set target='1' where id='$ai'";
                    $model = Yii::app()->db->createCommand($target)->execute();
                }
            }else{
                if($records['all_number']!=NULL){
                    $new=$a/$records['all_number'];
                    $old=$b/$records['all_number'];
                }
                if($records['surplus']!=NULL){
                    $m=($new-$old)*$records['surplus'];
                }else{
                    $m=0;
                }
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N'";
                $recordss = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$recordss['first_dt'];
                $timestrap=strtotime($date);
                $years=date('Y',$timestrap);
                $months=date('m',$timestrap);

                $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                $sql1="select * from acc_service_comm_hdr where year_no='".$years."' and month_no='".$months."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['othersalesman']);
                if($records1['performance']==1){
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    if(!empty($m)){
                        if($records2['new_calc']!=0&&empty(!$records2['new_calc'])){
                            $m=$m*$records2['new_calc'];
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money1+=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$records2['new_calc']."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }else{
                            $m=$m*$royalty[$ai];
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money1+=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$royalty[$ai]."'  where id='$ai'";
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

    public function performanceendSale($id,$index,$royalty){
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
                    $new=$a/$records['all_number'];
                }
                if($records['surplus']!=NULL){
                    $m=$new*$records['surplus'];
                }else{
                    $m=0;
                }
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$records['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);
                $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                if($records1['performance']==1){
                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";//当初提成比例
                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['othersalesman']);
                    if(!empty($m)){
                        if($records2['new_calc']!=0&&empty(!$records2['new_calc'])){
                            $m=$m*$records2['new_calc'];
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money[]=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$records2['new_calc']."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }else{
                            $m=$m*$royalty[$ai];
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money[]=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$royalty[$ai]."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }
                    }
                }
            }else{
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='A' order by status_dt ";//更改
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
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['othersalesman']);
                    $records_edit = Yii::app()->db->createCommand($sql1)->queryRow();
                        if($record[$i]['b4_paid_type']=='1'||$record[$i]['b4_paid_type']=='Y'){
                            $a=$record[$i]['b4_amt_paid'];
                        }else{
                            $a=$record[$i]['b4_amt_paid']*$record[$i]['ctrt_period'];
                        }

                    if($records['all_number']!=NULL){
                            $new=$a/$records['all_number'];
                        }
                    if($records['surplus']!=NULL){
                            $m=$new*$records['surplus']; //新单价*新次
                        }else{
                        $m=0;
                    }
                        $sqls="select * from  swoper$suffix.swo_service where company_name='".$record[$i]['company_name']."' and cust_type='".$record[$i]['cust_type']."' and status='N'";
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
                    }
                }
                $mons[]=$mon+$moneys;
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
                $money[]=$mons_sum*$royaltyes;
                $sqlct="update swoper$suffix.swo_service set royaltys='".$royaltyes."'  where id='$ai'";
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
        }elseif($records==1){
            if(empty($spanning['business_spanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['business_spanning'];
            }
        }elseif($records==2){
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
            $record = Yii::app()->db->createCommand($sql)->queryScalar();
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
        }elseif($records==1){
            if(empty($spanning['business_otherspanning'])){
                $proportion=0.5;
            }else{
                $proportion=$spanning['business_otherspanning'];
            }
        }elseif($records==2){
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
            $record = Yii::app()->db->createCommand($sql)->queryScalar();
            if($record!=$records){
                $proportion=0.5;
            }
        }
        return $proportion;
    }
}
