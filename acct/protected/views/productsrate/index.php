<?php
$this->pageTitle=Yii::app()->name . ' - Productsrate';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'Productsrate-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Product royalty ladder'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('XS03'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('productsrate/new'),
				)); 
		?>
	</div>
	</div></div>
	<?php 
		$search = array(
						'city_name',
						'start_dt',
					);
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('service','Product royalty ladder List'),
			'model'=>$model,
				'viewhdr'=>'//productsrate/_listhdr',
				'viewdtl'=>'//productsrate/_listdtl',
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


