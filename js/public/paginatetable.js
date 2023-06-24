function fill_data(data) {
	var row = $('<tr><td>' + item.Quote_id + '</td><td>' + item.Quote_date + '</td><td>' + item.Quote_status + '</td><td>' + item.Quote_total + '</td><td>' + item.Quote_action + '</td></tr>');
	return row;
}

var jsondata;
var currentPageIndex = 0;

jQuery(document).ready(function ($) {
	var jsondata;
	var stt = 0;
	var drawcnt = 1;
	var drawlnt = 10;
	var columns = [];

	if ($('th.woocommerce-orders-table__header-action').length > 0 &&
		$('th.woocommerce-orders-table__header-status').length > 0) {
		columns = [
			{ "data": "id" },
			{ "data": "date" },
			{ "data": "status" },
			{
				"data": "total",
				"orderable": false
			},
			{
				"data": "action",
				"orderable": false
			},
		];
	} else {
		columns = [
			{ "data": "id" },
			{ "data": "date" },
			{
				"data": "total",
				"orderable": false
			},
		];
	}

	var table = $('.footable').DataTable({
		// language: {
		// 	url: 'https://cdn.datatables.net/plug-ins/1.10.19/i18n/Hindi.json',
		// },
		"language": {
			"sEmptyTable": quoteupTableData.sEmptyTable,
			"sInfo": quoteupTableData.sInfo,
			"sInfoEmpty": quoteupTableData.sInfoEmpty,
			"sInfoFiltered": quoteupTableData.sInfoFiltered,
			"sInfoPostFix": quoteupTableData.sInfoPostFix,
			"sInfoThousands": quoteupTableData.sInfoThousands,
			"sLengthMenu": quoteupTableData.sLengthMenu,
			"sLoadingRecords": quoteupTableData.sLoadingRecords,
			"sProcessing": quoteupTableData.sProcessing,
			"sSearch": quoteupTableData.sSearch,
			"sZeroRecords": quoteupTableData.sZeroRecords,
			"oPaginate": {
				"sFirst": quoteupTableData.oPaginate.sFirst,
				"sLast": quoteupTableData.oPaginate.sLast,
				"sNext": quoteupTableData.oPaginate.sNext,
				"sPrevious": quoteupTableData.oPaginate.sPrevious,
			},
			"oAria": {
				"sSortAscending": quoteupTableData.oAria.sSortAscending,
				"sSortDescending": quoteupTableData.oAria.sSortDescending
			}
		},
		"responsive": true,
		"processing": true,
		"serverSide": true,
		"paging": true,
		"searching": true,
		"pageLength": 10,
		"columns": columns,
		// "scrollY"  : "36em",
		// "scrollCollapse": true,
		//    	"drawCallback": function( settings ) {
		//     alert( 'DataTables has redrawn the table' );
		// },

		"ajax": {
			url: quoteupTableData.ajax_url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'paginateQuote',
				start: getpageoffset(),
				search: $("input[type=search]").value

			}

		}
		// stt++;
	});
	// currentPageIndex=currentPageIndex+1;
	// var info=table.page.info();
	// console.log(info);

	function getpageoffset() {
		var text = $("current").val();
		return text;
	}



	$("input[type=search]").on("keyup", function () {
		search = this.value;
	});


});



	 // var ft = FooTable.init('#load-test', {
	 // 	columns:coldata,
		// rows:jsondata,
	 // });
	 // ft.loadRows(jsondata);



	// "columns": $.get('columns.json'),
	// "rows": $.get('rows.json')
	// var coldata=[
	// 				{"name":"Quote_id", "title":"Quote"},
	// 				{"name":"Quote_date","title":"Date"},
	// 				{"name":"Quote_status","title":"Status"},
	// 				{"name":"Quote_total","title":"Total"},
	// 				{"name":"Quote_action","name":"Action"}
	// 			];
	// var jsondata;
	// $.ajax({
 //    		url:ajax_vars.ajaxurl,
 //    		type:'POST',
 //    		data:{
 //    			action:'paginateQuote'
	// 		},
 //        	success: function (data) {
 //        		console.log(data);
 //        		jsondata=JSON.parse(data);
	//            	$.each(jsondata, function(index, item){
	// 	            var row = fill_data(item);
	// 	            $('.footable tbody').append(row);
	// 	        });

	// 			// $('.footable').footable({
	// 			// 	"columns": coldata,
	// 			// 	"rows": jsondata,
	// 			// 	"paging": {
	// 		 //                "enabled": true
	// 		 //              }
	// 			// });
	// 			// 


 //    	    }
 //    	    {
		// 	"url":ajax_vars.ajaxurl,
  //   		"type":'POST',

  //   		"data":{
  //   			"action":"paginateQuote",
  //   			"start":0,
  //   			"length":10,
  //   			"draw":1
		// 	},

  //       	"dataSrc": function (data) {
  //       		console.log(data);
  //       		jsondata=JSON.parse(data);
  //       		// return jsondata;
		// 	},
		// 	"dataSrc":jsondata
		// },
