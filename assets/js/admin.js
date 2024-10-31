jQuery(function($){
	$('.result-view').click(function(e){
		e.preventDefault();
		var log_id = $(this).data('log_id');
		$('#log-'+log_id).slideToggle();
	})
})