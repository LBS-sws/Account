<tr  style="color: <?php if(!empty($this->record['color'])&&$this->record['color']==2){ echo "red";}?>" >
<!--    <td><input type="checkbox" value="--><?php //echo $this->record['id']; ?><!--" name="ReportXS01List[id][]" checked="checked"></td>-->
    <td><?php echo $this->record['city_name']; ?></td>
    <td><?php echo $this->record['first_dt']; ?></td>
    <td><?php echo $this->record['sign_dt']; ?></td>
    <td><?php echo $this->record['company_name']; ?></td>
    <td><?php echo $this->record['othersalesman']; ?></td>
    <td><?php echo $this->record['type_desc']; ?></td>
    <td><?php echo $this->record['service']; ?></td>
    <td><?php echo $this->record['amt_paid']; ?></td>
    <td><?php echo $this->record['amt_install']; ?></td>

</tr>
