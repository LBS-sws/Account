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

/*
	protected function customLabels()
	{
		return array();
	}
	
	public function attributeLabels()
	{
		$out= array();
		$in = $this->customLabels();
		foreach ($in as $key=>$label)
		{
			$newkey = '[attr]'.$key
			$out[$newkey] = $label;
		}
		return $out;
	}
*/
	

	public function rules()
	{
		return array(
			array('attr, pageNum, noOfItem, totalRow, searchField, searchValue, orderField, orderType','safe',),
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
	
	public function setCriteria($criteria)
	{
		if (count($criteria) > 0) {
			foreach ($criteria as $k=>$v) {
				$this->$k = $v;
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
		);
	}
}