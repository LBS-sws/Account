<?php 
class WorkflowDMS extends Workflow {
	public function seekApprover($level=0) {
		$rtn = '';
		$city = $this->getRequestData('CITY');
		if (!empty($city)) {
			if ($level > 0) {
				$cityList = City::model()->getAncestorList($city);
				$itemno = ($level > count($cityList) ? count($cityList) : $level)-1;
				$region = $cityList[$itemno];
				$row = City::model()->findByPk($region);
				$rtn = ($row!==null) ? $row['incharge'] : '';
			} else {
				$row = City::model()->findByPk($city);
				$rtn = ($row!==null) ? $row['incharge'] : '';
			}
		}
		return $rtn;
	}
	
	public function seekManager() {
		return $this->seekApprover(0);
	}
	
	public function seekDirector() {
		return $this->seekApprover(1);
	}

	public function seekBoss($topRegion='') {
		$rtn = '';
		if ($topRegion!='') {
			$row = City::model()->findByPk($topRegion);
			$rtn = ($row!==null) ? $row['incharge'] : '';
		} else {
			$cityList = City::model()->getAncestorList($city);
			$rtn = end($cityList);
		}
		return $rtn;
	}
}
?>