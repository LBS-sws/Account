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
	public $tableidx = '';
	
	protected $record;
	protected $recordptr;

	public function run()
	{
		$field=$this->attribute;
		$idx = $this->tableidx;
		$layout = "<table id='tblDetail$idx' class='table table-hover'><thead>";
		$layout .= $this->render($this->viewhdr, null, true);
		$layout .= "</thead>";
		$layout .= "<tbody>";
		$rows = $this->getRecordArray();

		if (count($rows) > 0)
		{
			$odd = true;
			foreach ($rows as $i=>$row)
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

	private function getRecordArray() {
		$field = $this->attribute;
		$pos = strpos($field,'[');
		if ($pos!==false) {
			$tmp = split('[][]',$field);
			$items = array();
			foreach ($tmp as $value) {
				($value!='') && $items[] = $value;
			}
			$obj = $this->model->$items[0];
			foreach ($items as $i=>$key) {
				if ($i!=0) $obj = $obj[$key];
			}
			return $obj;
		} else {
			return $this->model->$field;
		}
	}
	
	public function getFieldName($field)
	{
		$modelName = get_class($this->model);
		$fld = $this->attribute;
		$pos = strpos($fld,'[');
		if ($pos!==false) {
			$tmp = split('[][]',$fld);
			$items = array();
			foreach ($tmp as $value) {
				($value!='') && $items[] = $value;
			}
			$name = $modelName;
			foreach ($items as $i=>$key) {
				$name .= '['.$key.']';
			}
			return $name.'['.$this->recordptr.']['.$field.']';
		} else {
			return $modelName.'['.$this->attribute.']['.$this->recordptr.']['.$field.']';
		}
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
