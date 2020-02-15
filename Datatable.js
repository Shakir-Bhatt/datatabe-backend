var plansTable;
$(function () {
    plansTable = $('#plansTable').DataTable({
        ajax: {
            url: APP_URL+"/plans",
            error: function(response){
            }
        },
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search"
        },

        drawCallback: function (data) {
            $('[data-toggle="popover"]').popover();
            $('[data-toggle="tooltip"]').tooltip();
        },
        "processing": true,
        "serverSide": true,
        "fixedHeader": true,
        "columnDefs":[{
            "sortable":false,
            "targets":[3,4,5]
        }],
        "order":[[2,"desc"]],

    });
});
