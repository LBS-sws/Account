<?php
$this->pageTitle=Yii::app()->name . ' - Reimbursement';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'request-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Reimbursement'); ?></strong>
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
			echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('trans','Batch Submit'), array(
				'id'=>'btnBatchSubmit'
			)); 
		?>
<?php 
// Dummy Button for include jQuery.yii.submitForm
echo TbHtml::button('dummyButton', array('style'=>'display:none','disabled'=>true,'submit'=>'#',));
?>		
	</div>
	</div></div>
	<?php 
		$search = array(
						'req_dt',
						'trans_type_desc',
						'acct_type_desc',
						'payee_name',
						'item_desc',
						'ref_no',
						'int_fee',
					);
		if (!Yii::app()->user->isSingleCity()) $search[] = 'city_name';
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('trans','Request List'),
			'model'=>$model,
				'viewhdr'=>'//realize/_listhdr',
				'viewdtl'=>'//realize/_listdtl',
				'search'=>$search,
		));
	?>
</section>
<?php $this->renderPartial('//site/fileviewx',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'PAYREAL',
													'header'=>Yii::t('trans','File Attachment'),
													)); 
?>
<?php $this->renderPartial('//site/fileviewx',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'PAYREQ',
													'header'=>Yii::t('trans','Request Attachment'),
													)); 
?>
<?php $this->renderPartial('//site/fileviewx',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'TAX',
													'header'=>Yii::t('trans','Tax Slip'),
													)); 
?>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>

<?php
Script::genFileDownload($model,$form->id,'PAYREQ');
Script::genFileDownload($model,$form->id,'TAX');

$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);

$js = "
$('body').on('click','#chkboxAll',function() {
	var val = $(this).prop('checked');
	$('input[type=checkbox][name*=\"select\"]').prop('checked',val);
});
";
Yii::app()->clientScript->registerScript('selectAll',$js,CClientScript::POS_READY);

$js = "
$('input[type=checkbox][name*=\"select\"]').on('click', function() {
	var val = $(this).prop('checked');
});
";
Yii::app()->clientScript->registerScript('enableButton',$js,CClientScript::POS_READY);

$link = Yii::app()->createAbsoluteUrl("realize");
$js = <<<EOF
function showpayreal(docid) {
	var data = "docId="+docid;
	var link = "$link"+"/listpayreal";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
			$("#fileviewpayreal").html(data);
			$('#fileuploadpayreal').modal('show');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}

function showattm(docid) {
	var data = "docId="+docid;
	var link = "$link"+"/listfile";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
			$("#fileviewpayreq").html(data);
			$('#fileuploadpayreq').modal('show');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}

function showtax(docid) {
	var data = "docId="+docid;
	var link = "$link"+"/listtax";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
			$("#fileviewtax").html(data);
			$('#fileuploadtax').modal('show');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}
EOF;
Yii::app()->clientScript->registerScript('fileview',$js,CClientScript::POS_HEAD);

$url = Yii::app()->createUrl('realize/batchsubmit');
$js = "
$('#btnBatchSubmit').on('click', function(){
	$('input[type=checkbox][name*=\"select\"]').each(function() {
		var val = $(this).prop('checked');
		if (val) {
			Loading.show();
			jQuery.yii.submitForm(this,'$url',{});
			return false;
		}
	});
	return false;
});
";
Yii::app()->clientScript->registerScript('batchSubmit',$js,CClientScript::POS_READY);
?>


