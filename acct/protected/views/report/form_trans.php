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
		<strong><?php echo Yii::t('report','Transaction List'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('report/translist'))); 
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
				<?php echo $form->labelEx($model,'trans_cat',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->dropDownList($model, 'trans_cat', 
						array('ALL'=>Yii::t('report','-- All --'), 'IN'=>Yii::t('code','In'),'OUT'=>Yii::t('code','Out')));
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						$list0 = array(0=>Yii::t('report','-- All --'));
						$citylist = City::model()->getDescendantList($model->city);
						$list1 = General::getAccountList($citylist);
						$list = $list0 + $list1;
						echo $form->dropDownList($model, 'acct_id', $list); 
					?>
				</div>
			</div>
			
		</div>
	</div>
</section>

<?php
$datefields = array();
if ($model->showField('start_dt')) $datefields[] = 'Report02Form_start_dt';
if ($model->showField('end_dt')) $datefields[] = 'Report02Form_end_dt';
if (!empty($datefields)) {
	$js = Script::genDatePicker($datefields);
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

