<?php
class PerformanceSetForm extends CFormModel
{
	public $id = 0;
	public $city;
    public $copy=0;
    public $name;
	public $city_name;
	public $start_dt;
    public $product_sale;
	public $detail = array(
				array('id'=>0,
					'hdr_id'=>0,
					'operator'=>'',
					'new_amount'=>0,
					'bonus_amount'=>0,
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
			'new_amount'=>Yii::t('service','new amount'),
			'bonus_amount'=>Yii::t('service','bonus amount'),
			'name'=>Yii::t('service','Setting Name'),
		);
	}

	public function rules()
	{
		return array(
			array('id','safe'),
			array('name,start_dt','required'),
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
					if (!is_numeric($row['bonus_amount']))
						$this->addError($attribute, Yii::t('service','Invalid amount').' '.$row['bonus_amount']);
					if (!is_numeric($row['new_amount']))
						$this->addError($attribute, Yii::t('service','Invalid HY PC Rate').' '.$row['new_amount']);
					if (!is_numeric($row['name']))
						$this->addError($attribute, Yii::t('service','Invalid INV Rate').' '.$row['name']);
				}
			}
		}
	}
	
	public function retrieveData($index)
	{
		$city = Yii::app()->user->city_allow();
		$sql = "select * from acc_performance_set where id=$index";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->city = $row['city'];
			$this->name = $row['name'];
			$this->start_dt = General::toDate($row['start_dt']);

			$sql = "select * from acc_performance_dtl where hdr_id=$index order by name desc,operator desc, new_amount asc";//operator
			$rows = Yii::app()->db->createCommand($sql)->queryAll();
			if (count($rows) > 0) {
				$this->detail = array();
				foreach ($rows as $row) {
					$temp = array();
					$temp['id'] = $row['id'];
					$temp['hdr_id'] = $row['hdr_id'];
					$temp['new_amount'] = floatval($row['new_amount']);
					$temp['bonus_amount'] = floatval($row['bonus_amount']);
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

	public static function getBonusArrForYearMonth($year,$month){
        $start_dt = date("Y-m-01",strtotime("{$year}-{$month}-01"));
	    $arr=array("LE"=>array(),"GT"=>array());
        $sql = "select id,name from acc_performance_set where start_dt<='{$start_dt}' ORDER BY start_dt desc";
        $row = Yii::app()->db->createCommand($sql)->queryRow();
        if($row){
            $sql = "select * from acc_performance_dtl where hdr_id={$row['id']} AND name='per_now_money' order by operator desc, new_amount asc";//operator
            $rows = Yii::app()->db->createCommand($sql)->queryAll();
            if($rows){
                foreach ($rows as $item){
                    $temp = array(
                        "new_amount"=>floatval($item['new_amount']),
                        "bonus_amount"=>floatval($item['bonus_amount']),
                    );
                    if($item["operator"]=="LE"){
                        $arr["LE"][] = $temp;
                    }else{
                        $arr["GT"][] = $temp;
                    }
                }
            }
        }
	    return $arr;
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
				$sql = "delete from acc_performance_set where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_performance_set(
						name,start_dt, city, luu, lcu
						) values (
						:name,:start_dt, :city, :luu, :lcu
						)";
				break;
			case 'edit':
				$sql = "update acc_performance_set set  
                            name = :name,  
							start_dt = :start_dt,
							luu = :luu 
						where id = :id
						";
				break;
		}

		$city = Yii::app()->user->city();
		$uid = Yii::app()->user->id;
		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':start_dt')!==false) {
			$sdate = General::toMyDate($this->start_dt);
			$command->bindParam(':start_dt',$sdate,PDO::PARAM_STR);
		}
        if (strpos($sql,':name')!==false) {
            $command->bindParam(':name',$this->name,PDO::PARAM_STR);
        }
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);
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

		foreach ($_POST['PerformanceSetForm']['detail'] as $row) {
		    if($_POST['PerformanceSetForm']['copy']==1){
                $row['uflag']='Y';
                //print_r('<pre>');print_r($_POST['PerformanceSetForm']['copy']);exit();
            }
			$sql = '';
			switch ($this->scenario) {
				case 'delete':
					$sql = "delete from acc_performance_dtl where hdr_id = :hdr_id";
					break;
				case 'new':
					if ($row['uflag']=='Y') {
						$sql = "insert into acc_performance_dtl(
									hdr_id, operator, new_amount, bonus_amount, name,
									luu, lcu
								) values (
									:hdr_id, :operator, :new_amount, :bonus_amount, :name,
									:luu, :lcu
								)";
					}
					break;
				case 'edit':
					switch ($row['uflag']) {
						case 'D':
							$sql = "delete from acc_performance_dtl where id = :id";
							break;
						case 'Y':
							$sql = ($row['id']==0)
									?
									"insert into acc_performance_dtl(
										hdr_id, operator, new_amount, bonus_amount, name,
										luu, lcu
									) values (
										:hdr_id, :operator, :new_amount, :bonus_amount, :name,
										:luu, :lcu
									)"
									: 
									"update acc_performance_dtl set
										hdr_id = :hdr_id,
										operator = :operator, 
										new_amount = :new_amount,
										name = :name,
										bonus_amount = :bonus_amount,
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
				if (strpos($sql,':new_amount')!==false) {
                    $amt = empty($row['new_amount'])?0:$row['new_amount'];
					$command->bindParam(':new_amount',$amt,PDO::PARAM_STR);
				}
				if (strpos($sql,':bonus_amount')!==false) {
					$rate = empty($row['bonus_amount'])?0:$row['bonus_amount'];
					$command->bindParam(':bonus_amount',$rate,PDO::PARAM_STR);
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

	public function getPerformanceList(){
        $arr=array(
            'per_now_money'=>'季度新签合同金额'
        );
      return $arr;
    }
	
	public function isReadOnly() {
		return ($this->scenario=='view');
	}
}
