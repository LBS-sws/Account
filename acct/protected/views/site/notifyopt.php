<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Notification Option';
?>

<div class="login-box">
	<div class="login-box-body">
		<div class="row">
			<h2 class="page-heading text-center"><?php echo Yii::t('app','Notification Option'); ?></h2>
		</div>

		<?php $form=$this->beginWidget('TbActiveForm', array(
			'id'=>'notifyopt-form',
			'enableClientValidation'=>true,
			'clientOptions'=>array(
			'validateOnSubmit'=>true,
			'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
			),
		)); ?>

		<div class="row">
			<div class="form-group">
				<?php echo $form->label($model,'status',array('class'=>"col-sm-3 control-label")); ?>
				<?php echo $form->hiddenfield($model, 'username'); ?>
				<div class="col-sm-5">
					<?php echo $form->dropDownList($model, 'status', 
						array('Y'=>Yii::t('misc','On'),'N'=>Yii::t('misc','Off'))
						); 
					?>
				</div>
			</div>
		</div>
		<div class="row">&nbsp;</div>
		<div class="row">
			<div class="col-xs-4 pull-right">
				<?php echo TbHtml::submitButton(Yii::t('dialog','OK'),
					array('class'=>'btn btn-default btn-block',)); 
				?>
			</div>
		</div>

		<?php $this->endWidget(); ?>
	</div><!-- form -->
</div>
