<tr>
	<th width="1%"></th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('code').$this->drawOrderArrow('a.code'),'#',$this->createOrderLink('planeAward-list','a.code'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('a.name'),'#',$this->createOrderLink('planeAward-list','a.name'))
			;
		?>
	</th>
	<th>
		<?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('planeAward-list','a.name'))
			;
		?>
	</th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('job_num').$this->drawOrderArrow('f.job_num'),'#',$this->createOrderLink('planeAward-list','f.job_num'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('money_num').$this->drawOrderArrow('f.money_num'),'#',$this->createOrderLink('planeAward-list','f.money_num'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('year_num').$this->drawOrderArrow('f.year_num'),'#',$this->createOrderLink('planeAward-list','f.year_num'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('other_sum').$this->drawOrderArrow('f.other_sum'),'#',$this->createOrderLink('planeAward-list','f.other_sum'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('plane_sum').$this->drawOrderArrow('f.plane_sum'),'#',$this->createOrderLink('planeAward-list','f.plane_sum'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('old_pay_wage').$this->drawOrderArrow('f.old_pay_wage'),'#',$this->createOrderLink('planeAward-list','f.old_pay_wage'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('difference').$this->drawOrderArrow('difference'),'#',$this->createOrderLink('planeAward-list','difference'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('plane_status').$this->drawOrderArrow('plane_status'),'#',$this->createOrderLink('planeAward-list','plane_status'))
        ;
        ?>
    </th>
</tr>
