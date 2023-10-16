<?php
$this->pageTitle=Yii::app()->name . ' - Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'report-form',
'action'=>Yii::app()->createUrl('report/generate'),
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('report','Reimbursement Form'); ?></strong>
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
		<?php echo TbHtml::button(Yii::t('misc','Submit'), array(
				'submit'=>Yii::app()->createUrl('report/reimburse'))); 
		?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'name'); ?>
			<?php echo $form->hiddenField($model, 'fields'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'start_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'start_dt', array('class'=>'form-control pull-right',)); ?>
					</div>
				</div>
			</div>
		
			<div class="form-group">
				<?php echo $form->labelEx($model,'end_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'end_dt', array('class'=>'form-control pull-right',)); ?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'ref_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'ref_no', array('maxlength'=>255,)); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php
                    $accountList = General::getAccountList(Yii::app()->user->city_allow());
                    $accountList[""] = Yii::t("report","-- All --");
                    ksort($accountList);
                    echo $form->dropDownList($model, 'acct_id', $accountList,
						array('disabled'=>($model->scenario=='view'))
					);
                    ?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
$datefields = array();
if ($model->showField('start_dt')) $datefields[] = 'Report01Form_start_dt';
if ($model->showField('end_dt')) $datefields[] = 'Report01Form_end_dt';
if (!empty($datefields)) {
	$js = Script::genDatePicker($datefields);
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

