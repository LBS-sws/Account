<?php
class SessionExpiryWidget extends CWidget
{
	public function run()
	{
// Check Session Expiration
	$checkurl = Yii::app()->createUrl("ajax/checksession");
	$loginurl = Yii::app()->createUrl("site/logout");
	$js = <<<EOF
var checkLogin = function() {
    $.ajax({
		type: 'GET', 
		url: '$checkurl',
		dataType: 'json', 
		success: function(json) {
			var x = json;
			var data = json;
			if (!data.loggedin) {
				clearInterval(logincheckinterval);
				window.location = '$loginurl';
			}
		},
		error: function(xhr, status, error) {
			skip=1;
		}
	});
};
var logincheckinterval = setInterval(checkLogin, 30000);
EOF;
	Yii::app()->clientScript->registerScript('checksession',$js,CClientScript::POS_READY);
// End - Check Session Expiration
	}
}
