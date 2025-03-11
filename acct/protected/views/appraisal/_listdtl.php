
<tr class='clickable-row <?php echo $this->record['style'];?>' data-href='<?php echo $this->getLink('XS14', 'appraisal/edit', 'appraisal/view', array('index'=>$this->record['id']));?>'>
    <td class="che">
        <?php if ($this->record['ready']): ?>
            <input value="<?php echo $this->record['id']; ?>"  type="checkbox">
        <?php endif ?>
    </td>
    <td><?php echo $this->drawEditButton('XS14', 'appraisal/edit', 'appraisal/view', array('index'=>$this->record['id'])); ?></td>

    <td><?php echo $this->record['code']; ?></td>
    <td><?php echo $this->record['name']; ?></td>
    <td><?php echo $this->record['entry_time']; ?></td>
    <td><?php echo $this->record['city_name']; ?></td>
    <td><?php echo $this->record['dept_name']; ?></td>
    <td><?php echo $this->record['time']; ?></td>
    <td><?php echo $this->record['status_type']; ?></td>
</tr>

