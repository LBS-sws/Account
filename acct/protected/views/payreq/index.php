<?php
$this->pageTitle=Yii::app()->name . ' - Payment Request';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'request-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Payment Request'); ?></strong>
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
<?php if (Yii::app()->user->validRWFunction('XA04') && Yii::app()->user->validFunction('CN03')) : ?>
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('payreq/new'), 
				)); 
		?>
	</div>
	</div></div>
<?php endif; ?>
	<?php 
		if (!Yii::app()->user->isSingleCity()) $search[] = 'city_name';
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('trans','Request List'),
			'model'=>$model,
				'viewhdr'=>'//payreq/_listhdr',
				'viewdtl'=>'//payreq/_listdtl',
				'advancedSearch'=>true,
				'hasDateButton'=>true,
		));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
	echo $form->hiddenField($model,'filter');
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
$js="
$('.stopTd').click(function(e){
    e.stopPropagation();
});
";
	$js.= Script::genTableRowClick();
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
?>


