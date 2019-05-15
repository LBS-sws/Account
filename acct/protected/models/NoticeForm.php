<?php

class NoticeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $note_type;
	public $subject;
	public $description;
	public $message;
	public $lcd;
	public $status;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'description'=>Yii::t('queue','Description'),
			'message'=>Yii::t('queue','Message'),
			'subject'=>Yii::t('queue','Subject'),
			'lcd'=>Yii::t('queue','Date'),
			'note_type'=>Yii::t('queue','Type'),
		);
	}

	/**
	 * Declares the validation rules.
	 */
	public function rules()
	{
		return array(
			array('id, note_type, subject, description, message, lcd, status','safe'), 
		);
	}

	public function retrieveData($index)
	{
		$uid = Yii::app()->user->id;
		$sysid = Yii::app()->params['systemId'];
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;

		$sql = "select a.*, b.status
				from swoper$suffix.swo_notification a, swoper$suffix.swo_notification_user b 
				where b.username='$uid' and a.system_id='$sysid'
				and a.id=b.note_id and a.id=$index
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			$this->id = $row['id'];
			switch ($row['note_type']) {
				case 'NOTI':
					$this->note_type = Yii::t('queue','Notify');
					break;
				case 'ACTN':
					$this->note_type = Yii::t('queue','Action');
					break;
				default: 
					$this->note_type = $row['note_type'];
			}
			$this->subject = $row['subject'];
			$this->description = $row['description'];
			$this->message = $row['message'];
			$this->lcd = $row['lcd'];
			$this->status = $row['status'];
			
			if ($this->status != 'C') {
				$sql = "update swoper$suffix.swo_notification_user set status='C', luu='$uid'
							where note_id=$index and username='$uid'
						";
				Yii::app()->db->createCommand($sql)->execute();
			}
			
			return true;
		}
		return false;
	}
}
