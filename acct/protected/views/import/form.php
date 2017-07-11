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

		<?php if ($model->showField('start_dt')): ?>
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
		<?php else: ?>
			<?php echo $form->hiddenField($model, 'start_dt'); ?>
		<?php endif ?>
		
		<?php if ($model->showField('end_dt')): ?>
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
		<?php else: ?>
			<?php echo $form->hiddenField($model, 'end_dt'); ?>
		<?php endif ?>

		<?php if ($model->showField('target_dt')): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'target_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'target_dt', 
							array('class'=>'form-control pull-right','readonly'=>($model->scenario=='view'),)); 
						?>
					</div>
				</div>
			</div>
		<?php else: ?>
			<?php echo $form->hiddenField($model, 'target_dt'); ?>
		<?php endif ?>

		<?php if ($model->showField('year')): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'year',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$item = array();
						for ($i=2015;$i<=2025;$i++) {$item[$i] = $i; }
						echo $form->dropDownList($model, 'year', $item); 
					?>
				</div>
			</div>
		<?php else: ?>
			<?php echo $form->hiddenField($model, 'year'); ?>
		<?php endif ?>

		
		<?php if ($model->showField('month')): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'month',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$item = array();
						for ($i=1;$i<=12;$i++) {$item[$i] = $i; }
						echo $form->dropDownList($model, 'month', $item); 
					?>
				</div>
			</div>
		<?php else: ?>
			<?php echo $form->hiddenField($model, 'month'); ?>
		<?php endif ?>

		<?php if ($model->showField('format')): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'format',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$item = array('EXCEL'=>'Excel','PDF'=>'PDF');
						if ($model->showField('format_e')) $item = array('EXCEL'=>'Excel','PDF'=>'PDF','EMAIL'=>Yii::t('report','Email'));
						echo $form->dropDownList($model, 'format', 
							$item, array('disabled'=>($model->scenario=='view'))
						); 
					?>
				</div>
			</div>
		<?php else: ?>
			<?php echo $form->hiddenField($model, 'format'); ?>
		<?php endif ?>

			<div id="email_div" style="display: none">
				<div class="form-group">
					<?php echo $form->labelEx($model,'email',array('class'=>"col-sm-2 control-label")); ?>
					<div class="col-sm-5">
						<?php echo $form->emailField($model, 'email', 
							array('size'=>40,'maxlength'=>250)
						); ?>
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
	$('#email_div').css('display','none');
	if ($('#ReportForm_format').val()=='EMAIL') $('#email_div').css('display','');
}
";
Yii::app()->clientScript->registerScript('changestyle',$js,CClientScript::POS_READY);

$datefields = array();
if ($model->showField('start_dt')) $datefields[] = 'ReportForm_start_dt';
if ($model->showField('end_dt')) $datefields[] = 'ReportForm_end_dt';
if ($model->showField('target_dt')) $datefields[] = 'ReportForm_target_dt';
if (!empty($datefields)) {
	$js = Script::genDatePicker($datefields);
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

