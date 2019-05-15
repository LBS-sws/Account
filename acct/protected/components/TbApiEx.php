<?php
class TbApiEx extends TbApi {
	public $adminLtePath;
	
	protected $assetPath;
	
    public function register()
    {
		$this->assetPath = Yii::app()->assetManager->publish($this->adminLtePath, false, -1, $this->forceCopyAssets);
		parent::register();
    }

    public function fixPanningAndZooming()
    {
        Yii::app()->getClientScript()->registerMetaTag('width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no', 'viewport');
//        Yii::app()->getClientScript()->registerMetaTag('width=device-width, initial-scale=1', 'viewport');
    }

    public function registerAllCss()
    {
		if (empty($this->assetPath))
			$this->assetPath = Yii::app()->assetManager->publish($this->adminLtePath, false, -1, $this->forceCopyAssets);

        $this->fixPanningAndZooming();

        $this->registerCoreCss($this->assetPath.'/bower_components/bootstrap/dist/css/bootstrap.min.css');
        $this->registerYiistrapCss();

		$cs = Yii::app()->clientScript;
		$cs->registerCssFile($this->assetPath.'/bower_components/font-awesome/css/font-awesome.min.css');
		$cs->registerCssFile($this->assetPath.'/bower_components/Ionicons/css/ionicons.min.css');
		$cs->registerCssFile($this->assetPath.'/dist/css/AdminLTE.min.css');
		$cs->registerCssFile($this->assetPath.'/dist/css/skins/_all-skins.min.css');
		$cs->registerCssFile($this->assetPath.'/plugins/iCheck/all.css');
//		$cs->registerCssFile($this->assetPath.'/bower_components/morris.js/morris.css');
//		$cs->registerCssFile($this->assetPath.'bower_components/jvectormap/jquery-jvectormap.css');
		$cs->registerCssFile($this->assetPath.'/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css');
		$cs->registerCssFile($this->assetPath.'/bower_components/bootstrap-daterangepicker/daterangepicker.css');
		$cs->registerCssFile($this->assetPath.'/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css');
		$cs->registerCssFile($this->assetPath.'/bower_components/select2/dist/css/select2.min.css');
		$cs->registerCssFile($this->assetPath.'/bower_components/bootstrap-colorpicker/dist/css/bootstrap-colorpicker.min.css');
		$cs->registerCssFile($this->assetPath.'/plugins/timepicker/bootstrap-timepicker.min.css');
//		$cs->registerCssFile('https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700,300italic,400italic,600italic');
    }

    public function registerAllScripts()
    {
		if (empty($this->assetPath))
			$this->assetPath = Yii::app()->assetManager->publish($this->adminLtePath, false, -1, $this->forceCopyAssets);
		
		$cs = Yii::app()->clientScript;
		$langcode = $this->getSelect2Lang();
/*
		$cs->scriptMap = array(
				'font-awesome.css'=>$this->assetPath.'/plugins/font-awesome/css/font-awesome.min.css',
				'ionicons.css'=>'https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css',
				'theme-style.css'=>$this->assetPath.'/dist/css/adminlte.min.css',
				'icheck.css'=>$this->assetPath.'/plugins/iCheck/all.css',
//				'morris-chart.css'=>$this->assetPath.'/plugins/morris/morris.css',
//				'jvectormap.css'=>$this->assetPath.'/plugins/jvectormap/jquery-jvectormap-1.2.2.css',
				'datepicker.css'=>$this->assetPath.'/plugins/datepicker/datepicker3.css',
				'daterangepicker.css'=>$this->assetPath.'/plugins/daterangepicker/daterangepicker-bs3.css',
				'wysihtml5.css'=>$this->assetPath.'/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css',
				'select2.min.css'=>$this->assetPath.'/plugins/select2/select2.min.css',
				'bootstrap.colorpicker.css'=>$this->assetPath.'/plugins/colorpicker/bootstrap-colorpicker.min.css',
				'bootstrap.timepicker.css'=>$this->assetPath.'/plugins/timepicker/bootstrap-timepicker.min.css',
//				'source-sans-pro.css'=>'https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700',

				'jquery.js'=>$this->assetPath.'/plugins/jquery/jquery.min.js',
				'bootstrap.js'=>$this->assetPath.'/plugins/bootstrap/js/bootstrap.bundle.min.js',
//				'morris.js.1'=>'https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js',
//				'morris.js.2'=>$this->assetPath.'/plugins/morris/morris.min.js',
				'sparkline.js'=>$this->assetPath.'/plugins/sparkline/jquery.sparkline.min.js',
//				'jvectormap.js.1'=>$this->assetPath.'/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js',
//				'jvectormap.js.2'=>$this->assetPath.'plugins/jvectormap/jquery-jvectormap-world-mill-en.js',
				'jquery.knob.js'=>$this->assetPath.'/plugins/knob/jquery.knob.js',
				'daterangepicker.js.1'=>'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.2/moment.min.js',
				'daterangepicker.js.2'=>$this->assetPath.'/plugins/daterangepicker/daterangepicker.js',
				'datepicker.js'=>$this->assetPath.'/plugins/datepicker/bootstrap-datepicker.js',
				'wysihtml5.js'=>$this->assetPath.'/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',
				'slimscroll.js'=>$this->assetPath.'/plugins/slimScroll/jquery.slimscroll.min.js',
				'fastclick.js'=>$this->assetPath.'/plugins/fastclick/fastclick.js',
				'select2.lang.js'=>$this->assetPath.'/plugins/select2/i18n/'.$langcode.'.js',
				'select2.full.min.js'=>$this->assetPath.'/plugins/select2/select2.full.min.js',
				'jquery.inputmask.js'=>$this->assetPath.'/plugins/input-mask/jquery.inputmask.js',
				'jquery.inputmask.date.extensions.js'=>$this->assetPath.'/plugins/input-mask/jquery.inputmask.date.extensions.js',
				'jquery.inputmask.extensions.js'=>$this->assetPath.'/plugins/input-mask/jquery.inputmask.extensions.js',
				'icheck.min.js'=>$this->assetPath.'/plugins/iCheck/icheck.min.js',
				'adminlte.js'=>$this->assetPath.'/dist/js/adminlte.min.js',
				'jquery.dataTables.min.js'=>$this->assetPath.'/plugins/datatables/jquery.dataTables.min.js',
				'dataTables.bootstrap.min.js'=>$this->assetPath.'/plugins/datatables/dataTables.bootstrap.min.js',
				'bootstrap-colorpicker.min.js'=>$this->assetPath.'/plugins/colorpicker/bootstrap-colorpicker.min.js',
				'bootstrap-timepicker.min.js'=>$this->assetPath.'/plugins/timepicker/bootstrap-timepicker.min.js',
			);
*/

//		$cs->registerScriptFile($this->assetPath.'/bower_components/jquery/dist/jquery.min.js',CClientScript::POS_END);
        $this->registerCoreScripts($this->assetPath.'/bower_components/bootstrap/dist/js/bootstrap.min.js');
//		$cs->registerScriptFile($this->assetPath.'/bower_components/jquery-ui/jquery-ui.min.js',CClientScript::POS_END);
//		$cs->registerScriptFile($this->assetPath.'/bower_components/raphael/raphael.min.js',CClientScript::POS_END);
//		$cs->registerScriptFile($this->assetPath.'/bower_components/morris.js/morris.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/jquery-sparkline/dist/jquery.sparkline.min.js',CClientScript::POS_END);
//		$cs->registerScriptFile($this->assetPath.'/plugins/jvectormap/jquery-jvectormap-1.2.2.min.js',CClientScript::POS_END);
//		$cs->registerScriptFile($this->assetPath.'/plugins/jvectormap/jquery-jvectormap-world-mill-en.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/jquery-knob/dist/jquery.knob.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/moment/min/moment.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/bootstrap-daterangepicker/daterangepicker.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/jquery-slimscroll/jquery.slimscroll.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/fastclick/lib/fastclick.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/select2/dist/js/select2.full.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/select2/dist/js/i18n/'.$langcode.'.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/plugins/input-mask/jquery.inputmask.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/plugins/input-mask/jquery.inputmask.date.extensions.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/plugins/input-mask/jquery.inputmask.extensions.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/plugins/iCheck/icheck.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/datatables.net/js/jquery.dataTables.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/plugins/timepicker/bootstrap-timepicker.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/bower_components/bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js',CClientScript::POS_END);
		$cs->registerScriptFile($this->assetPath.'/dist/js/adminlte.min.js',CClientScript::POS_END);
        $this->registerTooltipAndPopover();
	}
	
	protected function getSelect2Lang() {
		switch (Yii::app()->language) {
			case 'zh_tw' : $rtn = 'zh-TW'; break;
			case 'zh_cn' : $rtn = 'zh-CN'; break;
			default : $rtn = Yii::app()->language;
		}
		return $rtn;
	}
}
?>
