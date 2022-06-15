<tr class='clickable-row' data-href='<?php echo $this->getLink('XS102', 'sellSearch/view', 'sellSearch/view', array('index'=>$this->record['id']));?>'>
    <td><?php echo $this->drawEditButton('XS102', 'sellSearch/view', 'sellSearch/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['code']; ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['dept_name']; ?></td>
    <td><?php echo $this->record['time']; ?></td>
	<td><?php echo $this->record['moneys']; ?></td>
</tr>
