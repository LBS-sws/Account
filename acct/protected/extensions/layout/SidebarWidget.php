<?php
class SidebarWidget extends CWidget
{
	public $config;
	
	protected function printMenuHtml($items) {
		$rtn = '';
		$active = isset(Yii::app()->session['active_func']) ? Yii::app()->session['active_func'] : '';
		foreach ($items as $name=>$item) {
			if (!Yii::app()->user->isGuest && Yii::app()->user->validFunction($item['access'])) {
				$url = isset($item['url']) ? Yii::app()->createUrl($item['url']) : '#';
				$itemname = Yii::t('app',$name);
				$icon = isset($item['icon']) ? $item['icon'] : 'fa-folder';
				$spanid = 'counter'.$item['access'];
				if (isset($item['items'])) {
					$style = strpos($active, $item['access'])!==false ? 'treeview active' : 'treeview';
					$rtn .= <<<EOF
						<li class="$style">
							<a href="#">
								<i class="fa $icon"></i> <span id="$spanid">$itemname&nbsp;</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
EOF;
					$rtn .= $this->printMenuHtml($item['items']);
					$rtn .= <<<EOF
							</ul>
						</li>
EOF;
				} else {
					$activeclass = ($active==$item['access']) ? 'class="active"' : '';
					if (isset($item['url']) && $item['url']!='#') {
						$rtn .= <<<EOF
								<li $activeclass>
									<a href="$url"><i class="fa fa-circle-o"></i>$itemname
										<span id="$spanid" class="pull-right-container"></span>
									</a>
								</li>
EOF;
					}
				}
			}
		}
		return $rtn;
	}
	
	public function run()
	{
		$menuitems = require($this->config);
		$content = $this->printMenuHtml($menuitems);
		$label = Yii::t('app','MAIN NAVIGATION');
		
		$layout = <<<EOF
<!-- Left side column. contains the logo and sidebar -->
	<aside class="main-sidebar">
		<!-- sidebar: style can be found in sidebar.less -->
		<section class="sidebar">
			<!-- sidebar menu: : style can be found in sidebar.less -->
			<ul class="sidebar-menu" data-widget="tree">
				<li class="header">$label</li>
				$content
			</ul>
		</section>
		<!-- /.sidebar -->
	</aside>
EOF;
		
		echo $layout;
	}
}
