<tr class='clickable-row <?php echo $this->record['color']; ?>' data-href='<?php echo $this->getLink('RT03', 'remitPayment/edit', 'remitPayment/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('RT03', 'remitPayment/edit', 'remitPayment/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['exp_code']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['apply_date']; ?></td>
	<td><?php echo $this->record['employee']; ?></td>
	<td><?php echo $this->record['department']; ?></td>
	<td><?php echo $this->record['amt_money']; ?></td>
	<td><?php echo $this->record['status_str']; ?></td>
</tr>
