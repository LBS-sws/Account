<?php

class CommissionController extends Controller
{
    public $function_id='XS01';

    public function filters()
    {
        return array(
            'enforceRegisteredStation',
            'enforceSessionExpiration',
            'enforceNoConcurrentLogin',
            'accessControl', // perform access control for CRUD operations
            'postOnly + delete', // we only allow deletion via POST request
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',
                'actions'=>array('new','performance','performanceedit','performanceend','position','product',
                    'renewal','renewalend'),
                'expression'=>array('CommissionController','allowReadWrite'),
            ),
            array('allow',//超過兩個月後，不允許計算
                'actions'=>array('clear'),
                'expression'=>array('CommissionController','allowEditDate'),
            ),
            array('allow',//不允許使用舊版的銷售提成計算
                'actions'=>array('save','clear','add','newsave','editsave','endsave','performancesave','performanceeditsave','performanceendsave','renewalsave','renewalendsave','productsave'),
                'expression'=>array('CommissionController','allowEditOld'),
            ),
            array('allow',
                'actions'=>array('view','index','index_s','edit','end','test','remove','deleteOne'),
                'expression'=>array('CommissionController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    //清空重复的数据
    public function actionRemove(){
        $rows = Yii::app()->db->createCommand()->select("max(id) as id")
            ->from("acc_service_comm_hdr")
            ->group('year_no, month_no, employee_code, employee_name, city')
            ->having('count(*)>1')
            ->queryAll();
        if($rows){
            foreach ($rows as $row){
                Yii::app()->db->createCommand()->delete('acc_service_comm_hdr', 'id=:id',
                    array(':id'=>$row['id']));
            }
            echo "success";
        }else{
            echo "don't have data";
        }
    }

    //刪除某條數據的数据
    public function actionDeleteOne($id=0){
        $row = Yii::app()->db->createCommand()->select("id,year_no,month_no,employee_code,employee_name")
            ->from("acc_service_comm_hdr")
            ->where("id=:id",array(':id'=>$id))
            ->queryRow();
        if($row){
            Yii::app()->db->createCommand()->delete('acc_service_comm_hdr', 'id=:id',
                array(':id'=>$id));
            echo $row["employee_name"]." (".$row["employee_code"].")"." - ".$row["year_no"]."/".$row["month_no"];
            echo "<br/>";
            echo "delete success!";
        }else{
            echo "don't have data";
        }
    }

    public function actionTest($year=2021,$month=6,$id=0)
    {
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("code,name,city")
            ->from("hr$suffix.hr_employee")
            ->where("id=:id",array(":id"=>$id))
            ->queryRow();
        if($row){
            $id = Yii::app()->db->createCommand()->select("id")
                ->from("acc_service_comm_hdr")
                ->where("year_no=:year_no and month_no=:month_no and employee_code=:employee_code",
                    array(":year_no"=>$year,":month_no"=>$month,":employee_code"=>$row["code"])
                )->queryScalar();
            if($id){
                echo "error hdr_id";
            }else{
                //year_no,month_no,employee_code,employee_name,city
                Yii::app()->db->createCommand()->insert("acc_service_comm_hdr",
                    array(
                        "year_no"=>$year,
                        "month_no"=>$month,
                        "employee_code"=>$row["code"],
                        "employee_name"=>$row["name"],
                        "city"=>$row["city"]
                    )
                );
                echo "success";
            }
        }else{
            echo "error staff_id";
        }
    }

    public function actionIndex()
    {
        $model = new ReportXS01Form;
        //     $model->retrieveDatas($model);
//        print_r('<pre>');
//        print_r($model);
        $this->render('index',array('model'=>$model));
    }

    public function actionIndex_s($pageNum=0)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }

        if(!empty($_POST['ReportXS01List']['year'])){
            $year=$_POST['ReportXS01List']['year'];
            $month=$_POST['ReportXS01List']['month'];
        }
        if(!empty($_POST['ReportXS01Form']['year'])){
            $year=$_POST['ReportXS01Form']['year'];
            $month=$_POST['ReportXS01Form']['month'];
        }
        $session = Yii::app()->session;
        if(!empty($year)){
            $session['year']= $year;
            $session['month']=$month;
        }
        if(empty($year)&&empty($month)){
            $year=$session['year'];
            $month=$session['month'];
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum,$year,$month);
        $this->render('index_s',array('model'=>$model,'year'=>$year,'month'=>$month));
    }



    public function actionView($year,$month,$index)
    {
        $a=$this->position($index);
        $model = new ReportXS01Form('view');
        if (!$model->retrieveData($index,$a)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
//      print_r('<pre>');
//       print_r($index);
            $this->render('view',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
        }
    }

    public function actionNew($pageNum=0,$year,$month,$index)
    {

        $model = new ReportXS01List;

        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->copy();
        $model->newDataByPage($model->pageNum,$year,$month,$index);
//                print_r('<pre>');
//        print_r($_POST);
        $this->render('new',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }

    public function actionEdit($pageNum=0,$year,$month,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->editDataByPage($model->pageNum,$year,$month,$index);
//        print_r('<pre>');
//        print_r($model);
        $this->render('edit',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }


    public function actionEnd($pageNum=0,$year,$month,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->endDataByPage($model->pageNum,$year,$month,$index);
        $this->render('end',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }

    public function actionPerformance($pageNum=0,$year,$month,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->performanceDataByPage($model->pageNum,$year,$month,$index);
        $this->render('performance',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }

    public function actionPerformanceEdit($pageNum=0,$year,$month,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->performanceeditDataByPage($model->pageNum,$year,$month,$index);
        $this->render('performanceedit',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }

    public function actionPerformanceEnd($pageNum=0,$year,$month,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->performanceendDataByPage($model->pageNum,$year,$month,$index);
        $this->render('performanceend',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }

    public function actionRenewal($pageNum=0,$year,$month,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->renewalDataByPage($model->pageNum,$year,$month,$index);
        $this->render('renewal',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }

    public function actionRenewalEnd($pageNum=0,$year,$month,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->renewalendDataByPage($model->pageNum,$year,$month,$index);
        $this->render('renewalend',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }

    public function actionProduct($pageNum=0,$year,$month,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->productDataByPage($model->pageNum,$year,$month,$index);
        $this->render('product',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }

    public function actionAdd($year,$month,$index)
    {
        $model = new ReportXS01Form('add');
        $this->render('add',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
    }


    public function actionSave($year,$month,$index)
    {

        if (isset($_POST['ReportXS01Form'])) {
            $model = new ReportXS01Form;
            $model->attributes = $_POST['ReportXS01Form'];
//            print_r('<pre>');
//            print_r( $model->attributes);
            if ($model->validate()) {
                $model->saveData($_POST['ReportXS01Form'],$index);
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('new',array('model'=>$model,'index'=>$index,'year'=>$year,'month'=>$month,));
            }
        }
    }

    public function actionNewSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $a=$this->position($index);
        $date=$year."/".$month.'/'."01";
        $date1='2020/07/01';
        $employee=$this->getEmployee($index,$year,$month);
        if($city=='CD'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1||(($city=='FS'||$city=='NJ')&&strtotime($date)<strtotime('2021/02/01'))){
            $model = new ReportXS01SList;
        }else{
            $model = new ReportXS01List;
        }
        if (isset($_POST['ReportXS01From']['id'])) {
//            $model->attributes = $_POST['ReportXS01List'];
//                    print_r('<pre>');
//        print_r($_POST['ReportXS01From']['id']);
            $model->newSale($_POST['ReportXS01From']['id'],$year,$month,$index);

            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            ReportXS01SList::clearNewServiceCommission($index);
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            $new_calc = ReportXS01List::getAmountForCommId($index);
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, new_calc, new_amount,new_money
				) values (
					'".$index."','$new_calc','0','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set new_calc='$new_calc' , new_amount='0',new_money='0' where hdr_id='$index'";
            }
            $record = Yii::app()->db->createCommand($sql1)->execute();
            $sql2="update acc_service_comm_hdr set performance='2'  where id='$index'";
            $records = Yii::app()->db->createCommand($sql2)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }
    public function actionEditSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $a=$this->position($index);
        $date=$year."/".$month.'/'."01";
        $date1='2020/07/01';
        $employee=$this->getEmployee($index,$year,$month);
        if($city=='CD'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1||(($city=='FS'||$city=='NJ')&&strtotime($date)<strtotime('2021/02/01'))){
            $model = new ReportXS01SList;
        }else{
            $model = new ReportXS01List;
        }
        // print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->editSale($_POST['ReportXS01List']['id'],$index,$_POST['ReportXS01List']['royalty'],$year,$month);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/edit',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, edit_amount,edit_money
				) values (
					'".$index."','0' ,'0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set edit_amount='0' ,edit_money='0' where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/edit',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }

    public function actionEndSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $a=$this->position($index);
        $date=$year."/".$month.'/'."01";
        $date1='2020/07/01';
        $employee=$this->getEmployee($index,$year,$month);
        if($city=='CD'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1||(($city=='FS'||$city=='NJ')&&strtotime($date)<strtotime('2021/02/01'))){
            $model = new ReportXS01SList;
        }else{
            $model = new ReportXS01List;
        }
        //print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->endSale($_POST['ReportXS01List']['id'],$index,$_POST['ReportXS01List']['royalty'],$year,$month);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/end',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, end_amount
				) values (
					'".$index."','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set end_amount='0'  where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/end',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }

    public function actionPerformanceSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $a=$this->position($index);
        $date=$year."/".$month.'/'."01";
        $date1='2020/07/01';
        $employee=$this->getEmployee($index,$year,$month);
        if($city=='CD'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1||(($city=='FS'||$city=='NJ')&&strtotime($date)<strtotime('2021/02/01'))){
            $model = new ReportXS01SList;
        }else{
            $model = new ReportXS01List;
        }
        //print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01From']['id'])) {
            $model->performanceSale($_POST['ReportXS01From']['id'],$year,$month,$index);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/performance',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, performance_amount,out_money
				) values (
					'".$index."','0','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set performance_amount='0' ,out_money='0'  where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/performance',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }

    public function actionPerformanceEditSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $a=$this->position($index);
        $date=$year."/".$month.'/'."01";
        $date1='2020/07/01';
        $employee=$this->getEmployee($index,$year,$month);
        if($city=='CD'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1||(($city=='FS'||$city=='NJ')&&strtotime($date)<strtotime('2021/02/01'))){
            $model = new ReportXS01SList;
        }else{
            $model = new ReportXS01List;
        }
        //print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->performanceeditSale($_POST['ReportXS01List']['id'],$year,$month,$index,$_POST['ReportXS01List']['royalty']);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/performanceedit',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, performanceedit_amount,performanceedit_money
				) values (
					'".$index."','0' ,'0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set performanceedit_amount='0' ,performanceedit_money='0' where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/performanceedit',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }

    public function actionPerformanceEndSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $a=$this->position($index);
        $date=$year."/".$month.'/'."01";
        $date1='2020/07/01';
        $employee=$this->getEmployee($index,$year,$month);
        if($city=='CD'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1||(($city=='FS'||$city=='NJ')&&strtotime($date)<strtotime('2021/02/01'))){
            $model = new ReportXS01SList;
        }else{
            $model = new ReportXS01List;
        }
        //print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->performanceendSale($_POST['ReportXS01List']['id'],$index,$_POST['ReportXS01List']['royalty'],$year,$month);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/performanceend',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, performanceend_amount
				) values (
					'".$index."','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set performanceend_amount='0'  where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/performanceend',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }

    public function actionRenewalSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $model = new ReportXS01List;
//        print_r(1); exit();
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->renewalSale($_POST['ReportXS01List']['id'],$index,$year,$month);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/renewal',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, renewal_amount, renewal_money
				) values (
					'".$index."','0','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set renewal_amount='0',renewal_money='0'  where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/renewal',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }

    public function actionRenewalEndSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $model = new ReportXS01List;
        //print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->renewalendSale($_POST['ReportXS01List']['id'],$index,$year,$month);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/renewalend',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, renewalend_amount
				) values (
					'".$index."','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set renewalend_amount='0'  where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/renewalend',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }

    public function actionProductSave($year,$month,$index)
    {
        $city=Yii::app()->user->city();
        $model = new ReportXS01List;
        //print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->productSale($_POST['ReportXS01List']['id'],$index,$year,$month);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/product',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, product_amount
				) values (
					'".$index."','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set product_amount='0'  where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/product',array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }

    public function actionClear($year,$month,$index,$clear)
    {
        $city=Yii::app()->user->city();
        $model = new ReportXS01List;
        if(empty($_POST['ReportXS01List']['id'])){
            if(!empty($_POST['ReportXS01From']['id'])){
                $id=$_POST['ReportXS01From']['id'];
            }
        }else{
            if(!empty($_POST['ReportXS01List']['id'])){
                $id=$_POST['ReportXS01List']['id'];
            }
        }
     //   print_r($id);exit();
        if (isset($id)) {
            $model->clearn($id);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/'.$clear,array('year'=>$year,'month'=>$month,'index'=>$index)));
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/'.$clear,array('year'=>$year,'month'=>$month,'index'=>$index)));
        }
    }



    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('XS01');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('XS01');
    }

    public static function allowEditDate() {
        $year = key_exists("year",$_GET)?$_GET["year"]:0;
        $month = key_exists("month",$_GET)?$_GET["month"]:0;
        if(date("Y/m/d",strtotime("$year-$month-01"))>=date("Y/m/01",strtotime("-1 months"))){
            return Yii::app()->user->validRWFunction('XS01');
        }else{
            return false;
        }
    }

    public static function allowEditOld() {//不允許使用舊版的銷售提成計算
        return false;
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

    public  function getEmployee($index,$year,$month){
        $suffix = Yii::app()->params['envSuffix'];
        $sql="select e.user_id,a.employee_code from acc_service_comm_hdr a             
              left outer join hr$suffix.hr_employee d on  a.employee_code=d.code 
              left outer join hr$suffix.hr_binding e on  d.id=e.employee_id
              where a.id='$index'
";
        $records = Yii::app()->db->createCommand($sql)->queryRow();
        $sql1="select visit_dt from sales$suffix.sal_visit   where username='".$records['user_id']."' order by visit_dt
";
        $record = Yii::app()->db->createCommand($sql1)->queryRow();
        //$record['visit_dt'] = "2021/05/01";
        $timestrap=strtotime($record['visit_dt']);
        $years=date('Y',$timestrap);
        $months=date('m',$timestrap);
//        print_r($record);exit();
        if(date('d',$timestrap)=='01'){
            if($years==$year&&$months==$month){
                $a=1;//不加入东成西就
            }else{
                $a=2;
            }
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
}
