<?php

class ExpenseSearchController extends Controller
{
	public $function_id='DE04';
	
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
				'actions'=>array('edit'),
				'expression'=>array('ExpenseSearchController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','filedownload','print','listFile'),
				'expression'=>array('ExpenseSearchController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

    public function actionPrint($index)
    {
        $model = new ExpenseApplyForm();
        if (!$model->retrievePrint($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->printOne();
            Yii::app()->end();
        }
    }

	public function actionIndex($pageNum=0) 
	{
		$model = new ExpenseSearchList();
		if (isset($_POST['ExpenseSearchList'])) {
			$model->attributes = $_POST['ExpenseSearchList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session['expenseSearch_c01']) && !empty($session['expenseSearch_c01'])) {
				$criteria = $session['expenseSearch_c01'];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionView($index)
	{
		$model = new ExpenseSearchForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionEdit($index)
	{
		$model = new ExpenseSearchForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,'The requested page does not exist.');
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        if($doctype==="EXINFO"){
            $sql = "select id from acc_expense_info where id = $docId";
        }else{
            $sql = "select id from acc_expense where id = $docId";
        }
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'ExpenseSearchForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }

    public function actionListFile() {
        $model = new ExpenseSearchForm();
        if (isset($_POST['ExpenseSearchForm'])) {
            $model->attributes = $_POST['ExpenseSearchForm'];

            $docman = new DocMan($model->docType,$model->docId,'ExpenseSearchForm');
            $docman->setDocMasterId($model->docType,$model->docId,$model->docMasterId);
            $result = $docman->genTableFileListEx(true);
            print json_encode($result);
        } else {
            echo "NIL";
        }
    }
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('DE04');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('DE04');
	}
}
