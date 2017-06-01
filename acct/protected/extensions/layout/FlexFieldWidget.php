<?php
class FlexFieldWidget extends CWidget
{
	public $model;
	public $attribute;
	public $gridsize;
	public $height='100';
	public $readonly = false;
	
	public function run()
	{
		$layout = "<div class='grid_".$this->gridsize."' style='height:".$this->height."px;overflow:auto'>";
		$rows=$this->attribute;
		if (count($this->model->$rows) > 0)
		{
			foreach ($this->model->$rows as $i=>$row)
			{
				$labelName = $row['field_name'];
			
				$layout .= "<div class='grid_3 omega alpha'>".CHtml::label($labelName, false)."</div>";
				$layout	.= "<div class='grid_9 omega alpha'>";
				$layout .= CHtml::hiddenField($this->getFieldName('uflag',$i), $row['uflag']);
				$layout .= CHtml::hiddenField($this->getFieldName('rec_id',$i), $row['rec_id']);
				$layout .= CHtml::hiddenField($this->getFieldName('field_id',$i), $row['field_id']);
				$layout .= $this->renderField($i, $row, $this->readonly);
/*
				if ($this->readonly)
					$layout .= CHtml::textField($this->getFieldName('field_value',$i), $row["field_value"], 
									array("readonly"=>true, "size"=>"50", "class"=>"readonly"));
				else
					$layout .= CHtml::textField($this->getFieldName('field_value',$i), $row['field_value'], 
									array(
										'size'=>'50',
										'ajax'=>array(
											'type'=>'GET',
											'url'=>Yii::app()->createUrl('ajax/dummy'),
											'success'=>'function() {
												if ($("#'.$this->getFieldId('uflag',$i).'").val() == "N")
													$("#'.$this->getFieldId('uflag',$i).'").val("U");
											}',
										),
									)
								);
*/				
				$layout .= "</div>";
				if ($i % 2 != 0)
					$layout .= "<div class='clear'></div>";
			}
		}
		$layout .= "</div>";
		echo $layout;
	}
	
	protected function renderField($index, $defn, $readonly=false)
	{
		$ajaxupt = array(
						'ajax'=>array(
								'type'=>'GET',
								'url'=>Yii::app()->createUrl('ajax/dummy'),
								'success'=>'function() {
								if ($("#'.$this->getFieldId('uflag',$index).'").val() == "N")
									$("#'.$this->getFieldId('uflag',$index).'").val("U");
								}',
						),
					);

		$rtn = '';
		switch ($defn['field_type'])
		{
			case 'L':
				if ($readonly)
					$rtn = CHtml::textField($this->getFieldName('field_value',$index), $defn['field_value'], 
							array("readonly"=>true, "size"=>"50", "class"=>"readonly"));
				else
				{
					$list = array();
					$data = explode('~',$defn['validation']);
					if (count($data) > 0)
					{
						foreach($data as $val)
						{
							$list[$val] = $val;
						}
					}
					$rtn = CHtml::dropDownList($this->getFieldName('field_value',$index), $defn['field_value'], $list,
							$ajaxupt);
				}
				break;
			case 'D':
				if ($readonly)
					$rtn = CHtml::dateField($this->getFieldName('field_value',$index), $defn['field_value'], 
							array("readonly"=>true, "class"=>"readonly"));
				else
					$rtn = CHtml::textField($this->getFieldName('field_value',$index), $defn['field_value'], 
							$ajaxupt);
				break;
			default:
				if ($readonly)
					$rtn = CHtml::textField($this->getFieldName('field_value',$index), $defn['field_value'], 
							array("readonly"=>true, "size"=>"50", "class"=>"readonly"));
				else
				{
					$len = array(
								'size'=>'50',
								'maxlength'=>$defn['field_len'],
							);
					$option = array_merge($len, $ajaxupt);
					$rtn = CHtml::textField($this->getFieldName('field_value',$index), $defn['field_value'], 
							$option);
				}
		}
		return $rtn;
	}
	
	protected function getFieldName($fieldName, $index)
	{
		$modelName = get_class($this->model);
		return $modelName.'['.$this->attribute.']['.$index.']['.$fieldName.']';
	}
	
	protected function getFieldId($fieldName, $index)
	{
		$modelName = get_class($this->model);
		return $modelName.'_'.$this->attribute.'_'.$index.'_'.$fieldName;
	}
}
