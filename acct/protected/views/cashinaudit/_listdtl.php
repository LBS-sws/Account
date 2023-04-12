<tr class='clickable-row' data-href='<?php echo $this->getLink('XE04', (empty($this->record['audit_user'])?'cashinaudit/edit':'cashinaudit/view'), 'cashinaudit/view', array('index'=>$this->record['id']));?>'>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $this->record['audit_dt']; ?></td>
	<td><?php echo $this->record['balance']; ?></td>
	<td><?php echo $this->record['rec_amt']; ?></td>
	<td><?php echo $this->record['req_user_name']; ?></td>
	<td><?php echo $this->record['audit_user_name']; ?></td>
</tr>
