<?php
class ProductsrateForm extends CFormModel
{
	public $id = 0;
	public $city;
    public $copy=0;
	public $city_name;
	public $start_dt;
    public $product_sale;
	public $detail = array(
				array('id'=>0,
					'hdr_id'=>0,
					'operator'=>'',
					'sales_amount'=>0,
					'rate'=>0,
					'name'=>'',
					'uflag'=>'N',
				),
			);
			
	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'city'=>Yii::t('misc','City'),
			'city_name'=>Yii::t('misc','City'),
			'start_dt'=>Yii::t('service','Start Date'),
			'operator'=>Yii::t('service','Sign'),
			'sales_amount'=>Yii::t('service','Sales Amount'),
			'rate'=>Yii::t('service','Rate'),
			'name'=>Yii::t('service','Name'),
		);
	}

	public function rules()
	{
		return array(
			array('id, city_name','safe'),
			array('city, start_dt','required'),
			array('start_dt','date','allowEmpty'=>false,
				'format'=>array('MM/dd/yyyy','dd/MM/yyyy','yyyy/MM/dd',
							'MM-dd-yyyy','dd-MM-yyyy','yyyy-MM-dd',
							'M/d/yyyy','d/M/yyyy','yyyy/M/d',
							'M-d-yyyy','d-M-yyyy','yyyy-M-d',
							),
			),
			array('','validateDetailRecords'),
		);
	}

	public function validateDetailRecords($attribute, $params) {
		$rows = $this->$attribute;
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if ($row['uflag']=='Y') {
					if (!is_numeric($row['sales_amount'])) 
						$this->addError($attribute, Yii::t('service','Invalid amount').' '.$row['sales_amount']);
					if (!is_numeric($row['rate']))
						$this->addError($attribute, Yii::t('service','Invalid HY PC Rate').' '.$row['rate']);
					if (!is_numeric($row['name']))
						$this->addError($attribute, Yii::t('service','Invalid INV Rate').' '.$row['name']);
				}
			}
		}
	}
	
	public function retrieveData($index)
	{
		$city = Yii::app()->user->city_allow();
		$sql = "select * from acc_product_rate_hdr where id=$index and city in($city)";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->city = $row['city'];
			$this->start_dt = General::toDate($row['start_dt']);

			$sql = "select * from acc_product_rate_dtl where hdr_id=$index order by name desc, sales_amount";//operator
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				$this->detail = array();
				foreach ($rows as $row) {
					$temp = array();
					$temp['id'] = $row['id'];
					$temp['hdr_id'] = $row['hdr_id'];
					$temp['sales_amount'] = $row['sales_amount'];
					$temp['rate'] = $row['rate'];
					$temp['name'] = $row['name'];
					$temp['operator'] = $row['operator'];
					$temp['uflag'] = 'N';
					$this->detail[] = $temp;
				}
			}
			return true;
		} else {
			return false;
		}
		
	}
	
	public function saveData()
	{

		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveHeader($connection);
			$this->saveDetail($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.'.$e->getMessage());
		}
	}

	protected function saveHeader(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_product_rate_hdr where id = :id and city = :city";
				break;
			case 'new':
				$sql = "insert into acc_product_rate_hdr(
						name,start_dt, city, luu, lcu
						) values (
						:name,:start_dt, :city, :luu, :lcu
						)";
				if(isset($_POST['ProductsrateForm']['detail'][0]['name'])){
                    $name=$_POST['ProductsrateForm']['detail'][0]['name'];
                }else{
                    $name=$_POST['ProductsrateForm']['detail'][1]['name'];
                }

				break;
			case 'edit':
				$sql = "update acc_product_rate_hdr set  
                            name = :name,                     
							city = :city,
							start_dt = :start_dt,
							luu = :luu 
						where id = :id
						";
                if($_POST['ProductsrateForm']['detail'][0]['name']){
                    $name=$_POST['ProductsrateForm']['detail'][0]['name'];
                }else{
                    $name=$_POST['ProductsrateForm']['detail'][1]['name'];
                }
				break;
		}

//		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':start_dt')!==false) {
			$sdate = General::toMyDate($this->start_dt);
			$command->bindParam(':start_dt',$sdate,PDO::PARAM_STR);
		}
        if (strpos($sql,':name')!==false) {
            $command->bindParam(':name',$name,PDO::PARAM_STR);
        }
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$this->city,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}

	protected function saveDetail(&$connection)
	{
		$uid = Yii::app()->user->id;

		foreach ($_POST['ProductsrateForm']['detail'] as $row) {
		    if($_POST['ProductsrateForm']['copy']==1){
                $row['uflag']='Y';
                //print_r('<pre>');print_r($_POST['ProductsrateForm']['copy']);exit();
            }
			$sql = '';
			switch ($this->scenario) {
				case 'delete':
					$sql = "delete from acc_product_rate_dtl where hdr_id = :hdr_id";
					break;
				case 'new':
					if ($row['uflag']=='Y') {
						$sql = "insert into acc_product_rate_dtl(
									hdr_id, operator, sales_amount, rate, name,
									luu, lcu
								) values (
									:hdr_id, :operator, :sales_amount, :rate, :name,
									:luu, :lcu
								)";
					}
					break;
				case 'edit':
					switch ($row['uflag']) {
						case 'D':
							$sql = "delete from acc_product_rate_dtl where id = :id";
							break;
						case 'Y':
							$sql = ($row['id']==0)
									?
									"insert into acc_product_rate_dtl(
										hdr_id, operator, sales_amount, rate, name,
										luu, lcu
									) values (
										:hdr_id, :operator, :sales_amount, :rate, :name,
										:luu, :lcu
									)"
									: 
									"update acc_product_rate_dtl set
										hdr_id = :hdr_id,
										operator = :operator, 
										sales_amount = :sales_amount,
										name = :name,
										rate = :rate,
										luu = :luu 
									where id = :id
									";
							break;
					}
					break;
			}

			if ($sql != '') {
//                print_r('<pre>');
//                print_r($sql);exit();
				$command=$connection->createCommand($sql);
				if (strpos($sql,':id')!==false)
					$command->bindParam(':id',$row['id'],PDO::PARAM_INT);
				if (strpos($sql,':hdr_id')!==false)
					$command->bindParam(':hdr_id',$this->id,PDO::PARAM_INT);
				if (strpos($sql,':operator')!==false)
					$command->bindParam(':operator',$row['operator'],PDO::PARAM_STR);
				if (strpos($sql,':sales_amount')!==false) {
					$amt = $row['sales_amount'];
					$command->bindParam(':sales_amount',$amt,PDO::PARAM_STR);
				}
				if (strpos($sql,':rate')!==false) {
					$rate = General::toMyNumber($row['rate']);
					$command->bindParam(':rate',$rate,PDO::PARAM_STR);
				}
				if (strpos($sql,':name')!==false) {

					$command->bindParam(':name',$row['name'],PDO::PARAM_STR);
				}
				if (strpos($sql,':luu')!==false)
					$command->bindParam(':luu',$uid,PDO::PARAM_STR);
				if (strpos($sql,':lcu')!==false)
					$command->bindParam(':lcu',$uid,PDO::PARAM_STR);

				$command->execute();
			}

		}
		return true;
	}

	public function isProductSale(){
        $suffix = Yii::app()->params['envSuffix'];
        $city = Yii::app()->user->city();
        $sql="select id,description from swoper$suffix.swo_task where city='$city' and sales_products!='wu'";
        $records = Yii::app()->db->createCommand($sql)->queryAll();
        $arr=array(
            'paper'=>'纸品系列(默认)',
            'disinfectant'=>'消毒液及皂液系列(默认)',
            'purification'=>'空气净化系列(默认)',
            'chemical'=>'化学剂系列(默认)',
            'aromatherapy'=>'香熏系列(默认)',
            'pestcontrol'=>'虫控系列(默认)',
            'other'=>'其他系列(默认)',
        );
        foreach ($records as $a ){
            $arr[$a['id']]=$a['description'];
        }
      return $arr;
    }
	
	public function isReadOnly() {
		return ($this->scenario=='view');
	}
}
