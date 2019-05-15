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
?>
<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('baladj/index'))); 
		?>
<?php if (!$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Save'), array(
			'submit'=>Yii::app()->createUrl('baladj/save'),)); 
		?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box"><div class="box-body">
		<?php echo $form->hiddenField($model, 'scenario'); ?>
		<?php echo $form->hiddenField($model,'acct_id'); ?>
		<?php echo $form->hiddenField($model,'city'); ?>
		<?php echo $form->hiddenField($model,'bal_adj_id'); ?>
		<?php echo CHtml::hiddenField('dtltemplateT'); ?>
		<?php echo CHtml::hiddenField('dtltemplateL'); ?>

		<div class="form-group">
			<?php echo $form->label($model,'audit_year',array('class'=>"col-sm-1 control-label")); ?>
			<div class="col-sm-1">
					<?php echo $form->textField($model, 'audit_year', 
						array('readonly'=>true
						)); 
					?>
			</div>
			<?php echo $form->label($model,'audit_month',array('class'=>"col-sm-1 control-label")); ?>
			<div class="col-sm-1">
					<?php echo $form->textField($model, 'audit_month', 
						array('readonly'=>true
						)); 
					?>
			</div>
			<?php echo $form->label($model,'acct_name',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-5">
					<?php echo $form->textField($model, 'acct_name', 
						array('readonly'=>true
						)); 
					?>
			</div>
		</div>
	</div></div>
		
	<div class="box"><div class="box-body">
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'bal_t3', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'bal_t3', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_tr_lnr', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'tot_tr_lnr', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_tp_lnp', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'tot_tp_lnp', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_adj_t', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'tot_adj_t', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
	</div></div>

	<div class="box"><div class="box-body">
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'bal_lbs', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'bal_lbs', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_lr_tnr', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'tot_lr_tnr', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_lp_tnp', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'tot_lp_tnp', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
		<div class="col-md-3">
			<div class="form-group">
				<?php echo $form->labelEx($model,'tot_adj_l', array('class'=>'col-sm-8')); ?>
				<?php echo $form->textField($model, 'tot_adj_l', array('readonly'=>true, 'class'=>'form-control',)); ?>
			</div>
		</div>
	</div></div>
	
	<div class="box"><div class="box-body">
		<div class="form-group">
			<?php echo $form->label($model,'bal_adj',array('class'=>"col-sm-3 control-label")); ?>
			<div class="col-md-3">
				<?php echo $form->numberField($model, 'bal_adj', array('readonly'=>$model->isReadOnly(),)); ?>
			</div>
		</div>
	</div></div>

	<div class="box"><div class="box-body table-responsive">
		<legend><?php echo Yii::t('trans','T3 Items'); ?></legend>
		<?php $this->widget('ext.layout.TableView2Widget', array(
				'model'=>$model,
				'attribute'=>'t3record',
				'viewhdr'=>'//baladj/_formhdr1',
				'viewdtl'=>'//baladj/_formdtl1',
				'tableidx'=>'T',
			));
		?>
	</div></div>			

	<div class="box"><div class="box-body table-responsive">
		<legend><?php echo Yii::t('trans','LBS Items'); ?></legend>
		<?php $this->widget('ext.layout.TableView2Widget', array(
				'model'=>$model,
				'attribute'=>'lbsrecord',
				'viewhdr'=>'//baladj/_formhdr2',
				'viewdtl'=>'//baladj/_formdtl2',
				'tableidx'=>'L',
			));
		?>
	</div></div>			

</section>

<?php
$js = "
$('table').on('change','[id^=\"BalAdjForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#BalAdjForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
	
	if (n[3]=='amount' || n[3]=='adjtype') {
		var amtorig = 0;
		var amtadj = 0;
		var amtrec = 0;
		var amtpaid = 0;
		var suffix = n[1]=='lbsrecord' ? 'L' : 'T';
		$('#tblDetail'+suffix+' tr').each(function() {
			var uflag = $(this).find('[id*=\"_uflag\"]').val();
			var type = $(this).find('[id*=\"_adjtype\"]').val();
			var amount = $(this).find('[id*=\"_amount\"]').val();
			if (type!='' && $.isNumeric(amount)) {
				if (type=='L1' || type=='T1') amtrec += uflag=='D' ? 0 : parseFloat(amount);
				if (type=='L2' || type=='T2') amtpaid += uflag=='D' ? 0 : parseFloat(amount);
			}
		});
		if (n[1]=='lbsrecord') {
			amtorig = $('#BalAdjForm_bal_lbs').val();
			amtadj = parseFloat(amtorig) + parseFloat(amtrec) - parseFloat(amtpaid);
			$('#BalAdjForm_tot_lr_tnr').val(amtrec.toFixed(2));
			$('#BalAdjForm_tot_lp_tnp').val(amtpaid.toFixed(2));
			$('#BalAdjForm_tot_adj_l').val(amtadj.toFixed(2));
		} else {
			amtorig = $('#BalAdjForm_bal_t3').val();
			amtadj = parseFloat(amtorig) + parseFloat(amtrec) - parseFloat(amtpaid);
			$('#BalAdjForm_tot_tr_lnr').val(amtrec.toFixed(2));
			$('#BalAdjForm_tot_tp_lnp').val(amtpaid.toFixed(2));
			$('#BalAdjForm_tot_adj_t').val(amtadj.toFixed(2));
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
				$('#BalAdjForm_tot_adj_l').val((parseFloat(+$('#BalAdjForm_tot_adj_l').val())-parseFloat(amount)).toFixed(2));
				$('#BalAdjForm_tot_lr_tnr').val((parseFloat(+$('#BalAdjForm_tot_lr_tnr').val())-parseFloat(amount)).toFixed(2));
				break;
			case 'L2':
				$('#BalAdjForm_tot_adj_l').val((parseFloat(+$('#BalAdjForm_tot_adj_l').val())+parseFloat(amount)).toFixed(2));
				$('#BalAdjForm_tot_lp_tnp').val((parseFloat(+$('#BalAdjForm_tot_lp_tnp').val())-parseFloat(amount)).toFixed(2));
				break;
			case 'T1':
				$('#BalAdjForm_tot_adj_t').val((parseFloat(+$('#BalAdjForm_tot_adj_t').val())-parseFloat(amount)).toFixed(2));
				$('#BalAdjForm_tot_tr_lnr').val((parseFloat(+$('#BalAdjForm_tot_tr_lnr').val())-parseFloat(amount)).toFixed(2));
				break;
			case 'T2':
				$('#BalAdjForm_tot_adj_t').val((parseFloat(+$('#BalAdjForm_tot_adj_t').val())+parseFloat(amount)).toFixed(2));
				$('#BalAdjForm_tot_tp_lnp').val((parseFloat(+$('#BalAdjForm_tot_tp_lnp').val())-parseFloat(amount)).toFixed(2));
				break;
		}
	}
});
	";
	Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);

	$js = <<<EOF
$(document).ready(function(){
	var ct1 = $('#tblDetailT tr').eq(1).html();
	$('#dtltemplateT').attr('value',ct1);

	var ct2 = $('#tblDetailL tr').eq(1).html();
	$('#dtltemplateL').attr('value',ct2);

	$('#BalAdjForm_bal_t3').val(parseFloat(+$('#BalAdjForm_bal_t3').val()).toFixed(2));
	$('#BalAdjForm_tot_tr_lnr').val(parseFloat(+$('#BalAdjForm_tot_tr_lnr').val()).toFixed(2));
	$('#BalAdjForm_tot_tp_lnp').val(parseFloat(+$('#BalAdjForm_tot_tp_lnp').val()).toFixed(2));
	$('#BalAdjForm_tot_adj_t').val(parseFloat(+$('#BalAdjForm_tot_adj_t').val()).toFixed(2));

	$('#BalAdjForm_bal_lbs').val(parseFloat(+$('#BalAdjForm_bal_lbs').val()).toFixed(2));
	$('#BalAdjForm_tot_lr_tnr').val(parseFloat(+$('#BalAdjForm_tot_lr_tnr').val()).toFixed(2));
	$('#BalAdjForm_tot_lp_tnp').val(parseFloat(+$('#BalAdjForm_tot_lp_tnp').val()).toFixed(2));
	$('#BalAdjForm_tot_adj_l').val(parseFloat(+$('#BalAdjForm_tot_adj_l').val()).toFixed(2));
	
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
		$('#tblDetailT tr').eq(-1).find('[id*=\"BalAdjForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);
			if (id.indexOf('_id') != -1) $(this).attr('value','0');
			if (id.indexOf('_adjtype') != -1) $(this).attr('value','');
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
		$('#tblDetailL tr').eq(-1).find('[id*=\"BalAdjForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);
			if (id.indexOf('_id') != -1) $(this).attr('value','0');
			if (id.indexOf('_adjtype') != -1) $(this).val('');
//			if (id.indexOf('_adjtype') != -1) $(this).attr('value','');
			if (id.indexOf('_amount') != -1) $(this).attr('value','');
			if (id.indexOf('_remarks') != -1) $(this).attr('value','');
		});
	}
});
EOF;
	Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
}
	
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>
