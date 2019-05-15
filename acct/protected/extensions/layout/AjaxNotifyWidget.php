<?php
class AjaxNotifyWidget extends CWidget
{
	public function run()
	{
		$msg1 = Yii::t('queue','You have');
		$msg2 = Yii::t('queue','new message(s)');
		$msg3 = Yii::t('queue','new request(s) for action');
		$msg4 = Yii::t('queue','new notification(s)');
		$notifyurl = Yii::app()->createUrl("ajax/notify");
		$actionurl = Yii::app()->createUrl("notice/index",array('type'=>'ACTN'));
		$messageurl = Yii::app()->createUrl("notice/index",array('type'=>'NOTI'));
		$js = <<<EOF
$.ajax({
	type: 'GET', 
	url: '$notifyurl',
	dataType: 'json', 
	success: function(data) {
		if (data.length > 0) {
			var total = 0;
			data.forEach(function (arrayItem) {
				total += parseInt(arrayItem.count);
				switch (arrayItem.type) {
					case 'NOTI':
						$('#mm_note_msg').html('<a href="$messageurl"><i class="fa fa-info-circle text-blue"></i> '
							+arrayItem.count+' $msg4 </a>');
						break;
					case 'ACTN':
						$('#mm_note_act').html('<a href="$actionurl"><i class="fa fa-rocket text-red"></i> '
							+arrayItem.count+' $msg3 </a>');
						break;
				}
			});
			$('#mm_note_hdr').text('$msg1 '+total+' $msg2');
			$('#mm_note_num').text(total);
		}
	},
	error: function(xhr, status, error) {
		skip=1;
	}
});
EOF;
		Yii::app()->clientScript->registerScript('checknotify',$js,CClientScript::POS_READY);
	}
}
