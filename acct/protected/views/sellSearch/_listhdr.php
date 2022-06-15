<tr>
	<th width="1%"></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('code').$this->drawOrderArrow('b.code'),'#',$this->createOrderLink('sellCompute-list','b.code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('sellCompute-list','b.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('e.name'),'#',$this->createOrderLink('sellCompute-list','e.name'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('dept_name').$this->drawOrderArrow('c.name'),'#',$this->createOrderLink('sellCompute-list','c.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('time'),'#')
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('moneys').$this->drawOrderArrow('moneys'),'#',$this->createOrderLink('sellCompute-list','moneys'))
        ;
        ?>
    </th>
</tr>
