var repeatId = [];//物品的所有Id

function changeRepeatId() {
    var $brotherInput = $("#table-change input.testInput");
    repeatId = [];
    $brotherInput.each(function () {
        var goods_id = $(this).next("input").val();
        repeatId.push(goods_id);
    })
}

function inputDownList(arr,fn,bool){

    //獲取焦點后
    $("body").delegate(".testInput","focus",function (e) {
        var $that = $(this);
        var div = document.createElement("div");
        var ul = document.createElement("ul");
        $(div).addClass("dropdown-div").css({
            top:$that.offset().top+$that.outerHeight(),
            left:$that.offset().left
        });
        $(ul).addClass("dropdown-menu");
        $.each( arr, function(key, val){
            if(bool && val["goods_class"] != $("#OrderForm_order_class").val()){
                //類型是否一致
                return true;
            }
            if(bool  && ($.inArray(val["id"],repeatId))>=0){
                //去除已經存在的物品
                return true;
            }
            var li = document.createElement("li");
            $(li).attr({
                "dataid":val["id"],
                "datacode":val["goods_code"],
                "dataname":val["name"],
                "dataunit":val["unit"],
                "dataprice":val["price"],
                "datatype":val["type"]
            }).append("<a href='#'>"+val["goods_code"]+" -- "+val["name"]+"</a>");
            $(ul).append(li);
        });
        $(div).append(ul);
        $("body").append(div);
        $("body").on("click.menu",function () {
            $(div).remove();
            $("body").off(".menu");
            $that.off("keyup");
            $(".dropdown-div>.dropdown-menu a").undelegate("mousedown");
            validateGoods($that);
        });
        //選擇菜單的某個元素
        $(".dropdown-div>.dropdown-menu").delegate("a","mousedown",function () {
            $that.val($(this).parent("li").attr("datacode"));
            $that.next("input").val($(this).parent("li").attr("dataid"));
            if(fn != undefined){
                fn($that,$(this).parent("li"),bool);
            }
        });
        //鍵盤事件下拉菜單發送變化
        $(this).on("keyup",function () {
            $(ul).html("");
            $.each( arr, function(key, val){
                var str = $that.val();
                if(val["goods_code"].split(str).length > 1 ){
                    if(bool && val["goods_class"] != $("#OrderForm_order_class").val()){
                        //類型是否一致
                        return true;
                    }
                    if(bool  && ($.inArray(val["id"],repeatId))>=0){
                        //去除已經存在的物品
                        return true;
                    }
                    var li = document.createElement("li");
                    $(li).attr({
                        "dataid":val["id"],
                        "datacode":val["goods_code"],
                        "dataname":val["name"],
                        "dataunit":val["unit"],
                        "dataprice":val["price"],
                        "datatype":val["type"]
                    }).append("<a href='#'>"+val["goods_code"]+" -- "+val["name"]+"</a>");
                    $(ul).append(li);
                }
            });
        });

        if($(this).val() != "" && $(this).val() != undefined){
            $(this).trigger("keyup");
        }
    });

    //終止輸入框的事件冒泡
    $("body").delegate(".testInput","click",function (e) {
        e.preventDefault();
        return false;
    });

    //輸入框的驗證觸發事件
    $("body").delegate(".testInput","blur",function (e) {
        $("body").trigger("click");
    });

    //輸入框的驗證
    function validateGoods($element) {
        var id = $element.next("input").val();
        var name = $element.val();
        var bool = true;
        var lastname = "";
        $.each(arr,function (index, obj) {
            if(obj["id"] == id){
                lastname = obj["goods_code"];
                if(lastname == name){
                    bool = false;
                    return false;
                }
            }
        });
        if(bool){
            $element.val(lastname);
        }
    }

}
//表格內的物品發生變化，價格隨之變化
function tableGoodsChange($ele,$li,bool) {
    var $tr = $ele.parents("tr");
    if($tr.length > 0){
        var $tr = $ele.parents("tr");
        $tr.find("input.name").val($li.attr("dataname"));
        $tr.find("input.type").val($li.attr("datatype"));
        $tr.find("input.unit").val($li.attr("dataunit"));
        $tr.find("input.price").val($li.attr("dataprice"));
        if(bool){
            goodsTotalPrice();
            changeRepeatId();
        }
    }
}

//價格計算
function goodsTotalPrice() {
    var $table = $("#table-change>tbody");
    var totalPrice = 0;
    $table.find("tr").each(function () {
        var price = $(this).find("input.price").val();
        var num = $(this).find("input.goods_num").val();
        var sum = 0;
        var tem = "";

        if(price == "" || isNaN(price)){
            price = 0;
        }else{
            price = parseFloat(price);
        }
        if(num == "" || isNaN(num)){
            num = 0;
        }else{
            num = parseFloat(num);
        }

        sum = (price*100000)*num/100000;
        tem = sum.toString().split(".");
        if(tem.length == 2){
            if(tem[1].length >2){
                sum = sum.toFixed(2);
            }
        }
        totalPrice=(sum*100000 +totalPrice*100000)/100000;
        $(this).find("input.sum").val(sum);
    });
    $("#table-change>tfoot>tr>td").eq(1).text(totalPrice);
}

//向表格內添加物品
function addGoodsTable(data) {
    if($(this).prop("disabled")){
        return false;
    }
    var num = $("#table-change>tbody>tr:last").attr("datanum");
    num = $("#table-change>tbody>tr").length < 1?0:num;
    if(num == undefined && num == "" && num == undefined && isNaN(num)){
        alert("添加異常，請刷新頁面");
        return false;
    }
    num = parseInt(num)+1;
    var html ='<tr datanum="'+num+'">'+
        '<td>' +
        '<input type="text" autocomplete="off" class="form-control testInput" name="OrderForm[goods_list]['+num+'][goods_code]" >' +
        '<input type="hidden" name="OrderForm[goods_list]['+num+'][goods_id]"></td>'+
    '<td><input type="text" class="form-control name" name="OrderForm[goods_list]['+num+'][name]" readonly></td>'+
    '<td><input type="text" class="form-control type" name="OrderForm[goods_list]['+num+'][type]" readonly></td>'+
    '<td><input type="text" class="form-control unit" name="OrderForm[goods_list]['+num+'][unit]" readonly></td>'+
    '<td><input type="text" class="form-control price" name="OrderForm[goods_list]['+num+'][price]" readonly></td>'+
    '<td><input type="number" min="0" class="form-control numChange goods_num" name="OrderForm[goods_list]['+num+'][goods_num]"></td>'+
    '<td><input type="text" class="form-control sum" readonly></td>'+
    '<td><button type="button" class="btn btn-danger delGoods">'+data.data.btnStr+'</button></td>'+
    '</tr>';

    $("#table-change>tbody").append(html);
    changeRepeatId();
}

//向表格內添加物品(技術員)
function addGoodsTableTwo(data) {
    if($(this).prop("disabled")){
        return false;
    }
    var num = $("#table-change>tbody>tr:last").attr("datanum");
    num = $("#table-change>tbody>tr").length < 1?0:num;
    if(num == undefined && num == "" && num == undefined && isNaN(num)){
        alert("添加異常，請刷新頁面");
        return false;
    }
    num = parseInt(num)+1;
    var html ='<tr datanum="'+num+'">'+
        '<td>' +
        '<input type="text" autocomplete="off" class="form-control testInput" name="TechnicianForm[goods_list]['+num+'][goods_code]" >' +
        '<input type="hidden" name="TechnicianForm[goods_list]['+num+'][goods_id]"></td>'+
    '<td><input type="text" class="form-control name" name="TechnicianForm[goods_list]['+num+'][name]" readonly></td>'+
    '<td><input type="text" class="form-control type" name="TechnicianForm[goods_list]['+num+'][type]" readonly></td>'+
    '<td><input type="text" class="form-control unit" name="TechnicianForm[goods_list]['+num+'][unit]" readonly></td>'+
    '<td><input type="number" min="0" class="form-control numChange goods_num" name="TechnicianForm[goods_list]['+num+'][goods_num]"></td>'+
    '<td><button type="button" class="btn btn-danger delGoods">'+data.data.btnStr+'</button></td>'+
    '</tr>';

    $("#table-change>tbody").append(html);
}

//刪除表格里的某條物品
function delGoodsTable(data) {
    if($(this).prop("disabled")){
        return false;
    }
    var dataId = $(this).next("input");
    if(dataId.length < 1){
        $(this).parents("tr").remove();
    }else{
        if(confirm(data.data)){
            $.ajax({
                type: "post",
                url: "./OrderGoodsDelete",
                data: {id:dataId.val()},
                dataType: "json",
                success: function(data){
                    if(data.status == 1){
                        dataId.parents("tr").remove();
                    }
                }
            });
        }
    }
}


//控制表格能否輸入
function disabledTable(bool) {
    switch (bool){
        case 1:
            $("#table-change").find("input").prop("disabled",true);
            $("#table-change").find("button").prop("disabled",true);
            break
        case 2:
            $("#table-change").find("input").prop("readonly",true);
            $("#table-change").find("button").prop("disabled",true);
            break
    }
}


function confirmTotalPrice() {
    var $table = $("#confirm-change>tbody");
    var totalPrice = 0;
    $table.find("tr").each(function () {
        var price = $(this).find("td.price").text();
        var number = $(this).find("input.confirm_num").val();
        var sum = 0;
        var tem = "";

        if(price == "" || isNaN(price)){
            price = 0;
        }else{
            price = parseFloat(price);
        }
        if(number == "" || isNaN(number)){
            number = 0;
        }else{
            number = parseFloat(number);
        }

        sum = (price*100000)*number/100000;
        tem = sum.toString().split(".");
        if(tem.length == 2){
            if(tem[1].length >2){
                sum = sum.toFixed(2);
            }
        }
        totalPrice=(sum*100000 +totalPrice*100000)/100000;
        $(this).find("td:last").text(sum);
    });
    $("#confirm-change>tfoot>tr:last>td:last").text(totalPrice);
}



