<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Change Password';
?>

<?php 
	$form=$this->beginWidget('TbActiveForm', array(
		'id'=>'password-form',
		'enableClientValidation'=>true,
		'clientOptions'=>array(
			'validateOnSubmit'=>true,
			'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
		),
	)); 
?>

<section class="content">
<div class="login-box">
	<div class="login-box-body">
		<h2 class="page-heading text-center"><?php echo Yii::t('misc','Change Password'); ?></h2>


		<div class="form-group has-feedback">
			<?php echo $form->passwordField($model,'oldPassword',
				array('placeholder'=>Yii::t('misc','Old Password'),'class'=>'form-control')); 
			?>
			<span class="glyphicon glyphicon-lock form-control-feedback"></span>
		</div>
		<div class="form-group has-feedback">
			<?php echo $form->passwordField($model,'newPassword',
				array('placeholder'=>Yii::t('misc','New Password'),'class'=>'form-control')); 
			?>
			<span class="glyphicon glyphicon-lock form-control-feedback"></span>
		</div>
		<div class="form-group has-feedback">
			<?php echo $form->passwordField($model,'confirmPassword',
				array('placeholder'=>Yii::t('misc','Confirm Password'),'class'=>'form-control')); 
			?>
			<span class="glyphicon glyphicon-lock form-control-feedback"></span>
		</div>
		<div class="row">
			<div class="col-xs-4 pull-right">
				<?php echo TbHtml::submitButton(Yii::t('dialog','OK'),
					array('class'=>'btn btn-default btn-block',)); 
				?>
			</div>
<!--			
			<div class="col-xs-4">
				<?php echo TbHtml::button(Yii::t('dialog','Cancel'),array(
					'class'=>'btn btn-default btn-block',
					'submit'=>Yii::app()->createUrl('site/home')
				)); ?>
			</div>
-->			
			<!-- /.col -->
		</div>

	</div><!-- form -->
</div>
</section>

<?php $this->endWidget(); ?>
