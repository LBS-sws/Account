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
<?php if (!$model->isReadOnly()&&in_array($model->plane_status,array(0,3))): ?>
            <?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Save'), array(
                'submit'=>Yii::app()->createUrl('planeAward/save')));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Approval'), array(
                'submit'=>Yii::app()->createUrl('planeAward/audit')));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                    'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
            );
            ?>
<?php endif ?>
	</div>
            <?php if (Yii::app()->user->validRWFunction('PS07')&&$model->plane_status==2): ?>
                <div class="btn-group pull-right" role="group">
                    <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('plane','revoke'), array(
                        'submit'=>Yii::app()->createUrl('planeAward/revoke')));
                    ?>
                </div>
            <?php endif ?>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo CHtml::hiddenField('dtltemplate1'); ?>
            <?php echo CHtml::hiddenField('dtltemplate2'); ?>
            <?php echo CHtml::hiddenField('dtltemplate3'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'employee_id'); ?>
			<?php echo $form->hiddenField($model, 'plane_date'); ?>
			<?php echo $form->hiddenField($model, 'plane_year'); ?>
			<?php echo $form->hiddenField($model, 'plane_month'); ?>
			<?php echo $form->hiddenField($model, 'plane_status'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>

            <?php if ($model->plane_status==3): ?>
                <div class="form-group has-error">
                    <?php echo $form->labelEx($model,'reject_txt',array('class'=>"col-lg-2 control-label")); ?>
                    <div class="col-lg-8 ">
                        <?php echo $form->textArea($model, 'reject_txt',
                            array('readonly'=>(true),'rows'=>3)
                        ); ?>
                    </div>
                </div>
            <?php endif ?>
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
                <?php echo $form->labelEx($model,'old_money_value',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'old_money_value',
                        array('readonly'=>(true))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'show_date',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'show_date',
                        array('readonly'=>(true))
                    ); ?>
                </div>
			</div>
            <div class="form-group">
                <?php echo $form->hiddenField($model, 'money_id'); ?>
                <?php echo $form->labelEx($model,'money_value',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->numberField($model, 'money_value',
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
				<?php echo $form->labelEx($model,'old_take_amt',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'old_take_amt',
					array('readonly'=>(true))
				); ?>
				</div>
				<?php echo $form->labelEx($model,'other_sum',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'other_sum',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'take_amt',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'take_amt',
					array('readonly'=>(true))
				); ?>
				</div>
                <?php echo $form->labelEx($model,'plane_sum',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'plane_sum',
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
                <?php echo $form->labelEx($model,'plane_status',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo TbHtml::textField("plane_status",PlaneAwardList::getPlaneStatusList($model->plane_status)["str"],
                        array('readonly'=>(true))
                    );
                    ?>
                </div>
			</div>


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-6 col-lg-offset-3">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'tableidx'=>1,
                            'attribute'=>'info_list',
                            'viewhdr'=>'//planeAward/_formhdr',
                            'viewdtl'=>'//planeAward/_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-8 col-lg-offset-2">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'tableidx'=>3,
                            'attribute'=>'infoMoney',
                            'viewhdr'=>'//planeAward/m_formhdr',
                            'viewdtl'=>'//planeAward/m_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-8 col-lg-offset-2">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'tableidx'=>2,
                            'attribute'=>'infoDetail',
                            'viewhdr'=>'//planeAward/t_formhdr',
                            'viewdtl'=>'//planeAward/t_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>
<?php $this->renderPartial('//site/removedialog'); ?>
<div class="modal fade" id="quickModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">快捷操作(<small id="titleSmall"></small>)</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <p>格式：名称 金额<br/>
                            例如：<br/>
                            生日礼金 10.2<br/>
                            夜班 100<br/>
                        </p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <?php
                        echo TbHtml::textArea("quickTxt",'',array('rows'=>4,'id'=>'quickTxt'));
                        ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>
                <button type="button" class="btn btn-primary" id="quickOk">一键增加</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
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
$('table').on('click','.btnDelRow', function() {
    $(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
    $(this).closest('tr').removeClass('tr_show').addClass('tr_hide').hide();
});

$('.btnQuickRow').on('click',function(){
    var id = $(this).data('id');
    var title = $(this).data('title');
    $('#titleSmall').text(title);
    $('#quickTxt').val('').data('id',id);
    $('#quickModal').modal('show');
});

$('#quickOk').on('click',function(){
    var id = $('#quickTxt').data('id');
    var text = $('#quickTxt').val();
    var arr = text.split('\\n');
    $.each(arr,function(key,rowTxt){
        rowTxt = rowTxt.split("\\t").join(" ");
        rowTxt = rowTxt.split(' ');
        if(rowTxt.length>=2){
            var val_key='';
            var val_amt=0;
            $.each(rowTxt,function(num,val){
                if(num!=rowTxt.length-1){
                    val_key+=val_key==''?'':' ';
                    val_key+=val;
                }else{
                    val_amt = parseFloat(val);
                }
            });
            if($('#tblDetail'+id+' tr').eq(-1).find('.nullInput').val()!=''){
                $('#tblDetail'+id).find('.btnAddRow').eq(0).trigger('click');
            }
            switch(id){
                case 1:
                    $('#tblDetail'+id+' tr').eq(-1).find('.other_id').eq(0).find('option').each(function(){
                        if($(this).text()==val_key){
                            $('#tblDetail'+id+' tr').eq(-1).find('.other_id').val($(this).attr('value'));
                        }
                    });
                    $('#tblDetail'+id+' tr').eq(-1).find('.nullInput').val(val_amt);
                    break;
                case 2:
                    $('#tblDetail'+id+' tr').eq(-1).find('.takeTxt').val(val_key);
                    $('#tblDetail'+id+' tr').eq(-1).find('.nullInput').val(val_amt).trigger('change');
                    break;
                case 3:
                    $('#tblDetail'+id+' tr').eq(-1).find('.moneyTxt').val(val_key);
                    $('#tblDetail'+id+' tr').eq(-1).find('.nullInput').val(val_amt).trigger('change');
                    break;
            }
        }
    });
    $('#quickModal').modal('hide');
});
EOF;
    Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);

    $js = <<<EOF
$(document).ready(function(){
	var ct = $('#tblDetail1 tr').eq(1).html();
	$('#dtltemplate1').attr('value',ct);
	ct = $('#tblDetail2 tr').eq(1).html();
	$('#dtltemplate2').attr('value',ct);
	ct = $('#tblDetail3 tr').eq(1).html();
	$('#dtltemplate3').attr('value',ct);
});

$('.btnAddRow').on('click',function() {
    var tblId = $(this).data('id');
	var r = $('#tblDetail'+tblId+' tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate'+tblId+'').val();
		$('#tblDetail'+tblId+' tbody:last').append('<tr class="tr_show">'+ct+'</tr>');
		$('#tblDetail'+tblId+' tr').eq(-1).find('[id*=\"PlaneAwardForm_\"]').each(function(index) {
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
			if (id.indexOf('_takeTxt') != -1) $(this).val('');
			if (id.indexOf('_takeAmt') != -1) $(this).val('');
			if (id.indexOf('_moneyTxt') != -1) $(this).val('');
			if (id.indexOf('_moneyAmt') != -1) $(this).val('');
			if (id.indexOf('_uflag') != -1) $(this).attr('value','Y');
			if (id.indexOf('_id') != -1) $(this).attr('value',0);
		});
		if (nid != '') {
			var topos = $('#'+nid).position().top;
			$('#tbl_detail').scrollTop(topos);
		}
	}
});

$('#tblDetail2').on('change','.takeAmt',function(){
    var oldTakeAmt = $('#PlaneAwardForm_old_take_amt').val();
    oldTakeAmt = oldTakeAmt==''?0:parseFloat(oldTakeAmt);
    $('.takeAmt').each(function(){
        var numAmt = $(this).val();
        numAmt = numAmt==''?0:parseFloat(numAmt);
        oldTakeAmt+=numAmt;
    });
    $('#PlaneAwardForm_take_amt').val(oldTakeAmt);
});

$('#tblDetail3').on('change','.moneyAmt',function(){
    var oldMoneyAmt = $('#PlaneAwardForm_old_money_value').val();
    oldMoneyAmt = oldMoneyAmt==''?0:parseFloat(oldMoneyAmt);
    $('.moneyAmt').each(function(){
        var numAmt = $(this).val();
        numAmt = numAmt==''?0:parseFloat(numAmt);
        oldMoneyAmt+=numAmt;
    });
    $('#PlaneAwardForm_money_value').val(oldMoneyAmt);
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


