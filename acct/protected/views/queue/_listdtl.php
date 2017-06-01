<?php
	$idx = $this->recordptr + ($this->model->pageNum - 1) * $this->model->noOfItem;
	$dlnk = Yii::app()->createUrl('queue/view',array('index'=>$this->record['id']));
?>
<tr>
	<td><?php echo $this->record['id']; ?></td>
	<td><?php echo $this->record['rpt_desc']; ?></td>
	<td><?php echo $this->record['rpt_type']; ?></td>
	<td><?php echo $this->record['req_dt']; ?></td>
	<td><?php echo $this->record['fin_dt']; ?></td>
	<td>
		<?php 
			if ($this->record['sts']=='C')
				echo TbHtml::Button('<span class="fa fa-download"></span> '.Yii::t('misc','Download'), array('submit'=>$dlnk,'size' => TbHtml::BUTTON_SIZE_SMALL));
			else
				echo $this->record['status']; 
		?>
	</td>
</tr>
