
<tr class='clickable-row <?php echo $this->record['style'];?>' data-href='<?php echo $this->getLink('SG01', 'salesGroupBelow/edit', 'salesGroupBelow/view', array('index'=>$this->record['id']));?>'>
    <td class="che">
        <?php if ($this->record['ready']): ?>
            <input value="<?php echo $this->record['id']; ?>"  type="checkbox">
        <?php endif ?>
    </td>
    <td><?php echo $this->drawEditButton('SG01', 'salesGroupBelow/edit', 'salesGroupBelow/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['code']; ?></td>
    <td><?php echo $this->record['name']; ?></td>
    <td><?php echo $this->record['city_name']; ?></td>
    <td><?php echo $this->record['dept_name']; ?></td>
    <td><?php echo $this->record['time']; ?></td>
    <td><?php echo $this->record['bonus_amount']; ?></td>
</tr>

