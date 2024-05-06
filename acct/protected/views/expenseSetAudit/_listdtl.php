<tr class='clickable-row' data-href='<?php echo $this->getLink('DE06', 'expenseSetAudit/edit', 'expenseSetAudit/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('DE06', 'expenseSetAudit/edit', 'expenseSetAudit/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['employee_name']; ?></td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['audit_user_str']; ?></td>
</tr>

