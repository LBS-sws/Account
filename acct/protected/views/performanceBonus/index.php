<?php
$this->pageTitle=Yii::app()->name . ' - PerformanceBonus';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'PerformanceBonus-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','performance bonus setting'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
	</div>
	</div></div>
	<?php
    $modelClass=get_class($model);
    $search_add_html="";
    $search_add_html .= TbHtml::dropDownList("{$modelClass}[year_no]",$model->year_no,PerformanceBonusList::getYearList(),
        array("class"=>"form-control submitBtn","id"=>"selectYear"));
    $search_add_html.="<span>&nbsp;&nbsp;-&nbsp;&nbsp;</span>";
    $search_add_html .= TbHtml::dropDownList("{$modelClass}[quarter_no]",$model->quarter_no,PerformanceBonusList::getQuarterList(),
        array("class"=>"form-control submitBtn","id"=>"selectMonth"));

    $search = array(
        'code',
        'name',
        'city_name',
        'dept_name',
    );
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('app','performance bonus setting'),
        'model'=>$model,
        'viewhdr'=>'//performanceBonus/_listhdr',
        'viewdtl'=>'//performanceBonus/_listdtl',
        'search'=>$search,
        'search_add_html'=>$search_add_html,
    ));
    ?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>

<?php
$js = "
$('.submitBtn').change(function(){
    $('form:first').submit();
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>


