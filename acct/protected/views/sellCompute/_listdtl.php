<tr <?php echo $this->record['style']; ?> class='clickable-row' data-href='<?php echo $this->getLink('XS01', 'sellCompute/view', 'sellCompute/view', array('index'=>$this->record['id']));?>'>
    <td><?php echo $this->drawEditButton('XS01', 'sellCompute/view', 'sellCompute/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['code']; ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['dept_name']; ?></td>
    <td><?php echo $this->record['time']; ?></td>
	<td><?php echo $this->record['moneys']; ?></td>
    <td><?php echo $this->record['examine']; ?></td>
</tr>
