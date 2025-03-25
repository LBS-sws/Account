<tr class='clickable-row <?php echo $this->record['style']; ?>' style="<?php echo $this->record['color']; ?>" data-href='<?php echo $this->getLink('PS01', 'planeAudit/edit', 'planeAudit/view', array('index'=>$this->record['id']));?>'>
    <td><?php echo $this->drawEditButton('PS01', 'planeAudit/edit', 'planeAudit/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['code']; ?></td>
	<td><?php echo $this->record['name']; ?></td>
	<td><?php echo $this->record['city_name']; ?></td>
	<td><?php echo $this->record['plane_date']; ?></td>
	<td><?php echo $this->record['plane_sum']; ?></td>
	<td><?php echo $this->record['old_pay_wage']; ?></td>
	<td><?php echo $this->record['difference']; ?></td>
	<td><?php echo $this->record['plane_status']; ?></td>
</tr>
