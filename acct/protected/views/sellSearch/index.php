<?php
$this->pageTitle=Yii::app()->name . ' - SellCompute';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'sellCompute-list',
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
		<strong><?php echo Yii::t('app','Sales Commission'); ?></strong>
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
                <?php echo TbHtml::link('<span class="fa fa-hdd-o"></span> '."旧版本入口", Yii::app()->createUrl('query/index'),array('class'=>'btn btn-default'));
                ?>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-cloud-download"></span> ' . Yii::t('misc', 'Down'), array(
                    'submit' => Yii::app()->createUrl('sellSearch/downAll')));
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
        'viewhdr'=>'//sellSearch/_listhdr',
        'viewdtl'=>'//sellSearch/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search_add_html'=>$search_add_html,
        'advancedSearch'=>true,
    ));
    echo TbHtml::button("test",array("submit"=>"#","class"=>"hide"));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
    echo $form->hiddenField($model,'filter');
echo TbHtml::hiddenField("down_id",$model->down_id);
?>

<?php $this->endWidget(); ?>

<?php
$allotOneUrl = Yii::app()->createUrl('sellCompute/allotOne');
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
