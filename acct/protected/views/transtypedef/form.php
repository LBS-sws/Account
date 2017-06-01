<?php
$this->pageTitle=Yii::app()->name . ' - Default Account Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Default Account Form'); ?></strong>
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
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('transtypedef/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('transtypedef/save'))); 
			?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_type_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->hiddenField($model, 'trans_type_code'); ?>
					<?php echo $form->textField($model, 'trans_type_desc', 
						array('size'=>50,'maxlength'=>100,'readonly'=>true)
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_cat',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$list = array('IN'=>Yii::t('code','In'),'OUT'=>Yii::t('code','Out'));
						echo $form->hiddenField($model, 'trans_cat');
						echo TbHtml::textField('trans_cat', $list[$model->trans_cat],
							array('readonly'=>true)
						);
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						$list0 = array(0=>Yii::t('misc','-- None --'));
						$list1 = General::getAccountList();
						$list = $list0 + $list1;
						echo $form->dropDownList($model, 'acct_id', $list,array('disabled'=>($model->scenario=='view'))); 
					?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


