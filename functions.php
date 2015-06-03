<?php

if ( !function_exists( 'build_advanced_comment_control_post_rule_row' ) ) {

	function build_advanced_comment_control_post_rule_row( $post_rule=array(), $row_key ) {
	
		$default_post_rule = array(
			'content_type' 	=> 'comments',
			'post_type' 	=> 'post',
			'type' 			=> 'age',
			'time' 			=> '6',
			'unit' 			=> 'month',
		);
		$post_rule = wp_parse_args( $post_rule, $default_post_rule );
    	
		$return  = '<tr data-key="' . $row_key . '">';		
		$return .= '<td>';
		
		$return .= '<div class="advanced-comment-control-post-type-post-rule-options">';
		$content_types  = '<select class="advanced-comment-control-post-type-post-rule" name="post_rules[' . $row_key .'][content_type]">';
		$content_types .= '<option value="comments" ' . selected( 'comments', $post_rule['content_type'], false ) . '>' . __( 'Comments', 'advanced-comment-control' ) . '</option>';
		$content_types .= '<option value="pings" ' . selected( 'pings', $post_rule['content_type'], false ) . '>' . __( 'Pings/Trackbacks', 'advanced-comment-control' ) . '</option>';
		$content_types .= '</select>';
	    $return .= sprintf( __( 'Disable %s on', 'advanced-comment-control' ), $content_types );
	    $return .= '&nbsp;';
		$return .= '<select class="advanced-comment-control-post-type-post-rule" name="post_rules[' . $row_key .'][post_type]">';
		$hidden_post_types = array( 'attachment', 'revision', 'nav_menu_item' );
		$post_types = get_post_types( array(), 'objects' );
		foreach ( $post_types as $post_type_obj ) {
		
			//No sense in showing these post types
			if ( in_array( $post_type_obj->name, $hidden_post_types ) ) 
				continue;
				
			//If the post type doesn't support comments, don't add the option
			if ( !post_type_supports( $post_type_obj->name, 'comments' ) ) 
				continue;
							
			$return .= '<option value="' . $post_type_obj->name . '" ' . selected( $post_type_obj->name, $post_rule['post_type'], false ) . '>' . strtolower( $post_type_obj->labels->name ) . '</option>';
		
		}
		$return .= '</select> ';
		$return .= '&nbsp;';
		$return .= '</div>';
		
		$return .= '<div class="advanced-comment-control-type-post-rule-options">';
		$return .= '<select class="advanced-comment-control-type-post-rule" name="post_rules[' . $row_key .'][type]">';
		$types = array(
			'age' 	=> __( 'older than', 'advanced-comment-control' ),
			'limit' => __( 'with more than', 'advanced-comment-control' ),
		);
		foreach ( $types as $key => $string ) {
					
			$return .= '<option value="' . $key . '" ' . selected( $key, $post_rule['type'], false ) . '>' . $string . '</option>';
		
		}
		$return .= '</select> ';
		$return .= '&nbsp;';
		$return .= '</div>';
		
		$return .= '<div class="advanced-comment-control-type-option-post-rule-options">';
		switch ( $post_rule['type'] ) {
			
			case 'age':
				$return .= advanced_comment_control_age_rules_post_rule( $post_rule, $row_key );
				break;
				
			case 'limit':
				$return .= advanced_comment_control_limit_rules_post_rule( $post_rule, $row_key );
				break;
			
		}
		$return .= '</div>';

		$return .= '<span class="delete-x delete-advanced-comment-control-post-rule">&times;</span>';
		
		$return .= '</td>';
		$return .= '</tr>';
		
		return $return;
		
	}
	
}

if ( !function_exists( 'build_advanced_comment_control_add_new_post_rule_row_ajax' ) ) {

	/**
	 * AJAX Wrapper
	 *
	 * @since 1.0.0
	 */
	function build_advanced_comment_control_add_new_post_rule_row_ajax() {
	
		die( build_advanced_comment_control_post_rule_row( array(), $_REQUEST['key'] ) );
		
	}
	add_action( 'wp_ajax_advanced-comment-control-add-new-post-rule-row', 'build_advanced_comment_control_add_new_post_rule_row_ajax' );
	
}

if ( !function_exists( 'build_advanced_comment_control_update_type_rules_post_rule_ajax' ) ) {

	/**
	 * AJAX Wrapper
	 *
	 * @since 1.0.0
	 */
	function build_advanced_comment_control_update_type_rules_post_rule_ajax() {
	
		$return = '';
				
		if ( !empty( $_REQUEST['type'] ) && isset( $_REQUEST['key'] )) {
			
			switch ( $_REQUEST['type'] ) {
				
				case 'age':
					$return = advanced_comment_control_age_rules_post_rule( array(), $_REQUEST['key'] );
					break;
					
				case 'limit':
					$return = advanced_comment_control_limit_rules_post_rule( array(), $_REQUEST['key'] );
					break;
				
			}
			
		}
	
		die( $return );
		
	}
	add_action( 'wp_ajax_advanced-comment-control-change-type-rule-post-rule-row', 'build_advanced_comment_control_update_type_rules_post_rule_ajax' );
	
}

if ( !function_exists( 'advanced_comment_control_age_rules_post_rule' ) ) {
	
	function advanced_comment_control_age_rules_post_rule( $rule=array(), $row_key ) {
		
		if ( empty( $rule ) ) {
			$rule = array( 
				'time' => '6',
				'unit' => 'month',
			);
		}
		
		$return  = '<input class="advanced-comment-control-time-rule small-text" type="text" name="post_rules[' . $row_key .'][time]" value="' . $rule['time'] . '" />';
		$return .= '&nbsp;';
		
		$return .= '<select class="advanced-comment-control-unit-rule" name="post_rules[' . $row_key .'][unit]">';
		$units = array(
			'day' 	=> __( 'day(s)', 'advanced-comment-control' ),
			'week' 	=> __( 'week(s)', 'advanced-comment-control' ),
			'month' => __( 'month(s)', 'advanced-comment-control' ),
			'year' 	=> __( 'year(s)', 'advanced-comment-control' ),
		);
		foreach ( $units as $key => $string ) {
					
			$return .= '<option value="' . $key . '" ' . selected( $key, $rule['unit'], false ) . '>' . $string . '</option>';
		
		}
		$return .= '</select> ';
		
		return $return;
		
	}
	
}

if ( !function_exists( 'advanced_comment_control_limit_rules_post_rule' ) ) {
	
	function advanced_comment_control_limit_rules_post_rule( $rule=array(), $row_key ) {
		
		if ( empty( $rule ) ) {
			$rule = array( 
				'limit' => '100',
			);
		}
	
		$return  = '<input class="advanced-comment-control-limit-rule small-text" type="text" name="post_rules[' . $row_key .'][limit]" value="' . $rule['limit'] . '" />';
		$return .= '&nbsp;';
		$return .= __( 'comment(s)', 'advanced-comment-control' );
		
		return $return;
		
	}
	
}

if ( !function_exists( 'build_advanced_comment_control_role_rule_row' ) ) {

	function build_advanced_comment_control_role_rule_row( $role_rule=array(), $row_key ) {
	
		if ( empty( $role_rule ) ) {
			$role_rule = array(
				'role' 		=> 'administrator',
				'type' 		=> 'always',
				'post_type' => 'post',
			);
		}
    	
		$return  = '<tr data-key="' . $row_key . '">';		
		$return .= '<td>';
		
		$return .= '<div class="advanced-comment-control-role-type-role-rules">';
		$return .= '<select class="advanced-comment-control-role-type-role-rule" name="role_rules[' . $row_key .'][role]">';
		
	    $editable_roles = array_reverse( get_editable_roles() );
	
	    foreach ( $editable_roles as $role => $details ) {
	        $name = translate_user_role( $details['name'] );
	        $return .= '<option value="' . esc_attr( $role ) . '" ' . selected( $role, $role_rule['role'], false ) . '>' . $name . '</option>';
	    }
		$return .= '<option value="loggedin" ' . selected( 'loggedin', $role_rule['role'], false ) . '>' . __( 'Logged in', 'advanced-comment-control' ) . '</option>';
		$return .= '<option value="loggedout" ' . selected( 'loggedout', $role_rule['role'], false ) . '>' . __( 'Logged out', 'advanced-comment-control' ) . '</option>';
		$return .= '</select> ';
		$return .= '&nbsp;';
		$return .= '</div>';
		
		$return .= '<div class="advanced-comment-control-type-role-rule-options">';
	    $return .= __( 'users can', 'advanced-comment-control' );
		$return .= '&nbsp;';
		$return .= '<select class="advanced-comment-control-type-role-rule" name="role_rules[' . $row_key .'][type]">';
		$types = array(
			'always' 	=> __( 'always', 'advanced-comment-control' ),
			'never' => __( 'never', 'advanced-comment-control' ),
		);
		foreach ( $types as $key => $string ) {
					
			$return .= '<option value="' . $key . '" ' . selected( $key, $role_rule['type'], false ) . '>' . $string . '</option>';
		
		}
		$return .= '</select> ';
		$return .= '&nbsp;';
		$return .= '</div>';
		
		$return .= '<div class="advanced-comment-control-post-type-role-rule-options">';
	    $return .= __( 'comment on', 'advanced-comment-control' );
		$return .= '&nbsp;';
		$return .= '<select class="advanced-comment-control-post-type-role" name="role_rules[' . $row_key .'][post_type]">';
		$hidden_post_types = array( 'attachment', 'revision', 'nav_menu_item' );
		$post_types = get_post_types( array(), 'objects' );
		foreach ( $post_types as $post_type_obj ) {
		
			//No sense in showing these post types
			if ( in_array( $post_type_obj->name, $hidden_post_types ) ) 
				continue;
				
			//If the post type doesn't support comments, don't add the option
			if ( !post_type_supports( $post_type_obj->name, 'comments' ) ) 
				continue;
			$return .= '<option value="' . $post_type_obj->name . '" ' . selected( $post_type_obj->name, $role_rule['post_type'], false ) . '>' . strtolower( $post_type_obj->labels->name ) . '</option>';
		
		}
		$return .= '</select> ';
		$return .= '&nbsp;';
		$return .= '</div>';

		$return .= '<span class="delete-x delete-advanced-comment-control-role-rule">&times;</span>';
		
		$return .= '</td>';
		$return .= '</tr>';
		
		return $return;
		
	}
	
}

if ( !function_exists( 'build_advanced_comment_control_add_new_role_rule_row_ajax' ) ) {

	/**
	 * AJAX Wrapper
	 *
	 * @since 1.0.0
	 */
	function build_advanced_comment_control_add_new_role_rule_row_ajax() {
	
		die( build_advanced_comment_control_role_rule_row( array(), $_REQUEST['key'] ) );
		
	}
	add_action( 'wp_ajax_advanced-comment-control-add-new-role-rule-row', 'build_advanced_comment_control_add_new_role_rule_row_ajax' );
	
}

if ( !function_exists( 'wp_print_r' ) ) { 

	/**
	 * Helper function used for printing out debug information
	 *
	 * HT: Glenn Ansley @ iThemes.com
	 *
	 * @since 0.0.1
	 *
	 * @param int $args Arguments to pass to print_r
	 * @param bool $die TRUE to die else FALSE (default TRUE)
	 */
    function wp_print_r( $args, $die = true ) { 
	
        $echo = '<pre>' . print_r( $args, true ) . '</pre>';
		
        if ( $die ) die( $echo );
        	else echo $echo;
		
    }   
	
}