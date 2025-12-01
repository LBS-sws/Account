<?php

class NoticeForm extends CFormModel
{
	/* User Fields */
	public $id;
	public $note_type;
    public $flow_id;
    public $flow_title;
	public $flow_desc;
	public $flow_message;
	public $lcd;
	public $ready_bool;
	public $update_bool;
	public $pc_url;

	/**
	 * Declares customized attribute labels.
	 * If not declared here, an attribute would have a label that is
	 * the same as its name with the first letter in upper case.
	 */
	public function attributeLabels()
	{
		return array(
			'flow_desc'=>Yii::t('queue','Description'),
			'flow_message'=>Yii::t('queue','Message'),
			'flow_title'=>Yii::t('queue','Subject'),
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
			array('id, note_type, flow_title, flow_desc, flow_message, lcd, ready_bool','safe'),
		);
	}

	public function retrieveData($index)
	{
		$uid = Yii::app()->user->id;
		$sysid = Yii::app()->params['systemId'];
		$suffix = Yii::app()->params['envSuffix'];

		$sql = "select *
				from swoper$suffix.swo_flow_info 
				where username='$uid' and id=$index
			";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
            //$sts_name = $row['ready_bool']==1 ? Yii::t('queue','Read') : Yii::t('queue','Unread');
			$this->id = $row['id'];
            $this->note_type = $row['note_type'];
			$this->flow_id = $row['flow_id'];
			$this->flow_title = $row['flow_title'];
			$this->flow_desc = $row['flow_desc'];
			$this->flow_message = $row['flow_message'];
			$this->lcd = $row['lcd'];
			$this->ready_bool = $row['ready_bool'];
			$this->update_bool = $row['update_bool'];
			$this->pc_url = $row['pc_url'];

			if ($this->ready_bool != 1) {
                $this->ready_bool=1;
                $connection = Yii::app()->db;
                $transaction=$connection->beginTransaction();
				$sql = "update swoper$suffix.swo_flow_info set ready_bool=1, luu='$uid'
							where id=$index and username='$uid'
						";
				Yii::app()->db->createCommand($sql)->execute();
				if($this->note_type==2){ //通知类型
                    $this->sendMHData();//发送消息给门户网站，已阅
                }
                $transaction->commit();
			}
			
			return true;
		}
		return false;
	}

	protected function sendMHData(){
        $flowModel = new CNoticeFlowModel();
        $data = array(
            array("id"=>$this->id,"flow_id"=>$this->flow_id),
        );
        $flowModel->finishNoticeList($data);
    }

	public static function getNoticeAjax(){
        $rtn = array();
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];

        $sql = "select note_type, count(id) as num
				from swoper{$suffix}.swo_flow_info
				where username='$uid' and ((ready_bool=0 and note_type=2)or(update_bool=0 and note_type=1))
				group by note_type
			";
        $rows = Yii::app()->db->createCommand($sql)->queryAll();
        foreach ($rows as $row) {
            $rtn[] = array('type'=>$row['note_type'],'count'=>$row['num']);
        }
        return $rtn;
    }
}
