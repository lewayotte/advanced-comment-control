var advanced_comment_control_options = jQuery.noConflict();

advanced_comment_control_options(document).ready(function($) {
	
	/* Post Rules JS */
	$( '#advanced-comment-control-administrator-options' ).on( 'click', 'input#add-advanced-comment-control-post-rule', function( event ) {
		event.preventDefault();
        var data = {
            'action': 'advanced-comment-control-add-new-post-rule-row',
            'key': ++advanced_comment_control_last_post_rule_key
        }
        $.post( ajaxurl, data, function( response ) {
            $( 'table#advanced-comment-control-post-rules' ).append( response );
        });
	});
	
	$( '#advanced-comment-control-post-rules' ).on( 'change', 'select.advanced-comment-control-type-post-rule', function( event ) {
		event.preventDefault();
		var parent = $( this ).parents( 'tr' );
        var data = {
            'action': 'advanced-comment-control-change-type-rule-post-rule-row',
            'type': $( 'option:selected', this ).val(),
            'key': $( parent ).data( 'key' )
        }
        $.post( ajaxurl, data, function( response ) {
			$( '.advanced-comment-control-type-option-post-rule-options', parent ).html( response );
        });
	});
		
	$( '#advanced-comment-control-post-rules' ).on( 'click', '.delete-advanced-comment-control-post-rule, .delete-advanced-comment-control-role-rule', function ( event ) {
		event.preventDefault();
		var parent = $( this ).parents( 'tr' );
		parent.slideUp( 'normal', function() { $( this ).remove(); } );
	});
	
	/* Role Rules JS */
	$( '#advanced-comment-control-administrator-options' ).on( 'click', 'input#add-advanced-comment-control-role-rule', function( event ) {
		event.preventDefault();
        var data = {
            'action': 'advanced-comment-control-add-new-role-rule-row',
            'key': ++advanced_comment_control_last_role_rule_key
        }
        $.post( ajaxurl, data, function( response ) {
            $( 'table#advanced-comment-control-role-rules' ).append( response );
        });
	});
		
	$( '#advanced-comment-control-role-rules' ).on( 'click', '.delete-advanced-comment-control-role-rule', function ( event ) {
		event.preventDefault();
		var parent = $( this ).parents( 'tr' );
		parent.slideUp( 'normal', function() { $( this ).remove(); } );
	});
	
});