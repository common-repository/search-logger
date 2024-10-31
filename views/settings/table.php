<?php
use Codexpert\Plugin\Table;

global $wpdb;
$data = [];
$sql = "SELECT * FROM `{$wpdb->prefix}searches` WHERE 1 = 1";
if( isset( $_REQUEST['s'] ) && ( $keyword = sanitize_text_field( $_REQUEST['s'] ) ) != '' ) {
	$sql .= " AND `keyword` = '{$keyword}'";
}

$queries = $wpdb->get_results( $sql );

$time_format = get_option( 'links_updated_date_format' );
foreach ( $queries as $query ) {

	$filter	= '<a class="keyword-filter" href="' . add_query_arg( [ 'page' => 'search-logger', 's' => $query->keyword ] ) . '">' . __( 'Filter', 'search-logger' ) . '</a>';

	$view = '';
	if( $query->count > 0 ) {
		$view	= "<div class='result-wrap'>
		<button type='button' class='result-view' data-log_id='{$query->id}'>" . __( 'View', 'search-logger' ) . "</button>";
		$view	.= "<div class='result-section' id='log-{$query->id}' style='display: none'>";
		
		$view	.= '<ul>';
		foreach ( unserialize( $query->results ) as $result ) {
			$view .= '<li>' . get_the_title( $result ) . '</li>';	
		}
		$view	.= '</ul>';

		$view	.= "
			</div><!-- .result-section -->
		</div><!-- .result-wrap -->";
	}

	$data[] = [
		'keyword'	=> $query->keyword . $filter,
		'results'	=> sprintf( _n( '%d Result', '%d Results', $query->count, 'search-logger' ), $query->count ) . $view,
		'id'		=> $query->id,
		'timestamp'	=> date( $time_format, $query->timestamp ),
	];
}

$config = [
	'per_page'		=> 50,
	'columns'		=> [
		'keyword'	=> __( 'Keyword', 'search-logger' ),
		'results'	=> __( 'Results', 'search-logger' ),
		'id'		=> __( 'Log ID', 'search-logger' ),
		'timestamp'	=> __( 'Time', 'search-logger' ),
	],
	'sortable'		=> [ 'id', 'keyword', 'count', 'timestamp' ],
	'orderby'		=> 'id',
	'order'			=> 'desc',
	'data'			=> $data,
	'bulk_actions'	=> [
		'delete'	=> __( 'Delete', 'search-logger' ),
	],
];

$table = new Table( $config );
$table->prepare_items();
echo '
<form method="post" id="search-logger-form" action="">
	<input type="hidden" name="page" value="search-logger">';
	$table->search_box( 'Search', 'keyword' );
	$table->display();
echo '
</form>';