<?php

class CListPageModel extends CFormModel
{
	public $attr = array();
	
	public $pageNum = 0;
	
	public $noOfItem = 25;
	
	public $totalRow = 0;
	
	public $searchField;
	
	public $searchValue;
	
	public $orderField;
	
	public $orderType;
	
	public $filter;

	public function rules()
	{
		return array(
			array('attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType, filter','safe',),
		);
	}
	
	public function retrieveDataByPage($pageNum=0)
	{
		return true;
	}
	
	public function sqlWithPageCriteria($sql, $pageNum) {
		$rtn = $sql;
		if ($pageNum <= 0) $pageNum = 1;
		$offset = ($this->noOfItem != 0) ? ($pageNum-1) * $this->noOfItem : 0;
		if ($this->noOfItem != 0) $rtn .= ' LIMIT '.$offset.', '.$this->noOfItem;
		return $rtn;
	}
	
	public function determinePageNum($pageNum)
	{
		if ($pageNum!=0)
			$this->pageNum = $pageNum;
		else
			if (empty($this->pageNum) || $this->pageNum==0) $this->pageNum = 1;
	}

	public function criteriaName() {
		return 'criteria_'.get_class($this);
	}
	
	public function setCriteria($criteria)
	{
		if (count($criteria) > 0) {
			foreach ($criteria as $k=>$v) {
				if (isset($this->$k)) $this->$k = $v;
			}
		}
	}
	
	public function getCriteria() {
		return array(
			'searchField'=>$this->searchField,
			'searchValue'=>$this->searchValue,
			'orderField'=>$this->orderField,
			'orderType'=>$this->orderType,
			'noOfItem'=>$this->noOfItem,
			'pageNum'=>$this->pageNum,
			'filter'=>$this->filter,
		);
	}
	
	public function searchColumns() {
		return array();
	}
	
	public function isAdvancedSearch() {
		return ($this->searchField=='ex_advanced');
	}
	
	protected function buildSQLCriteria() {
		$rtn = '';

		$session_name = 'criteria_'.get_class($this);
		$columns = $this->searchColumns();
		
		$elm = array();
		$session = Yii::app()->session;
		$filter = isset($session[$session_name]['filter']) ? json_decode($session[$session_name]['filter']) : array();
		if (!empty($filter)) {
			foreach ($filter as $idx=>$obj) {
				if (!isset($elm[$obj->field_id])) $elm[$obj->field_id] = array();
				if (!isset($elm[$obj->field_id][$obj->operator])) 
					$elm[$obj->field_id][$obj->operator] = array($obj->srchval);
				else
					array_push($elm[$obj->field_id][$obj->operator], $obj->srchval);
			}
			foreach ($elm as $fid=>$items) {
				$equal = '';
				$range = '';
				foreach ($items as $oper=>$values) {
					$cond = $this->conditionclause($columns[$fid],$oper,$values);
					if (strpos('=,<>,like',$oper)!==false) {
						$equal .= ($equal!='' ? ' or ' : '').$cond;
					} else {
						$range .= ($range!='' ? ' and ' : '').$cond;
					}
				}
				$stmt = '';
				if ($equal!='') $stmt .= "($equal)";
				if ($range!='') $stmt .= ($stmt==''?'':' and ')."($range)";
				$rtn .= ($rtn==''?'':' and ').($stmt==''?'':'(').$stmt.($stmt==''?'':')');
			}
		}
		
		return ($rtn==''?'':' and ').$rtn;
	}
	
	protected function conditionclause($field, $operator, $values) {
		$rtn = '';
		switch ($operator) {
			case '=' :
				$rtn = " ".$field." in ('".implode("','",$values)."') ";
				break;
			case '<>' : 
				$rtn = " ".$field." not in ('".implode("','",$values)."') ";
				break;
			case 'like' :
				foreach($values as $value) {
					$rtn .= ($rtn=='' ? "" : " or ").$field." like '%".$value."%'";
				}
				if ($rtn!='') $rtn = " (".$rtn.") ";
				break;
			case '>' : 
				foreach ($values as $value) {
					$rtn .= ($rtn=='' ? "" : " and ").$field." > '".$value."'";
				}
				if ($rtn!='') $rtn = " (".$rtn.") ";
				break;
			case '<' :
				foreach ($values as $value) {
					$rtn .= ($rtn=='' ? "" : " and ").$field." < '".$value."'";
				}
				if ($rtn!='') $rtn = " (".$rtn.") ";
				break;
			case '<=' : 
				foreach ($values as $value) {
					$rtn .= ($rtn=='' ? "" : " and ").$field." <= '".$value."'";
				}
				if ($rtn!='') $rtn = " (".$rtn.") ";
				break;
			case '>=':
				foreach ($values as $value) {
					$rtn .= ($rtn=='' ? "" : " and ").$field." >= '".$value."'";
				}
				if ($rtn!='') $rtn = " (".$rtn.") ";
				break;
		}
		return $rtn;
	}
}
