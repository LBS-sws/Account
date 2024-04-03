<?php

class InvoiceEmailForm extends CFormModel
{
	public $id;
	public $start_dt;
	public $email_text;
	public $remarks;
	
	public function attributeLabels()
	{
        return array(
            'start_dt'=>Yii::t('code','effect date'),
            'email_text'=>Yii::t('queue','Email'),
            'remarks'=>Yii::t('code','Remarks'),
        );
	}

	public function rules()
	{
		return array(
			array('start_dt, email_text','required'),
			array('start_dt','validateDate'),
			array('id, start_dt, email_text, remarks','safe'),
		);
	}

	public function validateDate($attribute, $params) {
        $dateObj = date_create($this->start_dt);
        if($dateObj===false){
            $this->addError($attribute, "时间格式异常");
        }else{
            $id = empty($this->id)?0:$this->id;
            $date = date_format($dateObj,"Y-m-d");
            $row = Yii::app()->db->createCommand()->select("id")->from("acc_invoice_email")
                ->where("id!=:id and start_dt=:dt",array(":id"=>$id,":dt"=>$date))
                ->queryRow();
            if($row){
                $this->addError($attribute, $date." 已存在同时段配置：".$row["id"]);
            }
        }
	}
	
	public function retrieveData($index)
	{
        $row = Yii::app()->db->createCommand()->select("*")->from("acc_invoice_email")
            ->where("id=:id",array(":id"=>$index))
            ->queryRow();
		if ($row){
            $this->id = $row['id'];
            $this->start_dt = General::toDate($row['start_dt']);
            $this->email_text = $row['email_text'];
            $this->remarks = $row['remarks'];
		}
		return true;
	}
	
	public function saveData()
	{
		$connection = Yii::app()->db;
		$transaction=$connection->beginTransaction();
		try {
			$this->saveInvoiceEmail($connection);
			$transaction->commit();
		}
		catch(Exception $e) {
			$transaction->rollback();
			throw new CHttpException(404,'Cannot update.');
		}
	}

	protected function saveInvoiceEmail(&$connection)
	{
		$sql = '';
		switch ($this->scenario) {
			case 'delete':
				$sql = "delete from acc_invoice_email where id = :id";
				break;
			case 'new':
				$sql = "insert into acc_invoice_email(
						start_dt, email_text, remarks, lcu) values (
						:start_dt, :email_text, :remarks, :lcu)";
				break;
			case 'edit':
				$sql = "update acc_invoice_email set 
					start_dt = :start_dt, 
					email_text = :email_text,
					remarks = :remarks,
					luu = :luu
					where id = :id";
				break;
		}

		$uid = Yii::app()->user->id;

		$command=$connection->createCommand($sql);
		if (strpos($sql,':id')!==false)
			$command->bindParam(':id',$this->id,PDO::PARAM_INT);
		if (strpos($sql,':start_dt')!==false)
			$command->bindParam(':start_dt',$this->start_dt,PDO::PARAM_STR);
		if (strpos($sql,':email_text')!==false)
			$command->bindParam(':email_text',$this->email_text,PDO::PARAM_STR);
		if (strpos($sql,':remarks')!==false)
			$command->bindParam(':remarks',$this->remarks,PDO::PARAM_STR);

		if (strpos($sql,':luu')!==false)
			$command->bindParam(':luu',$uid,PDO::PARAM_STR);
		if (strpos($sql,':lcu')!==false)
			$command->bindParam(':lcu',$uid,PDO::PARAM_STR);
		$command->execute();

		if ($this->scenario=='new')
			$this->id = Yii::app()->db->getLastInsertID();
		return true;
	}
	
	public function isOccupied($index) {
        $row = Yii::app()->db->createCommand()->select("*")->from("acc_invoice_email")
            ->where("id=:id",array(":id"=>$index))
            ->queryRow();
        if($row){
            return false;//允许删除
        }else{
            return true;
        }
	}
	
	public function isReadOnly() {
		return $this->scenario=='view';
	}
}
