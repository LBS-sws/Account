<?php

class QueryController extends Controller
{
	public $function_id='XS02';
	
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
				'actions'=>array('new','edit','delete','save','downs','renewal','renewalend'),
				'expression'=>array('QueryController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','index_s','end','view','performance','performanceedit','performanceend'),
				'expression'=>array('QueryController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex()
	{
        $model = new ReportXS02Form;
        $this->render('index',array('model'=>$model));
	}


    public function actionIndex_s($pageNum=0)
    {
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
        } else {
            $session = Yii::app()->session;
            if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
                $criteria = $session[$model->criteriaName()];
                $model->setCriteria($criteria);
            }
        }
//                print_r('<pre>');
//        print_r($_POST);exit();
        if(!empty($_POST['ReportXS02List']['year'])){
            $year=$_POST['ReportXS02List']['year'];
            $month=$_POST['ReportXS02List']['month'];
        }
        if(!empty($_POST['ReportXS02Form']['year'])){
            $year=$_POST['ReportXS02Form']['year'];
            $month=$_POST['ReportXS02Form']['month'];
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

        $this->render('index_s',array('model'=>$model,'year'=>$year,'month'=>$month,));
    }

    public function actionView($year,$month,$index)
    {
        $a=$this->actionPosition($index);
        $model = new ReportXS02Form('view');
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
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
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
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
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
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
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
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
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
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
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
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
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
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
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
        $model = new ReportXS02List;
        if (isset($_POST['ReportXS02List'])) {
            $model->attributes = $_POST['ReportXS02List'];
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

	public function actionDowns($year,$month,$index)
	{

        $model = new ReportXS02Form('view');
        $model->retrieveData($index);
        $view=$model;
        $model = new ReportXS02List;
        $model->retrieveXiaZai($year,$month,$index,$view);

	}
//
//	public function actionView($index,$city)
//	{
//		$model = new AccountForm('view');
//		$city = ($city='99999') ? Yii::app()->user->city() : $city;
//		if (!$model->retrieveData($index,$city)) {
//			throw new CHttpException(404,'The requested page does not exist.');
//		} else {
//			$this->render('form',array('model'=>$model,));
//		}
//	}
//	
//	public function actionNew()
//	{
//		$model = new AccountForm('new');
//		$this->render('form',array('model'=>$model,));
//	}
//	
//	public function actionEdit($index,$city)
//	{
//		$model = new AccountForm('edit');
//		$city = ($city='99999') ? Yii::app()->user->city() : $city;
//		if (!$model->retrieveData($index,$city)) {
//			throw new CHttpException(404,'The requested page does not exist.');
//		} else {
//			$this->render('form',array('model'=>$model,));
//		}
//	}
//	
//	public function actionDelete()
//	{
//		$model = new AccountForm('delete');
//		if (isset($_POST['AccountForm'])) {
//			$model->attributes = $_POST['AccountForm'];
//			if ($model->isOccupied($model->id)) {
//				Dialog::message(Yii::t('dialog','Warning'), Yii::t('dialog','This record is already in use'));
//				$this->redirect(Yii::app()->createUrl('account/edit',array('index'=>$model->id)));
//			} else {
//				$model->saveData();
//				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
//				$this->redirect(Yii::app()->createUrl('account/index'));
//			}
//		}
//	}
//	
//	/**
//	 * Performs the AJAX validation.
//	 * @param CModel the model to be validated
//	 */
//	protected function performAjaxValidation($model)
//	{
//		if(isset($_POST['ajax']) && $_POST['ajax']==='account-form')
//		{
//			echo CActiveForm::validate($model);
//			Yii::app()->end();
//		}
//	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XS02');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XS02');
	}
}
