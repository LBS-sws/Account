<?php
class SimpleGridWidget extends CWidget
{
	public $model;
	public $attribute;
	public $gridsize;
	public $view;
	public $height='100';
	
	public $record;
	public $recordptr;
	
	public function run()
	{
		$layout = "<div class='grid_".$this->gridsize."' style='height:".$this->height."px;overflow:auto'>";
		$field=$this->attribute;
		$vp=$this->getViewPath();
		if (count($this->model->$field) > 0)
		{
			foreach ($this->model->$field as $i=>$row)
			{
				$this->record = $row;
				$this->recordptr = $i;
				$layout .= $this->render($this->view, $this->record, true);
			}
		}
		$layout .= "</div>";
		echo $layout;
	}

	public function getLabelName($attribute)
	{
		$labels = $this->model->attributeLabels();
		return (array_key_exists($attribute, $labels)) ? $labels[$attribute] : $attribute;
	}

	public function getFieldName($attribute)
	{
		$modelName = get_class($this->model);
		return $modelName.'['.$this->attribute.']['.$this->recordptr.']['.$attribute.']';
	}
	
	public function getCode($table)
	{
		$session = Yii::app()->session;
		if (isset($session['code']))
			return $session['code'][$table];
		else
			return array();
	}

	public function render($view,$data=null,$return=false)
	{
		$ctrl = $this->getController();
		if(($viewFile=$ctrl->getViewFile($view))!==false)
			return $this->renderFile($viewFile,$data,$return);
		else
			throw new CException(Yii::t('yii','{widget} cannot find the view "{view}".',
				array('{widget}'=>get_class($this), '{view}'=>$view)));
	}
}
