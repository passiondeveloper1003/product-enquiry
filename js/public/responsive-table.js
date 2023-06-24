// generate mobile version for each table
// Responsive script for approval-rejection view.
// Responsive script for enquiry cart product table
jQuery('.generated_for_desktop').each(function () {
        var table = jQuery(this); // cache table object
        var tableClass = table.attr('class');
        //Remove Generated for Desktop class
        tableClass = tableClass.replace("generated_for_desktop", "");
        var head = table.find('thead th');
        var rows = table.find('tbody tr').clone(); // appending afterwards does not break original table

        // create new table
        var newtable = jQuery(
            '<table class="generated_for_mobile ' + tableClass + '">' +
            '<colgroup>' +
            '<col span="1" style="width: 40%">' +
            '<col span="1" style="width: 60%;">' +
            '</colgroup>' +
            '  <tbody>' +
            '  </tbody>' +
            '</table>'
        );

        // cache tbody where we'll be adding data
        var newtable_tbody = newtable.find('tbody');

        rows.each(function (i) {
            var cols = jQuery(this).find('td');
            var classname = i % 2 ? 'even' : 'odd';
            cols.each(function (k) {
                var new_tr = jQuery('<tr class="' + classname + '"></tr>').appendTo(newtable_tbody);
                var $td    = jQuery(this);
                new_tr.append(head.clone().get(k));
                if ($td.hasClass('product-remove') || $td.hasClass('product-thumbnail')) {
                    $td.attr('colspan', '2');
                }
                new_tr.append($td);
            });
        });

        /**
        *   If it is a Approval Rejection Table, then show Total Row at the end
        */
    if (tableClass.indexOf('quoteup-quote-table') !== -1) {
        var lastRow = newtable_tbody.find("tr:last");
        var secondLastRow = lastRow.prev();
        var new_tr = jQuery('<tr class="' + lastRow.attr('class') + '"></tr>').appendTo(newtable_tbody);
        new_tr.append('<th>' + secondLastRow.find('td:last').text() + '</th>');
        new_tr.append('<td class="' + lastRow.find('td:last').attr('class') + '">' + lastRow.find('td:last').html() + '</td>');
        //Remove Unwanted Rows
        secondLastRow.remove();
        lastRow.remove();
    }

        jQuery(this).after(newtable);

});

/**
* Find the desktop table cell location on screen for every table cell on products table in quote cart.
* @param int index of the table cell row.
* @param int index of the table cell column.
*/
function findDesktopTableCellLocation($mobileTableRowNumber, $totalNoOfColumns)
{
    $temp = $mobileTableRowNumber - $totalNoOfColumns;
    if ($temp < 0) {
        $data = [];
        $data[0] = 0;
        $data[1] = $mobileTableRowNumber;
        return $data;
    } else {
        var $count = 1;
        while ($temp >= $totalNoOfColumns) {
            $temp = $temp - $totalNoOfColumns;
            $count++;
        }
        $data = [];
        $data[0] = $count;
        $data[1] = $temp;
        return $data;
    }
}

/**
* Find the mobile table cell location on screen for every table cell on products table in quote cart.
* @param int index of the table cell row.
* @param int index of the table cell column.
*/
function findMobileTableCellLocation($desktopTableRowNumber, $desktopTableColumnNumber, $totalNoOfColumns)
{
    $temp = ($desktopTableRowNumber * $totalNoOfColumns) +$desktopTableColumnNumber;
    $data = [];
    $data[0] = $temp;
    $data[1] = 1;
    return $data;
}

/**
* Returns the cell of the products table in quote cart
* @param string $selector element
* @param int $rowNumber row number of cell
* @param int $columnNumber column number of cell
*/
function selectCell($selector, $rowNumber, $columnNumber)
{
    var $table = jQuery($selector).find('tbody')[0];
    var $cell = $table.rows[$rowNumber].cells[$columnNumber]; // This is a DOM "TD" element
    return jQuery($cell); // Now it's a jQuery object.
}
