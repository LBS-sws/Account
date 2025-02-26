<?php
$this->pageTitle=Yii::app()->name . ' - PerformanceSet Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'performanceSet-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','performance bonus setting'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('performanceSet/index')));
		?>
        <?php echo TbHtml::button('<span class="fa fa-clone"></span> '.Yii::t('misc','Copy'), array(
                'submit'=>Yii::app()->createUrl('performanceSet/new', array('index'=>$model->id,'copy'=>1)))
        );
        ?>

<?php if (!$model->isReadOnly()): ?>
		<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('performanceSet/save')));
		?>
<?php endif ?>
        <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
            		'submit'=>Yii::app()->createUrl('performanceSet/delete')));
        ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo CHtml::hiddenField('dtltemplate'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'copy'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
                    <?php echo $form->textField($model, 'name',
                        array('class'=>'form-control','readonly'=>($model->isReadOnly()),));
                    ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'start_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'start_dt',
							array('class'=>'form-control pull-right','readonly'=>($model->isReadOnly()),));
						?>
					</div>
				</div>
			</div>

<div class="box">
<div class="box-body table-responsive">
			<?php 
				$this->widget('ext.layout.TableView2Widget', array(
					'model'=>$model,
					'attribute'=>'detail',
					'viewhdr'=>'//performanceSet/_formhdr',
					'viewdtl'=>'//performanceSet/_formdtl',
				));
			?>
</div>			
</div>			
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>


<?php
$js = "
$('table').on('change','[id^=\"PerformanceSetForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#PerformanceSetForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
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
});

$('#btnAddRow').on('click',function() {
	var r = $('#tblDetail tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate').val();
		$('#tblDetail tbody:last').append('<tr>'+ct+'</tr>');
		$('#tblDetail tr').eq(-1).find('[id*=\"PerformanceSetForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);

			if (id.indexOf('_operator') != -1) $(this).attr('value','');
			if (id.indexOf('_sales_amount') != -1) $(this).attr('value','');
			if (id.indexOf('_hy_pc_rate') != -1) $(this).attr('value','');
			if (id.indexOf('_inv_rate') != -1) $(this).attr('value','');
			if (id.indexOf('_id') != -1) $(this).attr('value',0);
		});
		if (nid != '') {
			var topos = $('#'+nid).position().top;
			$('#tbl_detail').scrollTop(topos);
		}
	}
});
EOF;
	Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);

	$js = Script::genDatePicker(array(
			'PerformanceSetForm_start_dt',
		));
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


