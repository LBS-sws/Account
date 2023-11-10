<?php
$this->pageTitle=Yii::app()->name . ' - ConsultApply Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'ConsultApply-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Consult Fee Apply'); ?></strong>
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
					'submit'=>Yii::app()->createUrl('consultApply/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('consultApply/index')));
		?>
<?php if (!$model->isReady()): ?>
			<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('consult','Draft'), array(
				'submit'=>Yii::app()->createUrl('consultApply/draft')));
			?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('consult','Send'), array(
				'submit'=>Yii::app()->createUrl('consultApply/send')));
			?>
<?php endif ?>
<?php if (!$model->isReady()&&$model->scenario!='new'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>

            <div class="btn-group pull-right" role="group">
                <?php
                $counter = ($model->no_of_attm['consu'] > 0) ? ' <span id="docconsu" class="label label-info">'.$model->no_of_attm['consu'].'</span>' : ' <span id="docconsu"></span>';
                echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
                        'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadconsu',)
                );
                ?>
                <?php if ($model->scenario!='new'): ?>
                    <?php echo TbHtml::button('<span class="fa fa-calendar"></span> '.Yii::t('consult','History'), array(
                        'data-toggle'=>'modal','data-target'=>'#historydialog'));
                    ?>
                <?php endif ?>
            </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'status'); ?>
            <?php echo CHtml::hiddenField('dtltemplate'); ?>

            <?php $this->renderPartial('//site/consultForm',array("model"=>$model,"form"=>$form)); ?>



            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-7 col-lg-offset-2">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'attribute'=>'info_list',
                            'viewhdr'=>'//consultApply/_formhdr',
                            'viewdtl'=>'//consultApply/_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'CONSU',
    'header'=>Yii::t('dialog','File Attachment'),
    'ronly'=>(false),
    'nodelete'=>!($model->scenario=='new'||$model->status == 0||$model->status == 3),
));
?>
<?php $this->renderPartial('//site/history',array('tableHtml'=>ConsultApplyForm::getHistoryHtml($model->id))); ?>
<?php $this->renderPartial('//site/removedialog'); ?>

<?php
Script::genFileUpload($model,$form->id,'CONSU');

$js = "

$('table').on('change','[id^=\"ConsultApplyForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#ConsultApplyForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});

$('#tblDetail').delegate('.good_money','keyup change',function(){
    var sum=0;
    $('.good_money').each(function(){
        var number = $(this).val();
        number = number!=''&&!isNaN(number)?parseFloat(number):0;
        sum+=number;
    });
    $('#consult_money').val(sum);
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);



$js = Script::genDeleteData(Yii::app()->createUrl('consultApply/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if(!$model->isReady()){

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
		$('#tblDetail tr').eq(-1).find('[id*=\"ConsultApplyForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);

			if (id.indexOf('_set_id') != -1) $(this).attr('value','');
			if (id.indexOf('_good_money') != -1) $(this).attr('value','');
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
        'apply_date',
    ));
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


