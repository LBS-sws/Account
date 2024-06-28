<tr class='clickable-row' data-href='<?php echo $this->getLink('DE05', 'expenseSetName/edit', 'expenseSetName/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('DE05', 'expenseSetName/edit', 'expenseSetName/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['return_value']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['display']; ?></td>
</tr>
