<?php
class UserMenuWidget extends CWidget
{
	public function run()
	{
		$image = Yii::app()->baseUrl."/images/user-icon.png";
		$display_name = Yii::app()->user->user_display_name();
		$login_time = Yii::t('app','Last Logon Time').': '.Yii::app()->user->logon_time();
		
		$lang_lbl = Yii::t('app','Languages');
		$lang_url = Yii::app()->createUrl('/site/language');
		
		$passwd_lbl = Yii::t('app','Change Password');
		$passwd_url = Yii::app()->createUrl('/site/password');
		
		$logout_lbl = Yii::t('misc','Logout');
		$logout_url = Yii::app()->createUrl('/site/logout');
		
		$layout = "
			<!-- User Account Menu -->
				<li class=\"dropdown user user-menu\">
				<!-- Menu Toggle Button -->
				<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">
					<!-- The user image in the navbar-->
					<img src=\"$image\" class=\"user-image\" alt=\"User Image\">
					<!-- hidden-xs hides the username on small devices so only the image appears. -->
					<span class=\"hidden-xs\">$display_name</span>
				</a>
				<ul class=\"dropdown-menu\">
					<!-- The user image in the menu -->
					<li class=\"user-header\">
						<img src=\"$image\" class=\"img-circle\" alt=\"User Image\">
						<p>
							$display_name
							<small>$login_time</small>
						</p>
					</li>
					<!-- Menu Body -->
					<li class=\"user-body\">
						<div class=\"row\">
							<div class=\"col-xs-6 text-center\">
								<a href=\"$lang_url\">$lang_lbl</a>
							</div>
							<div class=\"col-xs-6 text-center\">
								<a href=\"$passwd_url\">$passwd_lbl</a>
							</div>
						</div>
						<!-- /.row -->
					</li>
					<!-- Menu Footer-->
					<li class=\"user-footer\">
						<div class=\"pull-right\">
							<a href=\"$logout_url\" class=\"btn bg-blue btn-block\">$logout_lbl</a>
						</div>
					</li>
				</ul>
			</li>
";
   		
		echo $layout;
	}
}
