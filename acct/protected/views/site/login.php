<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Login';
?>

<div class="login-box">
	<div class="login-logo">
		<p><img src="<?php echo Yii::app()->baseUrl."/images/company.png";?>"></p>
		<b><?php echo CHtml::encode(Yii::t('app',Yii::app()->name)); ?></b>
		<p><small>(<?php echo ServerLoc::location(); ?>)</small></p>
	</div>
	
	<!-- /.login-logo -->
	<div class="login-box-body">
<?php $form=$this->beginWidget('TbActiveForm', array(
	'id'=>'login-form',
	'enableClientValidation'=>true,
	'clientOptions'=>array(
	'validateOnSubmit'=>true,
	'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
	),
)); ?>
		<div class="form-group has-feedback">
			<?php echo $form->textField($model,'username',
				array('placeholder'=>Yii::t('user','User ID'),'class'=>'form-control')); 
			?>			
			<span class="glyphicon glyphicon-user form-control-feedback"></span>
		</div>
		<div class="form-group has-feedback">
			<?php echo $form->passwordField($model,'password',
				array('placeholder'=>Yii::t('user','Password'),'class'=>'form-control')); 
			?>
			<span class="glyphicon glyphicon-lock form-control-feedback"></span>
		</div>
		<div class="row">
			<div class="col-xs-4 pull-right">
				<?php echo TbHtml::submitButton(Yii::t('misc','Login'),
					array('class'=>'btn bg-blue btn-block',)); 
				?>
			</div>
		</div>

		<div class="row">
		<p>&nbsp;</p>
		<p class="text-center">
			<small>
				<?php echo Yii::t('misc','**Please use Firefox web browser in order to use LBS Daily Management System for best result'); ?>
			</small>
		</p>
		</div>
			<!-- /.col -->

<?php $this->endWidget(); ?>
	</div>
	<!-- /.login-box-body -->
</div>

