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
                'actions'=>array('save','new','add','newsave','editsave','endsave'),
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
            if (isset($session['criteria_xs01']) && !empty($session['criteria_xs01'])) {
                $criteria = $session['criteria_xs01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
//        print_r('<pre>');
//        print_r($_POST);
        $this->render('index_s',array('model'=>$model));
    }



    public function actionView($index)
    {
        $model = new ReportXS01Form('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
//      print_r('<pre>');
//       print_r($model);
            $this->render('view',array('model'=>$model,'index'=>$index,));
        }
    }

    public function actionNew($pageNum=0,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['criteria_xs01']) && !empty($session['criteria_xs01'])) {
                $criteria = $session['criteria_xs01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->copy();
        $model->newDataByPage($model->pageNum,$index);
//                print_r('<pre>');
//        print_r($model);
        $this->render('new',array('model'=>$model,'index'=>$index,));
    }

    public function actionEdit($pageNum=0,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['criteria_xs01']) && !empty($session['criteria_xs01'])) {
                $criteria = $session['criteria_xs01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->editDataByPage($model->pageNum,$index);
//        print_r('<pre>');
//        print_r($model);
        $this->render('edit',array('model'=>$model,'index'=>$index,));
    }


    public function actionEnd($pageNum=0,$index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01List'])) {
            $model->attributes = $_POST['ReportXS01List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['criteria_xs01']) && !empty($session['criteria_xs01'])) {
                $criteria = $session['criteria_xs01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->endDataByPage($model->pageNum,$index);
        $this->render('end',array('model'=>$model,'index'=>$index,));
    }

    public function actionAdd($index)
    {
        $model = new ReportXS01Form('add');
        $this->render('add',array('model'=>$model,'index'=>$index));
    }


    public function actionSave($index)
{

    if (isset($_POST['ReportXS01Form'])) {
        $model = new ReportXS01Form($_POST['ReportXS01Form']['scenario']);
        $model->attributes = $_POST['ReportXS01Form'];
//            print_r('<pre>');
//            print_r( $model->attributes);
        if ($model->validate()) {
            $model->saveData($_POST['ReportXS01Form'],$index);
            Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
            $this->redirect(Yii::app()->createUrl('commission/new',array('index'=>$index)));
        } else {
            $message = CHtml::errorSummary($model);
            Dialog::message(Yii::t('dialog','Validation Message'), $message);
            $this->render('new',array('model'=>$model,'index'=>$index));
        }
    }
}

    public function actionNewSave($index)
    {
        $model = new ReportXS01List;
        if (isset($_POST['ReportXS01From']['id'])) {
//            $model->attributes = $_POST['ReportXS01List'];
//                    print_r('<pre>');
//        print_r($_POST['ReportXS01From']['id']);
            $model->newSale($_POST['ReportXS01From']['id'],$index);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/new',array('index'=>$index)));
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'),'请勾选列表');
            $this->redirect(Yii::app()->createUrl('commission/new',array('index'=>$index)));
        }
    }
    public function actionEditSave($index)
    {
        $model = new ReportXS01List;
       // print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->editSale($_POST['ReportXS01List']['id'],$index);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/edit',array('index'=>$index)));
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'),'请勾选列表');
            $this->redirect(Yii::app()->createUrl('commission/edit',array('index'=>$index)));
        }
    }

    public function actionEndSave($index)
    {
        $model = new ReportXS01List;
         print_r($_POST['ReportXS01List']['id']);
        if (isset($_POST['ReportXS01List']['id'])) {
            $model->endSale($_POST['ReportXS01List']['id'],$index);
            Dialog::message(Yii::t('dialog','Validation Message'),Yii::t('dialog','Save Done') );
            $this->redirect(Yii::app()->createUrl('commission/end',array('index'=>$index)));
        }else{
            Dialog::message(Yii::t('dialog','Validation Message'),'请勾选列表');
            $this->redirect(Yii::app()->createUrl('commission/end',array('index'=>$index)));
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('XS01');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('XS01');
    }
}
