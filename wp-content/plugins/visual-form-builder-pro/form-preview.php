<?php
define( 'IFRAME_REQUEST', true );
define( 'WP_USE_THEMES', false );

// Include the WP header so we can use WP functions and run a PHP page
function vfb_get_wp_root_path(){
    $base = dirname( __FILE__ );
    $path = false;
    
    if ( @file_exists( dirname( dirname( $base ) ) . '/wp-load.php' ) )
        $path = dirname( dirname( $base ) ) . '/wp-load.php';
    else {
	    if ( @file_exists( dirname( dirname( dirname( $base ) ) ) . '/wp-load.php' ) )
	        $path = dirname( dirname( dirname( $base ) ) ) . '/wp-load.php';
	    else
	    	$path = false;
	}
    
    if ( $path != false )
        $path = str_replace( '\\', '/', $path );
    
    return $path;
}

require_once( vfb_get_wp_root_path() );
	
// If you don't have permission, get lost
if ( !current_user_can( 'vfb_edit_forms' ) )
	wp_die('<p>'.__('You do not have sufficient permissions to view the preview for this site.').'</p>');

// Let's roll.
@header('Content-Type: ' . get_option('html_type') . '; charset=' . get_option('blog_charset'));

function visual_form_builder_pro_preview_scripts(){
	wp_enqueue_script( 'jquery-form-validation', 'https://ajax.aspnetcdn.com/ajax/jquery.validate/1.9/jquery.validate.min.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'vfb-jquery-ui', 'https://ajax.aspnetcdn.com/ajax/jquery.ui/1.9.0/jquery-ui.min.js', array( 'jquery' ), '', true );
	wp_enqueue_script( 'visual-form-builder-validation', plugins_url( "visual-form-builder-pro/js/vfb-validation.js" ) , array( 'jquery', 'jquery-form-validation' ), '', true );
	wp_enqueue_script( 'visual-form-builder-metadata', plugins_url( 'visual-form-builder-pro/js/jquery.metadata.js' ) , array( 'jquery', 'jquery-form-validation' ), '', true );
	wp_enqueue_script( 'farbtastic-js', admin_url( 'js/farbtastic.js' ) );
	
	wp_localize_script( 'visual-form-builder-validation', 'VfbAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	
	wp_enqueue_style( 'visual-form-builder-css', apply_filters( 'visual-form-builder-css', plugins_url( 'visual-form-builder-pro/css/visual-form-builder.css' ) ) );
	wp_enqueue_style( 'vfb-date-picker-css', apply_filters( 'vfb-date-picker-css', plugins_url( 'visual-form-builder-pro/css/smoothness/jquery-ui-1.9.2.min.css' ) ) );
	wp_enqueue_style( 'farbtastic' );
	
	wp_enqueue_script( 'visual-form-builder-quicktags', plugins_url( 'visual-form-builder-pro/js/js_quicktags.js' ) );
}


add_action( 'wp_enqueue_scripts', 'visual_form_builder_pro_preview_scripts' );
add_filter('show_admin_bar', '__return_false');

$vfb_countries = array( "", "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", "Congo", "Costa Rica", "Cote d\'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia, The", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepa", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe" );

?>
<?php
$vfb_post = '';
// Run Create User add-on
if ( class_exists( 'VFB_Pro_Create_Post' ) )
	$vfb_post = new VFB_Pro_Create_Post();

global $wpdb;

$form_table_name = $wpdb->prefix . 'vfb_pro_forms';
$field_table_name = $wpdb->prefix . 'vfb_pro_fields';

// Add JavaScript files to the front-end, only once
//if ( !$this->add_scripts )
	//$this->scripts();

// Get form id.  Allows use of [vfb id=1] or [vfb 1]
$form_id = isset( $_REQUEST['form'] ) ? absint( $_REQUEST['form'] ) : 0;

$output = '';
//$output = '';
$open_fieldset = $open_section = $open_page = false;

// Default the submit value
$submit = 'Submit';

// Get forms
$order = sanitize_sql_orderby( 'form_id DESC' );			
$forms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $form_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );

// Get fields
$order_fields = sanitize_sql_orderby( 'field_sequence ASC' );
$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $field_table_name WHERE form_id = %d ORDER BY $order_fields", $form_id ) );

$page_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( field_type ) + 1 FROM $field_table_name WHERE form_id = %d AND field_type = 'page-break';", $form_id ) );

// Setup count for fieldset and ul/section class names
$count = 1;
$page_num = 0;
$page = $total_page = $verification = '';

foreach ( $forms as $form ) :
	$form_title = esc_html( $form->form_title );
	$label_alignment = ( $form->form_label_alignment !== '' ) ? " $form->form_label_alignment" : '';
	
	// Set a default for displaying the verification section
	$display_verification = apply_filters( 'vfb_display_verification', $form->form_verification );
	
	// Allow the default action to be hooked into
	$action = apply_filters( 'vfb_form_action', '', $form_id );
	
	$output = '<div class="visual-form-builder-container"><form id="' . $form->form_key . '" class="visual-form-builder' . $label_alignment . '" method="post" enctype="multipart/form-data" action="' . $action . '">
				<input type="hidden" name="form_id" value="' . $form->form_id . '" />';
	$output .= wp_nonce_field( 'visual-form-builder-nonce', '_wpnonce', false, false );

	foreach ( $fields as $field ) {
		// If field is required, build the span and add setup the 'required' class
		$required_span 	= ( !empty( $field->field_required ) && $field->field_required === 'yes' ) ? ' <span>*</span>' : '';
		$required 		= ( !empty( $field->field_required ) && $field->field_required === 'yes' ) ? ' required' : '';
		$validation 	= ( !empty( $field->field_validation ) ) ? " $field->field_validation" : '';
		$css 			= ( !empty( $field->field_css ) ) ? " $field->field_css" : '';
		$id_attr 		= 'vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id;
		$size			= ( !empty( $field->field_size ) ) ? " vfb-$field->field_size" : '';
		$layout 		= ( !empty( $field->field_layout ) ) ? " vfb-$field->field_layout" : '';
		$default 		= ( !empty( $field->field_default ) ) ? html_entity_decode( stripslashes( $field->field_default ) ) : '';
		$conditional	= ( !empty( $field->field_rule ) ) ? ' vfb-conditional' : '';
		
		$conditional_show = '';
		if ( $field->field_rule ) {
			$field_rule = unserialize( $field->field_rule );
			$conditional_show = ( 'show' == $field_rule['conditional_show'] ) ? ' vfb-conditional-hide' : '';
		}
		
		// Close each section
		if ( $open_section == true ) {
			// If this field's parent does NOT equal our section ID
			if ( $sec_id && $sec_id !== $field->field_parent ) {
				$output .= '</div><div class="vfb-clear"></div>';
				$open_section = false;
			}
		}
		
		// Force an initial fieldset and display an error message to strongly encourage user to add one
		if ( $count === 1 && $field->field_type !== 'fieldset' ) {
			$output .= '<fieldset class="fieldset"><div class="legend" style="background-color:#FFEBE8;border:1px solid #CC0000;"><h3>Oops! Missing Fieldset</h3><p style="color:black;">If you are seeing this message, it means you need to <strong>add a Fieldset to the beginning of your form</strong>. Your form may not function or display properly without one.</p></div><ul class="section section-' . $count . '">';
			
			$count++;
		}
		
		if ( $field->field_type == 'fieldset' ) {
			// Close each fieldset
			if ( $open_fieldset == true )
				$output .= '</ul><br /></fieldset>';
			
			if ( $open_page == true && $page !== '' )
				$open_page = false;
									
			$output .= '<fieldset class="vfb-fieldset vfb-fieldset-' . $count . ' ' . $field->field_key . $css . $page . $conditional . $conditional_show . '" id="' . $id_attr . '"><div class="vfb-legend"><h3>' . stripslashes( $field->field_name ) . '</h3></div><ul class="vfb-section vfb-section-' . $count . '">';
			$open_fieldset = true;
			$count++;
		}
		elseif ( $field->field_type == 'section' ) {
			$output .= '<div class="vfb-section-div vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id . ' ' . $css . $conditional . $conditional_show . '"><h4>' . stripslashes( $field->field_name ) . '</h4>';
			
			// Save section ID for future comparison
			$sec_id = $field->field_id;
			$open_section = true;
		}
		elseif ( $field->field_type == 'page-break' ) {
			$page_num += 1;
			
			$total_page = '<span class="vfb-page-counter">' . $page_num . ' / ' . $page_count . '</span>';
			
			$output .= '<li class="vfb-item vfb-item-' . $field->field_type . '" id="item-' . $id_attr . '"><a href="#" id="page-' . $page_num . '" class="vfb-page-next' . $css . '">' . stripslashes( $field->field_name ) . '</a> ' . $total_page . '</li>';
			$page = " vfb-page page-$page_num";
			$open_page = true;
		}
		elseif ( !in_array( $field->field_type, array( 'verification', 'secret', 'submit' ) ) ) {
			
			$columns_choice = ( !empty( $field->field_size ) && in_array( $field->field_type, array( 'radio', 'checkbox' ) ) ) ? " vfb-$field->field_size" : '';
			
			if ( $field->field_type !== 'hidden' ) {
				$id_attr = 'vfb-' . esc_html( $field->field_key ) . '-' . $field->field_id;
				$output .= '<li class="vfb-item vfb-item-' . $field->field_type . $columns_choice . $layout . $conditional . $conditional_show . '" id="item-' . $id_attr . '"><label for="' . $id_attr . '" class="vfb-desc">'. stripslashes( $field->field_name ) . $required_span . '</label>';
			}
		}
		elseif ( in_array( $field->field_type, array( 'verification', 'secret' ) ) ) {
			
			if ( $field->field_type == 'verification' )
				$verification .= '<fieldset class="vfb-fieldset vfb-fieldset-' . $count . ' ' . $field->field_key . $css . $page . '" id="' . $id_attr . '"><div class="vfb-legend"><h3>' . stripslashes( $field->field_name ) . '</h3></div><ul class="vfb-section vfb-section-' . $count . '">';
			
			if ( $field->field_type == 'secret' ) {
				// Default logged in values
				$logged_in_display = '';
				$logged_in_value = '';

				// If the user is logged in, fill the field in for them
				if ( is_user_logged_in() ) {
					// Hide the secret field if logged in
					$logged_in_display = ' style="display:none;"';
					$logged_in_value = 14;
					
					// Get logged in user details
					$user = wp_get_current_user();
					$user_identity = ! empty( $user->ID ) ? $user->display_name : '';
					
					// Display a message for logged in users
					$verification .= '<li class="vfb-item" id="' . $id_attr . '">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. Verification not required.', 'visual-form-builder-pro' ), admin_url( 'profile.php' ), $user_identity ) . '</li>';
				}
				
				$validation = ' {digits:true,maxlength:2,minlength:2}';
				$verification .= '<li class="vfb-item vfb-item-' . $field->field_type . '"' . $logged_in_display . '><label for="' . $id_attr . '" class="vfb-desc">'. stripslashes( $field->field_name ) . $required_span . '</label>';
				
				// Set variable for testing if required is Yes/No
				if ( $required == '' )
					$verification .= '<input type="hidden" name="_vfb-required-secret" value="0" />';
				
				$verification .= '<input type="hidden" name="_vfb-secret" value="vfb-' . $field->field_id . '" />';
				
				if ( !empty( $field->field_description ) )
					$verification .= '<span><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $logged_in_value . '" class="vfb-text ' . $size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				else
					$verification .= '<input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $logged_in_value . '" class="vfb-text ' . $size . $required . $validation . $css . '" />';
			}
		}
		
		switch ( $field->field_type ) {
			case 'text' :
			case 'email' :
			case 'url' :
			case 'currency' :
			case 'number' :
			case 'phone' :
			case 'ip-address' :
			case 'credit-card' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text ' . $size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				else
					$output .= '<input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text ' . $size . $required . $validation . $css . '" />';
					
			break;
			
			case 'textarea' :
				
				$options = maybe_unserialize( $field->field_options );
				$min = ( isset( $options['min'] ) ) ? $options['min'] : '';
				$max = ( isset( $options['max'] ) ) ? $options['max'] : '';
				
				$words = $words_display = '';
				
				// Initial count if default set
				$word_count = str_word_count( $default );
				
				// Setup word count messages
				$words_message = array(
					'range'	=> sprintf( __( 'Must be between %s and %s words. Total words: %s', 'visual-form-builder-pro' ), "<strong>$min</strong>", "<strong>$max</strong>", "<strong class='vfb-word-count-total'>$word_count</strong>" ),
					'max'	=> sprintf( __( 'Maximum words allowed: %s. Total words: %s', 'visual-form-builder-pro' ), "<strong>$max</strong>", "<strong class='vfb-word-count-total'>$word_count</strong>" ),
					'min'	=> sprintf( __( 'Minimum words allowed: %s. Total words: %s', 'visual-form-builder-pro' ), "<strong>$min</strong>", "<strong class='vfb-word-count-total'>$word_count</strong>" )
				);
				
				$words_message = apply_filters( 'vfb_word_count_message', $words_message, $min, $max, $word_count, $form_id );
				
				// If Min and Max words are set, use Range words
				if ( !empty( $min ) && !empty( $max ) ) {
					$words = ' {rangeWords:[' . $min . ',' . $max . ']} vfb-textarea-word-count';
					$words_display = "<label class='vfb-word-count'>{$words_message['range']}</label>";
				}
				// If Min is empty and Max is set, use Max words
				elseif ( empty( $min ) && !empty( $max ) ) {
					$words = ' {maxWords:[' . $max . ']} vfb-textarea-word-count';
					$words_display = "<label class='vfb-word-count'>{$words_message['max']}</label>";
				}
				// If Min is set and Max is empty, use Min words
				elseif ( !empty( $min ) && empty( $max ) ) {
					$words = ' {minWords:[' . $min . ']} vfb-textarea-word-count';
					$words_display = "<label class='vfb-word-count'>{$words_message['min']}</label>";
				}
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				
				$output .= '<textarea name="vfb-' . $field->field_id . '" id="' . $id_attr . '" class="vfb-textarea ' . $size . $required . $css . $words . '">' . $default . '</textarea>' . $words_display;
					
			break;
			
			case 'select' :
				if ( !empty( $field->field_description ) )
					$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
						
				$output .= '<select name="vfb-' . $field->field_id . '" id="' . $id_attr . '" class="vfb-select ' . $size . $required . $css . '">';
				
				$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
				
				// Loop through each option and output
				foreach ( $options as $option => $value ) {
					$output .= '<option value="' . trim( stripslashes( $value ) ) . '"' . selected( $default, ++$option, 0 ) . '>'. trim( stripslashes( $value ) ) . '</option>';
				}
				
				$output .= '</select>';
				
			break;
			
			case 'radio' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				
				$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
				
				$output .= '<div>';
				
				// Loop through each option and output
				foreach ( $options as $option => $value ) {
					// Increment the base index by one to match $default
					$option++;
					$output .= '<span>
									<input type="radio" name="vfb-' . $field->field_id . '" id="' . $id_attr . '-' . $option . '" value="'. trim( stripslashes( $value ) ) . '" class="vfb-radio' . $required . $css . '"' . checked( $default, $option, 0 ) . ' />'. 
								' <label for="' . $id_attr . '-' . $option . '" class="vfb-choice">' . trim( stripslashes( $value ) ) . '</label>' .
								'</span>';
				}
				
				// Get 'Allow Other'
				$field_options_other = maybe_unserialize( $field->field_options_other );
				
				// Display 'Other' field
				if ( isset( $field_options_other['setting'] ) && 1 == $field_options_other['setting'] ) {
					$option++;
					$output .= '<span>
						<input type="radio" name="vfb-' . $field->field_id . '" id="' . $id_attr . '-' . $option . '" value="'. trim( stripslashes( $value ) ) . '" class="vfb-radio"' . checked( $default, $option, 0 ) . ' />'. 
					' <label for="' . $id_attr . '-' . $option . '" class="vfb-choice">' . trim( stripslashes( $field_options_other['other'] ) ) . '</label>' .
					' <input type ="text" name="vfb-' . $field->field_id . '[other]" id="' . $id_attr . '-' . $option . '" value="" class="vfb-text vfb-other">' . 
					'</span>';
				}
				
				$output .= '<div style="clear:both"></div></div>';
				
			break;
			
			case 'checkbox' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				
				$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
				
				$output .= '<div>';

				// Loop through each option and output
				foreach ( $options as $option => $value ) {
					// Increment the base index by one to match $default
					$option++;
					
					$output .= '<span><input type="checkbox" name="vfb-' . $field->field_id . '[]" id="' . $id_attr . '-' . $option . '" value="'. trim( stripslashes( $value ) ) . '" class="vfb-checkbox' . $required . $css . '"' . checked( $default, $option, 0 ) . ' />'. 
						' <label for="' . $id_attr . '-' . $option . '" class="vfb-choice">' . trim( stripslashes( $value ) ) . '</label></span>';
				}
				
				$output .= '<div style="clear:both"></div></div>';
			
			break;
			
			case 'address' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				
				$address_labels = array(
				    'address'    => __( 'Address', 'visual-form-builder-pro' ),
				    'address-2'  => __( 'Address Line 2', 'visual-form-builder-pro' ),
				    'city'       => __( 'City', 'visual-form-builder-pro' ),
				    'state'      => __( 'State / Province / Region', 'visual-form-builder-pro' ),
				    'zip'        => __( 'Postal / Zip Code', 'visual-form-builder-pro' ),
				    'country'    => __( 'Country', 'visual-form-builder-pro' )
				);
				
				$address_labels = apply_filters( 'vfb_address_labels', $address_labels, $form_id );
				
				$output .= '<div>
					<span class="vfb-full">
						<input type="text" name="vfb-' . $field->field_id . '[address]" id="' . $id_attr . '-address" maxlength="150" class="vfb-text vfb-medium' . $required . $css . '" />
						<label for="' . $id_attr . '-address">' . $address_labels['address'] . '</label>
					</span>
					<span class="vfb-full">
						<input type="text" name="vfb-' . $field->field_id . '[address-2]" id="' . $id_attr . '-address-2" maxlength="150" class="vfb-text vfb-medium' . $css . '" />
						<label for="' . $id_attr . '-address-2">' . $address_labels['address-2'] . '</label>
					</span>
					<span class="vfb-left">
						<input type="text" name="vfb-' . $field->field_id . '[city]" id="' . $id_attr . '-city" maxlength="150" class="vfb-text vfb-medium' . $required . $css . '" />
						<label for="' . $id_attr . '-city">' . $address_labels['city'] . '</label>
					</span>
					<span class="vfb-right">
						<input type="text" name="vfb-' . $field->field_id . '[state]" id="' . $id_attr . '-state" maxlength="150" class="vfb-text vfb-medium' . $required . $css . '" />
						<label for="' . $id_attr . '-state">' . $address_labels['state'] . '</label>
					</span>
					<span class="vfb-left">
						<input type="text" name="vfb-' . $field->field_id . '[zip]" id="' . $id_attr . '-zip" maxlength="150" class="vfb-text vfb-medium' . $required . $css . '" />
						<label for="' . $id_attr . '-zip">' . $address_labels['zip'] . '</label>
					</span>
					<span class="vfb-right">
					<select class="vfb-select' . $required . $css . '" name="vfb-' . $field->field_id . '[country]" id="' . $id_attr . '-country">';
					
					$default = apply_filters( 'vfb_default_country', $default );
					
					foreach ( $vfb_countries as $country ) {
						$output .= "<option value=\"$country\" " . selected( $default, $country, 0 ) . ">$country</option>";
					}
					
					$output .= '</select>
						<label for="' . $id_attr . '-country">' . $address_labels['country'] . '</label>
					</span>
				</div>';

			break;
			
			case 'date' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text vfb-date-picker ' . $size . $required . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				else
					$output .= '<input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text vfb-date-picker ' . $size . $required . $css . '" />';
				
			break;
			
			case 'time' :
				if ( !empty( $field->field_description ) )
					$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';

				// Get the time format (12 or 24)
				$time_format = str_replace( 'time-', '', $validation );
				
				$time_format = apply_filters( 'vfb_time_format', $time_format, $form_id );
				
				// Set whether we start with 0 or 1 and how many total hours
				$hour_start = ( $time_format == '12' ) ? 1 : 0;
				$hour_total = ( $time_format == '12' ) ? 12 : 23;
				
				// Hour
				$output .= '<span class="vfb-time"><select name="vfb-' . $field->field_id . '[hour]" id="' . $id_attr . '-hour" class="vfb-select' . $required . $css . '">';
				for ( $i = $hour_start; $i <= $hour_total; $i++ ) {
					// Add the leading zero
					$hour = ( $i < 10 ) ? "0$i" : $i;
					$output .= "<option value='$hour'>$hour</option>";
				}
				$output .= '</select><label for="' . $id_attr . '-hour">HH</label></span>';
				
				// Minute
				$output .= '<span class="vfb-time"><select name="vfb-' . $field->field_id . '[min]" id="'. $id_attr . '-min" class="vfb-select' . $required . $css . '">';
				
				$total_mins = apply_filters( 'vfb_time_min_total', 55, $form_id );
				$min_interval = apply_filters( 'vfb_time_min_interval', 5, $form_id );
				
				for ( $i = 0; $i <= $total_mins; $i += $min_interval ) {
					// Add the leading zero
					$min = ( $i < 10 ) ? "0$i" : $i;
					$output .= "<option value='$min'>$min</option>";
				}
				$output .= '</select><label for="' . $id_attr . '-min">MM</label></span>';
				
				// AM/PM
				if ( $time_format == '12' )
					$output .= '<span class="vfb-time"><select name="vfb-' . $field->field_id . '[ampm]" id="' . $id_attr . '-ampm" class="vfb-select' . $required . $css . '"><option value="AM">AM</option><option value="PM">PM</option></select><label for="' . $id_attr . '-ampm">AM/PM</label></span>';
				$output .= '<div class="clear"></div>';		
			break;
			
			case 'html' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';

				$output .= '<script type="text/javascript">edToolbar("' . $id_attr . '");</script>';
				$output .= '<textarea name="vfb-' . $field->field_id . '" id="' . $id_attr . '" class="vfb-textarea vfbEditor ' . $size . $required . $css . '">' . $default . '</textarea>';
					
			break;
			
			case 'file-upload' :
				
				$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
				$accept = ( !empty( $options[0] ) ) ? " {accept:'$options[0]'}" : '';

				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="file" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text ' . $size . $required . $validation . $accept . $css . '"' . ' /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				else
					$output .= '<input type="file" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text ' . $size . $required . $validation . $accept . $css . '"' . ' />';
			
						
			break;
			
			case 'instructions' :
				
				$output .= html_entity_decode( stripslashes( $field->field_description ) );
			
			break;
			
			case 'name' :
				$format = maybe_unserialize( $field->field_options );
				
				// Setup word count messages
				$labels = array(
					'title'    => __( 'Title', 'visual-form-builder-pro' ),
					'first'    => __( 'First', 'visual-form-builder-pro' ),
					'last'     => __( 'Last', 'visual-form-builder-pro' ),
					'suffix'   => __( 'Suffix', 'visual-form-builder-pro' ),
				);
				
				$labels = apply_filters( 'vfb_name_labels', $labels, $form_id );
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				
				// If Min and Max words are set, use Range words
				if ( 'normal' == $format[0] ) {
					$output .= '<span class="vfb-name-normal"><input type="text" name="vfb-' . $field->field_id . '[first]" id="' . $id_attr . '-first" value="' . $default . '" class="vfb-text ' . $required . $validation . $css . '" /><label for="vfb-' . $field->field_id . '-first">' . $labels['first'] . '</label></span>' . 
							'<span class="vfb-name-normal"><input type="text" name="vfb-' . $field->field_id . '[last]" id="' . $id_attr . '" value="' . $default . '" class="vfb-text ' . $required . $validation . $css . '" /><label for="vfb-' . $field->field_id . '[last]">' . $labels['last'] . '</label></span>';
				}
				else {
					$output .= '<span class="vfb-name-extras"><input type="text" name="vfb-' . $field->field_id . '[title]" id="' . $id_attr . '-title" value="' . $default . '" class="vfb-text ' . $required . $validation . $css . '" size="4" /><label for="' . $id_attr . '-title">' . $labels['title'] . '</label></span>' .
							'<span class="vfb-name-extras"><input type="text" name="vfb-' . $field->field_id . '[first]" id="' . $id_attr . '-first" value="' . $default . '" class="vfb-text ' . $required . $validation . $css . '" size="14" /><label for="' . $id_attr . '-first">' . $labels['first'] . '</label></span>' . 
							'<span class="vfb-name-extras"><input type="text" name="vfb-' . $field->field_id . '[last]" id="' . $id_attr . '-last" value="' . $default . '" class="vfb-text ' . $required . $validation . $css . '" size="14" /><label for="' . $id_attr . '-last">' . $labels['last'] . '</label></span>' .
							'<span class="vfb-name-extras"><input type="text" name="vfb-' . $field->field_id . '[suffix]" id="' . $id_attr . '-suffix" value="' . $default . '" class="vfb-text ' . $required . $validation . $css . '" size="3" /><label for="' . $id_attr . '-suffix">' . $labels['suffix'] . '</label></span>';
				}
								
			break;

			case 'username' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text username ' . $size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				else
					$output .= '<input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text username ' . $size . $required . $validation . $css . '" />';
					
			break;
			
			case 'password' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="password" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text password ' . $size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span><div class="password-meter"><div class="password-meter-message">Password Strength</div></div>';
				else
					$output .= '<input type="password" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text password ' . $size . $required . $validation . $css . '" /><div class="password-meter"><div class="password-meter-message">Password Strength</div></div>';
				
			break;
			
			case 'hidden' :
				$val = '';
				
				// If the options field isn't empty, unserialize and build array
				if ( !empty( $field->field_options ) ) {
					if ( is_serialized( $field->field_options ) )
						$opts_vals = unserialize( $field->field_options );
						
						switch ( $opts_vals[0] ) {
							case 'form_id' :
								$val = $form_id;
							break;
							case 'form_title' :
								$val = stripslashes( $form->form_title );
							break;
							case 'ip' :
								$val = $_SERVER['REMOTE_ADDR'];
							break;
							case 'uid' :
								$val = uniqid();
							break;
							case 'post_id' :
								$val = $form_id;
							break;
							case 'post_title' :
								$val = get_the_title();
							break;
							case 'custom' :
								$val = trim( stripslashes( $opts_vals[1] ) );
							break;
						}
				}
				
				$output .= '<input type="hidden" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $val . '" class="vfb-text ' . $size . $required . $validation . $css . '" />';
			break;
			
			case 'color-picker' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="#' . $default . '" class="vfb-text color ' . $size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span><div id="color-' . esc_html( $field->field_key )  . '-' . $field->field_id . '"class="colorPicker"></div>';
				else
					$output .= '<input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="#' . $default . '" class="vfb-text color ' . $size . $required . $validation . $css . '" /><div id="color-' . esc_html( $field->field_key )  . '-' . $field->field_id . '"class="colorPicker"></div>';
				
			break;
			
			case 'autocomplete' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text auto ' . $size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				else
					$output .= '<input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text auto ' . $size . $required . $validation . $css . '" />';
				
			break;
			
			case 'min' :
			case 'max' :
				// If the options field isn't empty, unserialize and build array
				if ( !empty( $field->field_options ) ) {
					if ( is_serialized( $field->field_options ) )
						$opts_vals = unserialize( $field->field_options );
				}
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" ' . $field->field_type . '="' . $opts_vals[0] . '" class="vfb-text ' . $size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				else
					$output .= '<input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" ' . $field->field_type . '="' . $opts_vals[0] . '" class="vfb-text ' . $size . $required . $validation . $css . '" />';

			break;
			
			case 'range' :
				// If the options field isn't empty, unserialize and build array
				if ( !empty( $field->field_options ) ) {
					if ( is_serialized( $field->field_options ) ) {
						$opts_vals = unserialize( $field->field_options );
						$min = $opts_vals[0];
						$max = $opts_vals[1];
					}
				}
				
				if ( !empty( $field->field_description ) )
					$output .= '<span><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text {range:[' . $min . ',' . $max . ']} ' . $size . $required . $validation . $css . '" /><label>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</label></span>';
				else
					$output .= '<input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text {range:[' . $min . ',' . $max . ']} ' . $size . $required . $validation . $css . '" />';

			break;
			
			case 'submit' :
				
				$submit = '<li class="vfb-item vfb-item-submit" id="' . $id_attr . '"><input type="submit" name="visual-form-builder-submit" value="' . stripslashes( $field->field_name ) . '" class="vfb-submit' . $css . '" id="sendmail" /></li>';
				$output .= ( false == $display_verification ) ? $submit : '';
				
			break;
			
			default:
				echo '';
				
				if ( class_exists( 'VFB_Pro_Create_Post' ) && method_exists( $vfb_post, 'form_output' ) ) {
					$create_post_vars = array(
						'field_type'	=> $field->field_type,
						'field_id'		=> $field->field_id,
						'id_attr'		=> $id_attr,
						'default'		=> $default,
						'size'			=> $size,
						'required'		=> $required,
						'validation'	=> $validation,
						'css'			=> $css,
						'description'	=> $field->field_description,
						'options'		=> $field->field_options
					);
					
					$output .= $vfb_post->form_output( $create_post_vars );
				}

			break;
		}
		if ( isset( $option ) )
			unset( $option );
		// Closing </li>
		$output .= ( !in_array( $field->field_type , array( 'verification', 'secret', 'submit', 'fieldset', 'section', 'hidden', 'page-break' ) ) ) ? '</li>' : '';
	}
	
	// Close user-added fields
	$output .= '</ul><br /></fieldset>';
	
	if ( $total_page !== '' )
		$total_page = '<span class="vfb-page-counter">' . $page_count . ' / ' . $page_count . '</span>';
	
	// Make sure the verification displays even if they have not updated their form
	/*if ( $verification == '' ) {
		$verification = '<fieldset class="vfb-fieldset vfb-verification">
				<div class="vfb-legend">
					<h3>' . __( 'Verification' , 'visual-form-builder-pro') . '</h3>
				</div>
				<ul class="vfb-section vfb-section-' . $count . '">
					<li class="vfb-item vfb-item-text">
						<label for="vfb-secret" class="vfb-desc">' . __( 'Please enter any two digits with' , 'visual-form-builder-pro') . ' <strong>' . __( 'no' , 'visual-form-builder-pro') . '</strong> ' . __( 'spaces (Example: 12)' , 'visual-form-builder-pro') . '<span>*</span></label>
						<div>
							<input type="text" name="vfb-secret" id="vfb-secret" class="vfb-text vfb-medium" />
						</div>
					</li>';
	}*/
	
	// Display the SPAM verification
	if ( true == $display_verification ) {
		// Output our security test
		$output .= $submit . $total_page;
	}
	
	// Close the form out
	$output .= '</form></div>';
		
endforeach;
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php echo get_option('blog_charset'); ?>" />
<title><?php echo $form_title; ?> &lsaquo; Form Preview &#8212; Visual Form Builder Pro</title>
<?php
wp_head();

$body_css = ( isset( $_REQUEST['preview'] ) && 1 == $_REQUEST['preview'] ) ? 'width:50%;margin:0 auto;' : 'width:100%;margin-top:-20px;margin-left:0;margin-right:0;';

?>
<style type="text/css">
html{margin-top:0 !important;overflow: auto;}
body{<?php echo $body_css; ?>height:100%;font-family: sans-serif;margin-bottom:10px;overflow: auto;}
</style>
</head>
<body>

<?php
echo $output;

wp_footer();
?>
</body>
</html>