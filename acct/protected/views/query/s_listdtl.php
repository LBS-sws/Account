<tr  >
<!--    <td><input type="checkbox" value="--><?php //echo $this->record['id']; ?><!--" name="ReportXS01From[id][]" checked="checked"></td>-->
    <td><?php echo $this->record['city_name']; ?></td>
    <td><?php echo $this->record['first_dt']; ?></td>
    <td><?php echo $this->record['sign_dt']; ?></td>
    <td><?php echo $this->record['company_name']; ?></td>
    <td><?php echo $this->record['othersalesman']; ?></td>
    <td><?php echo $this->record['type_desc']; ?></td>
    <td><?php echo $this->record['service']; ?></td>
    <td><?php echo $this->record['amt_paid']; ?></td>
    <td><?php echo $this->record['amt_install']; ?></td>
    <?php if($this->record['status_copy']==0){?>
    <td><?php echo '否'; ?></td>
    <?php }else{?>
        <td><?php echo '是'; ?></td>
    <?php }?>
</tr>
