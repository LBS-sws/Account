<?php
class NotifyBadgeWidget extends CWidget
{
	public $config;
	public $url;
	
	public function run() {
		$notifyitems = require($this->config);
		$param = json_encode($notifyitems);
		$url = $this->url;

		$js = <<<EOF
var notifyitems = '$param';
$.ajax({
	type: 'GET', 
	url: '$url',
	data: {param:notifyitems},
	dataType: 'json', 
	success: function(data) {
		if (data.length > 0) {
			data.forEach(function (arrayItem) {
				if (arrayItem.count > 0) {
					var prefix='';
					for (var i=0; i < arrayItem.code.length; i++) {
						if (Number(parseFloat(arrayItem.code[i]))==arrayItem.code[i]) break;
						prefix += arrayItem.code[i];
					}
					var badge1 = '<span class="label '+arrayItem.color+'">'+arrayItem.count+'</span>';
					$('#counter'+prefix).append(badge1);
					var badge2 = '<small class="label '+arrayItem.color+'">'+arrayItem.count+'</small>';
					$('#counter'+arrayItem.code).append(badge2);
				}
			});
		}
	},
	error: function(xhr, status, error) {
		skip=1;
	}
});
EOF;
		Yii::app()->clientScript->registerScript('systemnotification',$js,CClientScript::POS_READY);
	}
}
