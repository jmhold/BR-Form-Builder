<?php
$vfb_post = $vfb_payments = '';
// Run Create User add-on
if ( class_exists( 'VFB_Pro_Create_Post' ) )
	$vfb_post = new VFB_Pro_Create_Post();

// Run Payments add-on
if ( class_exists( 'VFB_Pro_Payments' ) )
	$vfb_payments = new VFB_Pro_Payments();

global $wpdb;

// Extract shortcode attributes, set defaults
extract( shortcode_atts( array(
	'id' => ''
	), $atts ) 
);

// Add JavaScript files to the front-end, only once
if ( !$this->add_scripts )
	$this->scripts();

// Get form id.  Allows use of [vfb id=1] or [vfb 1]
$form_id = ( isset( $id ) && !empty( $id ) ) ? $id : key( $atts );

//$output = '';
$open_fieldset = $open_section = $open_page = false;

// Default the submit value
$submit = 'Submit';

// If form is submitted, show success message, otherwise the form
if ( isset( $_REQUEST['visual-form-builder-submit'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'visual-form-builder-nonce' ) && isset( $_REQUEST['form_id'] ) && $_REQUEST['form_id'] == $form_id ) {
	$output = $this->confirmation();
	if ( !apply_filters( 'vfb_prepend_confirmation', false, $form_id ) )
		return;
}

// Get forms
$order = sanitize_sql_orderby( 'form_id DESC' );			
$forms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );

// Get fields
$order_fields = sanitize_sql_orderby( 'field_sequence ASC' );
$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d ORDER BY $order_fields", $form_id ) );

$page_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( field_type ) + 1 FROM $this->field_table_name WHERE form_id = %d AND field_type = 'page-break';", $form_id ) );

$entries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->entries_table_name WHERE form_id = %d", $form_id ) );

$rules = $this->get_conditional_fields( $form_id );

// Setup count for fieldset and ul/section class names
$count = 1;
$page_num = 0;
$page = $total_page = $verification = '';

foreach ( $forms as $form ) :
	$entries_allowed 	= !empty( $form->form_entries_allowed ) ? $form->form_entries_allowed : false;
	$entries_schedule 	= maybe_unserialize( $form->form_entries_schedule );
	$current_time	 	= current_time( 'timestamp' );
	$schedule_start		= strtotime( $entries_schedule['start'] );
	$schedule_end		= strtotime( $entries_schedule['end'] );
			
	$submissions_off = '<p class="vfb-entries-allowed">' . __( 'Sorry, but this form is no longer accepting submissions.', 'visual-form-builder-pro' ) . '</p>';
	
	// Check for # entries allowed and hide form, if needed
	if ( $entries_allowed && ( $entries_count >= $entries_allowed ) ) :
		$output = $submissions_off;
	elseif ( $schedule_start && ( $current_time <= $schedule_start ) ) :
		$output = $submissions_off;
	elseif ( $schedule_end && ( $current_time >= $schedule_end ) ) :
		$output = $submissions_off;
	// Display form as normal
	else :
	$label_alignment = ( $form->form_label_alignment !== '' ) ? " $form->form_label_alignment" : '';
	
	// Set a default for displaying the verification section
	$display_verification = apply_filters( 'vfb_display_verification', $form->form_verification );
	
	// Allow the default action to be hooked into
	$action = apply_filters( 'vfb_form_action', '', $form_id );
	
	$output .= '<div class="row-fluid"><form id="' . $form->form_key . '" class="visual-form-builder' . $label_alignment . '" method="post" enctype="multipart/form-data" action="' . $action . '">
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
				$output .= '</div></fieldset>';
			
			if ( $open_page == true && $page !== '' )
				$open_page = false;
									
			$output .= '<fieldset class="widget span12 clearfix" id="' . $id_attr . '"><div class="widget-header"><span><i class="icon-align-left"></i>  ' . stripslashes( $field->field_name ) . '</span></div><div class="widget-content">';
			$open_fieldset = true;
			$count++;
		}
		elseif ( $field->field_type == 'section' ) {
			$output .= '<div class="section">';
			//$output .= '<h4>' . stripslashes( $field->field_name ) . '</h4>';
			
			// Save section ID for future comparison
			$sec_id = $field->field_id;
			$open_section = true;
		}
		elseif ( $field->field_type == 'page-break' ) {
			$page_num += 1;
			
			$total_page = '<span class="vfb-page-counter">' . $page_num . ' / ' . $page_count . '</span>';
			
			$output .= '<a href="#" id="page-' . $page_num . '" class="vfb-page-next' . $css . '">' . stripslashes( $field->field_name ) . '</a> ' . $total_page;
			$page = " vfb-page page-$page_num";
			$open_page = true;
		}
		elseif ( !in_array( $field->field_type, array( 'verification', 'secret', 'submit' ) ) ) {
			
			$columns_choice = ( !empty( $field->field_size ) && in_array( $field->field_type, array( 'radio', 'checkbox' ) ) ) ? " vfb-$field->field_size" : '';
			
			
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
					$verification .= sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. Verification not required.', 'visual-form-builder-pro' ), admin_url( 'profile.php' ), $user_identity );
				}
				
				$validation = ' {digits:true,maxlength:2,minlength:2}';
				$verification .= '<label for="' . $id_attr . '" class="vfb-desc">'. stripslashes( $field->field_name ) . $required_span . '</label>';
				
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
				
				if ( !empty( $field->field_description ) )
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '<small>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</small></label><div><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text ' . $field->field_size . $required . $validation . $css . '" /></div>';
				else
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '</label><div><input type="text" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="vfb-text ' . $field->field_size . $required . $validation . $css . '" /></div>';
					
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
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '<small>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</small></label>';
				else
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '</label>';
				
				$output .= '<div>';
				$output .= '<textarea name="vfb-' . $field->field_id . '" id="' . $id_attr . '" class="vfb-textarea ' . $field->field_size . $required . $css . $words . '">' . $default . '</textarea>' . $words_display;
				$output .= '</div>';

			break;
			
			case 'select' :
				if ( !empty( $field->field_description ) )
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '<small>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</small></label>';
				else
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '</label>';
				
				$output .= '<div>';		
				$output .= '<select name="vfb-' . $field->field_id . '" id="' . $id_attr . '" class="vfb-select ' . $field->field_size . $required . $css . '">';
				
				$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
				
				// Loop through each option and output
				foreach ( $options as $option => $value ) {
					$output .= '<option value="' . trim( stripslashes( $value ) ) . '"' . selected( $default, ++$option, 0 ) . '>'. trim( stripslashes( $value ) ) . '</option>';
				}
				
				$output .= '</select></div>';
				$output .= '<div style="clear:both"></div>';
				
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
				
				if ( !empty( $field->field_description ) ){
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '<small>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</small></label>';
				}else{
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '</label>';
				}

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
					
					foreach ( $this->countries as $country ) {
						$output .= "<option value=\"$country\" " . selected( $default, $country, 0 ) . ">$country</option>";
					}
					
					$output .= '</select>
						<label for="' . $id_attr . '-country">' . $address_labels['country'] . '</label>
					</span>
				</div>';

			break;
			
			case 'date' :
				
				if ( !empty( $field->field_description ) )
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '<small>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</small></label><div><input id="dateMasked" class="' . $field->field_size . '"  type="text" tabindex="1" /></div>';
				else
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '</label><div><input id="dateMasked" class="' . $field->field_size . '"  type="text" tabindex="1" /></div>';
				
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
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '<small>' . html_entity_decode( stripslashes( $field->field_description ) ) . '</small></label><div><input type="file" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="fileupload vfb-text ' . $field->field_size . $required . $validation . $accept . $css . '"' . ' /></div>';
				else
					$output .= '<label>' . html_entity_decode( stripslashes( $field->field_name ) ) . '</label><div><input type="file" name="vfb-' . $field->field_id . '" id="' . $id_attr . '" value="' . $default . '" class="fileupload vfb-text ' . $field->field_size . $required . $validation . $accept . $css . '"' . ' /></div>';
			
						
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
				
				$submit = '<input type="submit" name="visual-form-builder-submit" value="' . stripslashes( $field->field_name ) . '" class="vfb-submit' . $css . '" id="sendmail" />';
				$output .= ( false == $display_verification ) ? $submit : '';
				
			break;
			
			default:
				echo '';
				
				// Output Create Post items
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
	}
	
	// Output payment options
	if ( class_exists( 'VFB_Pro_Payments' ) && method_exists( $vfb_payments, 'running_total' ) ) {
		$enable_payment = $vfb_payments->enable_payment( $form_id );
		
		// Output payment options only if enabled
		if ( $enable_payment ) :
			// Set PayPal default to a Cart
			$paypal_command = '_cart';
			
			$account_details = $vfb_payments->account_details( $form_id );
			
			// Account email
			if ( $account_details )
				$output .= $account_details;
			
			$currency = $vfb_payments->currency_code( $form_id );
			
			// Currency Code
			if ( $currency )
				$output .= $currency;
				
			$prices = $vfb_payments->running_total( $form_id );
			
			// Pricing fields for jQuery
			if ( $prices ) :
				wp_localize_script( 'vfb-pro-payments', 'VfbPrices', array( 'prices' => $prices ) );
				
				// Show running total
				if ( $vfb_payments->show_running_total( $form_id ) )
					$output .= $vfb_payments->running_total_output();
				
				// For dynamic pricing inputs
				$output .= '<div class="vfb-payment-hidden-inputs"></div>';
				
			endif;
			
			$recurring = $vfb_payments->recurring_payments( $form_id );
			
			// Recurring payment
			if ( $recurring ) :
				$paypal_command = '_xclick-subscriptions';
				
				// Recurring hidden inputs
				$output .= $recurring;
				
				// For totals
				$output .= '<div class="vfb-payment-hidden-totals"></div>';
				
				// Required input for subscriptions
				$output .= '<input type="hidden" name="no_note" value="1">';
			endif;
			
			// Collect Shipping Address
			if ( $vfb_payments->collect_shipping_address( $form_id ) ) :
				$output .= '<input type="hidden" name="no_shipping" value="2">';
			endif;
			
			// PayPal command
			$output .= '<input type="hidden" name="cmd" value="' . $paypal_command . '">';
		endif;
	}
	
	// Close user-added fields
	$output .= '</fieldset>';
	
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
	
	// Output the conditional rules
	if ( $rules )
		wp_localize_script( 'visual-form-builder-validation', 'VfbRules', array( 'rules' => $rules ) );
	
	endif;
endforeach;
?>