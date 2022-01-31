$( 'table.tablesorter, table#wms-jquery-tablesorter' ).each( function () {
  var sortArgs = {};
  if ( $( this ).data( 'sortlist' ) ) {
    /* Table provides default sort instructions */
    sortArgs.sortList = eval( $( this ).data( 'sortlist' ) );
  } else {
    sortArgs.sortList = [[0, 0]]; /* default to first column ASC */
  }
  $( this ).tablesorter( sortArgs );
} );