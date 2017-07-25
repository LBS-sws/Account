<?php
$this->pageTitle=Yii::app()->name . ' - Import Manager';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'queue-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('import','Import Manager'); ?></strong> 
<!--
		<small><?php echo Yii::t('queue','** Records will be kept in the system for 14 days only.'); ?></small>
-->
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
				echo TbHtml::button('<span class="fa fa-refresh"></span> '.Yii::t('misc','Refresh'), array(
					'submit'=>Yii::app()->createUrl('iqueue/index'), 
				)); 
		?>
	</div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('queue','Queue List'),
			'model'=>$model,
				'viewhdr'=>'//iqueue/_listhdr',
				'viewdtl'=>'//iqueue/_listdtl',
				'search'=>array(
							'import_type',
							'status',
						),
		));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>

<?php $this->renderPartial('//iqueue/logview',array('model'=>$model)); ?>

<?php
	$link = Yii::app()->createAbsoluteUrl("iqueue");
	$js = <<<EOF
function showlog(id) {
	var data = "index="+id;
	var link = "$link"+"/viewlog";
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		success: function(data) {
			$("#log_content").val(data);
			$('#logviewdialog').modal('show');
		},
		error: function(data) { // if error occured
			alert("Error occured.please try again");
		},
		dataType:'html'
	});
}
EOF;
	Yii::app()->clientScript->registerScript('logview',$js,CClientScript::POS_HEAD);
?>

<?php $this->endWidget(); ?>

