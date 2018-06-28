<?php
$this->pageTitle=Yii::app()->name . ' - Delegation Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Delegation Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('delegate/save'))); 
			?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo CHtml::hiddenField('dtltemplate'); ?>

<div class="box-body table-responsive">
			<?php $this->widget('ext.layout.TableView2Widget', array(
					'model'=>$model,
					'attribute'=>'detail',
					'viewhdr'=>'//delegate/_formhdr',
					'viewdtl'=>'//delegate/_formdtl',
					'gridsize'=>'24',
					'height'=>'200',
				));
			?>
</div>			
		</div>
	</div>
</section>

<?php
$js = "
$('table').on('change','[id^=\"LogisticForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#LogisticForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);

if ($model->scenario!='view') {
	$js = <<<EOF
$('table').on('click','#btnDelRow', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
EOF;
Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);

	$js = <<<EOF
$(document).ready(function(){
	var ct = $('#tblDetail tr').eq(1).html();
	$('#dtltemplate').attr('value',ct);
	$('.deadline').datepicker({autoclose: true, format: 'yyyy/mm/dd'});
});

$('#btnAddRow').on('click',function() {
	var r = $('#tblDetail tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate').val();
		$('#tblDetail tbody:last').append('<tr>'+ct+'</tr>');
		$('#tblDetail tr').eq(-1).find('[id*=\"DelegateForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);

			if (id.indexOf('_deletgated') != -1) $(this).attr('value','');
		});
		if (nid != '') {
			var topos = $('#'+nid).position().top;
			$('#tbl_detail').scrollTop(topos);
		}
	}
});
EOF;
	Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


