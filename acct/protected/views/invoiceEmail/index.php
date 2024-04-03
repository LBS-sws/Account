<?php
$this->pageTitle=Yii::app()->name . ' - InvoiceEmail';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'invoiceEmail-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Invoice Email'); ?></strong>
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
		<?php 
			if (Yii::app()->user->validRWFunction('XC09'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('invoiceEmail/new'), 
				)); 
		?>
	</div>
	</div></div>
	<?php 
		$search = array(
						'start_dt',
						'email_text',
					);
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('app','Invoice Email'),
			'model'=>$model,
				'viewhdr'=>'//invoiceEmail/_listhdr',
				'viewdtl'=>'//invoiceEmail/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
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


