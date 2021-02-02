<tr  <?php if($this->record['examine']=='待审核'){echo "style='color: blue'";} if($this->record['examine']=='审核通过'){echo "style='color: green'";}?>  class='clickable-row' data-href='<?php echo $this->getLink('XS07', 'salestable/edit', 'salestable/view', array('index'=>$this->record['id']));?>'>

	<td><?php echo $this->record['employee_code']; ?></td>
	<td><?php echo $this->record['employee_name']; ?></td>
	<td><?php echo $this->record['city']; ?></td>
	<td><?php echo $this->record['time']; ?></td>
	<td><?php echo $this->record['user_name']; ?></td>
    <td><?php echo $this->record['examine']; ?></td>
</tr>
