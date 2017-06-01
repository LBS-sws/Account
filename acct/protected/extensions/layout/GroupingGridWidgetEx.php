<?php
class GroupingGridWidgetEx extends CWidget
{
	public $model;
	public $parent;
	public $child;

	public $titleview;
	public $gridsize;
	public $height='100';
	
	public $addbutton = array(
								'parent'=>false,
								'parenturl'=>array(),
								'child'=>false,
								'childurl'=>array(),
							);
	
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
		if ($this->addbutton['parent'])
		{
			$lnkname = Yii::t('misc','Add New').' '.$this->addbutton['parenttitle'].' '.Yii::t('misc','Record');
			$lnk = Yii::app()->createUrl($this->getAddUrl($this->addbutton['parenturl']), $this->getAddUrlParamP($this->addbutton['parenturl']));
			$layout .= "<div class='grid_".$this->gridsize." addbutton'>".CHtml::link($lnkname, $lnk)."</div>";
			$layout .= "<div class='clear'></div>";
		}

		if (count($parent) > 0)
		{
			foreach ($parent as $i=>$prow)
			{
				$this->parentrecord = $prow;
				$this->parentptr = $i;
				$layout .= $this->render($this->parent['view'], $this->parentrecord, true);

				if ($this->addbutton['child'])
				{
					$lnkname = Yii::t('misc','Add New').' '.$this->addbutton['childtitle'].' '.Yii::t('misc','Record');
					$lnk = Yii::app()->createUrl($this->getAddUrl($this->addbutton['childurl']), $this->getAddUrlParamC($this->addbutton['childurl']));
					$layout .= "<div class='grid_".$this->gridsize." addbutton'>".CHtml::link($lnkname, $lnk)."</div>";
					$layout .= "<div class='clear'></div>";
				}
		
				foreach ($child as $j=>$crow)
				{
					if ($crow[$this->child['keyfield']] == $prow[$this->parent['keyfield']])
					{
						$this->childrecord = $crow;
						$this->childptr = $j;
						$layout .= $this->render($this->child['view'], $this->childrecord, true);
					}
				}
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
	
	public function getParentButtonId($buttonName)
	{
		return $buttonName.'_'.$this->parentptr;
	}

	public function getChildButtonId($buttonName)
	{
		return $buttonName.'_'.$this->childptr;
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
	
	protected function getAddUrl($url)
	{
		if (count($url) > 0)
			return $url[0];
		else
			return '';
	}
	
	protected function getAddUrlParamP($url)
	{
		$param = array();
		if (count($url) > 0)
		{
			foreach ($url as $key=>$val)
			{
				if ($key !== 0) $param[$key] = $val;
			}
		}
		return $param;
	}
	
	protected function getAddUrlParamC($url)
	{
		$param = array();
		if (count($url) > 0)
		{
			foreach ($url as $key=>$val)
			{
				if ($key !== 0)
				{
					switch ($val[0])
					{
						case 'fix':
							$param[$key] = $val[1];
							break;
						case 'variable':
							$param[$key] = $this->getParentFieldValue($val[1]);
							break;
					}
				}
			}
		}
		return $param;
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
