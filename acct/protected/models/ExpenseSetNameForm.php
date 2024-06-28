<?php

class ExpenseSetNameForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $name;
	public $return_value;
	public static $type_str="expense";
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
            'name'=>Yii::t('give','Name'),
            'return_value'=>Yii::t('give','return city'),
            'z_display'=>Yii::t('give','display'),
            'z_index'=>Yii::t('give','z_index'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
            array('id,name,return_value,z_index,z_display','safe'),
			array('name,return_value','required'),
            array('z_index,z_display','numerical','allowEmpty'=>false,'integerOnly'=>true),
            array('id','validateID','on'=>array("delete")),
		);
	}

    public function validateID($attribute, $params) {
        $id = $this->$attribute;
        $row = Yii::app()->db->createCommand()->select("id")->from("acc_expense_info")
            ->where("name_id=:id",array(":id"=>$id))->queryRow();
        if($row){
            $this->addError($attribute, "这条记录已被使用无法删除");
            return false;
        }
    }

	public function retrieveData($index)
	{
		$suffix = Yii::app()->params['envSuffix'];
		$type_str = self::$type_str;
		$sql = "select * from acc_set_name where id='".$index."' and type_str='{$type_str}' ";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			$this->name = $row['name'];
			$this->return_value = $row['return_value'];
			$this->z_display = $row['z_display'];
			$this->z_index = $row['z_index'];
            return true;
		}else{
		    return false;
        }
	}

    public static function getExpenseSetNameList($id=0){
        $id = empty($id)?0:$id;
        $list = array();
        $type_str = self::$type_str;
        $rows = Yii::app()->db->createCommand()->select("*")->from("acc_set_name")
            ->where("z_display=1 and type_str='{$type_str}' or id=:id",array(":id"=>$id))->order("z_index asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["name"];
            }
        }
        return $list;
    }

    public static function getExpenseSetAllList(){
        $list = array();
        $type_str = self::$type_str;
        $rows = Yii::app()->db->createCommand()->select("*")->from("acc_set_name")
            ->where("id>0 and type_str='{$type_str}'")->order("z_index asc")->queryAll();
        if($rows){
            foreach ($rows as $row){
                $list[$row["id"]] = $row["name"];
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
        $type_str = self::$type_str;
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_set_name where id = :id AND type_str='{$type_str}'";
				break;
			case 'new':
				$sql = "insert into acc_set_name(
						name,type_str, return_value, z_index, z_display, lcu, lcd) values (
						:name,'{$type_str}', :return_value, :z_index, :z_display, :lcu, :lcd)";
				break;
			case 'edit':
				$sql = "update acc_set_name set 
					name = :name, 
					return_value = :return_value,
					z_index = :z_index,
					z_display = :z_display,
					luu = :luu
					where id = :id AND type_str='{$type_str}'";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':z_index')!==false)
			$command->bindParam(':z_index',$this->z_index,PDO::PARAM_INT);
		if (strpos($sql,':z_display')!==false)
			$command->bindParam(':z_display',$this->z_display,PDO::PARAM_INT);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$this->name,PDO::PARAM_STR);
		if (strpos($sql,':return_value')!==false)
			$command->bindParam(':return_value',$this->return_value,PDO::PARAM_STR);

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