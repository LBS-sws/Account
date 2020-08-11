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
		where a.city ='$city'  and  a.salesman ='".$name['name']."' and a.status='C' and a.status_dt>='$start' and a.status_dt<='$end' and a.nature_type=1	  
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

    public function renewalendDataByPage($pageNum=1,$year,$month,$index)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $city=Yii::app()->user->city();
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
				where a.city ='$city'   and a.status='T' and a.status_dt>='$start' and a.status_dt<='$end'  and  a.salesman ='".$name['name']."'
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
            $sql2="select a.*,  c.description as type_desc, d.name as city_name					
				from swoper$suffix.swo_service a inner join security$suffix.sec_city d on a.city=d.code 			  
				left outer join swoper$suffix.swo_customer_type c on a.cust_type=c.id 
				where a.city ='$city'   and a.status='C'  and  a.company_name='".$record['company_name']."'  and  a.salesman ='".$name['name']."'
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
                if(empty($spanning)){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index)));
                }
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
            $sql_edit_money="select edit_money from acc_service_comm_dtl where hdr_id='$index'";
            $records_edit_money = Yii::app()->db->createCommand($sql_edit_money)->queryRow();
            if(!empty($records_edit_money)){
                $money_all=$money+$records_edit_money['edit_money'];
            }else{
                $money_all=$money;
            }
            $fuwu=$this->getAmount($city,$cust_type,$start_dt,$money_all);//本月提成比例服务
            $fuwu_last=$this->getAmountLast($year,$month,$index);//上月提成比例服务
            $fuwumoney=$moneys*$fuwu_last;
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
        $start_dt=$years."-".$months."-01";
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
                $spanning=$this->getRoyalty($index,$city,$years,$months,$records['othersalesman']);
                if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                    $cust_type='fw';
                    $moneys+=$c;
                    if(!empty($records['othersalesman'])){
                        $money+=$c*$spanning;
                    }else{
                        $money+=$c;
                    }
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
//                //                $records['company_name']=str_replace(' ','',$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N' and salesman='".$records['salesman']."'";
                $recordss = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$recordss['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);
                $records['salesman']=str_replace('(','',$records['salesman']);
                $records['salesman']=str_replace(')','',$records['salesman']);
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['salesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
//                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
//                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                if(empty($records1['id'])){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Some records cannot be calculated') );
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/edit',array('year'=>$years,'month'=>$months,'index'=>$index)));
                }
                $fuwu_last=$this->getAmountLast($year,$month,$records1['id']);//上月提成比例服务
                $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                if(isset($m)){
                    if(!empty($fuwu_last)){
                        $m=$m*$fuwu_last;
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            $cust_type='fw';
                            if(!empty($records['othersalesman'])){
                                $money1+=$m*$spanning;
                            }else{
                                $money1+=$m;
                            }
                            $sqlct="update swoper$suffix.swo_service set royalty='".$fuwu_last."'  where id='$ai'";
                            $model = Yii::app()->db->createCommand($sqlct)->execute();
                        }
                    }else{
                        $m=$m*$royalty[$ai];
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            $cust_type='fw';
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

        $sql_new_money="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records_new_money = Yii::app()->db->createCommand($sql_new_money)->queryRow();
        $fuwu_last=$this->getAmountLast($years,$months,$index);//上月提成比例服务
        if(!empty($records_new_money)){
            if(!empty($records_new_money['new_money'])&&$records_new_money['new_money']!=0){
                $new_m=$records_new_money['new_money'];
            }else{
                $new_m=0;
            }
            $new_money=$moneys+$new_m;
        }else{
            $new_money=$moneys;
        }
        $fuwu=$this->getAmount($city,$cust_type,$start_dt,$new_money);//提成比例服务
//            if(empty($zhuangji)){
//                $zhuangji=0;
//            }
        $money=$money*$fuwu_last;//更改新增提成
        $fuwumoney=$money+$money1;//更改总和
//        print_r($fuwu);exit();
        //新增补充修改
        $sql_new="update acc_service_comm_dtl set new_calc='$fuwu' where hdr_id='$index'";
        $model = Yii::app()->db->createCommand($sql_new)->execute();
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
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/end',array('year'=>$years,'month'=>$months,'index'=>$index)));
                    }
                    $new=$a/$records['all_number'];
                }
                if($records['surplus']!=NULL){
                    $m=$new*$records['surplus'];
                }else{
                    $m=0;
                }
                //                $records['company_name']=str_replace(' ','',$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N' and salesman='".$records['salesman']."'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$records['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);
                $records['salesman']=str_replace('(','',$records['salesman']);
                $records['salesman']=str_replace(')','',$records['salesman']);
                if(empty($records['city'])){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/end',array('year'=>$years,'month'=>$months,'index'=>$index)));
                }
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['salesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
//                $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
//                $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                if(empty($records1['id'])){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Some records cannot be calculated') );
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/end',array('year'=>$years,'month'=>$months,'index'=>$index)));
                }
                $fuwu_last=$this->getAmountLast($year,$month,$records1['id']);//上月提成比例服务
                $spanning=$this->getRoyalty($index,$city,$year,$month,$records['othersalesman']);
                if(isset($m)){
                    if(!empty($fuwu_last)){
                        $m=$m*$fuwu_last;
                        if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                            if(!empty($records['othersalesman'])){
                                $money[]=$m*$spanning;
                            }else{
                                $money[]=$m;
                            }
                            $sqlct="update swoper$suffix.swo_service set royalty='".$fuwu_last."'  where id='$ai'";
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
                //                $records['company_name']=str_replace(' ','',$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='A' and salesman='".$records['salesman']."' order by status_dt ";//更改
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
                        if($records['all_number']==0){
                            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
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
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
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
                                Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
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
//                    $sql2 = "select new_calc from  acc_service_comm_dtl where hdr_id='" . $records1['id'] . "'";
//                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    if(empty($records1['id'])){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Some records cannot be calculated') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performance',array('year'=>$year,'month'=>$month,'index'=>$index)));
                    }
                    $fuwu_last=$this->getAmountLast($year,$month,$records1['id']);//上月提成比例服务
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    if (!empty($a)) {
                        $moneys += $a * $otherspanning;
                        $a = $a * $fuwu_last;
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
//                    $sql2 = "select new_calc from  acc_service_comm_dtl where hdr_id='" . $records1['id'] . "'";
//                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    if(empty($records1['id'])){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Some records cannot be calculated') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performance',array('year'=>$year,'month'=>$month,'index'=>$index)));
                    }
                    $fuwu_last=$this->getAmountLast($year,$month,$records1['id']);//上月提成比例服务
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    if (!empty($a)) {
                        $moneys += $a * $otherspanning;
                        $a = $a * $fuwu_last;
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
//                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
//                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    if(empty($records1['id'])){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Some records cannot be calculated') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceedit',array('year'=>$year,'month'=>$month,'index'=>$index)));
                    }
                    $fuwu_last=$this->getAmountLast($year,$month,$records1['id']);//上月提成比例服务
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    $fuwumoney=$c*$fuwu_last;
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
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N' and salesman='".$records['salesman']."'";
                $recordss = Yii::app()->db->createCommand($sql)->queryRow();
                $date=$recordss['first_dt'];
                $timestrap=strtotime($date);
                $years=date('Y',$timestrap);
                $months=date('m',$timestrap);
                $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                $sql1="select * from acc_service_comm_hdr where year_no='".$years."' and month_no='".$months."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                if($records1['performance']==1){
//                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";
//                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    if(empty($records1['id'])){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Some records cannot be calculated') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceedit',array('year'=>$year,'month'=>$month,'index'=>$index)));
                    }
                    $fuwu_last=$this->getAmountLast($years,$months,$records1['id']);//上月提成比例服务
                    if(isset($m)){
                        if($fuwu_last!=0&&empty(!$fuwu_last)){
                            $m=$m*$fuwu_last;
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money1+=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$fuwu_last."'  where id='$ai'";
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
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='N' and salesman='".$records['salesman']."'";
                $records = Yii::app()->db->createCommand($sql)->queryRow();
                if(empty($records)){
                    Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Data is filled in incorrectly, please check and modify before proceeding') );
                    Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceedit',array('year'=>$years,'month'=>$months,'index'=>$index)));
                }
                $date=$records['first_dt'];
                $timestrap=strtotime($date);
                $year=date('Y',$timestrap);
                $month=date('m',$timestrap);
                $records['othersalesman']=str_replace('(','',$records['othersalesman']);
                $records['othersalesman']=str_replace(')','',$records['othersalesman']);
                $sql1="select * from acc_service_comm_hdr where year_no='".$year."' and month_no='".$month."' and city='".$records['city']."' and  concat_ws(' ',employee_name,employee_code)= '".$records['othersalesman']."' ";
                $records1 = Yii::app()->db->createCommand($sql1)->queryRow();
                if($records1['performance']==1){
//                    $sql2="select new_calc from  acc_service_comm_dtl where hdr_id='".$records1['id']."'";//当初提成比例
//                    $records2 = Yii::app()->db->createCommand($sql2)->queryRow();
                    if(empty($records1['id'])){
                        Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Some records cannot be calculated') );
                        Yii::app()->getRequest()->redirect(Yii::app()->createUrl('commission/performanceend',array('year'=>$years,'month'=>$months,'index'=>$index)));
                    }
                    $fuwu_last=$this->getAmountLast($year,$month,$records1['id']);//上月提成比例服务
                    $otherspanning=$this->getOtherRoyalty($index,$city,$year,$month,$records['salesman']);
                    if(isset($m)){
                        if($fuwu_last!=0&&empty(!$fuwu_last)){
                            $m=$m*$fuwu_last;
                            if($records['cust_type']=='1'||$records['cust_type']=='2'||$records['cust_type']=='3'||$records['cust_type']=='5'||$records['cust_type']=='6'||$records['cust_type']=='7'){
                                $money[]=$m*$otherspanning;
                            }
                            $sqlct="update swoper$suffix.swo_service set royaltys='".$fuwu_last."'  where id='$ai'";
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
                //                $records['company_name']=str_replace(' ','',$records['company_name']);
                $sql="select * from  swoper$suffix.swo_service where company_name='".$records['company_name']."' and cust_type='".$records['cust_type']."' and status='A' and salesman='".$records['salesman']."' order by status_dt ";//更改
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
//                    $record[$i]['company_name']=str_replace(' ','',$record[$i]['company_name']);
                    $sqls="select * from  swoper$suffix.swo_service where company_name='".$record[$i]['company_name']."' and cust_type='".$record[$i]['cust_type']."' and status='N' and salesman='".$record[$i]['salesman']."'";
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


    public function renewalSale($id,$index,$years,$months){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $money=array();
        //   $zhuangji=0;
        foreach ($id as $ai){
            $mons=array();
            $sql="select * from swoper$suffix.swo_service where id='$ai'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            $sql1="update swoper$suffix.swo_service  set royalty=0.01  where id='$ai'";
            $model = Yii::app()->db->createCommand($sql1)->execute();
            if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                $a=$records['amt_paid'];
            }else{
                $a=$records['amt_paid']*$records['ctrt_period'];
            }
            $money[]=$a;
        }
        $money=array_sum($money);
        $moneys=$money*0.01;
        $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($records)){
            $sql2 = "insert into acc_service_comm_dtl(
					hdr_id, renewal_amount,renewal_money
				) values (
					'".$index."','".$moneys."','".$money."'
				)";
        }else{
            $sql2="update acc_service_comm_dtl set renewal_amount='$moneys',renewal_money='$money'  where hdr_id='$index'";
        }
        $model = Yii::app()->db->createCommand($sql2)->execute();
//        print_r('<pre>');
//        print_r($sql1);  exit();

    }


    public function renewalendSale($id,$index,$years,$months){
        $city = Yii::app()->user->city();
        $suffix = Yii::app()->params['envSuffix'];
        $money=array();
        //   $zhuangji=0;
        foreach ($id as $ai){
            $mons=array();
            $sql="select * from swoper$suffix.swo_service where id='$ai'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();

            if($records['paid_type']=='1'||$records['paid_type']=='Y'){
                $a=$records['amt_paid'];
            }else{
                $a=$records['amt_paid']*$records['ctrt_period'];
            }
            $moneys=$a/$records['all_number']*$records['surplus'];
            $money[]=$moneys;
        }
        $money=array_sum($money);
        $money=-$money;
        $money=$money*0.01;
        $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        if(empty($records)){
            $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, renewalend_amount
				) values (
					'".$index."','".$money."'
				)";
        }else{
            $sql1="update acc_service_comm_dtl set renewalend_amount='$money'  where hdr_id='$index'";
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

    public function getAmountLast($year,$month,$id){
        $date=$year."/".$month;
        if($date<'2020/8'){
        }else{
            $month=$month-1;
            if($month==0){
                $month=12;
                $year=$year-1;
            }
        }
        $sql="select employee_name from acc_service_comm_hdr where id=$id";
        $name = Yii::app()->db->createCommand($sql)->queryScalar();
        $suffix = Yii::app()->params['envSuffix'];
        $sql1="select a.*, b.new_calc ,e.user_id from acc_service_comm_hdr a
              left outer join acc_service_comm_dtl b on  b.hdr_id=a.id
              left outer join hr$suffix.hr_employee d on  a.employee_code=d.code 
              left outer join hr$suffix.hr_binding e on  a.employee_name=e.employee_name            
              where  a.year_no='$year' and  a.month_no='$month' and a.employee_name='$name'
";
        $arr = Yii::app()->db->createCommand($sql1)->queryRow();
        $sql_point="select * from sales$suffix.sal_integral where year='$year' and month='$month' and username='".$arr['user_id']."'";
        $point = Yii::app()->db->createCommand($sql_point)->queryRow();
        if(empty($arr['new_calc'])){
            $arr['new_calc']=0.05;
        }
        $new_calc=$arr['new_calc']+$point['point'];
        return $new_calc;
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
            $record = Yii::app()->db->createCommand($sql)->queryScalar();
            if($record!=$records){
                $proportion=0.5;
            }
        }
        return $proportion;
    }
}
