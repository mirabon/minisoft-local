function addModalToIndex(prihod_code) {
    var id_table = "prihooood";
    var prihod_code = prihod_code;
    $("#modal").find("div").remove();
    $("#modal").append('<div id=\"test_modal\" class=\"modal fade bd-example-modal-lg\">' +
        '<div class="modal-dialog modal-lg">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>' +
        '<h4 class="modal-title">Накладна № '+ prihod_code +'</h4>' +
        '</div>' +
        '<div class="modal-body">' +
        '<table id="prihooood" class="table table-striped table-bordered" style="width:100%">' +
        '<thead>' +
        '<tr><th>Наименование</th>' +
        '<th>Код товара</th>' +
        '<th>Кол-во</th>' +
        '<th>Цена</th>' +
        '<th>Цена пост.</th>' +
        '<th>Наценка</th>' +
        '<th>Штрихкод</th></tr>' +
        '</thead>' +
        '<tbody></tbody>' +
        '<tfoot>' +
        '<tr><th>Наименование</th>' +
        '<th>Код товара</th>' +
        '<th>Кол-во</th>' +
        '<th>Цена</th>' +
        '<th>Цена пост.</th>' +
        '<th>Наценка</th>' +
        '<th>Штрихкод</th></tr>' +
        '</tfoot>' +
        '</table>' +
        '</div>' +
        '</div>' +
        '</div>');



    $('#test_modal').on('shown.bs.modal', function (event) {

        var button = $(event.relatedTarget) // Button that triggered the modal
        var recipient = button.data('whatever') // Extract info from data-* attributes
        // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
        // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
        var modal = $(this)
        loadProductOfPrihod(id_table, prihod_code);

    });

    $('#test_modal').modal('show');
}
