<tr  style="color: <?php if(!empty($this->record['color'])&&$this->record['color']==2){ echo "red";}?>" >
    <td><input type="checkbox" value="<?php echo $this->record['id']; ?>" name="ReportXS01List[id][]" checked="checked"></td>
    <td><?php echo $this->record['city_name']; ?></td>
    <td><?php echo $this->record['log_dt']; ?></td>
    <td><?php echo $this->record['company_name']; ?></td>
    <td><?php echo $this->record['description']; ?></td>
    <td><?php echo $this->record['qty']; ?></td>
    <td><?php echo $this->record['money']; ?></td>
    <td><?php echo $this->record['moneys']; ?></td>
</tr>
