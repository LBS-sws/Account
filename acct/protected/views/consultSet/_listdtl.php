<tr class='clickable-row' data-href='<?php echo $this->getLink('CF03', 'consultSet/edit', 'consultSet/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('CF03', 'consultSet/edit', 'consultSet/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['good_name']; ?></td>
	<td><?php echo $this->record['z_index']; ?></td>
	<td><?php echo $this->record['z_display']; ?></td>
</tr>
