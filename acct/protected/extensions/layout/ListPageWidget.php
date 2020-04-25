<?php
class ListPageWidget extends CWidget
{
	public $model;
	public $title;
	public $gridsize;
	public $viewhdr;
	public $viewdtl;
	public $height='100';
	public $search=array();
	public $hasNavBar = true;
	public $hasSearchBar = true;
	public $hasPageBar = true;
	public $searchlinkparam = array();
	
	public $advancedSearch = false;
	
	public $hasDateButton = false;
	
	public $record;
	public $recordptr;
	
	public function run()
	{
		$modelName = get_class($this->model);
		
		$layout = '<div class="box">';
		$layout .= '<div class="box-header"><h3 class="box-title"><strong>'.$this->title.'</strong></h3>';
		$layout .= $this->renderDateButton();
		$layout .= '</div>';
		$layout .= '<div class="box-body table-responsive">';
		if ($this->hasSearchBar || $this->hasNavBar) {
			$layout .= '<div class="box-tools">';
			if ($this->hasNavBar) {
				$layout .= $this->navBar();
			}
			if ($this->hasSearchBar) {
				$layout .= '<span class="pull-right">';
				$layout .= $this->searchBar();
				$layout .= '</span>';
			}
		$layout .= '</div>';
		}
		$layout .= '<div><table id="tblData" class="table table-hover">';
		$layout .= '<thead>';
		$layout .= $this->render($this->viewhdr, null, true);
		$layout .= '</thead>';

		$layout .= '<tbody>';
		if (count($this->model->attr) > 0)
		{
			foreach ($this->model->attr as $i=>$row)
			{
				$this->record = $row;
				$this->recordptr = $i;
				$line = $this->render($this->viewdtl, $this->record, true);
				$layout .= $line;
			}
		} else {
			$layout .= '<tr><td>&nbsp;</td></tr>';
		}
		$layout .= '</tbody>';
		$layout .= '</table></div>';
		$layout .= '</div>';
		
		$layout .= '<div class="box-footer clearfix">';
		if ($this->hasPageBar) {
			$layout .= '<div class="box-tools">'.$this->pageBar().'</div>';
		}
		$layout .= '<span class="pull-right">'.Yii::t('misc','Rec').': '.$this->model->totalRow.'&nbsp;&nbsp;<a href="#" id="goTableTop">'.Yii::t('misc','Go Top').'</a>'.'</span>';
		$layout .= '</div>';

		echo $layout;

		$link = '/'.$this->controller->uniqueId.'/'.$this->controller->action->id;
		$url = Yii::app()->createUrl($link);
		$formurl = Yii::app()->createAbsoluteUrl($link);
		$fldid = get_class($this->model).'_noOfItem';


		if ($this->hasSearchBar && $this->advancedSearch) {
			$fldlist = array('NA'=>Yii::t('misc','-- None --'));
			foreach ($this->model->searchColumns() as $field=>$value) {
				$fldlist[$field] = Yii::t('app',$this->getLabelName($field));
			}
			$this->controller->renderPartial('//site/filter',array('model'=>$this->model, 'fieldlist'=>$fldlist, 'formurl'=>$formurl));
		}
		
		$js = '
$("#goTableTop").click(function(){$("html,body").animate({scrollTop:0},600);return false;});
		';
		Yii::app()->clientScript->registerScript('ListGoTop',$js,CClientScript::POS_READY);
		
		$js = "
$('#$fldid').on('change', function(){Loading.show();jQuery.yii.submitForm(this,'$url',{});return false;});
		";
		Yii::app()->clientScript->registerScript('ListPageRefresh',$js,CClientScript::POS_READY);

		if ($this->hasSearchBar) {
			$droplistid = $modelName.'_searchField';
			$textid = $modelName.'_searchValue';

			if(empty($_GET['index'])){
                $param = array('pageNum'=>1);
            }else{
                $param = array('index'=>$_GET['index'],'pageNum'=>1);
            }
			if (!empty($this->searchlinkparam)) $param = array_merge($param, $this->searchlinkparam);
			$path = Yii::app()->createAbsoluteUrl($link, $param);
			
			$js = <<<EOF
$('#btnSearch').on('click', function(){
	if ($('#$droplistid').val()=='ex_advanced') {
		$('#filterdialog').modal('show');
	} else {
		jQuery.yii.submitForm(this,'$path',{});
	}
});
EOF;
			Yii::app()->clientScript->registerScript('ListPageSearchButton',$js,CClientScript::POS_READY);
			
			if ($this->advancedSearch) {
				$js = <<<EOF
$('#$droplistid').on('change', function(){
	if ($(this).val()=='ex_advanced') {
		$('#$textid').val('');
		$('#$textid').attr('readonly',true);
	} else {
		$('#$textid').attr('readonly',false);
	}
});
EOF;
				Yii::app()->clientScript->registerScript('ListPageAdvancedSrch',$js,CClientScript::POS_READY);
			}
		}
	}

	protected function renderDateButton() {
		$modelName = get_class($this->model);
		$rtn = TbHtml::hiddenField($modelName.'[dateRangeValue]', $this->model->dateRangeValue, array('id'=>$modelName.'_dateRangeValue'));
		
		if ($this->hasDateButton) {

			$rtn .= '<div class="box-tools">';
			$rtn .= '<span class="pull-right">';
			$rtn .= TbHtml::button(Yii::t('misc','Latest month'), array('id'=>'btnDateM1','class'=>'btn-default'));
			$rtn .= TbHtml::button(Yii::t('misc','3 months'), array('id'=>'btnDateM3','class'=>'btn-default'));
			$rtn .= TbHtml::button(Yii::t('misc','6 months'), array('id'=>'btnDateM6','class'=>'btn-default'));
			$rtn .= TbHtml::button(Yii::t('misc','1 year'), array('id'=>'btnDateY1','class'=>'btn-default'));
			$rtn .= TbHtml::button(Yii::t('misc','All'), array('id'=>'btnDateAll','class'=>'btn-default'));
			$rtn .= '</span>';
			$rtn .= '</div>';
			
			$fldname = $modelName.'_dateRangeValue';

			$link = '/'.$this->controller->uniqueId.'/'.$this->controller->action->id;
			$url = Yii::app()->createUrl($link);
			
			$js = <<<EOF
function setDateRange(v){
	var obj = $('#$fldname'); 
	obj.val(v);
	Loading.show();
	jQuery.yii.submitForm(obj,'$url',{});
}

$('#btnDateAll').on('click',function(){setDateRange('0');});
$('#btnDateM1').on('click',function(){setDateRange('1');});
$('#btnDateM3').on('click',function(){setDateRange('3');});
$('#btnDateM6').on('click',function(){setDateRange('6');});
$('#btnDateY1').on('click',function(){setDateRange('12');});

var dv = $('#$fldname').val();
switch (dv) {
	case '0': $('#btnDateAll').removeClass('btn-default').addClass('btn-info'); break;
	case '1': $('#btnDateM1').removeClass('btn-default').addClass('btn-info'); break;
	case '3': $('#btnDateM3').removeClass('btn-default').addClass('btn-info'); break;
	case '6': $('#btnDateM6').removeClass('btn-default').addClass('btn-info'); break;
	case '12': $('#btnDateY1').removeClass('btn-default').addClass('btn-info'); break;
}
EOF;
			Yii::app()->clientScript->registerScript('selectDateRange',$js,CClientScript::POS_READY);
		}
		return $rtn;
	}

	protected function navBar() 
	{
		$totalrow = $this->model->totalRow;
		$pageno = $this->model->pageNum;
		$pagerow = ($this->model->noOfItem == 0) ? $totalrow : $this->model->noOfItem;
		$remain = ($pagerow==0) ? 0 : $totalrow % $pagerow;
		$totalpage = ($pagerow==0) ? 1 : (($totalrow - $remain) / $pagerow) + (($remain==0) ? 0 : 1);
		$window = 10;
		$tab = 3;
		$width=80/$window;
		
		$link = '/'.$this->controller->uniqueId.'/'.$this->controller->action->id;
		
		$items = array();
		
		$param = array('pageNum'=>1);
		if (!empty($this->searchlinkparam)) $param = array_merge($param, $this->searchlinkparam);
//		$url = Yii::app()->createUrl($link,$param);
		$url = "javascript:Loading.show();window.location.href='".Yii::app()->createUrl($link,$param)."';";
		$items[] = array('label'=>'1','url'=>$url,'active'=>($pageno == 1));
		$cnt = 1;

		if ($pageno > $tab && $totalpage > $window) {
			$items[] = array('label'=>'...','url'=>'#',);
			$cnt++;
		}
		
		$hadj = ($pageno > $tab && $totalpage > $window) ? 2 : 1;
		$tadj = ($totalpage > $window) ? (($pageno < $totalpage-($window-$hadj)+1) ? 2 : 1) : 0;
		$adj = $hadj + $tadj;

		$pos = ($pageno > $tab && $totalpage > $window) 
				? (($pageno > $totalpage-($window-$hadj)+1) ? $totalpage-($window-$hadj)+1 : $pageno-($tab-1)) 
				: 2; 
		while ($pos <= $totalpage && $cnt < $window-$tadj) 
		{
			$param = array('pageNum'=>$pos);
			if (!empty($this->searchlinkparam)) $param = array_merge($param, $this->searchlinkparam);
//			$url = Yii::app()->createUrl($link,$param);
			$url = "javascript:Loading.show();window.location.href='".Yii::app()->createUrl($link,$param)."';";
			$items[] = array('label'=>$pos,'url'=>$url,'active'=>($pageno == $pos));
			$pos++;
			$cnt++;
		}
		
		if ($totalpage > $window) {
			if ($pageno < $totalpage-($window-$adj-$tab)-1 && $totalpage > $window) {
				$items[] = array('label'=>'...','url'=>'#',);
				$cnt++;
			}
			
			$param = array('pageNum'=>$totalpage);
			if (!empty($this->searchlinkparam)) $param = array_merge($param, $this->searchlinkparam);
//			$url = Yii::app()->createUrl($link,$param);
			$url = "javascript:Loading.show();window.location.href='".Yii::app()->createUrl($link,$param)."';";
			$items[] = array('label'=>$totalpage,'url'=>$url,'active'=>($pageno == $totalpage),);

			$cnt++;
		}
		
//		return TbHtml::pagination($items, array('align'=>TbHtml::PAGINATION_ALIGN_RIGHT,'size'=>TbHtml::PAGINATION_SIZE_SMALL));
		return TbHtml::pagination($items, array('class'=>'pagination pagination-sm no-margin'));
	}
	
	protected function searchBar() {
		$modelName = get_class($this->model);
		$link = '/'.$this->controller->uniqueId.'/'.$this->controller->action->id;
		$param = array('pageNum'=>1);
		if (!empty($this->searchlinkparam)) $param = array_merge($param, $this->searchlinkparam);
		$list[''] = Yii::t('misc','-- Field --');
		$labelplus = '';
		if ($this->advancedSearch) {
			$list['ex_advanced'] = Yii::t('misc','<< Advanced >>');
			$labelplus = ' <span class="fa fa-plus"></span>';
		}
		$flag = true;
		$columns = $this->model->searchColumns();
		if (!$this->advancedSearch && empty($columns)) {
			$columns = $this->search ;
			$flag = false;
		}
		foreach ($columns as $field=>$value) {
			$val = ($flag) ? $field : $value;
			$list[$val] = Yii::t('app',$this->getLabelName($val));
		}
		$layout = TbHtml::dropDownList($modelName.'[searchField]',$this->model->searchField,$list,array('id'=>$modelName.'_searchField'));
		$layout .= TbHtml::textField($modelName.'[searchValue]',$this->model->searchValue,
					array('size'=>15,'id'=>$modelName.'_searchValue','readonly'=>($this->model->isAdvancedSearch()),
						'placeholder'=>Yii::t('misc','Search'),
//						'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('misc','Search'), array('submit'=>Yii::app()->createUrl($link,$param),)),
						'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('misc','Search').$labelplus, array('id'=>'btnSearch',)),
				));
		return $layout;
	}
	
	protected function pageBar()
	{
		$modelName = get_class($this->model);
		$link = '/'.$this->controller->uniqueId.'/'.$this->controller->action->id;
		$list = array(
					'25'=>'25',
					'50'=>'50',
					'100'=>'100',
					'500'=>'500',
					'0'=>Yii::t('misc','All'),
				);
		$fldname = $modelName.'[noOfItem]';
/*
		$layout = '<div class="col-sm-3">'.Yii::t('misc','Display').': '
				.TbHtml::dropDownList($fldname,$this->model->noOfItem,$list,
					array('submit'=>Yii::app()->createUrl($link),)
				).'</div>';
*/
		$layout = '<div class="col-sm-3">'.Yii::t('misc','Display').': '
				.TbHtml::dropDownList($fldname,$this->model->noOfItem,$list
				).'</div>';
		return $layout;
	}
	
	public function getLabelName($attribute)
	{
		$labels = $this->model->attributeLabels();
		return (array_key_exists($attribute, $labels)) ? $labels[$attribute] : $attribute;
	}
	
	public function getFieldName($attribute)
	{
		$modelName = get_class($this->model);
		return $modelName.'[attr]['.$this->recordptr.']['.$attribute.']';
	}
	
	public function createOrderLink($form, $attribute)
	{
		$modelName = get_class($this->model);
		$link = array(
					'ajax'=>array(
						'type'=>'POST',
						'url'=>Yii::app()->createUrl('ajax/dummy'),
						'success'=>'function() {
							var oldfield = $("#'.$modelName.'_orderField").val();
							if (oldfield != "'.$attribute.'")
								$("#'.$modelName.'_orderType").val("A");
							else
							{
								var oldtype = $("#'.$modelName.'_orderType").val();
								if (oldtype == "D")
									$("#'.$modelName.'_orderType").val("A");
								else
									$("#'.$modelName.'_orderType").val("D");
							}
							$("#'.$modelName.'_orderField").val("'.$attribute.'");
							$("form#'.$form.'").submit();
						}',
					),
				);
		return $link;
	}
	
	public function getIndex() {
		return $this->recordptr + ($this->model->pageNum - 1) * $this->model->noOfItem;
	}
	
	public function getLink($access, $writeurl, $readurl, $param) {
		$rw = Yii::app()->user->validRWFunction($access); 
		$url = $rw ? $writeurl : $readurl;
		return Yii::app()->createUrl($url,$param);
	}
	
	public function drawEditButton($access, $writeurl, $readurl, $param) {
		$rw = Yii::app()->user->validRWFunction($access); 
		$url = $rw ? $writeurl : $readurl;
		$icon = $rw ? "glyphicon glyphicon-pencil" : "glyphicon glyphicon-eye-open";
		$alt = $rw ? Yii::t('misc','Edit') : Yii::t('misc','View');
		$lnk=Yii::app()->createUrl($url,$param);
		
		return "<a href=\"$lnk\"><span class=\"$icon\"></span></a>";
	}
	
	public function drawOrderArrow($attribute)
	{
		$arrow = '';
		if ($this->model->orderField == $attribute)
		{
			$arrow = ' <span class="fa '.(($this->model->orderType == 'D') ? 'fa-sort-amount-desc' : 'fa-sort-amount-asc').'"></span>';
		}
		return $arrow;
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
