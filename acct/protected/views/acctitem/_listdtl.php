<tr class='clickable-row' data-href='<?php echo $this->getLink('XC06', 'acctitem/edit', 'acctitem/view', array('index'=>$this->record['code']));?>'>
	<td><?php echo $this->drawEditButton('XC06', 'acctitem/edit', 'acctitem/view', array('index'=>$this->record['code'])); ?></td>
	<td><?php echo $this->record['code']; ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['item_type']; ?></td>
	<td><?php echo $this->record['acct_code']; ?></td>
</tr>
