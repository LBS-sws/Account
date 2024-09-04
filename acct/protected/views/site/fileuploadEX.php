<?php
$ftrbtn = array();
//if (!$ronly) {$ftrbtn[] = TbHtml::button(Yii::t('dialog','Upload'), array('id'=>$doc->uploadButtonName,));}
$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('id'=>"btnUploadCloseEx",'data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));

$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>"fileuploadEx",
    'header'=>$header,
    'footer'=>$ftrbtn,
    'show'=>false,
));
?>
<div class="box" id="file-list" style="max-height: 300px; overflow-y: auto;">
    <table id="fileuploadEx" class="table table-hover">
        <thead>
        <tr><th></th><th><?php echo Yii::t('dialog','File Name');?></th><th><?php echo Yii::t('dialog','Date');?></th></tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
<?php echo $form->hiddenField($model, 'docType',array("id"=>"docType")); ?>
<?php echo $form->hiddenField($model, 'docId',array("id"=>"docId")); ?>
<?php echo $form->hiddenField($model, 'docMasterId',array("id"=>"docMasterId")); ?>
<?php echo $form->hiddenField($model, 'removeFileId',array("id"=>"removeFileId")); ?>
<?php echo TbHtml::hiddenField("click_index","-1",array("id"=>"click_index"))?>
<div id="no_of_div" class="hide">
    <?php
    if(!empty($model->no_of_attm)){
        foreach ($model->no_of_attm as $key=>$num){
            if($key=="expen"){
                $className = "EXPEN".(empty($model->id)?0:$model->id);
            }else{
                $className = $key;
            }
            echo $form->hiddenField($model, "no_of_attm[{$key}]",array('class'=>$className));
        }
    }
    ?>
</div>
<div id="new_of_div" class="hide">
    <?php
    if(!empty($model->new_of_id)){
        foreach ($model->new_of_id as $key=>$num){
            $className = $num;
            echo $form->hiddenField($model, "new_of_id[{$key}]",array('class'=>$className,'data-id'=>$key));
        }
    }
    ?>
</div>
<?php
if (!$ronly) {
	echo TbHtml::fileField('attachmentEx[]', '', array('id'=>'attachmentEx','multiple'=>true));
    echo "<span id='msgblockEx' class='text-red'></span>";
}
?>

<?php
$this->endWidget();
?>

<?php
$ctrlname = Yii::app()->controller->id;
$formId = $form->id;
$modelname = get_class($model);

if (!$ronly) {
	$link = Yii::app()->createAbsoluteUrl($ctrlname."/fileupload");

	$warning1 = Yii::t('dialog','Exceeds file upload limit.');
	$warning2 = Yii::t('dialog','Invalid file type.');
	$message1 = Yii::t('dialog','Saving').'...';

	$maxSize = 10485760;

	$jscript = <<<EOF
$('#attachmentEx').on('change',function() {
	var errmsg = '';
	var fileInput = document.getElementById('attachmentEx');
	var closeButton = document.getElementById('btnUploadCloseEx');
	var msgBlock = document.getElementById('msgblockEx');
	var selectedFiles = [...fileInput.files];
	for (var i=0; i<selectedFiles.length; i++) {
		if (selectedFiles[i].size > $maxSize) errmsg += '$warning1 ($maxSize Bytes) -' + selectedFiles[i].name + "\\n";
		let elm = selectedFiles[i].name.split('.');
		if ('jpeg|jpg|gif|png|xlsx|xls|docx|doc|pdf|tif|'.indexOf(elm[elm.length - 1].toLowerCase()+'|')==-1)
			errmsg += '$warning2 -' + selectedFiles[i].name + "\\n";
	};
	if (errmsg!='') {
		alert(errmsg);
	} else {
		var form = document.getElementById('$formId');
		var formdata = new FormData(form);
		fileInput.disabled = true;
		closeButton.disabled = true;
		msgBlock.innerHTML = '<center><i>$message1</i></center>';
		$.ajax({
			type: 'POST',
			url: '$link',
		data: formdata,
		mimeType: 'multipart/form-data',
		contentType: false,
		processData: false,
		dataType: 'json',
		success: function(data) {
		    saveFileAjaxData(data);
			msgBlock.innerHTML = '';
			fileInput.disabled = false;
			fileInput.value = null;
			closeButton.disabled = false;
		},
		error: function(data) { // if error occured
			msgBlock.innerHTML = '';
			fileInput.disabled = false;
			fileInput.value = null;
			closeButton.disabled = false;
			alert('Error occured.please try again');
		}
	});
		
	}
});
EOF;
	Yii::app()->clientScript->registerScript('fileUploadEx2',$jscript,CClientScript::POS_READY);
}
?>