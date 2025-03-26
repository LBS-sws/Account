<tr>
    <th width="1%"><input name="Fruit"  type="checkbox"  id="all"></th>
    <th width="1%"></th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('code').$this->drawOrderArrow('b.code'),'#',$this->createOrderLink('PerformanceBonus-list','b.code'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('name').$this->drawOrderArrow('b.name'),'#',$this->createOrderLink('PerformanceBonus-list','b.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('city_name').$this->drawOrderArrow('e.name'),'#',$this->createOrderLink('PerformanceBonus-list','e.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('dept_name').$this->drawOrderArrow('c.name'),'#',$this->createOrderLink('PerformanceBonus-list','c.name'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('time'),'#')
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('quarter_no'),'#')
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('status_type').$this->drawOrderArrow('g.status_type'),'#',$this->createOrderLink('PerformanceBonus-list','g.status_type'))
        ;
        ?>
    </th>
    <th>
        <?php echo TbHtml::link($this->getLabelName('bonus_out').$this->drawOrderArrow('g.bonus_out'),'#',$this->createOrderLink('PerformanceBonus-list','g.bonus_out'))
        ;
        ?>
    </th>
</tr>
