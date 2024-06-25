<?php
$this->pageTitle=Yii::app()->name . ' - RemitApply Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'RemitApply-form',
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
		<strong><?php echo Yii::t('app','Remit Apply'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('remitApply/index')));
		?>
<?php if (!$model->readonly()): ?>
			<?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('remitApply/save')));
			?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('give','Send'), array(
				'submit'=>Yii::app()->createUrl('remitApply/audit')));
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'&&!$model->readonly()): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>

            <div class="btn-group pull-right" role="group">
                <?php if (in_array($model->status_type,array(4,6,9))): ?>
                    <?php echo TbHtml::link('<span class="fa fa-print"></span> '.Yii::t('invoice','print'),Yii::app()->createUrl('remitApply/print',array("index"=>$model->id)), array(
                            'class'=>'btn btn-default','target'=>'_blank')
                    );
                    ?>
                <?php endif ?>
                <?php if ($model->scenario!='new'): ?>
                    <?php echo TbHtml::button('<span class="fa fa-list"></span> '.Yii::t('give','Flow Info'), array(
                            'data-toggle'=>'modal','data-target'=>'#flowinfodialog',)
                    );
                    ?>
                <?php endif ?>
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
			<?php echo $form->hiddenField($model, 'city',array("id"=>"city")); ?>
            <?php echo CHtml::hiddenField('dtltemplate'); ?>

            <?php $this->renderPartial('//remitApply/remitForm',array("model"=>$model,"form"=>$form)); ?>

            <?php $this->renderPartial('//remitApply/remitAudit',array("model"=>$model,"form"=>$form)); ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>
<?php $this->renderPartial('//remitApply/remitHistory',array("model"=>$model)); ?>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'EXPEN',
    'header'=>Yii::t('dialog','File Attachment'),
    'ronly'=>$model->readonly(),
));?>

<?php
Script::genFileUpload($model,$form->id,'EXPEN');
$language = Yii::app()->language;

$link3=Yii::app()->createUrl('remitApply/AjaxPayee');
$js ="
function changeCustomerName(){
    var that = $(this);
    $(this).parent('div').addClass('open');
    $(this).next('.dropdown-menu').html('<li><a>查询中...</span></li>');
	var data = \"group=\"+$(this).val()+\"&city=\"+$('#city').val();
	$.ajax({
		type: 'GET',
		url: '$link3',
		data: data,
		success: function(data) {
			that.next('.dropdown-menu').html(data);
		},
		error: function(data) { // if error occured
			var x = 1;
		},
		dataType:'html'
	});
}
$('#payee').on('click',function(e){
    e.stopPropagation();
});
$('#payee').on('focus',changeCustomerName);
$('#payee').on('keyup',changeCustomerName);
$('body').on('click',function(){
    $('#payee').parent('div').removeClass('open');
});
$('#payeeGroup').on('click','.clickThis',function(e){
    $('#payee').val($(this).data('name'));
    $('#taxpayer_no').val($(this).data('no'));
    $('#bank_name').val($(this).data('bank'));
    $('#bank_no').val($(this).data('acct'));
});
";
Yii::app()->clientScript->registerScript('ajaxFunction',$js,CClientScript::POS_READY);
$js = "
$('#tblDetail').on('change keyup','.changeSumNumber',function() {
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
$js = "
$('table').on('change','[id^=\"RemitApplyForm\"]',function() {
	var n=$(this).attr('id').split('_');
	$('#RemitApplyForm_'+n[1]+'_'+n[2]+'_uflag').val('Y');
});
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);

if ($model->scenario!='view') {
    $js = <<<EOF
$('table').on('click','#btnDelRow', function() {
	$(this).closest('tr').hide().removeClass('changeTr');
	$(this).closest('tr').find('.changeNumber').trigger('change');
	$(this).closest('tr').find('[id*=\"_uflag\"]').val('D');
});
EOF;
    Yii::app()->clientScript->registerScript('removeRow',$js,CClientScript::POS_READY);

    $js = <<<EOF
$(document).ready(function(){
	var ct = $('#tblDetail>tbody>tr').eq(-1).html();
	$('#dtltemplate').attr('value',ct);
});

$('#btnAddRow').on('click',function() {
	var r = $('#tblDetail tr').length;
	if (r>0) {
		var nid = '';
		var ct = $('#dtltemplate').val();
		$('#tblDetail>tbody:last').append('<tr class=\"changeTr\">'+ct+'</tr>');
		$('#tblDetail>tbody>tr').eq(-1).find('[id*=\"RemitApplyForm_\"]').each(function(index) {
			var id = $(this).attr('id');
			var name = $(this).attr('name');

			var oi = 0;
			var ni = r;
			id = id.replace('_'+oi.toString()+'_', '_'+ni.toString()+'_');
			$(this).attr('id',id);
			name = name.replace('['+oi.toString()+']', '['+ni.toString()+']');
			$(this).attr('name',name);
			if (id.indexOf('_infoDate') != -1){
                $(this).val('');
                $(this).datepicker({autoclose: true,language: '$language', format: 'yyyy/mm/dd'});
			}
			if (id.indexOf('_id') != -1) $(this).val(0);
			if (id.indexOf('_setId') != -1) $(this).val('');
			if (id.indexOf('_infoRemark') != -1) $(this).val('');
			if (id.indexOf('_amtType') != -1){
			    $(this).val('').trigger('change');
			}
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
        'RemitApplyForm_apply_date,.info_date,#end_pay_date',
    ));
    Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}
$js = Script::genDeleteData(Yii::app()->createUrl('remitApply/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


