<?php
$this->pageTitle=Yii::app()->name . ' - Payroll File';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'payroll-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Payroll File'); ?></strong>
	</h1>
</section>

<section class="content">
    <div class="box">
        <div class="box-body">
            <div class="btn-group pull-right" role="group">
                <?php
                echo TbHtml::button('<span class="fa fa-down"></span> '.Yii::t('trans','down'), array(
                        'data-toggle'=>'modal','data-target'=>'#downPayDialog',)
                );
                ?>
            </div>
        </div>
    </div>

	<?php
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('trans','Payroll File List'),
			'model'=>$model,
				'viewhdr'=>'//payroll/_listhdr',
				'viewdtl'=>'//payroll/_listdtl',
            'hasDateButton'=>true,
            'advancedSearch'=>true,
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
	echo $form->hiddenField($model,'filter');
?>
<?php $this->endWidget(); ?>

<?php
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

<?php $this->renderPartial('//payroll/downPay'); ?>

