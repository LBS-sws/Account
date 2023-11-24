<?php
	$ftrbtn = array();
	$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','class'=>'pull-left','color'=>TbHtml::BUTTON_COLOR_DEFAULT));
	$ftrbtn[] = TbHtml::button(Yii::t('trans','down'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'id'=>'btnDownPay'));
	$this->beginWidget('bootstrap.widgets.TbModal', array(
					'id'=>'downPayDialog',
					'header'=>Yii::t('trans','down'),
					'footer'=>$ftrbtn,
					'show'=>false,
				));
?>
<style>
    .inline-select>.select{ display: inline;padding: 6px 4px;}
</style>

<div class="form-horizontal">
    <div class="form-group">
        <?php echo TbHtml::label(Yii::t("trans","City"),'',array('class'=>"col-lg-4 control-label")); ?>
        <div class="col-lg-4">
            <?php
            echo TbHtml::dropDownList("down_city",2,PayrollList::getCityList(),array("readonly"=>false));
            ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo TbHtml::label(Yii::t("trans","Search date"),'',array('class'=>"col-lg-4 control-label")); ?>
        <div class="col-lg-8 inline-select">
            <?php
            $year = date("Y");
            $month = date("n");
            echo TbHtml::dropDownList("year_start",$year,PayrollList::getYearList(),array("class"=>"select","style"=>"width:85px;height:34px;"));
            ?>
            <?php
            echo TbHtml::dropDownList("month_start",1,PayrollList::getMonthList(),array("class"=>"select","style"=>"width:60px;height:34px;"));
            ?>
            <?php echo Yii::t('trans','To');?>
            <?php
            echo TbHtml::dropDownList("year_end",$year,PayrollList::getYearList(),array("class"=>"select","style"=>"width:85px;height:34px;"));
            ?>
            <?php
            echo TbHtml::dropDownList("month_end",$month,PayrollList::getMonthList(),array("class"=>"select","style"=>"width:60px;height:34px;"));
            ?>
        </div>
    </div>
</div>

<?php
	$this->endWidget();
?>
<?php
$assignUrl = Yii::app()->createUrl('payroll/downExcel');
$js = <<<EOF
$('#allot_type').on('change',function(){
    if($(this).val()==1){
        $('#allot_city_div').hide();
        $('#allot_employee_div').show();
    }else{
        $('#allot_employee_div').hide();
        $('#allot_city_div').show();
    }
});

$('#btnDownPay').on('click',function(){
    var data = {};
    data['city']=$('#down_city').val();
    data['year_start']=$('#year_start').val();
    data['year_end']=$('#year_end').val();
    data['month_start']=$('#month_start').val();
    data['month_end']=$('#month_end').val();
    jQuery.yii.submitForm(this,'{$assignUrl}',data);
});
EOF;
Yii::app()->clientScript->registerScript('downPay',$js,CClientScript::POS_READY);

?>