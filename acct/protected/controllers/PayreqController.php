<?php

class PayreqController extends Controller 
{
	public $function_id='XA04';

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
				'actions'=>array('new','edit','delete','save','submit','request','cancel','fileupload','fileremove'),
				'expression'=>array('PayreqController','allowReadWrite'),
			),
			array('allow', 
				'actions'=>array('index','view','check','filedownload','void'),
				'expression'=>array('PayreqController','allowReadOnly'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex($pageNum=0) 
	{
		$model = new PayReqList;
		if (isset($_POST['PayReqList'])) {
			$model->attributes = $_POST['PayReqList'];
		} else {
			$session = Yii::app()->session;
			if (isset($session[$model->criteriaName()]) && !empty($session[$model->criteriaName()])) {
				$criteria = $session[$model->criteriaName()];
				$model->setCriteria($criteria);
			}
		}
		$model->determinePageNum($pageNum);
		$model->retrieveDataByPage($model->pageNum);
		$this->render('index',array('model'=>$model));
	}

	public function actionCancel()
	{
		if (isset($_POST['PayReqForm'])) {
			$model = new PayReqForm($_POST['PayReqForm']['scenario']);
			$model->attributes = $_POST['PayReqForm'];
			$model->cancel();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Cancel Done'));
			$this->redirect(Yii::app()->createUrl('payreq/edit',array('index'=>$model->id)));
		}
	}

	public function actionVoid()
	{
		if (isset($_POST['PayReqForm'])) {
			$model = new PayReqForm($_POST['PayReqForm']['scenario']);
			$model->attributes = $_POST['PayReqForm'];
			$model->voidRecord();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Void Done'));
			$this->redirect(Yii::app()->createUrl('payreq/edit',array('index'=>$model->id)));
		}
	}

	public function actionRequest()
	{
		if (isset($_POST['PayReqForm'])) {
			$model = new PayReqForm($_POST['PayReqForm']['scenario']);
			$model->attributes = $_POST['PayReqForm'];
			if ($model->validate()) {
				$model->request();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Request Checking Done'));
				$this->redirect(Yii::app()->createUrl('payreq/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionCheck()
	{
		if (isset($_POST['PayReqForm'])) {
			$model = new PayReqForm($_POST['PayReqForm']['scenario']);
			$model->attributes = $_POST['PayReqForm'];
			if ($model->validate()) {
				$model->check();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Checking and Submission Done'));
				$url = $this->allowReadWrite() ? 'payreq/edit' : 'payreq/view';
				$this->redirect(Yii::app()->createUrl($url,array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionSubmit()
	{
		if (isset($_POST['PayReqForm'])) {
			$model = new PayReqForm($_POST['PayReqForm']['scenario']);
			$model->attributes = $_POST['PayReqForm'];
			if ($model->validate()) {
				$model->submit();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Submission Done'));
				$this->redirect(Yii::app()->createUrl('payreq/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionSave()
	{
		if (isset($_POST['PayReqForm'])) {
			$model = new PayReqForm($_POST['PayReqForm']['scenario']);
			$model->attributes = $_POST['PayReqForm'];
			if ($model->validate()) {
				$model->saveData();
				Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
				$this->redirect(Yii::app()->createUrl('payreq/edit',array('index'=>$model->id)));
			} else {
				$message = CHtml::errorSummary($model);
				Dialog::message(Yii::t('dialog','Validation Message'), $message);
				$this->render('form',array('model'=>$model,));
			}
		}
	}

	public function actionView($index)
	{
		$model = new PayReqForm('view');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionNew($index=0)
	{
		if (!$this->checkCashAudit()) {
			Dialog::message(Yii::t('dialog','Information'), Yii::t('trans','Please carry out Cash In Audit Function before apply for new request'));
			$this->redirect(Yii::app()->createUrl('payreq/index'));
		}

		if (!$this->checkT3Audit()) {
			Dialog::message(Yii::t('dialog','Information'), Yii::t('trans','Please carry out T3 Audit Function before apply for new request'));
			$this->redirect(Yii::app()->createUrl('payreq/index'));
		}
		
		$model = new PayReqForm('new');
		if ($index!==0 && $model->retrieveData($index)) {
			$model->id = 0;
			$model->ref_no = '';
			$model->req_dt = date('Y/m/d');
			$model->wfstatus = '';
			$model->wfstatusdesc = '';
			$model->status = 'A';
			$model->no_of_attm['payreq'] = 0;
			$model->no_of_attm['tax'] = 0;
		}
		$this->render('form',array('model'=>$model,));
	}
	
	public function actionEdit($index)
	{
		$model = new PayReqForm('edit');
		if (!$model->retrieveData($index)) {
			throw new CHttpException(404,Yii::t('dialog','Unable to open this record. Maybe you don\'t have corresponding access right.'));
		} else {
			$this->render('form',array('model'=>$model,));
		}
	}
	
	public function actionDelete()
	{
		$model = new PayReqForm('delete');
		if (isset($_POST['PayReqForm'])) {
			$model->attributes = $_POST['PayReqForm'];
			$model->saveData();
			Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
			$this->redirect(Yii::app()->createUrl('payreq/index'));
		}
	}
	
	public function actionFileupload($doctype) {
		$model = new PayReqForm();
		if (isset($_POST['PayReqForm'])) {
			$model->attributes = $_POST['PayReqForm'];
			
			$id = ($_POST['PayReqForm']['scenario']=='new') ? 0 : $model->id;
			$docman = new DocMan($doctype,$id,get_class($model));
			$docman->masterId = $model->docMasterId[strtolower($doctype)];
			if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
			$docman->fileUpload();
			echo $docman->genTableFileList(false);
		} else {
			echo "NIL";
		}
	}
	
	public function actionFileRemove($doctype) {
		$model = new PayReqForm();
		if (isset($_POST['PayReqForm'])) {
			$model->attributes = $_POST['PayReqForm'];
			$docman = new DocMan($doctype,$model->id,get_class($model));
			$docman->masterId = $model->docMasterId[strtolower($doctype)];
			$docman->fileRemove($model->removeFileId[strtolower($doctype)]);
			echo $docman->genTableFileList(false);
		} else {
			echo "NIL";
		}
	}
	
	public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
		$sql = "select city from acc_request where id = $docId";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$citylist = Yii::app()->user->city_allow();
			if (strpos($citylist, $row['city']) !== false) {
				$docman = new DocMan($doctype,$docId,'PayReqForm');
				$docman->masterId = $mastId;
				$docman->fileDownload($fileId);
			} else {
				throw new CHttpException(404,'Access right not match.');
			}
		} else {
				throw new CHttpException(404,'Record not found.');
		}
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='request-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
	
	public static function allowReadWrite() {
		return Yii::app()->user->validRWFunction('XA04');
	}
	
	public static function allowReadOnly() {
		return Yii::app()->user->validFunction('XA04');
	}
	
	protected function checkCashAudit() {
		$city = Yii::app()->user->city();

		$sql = "select acct_id from acc_trans_type_def where trans_type_code='CASHIN' and city='$city'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		$acct_id = ($row===false) ? 2 : $row['acct_id'];

		$sql = "select a.trans_dt 
				from acc_trans a
				left outer join acc_trans_audit_dtl x on a.id=x.trans_id 
				where x.trans_id is null and a.acct_id=$acct_id and a.city='$city' 
				and a.status <> 'V' 
				order by a.trans_dt
				limit 1
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row===false) return true;
		
		$date = General::toDate($row['trans_dt']);
		$today = date("Y/m/d");
		if ($date >= $today) return true;
		
		$sql = "select (a.audit_dt + interval 1 day) as calc_dt
				from acc_trans_audit_hdr a
				where a.acct_id=$acct_id and a.city='$city' 
				order by a.audit_dt desc 
				limit 1
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row===false) return false;
		
		$date = General::toDate($row['calc_dt']);
		if ($date >= $today) return true;
		
		return false;
	}
	
	protected function checkT3Audit() {
		$city = Yii::app()->user->city();
		$day = date('d');
		if ($day > 10) {
			$end_dt = strtotime("last day of previous month");
			$year = date('Y',$end_dt);
			$month = date('m',$end_dt);
			$sql = "select id from acc_t3_audit_hdr where city='$city' and audit_year=$year and audit_month=$month and audit_dt is not null";
		} else {
			$dt1 = strtotime('-1 day',strtotime("first day of previous month"));
			$dt2 = strtotime("last day of previous month");
			$year1 = date('Y',$dt1);
			$month1 = date('m',$dt1);
			$year2 = date('Y',$dt2);
			$month2 = date('m',$dt2);
			$sql = "select id from acc_t3_audit_hdr where city='$city' 
					and ((audit_year=$year1 and audit_month=$month1) or (audit_year=$year2 and audit_month=$month2)) 
					and audit_dt is not null limit 1
				";
		}
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		return ($row!==false);
	}

}
