<tr class='clickable-row <?php echo $this->record['color']; ?>' data-href='<?php echo $this->getLink('CF04', 'consultSearch/edit', 'consultSearch/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('CF04', 'consultSearch/edit', 'consultSearch/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['consult_code']; ?></td>
	<td><?php echo $this->record['apply_date']; ?></td>
	<td><?php echo $this->record['customer_code']; ?></td>
	<td><?php echo $this->record['consult_money']; ?></td>
	<td><?php echo $this->record['apply_city']; ?></td>
	<td><?php echo $this->record['audit_city']; ?></td>
	<td><?php echo $this->record['status']; ?></td>
</tr>