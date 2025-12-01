<?php
$this->pageTitle=Yii::app()->name . ' - TemporaryPayment Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'TemporaryPayment-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    *[readonly]{ pointer-events: none;}
    .table-fixed{ table-layout: fixed;}
    .table-fixed>tbody>tr>th{ vertical-align: bottom;}
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Remit Payment'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('temporaryPayment/index')));
		?>
<?php if ($model->status_type==4): ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Approve'), array(
                'submit'=>Yii::app()->createUrl('temporaryPayment/audit')));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Deny'), array(
                    'data-toggle'=>'modal','data-target'=>'#denyDialog',)
            );
            ?>
<?php endif ?>
	</div>

            <div class="btn-group pull-right" role="group">
                <?php if ($model->status_type==4): ?>
                    <?php echo TbHtml::button('<span class="fa fa-random"></span> '.Yii::t('give','Shift City'), array(
                            'data-toggle'=>'modal','data-target'=>'#shiftDialog',)
                    );
                    ?>
                <?php endif ?>
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

            <?php $this->renderPartial('//temporaryApply/temporaryAcc',array("model"=>$model,"form"=>$form)); ?>

            <?php $this->renderPartial('//temporaryApply/temporaryForm',array("model"=>$model,"form"=>$form)); ?>

            <?php $this->renderPartial('//temporaryApply/temporaryAudit',array("model"=>$model,"form"=>$form)); ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//temporaryApply/temporaryHistory',array("model"=>$model)); ?>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'EXPEN',
    'header'=>Yii::t('dialog','File Attachment'),
    'ronly'=>$model->getReadyForAcc(),
    'delBtn'=>false
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
        TbHtml::button(Yii::t('dialog','OK'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'submit'=>Yii::app()->createUrl('temporaryPayment/reject'))),
        TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));

$content="<div class=\"form-group\">";
$content.=Tbhtml::label(Yii::t("give","Current City"),'',array('class'=>"col-lg-3 control-label"));
$content.="<div class=\"col-lg-6\">";
$content.= Tbhtml::textField("shift[city]",General::getCityName($model->city),
    array('readonly'=>true)
);
$content.="</div></div>";
$content.="<div class=\"form-group\">";
$content.=Tbhtml::label(Yii::t("give","Shift City"),'',array('class'=>"col-lg-3 control-label"));
$content.="<div class=\"col-lg-6\">";
$content.=$form->dropDownList($model, 'shift_city',General::getCityListWithNoDescendant(),
    array('readonly'=>false)
);
$content.="</div></div>";
$this->widget('bootstrap.widgets.TbModal', array(
    'id'=>'shiftDialog',
    'header'=>Yii::t('give','Shift City'),
    'content'=>$content,
    'footer'=>array(
        TbHtml::button(Yii::t('dialog','OK'), array('color'=>TbHtml::BUTTON_COLOR_PRIMARY,'submit'=>Yii::app()->createUrl('temporaryPayment/shift'))),
        TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
    ),
    'show'=>false,
));

?>

<?php
Script::genFileUpload($model,$form->id,'EXPEN');
$language = Yii::app()->language;

$js = "
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

$('#changeFootSumStr').trigger('change');
";
Yii::app()->clientScript->registerScript('changeAmt',$js,CClientScript::POS_READY);

$js = Script::genDatePicker(array(
    'TemporaryPaymentForm_payment_date',
));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


