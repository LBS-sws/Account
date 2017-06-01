<tr class='clickable-row' data-href='<?php echo $this->getLink('D01', 'user/edit', 'user/view', array('index'=>$this->record['username']));?>'>
	<td><?php echo $this->drawEditButton('D01', 'user/edit', 'user/view', array('index'=>$this->record['username'])); ?></td>
	<td><?php echo $this->record['username']; ?></td>
	<td><?php echo $this->record['disp_name']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['status']; ?></td>
	<td><?php echo $this->record['logon_time']; ?></td>
	<td><?php echo $this->record['logoff_time']; ?></td>
</tr>
