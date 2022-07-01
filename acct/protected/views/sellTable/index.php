<?php
$this->pageTitle=Yii::app()->name . ' - SellTable';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'sellTable-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>
<style>
    .pt-7{ padding-top: 7px;text-align: right;}
    option:disabled{ color: rgb(210,210,210);}
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('salestable','Sales commission Bi'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>

<section class="content">
    <div class="box"><div class="box-body">
            <div class="btn-group" role="group">
                <?php echo TbHtml::link('<span class="fa fa-hdd-o"></span> '."旧版本入口", Yii::app()->createUrl('salestable/index'),array('class'=>'btn btn-default'));
                ?>
            </div>
        </div></div>
	<?php
    $modelClass=get_class($model);
    $search_add_html="";
    $search_add_html .= TbHtml::dropDownList("{$modelClass}[year]",$model->year,SellComputeList::getYearList(),
        array("class"=>"form-control submitBtn","id"=>"selectYear"));
    $search_add_html.="<span>&nbsp;&nbsp;-&nbsp;&nbsp;</span>";
    $search_add_html .= TbHtml::dropDownList("{$modelClass}[month]",$model->month,SellComputeList::getMonthList(),
        array("class"=>"form-control submitBtn","id"=>"selectMonth"));

    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('app','sale commission man'),
        'model'=>$model,
        'viewhdr'=>'//sellTable/_listhdr',
        'viewdtl'=>'//sellTable/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search_add_html'=>$search_add_html,
        'search'=>array(
            'code',
            'name',
            'dept_name',
            'city_name',
            'examine'
        ),
    ));
    echo TbHtml::button("test",array("submit"=>"#","class"=>"hide"));
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
$allotOneUrl = Yii::app()->createUrl('sellTable/allotOne');
$js = "
$('.submitBtn').change(function(){
    $('form:first').submit();
});

if($('#selectYear').val()==2022){
    $('#selectMonth').children('option:lt(4)').prop('disabled',true);
}
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
