<?php

class TemporaryApplyController extends Controller
{
    public $function_id='TA01';

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
                'actions'=>array('new','edit','delete','save','audit','fileupload','fileremove','print','ajaxPayee'),
                'expression'=>array('TemporaryApplyController','allowReadWrite'),
            ),
            array('allow',
                'actions'=>array('index','view','filedownload'),
                'expression'=>array('TemporaryApplyController','allowReadOnly'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionAjaxPayee($group='',$city='')
    {
        echo ExpenseFun::AjaxPayee($group,$city);
    }

    public function actionPrint($index)
    {
        $model = new TemporaryApplyForm;
        if (!$model->retrievePrint($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $model->printOne();
            Yii::app()->end();
        }
    }

    public function actionIndex($pageNum=0)
    {
        $model = new TemporaryApplyList();
        if (isset($_POST['TemporaryApplyList'])) {
            $model->attributes = $_POST['TemporaryApplyList'];
        } else {
            $session = Yii::app()->session;
            if (isset($session['temporaryApply_c01']) && !empty($session['temporaryApply_c01'])) {
                $criteria = $session['temporaryApply_c01'];
                $model->setCriteria($criteria);
            }
        }
        $model->determinePageNum($pageNum);
        $model->retrieveDataByPage($model->pageNum);
        $this->render('index',array('model'=>$model));
    }


    public function actionSave()
    {
        if (isset($_POST['TemporaryApplyForm'])) {
            $model = new TemporaryApplyForm($_POST['TemporaryApplyForm']['scenario']);
            $model->attributes = $_POST['TemporaryApplyForm'];
            $model->status_type=0;
            if ($model->validate()) {
                $model->saveData();
                $model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('temporaryApply/edit',array('index'=>$model->id)));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }

    public function actionAudit()
    {
        if (isset($_POST['TemporaryApplyForm'])) {
            $model = new TemporaryApplyForm($_POST['TemporaryApplyForm']['scenario']);
            $model->attributes = $_POST['TemporaryApplyForm'];
            $model->status_type=2;
            if ($model->validate()) {
                $model->saveData();
                $model->scenario = 'edit';
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Save Done'));
                $this->redirect(Yii::app()->createUrl('temporaryApply/edit',array('index'=>$model->id)));
            } else {
                $model->status_type=0;
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model,));
            }
        }
    }

    public function actionView($index)
    {
        $model = new TemporaryApplyForm('view');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionNew()
    {
        $model = new TemporaryApplyForm('new');
        ExpenseFun::setModelEmployee($model,"employee_id");
        $this->render('form',array('model'=>$model,));
    }

    public function actionEdit($index)
    {
        $model = new TemporaryApplyForm('edit');
        if (!$model->retrieveData($index)) {
            throw new CHttpException(404,'The requested page does not exist.');
        } else {
            $this->render('form',array('model'=>$model,));
        }
    }

    public function actionDelete()
    {
        $model = new TemporaryApplyForm('delete');
        if (isset($_POST['TemporaryApplyForm'])) {
            $model->attributes = $_POST['TemporaryApplyForm'];
            if ($model->validate()) {
                $model->saveData();
                Dialog::message(Yii::t('dialog','Information'), Yii::t('dialog','Record Deleted'));
                $this->redirect(Yii::app()->createUrl('temporaryApply/index'));
            } else {
                $message = CHtml::errorSummary($model);
                Dialog::message(Yii::t('dialog','Validation Message'), $message);
                $this->render('form',array('model'=>$model));
            }
        }
    }

    public function actionFileupload($doctype) {
        $model = new TemporaryApplyForm();
        if (isset($_POST['TemporaryApplyForm'])) {
            $model->attributes = $_POST['TemporaryApplyForm'];

            $id = ($_POST['TemporaryApplyForm']['scenario']=='new') ? 0 : $model->id;
            $docman = new DocMan($model->docType,$id,get_class($model));
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            if (isset($_FILES[$docman->inputName])) $docman->files = $_FILES[$docman->inputName];
            $docman->fileUpload();
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileRemove($doctype) {
        $model = new TemporaryApplyForm();
        if (isset($_POST['TemporaryApplyForm'])) {
            $model->attributes = $_POST['TemporaryApplyForm'];

            $docman = new DocMan($model->docType,$model->id,'TemporaryApplyForm');
            $docman->masterId = $model->docMasterId[strtolower($doctype)];
            $docman->fileRemove($model->removeFileId[strtolower($doctype)]);
            echo $docman->genTableFileList(false);
        } else {
            echo "NIL";
        }
    }

    public function actionFileDownload($mastId, $docId, $fileId, $doctype) {
        $sql = "select id from acc_expense where id = $docId";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if ($row!==false) {
            $docman = new DocMan($doctype,$docId,'TemporaryApplyForm');
            $docman->masterId = $mastId;
            $docman->fileDownload($fileId);
        } else {
            throw new CHttpException(404,'Record not found.');
        }
    }

    public static function allowReadWrite() {
        return Yii::app()->user->validRWFunction('TA01');
    }

    public static function allowReadOnly() {
        return Yii::app()->user->validFunction('TA01');
    }
}
