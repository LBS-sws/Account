<?php
class MenuWidget extends CWidget
{
	public $config;
	
	protected function printMenuHtml($items) {
		$rtn = '';
		foreach ($items as $name=>$item) {
			if (!Yii::app()->user->isGuest && Yii::app()->user->validFunction($item['access'])) {
				$url = isset($item['url']) ? Yii::app()->createUrl($item['url']) : '#';
				$itemname = Yii::t('app',$name);
				if (isset($item['items'])) {
					$rtn .= "<li class=\"dropdown\">
								<a href=\"#\" class=\"dropdown-toggle\" data-toggle=\"dropdown\">$itemname <span class=\"caret\"></span></a>
								<ul class=\"dropdown-menu\" role=\"menu\">
						";
					$rtn .= $this->printMenuHtml($item['items']);
					$rtn .= "</ul></li>";
				} else {
					$rtn .= "<li><a href=\"$url\">$itemname</a></li>";
				}
			}
		}
		return $rtn;
	}
	
	public function run()
	{
		$menuitems = require($this->config);
		
		$layout = '<ul class="nav navbar-nav">';
		$layout .= $this->printMenuHtml($menuitems);
		$layout .= '</ul>';
		
		echo $layout;
	}
}
