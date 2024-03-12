<?php
$this->pageTitle=Yii::app()->name . ' - T3 Balance Checking Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'check-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','T3 Balance Checking Form'); ?></strong>
	</h1>
</section>

<?php
	$currcode = City::getCurrency($model->city);
	$sign = Currency::getSign($currcode); 
?>
<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('t3audit/index'))); 
		?>
<?php if (!$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Save'), array(
			'submit'=>Yii::app()->createUrl('t3audit/save'),)); 
		?>
		<?php echo TbHtml::button('<span class="fa fa-check"></span> '.Yii::t('misc','Confirm'), array(
			'id'=>'btnConfirm'));
		?>
<?php endif ?>
	</div>
	<div class="btn-group pull-right" role="group">
	<?php 
		$counter = ($model->no_of_attm['t3bal'] > 0) ? ' <span id="doct3bal" class="label label-info">'.$model->no_of_attm['t3bal'].'</span>' : ' <span id="doct3bal"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Bal. Screen').$counter, array(
			'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadt3bal',)
		);
	?>
	<?php 
		$counter = ($model->no_of_attm['t3cash'] > 0) ? ' <span id="doct3cash" class="label label-info">'.$model->no_of_attm['t3cash'].'</span>' : ' <span id="doct3cash"></span>';
		echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('trans','Cash Audit').$counter, array(
			'name'=>'btnFileC','id'=>'btnFileC','data-toggle'=>'modal','data-target'=>'#fileuploadt3cash',)
		);
	?>
	</div>
	</div></div>

	<div class="box"><div class="box-body">
		<?php echo $form->hiddenField($model, 'scenario'); ?>
		<?php echo $form->hiddenField($model, 'id'); ?>
		<?php echo $form->hiddenField($model, 'req_user'); ?>
		<?php //echo $form->hiddenField($model, 'audit_user'); ?>
		<?php echo $form->hiddenField($model, 'bal_diff'); ?>

		<div class="form-group">
			<?php echo $form->label($model,'audit_year',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
					<?php echo $form->textField($model, 'audit_year', 
						array('readonly'=>true
						)); 
					?>
			</div>
			<?php echo $form->label($model,'audit_month',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
					<?php echo $form->textField($model, 'audit_month', 
						array('readonly'=>true
						)); 
					?>
			</div>
		</div>
		
<?php if ($model->isReadOnly()): ?>
		<div class="form-group">
			<?php echo $form->label($model,'audit_dt',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
				<div class="input-group date">
					<div class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</div>
					<?php echo $form->textField($model, 'audit_dt', 
						array('class'=>'form-control pull-right','readonly'=>true
						)); 
					?>
				</div>
			</div>
		</div>

		<div class="form-group">
			<?php echo $form->label($model,'req_user_name',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
					<?php echo $form->textField($model, 'req_user_name', 
						array('readonly'=>true
						)); 
					?>
			</div>
			<?php echo $form->label($model,'audit_user_name',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
					<?php echo $form->textField($model, 'audit_user_name', 
						array('readonly'=>true
						)); 
					?>
			</div>
		</div>
<?php endif ?>

		<div class="form-group">
			<?php echo $form->labelEx($model,'remarks',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-7">
				<?php echo $form->textArea($model, 'remarks', 
					array('rows'=>3,'cols'=>60,'maxlength'=>1000,'readonly'=>($model->isReadOnly()),
						'placeholder'=>($model->isReadOnly() ? '' 
						:Yii::t('trans','Please state reason for balance difference (if any)'))
					)
				); ?>
			</div>
		</div>

		<legend>&nbsp;</legend>
	
<?php
	$modelName = get_class($model);
	$cnt=0;
	foreach ($model->record as $key=>$data) {
		$cnt++;
		$id_prefix = $modelName.'_record_'.$key;
		$name_prefix = $modelName.'[record]['.$key.']';
		echo '<div class="form-group">';
		echo '<div class="col-sm-4">';

//var_dump($model);
//if (!isset($data['acct_type_desc'])) {
//var_dump($data);
//Yii::app()->end();
//}
        $balance_test = key_exists("balance_test",$data)?$data["balance_test"]:0;
        $balance = key_exists("bal_month_end",$data)?$data["bal_month_end"]:0;
        $balance_class = floatval($balance)!=floatval($balance_test)?"has-error":"";
		$str = $cnt.'. ('.$data['acct_type_desc'].') '.$data['acct_name']
			.(empty($data['bank_name']) ? '' : ' - ').$data['bank_name'].' '.Yii::t('trans','Balance');
		echo  TbHtml::label($str,$id_prefix.'_bal_month_end');
		echo '</div>';
		echo "<div class=\"col-sm-2 {$balance_class}\">";
		echo TbHtml::numberField($name_prefix.'[bal_month_end]',$data['bal_month_end'],
				array('maxlength'=>100,'readonly'=>true,'data-balance'=>$balance_test)
			);		
		echo '</div>';
		echo '<div class="col-sm-2">';
		echo  TbHtml::label('T3 '.Yii::t('trans','Balance'),$id_prefix.'_bal_t3');
		echo '</div>';
		echo '<div class="col-sm-2">';
		echo TbHtml::numberField($name_prefix.'[bal_t3]',$data['bal_t3'],
				array('maxlength'=>100,'readonly'=>($model->isReadOnly()))
			);		
		echo TbHtml::hiddenField($name_prefix.'[acct_id]',$data['acct_id']);
		echo TbHtml::hiddenField($name_prefix.'[acct_name]',$data['acct_name']);
		echo TbHtml::hiddenField($name_prefix.'[bank_name]',$data['bank_name']);
		echo TbHtml::hiddenField($name_prefix.'[acct_type_desc]',$data['acct_type_desc']);
		echo TbHtml::hiddenField($name_prefix.'[bal_lbs]',$data['bal_lbs']);
		echo TbHtml::hiddenField($name_prefix.'[bal_adj]',$data['bal_adj']);
		echo TbHtml::hiddenField($name_prefix.'[bal_adj_id]',$data['bal_adj_id']);
		echo TbHtml::hiddenField($name_prefix.'[tot_tr_lnr]',$data['tot_tr_lnr']);
		echo TbHtml::hiddenField($name_prefix.'[tot_tp_lnp]',$data['tot_tp_lnp']);
		echo TbHtml::hiddenField($name_prefix.'[tot_lr_tnr]',$data['tot_lr_tnr']);
		echo TbHtml::hiddenField($name_prefix.'[tot_lp_tnp]',$data['tot_lp_tnp']);
		echo TbHtml::hiddenField($name_prefix.'[tot_adj_t]',$data['tot_adj_t']);
		echo TbHtml::hiddenField($name_prefix.'[tot_adj_l]',$data['tot_adj_l']);
		foreach ($data['t3record'] as $idx=>$row) {
			echo TbHtml::hiddenField($name_prefix.'[t3record]['.$idx.'][id]',$row['id']);
			echo TbHtml::hiddenField($name_prefix.'[t3record]['.$idx.'][adjtype]',$row['adjtype']);
			echo TbHtml::hiddenField($name_prefix.'[t3record]['.$idx.'][amount]',$row['amount']);
			echo TbHtml::hiddenField($name_prefix.'[t3record]['.$idx.'][remarks]',$row['remarks']);
			echo TbHtml::hiddenField($name_prefix.'[t3record]['.$idx.'][uflag]',$row['uflag']);
		}
		foreach ($data['lbsrecord'] as $idx=>$row) {
			echo TbHtml::hiddenField($name_prefix.'[lbsrecord]['.$idx.'][id]',$row['id']);
			echo TbHtml::hiddenField($name_prefix.'[lbsrecord]['.$idx.'][adjtype]',$row['adjtype']);
			echo TbHtml::hiddenField($name_prefix.'[lbsrecord]['.$idx.'][amount]',$row['amount']);
			echo TbHtml::hiddenField($name_prefix.'[lbsrecord]['.$idx.'][remarks]',$row['remarks']);
			echo TbHtml::hiddenField($name_prefix.'[lbsrecord]['.$idx.'][uflag]',$row['uflag']);
		}
		echo '</div>';

		echo '<div class="col-sm-2">';
		echo TbHtml::button(Yii::t('trans','Balance Adjustment'), array(
			'submit'=>Yii::app()->createUrl('t3audit/adjust', array('index'=>$key,)),));
		echo '</div>';
		echo '</div>';
	}
?>

	</div></div>
</section>

<?php $this->renderPartial('//t3audit/authen',array('model'=>$model,'form'=>$form)); ?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'T3BAL',
													'header'=>Yii::t('trans','Bal. Screen'),
													'ronly'=>($model->isReadOnly()),
													)); 
?>
<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'T3CASH',
													'header'=>Yii::t('trans','Cash Audit'),
													'ronly'=>($model->isReadOnly()),
													)); 
?>

<?php
Script::genFileUpload($model,$form->id,'T3BAL');
Script::genFileUpload($model,$form->id,'T3CASH');

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);

$mesg = Yii::t('trans','Please check balance record before proceed.');
$js = <<<EOF
$('#btnConfirm').on('click',function(){
	alert('$mesg');
	$('#authdialog').modal('show');
});
EOF;
Yii::app()->clientScript->registerScript('confirm',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


