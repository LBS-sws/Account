<tr>
	<th>  <input name="Fruit"  type="checkbox"  id="all"></th>

    <?php if (!Yii::app()->user->isSingleCity()) : ?>
        <th>
            <?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('Invoice-list','b.name'))
            ;
            ?>
        </th>
    <?php endif ?>
	<th>
		<?php echo TbHtml::link($this->getLabelName('invoice_no').$this->drawOrderArrow('a.invoice_no'),'#',$this->createOrderLink('Invoice-list','a.invoice_no'))
			;
		?>
	</th>

	<th>
		<?php echo TbHtml::link($this->getLabelName('invoice_dt').$this->drawOrderArrow('a.invoice_dt'),'#',$this->createOrderLink('Invoice-list','a.invoice_dt'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('customer_code').$this->drawOrderArrow('a.customer_code'),'#',$this->createOrderLink('Invoice-list','a.customer_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('staff_name').$this->drawOrderArrow('a.staff_name'),'#',$this->createOrderLink('Invoice-list','a.staff_name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('payment_term').$this->drawOrderArrow('a.payment_term'),'#',$this->createOrderLink('Invoice-list','a.payment_term'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name_zh').$this->drawOrderArrow('a.name_zh'),'#',$this->createOrderLink('Invoice-list','a.name_zh'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('head_type').$this->drawOrderArrow('a.head_type'),'#',$this->createOrderLink('Invoice-list','a.head_type'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('print_email').$this->drawOrderArrow('a.print_email'),'#',$this->createOrderLink('Invoice-list','a.print_email'))
			;
		?>
	</th>

</tr>
