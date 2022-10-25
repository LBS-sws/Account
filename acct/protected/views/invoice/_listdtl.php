<tr class='clickable-row' data-href='<?php echo $this->getLink('XI01', 'invoice/edit', 'invoice/view', array('index'=>$this->record['id']));?>'>
    <td class="che"> <input value="<?php echo $this->record['id']; ?>"  type="checkbox" name="InvoiceList[attr][]" ></td>
    <?php if (!Yii::app()->user->isSingleCity()) : ?>
        <td><?php echo $this->record['city_name']; ?></td>
    <?php endif ?>
	<td><?php echo $this->record['invoice_no']; ?></td>
	<td><?php echo $this->record['invoice_dt']; ?></td>
	<td><?php echo $this->record['customer_code']; ?></td>
	<td><?php echo $this->record['staff_name']; ?></td>
	<td><?php echo $this->record['payment_term']; ?></td>
	<td><?php echo $this->record['name_zh']; ?></td>

</tr>
