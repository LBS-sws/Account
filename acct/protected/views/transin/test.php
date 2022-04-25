<?php
$this->pageTitle=Yii::app()->name . ' - Transaction In Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'transin-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong>粘贴excel</strong>
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
				'submit'=>Yii::app()->createUrl('transin/index')));
		?>
        <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
            'submit'=>Yii::app()->createUrl('transin/testSave'),'disabled'=>"disabled","id"=>"saveBtn"));
        ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<div class="form-group">
				<?php echo TbHtml::label("excel复制文本","",array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-10">
                    <?php
                    echo TbHtml::textArea("excel","",array("rows"=>8,"id"=>"excel"))
                    ?>
				</div>
			</div>

            <div class="form-group">
                <?php echo TbHtml::label('交易类别','',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php
                    $allowadj = $model->adjustRight();
                    $list = General::getTransTypeList('IN',false,$allowadj);
                    echo $form->dropDownList($model, 'trans_type_code', $list,array('disabled'=>(false),'id'=>'trans_type_code'));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo TbHtml::label('账户','',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php
                    $list1 = General::getAccountList($model->city,'1');
                    $list = $list1;
                    echo $form->dropDownList($model, 'acct_id', $list,array('disabled'=>(false),'id'=>'acct_id'));
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'citem_desc',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php
                    echo $form->hiddenField($model, 'item_code',array("id"=>"item_code"));
                    echo $form->textField($model, 'citem_desc',
                        array('maxlength'=>500,'readonly'=>true,"id"=>"citem_desc",
                            'append'=>TbHtml::button('<span class="fa fa-search"></span> '.Yii::t('trans','Charge Item'),
                                array('name'=>'btnChargeItem','id'=>'btnChargeItem',
                                    'disabled'=>(false)
                                )
                            )
                        )
                    );
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'acct_code',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-4">
                    <?php
                    echo $form->hiddenField($model, 'acct_code',array("id"=>"acct_code"));
                    echo $form->textField($model, 'acct_code_desc',
                        array('readonly'=>true,"id"=>"acct_code_desc")
                    );
                    ?>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-12">
                    <div class="table-responsive">
                    <table class="table table-bordered table-hover table-condensed" style="table-layout: fixed">
                        <thead>
                        <tr>
                            <th width="25px"><input type="checkbox" name="checkbox" id="allBox"/></th>
                            <th width="95px">交易日期</th>
                            <th width="180px">交易类别</th>
                            <th width="300px">账户</th>
                            <th width="300px">付款人</th>
                            <th width="300px">出纳申请项目</th>
                            <th width="80px">会计编码</th>
                            <th width="60px">金額</th>
                            <th width="70px">备注1</th>
                            <th width="130px">识别状态</th>
                        </tr>
                        </thead>
                        <tbody id="tbody">
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>
<script>
    type_code_list = '<?php echo json_encode(General::getTransTypeList('IN',false,$allowadj));?>';
    type_code_list = JSON.parse(type_code_list);
    acct_id_list = '<?php echo json_encode(General::getAccountList($model->city,'1'));?>';
    acct_id_list = JSON.parse(acct_id_list);

    function validateSaveBtn(){
        if($("#excel").val()!=""&&$("#citem_desc").val()!=""&&$(".checkOne:checked").length>0){
            $("#saveBtn").removeClass("disabled").prop("disabled",false);
        }else{
            $("#saveBtn").addClass("disabled").prop("disabled",true);
        }
    }

    function fun_type_code(str) {
        var name = "",last;
        $.each(type_code_list,function (code, value) {
            last = code;
            if(value.indexOf(str)>=0){
                name = code;
            }
        });
        return name===""?last:name;
    }
    function fun_acct_id(str) {
        var name = "",last;
        $.each(acct_id_list,function (code, value) {
            last = code;
            if(value.indexOf(str)>=0){
                name = code;
            }
        });
        return name===""?last:name;
    }
    function fun_ajax_payer(list,validateList) {
        $.post("<?php echo Yii::app()->createUrl('transin/ajaxPayer'); ?>",
            {list:list},
            function(data){
                insertTable(data);
            },
            "json");
    }
     function selectHtml(name,value,list,cls){
        var html = '<select name="'+name+'" class="form-control '+cls+'">';
        $.each(list,function(key,item){
            if(value==key){
                html+='<option value="'+key+'" selected>'+item+'</option>';
            }else{
                html+='<option value="'+key+'">'+item+'</option>';
            }
        });
        return html+='</select>';
     }

    function insertTable(data) {
        var html ="";
        $.each(data,function (key, row) {
            if(row['status']==1){
                html+='<tr>';
                html+='<td>';
                html+='<input type="checkbox" name="test['+key+'][status]" class="checkOne">';
                html+='<input type="hidden" name="test['+key+'][trans_dt]" value="'+row['trans_dt']+'">';
                html+='<input type="hidden" name="test['+key+'][amount]" value="'+row['amount']+'">';
                html+='<input type="hidden" name="test['+key+'][trans_desc]" value="'+row['trans_desc']+'">';
                html+='<input type="hidden" name="test['+key+'][payer_type]" value="'+row['payer_type']+'">';
                html+='<input type="hidden" name="test['+key+'][payer_name]" value="'+row['payer_name']+'">';
                html+='<input type="hidden" name="test['+key+'][payer_id]" value="'+row['payer_id']+'">';
                html+='</td>';
            }else{
                html+='<tr class="danger">';
                html+='<td>&nbsp;</td>';
            }
            html+='<td>'+row['trans_dt']+'</td>';
            if(row['status']==1){
                html+='<td>'+selectHtml('test['+key+'][trans_type_code]',row['trans_type_code'],type_code_list,'trans_type_code')+'</td>';
                html+='<td>'+selectHtml('test['+key+'][acct_id]',row['acct_id'],acct_id_list,'acct_id')+'</td>';
            }else{
                html+='<td>'+row['trans_type_code']+'</td>';
                html+='<td>'+row['acct_id']+'</td>';
            }
            html+='<td>'+row['payer_name']+'</td>';
            if(row['status']==1){
                html+='<td class="citem_desc">'+row['citem_desc']+'</td>';
                html+='<td class="acct_code_desc">'+row['acct_code_desc']+'</td>';
            }else{
                html+='<td>'+row['citem_desc']+'</td>';
                html+='<td>'+row['acct_code_desc']+'</td>';
            }
            html+='<td>'+row['amount']+'</td>';
            html+='<td>'+row['trans_desc']+'</td>';

            if(row['status']==1){
                html+='<td>正常</td>';
            }else{
                html+='<td>'+row['error']+'</td>';
            }
            html+='</tr>';
        });
        $("#tbody").html(html);
    }

    $(function ($) {
        $("#excel").change(function () {
            var text = $(this).val();
            var list = text.split("\n");
            var data = [],validateList=[];
            var arr,payerList;
            $.each(list,function (key, row) {
                row = row.split("\t");
                arr={
                    "trans_dt":"",//交易日期
                    "trans_type_code":"",//交易类别
                    "acct_id":"",//账户
                    "payer_type":"",//付款人类别
                    "payer_code":"",//付款人编号
                    "payer_name":"",//付款人
                    "payer_id":"",//付款人
                    "citem_desc":"",//出纳申请项目
                    "item_code":"",//出纳申请项目
                    "acct_code_desc":"",//会计编码
                    "amount":"",//金額
                    "trans_desc":"",//备注1
                    "status":1//识别状态
                };
                if(row.length>=7){
                    if(row[4].indexOf('扫码')>=0){
                        row[4] = '扫码';
                    }
                    if(row[4].indexOf('一般户')>=0){
                        row[4] = '一般户';
                    }
                    if(row[4].indexOf('现金')>=0){
                        row[4] = '现金';
                    }
                    arr['trans_dt']=row[0];
                    arr['trans_type_code']=fun_type_code(row[4]);
                    arr['acct_id']=fun_acct_id(row[4]);
                    arr['payer_name']=""+row[3]+row[2];
                    arr['payer_code']=row[3];
                    arr['payer_type']="C";
                    arr['amount']=row[5];
                    arr['trans_desc']=row[6];
                    if(row[1]=="收款单"){
                        if(!isNaN(arr['amount'])){
                            validateList.push(row[3]);
                            arr['payer_id']=0;
                            arr['citem_desc']=$("#citem_desc").val();
                            arr['item_code']=$("#item_code").val();
                            arr['acct_code_desc']=$("#acct_code_desc").val();
                        }else{
                            arr['status']=0;
                            arr['error']="金额必须为数字";
                        }
                    }else{
                        arr['status']=0;
                        arr['error']="单据类型必须为:收款单";
                    }
                }else{
                    arr['status']=0;
                    arr['error']="数据异常";
                }
                data.push(arr);
            });
            fun_ajax_payer(data,validateList);
            validateSaveBtn();
        });

        $("#btnLookupSelect").click(function () {
            $('#lookupdialog').modal('hide');
            if($("#lstlookup option:selected").length==1){
                var text = $("#lstlookup option:selected").eq(0).text();
                var val = $("#lstlookup option:selected").eq(0).attr("value");
                var acct_code = $("#otherfld_"+val+"_acctcode").val();
                var acct_code_desc = $("#otherfld_"+val+"_acctcodedesc").val();
                $("#item_code").val(val);
                $("#citem_desc").val(text);
                $("#acct_code").val(acct_code);
                $("#acct_code_desc").val(acct_code_desc);
                $(".acct_code_desc").text(acct_code_desc);
                $(".citem_desc").text(text);
            }
            validateSaveBtn();
        });

        $("#acct_id,#trans_type_code").change(function () {
            var id = $(this).attr("id");
            var val = $(this).val();
            if(val!=""){
                $("."+id).val(val);
            }
        });

        $("#allBox").click(function () {
            if($(this).prop("checked")){
                $(".checkOne").prop("checked",true);
            }else{
                $(".checkOne").prop("checked",false);
            }
            validateSaveBtn();
        });
        $("#tbody").delegate(".checkOne","change",function () {
            validateSaveBtn();
        });
    })
</script>
<?php $this->renderPartial('//site/lookup'); ?>

<?php

$js = Script::genLookupSearchEx();
Yii::app()->clientScript->registerScript('lookupSearch',$js,CClientScript::POS_READY);

$js = Script::genLookupButtonEx('btnChargeItem', 'accountitemin', 'item_code', 'citem_desc',
    array('acctcode'=>'TransInForm_acct_code','acctcodedesc'=>'TransInForm_acct_code_desc',),
    false,
    array('acctid'=>'acct_id',)
);
Yii::app()->clientScript->registerScript('lookupChargeItem',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


