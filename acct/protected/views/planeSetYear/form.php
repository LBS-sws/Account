<?php
$this->pageTitle=Yii::app()->name . ' - PlaneSetYear Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'PlaneSetYear-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('plane','Plane Set Year Form'); ?></strong>
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
					'submit'=>Yii::app()->createUrl('planeSetYear/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('planeSetYear/index')));
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('planeSetYear/save')));
			?>
<?php endif ?>
<?php if ($model->scenario!='new' && $model->scenario!='view'): ?>
            <?php echo TbHtml::button('<span class="fa fa-clone"></span> '.Yii::t('misc','Copy'), array(
                    'submit'=>Yii::app()->createUrl('planeSetYear/new', array('index'=>$model->id)))
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
				<?php echo $form->labelEx($model,'set_name',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-3">
				<?php echo $form->textField($model, 'set_name',
					array('readonly'=>($model->scenario=='view'))
				); ?>
				</div>
                <div class="col-lg-7">
                    <p class="form-control-static text-success">方便自己识别是哪个配置</p>
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


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-6 col-lg-offset-3">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'attribute'=>'info_list',
                            'viewhdr'=>'//planeSetYear/_formhdr',
                            'viewdtl'=>'//planeSetYear/_formdtl',
                        ));
                        ?>
                    </div>

                    <?php if ($model->scenario!='view'): ?>
                    <div class="col-lg-3" style="border: 1px solid #000;">
                        <div class="form-group">
                            <div class="col-lg-12">
                                <p class="form-control-static text-success">快捷递增（增加配置时自动递增）</p>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $form->labelEx($model,'年限递增',array('class'=>"col-lg-5 control-label")); ?>
                            <div class="col-lg-5">
                                <?php echo TbHtml::numberField("addValue", 1,
                                    array('id'=>'addValue')
                                ); ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $form->labelEx($model,'金额递增',array('class'=>"col-lg-5 control-label")); ?>
                            <div class="col-lg-5">
                                <?php echo TbHtml::numberField("addMoney", 100,
                                    array('id'=>'addMoney')
                                ); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif ?>
                </div>
            </div>
		</div>
	</div>
</section>
<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = "
$('table').on('change','[id^=\"PlaneSetYearForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#PlaneSetYearForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
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

function proList(){
    var list = [],number=0;
    list['value_name'] = '';
    list['value_money'] = '';
    var tr = $('#tblDetail>tbody>tr.tr_show:last');
    if($('#addValue').val()!=''){
        number = tr.find('.value_name:first').val();
        number = number==""?0:parseInt(number,10);
        list['value_name'] = number+parseInt($('#addValue').val(),10);
    }
    if($('#addMoney').val()!=''){
        number = tr.find('.value_money:first').val();
        number = number==""?0:parseInt(number,10);
        list['value_money'] = number+parseInt($('#addMoney').val(),10);
    }
    
    return list;
}

$('#btnAddRow').on('click',function() {
	var r = $('#tblDetail tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate').val();
		var addProList = proList();
		$('#tblDetail tbody:last').append('<tr class="tr_show">'+ct+'</tr>');
		$('#tblDetail tr').eq(-1).find('[id*=\"PlaneSetYearForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);

			if (id.indexOf('_value_name') != -1) $(this).attr('value',addProList['value_name']);
			if (id.indexOf('_value_money') != -1) $(this).attr('value',addProList['value_money']);
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
    ));
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
$js = Script::genDeleteData(Yii::app()->createUrl('planeSetYear/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


