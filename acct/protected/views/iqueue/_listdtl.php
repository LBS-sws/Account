<?php
	$idx = $this->recordptr + ($this->model->pageNum - 1) * $this->model->noOfItem;
	$dlnk = Yii::app()->createUrl('iqueue/view',array('index'=>$this->record['id']));
	$id = $this->record['id'];
	$title = Yii::t('import','View Log');
?>
<tr>
	<td><?php echo $this->record['id']; ?></td>
	<td><?php echo $this->record['import_type']; ?></td>
	<td><?php echo $this->record['req_dt']; ?></td>
	<td><?php echo $this->record['fin_dt']; ?></td>
	<td>
		<?php 
			echo $this->record['status']; 
			if ($this->record['sts']=='C'||$this->record['sts']=='F')
				echo "&nbsp;<a href='javascript:showlog($id);'><span class='fa fa-info-circle' title='$title'></span></a>";
		?>
	</td>
</tr>
