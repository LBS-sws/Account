<?php

class PlaneSetOtherForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $set_name;
	public $z_display=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels(){
		return array(
            'set_name'=>Yii::t('plane','Other Name'),
            'z_display'=>Yii::t('plane','display'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules(){
		return array(
            array('id,set_name,z_display','safe'),
			array('z_display,set_name','required'),
            //array('z_index,display','numerical','allowEmpty'=>false,'integerOnly'=>true),
            array('id','validateID','on'=>array("delete")),
		);
	}

    public function validateID($attribute, $params){
        $row = Yii::app()->db->createCommand()->select("id")->from("acc_plane_info")
            ->where("other_id=:id",array(":id"=>$this->id))->queryRow();
        if($row){
            $this->addError($attribute, "该杂项已被使用，无法删除");
        }
    }

	public function retrieveData($index){
        $city = Yii::app()->user->city();
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from acc_plane_set_other where id='".$index."' and city='$city'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->set_name = $row['set_name'];
			$this->z_display = $row['z_display'];
            return true;
		}else{
		    return false;
        }
	}

    public static function getPlaneOtherList($id){
        $city = Yii::app()->user->city();
        $list = array(""=>"");
        $rows = Yii::app()->db->createCommand()->select("id,set_name")->from("acc_plane_set_other")
            ->where("(z_display=1 and city=:city) or id=:id",array(":id"=>$id,":city"=>$city))->order("id desc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["set_name"];
            }
        }
        return $list;
    }
	
	public function saveData(){
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveDataForSql($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
		    var_dump($e);
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveDataForSql(&$connection){
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_plane_set_other where id = :id and city=:city";
				break;
			case 'new':
				$sql = "insert into acc_plane_set_other(
						set_name,z_display, city, lcu, lcd) values (
						:set_name, :z_display, :city, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update acc_plane_set_other set 
					set_name = :set_name, 
					z_display = :z_display,
					luu = :luu
					where id = :id and city=:city";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':set_name')!==false)
			$command->bindParam(':set_name',$this->set_name,PDO::PARAM_STR);
		if (strpos($sql,':z_display')!==false)
			$command->bindParam(':z_display',$this->z_display,PDO::PARAM_STR);
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