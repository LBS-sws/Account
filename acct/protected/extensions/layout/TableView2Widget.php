<?php
class TableView2Widget extends CWidget
{
	public $model;
	public $attribute;
	public $gridsize;
	public $viewhdr;
	public $viewdtl;
	public $height='100';
	public $codelist = array();
	
	protected $record;
	protected $recordptr;

	public function run()
	{
		$field=$this->attribute;
		$layout = "<table id='tblDetail' class='table table-hover'><thead>";
		$layout .= $this->render($this->viewhdr, null, true);
		$layout .= "</thead>";
		$layout .= "<tbody>";

		if (count($this->model->$field) > 0)
		{
			$odd = true;
			foreach ($this->model->$field as $i=>$row)
			{
				$this->record = $row;
				$this->recordptr = $i;
				$line = $this->render($this->viewdtl, $this->record, true);
//				if ($odd) $line = str_replace("<tr>","<tr class='odd'>",$line);
				$layout .= $line;
				$odd = !($odd);
			}
		}
		$layout .= "</tbody></table>";
		echo $layout;
	}

	public function getFieldName($field)
	{
		$modelName = get_class($this->model);
		return $modelName.'['.$this->attribute.']['.$this->recordptr.']['.$field.']';
	}
	
	public function getFieldValue($field)
	{
		return $this->record[$field];
	}
	
	public function getLabelName($attribute)
	{
		$labels = $this->model->attributeLabels();
		return (array_key_exists($attribute, $labels)) ? $labels[$attribute] : $attribute;
	}
	
	public function getCodeList($id)
	{
		return (array_key_exists($id, $this->codelist)) ? $this->codelist[$id] : array(''=>Yii::t('misc','-- None --'),);
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
