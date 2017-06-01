<tr class='clickable-row' data-href='<?php echo $this->getLink('XE03', 'transout/edit', 'transout/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('XE03', 'transout/edit', 'transout/view', array('index'=>$this->record['id'])); ?></td>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $this->record['trans_dt']; ?></td>
	<td><?php echo $this->record['trans_type_desc']; ?></td>
	<td><?php echo $this->record['acct_type_desc']; ?></td>
	<td><?php echo $this->record['bank_name']; ?></td>
	<td><?php echo $this->record['acct_no']; ?></td>
	<td><?php echo $this->record['amount']; ?></td>
	<td><?php echo $this->record['status']; ?></td>
</tr>
