<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Language';
?>

<div class="login-box">
	<div class="login-box-body">
		<div class="row">
			<h2 class="page-heading text-center"><?php echo Yii::t('app','Languages'); ?></h2>
		</div>

		<?php $form=$this->beginWidget('TbActiveForm', array(
			'id'=>'language-form',
			'enableClientValidation'=>true,
			'clientOptions'=>array(
			'validateOnSubmit'=>true,
			'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
			),
		)); ?>

		<div class="row">
			<div class="form-group">
				<?php echo $form->label($model,'language',array('class'=>"col-sm-3 control-label")); ?>
				<div class="col-sm-5">
					<?php echo $form->dropDownList($model, 'language', 
						array('zh_cn'=>'中文(简)','zh_tw'=>'中文(繁)','en'=>'English')
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

		<?php $this->endWidget(); ?>
	</div><!-- form -->
</div>
