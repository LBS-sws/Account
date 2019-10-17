<?php
$this->pageTitle=Yii::app()->name . ' - Payment Request Approval';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'request-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Request Approval'); ?></strong>
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

<?php if (Yii::app()->user->validFunction('CN07')) : ?>
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			echo TbHtml::button('<span class="fa fa-check"></span> '.Yii::t('trans','Batch Approve'), array(
				'id'=>'btnBatchAppr', 
			)); 
		?>
		<?php 
			if (Yii::app()->user->validRWFunction('XA07')) {
				echo TbHtml::button('<span class="fa fa-check"></span> '.Yii::t('trans','Batch Sign'), array(
					'id'=>'btnBatchSign', 
				)); 
				
				$label = !$model->showtaxonly ? Yii::t('trans','Show w/Tax Slip') : Yii::t('trans','Show All');
				echo TbHtml::button('<span class="fa fa-eye"></span> '.$label, array(
					'id'=>'btnShow', 
				)); 
			}
		?>
<?php 
// Dummy Button for include jQuery.yii.submitForm
echo TbHtml::button('dummyButton', array('style'=>'display:none','disabled'=>true,'submit'=>'#',));
?>		
	</div>
	</div></div>
<?php endif ?>

	<div id="yw0" class="tabbable">
		<?php  ?>
		<ul class="nav nav-tabs" role="menu">
			<li <?php echo (($model->type=='P') ? 'class="active"' : ''); ?> role="menuitem">
				<a tabindex="-1" href="<?php echo Yii::app()->createUrl('apprreq/index', array('type'=>'P'));?>">
					<?php echo Yii::t('trans','Pending (Direct)'); ?>
				</a>
			</li>
			<li <?php echo (($model->type=='Q') ? 'class="active"' : ''); ?> role="menuitem">
				<a tabindex="-1" href="<?php echo Yii::app()->createUrl('apprreq/index', array('type'=>'Q'));?>">
					<?php echo Yii::t('trans','Pending (Related)'); ?>
				</a>
			</li>
		</ul>	
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
				'viewhdr'=>'//apprreq/_listhdr',
				'viewdtl'=>'//apprreq/_listdtl',
				'search'=>$search,
		));

	?>
	</div>
</section>
<?php
	echo $form->hiddenField($model,'type');
	echo $form->hiddenField($model,'showtaxonly');
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->renderPartial('//site/fileviewx',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'PAYREQ',
													'header'=>Yii::t('dialog','File Attachment'),
													)); 
?>
<?php $this->renderPartial('//site/fileviewx',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'TAX',
													'header'=>Yii::t('trans','Tax Slip'),
													)); 
?>

<?php $this->endWidget(); ?>

<?php
Script::genFileDownload($model,$form->id,'PAYREQ');
Script::genFileDownload($model,$form->id,'TAX');

$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);

$link = Yii::app()->createAbsoluteUrl("apprreq");
$js = <<<EOF
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

$url = Yii::app()->createUrl('apprreq/batchapprove');
$js = "
$('#btnBatchAppr').on('click', function(){
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
Yii::app()->clientScript->registerScript('batchApprove',$js,CClientScript::POS_READY);

$url = Yii::app()->createUrl('apprreq/batchsign');
$js = "
$('#btnBatchSign').on('click', function(){
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
Yii::app()->clientScript->registerScript('batchSign',$js,CClientScript::POS_READY);

$url = Yii::app()->createUrl('apprreq/index',array('type'=>$model->type));
$js = "
$('#btnShow').on('click', function(){
	var val = $('#ApprReqList_showtaxonly').val();
	var arg = val ? '&tax=N' : '&tax=Y';
	Loading.show();
	jQuery.yii.submitForm(this,'$url'+arg,{});
	return false;
});
";
Yii::app()->clientScript->registerScript('batchShow',$js,CClientScript::POS_READY);

$js = "
$('body').on('click','#chkboxAll',function() {
	var val = $(this).prop('checked');
	$('input[type=checkbox][name*=\"select\"]').prop('checked',val);
});
";
Yii::app()->clientScript->registerScript('selectAll',$js,CClientScript::POS_READY);
?>


