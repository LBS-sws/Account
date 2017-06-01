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
		<strong><?php echo Yii::t('report',$model->name); ?></strong>
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
				'submit'=>Yii::app()->createUrl('report/generate'))); 
		?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'name'); ?>
			<?php echo $form->hiddenField($model, 'fields'); ?>
			<?php echo $form->hiddenField($model, 'target_dt'); ?>
			<?php echo $form->hiddenField($model, 'email'); ?>
			<?php echo $form->hiddenField($model, 'emailcc'); ?>
			<?php echo $form->hiddenField($model, 'form'); ?>

		<?php if ($model->showField('city') && !Yii::app()->user->isSingleCity()): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->dropDownList($model, 'city', General::getCityListWithNoDescendant(Yii::app()->user->city_allow()),
						array('disabled'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>
		<?php else: ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
		<?php endif ?>
		
			<div class="form-group">
				<?php echo $form->labelEx($model,'start_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'start_dt', 
							array('class'=>'form-control pull-right','readonly'=>($model->scenario=='view'),)); 
						?>
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
						<?php echo $form->textField($model, 'end_dt', 
							array('class'=>'form-control pull-right','readonly'=>($model->scenario=='view'),)); 
						?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'format',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$item = array('EXCEL'=>'Excel');
						if (Yii::app()->user->isSingleCity()) $item['FEED']=Yii::t('report','For Feedback');
						echo $form->dropDownList($model, 'format', 
							$item, array('disabled'=>($model->scenario=='view'))
						); 
					?>
				</div>
			</div>
			
			<div id="feedback_div" style="display: none">
				<div class="form-group">
					<?php echo $form->labelEx($model,'touser',array('class'=>"col-sm-2 control-label")); ?>
					<div class="col-sm-5">
						<?php echo $form->dropDownList($model, 'touser', 
							General::getMgrFeedbackList(), array('disabled'=>($model->scenario=='view'))
						); ?>
					</div>
				</div>

				<div class="form-group">
					<?php echo $form->labelEx($model,'ccuser',array('class'=>"col-sm-2 control-label")); ?>
					<div class="col-sm-5">
						<?php 
							echo $form->listbox($model, 'ccuser', General::getEmailListboxData(), 
								array('size'=>6,'multiple'=>'multiple')
							); 
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
$js = "
showEmailField();

$('#ReportForm_format').on('change',function() {
	showEmailField();
});

function showEmailField() {
	$('#feedback_div').css('display','none');
	if ($('#ReportForm_format').val()=='FEED') $('#feedback_div').css('display','');
}
";
Yii::app()->clientScript->registerScript('changestyle',$js,CClientScript::POS_READY);

$js = Script::genDatePicker(array(
			'ReportForm_start_dt',
			'ReportForm_end_dt',
		));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

