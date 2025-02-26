<?php
$this->pageTitle=Yii::app()->name . ' - PerformanceSet';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'PerformanceSet-list',
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
		<?php 
			if (Yii::app()->user->validRWFunction('XS03'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('performanceSet/new'),
				)); 
		?>
	</div>
	</div></div>
	<?php 
		$search = array(
						'name',
						'start_dt',
					);
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('app','performance bonus setting'),
			'model'=>$model,
				'viewhdr'=>'//performanceSet/_listhdr',
				'viewdtl'=>'//performanceSet/_listdtl',
				'search'=>$search,
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


