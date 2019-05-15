<tr class='clickable-row' data-href='<?php echo $this->getLink('XE05', 't3audit/edit', 't3audit/view', array('index'=>$this->record['id']));?>'>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $this->record['audit_year']; ?></td>
	<td><?php echo $this->record['audit_month']; ?></td>
	<td><?php echo $this->record['req_user_name']; ?></td>
	<td><?php echo $this->record['req_dt']; ?></td>
	<td><?php echo $this->record['audit_user_name']; ?></td>
	<td><?php echo $this->record['audit_dt']; ?></td>
</tr>
