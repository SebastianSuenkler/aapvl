function redirect_export(table) {
  window.open("../export_results/"+table);
  return false;
}


var json = JSON.parse(php_language);


$("#date_queries_warning").hide();
$("#interval_completion_queries_warning").hide();

$("#date_urls_warning").hide();
$("#interval_completion_urls_warning").hide();


function unique_array(array) {
    return array.filter(function (el, index, arr) {
        return index === arr.indexOf(el);
    });
}



$.ajaxSetup({cache: true, timeout: 10000});

cs = setTimeout(cs_interval, 5000);

function cs_interval()
{
    //$.get("http://localhost/aapvl/jobs/SaveResources/29");
    //cs = setTimeout(cs_interval, 5000);
}



$("#status").fadeOut(3000, function () {
    $("#aapvl_status").hide();
});



$("#edit_case01").click(function () {
    $("input").prop('disabled', false);
    $("textarea").prop('disabled', false);
    $(".selectpicker").prop('disabled', false);
    $("button").prop('disabled', false);
    $("#edit_case01").prop('disabled', true);
});

$("#edit_case02").click(function () {
    $("input").prop('disabled', false);
    $("textarea").prop('disabled', false);
    $(".selectpicker").prop('disabled', false);
    $("button").prop('disabled', false);
    $("#edit_case02").prop('disabled', true);
});

$("#edit_query").click(function () {
    $("input").prop('disabled', false);
    $(".selectpicker").prop('disabled', false);
    $("button").prop('disabled', false);
    $("#edit_query").prop('disabled', true);
});


$("#edit_urls").click(function () {
    $("input").prop('disabled', false);
    $("textarea").prop('disabled', false);
    $(".selectpicker").prop('disabled', false);
    $("button").prop('disabled', false);
    $("#edit_urls").prop('disabled', true);
});

$("#extend_queries").prop('disabled', true);



$("#queries_query").on('input', function () {
    if ($('#queries_query').val()) {
        $("#generate_queries").prop('disabled', true);
        $("#extend_queries").prop('disabled', false);

    } else {
        $("#generate_queries").prop('disabled', false);

    }
});



$("#generate_queries").click(function () {
    $("#generate_queries").prop('disabled', true);
    $("#extend_queries").prop('disabled', false);
    var queries = $("#generate_queries").val();
    $('#queries_query').val(queries); //this puts the textarea for the id labeled 'area'
    return false;
});


$("#extend_queries").click(function () {
    if (($("#queries_query").val().length > 0)) {
        $("#generate_queries").prop('disabled', true);
        var queries = $("#queries_query").val();
        var split_queries = queries.split('\n');
        var extend = $("#extend_queries").val();
        var split_extend = extend.split('\n');
        var extended_query = new Array();
        var extension = '';
        var extension_counter = '';
        var rm = new Array();
        var extended_queries = new Array();


        $.each(split_queries, function (index, value) {

            var query = $.trim(value);
            if (query) {
                extended_query.push(query);

                $.each(split_extend, function (index, value) {

                    extension = $.trim(value);
                    extension = $.trim(query + ' ' + extension);
                    extended_query.push(extension);


                });

            }

        });



        extended_query = unique_array(extended_query);


        extended_queries = extended_query.join('\n');

        $('#queries_query').val(extended_queries); //this puts the textarea for the id labeled 'area'
        $("#extend_queries").prop('disabled', true);
        return false;
    }



});

/* Method to toggle between adding and removing the "responsive" class to topnav when the user clicks on the icon */
function showIcon() {
    var x = document.getElementById("AAPVLTopNav");
    if (x.className === "topnav") {
        x.className += " responsive";
    } else {
        x.className = "topnav";

    }
}

$(function () {
    // this will get the full URL at the address bar
    var url = window.location.href;

    if (url.search("dashboard") > 0) {

        var type = "dashboard";

    }

    if (url.search("case01") > 0) {

        var type = "case01";

    }

    if (url.search("case02") > 0) {

        var type = "case02";

    }

    if (url.search("case03") > 0) {

        var type = "case03";

    }

    if (url.search("case04") > 0) {

        var type = "case04";

    }

    if (url.search("report") > 0) {

        var type = "report";

    }

    if (url.search("admin") > 0) {

        var type = "admin";

    }


    // passes on every "a" tag
    $(".topnav li a").each(function () {
        href = this.href;

        // checks if its the same on the address bar
        if (href.indexOf(type) > 0) {
            $(this).closest("a").css('background-color', '#777');

        }
    });
});



$(function ()
{
    $("#date_case01").datepicker
            ({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });
});

$(function ()
{
    $("#date_case02").datepicker
            ({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });
});

$(function ()
{
    $("#date_queries").datepicker
            ({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });
});


$(function ()
{
    $("#interval_completion_queries").datepicker
            ({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });
});


$(function ()
{
    $("#date_urls").datepicker
            ({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });
});

$(function ()
{
    $("#interval_completion_urls").datepicker
            ({
                dateFormat: "yy-mm-dd",
                changeMonth: true,
                changeYear: true
            });
});

$("#save_queries").click(function () {

    var cur_date = $.datepicker.formatDate('yy-mm-dd', new Date());
    var date_queries = $("#date_queries").val();
    var interval_days_queries = $("#interval_days_queries").val();
    var interval_completion_queries = $("#interval_completion_queries").val();

    if (date_queries < cur_date) {

        $("#date_queries_warning").show();

        $("#date_queries_warning").click(function () {
            $("#date_queries_warning").hide();
        });

        $("#date_queries").click(function () {
            $("#date_queries_warning").hide();
        });

        return false;

    }

    if (interval_days_queries > 0) {

        if (!interval_completion_queries || interval_completion_queries < date_queries) {

            $("#interval_completion_queries_warning").show();
            $("#interval_completion_queries_warning").click(function () {
                $("#interval_completion_queries_warning").hide();
            });
            $("#interval_completion_queries").click(function () {
                $("#interval_completion_queries_warning").hide();
            });
            return false;
        }


    }

    if (interval_completion_queries && interval_days_queries == 0) {
        $("#interval_completion_queries_warning").show();
        $("#interval_completion_queries_warning").click(function () {
            $("#interval_completion_queries_warning").hide();
        });
        $("#interval_completion_queries_warning").click(function () {
            $("#interval_completion_queries_warning").hide();
        });
        return false;
    }

});

$("#save_urls").click(function () {

    var cur_date = $.datepicker.formatDate('yy-mm-dd', new Date());
    var date_urls = $("#date_urls").val();
    var interval_days_urls = $("#interval_days_urls").val();
    var interval_completion_urls = $("#interval_completion_urls").val();

    if (date_urls < cur_date) {

        $("#date_urls_warning").show();

        $("#date_urls_warning").click(function () {
            $("#date_urls_warning").hide();
        });

        $("#date_urls").click(function () {
            $("#date_urls_warning").hide();
        });

        return false;

    }

    if (interval_days_urls > 0) {

        if (!interval_completion_urls || interval_completion_urls < date_urls) {

            $("#interval_completion_urls_warning").show();
            $("#interval_completion_urls_warning").click(function () {
                $("#interval_completion_urls_warning").hide();
            });
            $("#interval_completion_urls").click(function () {
                $("#interval_completion_urls_warning").hide();
            });
            return false;
        }

    }

    if (interval_completion_urls && interval_days_urls == 0) {
        $("#interval_completion_urls_warning").show();
        $("#interval_completion_urls_warning").click(function () {
            $("#interval_completion_urls_warning").hide();
        });
        $("#interval_completion_urls").click(function () {
            $("#interval_completion_urls_warning").hide();
        });
        return false;
    }
});


// Fortschritte


$(document).ready(function () {


    $("#progress").hide();

    var url = window.location.href;



    if (url.search("queries") > 0) {



    } else if (url.search("urls") > 0) {



    }

    else if (url.search("judgement") > 0) {



    }

    else if (url.search("dashboard") > 0) {



    }

    else if (url.search("admin") > 0) {



    }


    else {


        progress_interval = setTimeout(progress, 0);
    }




});



function progress() {
    var url = window.location.href;


    if (url.search('status') > 0) {
        var stSplit = url.split("?");

        url = stSplit[0];

    }


    var id = url.match(/\d+$/)[0];



    $("#progress").load("../../jobs/check_progress/" + id);




    progress_interval = setTimeout(progress, 60000);


}


$(document).ready(function () {
    $('#aapvl_table_config').dataTable({
        columnDefs: [
            {targets: [6], orderable: false},
            {targets: [6], searchable: false}
        ]
    });


});

$(document).ready(function () {
    $('#aapvl_table_case01').dataTable({
        columnDefs: [
            {targets: [1, 3], orderable: false},
            {targets: [2, 3], searchable: false}
        ]
    });


});

$(document).ready(function () {
    $('#aapvl_table_case02').dataTable({
        columnDefs: [
            {targets: [1, 3], orderable: false},
            {targets: [2, 3], searchable: false}
        ],
        "order": [[ 5, "desc" ]]
    });


});

$(document).ready(function () {
    $('#aapvl_table_case04').dataTable({
        columnDefs: [
            {targets: [1, 3], orderable: false},
            {targets: [2, 3], searchable: false}
        ]
    });


});



$(document).ready(function () {
    $('#aapvl_table_queries').dataTable({
        columnDefs: [
            {targets: [2], orderable: false},
            {targets: [2, 3], searchable: false}
        ]
    });


});


$(document).ready(function () {
    $('#aapvl_table_urls').dataTable({
        columnDefs: [
            {targets: [2], orderable: false},
            {targets: [2, 3], searchable: false}
        ]
    });


});



/*
 // Ajax API
 $("#save_urls").click(function () {
 var urls = $("#elements_urls").val();
 var response = $.ajax({
 method: "POST",
 url: "../../../../helper/ajax_api.php",
 data: {action: "Normalize_URLs", elements: urls}
 })
 .done(function (msg) {
 var urls = $("#elements_urls").val(msg);
 });

 return false;
 });

 });*/
