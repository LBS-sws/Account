<?php
$this->pageTitle=Yii::app()->name . ' - Salestable Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'salestable-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

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
				'submit'=>Yii::app()->createUrl('salestable/index')));
		?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('salestable/save')));
			?>
        <?php if( Yii::app()->user->validFunction('CN12')&&$model->examine=='Y'){ ?>
            <?php  echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Audit'), array(
            'submit'=>Yii::app()->createUrl('salestable/audit')));
            ?>
            <?php  echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Deny'), array(
                'data-toggle'=>'modal','data-target'=>'#jectdialog'));
            ?>
        <?php  }else{  if($model->examine=='S'||$model->examine=='N')echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Approval'), array(
            'submit'=>Yii::app()->createUrl('salestable/examine')));
        }?>
	</div>



	<div class="btn-group pull-right" role="group">
        <?php echo TbHtml::button('<span class="fa fa-cloud-download"></span> '.Yii::t('misc','Down'), array(
            'submit'=>Yii::app()->createUrl('salestable/down')));
        ?>
	</div>
	</div></div>


	<div class="box box-info">
        <h3>&nbsp;&nbsp;&nbsp;<?php echo $model->year."年".$model->month."月".' '.$model->sale."提成表"."(".$model->examine.")";?></h3>
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo CHtml::hiddenField('dtltemplate'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>
            <?php echo $form->hiddenField($model, 'examine'); ?>


            <div class="form-group" style="margin-left: 2px;">

                <style type="text/css">
                    .tftable {font-size:12px;width:99%;text-align: center;}
                    .tftable th {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;text-align:left;text-align: center;}
                    .tftable td {font-size:12px;border-width: 1px;padding: 8px;border-style: solid;}

                </style>
                <div class="form-group">
                    <?php echo $form->labelEx($model,'examine',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-3">
                        <?php echo $form->dropDownList($model, 'examine',
                            array(
                                'N'=>Yii::t('salestable','Not reviewed'),
                                'A'=>Yii::t('salestable','Adopt'),
                                'S'=>Yii::t('salestable','Rejected'),
                                'Y'=>Yii::t('salestable','Reviewed'),
                            ),
                            array('disabled'=>'disabled')
                        ); ?>
                    </div>
                    <?php if($model->examine=='S'){echo $form->labelEx($model,'ject_remark',array('class'=>"col-sm-2 control-label")); ?>
                    <div class="col-sm-4">
                        <?php echo $form->textArea($model, 'ject_remark',
                            array('size'=>30,'maxlength'=>200,'readonly'=>'readonly')
                        ); ?>
                    </div>
                    <?php }?>
                </div>
                <table class="tftable" border="1" >
                    <tr><th rowspan="2">日期</th><th rowspan="2">客户名称</th><th colspan="4">IA（清洁）</th><th colspan="4">IB（灭虫）</th><th colspan="4">IC（租机）</th><th rowspan="2">焗雾/白蚁/甲醛/雾化消毒</th><th rowspan="2">I（装机费）</th><th colspan="7">销售</th></tr>
                    <tr><th>IA费/月</th><th>续约IA费/月</th><th>终止IA费/月</th><th>续约终止费/月</th><th>IB费/月</th><th>续约IB费/月</th><th>终止IB费/月</th><th>续约终止费/月</th><th>IC费/月</th><th>续约IC费/月</th><th>终止IC费/月</th><th>续约终止费/月</th><th>纸品系列</th><th>消毒液及皂液</th><th>空气净化</th><th>化学剂</th><th>香薰系列</th><th>虫控系列</th><th>其他</th></tr>
                    <?php if(!empty($model->group)){foreach ($model->group as $value){?>
                    <tr <?php if(!empty($value['othersalesman'])){echo "style='color: red'";}?>><td><?php echo $value['status_dt'];?></td><td><?php echo $value['company_name'];?></td><td><?php echo $value['ia'];?></td><td><?php echo $value['ia_c'];?></td><td><?php echo $value['ia_end'];?></td><td><?php echo $value['ia_c_end'];?></td><td><?php echo $value['ib'];?></td><td><?php echo $value['ib_c'];?></td><td><?php echo $value['ib_end'];?></td><td><?php echo $value['ib_c_end'];?></td><td><?php echo $value['ic'];?></td><td><?php echo $value['ic_c'];?></td><td><?php echo $value['ic_end'];?></td><td><?php echo $value['ic_c_end'];?></td><td><?php echo $value['amt_paid'];?></td><td><?php echo $value['amt_install'];?></td><td><?php echo $value['paper'];?></td><td><?php echo $value['disinfectant'];?></td><td><?php echo $value['purification'];?></td><td><?php echo $value['chemical'];?></td><td><?php echo $value['aromatherapy'];?></td><td><?php echo $value['pestcontrol'];?></td><td><?php echo $value['other'];?></td></tr>
                    <?php }}?>
                    <tr style="background-color: #acc8cc"><td></td><td>月营业额</td><td><?php echo $model->ia;?></td><td><?php echo $model->ia_c;?></td><td><?php echo $model->ia_end;?></td><td><?php echo $model->ia_c_end;?></td><td><?php echo $model->ib;?></td><td><?php echo $model->ib_c;?></td><td><?php echo $model->ib_end;?></td><td><?php echo $model->ib_c_end;?></td><td><?php echo $model->ic;?></td><td><?php echo $model->ic_c;?></td><td><?php echo $model->ic_end;?></td><td><?php echo $model->ic_c_end;?></td><td><?php echo $model->amt_paid;?></td><td><?php echo $model->amt_install;?></td><td><?php echo $model->paper;?></td><td><?php echo $model->disinfectant;?></td><td><?php echo $model->purification;?></td><td><?php echo $model->chemical;?></td><td><?php echo $model->aromatherapy;?></td><td><?php echo $model->pestcontrol;?></td><td><?php echo $model->other;?></td></tr>
                    <tr style="background-color: #acc8cc"><td></td><td>年营业额</td><td><?php echo $model->y_ia;?></td><td><?php echo $model->y_ia_c;?></td><td><?php echo $model->y_ia_end;?></td><td><?php echo $model->y_ia_c_end;?></td><td><?php echo $model->y_ib;?></td><td><?php echo $model->y_ib_c;?></td><td><?php echo $model->y_ib_end;?></td><td><?php echo $model->y_ib_c_end;?></td><td><?php echo $model->y_ic;?></td><td><?php echo $model->y_ic_c;?></td><td><?php echo $model->y_ic_end;?></td><td><?php echo $model->y_ic_c_end;?></td><td><?php echo $model->y_amt_paid;?></td><td><?php echo $model->amt_install;?></td><td colspan="7"><?php echo $model->all_sale;?></td></tr>
                    <tr><td></td><td>新客户IA/IB/IC营业额</td><td colspan="22"><?php echo $model->abc_money;?></td></tr>
                    <tr><td rowspan="2">名称</td><td rowspan="2"></td><td colspan="12">本月新客户营业提成</td><td colspan="2">本月续约营业提成</td><td colspan="8">本月扣除停止客户营业提成</td></tr>
                    <tr><td colspan="2">IA（清洁）</td><td colspan="2">IB（灭虫）</td><td colspan="2">焗雾/白蚁/甲醛/雾化消毒</td><td colspan="1">IC（租机）</td><td colspan="2">I（装机费）</td><td>销售</td><td colspan="2">化学剂（洗地易）</td><td colspan="2">续约</td><td colspan="2">IA（清洁）</td><td colspan="2">IB（灭虫）</td><td colspan="2">IC（租机）</td><td >续约</td></tr>
                    <tr><td>提成点数</td><td></td><td colspan="2"><?php echo $model->ia_royalty."%";?></td><td colspan="2"><?php echo $model->ib_royalty."%";?></td><td colspan="2"><?php echo $model->amt_paid_royalty."%";?></td><td colspan="1"><?php echo $model->ic_royalty."%";?></td><td colspan="2"><?php $a=$model->amt_install_royalty*100;echo $a."%";?></td><td ><?php echo $model->sale_royalty;?></td><td colspan="2"><?php echo $model->huaxueji_royalty."%";?></td><td colspan="2"><?php echo $model->xuyue_royalty."%";?></td><td colspan="2">/</td><td colspan="2">/</td><td colspan="2">/</td><td>1%</td></tr>
                    <tr style="background-color: #bedda7"><td>金额</td><td></td><td colspan="2"><?php echo $model->ia_money;?></td><td colspan="2"><?php echo $model->ib_money;?></td><td colspan="2"><?php echo $model->amt_paid_money;?></td><td colspan="1"><?php echo $model->ic_money;?></td><td colspan="2"><?php echo $model->amt_install_money;?></td><td ><?php echo $model->sale_money;?></td><td colspan="2"><?php echo $model->huaxueji_money;?></td><td colspan="2"><?php echo $model->xuyue_money;?></td><td colspan="2"><?php echo $model->ia_end_money;?></td><td colspan="2"><?php echo $model->ib_end_money;?></td><td colspan="2"><?php echo $model->ic_end_money;?></td><td><?php echo $model->xuyuezhong_money;?></td></tr>
                    <tr style="background-color: #acc8cc"><td>金额合计</td><td></td><td colspan="14"><?php echo $model->add_money;?></td><td colspan="8"><?php echo $model->reduce_money;?></td></tr>
                </table>


            </div>
            <?php
            $this->renderPartial('//site/ject',array(
                'form'=>$form,
                'model'=>$model,
                'rejectName'=>"ject_remark",
                'submit'=>Yii::app()->createUrl('salestable/reject'),
            ));
            ?>
<?php //print_r('<pre>');print_r($model);?>
			<div class="form-group">
<!--				--><?php //echo $form->labelEx('$model','',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2 control-label">
                    装机提成比例(请输入小数,显示百分数)
                </div>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'amt_install_royalty',
						array('rows'=>3,'cols'=>60,'maxlength'=>200,'readonly'=>$model->isReadOnly())
					); ?>
				</div>
                <div class="col-sm-2 control-label">
                    补充金额合计
                </div>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'supplement_money',
                        array('rows'=>3,'cols'=>60,'maxlength'=>200,'readonly'=>$model->isReadOnly())
                    ); ?>
                </div>
                <div class="col-sm-2 control-label">
                    最终金额合计
                </div>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'final_money',
                        array('rows'=>3,'cols'=>60,'maxlength'=>200,'readonly'=>$model->isReadOnly())
                    ); ?>
                </div>
			</div>

		</div>
	</div>
    <div class="box">
        <div class="box-body table-responsive">
            <legend><?php echo Yii::t('salestable','Supplementary notes'); ?></legend>
            <h4>除以上提成金额，如还有特殊情况，系统未能计算，请手动在下面增加 </h4>
            <?php $this->widget('ext.layout.TableView2Widget', array(
                'model'=>$model,
                'attribute'=>'detail',
                'viewhdr'=>'//salestable/_formhdr',
                'viewdtl'=>'//salestable/_formdtl',
                'gridsize'=>'24',
                'height'=>'200',
            ));
            ?>
        </div>
    </div>
    </div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//site/lookup'); ?>



<?php
//Script::genFileUpload($model,$form->id,'TRANS');
//
//$defaclist = General::getJsDefaultAccountList();

//$js = <<<EOF
//var defacc = { $defaclist };
//$('#TransInForm_trans_type_code').on('change', function() {
//	var choice = $(this).val();
//	var target = $('#TransInForm_acct_id').val();
////	if (target==0) {
//		$('#TransInForm_acct_id').val(defacc[choice]);
////	}
//});
//EOF;
//Yii::app()->clientScript->registerScript('defaultAc',$js,CClientScript::POS_READY);

//$js = Script::genLookupSearchEx();
//Yii::app()->clientScript->registerScript('lookupSearch',$js,CClientScript::POS_READY);
//
//$defButtonSts = $model->isReadOnly() ? 'true' : 'false';
//switch ($model->payer_type) {
//	case 'F': $defLookupType = 'staff'; break;
//	case 'S': $defLookupType = 'supplier'; break;
//	default: $defLookupType = 'company';
//}
//
////$js .= Script::genLookupButtonEx('btnPayer', '*', 'payer_id', 'payer_name');
//Yii::app()->clientScript->registerScript('lookupPayer',$js,CClientScript::POS_READY);
//
//$js .= Script::genLookupButtonEx('btnStaff', 'staff', 'handle_staff', 'handle_staff_name');
//Yii::app()->clientScript->registerScript('lookupStaff',$js,CClientScript::POS_READY);
//
//$js = Script::genLookupButtonEx('btnChargeItem', 'accountitemin', 'item_code', 'citem_desc',
//		array('acctcode'=>'TransInForm_acct_code','acctcodedesc'=>'TransInForm_acct_code_desc',),
//		false,
//		array('acctid'=>'TransInForm_acct_id',)
//	);
//Yii::app()->clientScript->registerScript('lookupChargeItem',$js,CClientScript::POS_READY);
//
//$js .= Script::genLookupButtonEx('btnProduct', 'product', 'product_id', 'product_name');
//Yii::app()->clientScript->registerScript('lookupProduct',$js,CClientScript::POS_READY);
//
//$js = Script::genLookupSelect();
//Yii::app()->clientScript->registerScript('lookupSelect',$js,CClientScript::POS_READY);

//$link = Yii::app()->createUrl('transin/delete');
//$js = "
//$('#btnDeleteData').on('click',function() {
//	$('#removedialog').modal('hide');
//	$('#rmkdialog').modal('show');
//});
//";
//Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

//if (!$model->isReadOnly()) {
//	$js = Script::genDatePicker(array(
//			'TransInForm_trans_dt',
//		));
//	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
//}
$js = "
$('table').on('change','[id^=\"SalesTableForm_\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#SalesTableForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);
$js = <<<EOF
$('table').on('click','#btnDelRow', function() {
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
	$(this).closest('tr').hide();
});
EOF;
Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);
$js = "
$(document).ready(function(){
	var ct = $('#tblDetail tr').eq(1).html();
	$('#dtltemplate').attr('value',ct);
	$('.date').datepicker({autoclose: true, format: 'yyyy/mm/dd'});
});

$('#btnAddRow').on('click',function() {
	var r = $('#tblDetail tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate').val();
		$('#tblDetail tbody:last').append('<tr>'+ct+'</tr>');
		$('#tblDetail tr').eq(-1).find('[id*=\"SalesTableForm_\"]').each(function(index) {
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
				$(this).datepicker({autoclose: true, format: 'yyyy/mm/dd'});
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
Yii::app()->clientScript->registerScript('addRow',$js,CClientScript::POS_READY);
$js = Script::genDatePicker(array(
    'SalesTableForm_date',
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


