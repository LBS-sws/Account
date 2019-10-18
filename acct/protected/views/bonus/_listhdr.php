<tr>
	<th></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city').$this->drawOrderArrow('city'),'#',$this->createOrderLink('Bonus-list','city'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('year').$this->drawOrderArrow('year'),'#',$this->createOrderLink('Bonus-list','year'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('month').$this->drawOrderArrow('month'),'#',$this->createOrderLink('Bonus-list','month'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('money').$this->drawOrderArrow('money'),'#',$this->createOrderLink('Bonus-list','money'))
        ;
        ?>
    </th>
</tr>
