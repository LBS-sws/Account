<?php

class LookupController extends Controller
{
	public $interactive = false;

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
						'account','accountex','accountitemin','accountiteminex','accountitemout','accountitemoutex'
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
	public function actionCompany($search, $incity='')
	{
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		$city = empty($incity) ? Yii::app()->user->city() : $incity;
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, concat(left(concat(code,space(8)),8),if(full_name is null or full_name='',name,full_name)) as value from swoper$suffix.swo_company
				where (code like '%$searchx%' or name like '$searchx%') and city='$city'";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15', 'multiple'=>true));
	}

	public function actionCompanyEx($search, $incity='') {
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		$city = empty($incity) ? Yii::app()->user->city() : $incity;
		$result = array();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, code, name, full_name, cont_name, cont_phone, address from swoper$suffix.swo_company
				where (code like '%$searchx%' or name like '%$searchx%') and city='$city'";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['id'],
						'value'=>substr($record['code'].str_repeat(' ',8),0,8).(empty($record['full_name'])?$record['name']:$record['full_name']),
						'contact'=>trim($record['cont_name']).'/'.trim($record['cont_phone']),
						'address'=>$record['address'],
					);
			}
		}
		print json_encode($result);
	}
	
	public function actionSupplier($search, $incity='')
	{
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		$city = empty($incity) ? Yii::app()->user->city() : $incity;
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, concat(left(concat(code,space(8)),8),if(full_name is null or full_name='',name,full_name)) as value from swoper$suffix.swo_supplier
				where (code like '%$searchx%' or name like '$searchx%') and city='$city'";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15', 'multiple'=>true));
	}

	public function actionSupplierEx($search, $incity='') {
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		$city = empty($incity) ? Yii::app()->user->city() : $incity;
		$result = array();
		$searchx = str_replace("'","\'",$search);
		$sql = "select id, code, name, full_name, cont_name, cont_phone, address from swoper$suffix.swo_supplier
				where (code like '%$searchx%' or name like '%$searchx%') and city='$city'";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['id'],
						'value'=>substr($record['code'].str_repeat(' ',8),0,8).(empty($record['full_name'])?$record['name']:$record['full_name']),
						'contact'=>trim($record['cont_name']).'/'.trim($record['cont_phone']),
						'address'=>$record['address'],
					);
			}
		}
		print json_encode($result);
	}
	
	public function actionStaff($search, $incity='')
	{
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		$city = empty($incity) ? Yii::app()->user->city() : $incity;
		$searchx = str_replace("'","\'",$search);

		$sql = "select id, concat(name, ' (', code, ')') as value from swoper$suffix.swo_staff_v
				where (code like '%$searchx%' or name like '%$searchx%') and city='$city'
				and leave_dt is null or leave_dt=0 or leave_dt > now() ";
		$result1 = Yii::app()->db->createCommand($sql)->queryAll();

		$sql = "select id, concat(name, ' (', code, ')',' ".Yii::t('app','(Resign)')."') as value from swoper$suffix.swo_staff_v
				where (code like '%$searchx%' or name like '%$searchx%') and city='$city'
				and  leave_dt is not null and leave_dt<>0 and leave_dt <= now() ";
		$result2 = Yii::app()->db->createCommand($sql)->queryAll();
		
		$result = array_merge($result1, $result2);
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15',));
	}

	public function actionStaffEx($search, $incity='')
	{
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		$city = empty($incity) ? Yii::app()->user->city() : $incity;
		$result = array();
		$searchx = str_replace("'","\'",$search);

		$sql = "select id, concat(name, ' (', code, ')') as value from swoper$suffix.swo_staff_v
				where (code like '%$searchx%' or name like '%$searchx%') and city='$city'
				and (leave_dt is null or leave_dt=0 or leave_dt > now()) ";
		$result1 = Yii::app()->db->createCommand($sql)->queryAll();

		$sql = "select id, concat(name, ' (', code, ')',' ".Yii::t('app','(Resign)')."') as value from swoper$suffix.swo_staff_v
				where (code like '%$searchx%' or name like '%$searchx%') and city='$city'
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

	public function actionAccount($search, $incity='')
	{
		$city = empty($incity) ? Yii::app()->user->city() : $incity;
		$searchx = str_replace("'","\'",$search);
		$sql = "select a.id, concat(b.acct_type_desc,' - ',a.acct_name,'(',a.acct_no,')') as value 
				from acc_account a, acc_account_type b 
				where a.acct_type_id=b.id and (a.acct_no like '%$searchx%' or a.acct_name like '%$searchx%') and a.city='$city' or a.city='99999'
				and a.id <> 2";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$data = TbHtml::listData($result, 'id', 'value');
		echo TbHtml::listBox('lstlookup', '', $data, array('size'=>'15',));
	}

	public function actionAccountEx($search, $incity='')
	{
		$suffix = Yii::app()->params['envSuffix'];
		$city = empty($incity) ? Yii::app()->user->city() : $incity;
		$result = array();
		$searchx = str_replace("'","\'",$search);
		$sql = "select a.id, concat(b.acct_type_desc,' - ',a.acct_name,'(',a.acct_no,')') as value 
				from acc_account a, acc_account_type b 
				where a.acct_type_id=b.id and (a.acct_no like '%$searchx%' or a.acct_name like '%$searchx%') and a.city='$city' or a.city='99999'
				and a.id <> 2";
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

	public function actionAccountItemIn($search) {
		echo $this->searchAccountItem($search, 'I');
	}

	public function actionAccountItemOut($search) {
		echo $this->searchAccountItem($search, 'O');
	}

	protected function searchAccountItem($search, $type) {
		$searchx = str_replace("'","\'",$search);
		$sql = "select code, concat(name,' (',code,')') as value from acc_account_item
				where (code like '%".$searchx."%' or name like '%".$searchx."%') and type in ('$type', 'B')";
		$result = Yii::app()->db->createCommand($sql)->queryAll();
		$data = TbHtml::listData($result, 'code', 'value');
		return TbHtml::listBox('lstlookup', '', $data, array('size'=>'15',));
	}
	
	public function actionAccountItemInEx($search, $acctid=0) {
		$type = 'ZZZ';
		if ($acctid != 0) {
			$sql = "select b.rpt_cat from acc_account a, acc_account_type b
					where a.acct_type_id = b.id and a.id = $acctid
				";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			if ($row!==false) $type = $row['rpt_cat']=='CASH' ? 'CI' : 'BI';
		}
		echo $this->searchAccountItemEx($search, $type);
	}

	public function actionAccountItemOutEx($search, $acctid=0) {
		$type = 'ZZZ';
		if ($acctid != 0) {
			$sql = "select b.rpt_cat from acc_account a, acc_account_type b
					where a.acct_type_id = b.id and a.id = $acctid
				";
			$row = Yii::app()->db->createCommand($sql)->queryRow();
			if ($row!==false) $type = $row['rpt_cat']=='CASH' ? 'CO' : 'BO';
		}
		echo $this->searchAccountItemEx($search, $type);
	}

	protected function searchAccountItemEx($search, $type)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$result = array();
		$itemtype = "'".$type."'"; //$type=='I' ? "'BI','CI'" : "'BO','CO'";
		$searchx = str_replace("'","\'",$search);
		$sql = "select a.code, concat(a.name,' (',a.code,')') as value, a.acct_code, b.name as acct_code_desc 
				from acc_account_item a left outer join acc_account_code b on a.acct_code=b.code 
				where (a.code like '%$searchx%' or a.name like '%$searchx%') and a.item_type in ($itemtype)";
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$result[] = array(
						'id'=>$record['code'],
						'value'=>$record['value'],
						'acctcode'=>$record['acct_code'],
						'acctcodedesc'=>$record['acct_code'].' '.$record['acct_code_desc'],
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
