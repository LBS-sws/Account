<tr class='clickable-row' data-href='<?php echo $this->getLink('XA07', 'signreq/edit', 'signreq/edit', array('index'=>$this->record['id']));?>'>
<?php if (!Yii::app()->user->isSingleCity()) : ?>
	<td><?php echo $this->record['city_name']; ?></td>
<?php endif ?>
	<td><?php echo $this->record['req_dt']; ?></td>
	<td><?php echo $this->record['user_name']; ?></td>
	<td><?php echo $this->record['trans_type_desc']; ?></td>
	<td><?php echo $this->record['payee_name']; ?></td>
	<td><?php echo $this->record['item_desc']; ?></td>
	<td><?php echo $this->record['amount']; ?></td>
	<td><?php echo $this->record['ref_no']; ?></td>
	<td><?php echo $this->record['int_fee']; ?></td>
</tr>
