<?php
$timestamp = time();
$string = "{
    \"consumerKey\": \"2FMf6HR9RDOCN0tL7QHJag\",
    \"companyOuid\": \"plhbKeM4R3yHHHqkh513Fw\",
    \"data\": {
        \"shopId\": \"755001\",
        \"ordersNo\": \"istore0026\",
        \"ordersSeq\": \"istore0026\",
        \"ordersTime\": \"2018-1-26 09:05:33\",
        \"deliverType\": \"H\",
        \"totalAmount\": \"109\",
        \"paidStatus\": \"P\",
        \"paidAmount\": \"109\",
        \"customerRefNo\": \"istore0008\",
        \"memo\": \"测试2\",
        \"takeaway\": {
            \"receiverTel\": \"13732925540\",
            \"receiverName\": \"LL\",
            \"receiverRegion\": \"深圳市南山区\",
            \"receiverAddress\": \"大冲商务中心D栋1203\",
            \"receiverPosition\": \"23.32,113.56\"
        },
        \"items\": [
            {
                \"skuId\": \"30100014\",
                \"skuName\": \"芝士咖啡\",
                \"salePrice\": \"24\",
                \"qty\": \"2\",
                \"saleAmount\": \"48\",
                \"saleSubtotal\": \"50\",
                \"memo\": \"少冰\",
                \"mixs\": [
                ],
                \"combos\": [
                ]
            }
        ],
        \"fees\": [
            {
                \"feeType\": \"S\",
                \"feeName\": \"门店优惠\",
                \"feePrice\": \"1\",
                \"feeQty\": \"2\",
                \"feeAmount\": \"-2\"
            }
        ]
    }
}";


function trimall($str){
    $qian=array(" ","　","\t","\n","\r");
    return str_replace($qian, '', $str);
}

var_dump(trimall($string));
echo md5(trimall($string)."15017273191dd4267a188698fbca03e83d560ce296");
/*$myfile = fopen("istorenewfile.txt", "w") or die("Unable to open file!");
fwrite($myfile, $order);
fclose($myfile);*/
?>