<?php
class TableViewWidget extends CWidget
{
	public $model;
	public $attribute;
	public $gridsize;
	public $viewhdr;
	public $viewdtl;
	public $height='100';
	public $record;
	public $recordptr;
	public $addbtnname='';
	public $delbtnname='';
	
	public function run()
	{
		$field=$this->attribute;
		$layout = "<div class='grid_".$this->gridsize." omega alpha' style='margin-bottom:5px;position:relative;height:".$this->height."px;'>";
		$layout .= "<table><thead>";
		$layout .= $this->render($this->viewhdr, null, true);
		$layout .= "</thead><tbody style='position:absolute;height:".($this->height-50)."px;overflow:auto;'>";
		if (count($this->model->$field) > 0)
		{
			foreach ($this->model->$field as $i=>$row)
			{
				$this->record = $row;
				$this->recordptr = $i;
				$layout .= $this->render($this->viewdtl, $this->record, true);
			}
		}
		$layout .= "</tbody></table></div><div class='clear'></div>";
		echo $layout;
		if ($this->addbtnname != '') createAddButtonScript();
		if ($this->delbtnname != '') createDelButtonScript();
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
	
	protected function createAddButtonScript()
	{
		$js = "
			$('".$this->addbtnname."').on('click',function() {
				var max_idx = 0;
				$(this).closest('table').children('tbody').children('tr').children('td').each(function(index) {
					$(this).children().each(function(index) {
						var nm = $(this).attr('name');
						if (nm) {
							var anm = nm.split('][');
							for (var i=0; i<anm.length; i++) {
								if (!isNaN(anm[i])) {
									if (anm[i] > max_idx) max_idx = parseInt(anm[i]);
									break;
								}
							}
						}
					});
				});
				max_idx += 1;
				var text;
				$(this).closest('table').children('tbody').children('tr:first-child').each(function(index) {
					text = $(this).html();
				});
				$(this).closest('table').children('tbody').children('tr:first-child').children('td').each(function(index) {
					$(this).children().each(function(index) {
						var oid = $(this).attr('id');
						if (oid) {
							var aid = oid.split('_');
							var nid = '';
							for (var i=0; i<aid.length; i++) {
								if (nid) nid += '_';
								if (isNaN(aid[i]))
									nid += aid[i];
								else
									nid += max_idx;
							}
							text = text.replace(oid, nid);
						}
						var onm = $(this).attr('name');
						if (onm) {
							var anm = onm.split('][');
							var nnm = '';
							for (var i=0; i<anm.length; i++) {
								if (nnm) nnm += '][';
								if (isNaN(anm[i]))
									nnm += anm[i];
								else
									nnm += max_idx;
							}
							text = text.replace(onm, nnm);
						}
					});
				});
				$(this).closest('table').children('tbody').children('tr:first-child').before('<tr>'+text+'</tr>');
			});
		";
		Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
	}
	
	protected function createDelButtonScript()
	{
		$js = "
			$('table').on('click','".$this->delbtnname."', function() {
				$(this).closest('tr').find('[id=\"_uflag\"').val('D');
				$(this).closest('tr').row.hide();
			});
		";
		Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);
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
