<?php
$this->pageTitle=Yii::app()->name . ' - SellTable Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'SellTable-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .click-th,.click-tr{ cursor: pointer;}
    .click-tr>.fa:before{ content: "\f062";}
    .click-tr.show-tr>.fa:before{ content: "\f063";}
    .table-fixed{ table-layout: fixed;}
    input[type="checkbox"].readonly{ opacity: 0.6;pointer-events: none;}
    .form-group{ margin-bottom: 0px;}
    .table-fixed>thead>tr>th,.table-fixed>tfoot>tr>td,.table-fixed>tbody>tr>td{ text-align: center;vertical-align: middle;font-size: 12px;border-color: #333;}
    .table-fixed>thead>tr>th.header-width{ height: 0px;padding: 0px;overflow: hidden;border-width: 0px;line-height: 0px;}
</style>

<section class="content-header">
	<h1>
        <strong><?php echo Yii::t('salestable','Sales commission Bi'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('sellTable/index')));
		?>
        <?php if ($model->examine == 'A'): ?>
            <?php echo TbHtml::button('<span class="fa fa-reply-all"></span> '.Yii::t('misc','Rollback'), array(
                'submit'=>Yii::app()->createUrl('sellTable/break')));
            ?>
        <?php endif ?>
        <?php if ($model->getReadonly()): ?>
            <?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Save'), array(
                'submit'=>Yii::app()->createUrl('sellTable/save')));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> ' . Yii::t('misc', 'Approval'), array(
                'submit' => Yii::app()->createUrl('sellTable/examine')));
            ?>
        <?php endif ?>
        <?php if (Yii::app()->user->validFunction('CN12') && $model->examine == 'Y'): ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> ' . Yii::t('misc', 'Audit'), array(
                'submit' => Yii::app()->createUrl('sellTable/audit')));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-remove"></span> ' . Yii::t('misc', 'Deny'), array(
                'data-toggle' => 'modal', 'data-target' => '#jectdialog'));
            ?>
        <?php endif ?>
	</div>


            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-cloud-download"></span> ' . Yii::t('misc', 'Down'), array(
                    'submit' => Yii::app()->createUrl('sellTable/down', array('index' => $model->id))));
                ?>
            </div>
	</div></div>

    <div class="box">
        <div id="yw0" class="tabbable">
            <div class="box-info" >
                <?php echo $form->hiddenField($model, 'id'); ?>
                <?php echo $form->hiddenField($model, 'year'); ?>
                <?php echo $form->hiddenField($model, 'month'); ?>
                <?php echo CHtml::hiddenField('dtltemplate'); ?>
                <div class="box-body" >
                    <?php if ($model->examine=="S"): ?>
                    <div class="col-lg-12">
                        <div class="form-group has-error" style="margin-bottom: 10px;">
                            <?php echo $form->labelEx($model,'ject_remark',array('class'=>"col-lg-2 control-label")); ?>
                            <div class="col-lg-6">
                                <?php echo $form->textArea($model, 'ject_remark',
                                    array('readonly'=>true,'rows'=>3)
                                ); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif ?>
                    <div class="col-lg-4">
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','new_calc'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("new_calc",SellComputeList::showText($model->dtl_list['new_calc'],false,"rate"),array('readonly'=>true));?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','point'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("point",SellComputeList::showText($model->dtl_list['point'],false,"rate"),array('readonly'=>true));?>
                            </div>
                        </div>
                        <?php if ($model->startDate<='2025-01-01'): ?>
                            <div class="form-group" >
                                <label class="col-sm-5 control-label"><?php echo Yii::t('commission','bring reward'); ?></label>
                                <div class="col-sm-7">
                                    <?php echo TbHtml::textField("service_reward",SellComputeList::showText($model->dtl_list['service_reward'],false,"rate"),array('readonly'=>true));?>
                                </div>
                            </div>
                        <?php endif ?>

                        <div class="form-group">
                            <?php echo $form->labelEx($model,'examine',array('class'=>"col-sm-5 control-label")); ?>
                            <div class="col-sm-7">
                                <?php echo $form->textField($model, 'examine_name',
                                    array('id'=>'examine_name','readonly'=>(true))
                                ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <div class="media">
                            <div class="media-left">
                                <p>注：</p>
                            </div>
                            <div class="media-body">
                                <p><span style="color: red;">红色</span>标记的为跨区记录（年金额计算到业绩里，提成根据地区设置的比例计算）</p>
                                <p><span style="color: blue;">蓝色</span>标记的为被跨区记录（年金额不计算到业绩里，提成满足跨区目标要求后，根据地区设置的比例计算）</p>
                                <!--
                                <p><span style="color: yellow;">黄色</span>标记的为提成点是零的续约服务</p>
                                -->
                                <p>甲醛、飘盈香客户归为IC类</p>
                                <?php if ($model->startDate<='2025-01-01'): ?>
                                    <p>创新业务提成点：满足月销3桶洗地易+月新签蔚诺及隔油池金额大于2500</p>
                                <?php endif ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12" style="padding-top: 15px;">
                        <div class="row panel panel-default" style="border-color: #333">
                            <!-- Default panel contents -->
                            <div class="panel-heading">
                                <h3 style="margin-top:10px;">
                                    <span><?php echo $model->year.Yii::t('app','Year').$model->month.Yii::t('app','Month');?></span>
                                    <span><?php echo $model->employee_name.Yii::t('app','Sales commission table');;?></span>
                                </h3>
                            </div>

                            <!-- Table -->
                            <div class="table-responsive">
                                <?php echo $model->sellTableHtml();?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php echo $form->labelEx($model,'supplement_money',array('class'=>"col-lg-2 control-label")); ?>
                            <div class="col-lg-2">
                                <?php echo $form->textField($model, 'supplement_money',
                                    array('id'=>'supplement_money','readonly'=>true)
                                ); ?>
                            </div>
                            <?php echo $form->labelEx($model,'final_money',array('class'=>"col-lg-2 control-label")); ?>
                            <div class="col-lg-2">
                                <?php echo $form->textField($model, 'final_money',
                                    array('id'=>'final_money','readonly'=>true,'data-num'=>$model->sumAllMoney[0]+$model->sumAllMoney[1]+$model->sumAllMoney[2])
                                ); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="box">
        <div class="box-body">
            <legend><?php echo Yii::t('salestable', 'Supplementary notes'); ?></legend>
            <h4>除以上提成金额，如还有特殊情况，系统未能计算，请手动在下面增加 </h4>
            <?php $this->widget('ext.layout.TableView2Widget', array(
                'model' => $model,
                'attribute' => 'detail',
                'viewhdr' => '//sellTable/_formhdr',
                'viewdtl' => '//sellTable/_formdtl',
                'gridsize' => '24',
                'height' => '200',
            ));
            ?>
        </div>
    </div>
</section>


<?php
$this->renderPartial('//site/ject', array(
    'form' => $form,
    'model' => $model,
    'rejectName' => "ject_remark",
    'submit' => Yii::app()->createUrl('sellTable/ject'),
));
?>
<?php
$js="
$('#tblDetail').delegate('.changeCom','keyup change',function(){
    var sum = 0;
    var final_money = $('#final_money').data('num');
    final_money=$.isNumeric(final_money)?parseFloat(final_money):0;
    $('#tblDetail>tbody>tr').each(function(){
        if($(this).css('display')!='none'){
            var num = $(this).find('.changeCom:first').val();
            num = $.isNumeric(num)?parseFloat(num):0;
            sum+=num;
        }
    });
    $('#supplement_money').val(sum);
    $('#final_money').val(sum+final_money);
});

    $('.click-th').click(function(){
        var startNum=2;
        var endNum = $(this).attr('colspan');
        $(this).prevAll('.click-th').each(function(){
            var colspan = $(this).attr('colspan');
            startNum += parseInt(colspan,10);
        });
        endNum = parseInt(endNum,10)+startNum;
        if($(this).hasClass('active')){
            $(this).text($(this).data('text')).removeClass('active');
            $('#sellTable>thead>tr').eq(0).children().slice(startNum,endNum).each(function(){
                var width = $(this).data('width')+'px';
                $(this).width(width);
            });
            $('#sellTable>thead>tr').eq(2).children().slice(startNum-2,endNum-2).each(function(){
                $(this).text($(this).data('text'));
            });
            $('#sellTable>tbody>tr').each(function(){
                $(this).children().slice(startNum,endNum).each(function(){
                    $(this).text($(this).data('text'));
                });
            });
        }else{
            $(this).data('text',$(this).text());
            $(this).text('.').addClass('active');
            $('#sellTable>thead>tr').eq(0).children().slice(startNum,endNum).each(function(){
                var width = '15px';
                switch(startNum){
                    case 7:
                    case 14:
                        width = '35px';
                        break;
                    case 21:
                        width = '25px';
                        break;
                    case 25:
                        width = '30px';
                        break;
                }
                $(this).width(width);
            });
            $('#sellTable>thead>tr').eq(2).children().slice(startNum-2,endNum-2).each(function(){
                $(this).data('text',$(this).text());
                $(this).text('');
            });
            $('#sellTable>tbody>tr').each(function(){
                $(this).children().slice(startNum,endNum).each(function(){
                    $(this).data('text',$(this).text());
                    $(this).text('');
                });
            });
        }
    });
    
    $('.click-tr').click(function(){
        var show = $(this).hasClass('show-tr');
        if(show){
            $(this).removeClass('show-tr');
        }else{
            $(this).addClass('show-tr');
        }
        $(this).parent('tr').nextAll('tr').each(function(){
            if($(this).hasClass('tr-end')||$(this).children('td:first').hasClass('click-tr')){
                return false;
            }else{
                if(show){
                    $(this).show();
                }else{
                    $(this).hide();
                }
            }
        });
    });
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);


$language = Yii::app()->language;
$js = "
$('table').on('change','[id^=\"SellTableForm_\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#SellTableForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('setFlag', $js, CClientScript::POS_READY);
$js = <<<EOF
$('table').on('click','#btnDelRow', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
	$('.changeCom:first').trigger('keyup');
});
EOF;
Yii::app()->clientScript->registerScript('removeRow', $js, CClientScript::POS_READY);
$js = "
$(document).ready(function(){
	var ct = $('#tblDetail tr').eq(1).html();
	$('#dtltemplate').attr('value',ct);
	$('.date').datepicker({autoclose: true,language: '{$language}', format: 'yyyy/mm/dd'});
});

$('#btnAddRow').on('click',function() {
	var r = $('#tblDetail tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate').val();
		$('#tblDetail tbody:last').append('<tr>'+ct+'</tr>');
		$('#tblDetail tr').eq(-1).find('[id*=\"SellTableForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);
			if (id.indexOf('_id') != -1) $(this).attr('value','0');
			if (id.indexOf('_hdrid') != -1) $(this).attr('value','0');
			if (id.indexOf('_type') != -1) $(this).attr('value','ia');				
			if (id.indexOf('_customer') != -1) $(this).attr('value','');
			if (id.indexOf('_information') != -1) $(this).attr('value','');
			if (id.indexOf('_date') != -1) {
				$(this).attr('value','');
				$(this).datepicker({ autoclose: true,language: '{$language}', format: 'yyyy/mm/dd'});
			}
			if (id.indexOf('_commission') != -1) $(this).attr('value','0');
		});
		if (nid != '') {
			var topos = $('#'+nid).position().top;
			$('#tbl_detail').scrollTop(topos);
		}
	}
});
	";
Yii::app()->clientScript->registerScript('addRow', $js, CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


