<?php
class NoticeWidget extends CWidget
{
	public $color;
	
	public function run()
	{
		$msg = Yii::t('queue','No New Notification');
		$view = Yii::t('queue','View All');
		$notice_url = Yii::app()->createUrl('/notice/index',array('type'=>'ALL'));
		
		$layout = <<<EOF
			<!-- Notifications: style can be found in dropdown.less -->
			<li class="dropdown notifications-menu">
				<a href="#" class="dropdown-toggle" data-toggle="dropdown">
					<i class="glyphicon glyphicon-bell"></i>
					<span id="mm_note_num" class="label label-warning"></span>
				</a>
				<ul class="dropdown-menu">
					<li id="mm_note_hdr" class="header">$msg</li>
					<li>
						<ul class="menu">
							<li id="mm_note_act"></li>
							<li id="mm_note_msg"></li>
						</ul>
					</li>
					<li class="footer">
						<a href="$notice_url">$view</a>
					</li>
				</ul>
			</li>
EOF;
		echo $layout;
	}
}
