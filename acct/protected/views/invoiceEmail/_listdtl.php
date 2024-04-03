<?php $params = array('index'=>$this->record['id']); ?>
<tr class='clickable-row' data-href='<?php echo $this->getLink('XC09', 'invoiceEmail/edit', 'invoiceEmail/view', $params);?>'>
	<td><?php echo $this->drawEditButton('XC09', 'invoiceEmail/edit', 'invoiceEmail/view', $params); ?></td>
	<td><?php echo $this->record['start_dt']; ?></td>
	<td><?php echo $this->record['email_text']; ?></td>
	<td><?php echo $this->record['remarks']; ?></td>
</tr>
