<?php
$this->pageTitle=Yii::app()->name . ' - RemitAudit Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'RemitAudit-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    input[readonly],select[readonly],label[readonly]{ pointer-events: none;}
    .table-fixed{ table-layout: fixed;}
    .table-fixed>tbody>tr>th{ vertical-align: bottom;}
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Remit Audit'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('remitAudit/index')));
		?>
<?php if ($model->status_type==2&&$model->current_username==Yii::app()->user->id): ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Approve'), array(
                'submit'=>Yii::app()->createUrl('remitAudit/audit')));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Deny'), array(
                    'data-toggle'=>'modal','data-target'=>'#denyDialog',)
            );
            ?>
<?php endif ?>
	</div>

            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('give','Flow Info'), array(
                        'data-toggle'=>'modal','data-target'=>'#flowinfodialog',)
                );
                ?>
                <?php
                $counter = ($model->no_of_attm['expen'] > 0) ? ' <span id="docexpen" class="label label-info">'.$model->no_of_attm['expen'].'</span>' : ' <span id="docexpen"></span>';
                echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
                        'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadexpen',)
                );
                ?>
            </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'employee_id'); ?>
			<?php echo $form->hiddenField($model, 'status_type'); ?>
            <?php echo CHtml::hiddenField('dtltemplate'); ?>

            <?php $this->renderPartial('//remitApply/remitForm',array("model"=>$model,"form"=>$form)); ?>

            <?php $this->renderPartial('//remitApply/remitAudit',array("model"=>$model,"form"=>$form)); ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//remitApply/remitHistory',array("model"=>$model)); ?>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'EXPEN',
    'header'=>Yii::t('dialog','File Attachment'),
    'ronly'=>$model->readonly(),
));?>

<?php
$content="<div class=\"form-group\">";
$content.=$form->labelEx($model,'reject_note',array('class'=>"col-lg-3 control-label"));
$content.="<div class=\"col-lg-8\">";
$content.=$form->textArea($model, 'reject_note',
    array('readonly'=>false,'id'=>'reject_note','rows'=>4)
);
$content.="</div></div>";
$this->widget('bootstrap.widgets.TbModal', array(
    'id'=>'denyDialog',
    'header'=>Yii::t('misc','Deny'),
    'content'=>$content,
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'submit'=>Yii::app()->createUrl('remitAudit/reject'))),
        TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));

?>

<?php
Script::genFileUpload($model,$form->id,'EXPEN');
$language = Yii::app()->language;

$js = "
$('#tblDetail').on('change','.changeAmtType',function() {
    var amt_type = $(this).val();
    $(this).parents('tr').find('.changeNumber').val('').prop('readonly',true).addClass('readonly').trigger('change');
    if(amt_type!==''){
        $(this).parents('tr').find('.changeNumber[data-type=\"'+amt_type+'\"]').prop('readonly',false).removeClass('readonly');
    }
});
$('#tblDetail').on('change keyup','.changeNumber',function() {
    var changeSumNumber='';
    $(this).parents('.changeTr').find('.changeNumber').each(function(){
        var this_num = $(this).val();
        if(this_num!=''){
            changeSumNumber = changeSumNumber===''?0:changeSumNumber;
            changeSumNumber+=parseFloat(this_num);
        }
    });
    if (typeof changeSumNumber === 'number') {
        changeSumNumber=changeSumNumber.toFixed(2); 
    }
    $(this).parents('.changeTr').find('.changeSumNumber').val(changeSumNumber);
    $('.changeSumNumber:first').trigger('change');
});
$('#tblDetail').on('change keyup','.changeNumber,.changeSumNumber',function() {
    var td_num = $(this).parent('td').index();
    var sum_amt = '';
    $('.changeTr').each(function(){
        var this_num = $(this).find('td').eq(td_num).find('input').val();
        if(this_num!=''){
            sum_amt = sum_amt===''?0:sum_amt;
            sum_amt+=parseFloat(this_num);
        }
    });
    td_num-=3;
    if (typeof sum_amt === 'number') {
        sum_amt=sum_amt.toFixed(2);
    }
    $('.changeFootOne>th').eq(td_num).text(sum_amt);
    
    if($('.changeFootOne>th').eq(td_num).attr('id')=='changeFootSumNum'){
        $('#changeFootSumStr').data('money',sum_amt).trigger('change');
    }
});

$('#changeFootSumStr').on('change',function() {
    var money = $(this).data('money');
    if(money!=''&&money!=undefined){
        money = parseFloat(money);
        money = money.toFixed(2);
        var money_str = convertCurrency(money);
        $(this).text(money_str);
    }
});

function convertCurrency(money) {  
　　var cnNums = new Array(\"零\", \"壹\", \"贰\", \"叁\", \"肆\", \"伍\", \"陆\", \"柒\", \"捌\", \"玖\"); //汉字的数字  
　　var cnIntRadice = new Array(\"\", \"拾\", \"佰\", \"仟\"); //基本单位  
　　var cnIntUnits = new Array(\"\", \"万\", \"亿\", \"兆\"); //对应整数部分扩展单位  
　　var cnDecUnits = new Array(\"角\", \"分\", \"毫\", \"厘\"); //对应小数部分单位  
　　var cnInteger = \"整\"; //整数金额时后面跟的字符  
　　var cnIntLast = \"元\"; //整型完以后的单位  
　　var maxNum = 999999999999999.9999; //最大处理的数字  
　　var IntegerNum; //金额整数部分  
　　var DecimalNum; //金额小数部分  
　　var ChineseStr = \"\"; //输出的中文金额字符串  
　　var parts; //分离金额后用的数组，预定义  
　　if (money == \"\") {  
　　return \"\";  
　　}  
　　money = parseFloat(money);  
　　if (money >= maxNum) {  
　　alert('超出最大处理数字');  
　　return \"\";  
　　}  
　　if (money == 0) {  
　　ChineseStr = cnNums[0] + cnIntLast + cnInteger;  
　　return ChineseStr;  
　　}  
　　money = money.toString(); //转换为字符串  
　　if (money.indexOf(\".\") == -1) {  
　　IntegerNum = money;  
　　DecimalNum = '';  
　　} else {  
　　parts = money.split(\".\");  
　　IntegerNum = parts[0];  
　　DecimalNum = parts[1].substr(0, 4);  
　　}  
　　if (parseInt(IntegerNum, 10) > 0) { //获取整型部分转换  
　　var zeroCount = 0;  
　　var IntLen = IntegerNum.length;  
　　for (var i = 0; i < IntLen; i++) {  
　　var n = IntegerNum.substr(i, 1);  
　　var p = IntLen - i - 1;  
　　var q = p / 4;  
　　var m = p % 4;  
　　if (n == \"0\") {  
　　zeroCount++;  
　　} else {  
　　if (zeroCount > 0) {  
　　ChineseStr += cnNums[0];  
　　}  
　　zeroCount = 0; //归零  
　　ChineseStr += cnNums[parseInt(n)] + cnIntRadice[m];  
　　}  
　　if (m == 0 && zeroCount < 4) {  
　　ChineseStr += cnIntUnits[q];  
　　}  
　　}  
　　ChineseStr += cnIntLast;  
　　//整型部分处理完毕  
　　}  
　　if (DecimalNum != '') { //小数部分  
　　var decLen = DecimalNum.length;  
　　for (var i = 0; i < decLen; i++) {  
　　var n = DecimalNum.substr(i, 1);  
　　if (n != '0') {  
　　ChineseStr += cnNums[Number(n)] + cnDecUnits[i];  
　　}  
　　}  
　　}  
　　if (ChineseStr == '') {  
　　ChineseStr += cnNums[0] + cnIntLast + cnInteger;  
　　} else if (DecimalNum == '') {  
　　ChineseStr += cnInteger;  
　　}  
　　return ChineseStr;  
}

$('.changeSumNumber').trigger('change');
";
Yii::app()->clientScript->registerScript('changeAmt',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


