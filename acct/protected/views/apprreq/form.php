<?php
$this->pageTitle=Yii::app()->name . ' - Payment Request Approval Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'transin-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Payment Request Approval Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('apprreq/index'))); 
		?>
		<?php echo TbHtml::button('<span class="fa fa-check"></span> '.Yii::t('misc','Approve'), array(
			'submit'=>Yii::app()->createUrl('apprreq/approve')));
		?>
		<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Deny'), array(
			'id'=>'btnDeny', 'name'=>'btnDeny'));
		?>
	</div>
	<div class="btn-group pull-right" role="group">
	<?php 
		$counter = ($model->no_of_attm['payreq'] > 0) ? ' <span id="docpayreq" class="label label-info">'.$model->no_of_attm['payreq'].'</span>' : ' <span id="docpayreq"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
			'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadpayreq',)
		);
	?>
	<?php 
		$counter = ($model->no_of_attm['tax'] > 0) ? ' <span id="doctax" class="label label-info">'.$model->no_of_attm['tax'].'</span>' : ' <span id="doctax"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Tax Slip').$counter, array(
			'name'=>'btnFileTS','id'=>'btnFileTS','data-toggle'=>'modal','data-target'=>'#fileuploadtax',)
		);
	?>
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
			<?php echo $form->hiddenField($model, 'status'); ?>
			<?php echo $form->hiddenField($model, 'wfstatus'); ?>
			<?php echo $form->hiddenField($model, 'req_user'); ?>
			<?php echo $form->hiddenField($model, 'type'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'ref_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'ref_no', 
						array('readonly'=>true,	)); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'user_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php echo $form->textField($model, 'user_name', 
						array('readonly'=>true,	)); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'req_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'req_dt', 
							array('class'=>'form-control pull-right',
								'readonly'=>true,
							)); 
						?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_type_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model, 'trans_type_code', General::getTransTypeList('OUT'),array('disabled'=>($model->isReadOnly()))); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model, 'acct_id', General::getAccountList($model->city),array('disabled'=>($model->isReadOnly()))); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'payee_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->dropDownList($model, 'payee_type', 
							array('C'=>Yii::t('trans','Client'),'S'=>Yii::t('trans','Supplier'),'A'=>Yii::t('trans','Company A/C'),'F'=>Yii::t('trans','Staff'),'O'=>Yii::t('trans','Others')),
							array('disabled'=>($model->isReadOnly()))
					); ?>
				</div>
				<div class="col-sm-7">
					<?php 
						echo $form->textField($model, 'payee_name', 
							array('size'=>60,'maxlength'=>500,'readonly'=>($model->isReadOnly()||$model->payee_type!='O'),
						)); 
						echo $form->hiddenField($model, 'payee_id');
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'pitem_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						echo $form->hiddenField($model, 'item_code');
						echo $form->textField($model, 'pitem_desc', 
							array('maxlength'=>500,'readonly'=>true,
							)
						); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'acct_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php 
						echo $form->hiddenField($model, 'acct_code');
						echo $form->textField($model, 'acct_code_desc', 
							array('maxlength'=>500,'readonly'=>true,)
						); 
					?>
				</div>

				<?php echo $form->labelEx($model,'int_fee',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-1">
					<?php 
						$list = array(''=>Yii::t('misc','No'),'N'=>Yii::t('misc','No'),'Y'=>Yii::t('misc','Yes'));
						echo TbHtml::textField('int_fee', $list[$model->int_fee], array('readonly'=>true,)); 
					?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'item_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textArea($model, 'item_desc', 
						array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->isReadOnly()))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'amount',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php
						echo $form->numberField($model, 'amount', 
							array('size'=>10,'min'=>0,
							'readonly'=>($model->isReadOnly()),
							'prepend'=>'<span class="fa '.$sign.'"></span>')
						); 
					?>
				</div>
			</div>

<?php if (!empty($model->wfstatus)): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'wfstatusdesc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'wfstatusdesc', 
						array('readonly'=>true,	)); 
					?>
				</div>
			</div>
<?php endif ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'PAYREQ',
													'header'=>Yii::t('dialog','File Attachment'),
													'ronly'=>true,
													)); 
?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'TAX',
													'header'=>Yii::t('trans','Tax Slip'),
													'ronly'=>true,
													)); 
?>
<?php $this->renderPartial('//apprreq/reason',array('model'=>$model,'form'=>$form)); ?>

<?php
Script::genFileUpload($model,$form->id,'PAYREQ');
Script::genFileUpload($model,$form->id,'TAX');

$js=<<<EOF
$('#btnDeny').on('click',function(){
	$('#rmkdialog').modal('show');
});
EOF;
Yii::app()->clientScript->registerScript('denyPopup',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


