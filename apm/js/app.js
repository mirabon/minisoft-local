$(document).ready(function () {
    console.log('Ready');

    loadPostavshiki();
    loadProducts();
});


$("#load_prihod_product").click(function () {
    var label = $('#selected_product');
    var product_code = label.text();
    var date1 = $("#prihod_product_datetimepicker1").find("input").val();
    var date2 = $("#prihod_product_datetimepicker2").find("input").val();

    loadPrihodProducts(product_code, date1, date2);
    loadSalesProducts(product_code, date1, date2);
});

$('#product_info').click(function () {
    var label = $('#selected_product');
});

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    var target = $(e.target).attr("href") // activated tab
    if (target == '#kassa') {
        getVkasse();
        let i = 4
        while (i) {
            getVkasseOne(i);
            i--;
        }
        getVkasseOne(13);
    }
});



function getVkasse() {
    $.ajax({
        url: '/minisoft.local/api.php/view_vkasse_all/',
        dataType: "json",
        beforeSend: function () { // сoбытиe дo oтпрaвки
            $('#vkasse_all').find("div").remove();
            $("h4#vkasse_all").text("");
            $('#vkasse_all').append('<div class="progress">' +
                '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 100%; min-width:2%">' +
                '</div></div>');
        },
        success: function (data) {
            $('#vkasse_all').find("div").remove();
            var vkasse = data[0].vkasse;
            $("h4#vkasse_all").text(vkasse);
            console.info(vkasse);

        },
        error: function (xhr, ajaxOptions, thrownError) {
            //            $("#accounts").find("tbody").find("tr").remove();
            //            $("#accounts").find("tbody:last").append('<tr class="alert-danger"><td colspan="9">Ошибка получения данных</td></tr>');
            //            console.error("Error");
            //            //console.log(xhr.responseText);
            //            //console.log(thrownError);
        }
    });
}

function getVkasseOne(cashdesk_code) {

    $.ajax({
        url: '/minisoft.local/api.php/view_vkasse_kassaone/' + cashdesk_code,
        dataType: "json",
        beforeSend: function () { // сoбытиe дo oтпрaвки
            $('h5#vkasse_one_' + cashdesk_code).find("div").remove();
            $('h5#vkasse_one_' + cashdesk_code).text("");
            $('h5#vkasse_one_' + cashdesk_code).append('<div class="progress">' +
                '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 100%; min-width:2%">' +
                '</div></div>');
        },
        success: function (data) {
            $('h5#vkasse_one_' + cashdesk_code).find("div").remove();
            var svkasse = data[0].svkasse;
            $("h5#vkasse_one_" + cashdesk_code).text(svkasse);
            console.info(svkasse);

        },
        error: function (xhr, ajaxOptions, thrownError) {
            //            $("#accounts").find("tbody").find("tr").remove();
            //            $("#accounts").find("tbody:last").append('<tr class="alert-danger"><td colspan="9">Ошибка получения данных</td></tr>');
            //            console.error("Error");
            //            //console.log(xhr.responseText);
            //            //console.log(thrownError);
        }
    });
}

function loadPostavshiki() {

    $.ajax({
        url: '/minisoft.local/api.php/getPostavschiki/',
        dataType: "json",
        beforeSend: function () { // сoбытиe дo oтпрaвки
            //                        $("#example").find("tbody").find("tr").remove();
            //                        $("#example").find("tbody:last").append('<tr id="progressbar"><td colspan="9">' +
            //                            '<div class="progress">' +
            //                            '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 100%; min-width:2%">' +
            //                            '</div></div>' +
            //                            '</td></tr>');
        },
        success: function (data) {
            for (var n in data) {
                $('#selectPostavschiki').append('<option value="' + data[n].postav_code + '">' + data[n].postav_name + '</option>');

            }
            $('#selectPostavschiki').selectpicker();
        },
        error: function (xhr, ajaxOptions, thrownError) {
            //            $("#accounts").find("tbody").find("tr").remove();
            //            $("#accounts").find("tbody:last").append('<tr class="alert-danger"><td colspan="9">Ошибка получения данных</td></tr>');
            //            console.error("Error");
            //            //console.log(xhr.responseText);
            //            //console.log(thrownError);
        }
    });
}

function loadProducts() {

    $.ajax({
        url: '/minisoft.local/api.php/product/all/',
        dataType: "json",
        beforeSend: function () { // сoбытиe дo oтпрaвки
            $("#products").find("tbody").find("tr").remove();
            $("#products").find("tbody:last").append('<tr id="progressbar"><td colspan="9">' +
                '<div class="progress">' +
                '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 100%; min-width:2%">' +
                '</div></div>' +
                '</td></tr>');
        },
        success: function (data) {
             n();
            $('#products').find('tbody').find('tr#progressbar').remove();
            $('#products').DataTable({
                responsive: true,
                destroy: true,
                "processing": true,
                "pageLength": 100,
                data: data,

                columns: [
                    {
                        data: "product_code",
                        render: function (product_code, type, row, meta) {
                            return '' + product_code + ' <span style="cursor:pointer;" data-toggle="modal" data-target="#combo_modal" id="product_info" class="glyphicon glyphicon-info-sign" data-whatever="' + product_code + '" onclick="openModalPrihod_product()"></span>';
                        }
                    },
                    {
                        data: "pname"
                    },
                    {
                        data: "cena"
                    },
                    {
                        data: "kolvo"
                    },
                    {
                        data: "bcforsearch",
                        render: function (bcforsearch, type, row) {
                            var l = bcforsearch.length;
                            var cardnumberean_str = bcforsearch.substring(1, l - 1);
                            var cardnumberean_array = cardnumberean_str.split(',');
                            var cardnumberean_new_str = '';
                            for (let card of cardnumberean_array) {
                                cardnumberean_new_str += card + ',' + '<br />';
                            }
                            return cardnumberean_new_str;
                        }
                    },
                    {
                        data: "cenapost"
                    },
                    {
                        data: "plu"
                    },
                    {
                        data: "toprint",
                        render: function (toprint, type, row, meta) {
                            var product_code = row['product_code'];
                            if (toprint == 1) {
                                return '<input type="checkbox" name="' + product_code + '" onchange="InverseProductToPrint(this)" checked />';
                            } else {
                                return '<input type="checkbox" name="' + product_code + '"  onchange="InverseProductToPrint(this)"/>';
                            }

                        }
                    },
                    {
                        data: "isfiscal",
                        render: function (isfiscal, type, row, meta) {
                            var product_code = row['product_code'];
                            var tax_type = row['tax_type'];
                           
                            if (isfiscal == 1) {
                                
                                return '<input type="checkbox" name="' + product_code + '" onchange="InverseProductToIsfiscal(this)" checked /> - [' + tax_type + ']' ;
                            } else {
                                return '<input type="checkbox" name="' + product_code + '"  onchange="InverseProductToIsfiscal(this)"/>- [' + tax_type + ']';
                            }
                        }
                    }
                ]
            });
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $("#products").find("tbody").find("tr").remove();
            $("#products").find("tbody:last").append('<tr class="alert-danger"><td colspan="9">Ошибка получения данных</td></tr>');
            console.error("Error");
            console.error(xhr.responseText);
            console.error(thrownError);
        }
    });
}

function InverseProductToPrint(obj) {

    var product_code = $(obj).attr("name");
    $.ajax({
        url: '/minisoft.local/api.php/product/inverse_product_to_print/' + product_code,
        beforeSend: function () {},
        success: function (data) {

        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.error("Error");
            console.error(xhr.responseText);
            console.error(thrownError);
        }
    });


}

function InverseProductToIsfiscal(obj) {

    var product_code = $(obj).attr("name");
    $.ajax({
        url: '/minisoft.local/api.php/product/inverse_product_to_isfiscal/' + product_code,
        beforeSend: function () {},
        success: function (data) {

        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.error("Error");
            console.error(xhr.responseText);
            console.error(thrownError);
        }
    });


}

function loadProductOfPrihod(id_table, prihod_code) {

    $.ajax({
        url: '/minisoft.local/api.php/prihod/products/' + prihod_code,
        dataType: "json",
        beforeSend: function () { // сoбытиe дo oтпрaвки
            $('#' + id_table).find("tbody").find("tr").remove();
            $('#' + id_table).find("tbody:last").append('<tr id="progressbar"><td colspan="9">' +
                '<div class="progress">' +
                '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 100%; min-width:2%">' +
                '</div></div>' +
                '</td></tr>');
        },
        success: function (data) {
            $('#' + id_table).find('tbody').find('tr#progressbar').remove();
            $('#' + id_table).DataTable({
                responsive: true,
                destroy: true,
                "processing": true,
                "pageLength": 100,
                data: data,

                columns: [
                    {
                        data: "pname"
                    },
                    {
                        data: "product_code",
                        render: function (product_code, type, row, meta) {
                            return '' + product_code + '<span style="cursor:pointer;" data-toggle="modal" data-target="#combo_modal" id="product_info" class="glyphicon glyphicon-info-sign" data-whatever="' + product_code + '" onclick="openModalPrihod_product()"></span>';
                        }
                    },

                    {
                        data: "kol"
                    },
                    {
                        data: "cena"
                    },
                    {
                        data: "cenapost"
                    },
                    {
                        data: "nacenka"
                    },
                    {
                        data: "cardnumberean"
                    }


                ]

            });
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $('#' + id_table).find("tbody").find("tr").remove();
            $('#' + id_table).find("tbody:last").append('<tr class="alert-danger"><td colspan="9">Ошибка получения данных</td></tr>');
            console.error("Error");
            //console.log(xhr.responseText);
            //console.log(thrownError);
        }
    });
}

function loadSalesProducts(data, date1, date2) {
    //$("#sales_product_modal").modal('show');
    $.ajax({
        url: '/minisoft.local/api.php/sales/product/' + data + '/' + date1 + '/' + date2,
        dataType: "json",
        beforeSend: function () { // сoбытиe дo oтпрaвки
            $("#sales_product").find("tbody").find("tr").remove();
            $("#sales_product").find("tbody:last").append('<tr id="progressbar"><td colspan="9">' +
                '<div class="progress">' +
                '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 100%; min-width:2%">' +
                '</div></div>' +
                '</td></tr>');
        },
        success: function (data) {
            $('#sales_product').find('tbody').find('tr#progressbar').remove();
            $('#sales_product').DataTable({
                responsive: true,
                destroy: true,
                "processing": true,
                "pageLength": 10,
                data: data,

                columns: [
                    {
                        data: "colvo"
                    },
                    {
                        data: "date"
                    }
                ]

            });
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $("#sales_product").find("tbody").find("tr").remove();
            $("#sales_product").find("tbody:last").append('<tr class="alert-danger"><td colspan="9">Ошибка получения данных</td></tr>');
            console.error("Error");
            //console.log(xhr.responseText);
            //console.log(thrownError);
        }
    });
}

function openModalPrihod_product() {
    $('#combo_modal').on('show.bs.modal', function (event) {
        $('#prihod_product_datetimepicker1').datetimepicker({
            locale: 'ru',
            stepping: 10,
            format: 'YYYY-MM-DD',
            daysOfWeekDisabled: [0, 6]
        });
        $('#prihod_product_datetimepicker2').datetimepicker({
            locale: 'ru',
            stepping: 10,
            format: 'YYYY-MM-DD',
            daysOfWeekDisabled: [0, 6]
        });
        var button = $(event.relatedTarget) // Button that triggered the modal
        var recipient = button.data('whatever') // Extract info from data-* attributes
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this)
        modal.find('#selected_product').text(recipient)
    })
}

function loadPrihodProducts(data, date1, date2) {

    $.ajax({
        url: '/minisoft.local/api.php/prihod_for_product/' + data + '/' + date1 + '/' + date2,
        dataType: "json",
        beforeSend: function () { // сoбытиe дo oтпрaвки
            $("#prihod_product_table").find("tbody").find("tr").remove();
            $("#prihod_product_table").find("tbody:last").append('<tr id="progressbar"><td colspan="11">' +
                '<div class="progress">' +
                '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 100%; min-width:2%">' +
                '</div></div>' +
                '</td></tr>');
        },
        success: function (data) {
            $('#prihod_product_table').find('tbody').find('tr#progressbar').remove();
            $('#prihod_product_table').DataTable({
                responsive: true,
                destroy: true,
                "processing": true,
                "pageLength": 10,
                data: data,

                columns: [
                    {
                        data: "prihod_code",
                        render: function (prihod_code, type, row) {
                            return '<span style="cursor:pointer;" data-toggle="modal" data-target="#prihod_naklad_view_modal" id="prihod_naklad_view" data-whatever="' + prihod_code + '" onclick="addModalToIndex(' + prihod_code + ')">' + prihod_code + '</span>';
                        }
                    },
                    {
                        data: "prihoddate",
                        render: function (prihoddate, type, row) {
                            return prihoddate.date.substring(0, 10);
                        }
                    },
                    {
                        data: "cenapost"
                    },
                    {
                        data: "sumkol"
                    },
                    {
                        data: "stype"
                    },
                    {
                        data: "postav_name"
                    }
                ],
                "footerCallback": function (row, data, start, end, display) {
                    var api = this.api(),
                        data;

                    // Remove the formatting to get integer data for summation
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                    };

                    // Total over all pages
                    total = api
                        .column(3)
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Total over this page
                    pageTotal = api
                        .column(3, {
                            page: 'current'
                        })
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // Update footer
                    $(api.column(3).footer()).html(
                        '' + pageTotal + ' ед.'
                    );
                }

            });
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $("#prihod_product_table").find("tbody").find("tr").remove();
            $("#prihod_product_table").find("tbody:last").append('<tr class="alert-danger"><td colspan="11">Ошибка получения данных</td></tr>');
            console.error("Error");
            //console.log(xhr.responseText);
            //console.log(thrownError);
        }
    });
}

function execute() {
    var date1 = $("#datetimepicker1").find("input").val();
    console.log('date1:' + date1);
    var date2 = $("#datetimepicker2").find("input").val();
    console.log('date2:' + date2);
    var selectPostavschiki = $("#selectPostavschiki").val();
    console.log('selectPostavschiki:' + selectPostavschiki);

    $.ajax({
        url: '/minisoft.local/api.php/supplier_report/' + date1 + '/' + date2 + '/' + selectPostavschiki,
        dataType: "json",
        beforeSend: function () { // сoбытиe дo oтпрaвки
            $("#example").find("tbody").find("tr").remove();
            $("#example").find("tbody:last").append('<tr id="progressbar"><td colspan="12">' +
                '<div class="progress">' +
                '<div class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" style="width: 100%; min-width:2%">' +
                '</div></div>' +
                '</td></tr>');
        },
        success: function (data) {
            $('#example').find('tbody').find('tr#progressbar').remove();
            $('#example').DataTable({
                responsive: true,
                destroy: true,
                "processing": true,
                "pageLength": 50,
                data: data,
                columns: [
                    {
                        data: "product_code",
                        render: function (product_code, type, row, meta) {
                            return '' + product_code + ' <span style="cursor:pointer;" data-toggle="modal" data-target="#combo_modal" id="product_info" class="glyphicon glyphicon-info-sign" data-whatever="' + product_code + '" onclick="openModalPrihod_product()"></span>';
                        }
                    },
                    {
                        data: "pname"
                    },
                    {
                        data: "prihod_period"
                    },
                    {
                        data: "sales_period"
                    },
                    {
                        data: "ostatok"
                    },
                    {
                        data: "zakaz"
                    },
                    {
                        data: "cena_rozn"
                    },
                    {
                        data: "cena_post"
                    },
                    {
                        data: "sales_summ"
                    },
                    {
                        data: "dohod"
                    },
                    {
                        data: "barcodes"
                    }
                ]

            });
        },
        error: function (xhr, ajaxOptions, thrownError) {
            $("#example").find("tbody").find("tr").remove();
            $("#example").find("tbody:last").append('<tr class="alert-danger"><td colspan="9">Ошибка получения данных</td></tr>');
            console.error("Error");
            //console.log(xhr.responseText);
            //console.log(thrownError);
        }
    });
}

function n(){
    
    $.ajax({
        url: '/minisoft.local/api.php/getTaxType/',
        dataType: "json",
        beforeSend: function () { 
        },
        success: function (data) {
            //<select class="form-control" id="selectPostavschiki" data-live-search="true">
            //                                <option value="">-- Select --</option>
            //                            </select>
            for (var n in data) {
                $('#selectPostavschiki').append('<option value="' + data[n].postav_code + '">' + data[n].postav_name + '</option>');

            }
            $('#selectPostavschiki').selectpicker();
        },
        error: function (xhr, ajaxOptions, thrownError) {
            //            $("#accounts").find("tbody").find("tr").remove();
            //            $("#accounts").find("tbody:last").append('<tr class="alert-danger"><td colspan="9">Ошибка получения данных</td></tr>');
            //            console.error("Error");
            //            //console.log(xhr.responseText);
            //            //console.log(thrownError);
        }
    });
}