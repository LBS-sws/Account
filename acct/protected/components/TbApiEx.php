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
    }

    public function registerAllCss()
    {
		if (empty($this->assetPath))
			$this->assetPath = Yii::app()->assetManager->publish($this->adminLtePath, false, -1, $this->forceCopyAssets);

        $this->registerCoreCss($this->assetPath.'/bootstrap/css/bootstrap.min.css');
        $this->registerYiistrapCss();
        $this->fixPanningAndZooming();
    }

    public function registerAllScripts()
    {
		if (empty($this->assetPath))
			$this->assetPath = Yii::app()->assetManager->publish($this->adminLtePath, false, -1, $this->forceCopyAssets);
		
		$cs = Yii::app()->clientScript;
		$cs->scriptMap = array(
				'font-awesome.min.css'=>'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css',
				'ionicons.min.css'=>'https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css',
				'AdminLTE.min.css'=>$this->assetPath.'/dist/css/AdminLTE.min.css',
				'_all-skins.min.css'=>$this->assetPath.'/dist/css/skins/_all-skins.min.css',
				'jquery.dataTables.min.js'=>$this->assetPath.'/plugins/datatables/jquery.dataTables.min.js',
				'dataTables.bootstrap.min.js'=>$this->assetPath.'/plugins/datatables/dataTables.bootstrap.min.js',
				'jquery.slimscroll.min.js'=>$this->assetPath.'/plugins/slimScroll/jquery.slimscroll.min.js',
				'fastclick.js'=>$this->assetPath.'/plugins/fastclick/fastclick.js',
				'app.min.js'=>$this->assetPath.'/dist/js/app.min.js',
				'demo.js'=>$this->assetPath.'/dist/js/demo.js',
				'jquery.js'=>$this->assetPath.'/plugins/jQuery/jquery-2.2.3.min.js',
				'select2.full.min.js'=>$this->assetPath.'/plugins/select2/select2.full.min.js',
				'jquery.inputmask.js'=>$this->assetPath.'/plugins/input-mask/jquery.inputmask.js',
				'jquery.inputmask.date.extensions.js'=>$this->assetPath.'/plugins/input-mask/jquery.inputmask.date.extensions.js',
				'jquery.inputmask.extensions.js'=>$this->assetPath.'/plugins/input-mask/jquery.inputmask.extensions.js',
				'moment.min.js'=>'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.11.2/moment.min.js',
				'daterangepicker.js'=>$this->assetPath.'/plugins/daterangepicker/daterangepicker.js',
				'bootstrap-datepicker.js'=>$this->assetPath.'/plugins/datepicker/bootstrap-datepicker.js',
				'bootstrap-colorpicker.min.js'=>$this->assetPath.'/plugins/colorpicker/bootstrap-colorpicker.min.js',
				'bootstrap-timepicker.min.js'=>$this->assetPath.'/plugins/timepicker/bootstrap-timepicker.min.js',
				'icheck.min.js'=>$this->assetPath.'/plugins/iCheck/icheck.min.js',
			);

		$cs->registerCssFile('font-awesome.min.css');
		$cs->registerCssFile('ionicons.min.css');
		$cs->registerCssFile('AdminLTE.min.css');
		$cs->registerCssFile('_all-skins.min.css');
		$cs->registerScriptFile('jquery.dataTables.min.js',CClientScript::POS_END);
		$cs->registerScriptFile('dataTables.bootstrap.min.js',CClientScript::POS_END);
		$cs->registerScriptFile('jquery.slimscroll.min.js',CClientScript::POS_END);
		$cs->registerScriptFile('fastclick.js',CClientScript::POS_END);
		$cs->registerScriptFile('app.min.js',CClientScript::POS_END);
		$cs->registerScriptFile('select2.full.min.js',CClientScript::POS_END);
		$cs->registerScriptFile('jquery.inputmask.js',CClientScript::POS_END);
		$cs->registerScriptFile('jquery.inputmask.date.extensions.js',CClientScript::POS_END);
		$cs->registerScriptFile('jquery.inputmask.extensions.js',CClientScript::POS_END);
		$cs->registerScriptFile('moment.min.js',CClientScript::POS_END);
		$cs->registerScriptFile('daterangepicker.js',CClientScript::POS_END);
		$cs->registerScriptFile('bootstrap-datepicker.js',CClientScript::POS_END);
		$cs->registerScriptFile('bootstrap-colorpicker.min.js',CClientScript::POS_END);
		$cs->registerScriptFile('icheck.min.js',CClientScript::POS_END);

        $this->registerCoreScripts($this->assetPath.'/bootstrap/js/bootstrap.min.js');
        $this->registerTooltipAndPopover();
	}
}
?>
