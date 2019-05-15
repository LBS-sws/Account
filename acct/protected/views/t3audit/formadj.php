<?php
$this->pageTitle=Yii::app()->name . ' - Balance Adjustment Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'balance-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Balance Adjustment Form'); ?></strong>
	</h1>
</section>

<?php
	$currcode = City::getCurrency($model->city);
	$sign = Currency::getSign($currcode); 
	$modelName = get_class($model);
?>
<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('t3audit/back', array('index'=>$index)))); 
		?>
	</div>
	</div></div>

	<div class="box"><div class="box-body">
		<?php echo $form->hiddenField($model, 'scenario'); ?>
		<?php echo $form->hiddenField($model,'id'); ?>
		<?php echo $form->hiddenField($model,'audit_dt'); ?>
		<?php echo $form->hiddenField($model,'req_dt'); ?>
		<?php echo $form->hiddenField($model,'req_user'); ?>
		<?php echo $form->hiddenField($model,'req_user_name'); ?>
		<?php echo $form->hiddenField($model,'audit_user'); ?>
		<?php echo $form->hiddenField($model,'audit_user_name'); ?>
		<?php echo $form->hiddenField($model,'audit_user_pwd'); ?>
		<?php echo $form->hiddenField($model,'city'); ?>
		<?php echo $form->hiddenField($model,'city_name'); ?>
		<?php echo $form->hiddenField($model,'remarks'); ?>
		<?php echo $form->hiddenField($model,'bal_diff'); ?>
		<?php echo $form->hiddenField($model,'files'); ?>
<?php 
	foreach ($model->docMasterId as $key=>$value) {
		$fldname = $modelName.'[docMasterId]['.$key.']';
		echo TbHtml::hiddenField($fldname,$value); 
	}
	foreach ($model->removeFileId as $key=>$value) {
		$fldname = $modelName.'[removeFileId]['.$key.']';
		echo TbHtml::hiddenField($fldname,$value); 
	}
	foreach ($model->no_of_attm as $key=>$value) {
		$fldname = $modelName.'[no_of_attm]['.$key.']';
		echo TbHtml::hiddenField($fldname,$value); 
	}
	foreach ($model->record as $i=>$data) {
		$name_prefix = $modelName.'[record]['.$i.']';
		echo TbHtml::hiddenField($name_prefix.'[acct_id]',$data['acct_id']);
		echo TbHtml::hiddenField($name_prefix.'[acct_name]',$data['acct_name']);
		echo TbHtml::hiddenField($name_prefix.'[bank_name]',$data['bank_name']);
		echo TbHtml::hiddenField($name_prefix.'[acct_type_desc]',$data['acct_type_desc']);
		echo TbHtml::hiddenField($name_prefix.'[bal_month_end]',$data['bal_month_end']);
		echo TbHtml::hiddenField($name_prefix.'[bal_adj_id]',$data['bal_adj_id']);
		if ($index!=$i) {
			echo TbHtml::hiddenField($name_prefix.'[bal_t3]',$data['bal_t3']);
			echo TbHtml::hiddenField($name_prefix.'[bal_lbs]',$data['bal_lbs']);
			echo TbHtml::hiddenField($name_prefix.'[bal_adj]',$data['bal_adj']);
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
		}
	}
?>

		<?php echo CHtml::hiddenField('dtltemplateT'); ?>
		<?php echo CHtml::hiddenField('dtltemplateL'); ?>

		<div class="form-group">
			<?php echo $form->label($model,'audit_year',array('class'=>"col-sm-1 control-label")); ?>
			<div class="col-sm-1">
					<?php echo $form->textField($model, 'audit_year', array('readonly'=>true)); ?>
			</div>
			<?php echo $form->label($model,'audit_month',array('class'=>"col-sm-1 control-label")); ?>
			<div class="col-sm-1">
					<?php echo $form->textField($model, 'audit_month', array('readonly'=>true)); ?>
			</div>
			<?php echo $form->label($model,'acct_name',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-5">
					<?php 
						$nameX = '('.$model->record[$index]['acct_type_desc'].') '
							.$model->record[$index]['acct_name']
							.(empty($model->record[$index]['bank_name']) ? '' : ' - ').$model->record[$index]['bank_name'];
						echo TbHtml::textField('acct_name_x', $nameX, array('readonly'=>true)); 
					?>
			</div>
		</div>
	</div></div>
		
	<div class="box"><div class="box-body">
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'bal_t3', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'record['.$index.'][bal_t3]', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_tr_lnr', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'record['.$index.'][tot_tr_lnr]', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_tp_lnp', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'record['.$index.'][tot_tp_lnp]', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_adj_t', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'record['.$index.'][tot_adj_t]', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
	</div></div>

	<div class="box"><div class="box-body">
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'bal_lbs', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'record['.$index.'][bal_lbs]', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_lr_tnr', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'record['.$index.'][tot_lr_tnr]', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_lp_tnp', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'record['.$index.'][tot_lp_tnp]', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_adj_l', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'record['.$index.'][tot_adj_l]', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
	</div></div>
	
	<div class="box"><div class="box-body">
		<div class="form-group">
			<?php echo $form->label($model,'bal_adj',array('class'=>"col-sm-3 control-label")); ?>
			<div class="col-md-3">
				<?php echo $form->numberField($model, 'record['.$index.'][bal_adj]', array('readonly'=>$model->isReadOnly(),)); ?>
			</div>
		</div>
	</div></div>

	<div class="box"><div class="box-body table-responsive">
		<legend><?php echo Yii::t('trans','T3 Items'); ?></legend>
		<?php $this->widget('ext.layout.TableView2Widget', array(
				'model'=>$model,
				'attribute'=>"record[$index][t3record]",
				'viewhdr'=>'//t3audit/_formhdr1',
				'viewdtl'=>'//t3audit/_formdtl1',
				'tableidx'=>'T',
			));
		?>
	</div></div>			

	<div class="box"><div class="box-body table-responsive">
		<legend><?php echo Yii::t('trans','LBS Items'); ?></legend>
		<?php $this->widget('ext.layout.TableView2Widget', array(
				'model'=>$model,
				'attribute'=>"record[$index][lbsrecord]",
				'viewhdr'=>'//t3audit/_formhdr2',
				'viewdtl'=>'//t3audit/_formdtl2',
				'tableidx'=>'L',
			));
		?>
	</div></div>			

</section>

<?php
$js = "
$('table').on('change','[id^=\"T3AuditForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#T3AuditForm_'+n[1]+'_'+n[2]+'_'+n[3]+'_'+n[4]+'_uflag').val('Y');
	
	if (n[5]=='amount' || n[5]=='adjtype') {
		var amtorig = 0;
		var amtadj = 0;
		var amtrec = 0;
		var amtpaid = 0;
		var suffix = n[3]=='lbsrecord' ? 'L' : 'T';
		$('#tblDetail'+suffix+' tr').each(function() {
			var uflag = $(this).find('[id*=\"_uflag\"]').val();
			var type = $(this).find('[id*=\"_adjtype\"]').val();
			var amount = $(this).find('[id*=\"_amount\"]').val();
			if (type!='' && $.isNumeric(amount)) {
				if (type=='L1' || type=='T1') amtrec += uflag=='D' ? 0 : parseFloat(amount);
				if (type=='L2' || type=='T2') amtpaid += uflag=='D' ? 0 : parseFloat(amount);
			}
		});
		if (n[3]=='lbsrecord') {
			amtorig = $('#T3AuditForm_record_".$index."_bal_lbs').val();
			amtadj = parseFloat(amtorig) - parseFloat(amtrec) + parseFloat(amtpaid);
			$('#T3AuditForm_record_".$index."_tot_lr_tnr').val(amtrec.toFixed(2));
			$('#T3AuditForm_record_".$index."_tot_lp_tnp').val(amtpaid.toFixed(2));
			$('#T3AuditForm_record_".$index."_tot_adj_l').val(amtadj.toFixed(2));
		} else {
			amtorig = $('#T3AuditForm_record_".$index."_bal_t3').val();
			amtadj = parseFloat(amtorig) - parseFloat(amtrec) + parseFloat(amtpaid);
			$('#T3AuditForm_record_".$index."_tot_tr_lnr').val(amtrec.toFixed(2));
			$('#T3AuditForm_record_".$index."_tot_tp_lnp').val(amtpaid.toFixed(2));
			$('#T3AuditForm_record_".$index."_tot_adj_t').val(amtadj.toFixed(2));
		}
	}
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);

if (!$model->isReadOnly()) {
	$js = "
$('table').on('click','#btnDelRow', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
	
	var type = $(this).closest('tr').find('[id*=\"_adjtype\"]').val();
	var amount = $(this).closest('tr').find('[id*=\"_amount\"]').val();
	if ($.isNumeric(amount)) {
		switch(type) {
			case 'L1':
				$('#T3AuditForm_record_".$index."_tot_adj_l').val((parseFloat(+$('#T3AuditForm_record_".$index."_tot_adj_l').val())+parseFloat(amount)).toFixed(2));
				$('#T3AuditForm_record_".$index."_tot_lr_tnr').val((parseFloat(+$('#T3AuditForm_record_".$index."_tot_lr_tnr').val())-parseFloat(amount)).toFixed(2));
				break;
			case 'L2':
				$('#T3AuditForm_record_".$index."_tot_adj_l').val((parseFloat(+$('#T3AuditForm_record_".$index."_tot_adj_l').val())-parseFloat(amount)).toFixed(2));
				$('#T3AuditForm_record_".$index."_tot_lp_tnp').val((parseFloat(+$('#T3AuditForm_record_".$index."_tot_lp_tnp').val())-parseFloat(amount)).toFixed(2));
				break;
			case 'T1':
				$('#T3AuditForm_record_".$index."_tot_adj_t').val((parseFloat(+$('#T3AuditForm_record_".$index."_tot_adj_t').val())+parseFloat(amount)).toFixed(2));
				$('#T3AuditForm_record_".$index."_tot_tr_lnr').val((parseFloat(+$('#T3AuditForm_record_".$index."_tot_tr_lnr').val())-parseFloat(amount)).toFixed(2));
				break;
			case 'T2':
				$('#T3AuditForm_record_".$index."_tot_adj_t').val((parseFloat(+$('#T3AuditForm_record_".$index."_tot_adj_t').val())-parseFloat(amount)).toFixed(2));
				$('#T3AuditForm_record_".$index."_tot_tp_lnp').val((parseFloat(+$('#T3AuditForm_record_".$index."_tot_tp_lnp').val())-parseFloat(amount)).toFixed(2));
				break;
		}
	}
});
	";
	Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);

	$js = "
$(document).ready(function(){
	var ct1 = $('#tblDetailT tr').eq(1).html();
	$('#dtltemplateT').attr('value',ct1);

	var ct2 = $('#tblDetailL tr').eq(1).html();
	$('#dtltemplateL').attr('value',ct2);

	$('#T3AuditForm_record_".$index."_bal_t3').val(parseFloat(+$('#T3AuditForm_record_".$index."_bal_t3').val()).toFixed(2));
	$('#T3AuditForm_record_".$index."_tot_tr_lnr').val(parseFloat(+$('#T3AuditForm_record_".$index."_tot_tr_lnr').val()).toFixed(2));
	$('#T3AuditForm_record_".$index."_tot_tp_lnp').val(parseFloat(+$('#T3AuditForm_record_".$index."_tot_tp_lnp').val()).toFixed(2));
	$('#T3AuditForm_record_".$index."_tot_adj_t').val(parseFloat(+$('#T3AuditForm_record_".$index."_tot_adj_t').val()).toFixed(2));

	$('#T3AuditForm_record_".$index."_bal_lbs').val(parseFloat(+$('#T3AuditForm_record_".$index."_bal_lbs').val()).toFixed(2));
	$('#T3AuditForm_record_".$index."_tot_lr_tnr').val(parseFloat(+$('#T3AuditForm_record_".$index."_tot_lr_tnr').val()).toFixed(2));
	$('#T3AuditForm_record_".$index."_tot_lp_tnp').val(parseFloat(+$('#T3AuditForm_record_".$index."_tot_lp_tnp').val()).toFixed(2));
	$('#T3AuditForm_record_".$index."_tot_adj_l').val(parseFloat(+$('#T3AuditForm_record_".$index."_tot_adj_l').val()).toFixed(2));
	
	$('#tblDetailT tr').each(function() {
		var uflag = $(this).find('[id*=\"_uflag\"]').val();
		if (uflag=='D') $(this).hide();
	});

	$('#tblDetailL tr').each(function() {
		var uflag = $(this).find('[id*=\"_uflag\"]').val();
		if (uflag=='D') $(this).hide();
	});
});

$('#btnAddRowT').on('click',function() {
	var r = $('#tblDetailT tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplateT').val();
		$('#tblDetailT tbody:last').append('<tr>'+ct+'</tr>');
		$('#tblDetailT tr').eq(-1).find('[id*=\"T3AuditForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_t3record_'+oi.toString()+'_', '_t3record_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('[t3record]['+oi.toString()+']', '[t3record]['+ni.toString()+']');
			$(this).attr('name',name);
			if (id.indexOf('_id') != -1) $(this).attr('value','0');
			if (id.indexOf('_adjtype') != -1) $(this).val('');
//			if (id.indexOf('_adjtype') != -1) $(this).attr('value','');
			if (id.indexOf('_amount') != -1) $(this).attr('value','');
			if (id.indexOf('_remarks') != -1) $(this).attr('value','');
		});
	}
});

$('#btnAddRowL').on('click',function() {
	var r = $('#tblDetailL tr').length;
	if (r>0) {
		var ct = $('#dtltemplateL').val();
		$('#tblDetailL tbody:last').append('<tr>'+ct+'</tr>');
		$('#tblDetailL tr').eq(-1).find('[id*=\"T3AuditForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_lbsrecord_'+oi.toString()+'_', '_lbsrecord_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('[lbsrecord]['+oi.toString()+']', '[lbsrecord]['+ni.toString()+']');
			$(this).attr('name',name);
			if (id.indexOf('_id') != -1) $(this).attr('value','0');
			if (id.indexOf('_adjtype') != -1) $(this).val('');
//			if (id.indexOf('_adjtype') != -1) $(this).attr('value','');
			if (id.indexOf('_amount') != -1) $(this).attr('value','');
			if (id.indexOf('_remarks') != -1) $(this).attr('value','');
		});
	}
});
";
	Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
}
	
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>
