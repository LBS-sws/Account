<?php
$this->pageTitle=Yii::app()->name . ' - Transaction Enquiry';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'enquiry-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Transaction Enquiry'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>

<?php
	$currcode = City::getCurrency($model->city);
	$sign = Currency::getSign($currcode); 
?>
<section class="content">
	<div class="box"><div class="box-body">
		<div class="form-group">
			<?php echo $form->labelEx($model,'fm_dt',array('class'=>"col-sm-1 control-label")); ?>
			<div class="col-sm-3">
				<div class="input-group date">
					<div class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</div>
					<?php echo $form->textField($model, 'fm_dt', 
						array('class'=>'form-control pull-right',
						)); 
					?>
				</div>
			</div>
			<?php echo $form->labelEx($model,'to_dt',array('class'=>"col-sm-1 control-label")); ?>
			<div class="col-sm-3">
				<div class="input-group date">
					<div class="input-group-addon">
						<i class="fa fa-calendar"></i>
					</div>
					<?php echo $form->textField($model, 'to_dt', 
						array('class'=>'form-control pull-right',
						)); 
					?>
				</div>
			</div>
			<div class="col-sm-3 pull-right">
				<?php 
					echo TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('misc','Search'),
					array('submit'=>Yii::app()->createUrl('transenq/index2',array('index'=>$model->acct_id,'city'=>$model->city,'pageNum'=>1)),)); 
				?>
				<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
					'submit'=>Yii::app()->createUrl('transenq/index'))); 
				?>
			</div>
		</div>
	</div></div>
	<div class="box"><div class="box-body">
		<div class="form-group">
			<?php echo $form->hiddenField($model,'acct_id'); ?>
			<?php echo $form->hiddenField($model,'city'); ?>
			<?php echo $form->labelEx($model,'acct_name',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-4">
				<?php echo $form->textField($model, 'acct_name', 
						array('size'=>50,'maxlength'=>255,'readonly'=>true
				)); ?>
			</div>
			<?php echo $form->labelEx($model,'balance',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-4">
				<?php
					echo $form->numberField($model, 'balance', 
						array('size'=>10,
						'readonly'=>true,
						'prepend'=>'<span class="fa '.$sign.'"></span>')
					); 
				?>
			</div>
		</div>
	</div></div>
	<?php 
		$search = array(
						'trans_type_desc',
						'pay_subject',
						'cheque_no',
						'invoice_no',
						'trans_desc',
					);
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('trans','Transaction List'),
			'model'=>$model,
				'viewhdr'=>'//transenq/_listhdr2',
				'viewdtl'=>'//transenq/_listdtl2',
				'search'=>$search,
				'searchlinkparam'=>array('index'=>$model->acct_id,'city'=>$model->city),
		));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->renderPartial('//site/fileview',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'TRANS',
													'header'=>Yii::t('dialog','File Attachment'),
													)); 
?>
<?php $this->renderPartial('//transenq/dtlview',array('model'=>$model)); ?>

<?php
	Script::genFileDownload($model,$form->id,'TRANS');

	$js = Script::genDatePicker(array(
			'TransEnq2List_fm_dt',
			'TransEnq2List_to_dt',
		));
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);

	$link = Yii::app()->createAbsoluteUrl("transenq");
	$js = <<<EOF
function showattm(docid) {
	var data = "docId="+docid;
	var link = "$link"+"/listfile";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
			$("#fileview").html(data);
			$('#fileuploadtrans').modal('show');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}
EOF;
	Yii::app()->clientScript->registerScript('fileview',$js,CClientScript::POS_HEAD);
	
	$js = <<<EOF
function showdtl(docid,type) {
	var data = "index="+docid+"&type="+type;
	var link = "$link"+"/viewdetail";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
			$("#dtl-list").html(data);
			$('#dtlviewdialog').modal('show');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}
EOF;
	Yii::app()->clientScript->registerScript('dtlview',$js,CClientScript::POS_HEAD);

	//	$js = Script::genTableRowClick();
//	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


