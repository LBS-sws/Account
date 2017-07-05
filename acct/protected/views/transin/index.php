<?php
$this->pageTitle=Yii::app()->name . ' - Transaction In';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'trans-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Transaction(In)'); ?></strong>
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
			if (Yii::app()->user->validRWFunction('XE01'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('transin/new'), 
				)); 
		?>
	</div>
	</div></div>
	<?php 
		$search = array(
						'trans_type_desc',
						'acct_type_desc',
						'bank_name',
						'acct_no',
						'int_fee',
						'status',
					);
		if (!Yii::app()->user->isSingleCity()) $search[] = 'city_name';
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('trans','Transaction List'),
			'model'=>$model,
				'viewhdr'=>'//transin/_listhdr',
				'viewdtl'=>'//transin/_listdtl',
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


