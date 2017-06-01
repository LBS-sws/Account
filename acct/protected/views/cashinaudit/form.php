<?php
$this->pageTitle=Yii::app()->name . ' - Cash Checking Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'check-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Cash In Checking Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('cashinaudit/index'))); 
		?>
<?php if (!$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-check"></span> '.Yii::t('misc','Confirm'), array(
			'id'=>'btnConfirm'));
		?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box"><div class="box-body">
		<div class="form-group">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'hdr_id'); ?>
			<?php echo $form->hiddenField($model, 'acct_id'); ?>
			<?php echo $form->hiddenField($model, 'req_user'); ?>
			<?php echo $form->hiddenField($model, 'audit_user'); ?>
			<?php echo $form->hiddenField($model, 'id_list'); ?>
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
			<?php echo $form->label($model,'balance',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
				<?php
					echo $form->numberField($model, 'balance', 
						array('size'=>10,
						'readonly'=>true,
						'prepend'=>'<span class="fa fa-cny"></span>')
					); 
				?>
			</div>
		</div>
<?php if ($model->isReadOnly()): ?>
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
	</div></div>

	<?php 
		$search = array();
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('trans','Transaction List'),
			'model'=>$model,
				'viewhdr'=>'//cashinaudit/_listhdr2',
				'viewdtl'=>'//cashinaudit/_listdtl2',
				'hasSearchBar'=>false,
				'hasNavBar'=>false,
				'hasPageBar'=>false,
		));
	?>
</section>

<?php $this->renderPartial('//site/fileview',array('model'=>$model,
													'form'=>$form,
													'doctype'=>'TRANS',
													'header'=>Yii::t('dialog','File Attachment'),
													)); 
?>
<?php $this->renderPartial('//transenq/dtlview',array('model'=>$model)); ?>
<?php $this->renderPartial('//cashinaudit/authen',array('model'=>$model,'form'=>$form)); ?>

<?php
Script::genFileDownload($model,$form->id,'TRANS');

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);

	$link = Yii::app()->createAbsoluteUrl("cashinaudit");
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

	$js = <<<EOF
$('#btnConfirm').on('click',function(){
	$('#authdialog').modal('show');
});
EOF;
	Yii::app()->clientScript->registerScript('confirm',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


