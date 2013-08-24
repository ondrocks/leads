<?phpadd_action('admin_init', 'wp_cta_rebuild_permalinks');function wp_cta_rebuild_permalinks(){	$activation_check = get_option('wp_cta_activate_rewrite_check',0);		if ($activation_check)	{				global $wp_rewrite;		$wp_rewrite->flush_rules();		update_option( 'wp_cta_activate_rewrite_check', '0');	}}add_action('init', 'wp_call_to_action_register');function wp_call_to_action_register() {		$slug = get_option( 'main-wp-call-to-action-permalink-prefix', 'cta' );    $labels = array(        'name' => _x('Call to Action', 'post type general name'),        'singular_name' => _x('Call to Action', 'post type singular name'),        'add_new' => _x('Add New', 'Call to Action'),        'add_new_item' => __('Add New Call to Action'),        'edit_item' => __('Edit Call to Action'),        'new_item' => __('New Call to Action'),        'view_item' => __('View Call to Action'),        'search_items' => __('Search Call to Action'),        'not_found' =>  __('Nothing found'),        'not_found_in_trash' => __('Nothing found in Trash'),        'parent_item_colon' => ''    );	    $args = array(        'labels' => $labels,        'public' => true,        'publicly_queryable' => true,        'show_ui' => true,        'query_var' => true,        'menu_icon' => WP_CTA_URLPATH . '/images/click.png',        'rewrite' => array("slug" => "$slug"),        'capability_type' => 'post',        'hierarchical' => false,        'menu_position' => null,        'show_in_nav_menus'   => false,        'supports' => array('title','editor', 'custom-fields','thumbnail')      );	      register_post_type( 'wp-call-to-action' , $args );		//flush_rewrite_rules( false );	register_taxonomy('wp_call_to_action_category','wp-call-to-action', array(            'hierarchical' => true,            'label' => "Categories",            'singular_label' => "Call to Action Category",            'show_ui' => true,            'query_var' => true,			"rewrite" => true     ));}// Change except box titleadd_action( 'admin_init', 'wp_cta_change_excerpt_to_summary' );function wp_cta_change_excerpt_to_summary() {	$post_type = "wp-call-to-action";	if ( post_type_supports($post_type, 'excerpt') ) {	add_meta_box('postexcerpt', __('Short Description'), 'post_excerpt_meta_box', $post_type, 'normal', 'core'); }}// Fix the_title on landing pages if the_title() is used in templateif (!is_admin()){	// Need conditional here for only current page title if on landing page	add_filter('the_title', 'wp_cta_fix_wp_cta_title', 10, 2);	add_filter('get_the_title', 'wp_cta_fix_wp_cta_title', 10, 2);		function wp_cta_fix_wp_cta_title($title) 	{		global $post;		$the_id = $post->ID;		if (isset($post)&&'wp-call-to-action' == $post->post_type) {						$title = get_post_meta($post->ID, 'wp-cta-main-headline', true);			$title = apply_filters('wp-cta-main-headline', $title);		}		return $title;	}}/*********PREPARE COLUMNS FOR IMPRESSIONS AND CONVERSIONS***************/if (is_admin()){	//include_once(WP_CTA_PATH.'filters/filters.post-type.php');		//add_filter('manage_edit-wp-call-to-action_sortable_columns', 'wp_cta_column_register_sortable');	add_filter("manage_edit-wp-call-to-action_columns", 'wp_cta_columns');	add_action("manage_posts_custom_column", "wp_cta_column");	add_filter('wp-call-to-action_orderby','wp_cta_column_orderby', 10, 2);		// remove SEO filter	if ( (isset($_GET['post_type']) && ($_GET['post_type'] == 'wp-call-to-action') ) ) 		{ add_filter( 'wpseo_use_page_analysis', '__return_false' ); }			//define columns for landing pages	function wp_cta_columns($columns)	{		$columns = array(			"cb" => "<input type=\"checkbox\" />",						//"ID" => "ID",			"thumbnail-cta" => "Preview",			"title" => "Call to Action Title",			"cta_stats" => "Variation Testing Stats",				"cta_impressions" => "Total<br>Impressions",			"cta_actions" => "Total<br>Conversions",			"cta_cr" => "Total<br>Click Through Rate"					);		return $columns;	}		if (is_admin())	{		$parts = explode('wp-content',WP_PLUGIN_DIR);		$part = $parts[1];		$plugin_path = "./../wp-content{$part}/wp-call-to-actions/";			}		function wp_cta_show_stats_list() {			global $post;		$permalink = get_permalink($post->ID);		$variations = get_post_meta($post->ID, 'wp-cta-ab-variations', true);		if ($variations)		{			$variations = explode(",", $variations);			$variations = array_filter($variations,'is_numeric');						//echo "<b>".$wp_cta_impressions."</b> visits";			echo "<span class='show-stats button'>Show Variation Stats</span>";			echo "<ul class='wp-cta-varation-stat-ul'>";						$first_status = get_post_meta($post->ID,'wp_cta_ab_variation_status', true); // Current status			$first_notes = get_post_meta($post->ID,'wp-cta-variation-notes', true);			$cr_array = array();			$i = 0;			$impressions = 0;			$conversions = 0;			foreach ($variations as $vid) 			{				$letter = wp_cta_ab_key_to_letter($vid); // convert to letter				$each_impression = get_post_meta($post->ID,'wp-cta-ab-variation-impressions-'.$vid, true); // get impressions				$v_status = get_post_meta($post->ID,'wp_cta_ab_variation_status-'.$vid, true); // Current status								if ($i === 0) { $v_status = $first_status; } // get status of first								(($v_status === "")) ? $v_status = "1" : $v_status = $v_status; // Get on/off status								$each_notes = get_post_meta($post->ID,'wp-cta-variation-notes-'.$vid, true); // Get Notes								if ($i === 0) { $each_notes = $first_notes; } // Get first notes								$each_conversion = get_post_meta($post->ID,'wp-cta-ab-variation-conversions-'.$vid, true);				(($each_conversion === "")) ? $final_conversion = 0 : $final_conversion = $each_conversion;								$impressions += get_post_meta($post->ID,'wp-cta-ab-variation-impressions-'.$vid, true);								$conversions += get_post_meta($post->ID,'wp-cta-ab-variation-conversions-'.$vid, true);								if ($each_impression != 0) 				{					$conversion_rate = $final_conversion / $each_impression;				} 				else 				{					$conversion_rate = 0;				}								$conversion_rate = round($conversion_rate,2) * 100; 				$cr_array[] = $conversion_rate;								if ($v_status === "0")				{					$final_status = "(Paused)";				} 				else 				{					$final_status = "";				}				/*if ($cr_array[$i] > $largest) {				$largest = $cr_array[$i];				 } 				(($largest === $conversion_rate)) ? $winner_class = 'wp-cta-current-winner' : $winner_class = ""; */				(($final_conversion === "1")) ? $c_text = 'conversion' : $c_text = "conversions"; 				(($each_impression === "1")) ? $i_text = 'view' : $i_text = "views";				(($each_notes === "")) ? $each_notes = 'No notes' : $each_notes = $each_notes;				$data_letter = "data-letter=\"".$letter."\"";				$popup = "data-notes=\"<span class='wp-cta-pop-description'>".$each_notes."</span><span class='wp-cta-pop-controls'><span class='wp-cta-pop-edit button-primary'><a href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=".$vid."&action=edit'>Edit This Varaition</a></span><span class='wp-cta-pop-preview button'><a title='Click to Preview this variation' class='thickbox' href='".$permalink."?wp-cta-variation-id=".$vid."&wp_cta_iframe_window=on&post_id=".$post->ID."&TB_iframe=true&width=640&height=703' target='_blank'>Preview This Varaition</a></span><span class='wp-cta-bottom-controls'><span class='wp-cta-delete-var-stats' data-letter='".$letter."' data-vid='".$vid."' rel='".$post->ID."'>Clear These Stats</span></span></span>\"";								echo "<li rel='".$final_status."' data-postid='".$post->ID."' data-letter='".$letter."' data-wp-cta='' class='wp-cta-stat-row-".$vid." ".$post->ID. '-'. $conversion_rate ." status-".$v_status. "'><a ".$popup." ".$data_letter." class='wp-cta-letter' title='click to edit this variation' href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=".$vid."&action=edit'>" . $letter . "</a><span class='wp-cta-numbers'> <span class='wp-cta-impress-num'>" . $each_impression . "</span><span class='visit-text'>".$i_text." with</span><span class='wp-cta-con-num'>". $final_conversion . "</span> ".$c_text."</span><a ".$popup." ".$data_letter." class='cr-number cr-empty-".$conversion_rate."' href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=".$vid."&action=edit'>". $conversion_rate . "%</a></li>";				$i++;			}			echo "</ul>";						$winning_cr = max($cr_array); // best conversion rate						if ($winning_cr != 0) {			 echo "<span class='variation-winner-is'>".$post->ID. "-".$winning_cr."</span>";			}			//echo "Total Visits: " . $impressions;			//echo "Total Conversions: " . $conversions;		}		else		{			$notes = get_post_meta($post->ID,'wp-cta-variation-notes', true); // Get Notes			$cr = wp_cta_show_aggregated_stats("cr");			(($notes === "")) ? $notes = 'No notes' : $notes = $notes;			$popup = "data-notes=\"<span class='wp-cta-pop-description'>".$notes."</span><span class='wp-cta-pop-controls'><span class='wp-cta-pop-edit button-primary'><a href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=0&action=edit'>Edit This Varaition</a></span><span class='wp-cta-pop-preview button'><a title='Click to Preview this variation' class='thickbox' href='".$permalink."?wp-cta-variation-id=0&wp_cta_iframe_window=on&post_id=".$post->ID."&TB_iframe=true&width=640&height=703' target='_blank'>Preview This Varaition</a></span><span class='wp-cta-bottom-controls'><span class='wp-cta-delete-var-stats' data-letter='A' data-vid='0' rel='".$post->ID."'>Clear These Stats</span></span></span>\"";			echo "<ul class='wp-cta-varation-stat-ul'><li rel='' data-postid='".$post->ID."' data-letter='A' data-wp-cta=''><a ".$popup." data-letter=\"A\" class='wp-cta-letter' title='click to edit this variation' href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=0&action=edit'>A</a><span class='wp-cta-numbers'> <span class='wp-cta-impress-num'>" . wp_cta_show_aggregated_stats("impressions") . "</span><span class='visit-text'>visits with</span><span class='wp-cta-con-num'>". wp_cta_show_aggregated_stats("actions") . "</span> conversions</span><a class='cr-number cr-empty-".$cr."' href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=0&action=edit'>". $cr . "%</a></li></ul>";			echo "<div class='no-stats-yet'>No A/B Tests running for this landing page. <a href='/wp-admin/post.php?post=".$post->ID."&wp-cta-variation-id=1&action=edit&new-variation=1&wp-cta-message=go'>Start one</a></div>";								}	}	function wp_cta_show_aggregated_stats($type_of_stat) 	{		global $post;				$variations = get_post_meta($post->ID, 'wp-cta-ab-variations', true);		$variations = explode(",", $variations);				$impressions = 0;		$conversions = 0;				foreach ($variations as $vid) 		{			$each_impression = get_post_meta($post->ID,'wp-cta-ab-variation-impressions-'.$vid, true);			$each_conversion = get_post_meta($post->ID,'wp-cta-ab-variation-conversions-'.$vid, true);			(($each_conversion === "")) ? $final_conversion = 0 : $final_conversion = $each_conversion;			$impressions += get_post_meta($post->ID,'wp-cta-ab-variation-impressions-'.$vid, true);			$conversions += get_post_meta($post->ID,'wp-cta-ab-variation-conversions-'.$vid, true);					}				if ($type_of_stat === "cta_actions")		{			return $conversions;		} 		if ($type_of_stat === "cta_impressions") 		{			return $impressions;		}		if ($type_of_stat === "cta_cr") 		{			if ($impressions != 0) {			$conversion_rate = $conversions / $impressions;			} else {			$conversion_rate = 0;			}			$conversion_rate = round($conversion_rate,2) * 100; 			return $conversion_rate;		}					}	//populate collumsn for landing pages	function wp_cta_column($column)	{		global $post;		global $plugin_path;				if ("ID" == $column)		{			echo $post->ID;		}		else if ("title" == $column)		{		}		else if ("author" == $column)		{		}		else if ("date" == $column)		{		}		else if ("thumbnail-cta" == $column)		{					$template = get_post_meta($post->ID, 'wp-cta-selected-template', true);			$permalink = get_permalink($post->ID);			$datetime = the_modified_date('YmjH',null,null,false);			$permalink = wp_cta_ready_screenshot_url($permalink,$datetime);			$thumbnail = 'http://s.wordpress.com/mshots/v1/' . urlencode(esc_url($permalink)) . '?w=140';			echo "<a title='Click to Preview this variation' class='thickbox' href='".$permalink."?wp-cta-variation-id=0&wp_cta_iframe_window=on&post_id=".$post->ID."&TB_iframe=true&width=640&height=703' target='_blank'><img src=".$thumbnail."' style='width:150px;height:110px;' title='Click to Preview'></a>";					}		else		{			$wp_cta_impressions = wp_cta_get_page_views($post->ID);			$wp_cta_conversions = wp_cta_get_conversions($post->ID);			if ($wp_cta_conversions>0){                 				$wp_cta_cr = round(($wp_cta_conversions/$wp_cta_impressions), 2);			} else {				$wp_cta_cr = "0.0";			}		}		if ("cta_stats" == $column) 		{						$wp_cta_impressions =  apply_filters('wp_cta_col_impressions',$wp_cta_impressions);						wp_cta_show_stats_list();							}		elseif ("cta_impressions" == $column) 		{						echo wp_cta_show_aggregated_stats("cta_impressions");				}		elseif ("cta_actions" == $column)		{			echo wp_cta_show_aggregated_stats("cta_actions");		}		elseif ("cta_cr" == $column)  		{			 echo wp_cta_show_aggregated_stats("cta_cr") . "%";		}		elseif ("template" == $column) {			$template_used = get_post_meta($post->ID, 'wp-cta-selected-template', true);			echo $template_used;		}	}	// Add category sort to landing page list	function wp_cta_taxonomy_filter_restrict_manage_posts() {	    global $typenow;	    		if ($typenow === "wp-call-to-action") { 	    $post_types = get_post_types( array( '_builtin' => false ) );	    if ( in_array( $typenow, $post_types ) ) {	    	$filters = get_object_taxonomies( $typenow );	    		        foreach ( $filters as $tax_slug ) {	            $tax_obj = get_taxonomy( $tax_slug );	            (isset($_GET[$tax_slug])) ? $current = $_GET[$tax_slug] : $current = 0;	            wp_dropdown_categories( array(	                'show_option_all' => __('Show All '.$tax_obj->label ),	                'taxonomy' 	  => $tax_slug,	                'name' 		  => $tax_obj->name,	                'orderby' 	  => 'name',	                'selected' 	  => $current,	                'hierarchical' 	  => $tax_obj->hierarchical,	                'show_count' 	  => false,	                'hide_empty' 	  => true	            ) );		        }		    }			}	}		add_action( 'restrict_manage_posts', 'wp_cta_taxonomy_filter_restrict_manage_posts' );	function convert_wp_call_to_action_category_id_to_taxonomy_term_in_query($query) {		global $pagenow;		$qv = &$query->query_vars;		if( $pagenow=='edit.php' && isset($qv['wp_call_to_action_category']) && is_numeric($qv['wp_call_to_action_category']) ) {			$term = get_term_by('id',$qv['wp_call_to_action_category'],'wp_call_to_action_category');			$qv['wp_call_to_action_category'] = $term->slug;		}	}	add_filter('parse_query','convert_wp_call_to_action_category_id_to_taxonomy_term_in_query');  // Make these columns sortablefunction wp_cta_sortable_columns() {  return array(  	'title' => 'title',    'impressions'      => 'impressions',    'actions' => 'actions',    'cr'     => 'cr'  );}add_filter( 'manage_edit-wp-call-to-action_sortable_columns', 'wp_cta_sortable_columns' );  		//START Custom styling of post state (eg: pretty highlighting of post_status on landing pages page	//add_filter( 'display_post_states', 'wp_cta_custom_post_states' );	function wp_cta_custom_post_states( $post_states ) {	   foreach ( $post_states as &$state ){	   $state = '<span class="'.strtolower( $state ).' states">' . str_replace( ' ', '-', $state ) . '</span>';	   }	   return $post_states;	}	//***********ADDS 'CLEAR STATS' BUTTON TO POSTS EDITING AREA******************/	add_filter('post_row_actions', 'wp_cta_add_clear_tracking',10,2);	function wp_cta_add_clear_tracking($actions, $post) {			if ($post->post_type=='wp-call-to-action')			{				$actions['clear'] = '<a href="#clear-stats" id="wp_cta_clear_'.$post->ID.'" class="clear_stats" title="'				. esc_attr(__("Clear impression and conversion records", 'inboundnow_clear_stats'))				. '" >' .  __('Clear All Stats', 'Clear impression and conversion records') . '</a><span class="hover-description">Hover over the letters to the right for more options</span>';					}			return $actions;	}}/* Create Tab in Wp-lead */add_filter('wpl_lead_activity_tabs', 'show_cta_callback_function', 10, 1);        function show_cta_callback_function($nav_items)        {           $nav_items[] = array('id'=>'wpleads_lead_cta_click_tab','label'=>'CTA Clicks');        return $nav_items;        }// Add cta clicks to lead Activity Tabadd_action('wpleads_after_activity_log','show_cta_click_content');function show_cta_click_content() {     global $post; ?>    <div id="wpleads_lead_cta_click_tab" class='lead-activity'>        <h2>CTA's Clicked</h2>        <?php $events = get_post_meta($post->ID,'call_to_action_clicks', true);            $events_triggered = get_post_meta( $post->ID, 'call_to_action_clicks', TRUE );            //echo $events_triggered;           // echo $events;             $the_array = json_decode($events, true);            // echo "First id : ". $the_array[1]['id'] . "!"; // Get specific value            if ($events) {            $count = 1;            foreach($the_array as  $key=>$val)                {                    $id = $the_array[$count]['id'];                    $title = get_the_title($id);                   // $display_location = get_permalink($id);                    $date_raw = new DateTime($the_array[$count]['datetime']);                    //date_format($date, 'F jS, Y \a\t g:ia (l)')                    $date_of_conversion = $date_raw->format('F jS, Y \a\t g:ia (l)');                    $clean_date = $date_raw->format('Y-m-d H:i:s');                    //echo $count . ": ". $the_array[$count]['datetime'] . "!<br>";                  //  echo "<div data-date='$clean_date' class='recent-conversion-item'><span class='lead-item-num'>".$count.".</span> <a href='' id='lead-session-".$count."' rel='".$count."' target='_blank'>{$title}</a><span class='conversion-date'>".$date_of_conversion."</span></div>";                     echo '<div class="lead-timeline recent-conversion-item cta-tracking-item" data-date="'.$clean_date.'">                                <a class="lead-timeline-img" href="#non">                                   <!--<i class="lead-icon-target"></i>-->                                </a>                                                                    <div class="lead-timeline-body">                                    <div class="lead-event-text">                                      <p><span class="lead-item-num">'.$count.'. </span><span class="lead-helper-text">Call to Action Click: </span><a href="#">'.$title.'</a><span class="conversion-date">'.$date_of_conversion.'</span></p>                                    </div>                                </div>                            </div>';                        foreach ($val as $key => $value) {                            //echo $key . "=" . $value;                        }                    $count++;                }            } else {                echo "<span id='wpl-message-none'>No Call to Action Clicks found!</span>";            }               ?>    </div><?php}