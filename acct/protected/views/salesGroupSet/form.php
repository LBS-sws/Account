<?php
$this->pageTitle=Yii::app()->name . ' - SalesGroupSet Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'SalesGroupSet-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Sales Group Set'); ?></strong>
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
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('salesGroupSet/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('salesGroupSet/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('salesGroupSet/save')));
			?>
<?php endif ?>
<?php if ($model->scenario!='new' && $model->scenario!='view'): ?>
            <?php echo TbHtml::button('<span class="fa fa-clone"></span> '.Yii::t('misc','Copy'), array(
                    'submit'=>Yii::app()->createUrl('salesGroupSet/new', array('index'=>$model->id)))
            );
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

			<div class="form-group">
				<?php echo $form->labelEx($model,'employee_id',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
                <?php
                echo TbHtml::textField("employeeName", $model->getEmployeeNameByID($model->employee_id),
                    array('readonly'=>true,'class'=>'employeeName',
                        'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('group','staff'),array('class'=>'searchUser','disabled'=>$model->isReadOnly())),
                    ));
                ?>
                <?php echo $form->hiddenField($model, 'employee_id',array("class"=>'employeeID')); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'start_date',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
				<?php echo $form->textField($model, 'start_date',
					array('id'=>'start_date','readonly'=>($model->scenario=='view'),'prepend'=>'<span class="fa fa-calendar"></span>')
				); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'end_date',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
				<?php echo $form->textField($model, 'end_date',
					array('id'=>'end_date','readonly'=>($model->scenario=='view'),'prepend'=>'<span class="fa fa-calendar"></span>')
				); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'employee_type',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
				<?php echo $form->dropDownList($model, 'employee_type',SalesGroupSetForm::getGroupTypeList(),
					array('id'=>'employee_type','readonly'=>($model->scenario=='view'))
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
                            'viewhdr'=>'//salesGroupSet/_formhdr',
                            'viewdtl'=>'//salesGroupSet/_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>
<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//site/lookup'); ?>

<?php
$js = "
$('table').on('change','[id^=\"SalesGroupSetForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#SalesGroupSetForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);


if ($model->scenario!='view') {
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
		$('#tblDetail tr').eq(-1).find('[id*=\"SalesGroupSetForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);

			if (id.indexOf('_value_name') != -1) $(this).attr('value','');
			if (id.indexOf('_value_money') != -1) $(this).attr('value','');
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

    $js = Script::genDatePicker(array(
        'start_date',
        'end_date',
    ));
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);

    $js = Script::genLookupSearchEx();
    Yii::app()->clientScript->registerScript('lookupSearch',$js,CClientScript::POS_READY);

    $multiflag = 'false';
    $js = <<<EOF
$('body').on('click','.searchUser',function() {
	var value = $(this).parents('.input-group:first').children('.employeeName').attr("id");
	var code = $(this).parents('.input-group:first').next('.employeeID').attr("id");
	var title = '员工查询';
	$('#lookuptype').val('employee');
	$('#lookupcodefield').val(code);
	$('#lookupvaluefield').val(value);
	$('#lookupotherfield').val('');
	$('#lookupparamfield').val('');
	if ($multiflag) $('#lstlookup').attr('multiple','multiple');
	if (!($multiflag)) $('#lookup-label').attr('style','display: none');
	$('#lookupdialog').find('.modal-title').text(title);
	$('#lookupdialog').modal('show');
	$(this).parents('.input-group:first').next('.employeeID').trigger('change');
});
EOF;
    Yii::app()->clientScript->registerScript('lookupEmployee',$js,CClientScript::POS_READY);

    $js = Script::genLookupSelect();
    Yii::app()->clientScript->registerScript('lookupSelect',$js,CClientScript::POS_READY);
}
$js = Script::genDeleteData(Yii::app()->createUrl('salesGroupSet/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


