<?php
$this->pageTitle=Yii::app()->name . ' - ConsultSearch';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'consultSearch-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Consult Fee Search'); ?></strong>
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
    <?php $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('consult','Search List'),
        'model'=>$model,
        'viewhdr'=>'//consultSearch/_listhdr',
        'viewdtl'=>'//consultSearch/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
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
?>
<?php $this->renderPartial('//site/fileviewx',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'CONSU',
    'header'=>Yii::t('dialog','File Attachment'),
));
?>
<?php $this->endWidget(); ?>

<?php
Script::genFileDownload($model,$form->id,'CONSU');

$link = Yii::app()->createAbsoluteUrl("ConsultSearch");
$js = <<<EOF
function showconsu(docid) {
	var data = "docId="+docid;
	var link = "$link"+"/listfile";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
			$("#fileviewconsu").html(data);
			$('#fileuploadconsu').modal('show');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}
EOF;
Yii::app()->clientScript->registerScript('fileview',$js,CClientScript::POS_HEAD);

$js="
$('.stopTd').click(function(e){
    e.stopPropagation();
});
";
$js.= Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
