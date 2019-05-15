<?php
	$idx = $this->recordptr + ($this->model->pageNum - 1) * $this->model->noOfItem;
	$dlnk = Yii::app()->createUrl('notice/view',array('index'=>$this->record['id']));
?>
<tr class='clickable-row' data-href='<?php echo Yii::app()->createUrl('notice/view',array('index'=>$this->record['id'])); ?>'>
	<td><?php echo $this->record['note_dt']; ?></td>
	<td><?php echo $this->record['note_type']; ?></td>
	<td><?php echo $this->record['subject']; ?></td>
	<td><?php echo $this->record['status']; ?></td>
</tr>
