<?php
$ftrbtn = array();
$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'tripDialog',
    'header'=>"出差详情",
    'footer'=>$ftrbtn,
    'show'=>false,
));
?>

    <div class="form-group">
        <label class="col-sm-3 control-label">出差编号:</label>
        <div class="col-sm-2" style="overflow: visible;white-space: nowrap;">
            <p class="form-control-static" id="trip_code"></p>
        </div>
        <label class="col-sm-3 control-label">出差员工:</label>
        <div class="col-sm-4">
            <p class="form-control-static" id="trip_employee"></p>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-1">
            <table class="tblDetail table table-hover table-bordered">
                <thead>
                <tr>
                    <th width="50%">计划开始时间</th>
                    <th width="50%">计划结束时间</th>
                </tr>
                </thead>
                <tbody id="trip_date">
                </tbody>
            </table>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">目的地:</label>
        <div class="col-sm-7">
            <p class="form-control-static" id="trip_address"></p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">公司名称:</label>
        <div class="col-sm-7">
            <p class="form-control-static" id="trip_company"></p>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-10 col-sm-offset-1">
            <table class="tblDetail table table-hover table-bordered">
                <thead>
                <tr>
                    <th width="70%">出差项目名称</th>
                    <th width="30%">出差预估费用</th>
                </tr>
                </thead>
                <tbody id="trip_money">
                </tbody>
            </table>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">预估总费用:</label>
        <div class="col-sm-7">
            <p class="form-control-static" id="trip_cost"></p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">出差目的:</label>
        <div class="col-sm-7">
            <p class="form-control-static" id="trip_cause"></p>
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-4 control-label">出差结果说明:</label>
        <div class="col-sm-7">
            <p class="form-control-static" id="trip_result"></p>
        </div>
    </div>

<?php
$this->endWidget();
?>
//
<?php
$ajaxUrl = Yii::app()->createUrl('expenseApply/ajaxTrip');
$js = <<<EOF
$('.look-trip').on('click',function() {
    var trip_id = $(this).data('id');
    $.ajax({
        type:'post',
        url:'{$ajaxUrl}',
        data:{
            'trip_id':trip_id
        },
        dataType:'json',
        success:function(data){
            $("#trip_employee").text($("#employee").val());
            if(data.status==1){
                var list = data.data;
                $("#trip_code").text(list['trip_code']);
                $("#trip_cost").text(list['trip_cost']);
                $("#trip_cause").text(list['trip_cause']);
                $("#trip_address").text(list['trip_address']);
                $("#trip_company").text(list['trip_company']);
                $("#trip_result").text(list['trip_result']);
                $("#trip_date").html(list['trip_date']);
                $("#trip_money").html(list['trip_money']);
                
                $('#tripDialog').modal('show');
            }else{
                $('#errorModal .modal-body').eq(0).html("数据异常，请刷新重试");
                $('#errorModal').modal('show');
            }
        }
    });
});
EOF;
Yii::app()->clientScript->registerScript('changeTripLook',$js,CClientScript::POS_READY);
?>
