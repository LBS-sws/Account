<?php

class WebSqlController extends Controller{
    public $interactive = false;
	
	public function filters(){
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
	public function accessRules(){
		return array(
            array('allow',  // allow all users to perform 'index' and 'view' actions
                'actions'=>array('index','test','all'
                ),
                'users'=>array('@'),
            ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex(){
        echo "start<br/>";
	    $model = new WebSqlForm('view');
        $model->validateTableForDate();
        echo "<br/> end !";
	    die();
	}

	public function actionAll(){
        echo "start<br/>";
	    $model = new WebSqlForm('view');
        $model->validateTableAll();
        echo "<br/> end !";
	    die();
	}

	public function actionTest(){
	}

}
