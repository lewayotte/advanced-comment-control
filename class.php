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
			add_action( 'load-post.php', array( $this, 'load_post_php' ) );
			    
			add_action( 'save_post', array( $this, 'save_advanced_comment_control_status_meta_box' ) );

			add_filter( 'comments_open', array( $this, 'comments_open' ), 10, 2 );
			add_filter( 'pings_open', array( $this, 'pings_open' ), 10, 2 );
			
		}
		
		function comments_open( $open, $post_id ) {
		
			$post = get_post( $post_id );
			
			if ( !empty( $post ) ) {
					
				$settings = $this->get_settings();
				
				$disable_advanced_comment_control_user_role_rules = get_post_meta( $post->ID, '_disable_advanced_comment_control_user_role_rules', true );
				
				if ( empty( $disable_advanced_comment_control_user_role_rules ) && !empty( $settings['role_rules'] )  ) {
				
					$current_user = wp_get_current_user();
															
					foreach ( $settings['role_rules'] as $rule ) {
						
						if ( $post->post_type === $rule['post_type'] ) {
							
							switch( $rule['role'] ) {
								
								case 'loggedin':
									if (  0 !== $current_user->ID ) { //current user is logged in
										if ( 'always' === $rule['type'] ) {
											return true;
										} else if ( 'never' === $rule['type'] ) {
											return false;
										}
									}
									break;
									
								case 'loggedout':
									if (  0 === $current_user->ID ) { //current user is not logged in
										if ( 'always' === $rule['type'] ) {
											return true;
										} else if ( 'never' === $rule['type'] ) {
											return false;
										}
									}
									break;
									
								default: //Any WordPress user role
									foreach( $current_user->roles as $role ) {
										if ( $role === $rule['role'] ) {
											if ( 'always' === $rule['type'] ) {
												return true;
											} else if ( 'never' === $rule['type'] ) {
												return false;
											}
										}
									}
									break;
								
							}
							
						}
						
					}
					
				}

				$disable_advanced_comment_control_post_rules = get_post_meta( $post->ID, '_disable_advanced_comment_control_post_rules', true );

				if ( empty( $disable_advanced_comment_control_post_rules ) && !empty( $settings['post_rules'] ) ) {
						
					foreach( $settings['post_rules'] as $rule ) {
						
						if ( empty( $rule['content_type'] ) || $rule['content_type'] === 'comments' ) {
					
							if ( $post->post_type === $rule['post_type'] ) {
							
								switch( $rule['type'] ) {
								
									case 'age':
										if ( strtotime( $post->post_date_gmt ) < strtotime( sprintf( '-%d %s', $rule['time'], $rule['unit'] ) ) ) {
											return false;
										}
										break;
										
									case 'limit':
										if ( $post->comment_count >= $rule['limit'] ) {
											return false;
										}
										break;
										
								}
								
							}
							
						}
						
					}
					
				}
							
			}
			return $open;
			
		}
		
		function pings_open( $open, $post_id ) {
		
			$post = get_post( $post_id );
			
			if ( !empty( $post ) ) {
					
				$settings = $this->get_settings();

				$disable_advanced_comment_control_post_rules = get_post_meta( $post->ID, '_disable_advanced_comment_control_post_rules', true );

				if ( empty( $disable_advanced_comment_control_post_rules ) && !empty( $settings['post_rules'] ) ) {
						
					foreach( $settings['post_rules'] as $rule ) {
						
						if ( $rule['content_type'] === 'pings' ) {
				
							if ( $post->post_type === $rule['post_type'] ) {
							
								switch( $rule['type'] ) {
								
									case 'age':
										if ( strtotime( $post->post_date_gmt ) < strtotime( sprintf( '-%d %s', $rule['time'], $rule['unit'] ) ) ) {
											return false;
										}
										break;
										
									case 'limit':
										if ( $post->comment_count >= $rule['limit'] ) {
											return false;
										}
										break;
										
								}
								
							}
								
						}
						
					}
					
				}
							
			}
			var_dump( $open );
			return $open;
			
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
		 * Initialize Advanced Comment Control Admin Menu
		 *
		 * @since 1.0.0
		 * @uses add_options_page() Creates Settings submenu to Settings menu in WordPress
		 */
		function admin_menu() {
						
			add_comments_page( 'Advanced Comment Control Settings', 'Advanced Controls', 'manage_options', 'advanced_comment_control_settings', array( $this, 'settings_page' ) );

			
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
					
					if ( isset( $_REQUEST['post_rules'] ) ) {
						$settings['post_rules'] = $_REQUEST['post_rules'];
					} else {
						$settings['post_rules'] = array();
					}

					if ( isset( $_REQUEST['role_rules'] ) ) {
						$settings['role_rules'] = $_REQUEST['role_rules'];
					} else {
						$settings['role_rules'] = array();
					}
												
					$settings = apply_filters( 'update_advanced_comment_control_settings', $settings );
					
					update_option( 'advanced-comment-control', $settings );
					$settings_updated = true;
					
				}
				
			}
			
			if ( $settings_updated )
				echo '<div class="updated"><p><strong>' . __( 'Advanced Comment Control Settings Updated.', 'advanced-comment-control' ) . '</strong></p></div>';
			
			// Display HTML form for the options below
			?>
			<div id="advanced-comment-control-administrator-options" class="wrap">
            
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
	            
	            <div style="width:25%; float:right;" class="postbox-container">
		            <div class="metabox-holder">	
		            	<div class="meta-box-sortables ui-sortable">
		                    <div id="modules" class="postbox">
		                        <div class="handlediv" title="Click to toggle"><br /></div>
		                        
		                        <h3 class="hndle"><span><?php _e( 'Help Keep This Plugin Alive', 'advanced-comment-control' ); ?></span></h3>
		                        
		                        <div class="inside">
									
									<div class="other-leenkme-plugins">
										<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
										<input type="hidden" name="cmd" value="_s-xclick">
										<input type="hidden" name="hosted_button_id" value="AQMUS8459Q8T6">
										<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
										<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
										</form>
									</div>
									
		                        </div>
		                    </div>
		                    
		                    <div id="modules" class="postbox">
		                        <div class="handlediv" title="Click to toggle"><br /></div>
		                        
		                        <h3 class="hndle"><span><?php _e( 'Pigeon Pack', 'advanced-comment-control' ); ?></span></h3>
		                        
		                        <div class="inside">
									
									<div class="other-leenkme-plugins">
										<a href="http://pigeonpack.com"><img src="http://pigeonpack.com/icon-128x128.png" /></a>
										<p><a href="http://pigeonpack.com"><?php _e( 'Free and easy email marketing, newsletters, and campaigns; built into your WordPress dashboard!', 'advanced-comment-control' ); ?></a></p>
									</div>
									
		                        </div>
		                    </div>
		                    
		                    <div id="modules" class="postbox">
		                        <div class="handlediv" title="Click to toggle"><br /></div>
		                        
		                        <h3 class="hndle"><span><?php _e( 'leenk.me', 'advanced-comment-control' ); ?></span></h3>
		                        
		                        <div class="inside">
									
									<div class="other-leenkme-plugins">
										<a href="http://leenk.me"><img src="http://leenk.me/icon-128x128.png" /></a>
										<p><a href="http://leenk.me"><?php _e( 'Publicize your WordPress content to your Twitter, Facebook, & LinkedIn accounts easily and automatically!', 'advanced-comment-control' ); ?></a></p>
									</div>
									
		                        </div>
		                    </div>
		            	</div>
		            </div>
	            </div>
			</div>
			<?php
			
		}
		
		/**
		 * Output Advanced Comment Control's settings page and saves new settings on form submit
		 *
		 * @since 1.0.0
		 * @uses add_meta_box() To call 'advanced_comment_control_status_meta_box' 
		 */
		function load_post_php() {
			if ( isset( $_GET['post'] ) )
			    $post_id = $post_ID = (int) $_GET['post'];
			elseif ( isset( $_POST['post_ID'] ) )
			    $post_id = $post_ID = (int) $_POST['post_ID'];
			else
			    $post_id = $post_ID = 0;
			
			$post = $post_type = $post_type_object = null;
			
			if ( $post_id )
			    $post = get_post( $post_id );
			
			if ( $post ) {
			    $post_type = $post->post_type;
			    $post_type_object = get_post_type_object( $post_type );
			}
			
			if ( post_type_supports( $post_type, 'comments' ) )
			    add_meta_box('advancedcommentcontroldiv', __( 'Advanced Comment Control', 'advanced-comment-control' ), array( $this, 'advanced_comment_control_status_meta_box' ), null, 'normal', 'core');
		}
		
		function advanced_comment_control_status_meta_box( $post ) {
								
			$settings = $this->get_settings();
		    $post_type = $post->post_type;
		    $post_type_object = get_post_type_object( $post_type );
		    $limits = array();
						
			if ( !empty( $settings['post_rules'] ) ) {
					
				foreach( $settings['post_rules'] as $rule ) {
				
					if ( $post_type === $rule['post_type'] ) {
					
						switch( $rule['type'] ) {
						
							case 'age':
								if ( strtotime( $post->post_date_gmt ) < strtotime( sprintf( '-%d %s', $rule['time'], $rule['unit'] ) ) ) {
									$effective_post_rules[] = sprintf( __( 'is over %s %s(s) old', 'advanced-comment-control' ), $rule['time'], $rule['unit'] );
								} else {
									$ineffective_post_rules[] = sprintf( __( 'are older than %s %s(s)', 'advanced-comment-control' ), $rule['time'], $rule['unit'] );
								}
								break;
								
							case 'limit':
								if ( $post->comment_count >= $rule['limit'] ) {
									$effective_post_rules[] =  sprintf( __( 'has more than %s comment(s)', 'advanced-comment-control' ), $rule['limit'] );
								} else {
									$ineffective_post_rules[] = sprintf( __( 'have more than %s comment(s)', 'advanced-comment-control' ), $rule['limit'] );
								}
								break;
								
						}
						
					}
					
				}
				
			}

			$disable_advanced_comment_control_post_rules = get_post_meta( $post->ID, '_disable_advanced_comment_control_post_rules', true );
			
			echo '<p><label for="disable_advanced_comment_control_post_rules" class="selectit"><input name="disable_advanced_comment_control_post_rules" type="checkbox" id="disable_advanced_comment_control_post_rules" ' . checked( $disable_advanced_comment_control_post_rules, 'on', false ). ' /> ' . sprintf( __( 'Disable post rules for this %s.', 'advanced-comment-control' ), strtolower( $post_type_object->labels->singular_name ) ) . '</label></p>';		

			if ( !empty( $effective_post_rules ) ) {
				$last  = array_slice( $effective_post_rules, -1 );
				$first = join( ', ', array_slice( $effective_post_rules, 0, -1 ) );
				$both  = array_filter( array_merge( array( $first ), $last ) );
				$restrictions = join( ' and ', $both );
				
				echo '<p class="description">' . sprintf( __( 'Unless disabled, users may not be able to comment on this %s because it %s.', 'advanced-comment-control' ), strtolower( $post_type_object->labels->singular_name ), $restrictions ) . '</p>';
			}
			
			if ( !empty( $ineffective_post_rules ) ) {
				$last  = array_slice( $ineffective_post_rules, -1 );
				$first = join( ', ', array_slice( $ineffective_post_rules, 0, -1 ) );
				$both  = array_filter( array_merge( array( $first ), $last ) );
				$restrictions = join( ' and ', $both );

				echo '<p class="description">' . sprintf( __( '%s that %s will be disabled.', 'advanced-comment-control' ), $post_type_object->labels->name, $restrictions ) . '</p>';
			}
			
			if ( empty( $effective_post_rules ) && empty( $ineffective_post_rules ) ) {
				echo '<p class="description">' . sprintf( __( 'There are currently no Post Rules restricting %s.', 'advanced-comment-control' ), strtolower( $post_type_object->labels->name ), $restrictions ) . '</p>';
			}

			$disable_advanced_comment_control_user_role_rules = get_post_meta( $post->ID, '_disable_advanced_comment_control_user_role_rules', true );
			
			if ( !empty( $settings['role_rules'] )  ) {
			
				foreach ( $settings['role_rules'] as $rule ) {
				
					if ( $post->post_type === $rule['post_type'] ) {
						
						switch( $rule['role'] ) {
							
							case 'loggedin':
								if ( 'never' === $rule['type'] ) {
									$never_user_role_rules[] = __( 'logged in users', 'advanced-comment-control' );
								} else {
									$always_user_role_rules[] = __( 'logged in users', 'advanced-comment-control' );
								}
								break;
								
							case 'loggedout':
								if ( 'never' === $rule['type'] ) {
									$never_user_role_rules[] = __( 'logged out users', 'advanced-comment-control' );
								} else {
									$always_user_role_rules[] = __( 'logged out users', 'advanced-comment-control' );
								}
								break;
								
							default: //Any WordPress user role
								$editable_roles = array_reverse( get_editable_roles() );
								if ( !empty( $editable_roles[$rule['role']] ) ) {
									$role_name = $editable_roles[$rule['role']]['name'];
								} else {
									$role_name = $rule['role'];
								}
								
								if ( 'never' === $rule['type'] ) {
									$never_user_role_rules[] = sprintf( __( '%s users', 'advanced-comment-control' ), $role_name );
								} else {
									$always_user_role_rules[] = sprintf( __( '%s users', 'advanced-comment-control' ), $role_name );
								}
								break;
						
						}
					
					}
					
				}
				
			}
			
			echo '<p><label for="disable_advanced_comment_control_user_role_rules" class="selectit"><input name="disable_advanced_comment_control_user_role_rules" type="checkbox" id="disable_advanced_comment_control_user_role_rules" ' . checked( $disable_advanced_comment_control_user_role_rules, 'on', false ). ' /> ' . sprintf( __( 'Disable user role rules for this %s.', 'advanced-comment-control' ), $post_type_object->labels->singular_name ) . '</label></p>';

			if ( !empty( $never_user_role_rules ) ) {
				$last  = array_slice( $never_user_role_rules, -1 );
				$first = join( ', ', array_slice( $never_user_role_rules, 0, -1 ) );
				$both  = array_filter( array_merge( array( $first ), $last ) );
				$restrictions = join( ' and ', $both );
				
				echo '<p class="description">' . sprintf( __( 'Unless disabled, %s will not be able to comment on this %s.', 'advanced-comment-control' ), $restrictions, strtolower( $post_type_object->labels->singular_name ) ) . '</p>';
			}
			
			if ( !empty( $always_user_role_rules ) ) {
				$last  = array_slice( $always_user_role_rules, -1 );
				$first = join( ', ', array_slice( $always_user_role_rules, 0, -1 ) );
				$both  = array_filter( array_merge( array( $first ), $last ) );
				$restrictions = join( ' and ', $both );
				
				echo '<p class="description">' . ucfirst( sprintf( __( '%s will always be able to comment on this %s.', 'advanced-comment-control' ), $restrictions, strtolower( $post_type_object->labels->singular_name ) ) ) . '</p>';
			}
			
			if ( empty( $never_user_role_rules ) && empty( $always_user_role_rules ) ) {
				echo '<p class="description">' . sprintf( __( 'There are currently no User Role Rules restricting %s', 'advanced-comment-control' ), $post_type_object->labels->name, $restrictions ) . '</p>';
			}
						
			wp_nonce_field( 'advanced_comment_control_status_meta_box', 'advanced_comment_control_status_meta_box_nonce' );
						
		}
				
		/**
		 * When the post is saved, saves our custom data.
		 *
		 * @param int $post_id The ID of the post being saved.
		 */
		function save_advanced_comment_control_status_meta_box( $post_id ) {
			/*
			 * We need to verify this came from our screen and with proper authorization,
			 * because the save_post action can be triggered at other times.
			 */
		
			// Check if our nonce is set.
			if ( ! isset( $_POST['advanced_comment_control_status_meta_box_nonce'] ) ) {
				return;
			}
		
			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['advanced_comment_control_status_meta_box_nonce'], 'advanced_comment_control_status_meta_box' ) ) {
				return;
			}
		
			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}
		
			// Check the user's permissions.
			if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		
				if ( ! current_user_can( 'edit_page', $post_id ) ) {
					return;
				}
		
			} else {
		
				if ( ! current_user_can( 'edit_post', $post_id ) ) {
					return;
				}
			}
			/* OK, it's safe for us to save the data now. */
			
			// Make sure that it is set.
			if ( !empty( $_POST['disable_advanced_comment_control_post_rules'] ) ) {
				update_post_meta( $post_id, '_disable_advanced_comment_control_post_rules', 'on' );
			} else {
				delete_post_meta( $post_id, '_disable_advanced_comment_control_post_rules' );
			}
			
			if ( !empty( $_POST['disable_advanced_comment_control_user_role_rules'] ) ) {
				update_post_meta( $post_id, '_disable_advanced_comment_control_user_role_rules', 'on' );
			} else {
				delete_post_meta( $post_id, '_disable_advanced_comment_control_user_role_rules' );
			}
		
			// Update the meta field in the database.
		}
				
	}
	
}
