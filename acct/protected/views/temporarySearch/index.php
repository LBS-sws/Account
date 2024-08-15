<?php
$this->pageTitle=Yii::app()->name . ' - TemporarySearch';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'temporarySearch-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Temporary Search'); ?></strong>
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
			'title'=>Yii::t('app','Temporary Apply'),
			'model'=>$model,
				'viewhdr'=>'//temporarySearch/_listhdr',
				'viewdtl'=>'//temporarySearch/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
				'search'=>array(
							'exp_code',
							'city',
							'apply_date',
							'employee',
							'department',
							'status_type',
						),
		));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
echo TbHtml::button("",array("submit"=>"#","class"=>"hide"));
?>
<?php $this->endWidget(); ?>

<?php
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
