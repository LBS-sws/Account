<?php
$ftrbtn = array();
$ftrbtn[] = TbHtml::button(Yii::t('dialog','Close'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY));
$this->beginWidget('bootstrap.widgets.TbModal', array(
    'id'=>'flowinfodialog',
    'header'=>Yii::t('give','Flow Info'),
    'footer'=>$ftrbtn,
    'show'=>false,
));
?>

<div class="box" id="flow-list" style="max-height: 300px; overflow-y: auto;">
    <table id="tblFlow" class="table table-bordered table-striped table-hover">
        <thead>
        <tr>
            <th><?php echo Yii::t("give","Operator User"); ?></th>
            <th><?php echo Yii::t("give","Operator Time"); ?></th>
            <th><?php echo Yii::t("give","Operator Text"); ?></th>
        </tr>
        </thead>
        <tbody>

        <?php
        $list = ExpenseFun::getExpenseHistoryForID($model->id);
        if($list){
            foreach ($list as $row){
                echo "<tr><td>".$row['lcu']."</td><td>".$row['lcd']."</td><td>".$row['history_text']."</td></tr>";
            }
        }
        ?>
        </tbody>
    </table>
</div>

<?php
$this->endWidget();
?>
