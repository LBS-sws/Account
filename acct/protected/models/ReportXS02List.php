<?php

class ReportXS02List extends CListPageModel
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
            'log_dt'=>Yii::t('app','Log Dt'),
            'description'=>Yii::t('app','Description'),
            'qty'=>Yii::t('app','Qty'),

            'money'=>Yii::t('app','Qty Money'),
            'moneys'=>Yii::t('app','Money'),
		);
	}
	
	public function retrieveDataByPage($pageNum=1,$year,$month)
	{
//        print_r('<pre>');
//        print_r($month);
		$suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $citys = Yii::app()->user->city_allow();
        $month=$month-1;
        if($month==0){
            $month=12;
            $year=$year-1;
        }
        $user=Yii::app()->user->id;
        if(Yii::app()->user->validFunction('CN09')){
            $sql1 = "select a.*,c.name,d.new_amount,d.edit_amount,d.end_amount,d.performance_amount,d.performanceedit_amount,d.performanceend_amount,d.renewal_amount,d.renewalend_amount,e.name as cityname from acc_service_comm_hdr a
                 inner join  hr$suffix.hr_employee b  on b.code=a.employee_code   
                 inner join  hr$suffix.hr_dept c on b.position=c.id      
                 inner join security$suffix.sec_city e on a.city=e.code 		  
                 left outer join  acc_service_comm_dtl d on a.id=d.hdr_id            
			     where  a.year_no='$year'  and a.month_no='$month' and a.city in ($citys) and b.city in ($citys)  
			";
            $sql2 = "select count(a.id) from acc_service_comm_hdr a
			      inner join  hr$suffix.hr_employee b  on b.code=a.employee_code   
                 inner join  hr$suffix.hr_dept c on b.position=c.id   
                  inner join security$suffix.sec_city e on a.city=e.code 		   
                  left outer join  acc_service_comm_dtl d on a.id=d.hdr_id          
			     where  a.year_no='$year'  and a.month_no='$month' and a.city in ($citys) and b.city in ($citys)  
			";
        }else{
            $sql1 = "select a.*,c.name,d.new_amount,d.edit_amount,d.end_amount,d.performance_amount,d.performanceedit_amount,d.performanceend_amount,d.renewal_amount,d.renewalend_amount,e.name as cityname from acc_service_comm_hdr a
                 inner join  hr$suffix.hr_employee b  on b.code=a.employee_code
                 inner join  hr$suffix.hr_dept c on b.position=c.id
                 inner join security$suffix.sec_city e on a.city=e.code
                 left outer join  acc_service_comm_dtl d on a.id=d.hdr_id
                 left outer join  hr$suffix.hr_binding e on b.name=e.employee_name
			     where  a.year_no='$year'  and a.month_no='$month' and a.city='$city' and b.city='$city'  and e.user_id='$user'  and b.staff_status = 0
			";
            $sql2 = "select count(a.id) from acc_service_comm_hdr a
			      inner join  hr$suffix.hr_employee b  on b.code=a.employee_code
                  inner join  hr$suffix.hr_dept c on b.position=c.id
                  inner join security$suffix.sec_city e on a.city=e.code
                  left outer join  acc_service_comm_dtl d on a.id=d.hdr_id
                  left outer join  hr$suffix.hr_binding e on b.name=e.employee_name
			     where  a.year_no='$year'  and a.month_no='$month' and a.city='$city' and b.city='$city'  and e.user_id='$user'  and b.staff_status = 0
			";
        }

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
                $arr=$record['new_amount']+$record['edit_amount']+$record['end_amount']+$record['performance_amount']+$record['performanceedit_amount']+$record['performanceend_amount']+$record['renewal_amount']+$record['renewalend_amount']+$record['product_amount'];
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
                    $a=$record['amt_paid']*12;
                }
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
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
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end' and (surplus!=0 or surplus_edit0!=0 or surplus_edit1!=0 or surplus_edit2!=0 or surplus_edit3!=0) 
			";
        $sql2 = "select count(a.id)
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end' and (surplus!=0 or surplus_edit0!=0 or surplus_edit1!=0 or surplus_edit2!=0 or surplus_edit3!=0) 
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
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
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
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'color'=>$color['performance'],
                );
            }
        }
        $session = Yii::app()->session;
        $session['criteria_XS01'] = $this->getCriteria();
//        print_r('<pre>');
//        print_r($this);
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
                    $a=$record['amt_paid']*12;
                }
                $this->attr[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"), //服务时间
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
                    'sign_dt'=>date_format(date_create($arrs['sign_dt']),"Y/m/d"),  //签约时间
                    'first_dt'=>date_format(date_create($arrs['first_dt']),"Y/m/d"), //服务时间
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
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
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

    public function renewalDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $citys = "'BJ','SH','GZ','SZ'";
        $city=Yii::app()->user->city();
        if(strstr($citys, $city)){
            $amt_paid_money=2000;
        }else{
            $amt_paid_money=1000;
        }
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where  a.salesman ='".$name['name']."' and a.status='C' and a.status_dt>='$start' and a.status_dt<='$end' and a.nature_type=2 
				and a.city ='$city' and (((a.amt_paid>='$amt_paid_money'*12) and (a.paid_type=1 or a.paid_type='Y')) or((a.amt_paid>='$amt_paid_money') and  a.paid_type='M'))			
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
        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();
//续约金额都可以不同服务累加，但金额是按单店来判定是否满足续约要求。
        $list = array();//条件门店名字
        $ids=array();//所有id
        $sql_ou = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where a.city ='$city'  and  a.salesman ='".$name['name']."' and a.status='C' and a.status_dt>='$start' and a.status_dt<='$end'
				 and a.nature_type=2 and  (((a.amt_paid<'$amt_paid_money'*12) and (a.paid_type=1 or a.paid_type='Y')) or((a.amt_paid<'$amt_paid_money') and  a.paid_type='M'))			
			";
        $ou = Yii::app()->db->createCommand($sql_ou)->queryAll();
        //判断已经需要计算的门店
        $sql2 = "select a.company_name					
				from swoper$suffix.swo_service a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where  a.salesman ='".$name['name']."' and a.status='C' and a.status_dt>='$start' and a.status_dt<='$end' and a.nature_type=2 
				and a.city ='$city' and (((a.amt_paid>='$amt_paid_money'*12) and (a.paid_type=1 or a.paid_type='Y')) or((a.amt_paid>='$amt_paid_money') and  a.paid_type='M'))			
			";
        $company_name = Yii::app()->db->createCommand($sql2)->queryColumn();
//判断续约金额加起来有2000/1000的
        foreach ($ou as &$v){
            if($v['paid_type']=='M'){
                $v['sum']=$v['amt_paid']*$v['ctrt_period'];
            }else{
                $v['sum']=$v['amt_paid'];
            }
        }
        foreach($ou as $k=>&$v){
            if(!isset($list[$v['company_name']])){
                $list[$v['company_name']]=$v['sum'];
            }else{
                $list[$v['company_name']]+=$v['sum'];
            }
        }
        foreach ($list as $key => $value){
            if($value>=$amt_paid_money*12){
                $ids[]=$key;
            }
        }
        foreach($ou as $k=>&$v){
            if(in_array($v['company_name'],$ids)||in_array($v['company_name'],$company_name)){

            }else{
                unset($ou[$k]);
            }
        }

//
//餐饮连锁客户（餐饮类）
        $sql_eat="select a.*,  c.description as type_desc, d.name as city_name,b.group_id
        from swoper$suffix.swo_service a
        left outer join security$suffix.sec_city d on a.city=d.code 			  
		left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
		left outer join swoper$suffix.swo_company b on a.company_name=concat_ws('',b.code,b.name) 
		where a.city ='$city'  and  a.salesman ='".$name['name']."' and a.status='C' and a.status_dt>='$start' and a.status_dt<='$end' and a.nature_type=1	  
        ";
        $eat = Yii::app()->db->createCommand($sql_eat)->queryAll();
        foreach ($eat as $k=>&$v){
            $sql="select count(id) from  swoper$suffix.swo_company where group_id='".$v['group_id']."' and status=1 and group_id<>''";
            $sum = Yii::app()->db->createCommand($sql)->queryScalar();
            if($sum<10){
                unset($eat[$k]);
            }
        }
        $records=array_merge($eat,$ou,$records);
        $this->totalRow = count($records);


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

    public function renewalendDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $sql1="select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where a.city in ($city)  and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'  and  a.salesman ='".$name['name']."' and (surplus!=0 or surplus_edit0!=0 or surplus_edit1!=0 or surplus_edit2!=0 or surplus_edit3!=0) 
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
        $sql = $sql1.$clause.$order;
        $sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($records as $k=>&$record){
            $company_name=str_replace("'","''",$record['company_name']);
            $sql2="select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where a.city in ($city)   and a.status='C'  and  a.company_name='".$company_name."'  and  a.salesman ='".$name['name']."'
				and  a.cust_type='".$record['cust_type']."' and a.cust_type_name='".$record['cust_type_name']."'  and a.royalty=0.01
				order by  a.id desc
";
            $c = Yii::app()->db->createCommand($sql2)->queryRow();
            if(empty($c)){
                unset($records[$k]);
            }
        }
        $this->totalRow = count($records);
        $list = array();
        $this->attr = array();
        if (count($records) > 0) {
            foreach ($records as $k=>&$record) {
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

    public function productDataByPage($pageNum=1,$year,$month,$index){
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $sql = "select b.log_dt,b.company_name,a.money,a.qty,c.description,c.sales_products,a.id,d.name as city_name ,(a.money*a.qty) as moneys from swoper$suffix.swo_logistic_dtl a
                left outer join swoper$suffix.swo_logistic b on b.id=a.log_id		
               	left outer join swoper$suffix.swo_task c on a.task=c.	id
             	left outer join security$suffix.sec_city d on a.city=d.code 			  
                where b.log_dt<='$end' and  b.log_dt>='$start' and b.salesman='".$name['name']."' and b.city ='$city' and a.money>0";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        $sql1 = "select count(a.id) from swoper$suffix.swo_logistic_dtl a
                left outer join swoper$suffix.swo_logistic b on b.id=a.log_id		
               	left outer join swoper$suffix.swo_task c on a.task=c.	id
             	left outer join security$suffix.sec_city d on a.city=d.code 			  
                where b.log_dt<='$end' and  b.log_dt>='$start' and b.salesman='".$name['name']."' and b.city ='$city' and a.money>0";
        $this->totalRow = Yii::app()->db->createCommand($sql1)->queryScalar();
        if (count($rows) > 0) {
            foreach ($rows as $record) {

                $this->attr[] = array(
                    'id'=>$record['id'],
                    'city_name'=>$record['city_name'],               //地区
                    'log_dt'=>General::toDate($record['log_dt']),    //出单日期
                    'company_name'=>$record['company_name'],              //客户编号及名称
                    'description'=>$record['description'],                 //产品名称
                    'qty'=>$record['qty'],                          //数量
                    'money'=>$record['money'],                      //金额
                    'moneys'=>$record['moneys'],                    //总金额

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

    public function retrieveXiaZai($year,$month,$index,$view){
        $pageNum=1;
        Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $objPHPExcel = new PHPExcel;
        $objReader  = PHPExcel_IOFactory::createReader('Excel2007');
        $path = Yii::app()->basePath.'/commands/template/salecommsion.xlsx';
        $objPHPExcel = $objReader->load($path);
        $objPHPExcel->setActiveSheetIndex(0)->setTitle('提成明细报表-'.$view['city']);
        $objPHPExcel->getActiveSheet()->setCellValue('A1','提成明细报表 - '.$view['employee_name']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A2','提成月份 : '.$view['saleyear']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A4','组别 : '.$view['group_type']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A5','新增提成比例 : '.$view['new_calc']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A6','新增生意提成 : '.$view['new_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A7','更改生意提成 : '.$view['edit_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A8','终止生意提成 : '.$view['end_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A9','跨区新增提成 : '.$view['performance_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A10','跨区更改提成 : '.$view['performanceedit_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A11','跨区终止提成 : '.$view['performanceend_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A12','续约生意提成 : '.$view['renewal_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A13','续约终止提成 : '.$view['renewalend_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A14','产品提成 : '.$view['product_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('A15','总额 : '.$view['all_amount']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('B4','跨区提成是否计算 : '.$view['performance']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('B5','销售提成激励点 : '.$view['point']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('B6','新增业绩 : '.$view['new_money']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('B7','更改新增业绩 : '.$view['edit_money']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('B9','跨区业绩 : '.$view['out_money']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('B10','跨区更改新增业绩 : '.$view['performanceedit_money']) ;
        $objPHPExcel->getActiveSheet()->setCellValue('B12','续约业绩 : '.$view['renewal_money']) ;

        $objPHPExcel->getActiveSheet()->getStyle('A17:H17')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A17:H17')->getFill()->getStartColor()->setARGB('99FFFF');

        $new=$this->Newdown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A19','类别 : 新生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A19:H19');
        $objPHPExcel->getActiveSheet()->getStyle('A19')->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A19')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A19')->getFill()->getStartColor()->setARGB('99FFFF');

        $i=20;
        for($o=0;$o<count($new);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$new[$o]['first_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$new[$o]['sign_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$new[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$new[$o]['othersalesman']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$new[$o]['type_desc']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$new[$o]['service']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$new[$o]['amt_paid']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$new[$o]['amt_install']) ;
//            $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[$o]['status_copy']) ;
            $i=$i+1;
        }
        $i=$i+1;
        $Edit=$this->Editdown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'类别 : 更改生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':H'.$i);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->getStartColor()->setARGB('99FFFF');

        $i=$i+1;
        for($o=0;$o<count($Edit);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$Edit[$o]['first_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$Edit[$o]['sign_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$Edit[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$Edit[$o]['othersalesman']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$Edit[$o]['type_desc']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$Edit[$o]['service']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$Edit[$o]['amt_paid']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$Edit[$o]['amt_install']) ;
          //  $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[0]['status_copy']) ;
            $i=$i+1;
        }
        $i=$i+1;
        $End=$this->Enddown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'类别 : 终止生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':H'.$i);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->getStartColor()->setARGB('99FFFF');
        $i=$i+1;
        for($o=0;$o<count($End);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$End[$o]['first_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$End[$o]['sign_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$End[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$End[$o]['othersalesman']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$End[$o]['type_desc']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$End[$o]['service']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$End[$o]['amt_paid']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$End[$o]['amt_install']) ;
          //  $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[$o]['status_copy']) ;
            $i=$i+1;
        }
        $i=$i+1;
        $new=$this->NewPerdown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'类别 : 跨区新增生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':H'.$i);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->getStartColor()->setARGB('99FFFF');
        $i=$i+1;
        for($o=0;$o<count($new);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$new[$o]['first_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$new[$o]['sign_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$new[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$new[$o]['othersalesman']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$new[$o]['type_desc']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$new[$o]['service']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$new[$o]['amt_paid']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$new[$o]['amt_install']) ;
//            $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[$o]['status_copy']) ;
            $i=$i+1;
        }
        $i=$i+1;
        $eidt=$this->EditPerdown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'类别 : 跨区更改生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':H'.$i);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->getStartColor()->setARGB('99FFFF');
        $i=$i+1;
        for($o=0;$o<count($eidt);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$eidt[$o]['first_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$eidt[$o]['sign_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$eidt[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$eidt[$o]['othersalesman']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$eidt[$o]['type_desc']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$eidt[$o]['service']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$eidt[$o]['amt_paid']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$eidt[$o]['amt_install']) ;
         //   $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[0]['status_copy']) ;
            $i=$i+1;
        }
        $i=$i+1;
        $end=$this->EndPerdown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'类别 : 跨区终止生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':H'.$i);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->getStartColor()->setARGB('99FFFF');
        $i=$i+1;
        for($o=0;$o<count($end);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$end[$o]['first_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$end[$o]['sign_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$end[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$end[$o]['othersalesman']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$end[$o]['type_desc']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$end[$o]['service']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$end[$o]['amt_paid']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$end[$o]['amt_install']) ;
         //   $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[$o]['status_copy']) ;
            $i=$i+1;
        }
        $i=$i+1;
        $end=$this->renewaldown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'类别 : 续约生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':H'.$i);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->getStartColor()->setARGB('99FFFF');
        $i=$i+1;
        for($o=0;$o<count($end);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$end[$o]['first_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$end[$o]['sign_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$end[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$end[$o]['othersalesman']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$end[$o]['type_desc']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$end[$o]['service']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$end[$o]['amt_paid']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$end[$o]['amt_install']) ;
            //   $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[$o]['status_copy']) ;
            $i=$i+1;
        }
        $i=$i+1;
        $end=$this->renewalenddown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'类别 : 续约终止生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':H'.$i);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->getStartColor()->setARGB('99FFFF');
        $i=$i+1;
        for($o=0;$o<count($end);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$end[$o]['first_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$end[$o]['sign_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$end[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$end[$o]['othersalesman']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$end[$o]['type_desc']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$end[$o]['service']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$end[$o]['amt_paid']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$end[$o]['amt_install']) ;
            //   $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[$o]['status_copy']) ;
            $i=$i+1;
        }
        $i=$i+1;
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'出单日期') ;
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,'客户名称') ;
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,'产品名称') ;
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,'数量') ;
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,'单价') ;
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,'总金额') ;
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':H'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i.':H'.$i)->getFill()->getStartColor()->setARGB('99FFFF');
        $i=$i+1;
        $end=$this->productdown($year,$month,$index);
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,'类别 : 产品生意额') ;
        $objPHPExcel->getActiveSheet()->mergeCells('A'.$i.':H'.$i);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFont()->setBold(true);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A'.$i)->getFill()->getStartColor()->setARGB('99FFFF');
        $i=$i+1;
        for($o=0;$o<count($end);$o++){
            $objPHPExcel->getActiveSheet()->setCellValue('A'.$i,$end[$o]['log_dt']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$end[$o]['company_name']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$end[$o]['description']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$end[$o]['qty']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$end[$o]['money']) ;
            $objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$end[$o]['moneys']) ;;
            //   $objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$this[$o]['status_copy']) ;
            $i=$i+1;
        }

        $time=time();
        $str="salecommsion_".$time.".xlsx";
        header("Content-Type:application/vnd.ms-excel");
        header('Content-Disposition:attachment;filename="'.$str.'"');
         header("Pragma: no-cache");
        header("Expires: 0");

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        ob_end_clean();
        $objWriter->save('php://output');

        spl_autoload_register(array('YiiBase','autoload'));

    }

    public function Newdown($year,$month,$index){
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $new=array();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='N'  and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
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
                $new[] = array(
                    'id'=>$arrs['id'].'+',
                    'company_name'=>$arrs['company_name'],        //客户名称
                    'city_name'=>$arrs['city_name'],               //城市
                    'type_desc'=>$arrs['type_desc'],               //类别
                    'service'=>$arrs['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($arrs['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($arrs['first_dt']),"Y/m/d"),  //服务时间
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
                $new[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'status_copy'=>$record['status_copy'],           //是否计算
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                );
            }
        }
        return $new;
    }

    public function Editdown($year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $new=array();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
			    inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='A' and a.status_dt>='$start' and a.status_dt<='$end'	  
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*$record['ctrt_period'];
                }
                $new[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royalty'],           //提成比例
                );
            }
        }
        return $new;
    }

    public function Enddown($year,$month,$index){
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $new=array();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end' and (surplus!=0 or surplus_edit0!=0 or surplus_edit1!=0 or surplus_edit2!=0 or surplus_edit3!=0) 
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*$record['ctrt_period'];
                }
                $new[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royalty'],           //提成比例
                );
            }
        }
        return $new;
    }

    public function NewPerdown($year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $newper=array();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='N' and a.status_dt>='$start' and a.status_dt<='$end'
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        $sqls = "select a.*,  c.description as type_desc, d.name as city_name					
				from acc_service_comm_copy a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 			 
			  where a.othersalesman='".$name['name']."'   and a.first_dt>='$start' and a.first_dt<='$end'
			";
        $arr = Yii::app()->db->createCommand($sqls)->queryAll();
        if (count($arr) > 0) {
            foreach ($arr as $k=>$arrs) {
                if($arrs['paid_type']=='1'||$arrs['paid_type']=='Y'){
                    $a=$arrs['amt_paid'];
                }else{
                    $a=$arrs['amt_paid']*$arrs['ctrt_period'];
                }
                $newper[] = array(
                    'id'=>$arrs['id'].'+',
                    'company_name'=>$arrs['company_name'],        //客户名称
                    'city_name'=>$arrs['city_name'],               //城市
                    'type_desc'=>$arrs['type_desc'],               //类别
                    'service'=>$arrs['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($arrs['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($arrs['first_dt']),"Y/m/d"),  //服务时间
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
                $newper[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'status_copy'=>$record['status_copy'],           //是否计算
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员

                );
            }
        }
        return $newper;
    }

    public function EditPerdown($year,$month,$index){
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $editper=array();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
			    inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='A' and a.status_dt>='$start' and a.status_dt<='$end'	  
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*$record['ctrt_period'];
                }
                $editper[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royaltys'],           //提成比例
                );
            }
        }
        return $editper;
    }

    public function EndPerdown($year,$month,$index){
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $endper=array();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				inner join  acc_service_comm_hdr b on b.id=$index
				where a.city in ($city)  and  a.othersalesman ='".$name['name']."' and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end' and (surplus!=0 or surplus_edit0!=0 or surplus_edit1!=0 or surplus_edit2!=0 or surplus_edit3!=0) 
			";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*$record['ctrt_period'];
                }
                $endper[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royaltys'],           //提成比例
                );
            }
        }
        return $endper;
    }

    public function renewaldown($year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $citys = "'BJ','SH','GZ','SZ'";
        $city = Yii::app()->user->city_allow();
        if(strstr($citys, $city)){
            $amt_paid_money=2000;
        }else{
            $amt_paid_money=1000;
        }
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $sql1 = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where  a.salesman ='".$name['name']."' and a.status='C' and a.status_dt>='$start' and a.status_dt<='$end' and a.nature_type=2 
				and a.city in ($city) and (((a.amt_paid>='$amt_paid_money'*12) and (a.paid_type=1 or a.paid_type='Y')) or((a.amt_paid>='$amt_paid_money') and  a.paid_type='M'))
			  
			";

        $records = Yii::app()->db->createCommand($sql1)->queryAll();
//续约金额都可以不同服务累加，但金额是按单店来判定是否满足续约要求。
        $list = array();//条件门店名字
        $ids=array();//所有id
        $sql_ou = "select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='C' and a.status_dt>='$start' and a.status_dt<='$end'
				 and a.nature_type=2 and  (((a.amt_paid<'$amt_paid_money'*12) and (a.paid_type=1 or a.paid_type='Y')) or((a.amt_paid<'$amt_paid_money') and  a.paid_type='M'))			
			";
        $ou = Yii::app()->db->createCommand($sql_ou)->queryAll();
//判断续约金额加起来有2000/1000的
        foreach ($ou as &$v){
            if($v['paid_type']=='M'){
                $v['sum']=$v['amt_paid']*$v['ctrt_period'];
            }else{
                $v['sum']=$v['amt_paid'];
            }
        }
        foreach($ou as $k=>&$v){
            if(!isset($list[$v['company_name']])){
                $list[$v['company_name']]=$v['sum'];
            }else{
                $list[$v['company_name']]+=$v['sum'];
            }
        }
        foreach ($list as $key => $value){
            if($value>=$amt_paid_money*12){
                $ids[]=$key;
            }
        }
        foreach($ou as $k=>&$v){
            if(in_array($v['company_name'],$ids)){

            }else{
                unset($ou[$k]);
            }
        }
//
//餐饮连锁客户（餐饮类）
        $sql_eat="select a.*,  c.description as type_desc, d.name as city_name,b.group_id
        from swoper$suffix.swo_service a
        left outer join security$suffix.sec_city d on a.city=d.code 			  
		left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
		left outer join swoper$suffix.swo_company b on a.company_name=concat_ws(' ',b.code,b.name) 
		where a.city in ($city)  and  a.salesman ='".$name['name']."' and a.status='C' and a.status_dt>='$start' and a.status_dt<='$end' and a.nature_type=1	  
        ";
        $eat = Yii::app()->db->createCommand($sql_eat)->queryAll();
//                print_r('<pre>');
//        print_r($eat);
        foreach ($eat as $k=>&$v){
            $sql="select count(id) from  swoper$suffix.swo_company where group_id='".$v['group_id']."' and status=1 and group_id<>''";
            $sum = Yii::app()->db->createCommand($sql)->queryScalar();
            if($sum<10){
                unset($eat[$k]);
            }
        }
        $records=array_merge($eat,$ou,$records);
        $renewal = array();
        if (count($records) > 0) {
            foreach ($records as $k=>$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*$record['ctrt_period'];
                }
                $renewal[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royaltys'],           //提成比例
                );
            }
        }
        return $renewal;
    }

    public function renewalenddown($year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city_allow();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $sql1="select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a 
				inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where a.city in ($city)   and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'  and  a.salesman ='".$name['name']."' and (surplus!=0 or surplus_edit0!=0 or surplus_edit1!=0 or surplus_edit2!=0 or surplus_edit3!=0) 
";
        $records = Yii::app()->db->createCommand($sql1)->queryAll();
        foreach ($records as $k=>&$record){
            $sql2="select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where a.city in ($city)   and a.status='C'  and  a.company_name='".$record['company_name']."'  and  a.salesman ='".$name['name']."'
				and  a.cust_type='".$record['cust_type']."' and a.cust_type_name='".$record['cust_type_name']."'  and a.royalty=0.01
				order by  a.id desc
";
            $c = Yii::app()->db->createCommand($sql2)->queryRow();
            if(empty($c)){
                unset($records[$k]);
            }
        }
        $renewalend = array();
        if (count($records) > 0) {
            foreach ($records as $k=>&$record) {
                if($record['paid_type']=='1'||$record['paid_type']=='Y'){
                    $a=$record['amt_paid'];
                }else{
                    $a=$record['amt_paid']*$record['ctrt_period'];
                }
                $renewalend[] = array(
                    'id'=>$record['id'],
                    'company_name'=>$record['company_name'],        //客户名称
                    'city_name'=>$record['city_name'],               //城市
                    'type_desc'=>$record['type_desc'],               //类别
                    'service'=>$record['service'],                    //服务频率
                    'sign_dt'=>date_format(date_create($record['sign_dt']),"Y/m/d"),    //签约时间
                    'first_dt'=>date_format(date_create($record['first_dt']),"Y/m/d"),  //服务时间
                    'amt_paid'=>$a,                                     //服务年金额金额
                    'amt_install'=>$record['amt_install'],           //安装金额
                    'othersalesman'=>$record['othersalesman'],           //跨区业务员
                    'royalty'=>$record['royaltys'],           //提成比例
                );
            }
        }
        return $renewalend;
    }

    public function productdown($year,$month,$index){
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city();
        $sqlm="select concat_ws(' ',employee_name,employee_code) as name from acc_service_comm_hdr where id='$index'";
        $name = Yii::app()->db->createCommand($sqlm)->queryRow();
        $name['name']=str_replace(' ',' (',$name['name']);
        $name['name'].=")";
        $start=$year."-".$month."-01";
        $end=$year."-".$month."-31";
        $sql = "select b.log_dt,b.company_name,a.money,a.qty,c.description,c.sales_products,a.id,d.name as city_name ,(a.money*a.qty) as moneys from swoper$suffix.swo_logistic_dtl a
                left outer join swoper$suffix.swo_logistic b on b.id=a.log_id		
               	left outer join swoper$suffix.swo_task c on a.task=c.	id
             	left outer join security$suffix.sec_city d on a.city=d.code 			  
                where b.log_dt<='$end' and  b.log_dt>='$start' and b.salesman='".$name['name']."' and b.city ='$city' and a.money>0";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        $product = array();
        if (count($rows) > 0) {
            foreach ($rows as $record) {
                $product[] = array(
                    'id'=>$record['id'],
                    'city_name'=>$record['city_name'],               //地区
                    'log_dt'=>date_format(date_create($record['log_dt']),"Y/m/d"),   //出单日期
                    'company_name'=>$record['company_name'],              //客户编号及名称
                    'description'=>$record['description'],                 //产品名称
                    'qty'=>$record['qty'],                          //数量
                    'money'=>$record['money'],                      //金额
                    'moneys'=>$record['moneys'],                    //总金额

                );
            }
        }
        return $product;
    }

}
