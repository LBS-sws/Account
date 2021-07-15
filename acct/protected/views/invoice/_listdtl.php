<tr class='clickable-row' data-href='<?php echo $this->getLink('XI01', 'invoice/edit', 'invoice/view', array('index'=>$this->record['id']));?>'>
    <td class="che"> <input value="<?php echo $this->record['id']; ?>"  type="checkbox" name="InvoiceList[attr][]" ></td>
	<td><?php echo $this->record['invoice_no']; ?></td>
	<td><?php echo $this->record['invoice_dt']; ?></td>
	<td><?php echo $this->record['customer_code']; ?></td>
	<td><?php echo $this->record['name_zh']; ?></td>

</tr>
