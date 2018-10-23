$(function() {
    var filter = $('#list-filter');

    $('select', filter).on('change', function () {
        filter.submit();
    });

    tableCellAutoupdate();
});

function tableCellAutoupdate() {

    var updateCell = function(el, module, section) {
        $.ajax({
            url: '/admin/' + module + '/' + section + '/update-table-cell/',
            method: 'post',
            data: {
                id: el.closest('.tb-row').data('id'),
                field: el.closest('.tb-cell').data('field'),
                value: el.val(),
            },
            success: function () {

            }
        });
    };

    $('.table-list').each(function () {
        var table = $(this);
        var module = table.data('module');
        var section = table.data('section');

        $('select', $('.tb-cell', table)).on('change', function () {
            var el = $(this);
            updateCell(el, module, section);
        });

        var timer = null;
        $('input, textarea', $('.tb-cell', table)).on('keyup', function () {
            var el = $(this);
            clearTimeout(timer);
            timer = setTimeout(function() {
                updateCell(el, module, section)
            }, 300);
        });
    });
}

function dataTableAction(tableId, module, section){
    var table = $('.table-list[data-id="' + tableId + '"]');

    var removeForm = $('.popup-delete', table);
    removeForm.fancybox({
        closeBtn: false,
        minHeight: 30
    });

    $('.yes', removeForm).click(function(){
        $.fancybox.close();
        var id = $(this).attr('data-id');

        $.ajax({
            url: '/admin/' + module + '/' + section + '/delete/',
            data: {
                id: id
            },
            success: function(resp) {
                $('.rowset .tb-row[data-id="' + id + '"]', table).fadeOut(200);
            },
            dataType: 'json',
            type: 'post'
        });

        return false;
    });

    $('.no', removeForm).click(function(){
        $.fancybox.close();
    });

    $('.tbl-btn-remove', table).click(function(){
        $('.yes', removeForm).attr('data-id', $(this).attr('data-id'));
        removeForm.eq(0).trigger("click");
    });

    $('.tbl-btn-remove', table).click(function(){
        $('#popup-delete .btn-remove').attr('data-id', $(this).attr('data-id'));
    });

    $('.btn-submit').on('click', function() {
        $(this).closest('form').submit();
        return false;
    });

    $('input, select', '.list-form').on('change', function() {
        $(this).closest('form').submit();
    });
}