<?php
class IDLadderForm extends CFormModel
{
	public $id = 0;
	public $name;
	public $only_num=0;
	public $city;
	public $city_name;
	public $start_dt;
	public $detail = array(
				array('id'=>0,
					'hdr_id'=>0,
					'operator'=>'',
					'month_num'=>0,
					'rate'=>0,
					'type_id'=>0,
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
			'month_num'=>Yii::t('app','Ctrt_period'),
			'rate'=>Yii::t('service','Rate'),
			'type_id'=>Yii::t('service','Name'),
			'name'=>Yii::t('service','Name'),
            'only_num'=>Yii::t('service','judge general'),
		);
	}

	public function rules()
	{
		return array(
			array('id, name, city_name, only_num, detail','safe'),
			array('city, name','required'),
            /*
			array('start_dt','date','allowEmpty'=>false,
				'format'=>array('MM/dd/yyyy','dd/MM/yyyy','yyyy/MM/dd',
							'MM-dd-yyyy','dd-MM-yyyy','yyyy-MM-dd',
							'M/d/yyyy','d/M/yyyy','yyyy/M/d',
							'M-d-yyyy','d-M-yyyy','yyyy-M-d',
							),
			),
            */
			array('detail','validateDetailRecords'),
            array('only_num','validateFinish'),
		);
	}

	public function validateFinish($attribute, $params) {
        if($this->only_num == 1){
            Yii::app()->db->createCommand()->update("acc_serviceid_rate_hdr",array("only_num"=>0),"id>0");
        }
    }

	public function validateDetailRecords($attribute, $params) {
		$rows = $this->$attribute;
		if (is_array($rows)) {
			foreach ($rows as $row) {
				if ($row['uflag']=='Y') {
					if (!is_numeric($row['month_num']))
						$this->addError($attribute, Yii::t('service','Invalid amount').' '.$row['month_num']);
					if (!is_numeric($row['rate']))
						$this->addError($attribute, Yii::t('service','Invalid HY PC Rate').' '.$row['rate']);
					if (!is_numeric($row['type_id']))
						$this->addError($attribute, Yii::t('service','Invalid INV Rate').' '.$row['type_id']);
				}
			}
		}
	}

	public static function getIDServiceTypeList(){
        $suffix = Yii::app()->params['envSuffix'];
        $arr = array(""=>"请选择客户类别");
        $rows = Yii::app()->db->createCommand()->select("id,cust_type_name")->from("swoper{$suffix}.swo_customer_type_info")
            ->where("index_num=2")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $arr[$row["id"]] = $row["cust_type_name"];
            }
        }
        return $arr;
    }
	
	public function retrieveData($index)
	{
		$city = Yii::app()->user->city_allow();
		$sql = "select * from acc_serviceid_rate_hdr where id=$index and city in($city)";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->only_num = $row['only_num'];
			$this->city = $row['city'];
			$this->start_dt = General::toDate($row['start_dt']);

			$sql = "select * from acc_serviceid_rate_dtl where hdr_id=$index order by operator desc, month_num";
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				$this->detail = array();
				foreach ($rows as $row) {
					$temp = array();
					$temp['id'] = $row['id'];
					$temp['hdr_id'] = $row['hdr_id'];
					$temp['month_num'] = $row['month_num'];
					$temp['rate'] = $row['rate'];
					$temp['type_id'] = $row['type_id'];
					$temp['operator'] = $row['operator'];
					$temp['uflag'] = $this->getScenario()=="new"?"Y":"N";
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
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveHeader(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_serviceid_rate_hdr where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_serviceid_rate_hdr(
						name,start_dt, only_num, city, luu, lcu
						) values (
						:name,:start_dt, :only_num, :city, :luu, :lcu
						)";
				break;
			case 'edit':
				$sql = "update acc_serviceid_rate_hdr set  
                            name = :name,                     
							only_num = :only_num,
							city = :city,
							luu = :luu 
						where id = :id
						";
				break;
		}

//		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':only_num')!==false)
			$command->bindParam(':only_num',$this->only_num,PDO::PARAM_INT);
		if (strpos($sql,':start_dt')!==false) {
			$sdate = date("Y/m/d H:i:s");
			$command->bindParam(':start_dt',$sdate,PDO::PARAM_STR);
		}
        if (strpos($sql,':name')!==false) {
            $command->bindParam(':name',$this->name,PDO::PARAM_STR);
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

		foreach ($this->detail as $row) {
			$sql = '';
			switch ($this->scenario) {
				case 'delete':
					$sql = "delete from acc_serviceid_rate_dtl where hdr_id = :hdr_id";
					break;
				case 'new':
					if ($row['uflag']=='Y') {
						$sql = "insert into acc_serviceid_rate_dtl(
									hdr_id, operator, month_num, rate, type_id,
									luu, lcu
								) values (
									:hdr_id, :operator, :month_num, :rate, :type_id,
									:luu, :lcu
								)";
					}
					break;
				case 'edit':
					switch ($row['uflag']) {
						case 'D':
							$sql = "delete from acc_serviceid_rate_dtl where id = :id";
							break;
						case 'Y':
							$sql = ($row['id']==0)
									?
									"insert into acc_serviceid_rate_dtl(
										hdr_id, operator, month_num, rate, type_id,
										luu, lcu
									) values (
										:hdr_id, :operator, :month_num, :rate, :type_id,
										:luu, :lcu
									)"
									: 
									"update acc_serviceid_rate_dtl set
										hdr_id = :hdr_id,
										operator = :operator, 
										month_num = :month_num,
										rate = :rate,
										type_id = :type_id,
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
				if (strpos($sql,':month_num')!==false) {
					$amt = General::toMyNumber($row['month_num']);
					$command->bindParam(':month_num',$amt,PDO::PARAM_STR);
				}
				if (strpos($sql,':rate')!==false) {
					$rate1 = General::toMyNumber($row['rate']);
					$command->bindParam(':rate',$rate1,PDO::PARAM_STR);
				}
				if (strpos($sql,':type_id')!==false) {
					$command->bindParam(':type_id',$row['type_id'],PDO::PARAM_STR);
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
	
	public function isReadOnly() {
		return ($this->scenario=='view');
	}
}
