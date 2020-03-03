<?php
$this->pageTitle=Yii::app()->name . ' - Bank Balance Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'acctfile-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Bank Balance Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('acctfile/index'))); 
		?>
<?php if (!$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Save'), array(
			'submit'=>Yii::app()->createUrl('acctfile/save'),)); 
		?>
<?php endif ?>
<?php if (!$model->isReadOnly() && $model->lcd!=$model->lud) : ?>
		<?php echo TbHtml::button('<span class="fa fa-envelope"></span> '.Yii::t('trans','Email'), array(
			'submit'=>Yii::app()->createUrl('acctfile/send'))); 
		?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'lcd'); ?>
			<?php echo $form->hiddenField($model, 'lud'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
			<?php echo $form->hiddenField($model, 'mail_dt'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'year_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'year_no', 
						array('size'=>10,'readonly'=>true)
					); ?>
				</div>
				<?php echo $form->labelEx($model,'month_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'month_no', 
						array('size'=>10,'readonly'=>true)
					); ?>
				</div>
			</div>
	
			<div class="form-group">
				<?php echo $form->labelEx($model,'city_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'city_name', 
						array('size'=>10,'readonly'=>true)
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'remarks', 
					array('rows'=>2,'cols'=>60,'readonly'=>$model->isReadOnly())
					); ?>
				</div>
			</div>
			<legend><?php echo Yii::t('trans','Files'); ?></legend>
	
	<div class="col-sm-2">
	<?php 
		$counter = ($model->no_of_attm['acctfile1'] > 0) ? ' <span id="docacctfile1" class="label label-info">'.$model->no_of_attm['acctfile1'].'</span>' : ' <span id="docacctfile1"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','General AC').$counter, array(
			'name'=>'btnAcctfile1','id'=>'btnAcctfile1','data-toggle'=>'modal','data-target'=>'#fileuploadacctfile1',)
		);
	?>
	</div>
	<div class="col-sm-2">
	<?php 
		$counter = ($model->no_of_attm['acctfile2'] > 0) ? ' <span id="docacctfile2" class="label label-info">'.$model->no_of_attm['acctfile2'].'</span>' : ' <span id="docacctfile2"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Basic AC').$counter, array(
			'name'=>'btnAcctfile2','id'=>'btnAcctfile2','data-toggle'=>'modal','data-target'=>'#fileuploadacctfile2',)
		);
	?>
	</div>
	<div class="col-sm-2">
	<?php 
		$counter = ($model->no_of_attm['acctfile3'] > 0) ? ' <span id="docacctfile3" class="label label-info">'.$model->no_of_attm['acctfile3'].'</span>' : ' <span id="docacctfile3"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Other AC').$counter, array(
			'name'=>'btnAcctfile3','id'=>'btnAcctfile3','data-toggle'=>'modal','data-target'=>'#fileuploadacctfile3',)
		);
	?>
	</div>
	<div class="col-sm-2">
	<?php 
		$counter = ($model->no_of_attm['acctfile4'] > 0) ? ' <span id="docacctfile4" class="label label-info">'.$model->no_of_attm['acctfile4'].'</span>' : ' <span id="docacctfile4"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Other Files').$counter, array(
			'name'=>'btnAcctfile4','id'=>'btnAcctfile4','data-toggle'=>'modal','data-target'=>'#fileuploadacctfile4',)
		);
	?>
	</div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'ACCTFILE1',
													'header'=>Yii::t('trans','General AC'),
													'ronly'=>$model->isReadOnly(),
													)); 
?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'ACCTFILE2',
													'header'=>Yii::t('trans','Basic AC'),
													'ronly'=>$model->isReadOnly(),
													)); 
?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'ACCTFILE3',
													'header'=>Yii::t('trans','Other AC'),
													'ronly'=>$model->isReadOnly(),
													)); 
?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'ACCTFILE4',
													'header'=>Yii::t('trans','Other Files'),
													'ronly'=>$model->isReadOnly(),
													)); 
?>

<?php
Script::genFileUpload($model,$form->id,'ACCTFILE1');
Script::genFileUpload($model,$form->id,'ACCTFILE2');
Script::genFileUpload($model,$form->id,'ACCTFILE3');
Script::genFileUpload($model,$form->id,'ACCTFILE4');

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


