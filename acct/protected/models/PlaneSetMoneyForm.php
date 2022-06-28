<?php

class PlaneSetMoneyForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $set_name;
	public $start_date;
    public $info_list = array(
        array('id'=>0,
            'money_id'=>0,
            'value_name'=>'',
            'value_money'=>'',
            'uflag'=>'Y',
        ),
    );

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels(){
		return array(
            'set_name'=>Yii::t('plane','Set Name'),
            'start_date'=>Yii::t('plane','start date'),
            'info_list'=>Yii::t('plane','Set Detail'),
            'value_name'=>Yii::t('plane','Set Money'),
            'value_money'=>Yii::t('plane','Plane Money'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules(){
		return array(
            array('id,set_name,start_date,info_list','safe'),
			array('start_date,set_name','required'),
            //array('z_index,display','numerical','allowEmpty'=>false,'integerOnly'=>true),
            //array('id','validateID','on'=>array("delete")),
            array('info_list','validateList','on'=>array("edit","new")),
		);
	}

    public function validateList($attribute, $params){
        $list = array();
        if(!empty($this->info_list)){
            foreach ($this->info_list as $row){
                if(!empty($row["value_name"])&&!empty($row["value_money"])){
                    $row["value_money"] = intval($row["value_money"]);
                    $list[]=$row;
                }
            }
        }
        if(empty($list)){
            $this->addError($attribute, "配置详情不能为空");
            return false;
        }else{
            $this->info_list = $list;
        }
    }

	public function retrieveData($index){
        $city = Yii::app()->user->city();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from acc_plane_set_money where id='".$index."' and city='{$city}'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->set_name = $row['set_name'];
			$this->start_date = General::toDate($row['start_date']);
            $this->info_list = array();
            $rows = Yii::app()->db->createCommand()->select("id,value_name,value_money")->from("acc_plane_set_money_info")
                ->where("money_id=:id",array(":id"=>$index))->order("value_name asc")->queryAll();
            if($rows){
                foreach ($rows as $arr){
                    $temp = array();
                    $temp['id'] = $arr['id'];
                    $temp['money_id'] = $index;
                    $temp['value_name'] = $arr['value_name'];
                    $temp['value_money'] = $arr['value_money'];
                    $temp['uflag'] = "Y";
                    $this->info_list[] = $temp;
                }
            }
            return true;
		}else{
		    return false;
        }
	}

    public static function getPlaneForDateAndMoney($date,$money,$city){
        $date = date("Y-m-d",strtotime($date));
        $plane = array("value"=>0,"id"=>0);
        $row = Yii::app()->db->createCommand()->select("id,set_name")->from("acc_plane_set_money")
            ->where("start_date<=:date and city=:city",array(":date"=>$date,":city"=>$city))->order("start_date desc")->queryRow();
        if($row){
            $list = Yii::app()->db->createCommand()->select("id,value_name,value_money")->from("acc_plane_set_money_info")
                ->where("money_id=:id",array(":id"=>$row["id"]))->order("value_name asc")->queryAll();
            $plane = General::getValueMoneyForAsc($list,$money);
        }

        return $plane;
    }
	
	public function saveData(){
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDataForSql($connection);
			$this->saveInfo($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveInfo(&$connection){

        $uid = Yii::app()->user->id;

        foreach ($this->info_list as $row) {
            $sql = '';
            switch ($this->scenario) {
                case 'delete':
                    $sql = "delete from acc_plane_set_money_info where money_id = :money_id";
                    break;
                case 'new':
                    if ($row['uflag']=='Y') {
                        $sql = "insert into acc_plane_set_money_info(
									money_id, value_name, value_money
								) values (
									:money_id, :value_name, :value_money
								)";
                    }
                    break;
                case 'edit':
                    switch ($row['uflag']) {
                        case 'D':
                            $sql = "delete from acc_plane_set_money_info where id = :id";
                            break;
                        case 'Y':
                            $sql = ($row['id']==0)
                                ?
                                "insert into acc_plane_set_money_info(
										money_id, value_name, value_money
									) values (
										:money_id, :value_name, :value_money
									)"
                                :
                                "update acc_plane_set_money_info set
										value_name = :value_name,
										value_money = :value_money
									where id = :id and money_id=:money_id
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
                if (strpos($sql,':money_id')!==false)
                    $command->bindParam(':money_id',$this->id,PDO::PARAM_INT);

                if (strpos($sql,':value_name')!==false) {
                    $value_name = General::toMyNumber($row['value_name']);
                    $command->bindParam(':value_name',$value_name,PDO::PARAM_STR);
                }
                if (strpos($sql,':value_money')!==false) {
                    $value_money = General::toMyNumber($row['value_money']);
                    $command->bindParam(':value_money',$value_money,PDO::PARAM_STR);
                }
                $command->execute();
            }
        }
    }

	protected function saveDataForSql(&$connection){
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_plane_set_money where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_plane_set_money(
						set_name,start_date, city, lcu, lcd) values (
						:set_name, :start_date, :city, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update acc_plane_set_money set 
					set_name = :set_name, 
					start_date = :start_date,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':set_name')!==false)
			$command->bindParam(':set_name',$this->set_name,PDO::PARAM_STR);
		if (strpos($sql,':start_date')!==false)
			$command->bindParam(':start_date',$this->start_date,PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$city,PDO::PARAM_STR);

		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcd')!==false){
            $date = date("Y-m-d H:i:s");
            $command->bindParam(':lcd',$date,PDO::PARAM_STR);
        }
		$command->execute();

        if ($this->scenario=='new')
            $this->id = Yii::app()->db->getLastInsertID();

		return true;
	}

	public function isReadOnly(){
	    return $this->getScenario()=='view';
    }
}