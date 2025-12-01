<?php
$this->pageTitle=Yii::app()->name . ' - PlaneAward Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'PlaneAward-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('plane','Plane Award Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('planeAward/index')));
		?>
<?php if (!$model->isReadOnly()): ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
                'submit'=>Yii::app()->createUrl('planeAward/save')));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                    'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
            );
            ?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo CHtml::hiddenField('dtltemplate'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'employee_id'); ?>
			<?php echo $form->hiddenField($model, 'plane_date'); ?>
			<?php echo $form->hiddenField($model, 'plane_year'); ?>
			<?php echo $form->hiddenField($model, 'plane_month'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'employee_code',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'employee_code',
					array('readonly'=>(true))
				); ?>
				</div>
				<?php echo $form->labelEx($model,'employee_name',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'employee_name',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'city_name',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'city_name',
					array('readonly'=>(true))
				); ?>
				</div>
                <?php echo $form->labelEx($model,'entry_time',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'entry_time',
                        array('readonly'=>(true))
                    ); ?>
                </div>
			</div>

			<div class="form-group">
                <?php echo $form->labelEx($model,'show_date',array('class'=>"col-lg-2 col-lg-offset-4 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'show_date',
                        array('readonly'=>(true))
                    ); ?>
                </div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'job_id',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'job_id',
					array('readonly'=>(true))
				); ?>
				</div>
				<?php echo $form->labelEx($model,'job_num',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'job_num',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>
			<div class="form-group">
                <?php echo $form->hiddenField($model, 'money_id'); ?>
				<?php echo $form->labelEx($model,'money_value',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'money_value',
					array('readonly'=>(true))
				); ?>
				</div>
				<?php echo $form->labelEx($model,'money_num',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'money_num',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>
			<div class="form-group">
                <?php echo $form->hiddenField($model, 'year_id'); ?>
                <?php echo $form->labelEx($model,'year_month',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'year_month',
                        array('readonly'=>(true),'append'=>'年')
                    ); ?>
                </div>
				<?php echo $form->labelEx($model,'year_num',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'year_num',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'other_sum',array('class'=>"col-lg-2 col-lg-offset-4 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'other_sum',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'old_pay_wage',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->numberField($model, 'old_pay_wage',
					array('readonly'=>($model->isReadOnly()))
				); ?>
				</div>
				<?php echo $form->labelEx($model,'plane_sum',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'plane_sum',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-6 col-lg-offset-3">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'attribute'=>'info_list',
                            'viewhdr'=>'//planeAward/_formhdr',
                            'viewdtl'=>'//planeAward/_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>
<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = "
$('table').on('change','[id^=\"PlaneAwardForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#PlaneAwardForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);


if (!$model->isReadOnly()) {
    $js = <<<EOF
$('table').on('click','#btnDelRow', function() {
    $(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
    $(this).closest('tr').removeClass('tr_show').addClass('tr_hide').hide();
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
		$('#tblDetail tbody:last').append('<tr class="tr_show">'+ct+'</tr>');
		$('#tblDetail tr').eq(-1).find('[id*=\"PlaneAwardForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);

			if (id.indexOf('_other_num') != -1) $(this).val('');
			if (id.indexOf('_other_id') != -1) $(this).val('');
			if (id.indexOf('_uflag') != -1) $(this).attr('value','Y');
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
}
$js = Script::genDeleteData(Yii::app()->createUrl('planeAward/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


