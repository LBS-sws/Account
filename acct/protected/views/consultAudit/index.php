<?php
$this->pageTitle=Yii::app()->name . ' - ConsultAudit';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'consultAudit-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Consult Fee Audit'); ?></strong>
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
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('consult','Audit List'),
			'model'=>$model,
				'viewhdr'=>'//consultAudit/_listhdr',
				'viewdtl'=>'//consultAudit/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
				'search'=>array(
							'consult_code',
							//'customer_code',
							'apply_city',
							'audit_city',
						),
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
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
