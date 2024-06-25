<?php
$this->pageTitle=Yii::app()->name . ' - ExpenseAudit';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'expenseAudit-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Expense Audit'); ?></strong>
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
			'title'=>Yii::t('app','Expense Apply'),
			'model'=>$model,
				'viewhdr'=>'//expenseAudit/_listhdr',
				'viewdtl'=>'//expenseAudit/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
				'search'=>array(
							'exp_code',
							'city',
							'apply_date',
							'employee',
							'department',
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
