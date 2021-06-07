<?php
class AnnounceWidget extends CWidget
{
	public function run() {
		$noToShow = 10;
		$content = '';
		if (!$this->hasRead()) {
			$noOfItem = $this->noOfItem();
			if ($noOfItem > 0) {
				$content .= $this->renderHeader();
				$content .= $this->renderBody($noToShow, $noOfItem);
				$content .= $this->renderFooter();
			
				$level = Yii::app()->user->ranklevel();
				if (Yii::app()->params['showRank']!='on' || empty($level))
					$this->renderScript();
				$this->setRead();
			}
		}
		echo $content;
	}

	protected function renderHeader() {
		$title = Yii::t('misc','Announcement');
		$out = <<<EOF
<div class="modal fade" id="modal-default">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">$title</h4>
			</div>
			<div class="modal-body">
				<div id="carousel-generic" class="carousel slide" data-ride="carousel">
					<div class="carousel-inner">
		
EOF;
		return $out;
	}
	
	protected function renderFooter() {
		$out = <<<EOF
					</div>
					<a class="left carousel-control" href="#carousel-generic" data-slide="prev">
						<span class="fa fa-angle-left"></span>
					</a>
					<a class="right carousel-control" href="#carousel-generic" data-slide="next">
						<span class="fa fa-angle-right"></span>
					</a>
				</div>
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

	protected function renderBody($show, $contain) {
		$out = '';
		
		$rows = ($show >= $contain) ? $this->getItems() : $this->getRandomItems($show);
		$active = true;
		foreach ($rows as $row) {
			$out .= $active ? '<div class="item active">' : '<div class="item">';
			if (!empty($row['image'])) {
				$type = ($row['image_type']=='jpg') ? 'jpeg' : $row['image_type'];
				$img = "data:image/".$type.";base64,".$row['image'];
				$out .= CHtml::image($img,'image',array('width'=>900,'height'=>500));
				if (!empty($row['image_caption'])) {
					$out .= '<div class="carousel-caption">';
					$out .= '<h3>'.$row['image_caption'].'</h3>';
					$out .= '</div>';
				}
				if (!empty($row['content'])) {
					$out .= '<p>'.$row['content'].'</p>';
				}
			} else {
				$out .= '<table height=500><tr><td width=100>&nbsp;</td><td><h4><center>'.$row['content'].'</center></h4></td><td width=100>&nbsp;</td></tr></table>';
			}
			$out .= '</div>';
			$active = false;
		}
		return $out;
	}

	protected function renderScript() {
		$js = <<<EOF
$('#modal-default').modal('show');
EOF;
		Yii::app()->clientScript->registerScript('announcement',$js,CClientScript::POS_READY);
	}

	protected function hasRead() {
		$session = Yii::app()->session;
		return (isset($session['announcement']) && !empty($session['announcement'])) ?  $session['announcement'] : false;
	}
	
	protected function setRead() {
		$session = Yii::app()->session;
		$session['announcement'] = true;
	}

	protected function hasItem() {
        $suffix = Yii::app()->params['envSuffix'];
		$sql = "select count(id) from announcement$suffix.ann_announce where start_dt<=now() and date_add(end_dt, interval 1 day)>=now()";
		$rtn = Yii::app()->db->createCommand($sql)->queryScalar();
		return ($rtn > 0);
	}

	protected function noOfItem() {
        $suffix = Yii::app()->params['envSuffix'];
		$sql = "select count(id) from announcement$suffix.ann_announce where start_dt<=now() and date_add(end_dt, interval 1 day)>=now()";
		$rtn = Yii::app()->db->createCommand($sql)->queryScalar();
		return $rtn;
	}

	protected function getItems() {
        $suffix = Yii::app()->params['envSuffix'];
		$sql = "select * from announcement$suffix.ann_announce where start_dt<=now() and date_add(end_dt, interval 1 day)>=now() order by priority desc";
		$rtn = Yii::app()->db->createCommand($sql)->queryAll();
		return $rtn;
	}

	protected function getRandomItems($num) {
		if (empty($num)) $num = 10;
        $suffix = Yii::app()->params['envSuffix'];
		$sql = "select a.* from announcement$suffix.ann_announce as a join 
				(select id from announcement$suffix.ann_announce where start_dt<=now() and date_add(end_dt, interval 1 day)>=now() order by rand() limit $num) as b
				on a.id=b.id
			";
		$rtn = Yii::app()->db->createCommand($sql)->queryAll();
		return $rtn;
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
