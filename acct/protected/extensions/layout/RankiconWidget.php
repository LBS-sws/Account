<?php
class RankiconWidget extends CWidget
{
	public function run() {
		$content = '';
		$level = Yii::app()->user->ranklevel();
		
		if (!empty($level) && (!$this->hasRead() || !$this->hasReadInSalesSystem())) {
			$content .= $this->renderContent($level);
			
			$this->renderScript();
			$this->setRead();
		}
		echo $content;
	}

	protected function renderContent($level) {
		$title = Yii::t('misc','Your ranking is:');
		$image = CHtml::image(Yii::app()->baseUrl."/images/rank/$level.png",'image',array('width'=>140,'height'=>160));
		
		$out = <<<EOF
<div class="modal fade" id="modal-ranking">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h3 class="modal-title">$title</h3>
			</div>
			<div class="modal-body">
				<table width="100%"><tr>
					<td width="10%">&nbsp;</td>
					<td align="center">$image</td>
					<td align="center"><h2><strong>$level</strong></h2></td>
					<td width="10%">&nbsp;</td>
				</tr></table>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal -->
EOF;
		return $out;
	}
	
	protected function renderScript() {
		$js = <<<EOF
$('#modal-ranking').modal('show');
$('#modal-ranking').on("hidden.bs.modal", function() {
	$('#modal-default').modal('show');
});
EOF;
		Yii::app()->clientScript->registerScript('rankicon',$js,CClientScript::POS_READY);
	}

	protected function hasRead() {
		$session = Yii::app()->session;
		return (isset($session['rankicon']) && !empty($session['rankicon'])) ?  $session['rankicon'] : false;
	}
	
	protected function hasReadInSalesSystem() {
		if (Yii::app()->user->system()!='sal') return true;
		$session = Yii::app()->session;
		return (isset($session['rankiconsal']) && !empty($session['rankiconsal'])) ?  $session['rankiconsal'] : false;
	}
	
	protected function setRead() {
		$session = Yii::app()->session;
		$session['rankicon'] = true;
		if (Yii::app()->user->system()=='sal') $session['rankiconsal'] = true;
	}

	public function render($view,$data=null,$return=false) {
		$ctrl = $this->getController();
		if(($viewFile=$ctrl->getViewFile($view))!==false)
			return $this->renderFile($viewFile,$data,$return);
		else
			throw new CException(Yii::t('yii','{widget} cannot find the view "{view}".',
				array('{widget}'=>get_class($this), '{view}'=>$view)));
	}
}
