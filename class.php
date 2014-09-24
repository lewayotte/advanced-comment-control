<?php
/**
 * Registers Advanced Comment Control class for setting up Advanced Comment Control
 *
 * @package Advanced Comment Control
 * @since 1.0.0
 */

if ( !class_exists( 'AdvancedCommentControl' ) ) {
	
	/**
	 * This class registers the main Advanced Comment Control functionality
	 *
	 * @since 1.0.0
	 */	
	class AdvancedCommentControl {
		
		/**
		 * Class constructor, puts things in motion
		 *
		 * @since 1.0.0
		 * @uses add_action() Calls 'admin_init' hook on $this->upgrade
		 * @uses add_action() Calls 'admin_enqueue_scripts' hook on $this->admin_wp_enqueue_scripts
		 * @uses add_action() Calls 'admin_print_styles' hook on $this->admin_wp_print_styles
		 * @uses add_action() Calls 'admin_menu' hook on $this->admin_menu
		 * @uses add_filteR() Calls 'the_posts' hook on $this->close_comments
		 */
		function AdvancedCommentControl() {
			
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_wp_enqueue_scripts' ), 999 );
			add_action( 'admin_print_styles', array( $this, 'admin_wp_print_styles' ), 999 );
			
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			
			add_filter( 'comments_open', array( $this, 'comments_open' ), 10, 2 );
			
		}
		
		function comments_open( $open, $post_id ) {
		
			$post = get_post( $post_id );
			
			if ( !empty( $post ) ) {
					
				$settings = $this->get_settings();
				
				if ( !empty( $settings['role_rules'] )  ) {
				
					$current_user = wp_get_current_user();
															
					foreach ( $settings['role_rules'] as $rule ) {
						
						switch( $rule['role'] ) {
							
							case 'loggedin':
								if (  0 !== $current_user->ID ) { //current user is logged in
									if ( $post->post_type === $rule['post_type'] ) {
										if ( 'always' === $rule['type'] )
											return true;
										else if ( 'never' === $rule['type'] )
											return false;
									}
								}
								
							case 'loggedout':
								if (  0 === $current_user->ID ) { //current user is not logged in
									if ( $post->post_type === $rule['post_type'] ) {
										if ( 'always' === $rule['type'] )
											return true;
										else if ( 'never' === $rule['type'] )
											return false;
									}
								}
								
							default: //Any WordPress user role
								foreach( $current_user->roles as $role ) {
									if ( $role === $rule['role'] ) {
										if ( 'always' === $rule['type'] )
											return true;
										else if ( 'never' === $rule['type'] )
											return false;
									}
								}
							
						}
						
					}
					
				}

				if ( !empty( $settings['post_rules'] ) ) {
						
					foreach( $settings['post_rules'] as $rule ) {
					
						if ( $post->post_type === $rule['post_type'] ) {
						
							switch( $rule['type'] ) {
							
								case 'age':
									if ( strtotime( $post->post_date_gmt ) < strtotime( sprintf( '-%d %s', $rule['time'], $rule['unit'] ) ) ) {
										return false;
									}
									
								case 'limit':
									if ( $post->comment_count >= $rule['limit'] ) {
										return false;
									}
									
							}
							
						}
						
					}
					
				}
							
			}
			return $open;
			
		}
				
		/**
		 * Initialize Advanced Comment Control Admin Menu
		 *
		 * @since 1.0.0
		 * @uses add_options_page() Creates Settings submenu to Settings menu in WordPress
		 */
		function admin_menu() {
						
			add_comments_page( 'Advanced Comment Control Settings', 'Advanced Controls', 'manage_options', 'advanced_comment_control_settings', array( $this, 'settings_page' ) );

			
		}
		
		/**
		 * Prints backend Advanced Comment Control styles
		 *
		 * @since 1.0.0
		 * @uses $hook_suffix to determine which page we are looking at, so we only load the CSS on the proper page(s)
		 * @uses wp_enqueue_style to enqueue the necessary pigeon pack style sheets
		 */
		function admin_wp_print_styles() {
		
			global $hook_suffix;
						
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
				
			if ( isset( $_REQUEST['post_type'] ) ) {
				
				$post_type = $_REQUEST['post_type'];
				
			} else {
				
				if ( isset( $_REQUEST['post'] ) )
					$post_id = (int) $_REQUEST['post'];
				elseif ( isset( $_REQUEST['post_ID'] ) )
					$post_id = (int) $_REQUEST['post_ID'];
				else
					$post_id = 0;
				
				if ( $post_id )
					$post = get_post( $post_id );
				
				if ( isset( $post ) && !empty( $post ) )
					$post_type = $post->post_type;
				
			}
			
			if ( 'comments_page_advanced_comment_control_settings' === $hook_suffix ) {
					
				wp_enqueue_style( 'advanced_comment_control_admin_style', ADVANCED_COMMENT_CONTROL_PLUGIN_URL . '/css/advanced-comment-control-options'.$suffix.'.css', false, ADVANCED_COMMENT_CONTROL_VERSION );
			
			}
			
		}
		
		/**
		 * Enqueues backend Advanced Comment Control scripts
		 *
		 * @since 1.0.0
		 * @uses wp_enqueue_script to enqueue the necessary pigeon pack javascripts
		 * 
		 * @param $hook_suffix passed through by filter used to determine which page we are looking at
		 *        so we only load the CSS on the proper page(s)
		 */
		function admin_wp_enqueue_scripts( $hook_suffix ) {
		
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			
			if ( isset( $_REQUEST['post_type'] ) ) {
				
				$post_type = $_REQUEST['post_type'];
				
			} else {
				
				if ( isset( $_REQUEST['post'] ) )
					$post_id = (int) $_REQUEST['post'];
				elseif ( isset( $_REQUEST['post_ID'] ) )
					$post_id = (int) $_REQUEST['post_ID'];
				else
					$post_id = 0;
				
				if ( $post_id )
					$post = get_post( $post_id );
				
				if ( isset( $post ) && !empty( $post ) )
					$post_type = $post->post_type;
				
			}
			
			if ( 'comments_page_advanced_comment_control_settings' === $hook_suffix ) {
			
				wp_enqueue_script( 'advanced_comment_control_options', ADVANCED_COMMENT_CONTROL_PLUGIN_URL . '/js/advanced-comment-control-options'.$suffix.'.js', array( 'jquery' ), ADVANCED_COMMENT_CONTROL_VERSION, true );
				
			}
			
		}
		
		/**
		 * Get Advanced Comment Control options set in options table
		 *
		 * @since 1.0.0
		 * @uses apply_filters() To call 'advanced_comment_control_default_settings' for future addons
		 * @uses wp_parse_args function to merge default with stored options
		 *
		 * return array Advanced Comment Control settings
		 */
		function get_settings() {
			
			$defaults = array( 
				'post_rules' => array(
					array(
						'post_type' => 'post',
						'type' 		=> 'age',
						'time' 		=> 6,
						'unit' 		=> 'month',
					),
				),
				'role_rules' => array(
					array(
						'role' 		=> 'administrator',
						'type' 		=> 'always',
						'post_type' => 'post',
					),
					array(
						'role' 		=> 'administrator',
						'type' 		=> 'always',
						'post_type' => 'page',
					),
				),
			);
			$defaults = apply_filters( 'advanced_comment_control_default_settings', $defaults );
		
			$settings = get_option( 'advanced-comment-control' );
			
			return wp_parse_args( $settings, $defaults );
			
		}
		
		/**
		 * Output Advanced Comment Control's settings page and saves new settings on form submit
		 *
		 * @since 1.0.0
		 * @uses do_action() To call 'advanced_comment_control_settings_page' for future addons
		 */
		function settings_page() {

			// Get the user options
			$settings = $this->get_settings();
			$settings_updated = false;
			
			if ( isset( $_REQUEST['update_advanced_comment_control_settings'] ) ) {
				
				if ( !isset( $_REQUEST['advanced_comment_control_general_options_nonce'] ) 
					|| !wp_verify_nonce( $_REQUEST['advanced_comment_control_general_options_nonce'], 'advanced_comment_control_general_options' ) ) {
						
					
					echo '<div class="error"><p><strong>' . __( 'ERROR: Unable to save settings.', 'advanced-comment-control' ) . '</strong></p></div>';
				
				} else {
					
					if ( isset( $_REQUEST['post_rules'] ) )
						$settings['post_rules'] = $_REQUEST['post_rules'];
						
					if ( isset( $_REQUEST['role_rules'] ) )
						$settings['role_rules'] = $_REQUEST['role_rules'];
												
					$settings = apply_filters( 'update_advanced_comment_control_settings', $settings );
					
					update_option( 'advanced-comment-control', $settings );
					$settings_updated = true;
					
				}
				
			}
			
			if ( $settings_updated )
				echo '<div class="updated"><p><strong>' . __( 'Advanced Comment Control Settings Updated.', 'advanced-comment-control' ) . '</strong></p></div>';
			
			// Display HTML form for the options below
			?>
			<div id="advanced-comment-control-administrator-options" class=wrap>
            
            <div class="icon32 icon32-pigeonpack_settings" id="icon-edit"><br></div>
            
            <h2><?php _e( 'Advanced Comment Control Settings', 'advanced-comment-control' ); ?></h2>

            <div style="width:70%;" class="postbox-container">
            <div class="metabox-holder">	
            <div class="meta-box-sortables ui-sortable">
            
                <form id="advanced-comment-control" method="post" action="">
                    
                    <div id="modules" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        
                        <h3 class="hndle"><span><?php _e( 'Post Rules', 'advanced-comment-control' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <table id="advanced-comment-control-post-rules">
                        
                        	<?php
                        	$last_key = -1;
                        	if ( !empty( $settings['post_rules'] ) ) {
                        	
	                        	foreach( $settings['post_rules'] as $key => $rule ) {
	                        	
	                        		echo build_advanced_comment_control_post_rule_row( $rule, $key );
	                        		$last_key = $key;

	                        	}
	                        	
                        	}
                        	?>
                                                    
                        </table>
                        
				        <script type="text/javascript" charset="utf-8">
				            var advanced_comment_control_last_post_rule_key = <?php echo $last_key; ?>;
				        </script>

                    	<p>
                       		<input class="button-secondary" id="add-advanced-comment-control-post-rule" type="submit" name="add-advanced-comment-control-post-rule" value="<?php _e( 'Add New Post Rule', 'advanced-comment-control' ); ?>" />
                    	</p>
                        
                        <?php wp_nonce_field( 'advanced_comment_control_general_options', 'advanced_comment_control_general_options_nonce' ); ?>
                                                  
                        <p class="submit">
                            <input class="button-primary" type="submit" name="update_advanced_comment_control_settings" value="<?php _e( 'Save Settings', 'advanced-comment-control' ) ?>" />
                        </p>

                        </div>
                        
                    </div>
                    
                    <div id="modules" class="postbox">
                    
                        <div class="handlediv" title="Click to toggle"><br /></div>
                        
                        <h3 class="hndle"><span><?php _e( 'User Role Options', 'advanced-comment-control' ); ?></span></h3>
                        
                        <div class="inside">
                        
                        <table id="advanced-comment-control-role-rules">
                        
                        	<?php
                        	$last_key = -1;
                        	if ( !empty( $settings['role_rules'] ) ) {
                        	
	                        	foreach( $settings['role_rules'] as $key => $rule ) {
	                        	
	                        		echo build_advanced_comment_control_role_rule_row( $rule, $key );
	                        		$last_key = $key;

	                        	}
	                        	
                        	}
                        	?>
                                                    
                        </table>
                        
				        <script type="text/javascript" charset="utf-8">
				            var advanced_comment_control_last_role_rule_key = <?php echo $last_key; ?>;
				        </script>

                    	<p>
                       		<input class="button-secondary" id="add-advanced-comment-control-role-rule" type="submit" name="add-advanced-comment-control-role-rule" value="<?php _e( 'Add New Role Rule', 'advanced-comment-control' ); ?>" />
                    	</p>
                        
                        <?php wp_nonce_field( 'advanced_comment_control_general_options', 'advanced_comment_control_general_options_nonce' ); ?>
                                                  
                        <p class="submit">
                            <input class="button-primary" type="submit" name="update_advanced_comment_control_settings" value="<?php _e( 'Save Settings', 'advanced-comment-control' ) ?>" />
                        </p>

                        </div>
                        
                    </div>
                    
                    <?php do_action( 'advanced_comment_control_settings_page' ); ?>
                    
                </form>
                
            </div>
            </div>
            </div>
			</div>
			<?php
			
		}
				
	}
	
}
