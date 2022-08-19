<?php

class ConsultSetForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $good_name;
	public $z_index=0;
	public $z_display=1;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
            'good_name'=>Yii::t('consult','good name'),
            'z_index'=>Yii::t('consult','z_index'),
            'z_display'=>Yii::t('consult','display'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,good_name,z_index','safe'),
			array('good_name','required'),
            array('z_index,z_display','numerical','allowEmpty'=>false,'integerOnly'=>true),
            array('id','validateID','on'=>array("delete")),
		);
	}

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $row = Yii::app()->db->createCommand()->select("id")->from("acc_consult_info")
            ->where("set_id=:id",array(":id"=>$id))->queryRow();
        if($row){
            $this->addError($attribute, "这条记录已被使用无法删除");
            return false;
        }
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from acc_consult_set where id='".$index."'";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->good_name = $row['good_name'];
			$this->z_display = $row['z_display'];
			$this->z_index = $row['z_index'];
            return true;
		}else{
		    return false;
        }
	}

    public static function getConsultSetList($id=0){
        $id = empty($id)?0:$id;
        $list = array(""=>"");
        $rows = Yii::app()->db->createCommand()->select("*")->from("acc_consult_set")
            ->where("z_display=1 or id=:id",array(":id"=>$id))->order("z_index asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["good_name"];
            }
        }
        return $list;
    }
	
	public function saveData()
	{
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

	protected function saveDataForSql(&$connection)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_consult_set where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_consult_set(
						good_name, z_index, z_display, city, lcu, lcd) values (
						:good_name, :z_index, :z_display, :city, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update acc_consult_set set 
					good_name = :good_name, 
					z_index = :z_index,
					z_display = :z_display,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;
        $city = Yii::app()->user->city();

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':z_index')!==false)
			$command->bindParam(':z_index',$this->z_index,PDO::PARAM_INT);
		if (strpos($sql,':z_display')!==false)
			$command->bindParam(':z_display',$this->z_display,PDO::PARAM_INT);
		if (strpos($sql,':good_name')!==false)
			$command->bindParam(':good_name',$this->good_name,PDO::PARAM_STR);

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
}