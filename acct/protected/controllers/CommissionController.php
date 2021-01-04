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
                'actions'=>array('save','new','add','newsave','performance','performanceedit','performanceend','editsave','endsave','performancesave','position',
                    'performanceeditsave','performanceendsave','renewal','renewalend','renewalsave','renewalendsave','clear'),
                'expression'=>array('CommissionController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('view','index','index_s','edit','end'),
                'expression'=>array('CommissionController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
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
        if($city=='CD'||$city=='FS'||$city=='NJ'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1){
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
            $sql="select * from acc_service_comm_dtl where hdr_id='$index'";
            $records = Yii::app()->db->createCommand($sql)->queryRow();
            if(empty($records)){
                $sql1 = "insert into acc_service_comm_dtl(
					hdr_id, new_calc, new_amount,new_money
				) values (
					'".$index."','0','0','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set new_calc='0' , new_amount='0',new_money='0' where hdr_id='$index'";
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
        if($city=='CD'||$city=='FS'||$city=='NJ'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1){
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
        if($city=='CD'||$city=='FS'||$city=='NJ'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1){
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
        if($city=='CD'||$city=='FS'||$city=='NJ'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1){
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
        if($city=='CD'||$city=='FS'||$city=='NJ'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1){
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
        if($city=='CD'||$city=='FS'||$city=='NJ'||$city=='TJ'||$a==1||strtotime($date)<strtotime($date1)||$employee==1){
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
					hdr_id, renewal_amount
				) values (
					'".$index."','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set renewal_amount='0'  where hdr_id='$index'";
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
					hdr_id, renewal_amount
				) values (
					'".$index."','0'
				)";
            }else{
                $sql1="update acc_service_comm_dtl set renewal_amount='0'  where hdr_id='$index'";
            }
            $model = Yii::app()->db->createCommand($sql1)->execute();
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/renewalend',array('year'=>$year,'month'=>$month,'index'=>$index)));
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
        $sql="select entry_time from hr$suffix.hr_employee where code= '".$records['employee_code']."' ";
        $record = Yii::app()->db->createCommand($sql)->queryScalar();
        $timestraps=strtotime($record);
        $entry_time_year=date('Y',$timestraps);
        $entry_time_month=date('m',$timestraps);
        if($entry_time_year==$year&&$entry_time_month==$month){
            $sql1="select visit_dt from sales$suffix.sal_visit   where username='".$records['user_id']."' order by visit_dt
";
            $record = Yii::app()->db->createCommand($sql1)->queryRow();
            $timestrap=strtotime($record['visit_dt']);
            $years=date('Y',$timestrap);
            $months=date('m',$timestrap);
//        print_r($record);exit();
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
}
