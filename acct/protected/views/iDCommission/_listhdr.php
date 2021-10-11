    <tr>

    <th>
        <?php echo TbHtml::link($this->getLabelName('employee_code').$this->drawOrderArrow('a.code'),'#',$this->createOrderLink('commission-list','a.code'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('employee_name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('commission-list','a.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('e.name'),'#',$this->createOrderLink('commission-list','e.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('description').$this->drawOrderArrow('c.name'),'#',$this->createOrderLink('commission-list','c.name'))
        ;
        ?>
    </th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('日期'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('sum_amount'))
			;
		?>
	</th>


</tr>
