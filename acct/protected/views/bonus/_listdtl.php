<tr class='clickable-row' data-href='<?php echo $this->getLink('HK04', 'bonus/edit', 'bonus/view', array('index'=>$this->record['id']));?>'>
	<td><?php echo $this->drawEditButton('HK04', 'bonus/edit', 'bonus/view', array('index'=>$this->record['id'])); ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['year']; ?></td>
	<td><?php echo $this->record['month']; ?></td>
    <td><?php echo $this->record['money']; ?></td>
</tr>

