<?php
class GroupingGridWidget extends CWidget
{
	public $model;
	public $parent;
	public $child;

	public $titleview;
	public $gridsize;
	public $height='100';
	
	public $deletebutton = array(
								'parent'=>false, 
								'parentfunctionok'=>'return false;',
								'parentfunctioncancel'=>'return false;',
								'child'=>false,
								'childfunctionok'=>'return false;',
								'childfunctioncancel'=>'return false;',
							);
	
	protected $parentrecord;
	protected $parentptr;
	
	protected $childrecord;
	protected $childptr;

	public function run()
	{
		$pattr = $this->parent['attribute'];
		$parent=$this->model->$pattr;

		$cattr = $this->child['attribute'];
		$child=$this->model->$cattr;

		$layout = "<div class='grid_".$this->gridsize." omega alpha' style='height:".$this->height."px;position:relative;'>";
		$layout .= $this->render($this->titleview, null, true);
		$layout .= "<div class='body_panel' style='height:".($this->height-30)."px;'>";
		if (count($parent) > 0)
		{
			foreach ($parent as $i=>$prow)
			{
				$this->parentrecord = $prow;
				$this->parentptr = $i;
				$tempstr = '';
				$childcount = 0;
				foreach ($child as $j=>$crow)
				{
					if ($crow[$this->child['keyfield']] == $prow[$this->parent['keyfield']])
					{
						$this->childrecord = $crow;
						$this->childptr = $j;
						$childcount++;
						$tempstr .= $this->render($this->child['view'], $this->childrecord, true);
					}
				}
				$this->parentrecord['child_count'] = $childcount;
				$layout .= $this->render($this->parent['view'], $this->parentrecord, true);
				$layout .= $tempstr;
			}
		}
		$layout .= "</div></div>";

		if ($this->deletebutton['parent'])
			$layout .= $this->controller->renderPartial('//site/confirmdialog', 
						array(
							'id'=>'confirmdialogP',
							'title'=>Yii::t('dialog','Confirmation'),
							'message'=>Yii::t('dialog','Are you sure to delete record?'),
							'functionOk'=>$this->deletebutton['parentfunctionok'],
							'functionCancel'=>$this->deletebutton['parentfunctioncancel'],
						),true
					);

		if ($this->deletebutton['child'])
			$layout .= $this->controller->renderPartial('//site/confirmdialog', 
						array(
							'id'=>'confirmdialogC',
							'title'=>Yii::t('dialog','Confirmation'),
							'message'=>Yii::t('dialog','Are you sure to delete record?'),
							'functionOk'=>$this->deletebutton['childfunctionok'],
							'functionCancel'=>$this->deletebutton['childfunctioncancel'],
						),true
					);

		echo $layout;
	}

	public function getParentDialogId()
	{
		return 'confirmdialogP';
	}
	
	public function getChildDialogId()
	{
		return 'confirmdialogC';
	}
	
	public function getLabelName($attribute)
	{
		$labels = $this->model->attributeLabels();
		return (array_key_exists($attribute, $labels)) ? $labels[$attribute] : $attribute;
	}
	
	public function getCode($table)
	{
		$session = Yii::app()->session;
		if (isset($session['code']))
			return $session['code'][$table];
		else
			return array();
	}

	public function getParentFieldName($field)
	{
		$modelName = get_class($this->model);
		return $modelName.'['.$this->parent['attribute'].']['.$this->parentptr.']['.$field.']';
	}

	public function getParentFieldId($field)
	{
		$modelName = get_class($this->model);
		return $modelName.'_'.$this->parent['attribute'].'_'.$this->parentptr.'_'.$field;
	}

	public function getParentFieldValue($field)
	{
		return $this->parentrecord[$field];
	}
	
	public function getChildFieldName($field)
	{
		$modelName = get_class($this->model);
		return $modelName.'['.$this->child['attribute'].']['.$this->childptr.']['.$field.']';
	}
	
	public function getChildFieldId($field)
	{
		$modelName = get_class($this->model);
		return $modelName.'_'.$this->child['attribute'].'_'.$this->childptr.'_'.$field;
	}
	
	public function getChildFieldValue($field)
	{
		return $this->childrecord[$field];
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
