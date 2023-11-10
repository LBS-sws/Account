<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('consult_code').$this->drawOrderArrow('consult_code'),'#',$this->createOrderLink('consultSearch-list','consult_code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('apply_date').$this->drawOrderArrow('apply_date'),'#',$this->createOrderLink('consultSearch-list','apply_date'))
			;
		?>
	</th>
    <!--刪除客戶識別號
	<th>
		<?php echo TbHtml::link($this->getLabelName('customer_code').$this->drawOrderArrow('customer_code'),'#',$this->createOrderLink('consultSearch-list','customer_code'))
			;
		?>
	</th>
	-->
	<th>
		<?php echo TbHtml::link($this->getLabelName('consult_money').$this->drawOrderArrow('consult_money'),'#',$this->createOrderLink('consultSearch-list','consult_money'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('apply_city').$this->drawOrderArrow('apply_city'),'#',$this->createOrderLink('consultSearch-list','apply_city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('audit_city').$this->drawOrderArrow('audit_city'),'#',$this->createOrderLink('consultSearch-list','audit_city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('status').$this->drawOrderArrow('status'),'#',$this->createOrderLink('consultSearch-list','status'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('countdoc').$this->drawOrderArrow('countdoc'),'#',$this->createOrderLink('consultSearch-list','countdoc'))
        ;
        ?>
    </th>
</tr>
