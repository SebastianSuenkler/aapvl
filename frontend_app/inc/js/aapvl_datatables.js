
$.extend(true, $.fn.dataTable.defaults, {
    scrollY: '50vh',
    scrollCollapse: true,
    stateSave: true
});


// // Pipelining function for DataTables. To be used to the `ajax` option of DataTables
//
$.fn.dataTable.pipeline = function (opts) {
    // Configuration options
    var conf = $.extend({
        pages: 5, // number of pages to cache
        url: '', // script url
        data: null, // function or object with parameters to send to the server
        // matching how `ajax.data` works in DataTables
        method: 'GET'
    }, opts);

    // Private variables for storing the cache
    var cacheLower = -1;
    var cacheUpper = null;
    var cacheLastRequest = null;
    var cacheLastJson = null;

    return function (request, drawCallback, settings) {
        var ajax = false;
        var requestStart = request.start;
        var drawStart = request.start;
        var requestLength = request.length;
        var requestEnd = requestStart + requestLength;

        if (settings.clearCache) {
            // API requested that the cache be cleared
            ajax = true;
            settings.clearCache = false;
        } else if (cacheLower < 0 || requestStart < cacheLower || requestEnd > cacheUpper) {
            // outside cached data - need to make a request
            ajax = true;
        } else if (JSON.stringify(request.order) !== JSON.stringify(cacheLastRequest.order) ||
                JSON.stringify(request.columns) !== JSON.stringify(cacheLastRequest.columns) ||
                JSON.stringify(request.search) !== JSON.stringify(cacheLastRequest.search)
                ) {
            // properties changed (ordering, columns, searching)
            ajax = true;
        }

        // Store the request for checking next time around
        cacheLastRequest = $.extend(true, {}, request);

        if (ajax) {
            // Need data from the server
            if (requestStart < cacheLower) {
                requestStart = requestStart - (requestLength * (conf.pages - 1));

                if (requestStart < 0) {
                    requestStart = 0;
                }
            }

            cacheLower = requestStart;
            cacheUpper = requestStart + (requestLength * conf.pages);

            request.start = requestStart;
            request.length = requestLength * conf.pages;

            // Provide the same `data` options as DataTables.
            if ($.isFunction(conf.data)) {
                // As a function it is executed with the data object as an arg
                // for manipulation. If an object is returned, it is used as the
                // data object to submit
                var d = conf.data(request);
                if (d) {
                    $.extend(request, d);
                }
            } else if ($.isPlainObject(conf.data)) {
                // As an object, the data given extends the default
                $.extend(request, conf.data);
            }

            settings.jqXHR = $.ajax({
                "timeout": 60000,
                "type": conf.method,
                "url": conf.url,
                "data": request,
                "dataType": "json",
                "cache": false,
                "success": function (json) {
                    cacheLastJson = $.extend(true, {}, json);

                    if (cacheLower != drawStart) {
                        json.data.splice(0, drawStart - cacheLower);
                    }
                    json.data.splice(requestLength, json.data.length);

                    drawCallback(json);
                }
            });
        } else {
            json = $.extend(true, {}, cacheLastJson);
            json.draw = request.draw; // Update the echo for each response
            json.data.splice(0, requestStart - cacheLower);
            json.data.splice(requestLength, json.data.length);

            drawCallback(json);
        }
    }
};

// Register an API method that will empty the pipelined data, forcing an Ajax
// fetch on the next draw (i.e. `table.clearPipeline().draw()`)
$.fn.dataTable.Api.register('clearPipeline()', function () {
    return this.iterator('table', function (settings) {
        settings.clearCache = true;
    });
});/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function () {
    $('#aapvl_table_case01').dataTable({
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


$(document).ready(function () {
    $('#aapvl_table_grid_resources').dataTable({
        "processing": true,
        "serverSide": true,
        "ajax": $.fn.dataTable.pipeline({
            url: "http://localhost/aapvl/inc/datatables/server_processing.php",
            pages: 5, // number of pages to cache
        }),
        "columnDefs": [{
                "targets": 0,
                "searchable": false
            }],
        "bStateSave": true,
    });


});








  $(document).ready(function() {

    var options = [
    	"ja",
    	"nein",
    	"unklar"
    ];



    $('#aapvl_table_judgement_cases01').DataTable( {
      "bStateSave": true,
      "scrollCollapse": true,
      "scrollX": true,
     "columnDefs": [{
             "name":"pk_results",
             "targets": 0,
             "searchable": false

         },
         {
          "targets": [4,5,6,7,8,9,12,13],
         "className": "editable"
       },
       {
        "targets": [10],
        "render": function(d,t,r){
                  	var $select = $("<select></select>", {
                      	"id": r[0],
                          "value": d,
                          "name": "Shop"
                      });
                  	$.each(options, function(k,v){
                      	var $option = $("<option></option>", {
                          	"text": v,
                              "value": v
                          });
                          if(d === v){
                          	$option.attr("selected", "selected")
                          }
                      	$select.append($option);
                      });
                      return $select.prop("outerHTML");
                  },
       "className": "editable_select"
       },
       {
        "targets": [11],
        "render": function(d,t,r){
                  	var $select = $("<select></select>", {
                      	"id": r[0],
                          "value": d,
                          "name": "Lebensmittel-Shop"
                      });
                  	$.each(options, function(k,v){
                      	var $option = $("<option></option>", {
                          	"text": v,
                              "value": v
                          });
                          if(d === v){
                          	$option.attr("selected", "selected")
                          }
                      	$select.append($option);
                      });
                      return $select.prop("outerHTML");
                  },
       "className": "editable_select"
       },

       ],

       'createdRow': function(row, data, dataIndex){
             $('td:eq(0)', row).css('min-width', '40px');
             $('td:eq(1)', row).css('min-width', '80px');
             $('td:eq(2)', row).css('min-width', '200px').css('vertical-align', 'top');
             $('td:eq(3)', row).css('min-width', '200px').css('vertical-align', 'top');
             $('td:eq(4)', row).css('min-width', '80px').attr('name', 'Anbietername').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(5)', row).css('min-width', '80px').attr('name', 'Straße').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(6)', row).css('min-width', '80px').attr('name', 'PLZ').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(7)', row).css('min-width', '80px').attr('name', 'Ort').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(8)', row).css('min-width', '80px').attr('name', 'Bundesland').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(9)', row).css('min-width', '80px').attr('name', 'Kreis').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(10)', row).css('min-width', '80px').attr('name', 'Shop').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(11)', row).css('min-width', '80px').attr('name', 'Lebensmittel-Shop').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(12)', row).css('min-width', '80px').attr('name', 'Verdacht').attr('id', data[0]).css('vertical-align', 'top');
             $('td:eq(13)', row).css('min-width', '80px').attr('name', 'Rückmeldungen').attr('id', data[0]).css('vertical-align', 'top');
          },

        "serverSide": true,
        "bProcessing": true,
        "language": {
            processing: ''},

    //    ajax: "../server/server_processing.php",
        "ajax": $.fn.dataTable.pipeline({
            url: "../server/server_processing.php",
            pages: 5, // number of pages to cache
        }),
        "keys": true
    } );


  // Inline editing

  var oldValue = null;
  $(document).on('dblclick', '.editable', function(){
    oldValue = $(this).html();

    $(this).removeClass('editable');	// to stop from making repeated request

    $(this).html('<textarea class="update">'+ oldValue +'</textarea>');
    $(this).find('.update').focus();
  });

  var newValue = null;
  			$(document).on('blur', '.update', function(){
  				var elem    = $(this);
  				newValue 	= $(this).val();
  				var pk_results	= $(this).parent().attr('id');
  				var colName	= $(this).parent().attr('name');


  				if(newValue != oldValue)
  				{
  					$.ajax({
  						url : '../server/updateDataCase01.php',
  						method : 'post',
  						data :
  						{
  							pk_results: pk_results,
  							colName  : colName,
  							newValue : newValue,
  						},
  						success : function(respone)
  						{
  							$(elem).parent().addClass('editable');
  							$(elem).parent().html(newValue);
  						}
  					});
  				}
  				else
  				{
  					$(elem).parent().addClass('editable');
  					$(this).parent().html(newValue);
  				}
  			});

        var ValueSelect = null;
        $(document).on('change', '.editable_select', function(){

          ValueSelect = $("option:selected", this).val();
          pk_results = $(this).attr("id");
          colName = $(this).attr("name");

          $.ajax({
            url : '../server/updateDataCase01.php',
            method : 'post',
            data :
            {
              pk_results: pk_results,
              colName  : colName,
              newValue : ValueSelect,
            }	});

        });




});





$(document).ready(function () {
    var url = ""+location+"";
    var res = url.split("?");
    var redirect = ""+res[0]+"";

    $(".dataTables_length").append('&nbsp;&nbsp;<a class="btn btn-default btn-xs" role="button" href="'+redirect+'")><i class="fa fa-refresh fa-1x" aria-hidden="true" style="padding: 5px;"></i></a>');

});
