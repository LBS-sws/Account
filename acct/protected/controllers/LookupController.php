<?php

class LookupController extends Controller
{
	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'enforceRegisteredStation',
			'enforceSessionExpiration', 
			'enforceNoConcurrentLogin',
			'accessControl', // perform access control for CRUD operations
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
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('company','supplier','staff','product','companyex','supplierex','staffex','productex','template',
						'account','accountex'
					),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Lists all models.
	 */
	public function actionCompany($search)
	{
//		$suffix = Yii::app()->params['envSuffix'];
		$suffix = '_w';
		$city = Yii::app()->user->city();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, concat(left(concat(code,space(8)),8),name) as value from swoper$suffix.swo_company
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and city='".$city."'";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15', 'multiple'=>true));
	}

	public function actionCompanyEx($search) {
//		$suffix = Yii::app()->params['envSuffix'];
		$suffix = '_w';
		$city = Yii::app()->user->city();
		$result = array();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, code, name, cont_name, cont_phone, address from swoper$suffix.swo_company
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and city='".$city."'";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['id'],
						'value'=>substr($record['code'].str_repeat(' ',8),0,8).$record['name'],
						'contact'=>trim($record['cont_name']).'/'.trim($record['cont_phone']),
						'address'=>$record['address'],
					);
			}
		}
		print json_encode($result);
	}
	
	public function actionSupplier($search)
	{
//		$suffix = Yii::app()->params['envSuffix'];
		$suffix = '_w';
		$city = Yii::app()->user->city();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, concat(left(concat(code,space(8)),8),name) as value from swoper$suffix.swo_supplier
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and city='".$city."'";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15', 'multiple'=>true));
	}

	public function actionSupplierEx($search) {
//		$suffix = Yii::app()->params['envSuffix'];
		$suffix = '_w';
		$city = Yii::app()->user->city();
		$result = array();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, code, name, cont_name, cont_phone, address from swoper$suffix.swo_supplier
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and city='".$city."'";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['id'],
						'value'=>substr($record['code'].str_repeat(' ',8),0,8).$record['name'],
						'contact'=>trim($record['cont_name']).'/'.trim($record['cont_phone']),
						'address'=>$record['address'],
					);
			}
		}
		print json_encode($result);
	}
	
	public function actionStaff($search)
	{
//		$suffix = Yii::app()->params['envSuffix'];
		$suffix = '_w';
		$city = Yii::app()->user->city();
		$searchx = str_replace("'","\'",$search);

		$sql = "select id, concat(name, ' (', code, ')') as value from swoper$suffix.swo_staff
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and city='".$city."'
				and leave_dt is null or leave_dt=0 or leave_dt > now() ";
		$result1 = Yii::app()->db->createCommand($sql)->queryAll();

		$sql = "select id, concat(name, ' (', code, ')',' ".Yii::t('app','(Resign)')."') as value from swoper$suffix.swo_staff
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and city='".$city."'
				and  leave_dt is not null and leave_dt<>0 and leave_dt <= now() ";
		$result2 = Yii::app()->db->createCommand($sql)->queryAll();
		
		$result = array_merge($result1, $result2);
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15',));
	}

	public function actionStaffEx($search)
	{
//		$suffix = Yii::app()->params['envSuffix'];
		$suffix = '_w';
		$city = Yii::app()->user->city();
		$result = array();
		$searchx = str_replace("'","\'",$search);

		$sql = "select id, concat(name, ' (', code, ')') as value from swoper$suffix.swo_staff
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and city='".$city."'
				and (leave_dt is null or leave_dt=0 or leave_dt > now()) ";
		$result1 = Yii::app()->db->createCommand($sql)->queryAll();

		$sql = "select id, concat(name, ' (', code, ')',' ".Yii::t('app','(Resign)')."') as value from swoper$suffix.swo_staff
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and city='".$city."'
				and  leave_dt is not null and leave_dt<>0 and leave_dt <= now() ";
		$result2 = Yii::app()->db->createCommand($sql)->queryAll();
		
		$records = array_merge($result1, $result2);
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['id'],
						'value'=>$record['value'],
					);
			}
		}
		print json_encode($result);
	}

	public function actionProduct($search)
	{
		$city = '99999';	//Yii::app()->user->city();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, concat(left(concat(code,space(8)),8),description) as value from swo_product
				where (code like '%".$searchx."%' or description like '%".$searchx."%') and city='".$city."'";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15',));
	}

	public function actionProductEx($search)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = '99999';	//Yii::app()->user->city();
		$result = array();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, concat(left(concat(code,space(8)),8),description) as value from swoper_w.swo_product
				where (code like '%".$searchx."%' or description like '%".$searchx."%') and city='".$city."'";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['id'],
						'value'=>$record['value'],
					);
			}
		}
		print json_encode($result);
	}

	public function actionAccount($search)
	{
		$city = Yii::app()->user->city();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, concat(acct_name,'(',acct_no,')') as value from swo_product
				where (acct_no like '%".$searchx."%' or acct_name like '%".$searchx."%') and city='".$city."'";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15',));
	}

	public function actionAccountEx($search)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = Yii::app()->user->city();
		$result = array();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, concat(acct_name,'(',acct_no,')') as value from acc_account
				where (acct_name like '%".$searchx."%' or acct_no like '%".$searchx."%') and city='".$city."' or city='99999'
				and id <> 2";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['id'],
						'value'=>$record['value'],
					);
			}
		}
		print json_encode($result);
	}

	public function actionTemplate($system) {
		$result = array();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select temp_id, temp_name from security$suffix.sec_template
				where system_id='$system'
			";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['temp_id'],
						'name'=>$record['temp_name'],
					);
			}
		}
		print json_encode($result);
	}

//	public function actionSystemDate()
//	{
//		echo CHtml::tag( date('Y-m-d H:i:s'));
//		Yii::app()->end();
//	}
}
