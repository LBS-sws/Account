<?php
$this->pageTitle=Yii::app()->name . ' - Import';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'import-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
'htmlOptions'=>array('enctype'=>'multipart/form-data'),
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('import','Import'); ?></strong>
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
			echo TbHtml::button(Yii::t('misc','Submit'), array('submit'=>Yii::app()->createUrl('import/submit'))); 
		//	echo TbHtml::button(Yii::t('misc','Submit'), array('submit'=>Yii::app()->createUrl('import/activate'))); 
		?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'queue_id'); ?>
			<?php echo $form->hiddenField($model, 'file_type'); ?>
			<?php echo $form->hiddenField($model, 'file_content'); ?>

		<?php if (!Yii::app()->user->isSingleCity()): ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->dropDownList($model, 'city', General::getCityListWithNoDescendant(Yii::app()->user->city_allow()),
						array('disabled'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>
		<?php else: ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
		<?php endif ?>

		<div class="form-group">
			<?php echo $form->labelEx($model,'import_type',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
				<?php echo $form->dropDownList($model, 'import_type', $model->getImportTypeList()); ?>
			</div>
		</div>

		<div class="form-group">
			<?php echo $form->labelEx($model,'import_file',array('class'=>"col-sm-2 control-label")); ?>
			<div class="col-sm-3">
				<?php echo $form->fileField($model, 'import_file'); ?>
			</div>
		</div>

		<div class="box" id="mapping-div" style="display:none">
			<table id="mapping" class="table table-hover">
				<thead>
					<tr><th><?php echo Yii::t('dialog','Database Field');?></th><th><?php echo Yii::t('dialog','File Field');?></th></tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</section>

<?php
// Enable direct submit without field mapping
/*
$link = Yii::app()->createAbsoluteUrl("import/loadfile");
$js = "
$('#ImportForm_import_file').on('change',function() {
	var fileExtension = ['xls','xlsx'];
	if ($.inArray($(this).val().split('.').pop().toLowerCase(), fileExtension) == -1) {
		alert('Only formats are allowed : '+fileExtension.join(', '));
	} else 
		showFieldList();
});

$('#ImportForm_import_type').on('change',function() {
	if ($('#ImportForm_import_file').val() != '') showFieldList();
});

function showFieldList() {
	var form = document.getElementById('import-form');
	var formdata = new FormData(form);
	$.ajax({
		type: 'POST',
		url: '$link',
		data: formdata,
		mimeType: 'multipart/form-data',
		contentType: false,
		processData: false,
		success: function(data) {
			if (data!='') {
				$('#mapping').find('tbody').empty().append(data);
				id = $('#temp_qid').val();
				$('#ImportForm_queue_id').val(id);
				$('#mapping-div').css('display','');
				$('#yt0').prop('disabled',false);
			} else {
				$('#mapping-div').css('display','none');
				$('#yt0').prop('disabled',true);
			}
		},
		error: function(data) { // if error occured
			alert('Error occured.please try again');
		}
	});
}

$('#yt0').prop('disabled',true);
";
Yii::app()->clientScript->registerScript('changestyle',$js,CClientScript::POS_READY);
*/
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

