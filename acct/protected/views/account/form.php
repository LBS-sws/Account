<?php
$this->pageTitle=Yii::app()->name . ' - Account Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'account-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Account Form'); ?></strong>
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
		<?php 
			if ($model->scenario!='new' && !$model->isReadOnly()) {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('account/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('account/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('account/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit' && !$model->isReadOnly()): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
	</div></div>

<?php
	$currcode = City::getCurrency($model->city);
	$sign = Currency::getSign($currcode); 
?>
	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
			<?php echo $form->hiddenField($model, 'trans_city'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_type_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php
						if ($model->isReadOnly()) {
							$typelist = General::getAcctTypeList(true);
							echo $form->hiddenField($model, 'acct_type_id');
							echo TbHtml::textField('acct_type_id', $typelist[$model->acct_type_id], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'acct_type_id', General::getAcctTypeList(true)); 
						}
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'bank_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'bank_name', 
						array('size'=>50,'maxlength'=>255,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'acct_no', 
						array('size'=>50,'maxlength'=>255,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'acct_name', 
						array('size'=>50,'maxlength'=>255,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'coa',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'coa', 
						array('size'=>30,'maxlength'=>255,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'remarks',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'remarks', 
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'open_bal',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php
						echo $form->numberField($model, 'open_bal', 
							array('size'=>10,'min'=>0,
							'readonly'=>($model->scenario=='view'||($model->scenario!='new'&&$model->isOccupied($model->id))),
							'prepend'=>'<span class="fa '.$sign.'"></span>')
						); 
					?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'open_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'open_dt', 
							array('class'=>'form-control pull-right',
								'readonly'=>($model->scenario=='view'||($model->scenario!='new'&&$model->isOccupied($model->id))),
							)); 
						?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'status',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php
						$statuslist = array('Y'=>Yii::t('misc','Yes'), 'N'=>Yii::t('misc','No'));
						if ($model->isReadOnly()) {
							echo $form->hiddenField($model, 'status');
							echo TbHtml::textField('status_desc', $statuslist[$model->status], array('readonly'=>true));
						} else {
							echo $form->dropDownList($model, 'status', $statuslist); 
						}
					?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('account/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if ($model->scenario!='view'&&($model->scenario=='new'||!$model->isOccupied($model->id))) {
	$js = Script::genDatePicker(array(
			'AccountForm_open_dt',
		));
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


