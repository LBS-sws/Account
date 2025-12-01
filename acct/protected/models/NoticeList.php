<?php

class NoticeList extends CListPageModel
{
	public function attributeLabels()
	{
		return array(
			'flow_title'=>Yii::t('queue','Subject'),
			'lcd'=>Yii::t('queue','Date'),
			'ready_bool'=>Yii::t('queue','Status'),
			'note_type'=>Yii::t('queue','Type'),
			'id'=>Yii::t('queue','ID'),
			'system_id'=>"系统来源",
		);
	}
	
	public function retrieveDataByPage($pageNum=1)
	{
		$uid = Yii::app()->user->id;
		$sysid = Yii::app()->params['systemId'];
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "select *
				from swoper$suffix.swo_flow_info
				where username='$uid'
			";
		$sql2 = "select count(id)
				from swoper$suffix.swo_flow_info
				where username='$uid'
			";
		$clause = "";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
				case 'flow_title':
					$clause .= General::getSqlConditionClause('flow_title',$svalue);
					break;
				case 'note_dt':
					$clause .= General::getSqlConditionClause('lcd',$svalue);
					break;
				case 'ready_bool':
					$field = "(if(note_type=1,if(update_bool=1,'已执行','未执行'),if(ready_bool=1,'已读','未读'))) ";
					$clause .= General::getSqlConditionClause($field, $svalue);
					break;
			}
		}
		
		$order = "";
		if (!empty($this->orderField)) {
			switch ($this->orderField) {
				case 'note_dt':
					$order .= " order by lcd ";
					break;
				default: 
					$order .= " order by ".$this->orderField." ";
			}
			if ($this->orderType=='D') $order .= "desc ";
		} else {
			$order .= " order by id desc ";
		}

		$sql = $sql2.$clause;
		$this->totalRow = Yii::app()->db->createCommand($sql)->queryScalar();
		
		$sql = $sql1.$clause.$order;
		$sql = $this->sqlWithPageCriteria($sql, $this->pageNum);
		$records = Yii::app()->db->createCommand($sql)->queryAll();
		
		$list = array();
		$this->attr = array();
		if (count($records) > 0) {
			foreach ($records as $k=>$record) {
				$sts_name = self::getStsName($record['note_type'],$record['ready_bool'],$record['update_bool']);
				$this->attr[] = array(
					'id'=>$record['id'],
					'flow_title'=>$record['flow_title'],
					'lcd'=>$record['lcd'],
					'system_id'=>self::getSystemName($record['system_id']),
					'ready_bool'=>$sts_name,
					'note_type'=>$record['note_type']==1?Yii::t('queue','Action'):Yii::t('queue','Notify'),
				);
			}
		}
		$session = Yii::app()->session;
		$session[$this->criteriaName()] = $this->getCriteria();
		return true;
	}

	public static function getStsName($noteType,$readyBool,$updateBool){
	    $sts_name="";
	    if($noteType==1){//审核流程
            if($updateBool==1){
                $sts_name="已执行";
            }else{
                $sts_name.="未执行";
            }
        }else{
            if($readyBool==1){
                $sts_name.="已读";
            }else{
                $sts_name.="未读";
            }
        }
        return $sts_name;
    }

	public function criteriaName() {
		return Yii::app()->params['systemId'].'_criteria_z101';
	}
	
	public function markRead() {
		$uid = Yii::app()->user->id;
		$sysid = Yii::app()->params['systemId'];
		$suffix = Yii::app()->params['envSuffix'];
		$sql1 = "update swoper$suffix.swo_flow_info 
				set ready_bool=1, luu='$uid'
				where id>0
			";
		$clause = " and username='$uid' and ready_bool=0 and note_type=2 ";
		if (!empty($this->searchField) && !empty($this->searchValue)) {
			$svalue = str_replace("'","\'",$this->searchValue);
			switch ($this->searchField) {
                case 'flow_title':
                    $clause .= General::getSqlConditionClause('flow_title',$svalue);
                    break;
                case 'note_dt':
                    $clause .= General::getSqlConditionClause('lcd',$svalue);
                    break;
                case 'ready_bool':
                    $field = "(if(note_type=1,if(update_bool=1,'已执行','未执行'),if(ready_bool=1,'已读','未读'))) ";
                    $clause .= General::getSqlConditionClause($field, $svalue);
                    break;
			}
		}

        $flowInfoRows = Yii::app()->db->createCommand()->select("id,flow_id")
            ->from("swoper$suffix.swo_flow_info")->where("id>0 {$clause}")->queryAll();
        if($flowInfoRows){
            $connection = Yii::app()->db;
            $transaction=$connection->beginTransaction();
            $sql = $sql1.$clause;
            Yii::app()->db->createCommand($sql)->execute();

            $flowModel = new CNoticeFlowModel();
            $flowModel->finishNoticeList($flowInfoRows);
            $transaction->commit();
        }
	}

	public static function getSystemName($system_id){
        $config = Yii::app()->basePath.'/config/system.php';
        $menuitems = require($config);
        if(key_exists($system_id,$menuitems)){
            return Yii::t("app",$menuitems[$system_id]["name"]);
        }else{
            return $system_id;
        }
    }
}
