<?php
/**
 * Created by PhpStorm.
 * User: Vasilij
 * Date: 23.03.2018
 * Time: 22:57
 */
//https://discourse.slimframework.com/t/slim-3-how-to-read-post-variables-from-body/766
header("Content-Type: application/json; charset=UTF-8");

//header("Client_id: v7sKtQZu889ihwOcJyxV");
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require './vendor/autoload.php';
require './database/db.php';

$container = new \Slim\Container;
$app = new \Slim\App($container);


$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Hello!!!");

    return $response;
});

$app->get('/product_code/{code}', function (Request $request, Response $response, array $args) {
    $product_code = $args['code'];

    $db = new db();
    $conn = $db->connect();

    if ($conn) {
        $result = sqlsrv_query($conn, "SELECT * FROM product where product_code=" . $product_code);
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE);
//            echo "Название: " . $row['pname'] . "<br />";
//            echo "Количество: " . $row['kolvo'] . "<br />";
//            echo "Цена розничная: " . $row['cena'] . "<br />";
//            echo "Цена поставщика: " . $row['cenapost'] . "<br />";
        }
    } else {
        echo "Connection could not be established.\n";
        die(print_r(sqlsrv_errors(), true));
    }


    /* Close the connection. */
    $db->disconnect();

});

$app->get('/sales/product/{code}/{month}', function (Request $request, Response $response, array $args) {
    $product_code = $args['code'];
    $month = $args['month'];
    $str_month = "-" . $month . " month";
    $date = new DateTime($str_month);
    $date = $date->format('Y-m-d');
    $db = new db();
    $conn = $db->connect();

    if ($conn) {
        $result = sqlsrv_query($conn, "select SUM(frt.colvo1) as colvo, CONVERT(varchar(10), frt.ddd, 102) as date from
(SELECT sp.colvo as colvo1, sp.sales_code,  s.dates as ddd
  FROM sales_product sp
  LEFT JOIN (select sales_code, dates from sales) as s ON s.sales_code = sp.sales_code
  where product_code = " . $product_code . " and dates >='" . $date . "') as frt
 group by CONVERT(varchar(10), frt.ddd, 102)
 order by date");
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $json[] = $row;

        }
    } else {
        echo "Connection could not be established.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);
    /* Close the connection. */
    $db->disconnect();

});

$app->get('/sales/product/{code}/{d1}/{d2}', function (Request $request, Response $response, array $args) {
    $product_code = $args['code'];
    $d1 = $args['d1'];
    $d2 = $args['d2'];
    
    $date1 = new DateTime($d1);
    $d1 = $date1->format('Y-m-d H:i:s');

    $date2 = new DateTime($d2);
    $d2 = $date2->format('Y-m-d H:i:s');
    
    $db = new db();
    $conn = $db->connect();

    if ($conn) {
        $result = sqlsrv_query($conn, "select SUM(frt.colvo1) as colvo, CONVERT(varchar(10), frt.ddd, 102) as date from
(SELECT sp.colvo as colvo1, sp.sales_code,  s.dates as ddd
  FROM sales_product sp
  LEFT JOIN (select sales_code, dates from sales) as s ON s.sales_code = sp.sales_code
  where product_code = " . $product_code . " and dates >='" . $d1 . "' and dates <='" . $d2 . "') as frt
 group by CONVERT(varchar(10), frt.ddd, 102)
 order by date");
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $json[] = $row;

        }
    } else {
        echo "Connection could not be established.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);
    /* Close the connection. */
    $db->disconnect();

});

$app->get('/supplier_report/{d1}/{d2}/{postav_code}', function (Request $request, Response $response, array $args) {
    
    $d1 = $args['d1'];
    $d2 = $args['d2'];
    $postav_code = $args['postav_code'];
    
    $date1 = new DateTime($d1);
    $d1 = $date1->format('Y-m-d H:i:s');

    $date2 = new DateTime($d2);
    $d2 = $date2->format('Y-m-d H:i:s');
    
    $db = new db();
    $conn = $db->connect();

    if ($conn) {
        $result = sqlsrv_query($conn, "
        select
            [Код товара] as product_code, 
            [Наименование] as pname, 
            [Приход за период] as prihod_period, 
            [Продажи за период] as sales_period, 
            [Остаток текущий] as ostatok,
            (([Продажи за период] / (4 * 3) * 1.5) - [Остаток текущий]) as zakaz, 
            [Цена розн.] as cena_rozn, 
            [Цена закуп.] as cena_post, 
            ([Продажи за период] * [Цена розн.]) as sales_summ, 
            ([Продажи за период] * [Цена розн.] - [Продажи за период] * [Цена закуп.]) as dohod, 
            [Штрихкод] as barcodes
            from
            (
            SELECT     prihod_naklad.product_code AS [Код товара], product.pname as [Наименование], SUM(prihod_naklad.kol) AS [Приход за период]
            FROM         prihod_naklad INNER JOIN
                                  prihod ON prihod_naklad.prihod_code = prihod.prihod_code INNER JOIN
                                  postavschiki ON prihod.postav_code = postavschiki.postav_code INNER JOIN
                                  product ON prihod_naklad.product_code = product.product_code
            WHERE     (postavschiki.postav_code = ".$postav_code.") AND (prihod.prihoddate >= '".$d1."') AND (prihod.prihoddate <= '".$d2."') AND (prihod.type = 0) AND 
                                  (prihod.prihodstate = 1) AND (prihod.invent_code IS NULL)
            GROUP BY prihod_naklad.product_code, product.pname
            ) as post

            left join

            (
            SELECT     sales_product.product_code AS [Код товара1]
            , SUM(sales_product.colvo) AS [Продажи за период]
            FROM         sales_product INNER JOIN
                                  sales ON sales_product.sales_code = sales.sales_code INNER JOIN
                                  product ON sales_product.product_code = product.product_code
            WHERE     sales.dates >='".$d1."' and sales.dates <='".$d2."'
            GROUP BY sales_product.product_code, product.pname
            ) as sales
            on post.[Код товара]=sales.[Код товара1]

            left join
            (
            SELECT     product_code AS [Код товара2]
            , kolvo AS [Остаток текущий]
            FROM         product
            where disable=0
            ) as ostatok
            on post.[Код товара]=ostatok.[Код товара2]

            left join (
            select product_code AS [Код товара3], cena AS [Цена розн.], cenapost as [Цена закуп.] 
            from product
            ) as cenu 
            on post.[Код товара] = cenu.[Код товара3]


            left join (
            select product_code AS [Код товара4], cardnumberean as [Штрихкод]
            from product
            ) as shk
            on post.[Код товара] = shk.[Код товара4]

            order by [Наименование]");
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $json[] = $row;

        }
    } else {
        echo "Connection could not be established.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);
    /* Close the connection. */
    $db->disconnect();

});

$app->get('/product/all/', function (Request $request, Response $response, array $args) {

    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $result = sqlsrv_query($conn, "SELECT  product_code,pname,cena,kolvo,cardnumberean, kolvop,cenapost,plu, bcforsearch, tax_type, isfiscal, toprint FROM product order by product_code");


    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $json[] = $row;

    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);

});

$app->get('/product/inverse_product_to_print/{product_code}', function (Request $request, Response $response, array $args) {
    $product_code = $args['product_code'];

    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $procedure_params = array(
        array(&$product_code, SQLSRV_PARAM_IN)
    );

    $sql = "EXEC dbo.InverseProductToPrint @product_code = ?";

    $result = sqlsrv_query($conn, $sql, $procedure_params);
    
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $json[] = $row;

    }
    //echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);
    /* Close the connection. */
    $db->disconnect();
});
$app->get('/product/inverse_product_to_isfiscal/{product_code}', function (Request $request, Response $response, array $args) {
    $product_code = $args['product_code'];

    $db = new db();
    $conn = $db->connect();
    if ($conn) {
        $result = sqlsrv_query($conn, "update dbo.product set isfiscal = ~ isfiscal where product_code =" . $product_code);
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE);
//            echo "Название: " . $row['pname'] . "<br />";
//            echo "Количество: " . $row['kolvo'] . "<br />";
//            echo "Цена розничная: " . $row['cena'] . "<br />";
//            echo "Цена поставщика: " . $row['cenapost'] . "<br />";
        }
    } else {
        echo "Connection could not be established.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    sqlsrv_free_stmt($result);
    /* Close the connection. */
    $db->disconnect();
});
$app->post('/product/update/', function ($request, $response, $args) {

    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $json = $request->getBody();
    $data = json_decode($json);
    $procedure_params = array(


        // @product_code int,
        array($data->product_code, SQLSRV_PARAM_IN),
        // @pname varchar(255),
        array($data->pname, SQLSRV_PARAM_IN),
        // @edenicaizm varchar(10),
        array($data->edenicaizm, SQLSRV_PARAM_IN),
        // @cena decimal(24, 2),
        array($data->cena, SQLSRV_PARAM_IN),
        // @isskidka bit,
        array($data->isskidka, SQLSRV_PARAM_IN),
        // @group_code int,
        array($data->group_code, SQLSRV_PARAM_IN),
        // @kolvo numeric(24, 4),
        array($data->kolvo, SQLSRV_PARAM_IN),
        // @cardnumberean varchar(13),
        array($data->cardnumberean, SQLSRV_PARAM_IN),
        // @cardnumber bigint,
        array($data->cardnumber, SQLSRV_PARAM_IN),
        // @isourcode bit,
        array($data->isourcode, SQLSRV_PARAM_IN),
        // @nacenka float,
        array($data->nacenka, SQLSRV_PARAM_IN),
        // @article varchar(255),
        array($data->article, SQLSRV_PARAM_IN),
        // @r1 int,
        array(NULL, SQLSRV_PARAM_IN),
        // @r2 int ,
        array(NULL, SQLSRV_PARAM_IN),
        // @r3 int ,
        array(NULL, SQLSRV_PARAM_IN),
        // @ves numeric(24, 3),
        array(NULL, SQLSRV_PARAM_IN),
        // @isupakovka bit,
        array($data->isupakovka, SQLSRV_PARAM_IN),
        // @analog1 varchar(300) ,
        array($data->analog1, SQLSRV_PARAM_IN),
        // @analog2 varchar(300) ,
        array($data->analog2, SQLSRV_PARAM_IN),
        // @other varchar(5000) ,
        array($data->other, SQLSRV_PARAM_IN),
        // @mincena numeric(24, 2) ,
        array(NULL, SQLSRV_PARAM_IN),
        // @spdvcena numeric(24, 2) ,
        array(NULL, SQLSRV_PARAM_IN),
        // @nz int,
        array(NULL, SQLSRV_PARAM_IN),
        // @proizvoditel varchar(300),
        array($data->proizvoditel, SQLSRV_PARAM_IN),
        // @poshtuchno bit,
        array($data->poshtuchno, SQLSRV_PARAM_IN),
        // @kolvop int,
        array(NULL, SQLSRV_PARAM_IN),
        // @cenapost decimal(24,2),
        array($data->cenapost, SQLSRV_PARAM_IN),
        // @isprodukciya bit,
        array($data->isprodukciya, SQLSRV_PARAM_IN),
        // @bezprih bit,
        array($data->bezprih, SQLSRV_PARAM_IN),
        // @tax_type int,
        array($data->tax_type, SQLSRV_PARAM_IN),
        // @pshortname varchar(255),
        array($data->pshortname, SQLSRV_PARAM_IN),
        // @toprint bit,
        array($data->toprint, SQLSRV_PARAM_IN),
        // @isfiscal bit,
        array($data->isfiscal, SQLSRV_PARAM_IN),
        // @isdopprinter bit,
        array($data->isdopprinter, SQLSRV_PARAM_IN),
        // @isscales bit,
        array($data->isscales, SQLSRV_PARAM_IN),
        // @isprinteretiketok bit,
        array($data->isprinteretiketok, SQLSRV_PARAM_IN),
        // @ismaterial bit,
        array($data->ismaterial, SQLSRV_PARAM_IN),
        // @isinternetshop bit,
        array($data->isinternetshop, SQLSRV_PARAM_IN),
        // @isprogrscales bit,
        array($data->isprogrscales, SQLSRV_PARAM_IN),
        // @expiration_date datetime,
        array(NULL, SQLSRV_PARAM_IN),
        // @certificate_number varchar(255),
        array(NULL, SQLSRV_PARAM_IN),
        // @isuse_expiration_date bit,
        array($data->isuse_expiration_date, SQLSRV_PARAM_IN),
        // @disable bit = 0,
        array($data->disable, SQLSRV_PARAM_IN),
        // @plu int = NULL,
        array($data->plu, SQLSRV_PARAM_IN),
        // @optcena decimal(24,2),
        array($data->optcena, SQLSRV_PARAM_IN),
        // @optnacenka float,
        array($data->optnacenka, SQLSRV_PARAM_IN),
        // @currency_code int = NULL,
        array(NULL, SQLSRV_PARAM_IN),
        // @exchange float = 1,
        array(1, SQLSRV_PARAM_IN),
        // @NOTChangeKolvo bit = 0,
        array($data->NOTChangeKolvo, SQLSRV_PARAM_IN),
        // @custom_field_cb1 bit = 0
        array($data->custom_field_cb1, SQLSRV_PARAM_IN)

    );

    $sql = "{call product_update (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)}";
    var_dump($procedure_params);
    $stmt = sqlsrv_query($conn, $sql, $procedure_params);


    //while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    //    $json[] = $row;
    //}

    //echo json_encode($json, JSON_UNESCAPED_UNICODE);
    //sqlsrv_free_stmt($stmt);
});

$app->get('/product/quantity/', function (Request $request, Response $response, array $args) {

    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $result = sqlsrv_query($conn, "SELECT COUNT (*) FROM product");


//    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
//        $json[] = $row;
//
//    }
//    $json = ['c' => $result];
    $count = sqlsrv_fetch_array($result);
    $json = [['c' => $count[0]]];
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);

});

$app->get('/barcode/{code}', function (Request $request, Response $response, array $args) {
    $cardnumberean = $args['code'];

    $db = new db();
    $conn = $db->connect();

    if ($conn) {
        $result = sqlsrv_query($conn, "SELECT * FROM product where bcforsearch LIKE '%" . $cardnumberean . "%'");
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            echo json_encode($row, JSON_UNESCAPED_UNICODE);
            /* echo "Название: " . $row['pname'] . "<br />";
             echo "Количество: " . $row['kolvo'] . "<br />";
             echo "Цена розничная: " . $row['cena'] . "<br />";
             echo "Цена поставщика: " . $row['cenapost'] . "<br />";*/
        }
    } else {
        echo "Connection could not be established.\n";
        die(print_r(sqlsrv_errors(), true));
    }


    /* Close the connection. */
    $db->disconnect();
});

$app->get('/prihod_for_product/{product_code}/{d1}/{d2}', function (Request $request, Response $response, array $args) {

    $db = new db();
    $conn = $db->connect();
    $json = array();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

//    $myparams['product_code'] = 10;
//    $myparams['d1'] = '2018-01-01 00:00:00';
//    $myparams['d2'] = '2019-02-27 00:00:00';


    $myparams['product_code'] = $args['product_code'];
    $myparams['d1'] = $args['d1'];
    $myparams['d2'] = $args['d2'];


    $procedure_params = array(
        array(&$myparams['product_code'], SQLSRV_PARAM_IN),
        array(&$myparams['d1'], SQLSRV_PARAM_IN),
        array(&$myparams['d2'], SQLSRV_PARAM_IN)
    );

    $sql = "EXEC view_prihod_for_productreport @product_code = ?, @d1 = ?, @d2=?";

    $stmt = sqlsrv_query($conn, $sql, $procedure_params);


    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $json[] = $row;

    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($stmt);
});

$app->get('/prihod/products/{prihod_code}', function (Request $request, Response $response, array $args) {

    $prihod_code = $args['prihod_code'];
    
    $db = new db();
    $conn = $db->connect();

    if ($conn) {
        $result = sqlsrv_query($conn, "select p.* from prihod_naklad p where p.prihod_code = '".$prihod_code."' order by p.prihod_naklad_code");
        if ($result === false) {
            die(print_r(sqlsrv_errors(), true));
        }

        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $json[] = $row;

        }
    } else {
        echo "Connection could not be established.\n";
        die(print_r(sqlsrv_errors(), true));
    }

    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);
    /* Close the connection. */
    $db->disconnect();
});

$app->get('/view_vkasse_all/', function (Request $request, Response $response, array $args) {

    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $result = sqlsrv_query($conn, "SELECT vkasse from view_vkasse");


    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $json[] = $row;

    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);

});

$app->get('/view_vkasse_kassaone/{cashdesk_code}', function (Request $request, Response $response, array $args) {

    $cashdesk_code = $args['cashdesk_code'];
    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $procedure_params = array(
        array(&$cashdesk_code, SQLSRV_PARAM_IN)
    );

    $sql = "EXEC dbo.view_vkasse_kassaone @cashdesk_code = ?";

    $result = sqlsrv_query($conn, $sql, $procedure_params);

    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $json[] = $row;

    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);

});

$app->get('/getPostavschiki/', function (Request $request, Response $response, array $args) {
    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $result = sqlsrv_query($conn, "select postav_code, postav_name from postavschiki order by 1-isactive , postav_name");


    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $json[] = $row;

    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);

});

$app->get('/getGroups/', function (Request $request, Response $response, array $args) {
    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }


    $result = sqlsrv_query($conn, "SELECT group_code, gname, parent_group_code FROM groups");


    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $json[] = $row;

    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
    sqlsrv_free_stmt($result);

});

$app->get('/product/all2/', function (Request $request, Response $response, array $args) {
    $json = file_get_contents(__DIR__ . '\api\product.json');
    echo json_encode($json, JSON_UNESCAPED_UNICODE);


});

$app->get('/getTaxType/', function (Request $request, Response $response, array $args) {
    $db = new db();
    $conn = $db->connect();
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    } else {
       
    $result = sqlsrv_query($conn, "SELECT tax_code, taxname FROM tax");


    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        $json[] = $row;

    }
    echo json_encode($json, JSON_UNESCAPED_UNICODE);
     
    }
    sqlsrv_free_stmt($result);
    $db->disconnect();

});

$app->run();
//exec dbo.view_kassa_dates_all_kassaall '2020-01-07 00:00:00','2020-01-08 00:00:00'
