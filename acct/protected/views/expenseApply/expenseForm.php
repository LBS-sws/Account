<?php
$employeeList = ExpenseFun::getEmployeeListForID($model->employee_id);
?>
<?php if ($model->status_type==3): ?>
<div class="form-group has-error">
    <?php echo $form->labelEx($model,'reject_note',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-5">
        <?php
        echo $form->textArea($model, 'reject_note',array('readonly'=>true,'rows'=>4));
        ?>
    </div>
</div>
<?php endif ?>
<div class="form-group">
    <?php echo $form->labelEx($model,'employee',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("employee",$employeeList["employee"],array('readonly'=>true));
        ?>
    </div>
    <?php echo $form->labelEx($model,'department',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo TbHtml::textField("department",$employeeList["department"],array('readonly'=>true));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo Tbhtml::label(Yii::t("give","payment company"),'payment_company',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        $payment_company = in_array($model->status_type,array(0,3))?ExpenseFun::getCompanyIdToEmployeeID($model->employee_id):$model->tableDetail["payment_company"];
        echo $form->hiddenField($model, 'tableDetail[payment_company]');
        echo TbHtml::textField("payment_company", ExpenseFun::getCompanyNameToID($payment_company),
            array('readonly'=>true,'id'=>'payment_company'
            ));
        ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model,'apply_date',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-2">
        <?php
        echo $form->textField($model, 'apply_date',
            array('readonly'=>$model->readonly(),'autocomplete'=>'off',
                'prepend'=>'<span class="fa fa-calendar"></span> ',
            ));
        ?>
    </div>
    <?php if ($model->scenario!='new'): ?>
        <?php echo $form->labelEx($model,'exp_code',array('class'=>"col-sm-2 control-label")); ?>
        <div class="col-sm-2">
            <?php
            echo $form->textField($model, 'exp_code',array('readonly'=>true));
            ?>
        </div>
    <?php endif ?>
</div>
<div class="form-group">
    <?php echo Tbhtml::label(Yii::t("give","trip bool"),'trip_bool',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->inlineRadioButtonList($model, 'tableDetail[trip_bool]',ExpenseFun::getNoOrYesList(),
            array('readonly'=>$model->readonly(),'id'=>'trip_bool'
            ));
        ?>
    </div>
</div>
<div class="form-group" id="trip_select_div" style="<?php echo empty($model->tableDetail["trip_bool"])?"display:none;":"";?>">
    <?php echo Tbhtml::label(Yii::t("give","trip id"),'trip_id',array('class'=>"col-sm-2 control-label")); ?>
    <?php
        if($model->readonly()){//不允许修改
            $html='<div class="col-sm-7">';
            $html.=$form->hiddenField($model,"tableDetail[trip_id]");

            $html.='<div class="input-group">';
            $html.=Tbhtml::textField("trip_name",ExpenseFun::getTripNameForTripID($model->tableDetail["trip_id"]),array(
                "readonly"=>true
            ));
            $html.='<span class="input-group-btn">';
            $html.='<button class="btn btn-default" id="look_trip" type="button">&nbsp;<span class="fa fa-eye text-primary"></span>&nbsp;</button>';
            $html.='</span>';
            $html.='</div>';

            $html.='</div>';
            //input-group-btn
        }else{
            $html='<div class="col-sm-7">';
            $html.=$form->dropDownList($model, 'tableDetail[trip_id]',ExpenseFun::getTripListForEmployeeID($model->employee_id,$model->tableDetail["trip_id"]),
                array('readonly'=>$model->readonly(),'id'=>'trip_id','empty'=>''
                ));
            $html.='</div>';
            $html.='<div class="col-sm-7 col-sm-offset-2">';
            $html.='<p class="form-control-static text-danger">只能选择已完成的出差申请</p>';
            $html.='</div>';
        }
        echo $html;
    ?>
</div>
<div class="form-group">
    <?php echo Tbhtml::label(Yii::t("give","local bool"),'local_bool',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        $localId=ExpenseFun::getLocalSetIdToCity($model->city);
        echo TbHtml::hiddenField("localSetID",$localId,array("id"=>"localSetID"));
        echo $form->inlineRadioButtonList($model, 'tableDetail[local_bool]',ExpenseFun::getNoOrYesList(),
            array('readonly'=>$model->readonly(),'id'=>'local_bool'
            ));
        ?>
    </div>
</div>
<div class="form-group">
    <?php echo Tbhtml::label(Yii::t("give","payment condition"),'payment_condition',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-3">
        <?php
        echo $form->dropDownList($model, 'tableDetail[payment_condition]',ExpenseFun::getPaymentConditionList(),
            array('readonly'=>$model->readonly(),'id'=>'payment_condition','empty'=>''
            ));
        ?>
    </div>
</div>

<legend><?php echo Yii::t("give","Expense Detail");?></legend>
<div class="box">
    <div class="box-body table-responsive">
        <?php
        $this->widget('ext.layout.TableView2Widget', array(
            'model'=>$model,
            'tableClass'=>' table-fixed table-condensed table-bordered',
            'attribute'=>'infoDetail',
            'viewhdr'=>'//expenseApply/_formhdr',
            'viewdtl'=>'//expenseApply/_formdtl',
            'viewFoot'=>'//expenseApply/_formFoot',
        ));
        ?>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model,'remark',array('class'=>"col-sm-2 control-label")); ?>
    <div class="col-sm-4">
        <?php
        echo $form->textArea($model, 'remark',array('readonly'=>$model->readonly(),'rows'=>4));
        ?>
    </div>
</div>

<?php
$js = <<<EOF
$('input[name="ExpenseApplyForm[tableDetail][trip_bool]"]').on('click',function() {
    if($(this).val()==0){
        $('#trip_select_div').slideUp(100);
    }else{
        $('#trip_select_div').slideDown(100);
    }
});
$('input[name="ExpenseApplyForm[tableDetail][local_bool]"]').on('click',function() {
    if($(this).val()==0){
        $('select.setId').removeAttr('readonly').removeClass('readonly');
    }else{
        $('select.setId').attr('readonly','readonly').addClass('readonly').val($("#localSetID").val());
    }
});
$('#tblDetail').on('change','.changeAmtType',function(){
    var amtType = ""+$(this).val();
    switch(amtType){
        case "0"://本地费用
            $(this).parents('tr').eq(0).find('.infoRemark').attr('placeholder','202406市内交通费“或”XX会议餐费');
            break;
        case "1"://差旅费用
            $(this).parents('tr').eq(0).find('.infoRemark').attr('placeholder','202406XX项目上海-广州差旅费-酒店2晚/高铁往返');
            break;
        case "2"://办公费
            $(this).parents('tr').eq(0).find('.infoRemark').attr('placeholder','202406采购办公用品一批-附清单"或零星采购时“202406办公用品-文件袋');
            break;
        case "3"://快递费
            $(this).parents('tr').eq(0).find('.infoRemark').attr('placeholder','202406顺丰快递XX客户到付件“或“202406XX货品采购快递费');
            break;
        case "4"://通讯费
            $(this).parents('tr').eq(0).find('.infoRemark').attr('placeholder','202405-202406电话费-号码138xxxxxxxx');
            break;
        case "5"://其他
            $(this).parents('tr').eq(0).find('.infoRemark').attr('placeholder','20240604因XX客户临时购买1把扳手”或”代付202405XX办事处水电费');
            break;
        default:
            $(this).parents('tr').eq(0).find('.infoRemark').attr('placeholder','');
    }
});
$('.changeAmtType').trigger('change');
EOF;
Yii::app()->clientScript->registerScript('changeTripDiv',$js,CClientScript::POS_READY);
?>