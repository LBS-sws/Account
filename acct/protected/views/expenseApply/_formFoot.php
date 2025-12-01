<tr class="changeFootOne">
    <th>&nbsp;</th>
    <th colspan="4" class="text-right">人民币合计(RMB)</th>
    <th  id="changeFootSumNum">&nbsp;</th>
    <th>&nbsp;</th>
    <?php
    $tdTwoList = ExpenseFun::getAmtTypeTwo();
    $html="";
    foreach ($tdTwoList as $key=>$itemList){
        $key="".$key;
        $html.= "<th>";
        $html.="&nbsp;";
        $html.= "</th>";
    }
    echo $html;
    ?>
</tr>
<tr>
    <th>人民币大写</th>
    <th colspan="4" class="text-center" id="changeFootSumStr">&nbsp;</th>
    <th>&nbsp;</th>
    <?php
    $tdTwoList = ExpenseFun::getAmtTypeTwo();
    $tdCount = count($tdTwoList);
    echo "<th colspan='{$tdCount}'>&nbsp;</th>";
    ?>
</tr>
