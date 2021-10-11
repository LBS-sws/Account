<tr class='clickable-row' data-href='<?php echo $this->getLink('XS09', 'IDLadder/edit', 'IDLadder/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('XS09', 'IDLadder/edit', 'IDLadder/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['only_num']; ?></td>
</tr>
