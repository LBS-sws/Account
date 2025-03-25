<?php
$this->pageTitle=Yii::app()->name . ' - PlaneAudit';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'planeAudit-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>
<style>
    .pt-7{ padding-top: 7px;text-align: right;}
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Audit for plane'); ?></strong>
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
    <div class="box">
        <div class="box-body">
        </div>
    </div>
	<?php
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('plane','Plane Allot List'),
        'model'=>$model,
        'viewhdr'=>'//planeAudit/_listhdr',
        'viewdtl'=>'//planeAudit/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search'=>array(
            'code',
            'name',
            'city_name'
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
$js = "

";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
