<?php
$this->pageTitle=Yii::app()->name . ' - Account File';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'acctfile-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Bank Balance'); ?></strong>
	</h1>
</section>

<section class="content">
	<?php 
		$search = array(
							'year_no',
							'month_no',
					);
		if (!Yii::app()->user->isSingleCity()) $search[] = 'city_name';
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('trans','Bank List'),
			'model'=>$model,
				'viewhdr'=>'//acctfile/_listhdr',
				'viewdtl'=>'//acctfile/_listdtl',
				'search'=>$search,
		));
	?>
</section>

<?php
// Dummy Button for include jQuery.yii.submitForm
echo TbHtml::button('dummyButton', array('style'=>'display:none','disabled'=>true,'submit'=>'#',));
?>

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

