<?php
$serverName = "188.239.193.149\MSQLEXPRESS, 49166"; //serverName\instanceName

//$serverName = "<имя_вашего_sql-сервера\имя_инстанции,номер_порта>"; //если instance и port стандартные, то можно не указывать
$connectionInfo = array("UID" => "nd", "PWD" => "magnatBR", "Database"=>"minisoft", "CharacterSet" => "UTF-8");
$conn = sqlsrv_connect( $serverName, $connectionInfo);
 
if( $conn )
{
     echo "Connection established.<br />";
   $result = sqlsrv_query($conn, "SELECT * FROM product where product_code=24045");
if($result === false) {
    die( print_r( sqlsrv_errors(), true) );
}

while( $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC) ) {
    echo json_encode($row, JSON_UNESCAPED_UNICODE);
    echo "Название: " .$row['pname'] ."<br />";
    echo "Количество: " .$row['kolvo'] ."<br />";
    echo "Цена розничная: " .$row['cena'] ."<br />";
    echo "Цена поставщика
    : " .$row['cenapost'] ."<br />";
}
}
else
{
     echo "Connection could not be established.\n";
     die( print_r( sqlsrv_errors(), true));
}
 
 
/* Close the connection. */
sqlsrv_close( $conn);
?>