<?php
global $wpdb, $post;
		
$required = ( isset( $_REQUEST['_vfb-required-secret'] ) && $_REQUEST['_vfb-required-secret'] == '0' ) ? false : true;
$secret_field = ( isset( $_REQUEST['_vfb-secret'] ) ) ? $_REQUEST['_vfb-secret'] : '';
$honeypot = ( isset( $_REQUEST['vfb-spam'] ) ) ? $_REQUEST['vfb-spam'] : '';

// If the verification is set to required, run validation check
if ( true == $required && !empty( $secret_field ) ) {
	// Use to skip the validate_input function for this field
	$novalidate = true;
	
	if ( !empty( $honeypot ) )
		wp_die( __( 'Security check: hidden spam field should be blank.' , 'visual-form-builder-pro'), '', array( 'back_link' => true ) );
	if ( !is_numeric( $_REQUEST[ $secret_field ] ) || strlen( $_REQUEST[ $secret_field ] ) !== 2 )
		wp_die( __( 'Security check: failed secret question. Please try again!' , 'visual-form-builder-pro'), '', array( 'back_link' => true ) );
}

// Basic security check before moving any further
if ( isset( $_REQUEST['visual-form-builder-submit'] ) ) :
	$nonce = $_REQUEST['_wpnonce'];
	
	// Security check to verify the nonce
	if ( ! wp_verify_nonce( $nonce, 'visual-form-builder-nonce' ) )
		wp_die( __( 'Security check: unable to verify nonce value.' , 'visual-form-builder-pro'), '', array( 'back_link' => true ) );
	
	// Test if it's a known SPAM bot
	if ( $this->isBot() )
		wp_die( __( 'Security check: looks like you are a SPAM bot. If you think this is an error, please email the site owner.' , 'visual-form-builder-pro'), '', array( 'back_link' => true ) );
	
	// Set submitted action to display success message
	$this->submitted = true;
	
	// Tells us which form to get from the database
	$form_id = absint( $_REQUEST['form_id'] );
	
	// Query to get all forms
	$order = sanitize_sql_orderby( 'form_id DESC' );
	$forms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );
	
	// Get sender and email details
	foreach ( $forms as $form ) {
		$form_settings = (object) array(
			'form_title' 					=> stripslashes( html_entity_decode( $form->form_title, ENT_QUOTES, 'UTF-8' ) ),
			'form_subject' 					=> stripslashes( html_entity_decode( $form->form_email_subject, ENT_QUOTES, 'UTF-8' ) ),
			'form_to' 						=> ( is_array( unserialize( $form->form_email_to ) ) ) ? unserialize( $form->form_email_to ) : explode( ',', unserialize( $form->form_email_to ) ),
			'form_from' 					=> stripslashes( $form->form_email_from ),
			'form_from_name' 				=> stripslashes( $form->form_email_from_name ),
			'form_notification_setting' 	=> stripslashes( $form->form_notification_setting ),
			'form_notification_email_name' 	=> stripslashes( $form->form_notification_email_name ),
			'form_notification_email_from' 	=> stripslashes( $form->form_notification_email_from ),
			'form_notification_subject' 	=> stripslashes( html_entity_decode( $form->form_notification_subject, ENT_QUOTES, 'UTF-8' ) ),
			'form_notification_message' 	=> stripslashes( $form->form_notification_message ),
			'form_notification_entry' 		=> stripslashes( $form->form_notification_entry )
		);
		// Allow the form settings to be filtered (ex: return $form_settings->'form_title' = 'Hello World';)				
		$form_settings = (object) apply_filters_ref_array( 'vfb_email_form_settings', array( $form_settings, $form_id ) );
		
		// Save form verification for validate_input
		$form_verification = $form->form_verification;
				
		$email_design = unserialize( $form->form_email_design );
		
		// Set email design variables
		$email_settings = (object) array(
			'color_scheme' 			=> ( !empty( $email_design['color_scheme'] ) ) ? stripslashes( $email_design['color_scheme'] ) : '',
			'format' 				=> ( !empty( $email_design['format'] ) ) ? stripslashes( $email_design['format'] ) : 'html',
			'link_love' 			=> ( !empty( $email_design['link_love'] ) ) ? stripslashes( $email_design['link_love'] ) : 'yes',
			'footer_text' 			=> ( !empty( $email_design['footer_text'] ) ) ? stripslashes( $email_design['footer_text'] ) : '',
			'background_color' 		=> ( !empty( $email_design['background_color'] ) ) ? stripslashes( $email_design['background_color'] ) : '#eeeeee',
			'header_image' 			=> ( !empty( $email_design['header_image'] ) ) ? stripslashes( $email_design['header_image'] ) : '',
			'header_color' 			=> ( !empty( $email_design['header_color'] ) ) ? stripslashes( $email_design['header_color'] ) : '#810202',
			'header_text_color' 	=> ( !empty( $email_design['header_text_color'] ) ) ? stripslashes( $email_design['header_text_color'] ) : '#ffffff',
			'fieldset_color' 		=> ( !empty( $email_design['fieldset_color'] ) ) ? stripslashes( $email_design['fieldset_color'] ) : '#680606',
			'section_color' 		=> ( !empty( $email_design['section_color'] ) ) ? stripslashes( $email_design['section_color'] ) : '#5C6266',
			'section_text_color' 	=> ( !empty( $email_design['section_text_color'] ) ) ? stripslashes( $email_design['section_text_color'] ) : '#ffffff',
			'text_color' 			=> ( !empty( $email_design['text_color'] ) ) ? stripslashes( $email_design['text_color'] ) : '#333333',
			'link_color' 			=> ( !empty( $email_design['link_color'] ) ) ? stripslashes( $email_design['link_color'] ) : '#1b8be0',
			'row_color' 			=> ( !empty( $email_design['row_color'] ) ) ? stripslashes( $email_design['row_color'] ) : '#ffffff',
			'row_alt_color' 		=> ( !empty( $email_design['row_alt_color'] ) ) ? stripslashes( $email_design['row_alt_color'] ) : '#eeeeee',
			'border_color' 			=> ( !empty( $email_design['border_color'] ) ) ? stripslashes( $email_design['border_color'] ) : '#cccccc',
			'footer_color' 			=> ( !empty( $email_design['footer_color'] ) ) ? stripslashes( $email_design['footer_color'] ) : '#333333',
			'footer_text_color' 	=> ( !empty( $email_design['footer_text_color'] ) ) ? stripslashes( $email_design['footer_text_color'] ) : '#ffffff',
			'font_family' 			=> ( !empty( $email_design['font_family'] ) ) ? stripslashes( $email_design['font_family'] ) : 'Arial',
			'header_font_size' 		=> ( !empty( $email_design['header_font_size'] ) ) ? stripslashes( $email_design['header_font_size'] ) : 32,
			'fieldset_font_size' 	=> ( !empty( $email_design['fieldset_font_size'] ) ) ? stripslashes( $email_design['fieldset_font_size'] ) : 20,
			'section_font_size' 	=> ( !empty( $email_design['section_font_size'] ) ) ? stripslashes( $email_design['section_font_size'] ) : 15,
			'text_font_size' 		=> ( !empty( $email_design['text_font_size'] ) ) ? stripslashes( $email_design['text_font_size'] ) : 13,
			'footer_font_size' 		=> ( !empty( $email_design['footer_font_size'] ) ) ? stripslashes( $email_design['footer_font_size'] ) : 11
		);
		
		// Allow the email design to be filtered (ex: return $email_settings->'format' = 'html';)				
		$email_settings = (object) apply_filters_ref_array( 'vfb_email_design_settings', array( $email_settings, $form_id ) );				
	}
	
	// Apply templating to form_subject, form_notification_subject
	$form_settings->form_subject 				= $this->templating( $form_settings->form_subject );
	$form_settings->form_notification_subject 	= $this->templating( $form_settings->form_notification_subject );
	
	// Sender name field ID
	$sender = $wpdb->get_var( $wpdb->prepare( "SELECT form_email_from_name_override FROM $this->form_table_name WHERE form_id = %d", $form_id ) );
	
	// Sender email field ID
	$email = $wpdb->get_var( $wpdb->prepare( "SELECT form_email_from_override FROM $this->form_table_name WHERE form_id = %d", $form_id ) );
	
	// Notifcation email field ID
	$notify = $wpdb->get_var( $wpdb->prepare( "SELECT form_notification_email FROM $this->form_table_name WHERE form_id = %d", $form_id ) );

	$reply_to_name	= $form_settings->form_from_name;
	$reply_to_email	= $form_settings->form_from;
	
	// Use field for sender name
	if ( !empty( $sender ) ) {
		$form_settings->form_from_name = wp_kses_data( $_POST[ 'vfb-' . $sender ] );
		$reply_to_name = $form_settings->form_from_name;
	}
	
	// Use field for sender email	
	if ( !empty( $email ) ) {
		$form_settings->form_from = sanitize_email( $_POST[ 'vfb-' . $email ] );
		$reply_to_email = $form_settings->form_from;
	}
	
	// Use field for copy email	
	$copy_email = ( !empty( $notify ) ) ? sanitize_email( $_POST[ 'vfb-' . $notify ] ) : '';

	// Query to get all forms
	$order = sanitize_sql_orderby( 'field_sequence ASC' );
	$fields = $wpdb->get_results( $wpdb->prepare( "SELECT field_id, field_key, field_name, field_type, field_options, field_options_other, field_parent, field_required FROM $this->field_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );
	
	$open_fieldset = false;
	
	// Setup counter for alt rows
	$i = $points = 0;
	
	// Setup HTML email vars
	$header = $body = $message = $footer = $html_email = $plain_text = $auto_response_email = $attachments = '';
	
	// Prepare the beginning of the content
	$header = '<html>
				<head>
				<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
				<title>HTML Email</title>
				</head>
				<body style="background-color: ' . $email_settings->background_color . ';">
				<table class="bg1" cellspacing="0" border="0" style="background-color: ' . $email_settings->background_color . ';" cellpadding="0" width="100%">
				  <tr>
					<td align="center">
					<table class="bg2" cellspacing="0" border="0" style="background-color: #ffffff;" cellpadding="0" width="600">
						<tr>
						  <td class="permission" align="center" style="background-color: ' . $email_settings->background_color . ';padding: 10px 20px 10px 20px;">&nbsp;</td>
						</tr>
						<tr>';
						if ( isset( $email_settings->header_image ) && $email_settings->header_image == '' )
							$header .= '<td class="header" align="left" style="background-color:' . $email_settings->header_color . ';padding: 50px 20px 50px 20px;"><h1 style="font-family: ' . $email_settings->font_family . ';font-size: ' . $email_settings->header_font_size . 'px;font-weight:normal;margin:0;padding:0;color:' . $email_settings->header_text_color . ';">' . $form_settings->form_title . '</h1></td>';
						else {
							@list( $width, $height, $type, $attr ) = getimagesize( $email_settings->header_image );
							$header .= '<td class="header" align="left" style="background-color:' . $email_settings->header_color . ';"><img ' . $attr . ' src="' . $email_settings->header_image . '" alt="' . $form_settings->form_title . '" /></td>';
						}
						$header .= '</tr>
						<tr>
						  <td class="body" valign="top" style="background-color: ' . $email_settings->row_color . ';padding: 20px 20px 20px 20px;">
						  <table cellspacing="0" border="0" cellpadding="0" width="100%">
							  <tr>
								<td class="mainbar" align="left" valign="top">' . "\n";
	
	// Start setting up plain text email
	$plain_text .= "============ {$form_settings->form_title} =============\n";
	
	// Loop through each form field and build the body of the message
	foreach ( $fields as $field ) {
		$alt_row = ( $i % 2 == 0 ) ? 'background-color:' . $email_settings->row_alt_color : '';
		
		$options_other = $field->field_options_other;
		
		// Handle attachments
		if ( $field->field_type == 'file-upload' ) {
			$value = ( isset( $_FILES[ 'vfb-' . $field->field_id ] ) ) ? $_FILES[ 'vfb-' . $field->field_id ] : '';
			
			if ( $value['size'] > 0 ) {
				// 25MB is the max size allowed
				$size = apply_filters( 'vfb_max_file_size', 25 );
				$max_attach_size = $size * 1048576;
				
				// Display error if file size has been exceeded
				if ( $value['size'] > $max_attach_size )
					wp_die( sprintf( __( "File size exceeds %dMB. Most email providers will reject emails with attachments larger than %dMB. Please decrease the file size and try again.", 'visual-form-builder-pro' ), $size ), '', array( 'back_link' => true ) );
				
				// Options array for the wp_handle_upload function. 'test_form' => false
				$upload_overrides = array( 'test_form' => false ); 
				
				// We need to include the file that runs the wp_handle_upload function
				require_once( ABSPATH . 'wp-admin/includes/file.php' );
				
				// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
				$uploaded_file = wp_handle_upload( $value, $upload_overrides );
				
				// If the wp_handle_upload call returned a local path for the image
				if ( isset( $uploaded_file['file'] ) ) {
					// Retrieve the file type from the file name. Returns an array with extension and mime type
					$wp_filetype = wp_check_filetype( basename( $uploaded_file['file'] ), null );
					
					// Return the current upload directory location
						$wp_upload_dir = wp_upload_dir();
												
					$media_upload = array(
						'guid' 				=> $wp_upload_dir['baseurl'] . _wp_relative_upload_path( $uploaded_file['file'] ), 
						'post_mime_type' 	=> $wp_filetype['type'],
						'post_title' 		=> preg_replace( '/\.[^.]+$/', '', basename( $uploaded_file['file'] ) ),
						'post_content' 		=> '',
						'post_status' 		=> 'inherit'
					);
					
					// Allow the uploads to be attached to a post/page
					$post_id = apply_filters( 'vfb_upload_attach_post', 0, $form_id );
					
					// Insert attachment into Media Library and get attachment ID
					$attach_id = wp_insert_attachment( $media_upload, $uploaded_file['file'], $post_id );
					
					// Include the file that runs wp_generate_attachment_metadata()
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					
					// Setup attachment metadata
					$attach_data = wp_generate_attachment_metadata( $attach_id, $uploaded_file['file'] );
					
					// Update the attachment metadata
					wp_update_attachment_metadata( $attach_id, $attach_data );
					
					$attachments[ 'vfb-' . $field->field_id ] = $uploaded_file['file'];

					$data[] = array(
						'id'              => $field->field_id,
						'slug'            => $field->field_key,
						'name'            => $field->field_name,
						'type'            => $field->field_type,
						'options'         => $field->field_options,
						'options_other'   => $options_other,
						'parent_id'       => $field->field_parent,
						'value'           => $uploaded_file['url']
					);
					
					$body .= '<tr>
								  <td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . ';border-bottom:1px solid ' . $email_settings->border_color . ';"><p style="font-size: ' . $email_settings->text_font_size . 'px; font-weight: bold; margin: 14px 0 14px 5px; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->text_color . '; padding: 0;">' . stripslashes( $field->field_name ) . ':</p></td>
								  <td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . ';border-bottom:1px solid ' . $email_settings->border_color . ';"><p style="font-size: ' . $email_settings->text_font_size . 'px; font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->text_color . '; padding: 0;"><a href="' . $uploaded_file['url'] . '" style="font-size: 13px; font-weight: normal; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->link_color . ';">' . $uploaded_file['url'] . '</a></p></td>
								</tr>' . "\n";
					
					$plain_text .= stripslashes( $field->field_name ) . ": {$uploaded_file['url']}\n";
				}
			}
			else {
				$value = ( isset( $_POST[ 'vfb-' . $field->field_id ] ) ) ? $_POST[ 'vfb-' . $field->field_id ] : '';
				$body .= '<tr>
								  <td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . ';border-bottom:1px solid ' . $email_settings->border_color . ';"><p style="font-size: ' . $email_settings->text_font_size . 'px; font-weight: bold; margin: 14px 0 14px 5px; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->text_color . '; padding: 0;">' . stripslashes( $field->field_name ) . ':</p></td>
								  <td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . ';border-bottom:1px solid ' . $email_settings->border_color . ';"><p style="font-size: ' . $email_settings->text_font_size . 'px; font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->text_color . '; padding: 0;">' . $value . '</p></td>
								</tr>' . "\n";
				
				$plain_text .= stripslashes( $field->field_name ) . ": $value\n";
			}
			
			// Increment our alt row counter
			$i++;
		}
		// Everything else
		else {
			$value = ( isset( $_POST[ 'vfb-' . $field->field_id ] ) ) ? $_POST[ 'vfb-' . $field->field_id ] : '';
			
			// If time field, build proper output
			if ( is_array( $value ) && array_key_exists( 'hour', $value ) && array_key_exists( 'min', $value ) )
				$value = ( array_key_exists( 'ampm', $value ) ) ? substr_replace( implode( ':', $value ), ' ', 5, 1 ) : implode( ':', $value );
			// If address field, build proper output
			elseif ( is_array( $value ) && array_key_exists( 'address', $value ) && array_key_exists( 'address-2', $value ) ) {
				$address = '';
				
				if ( !empty( $value['address'] ) )
					$address .= $value['address'];
				
				if ( !empty( $value['address-2'] ) ) {
					if ( !empty( $address ) )
						$address .= '<br>';
					$address .= $value['address-2'];
				}
				
				if ( !empty( $value['city'] ) ) {
					if ( !empty( $address ) )
						$address .= '<br>';
					$address .= $value['city'];
				}
				if ( !empty( $value['state'] ) ) {
					if ( !empty( $address ) && empty( $value['city'] ) )
						$address .= '<br>';
					elseif ( !empty( $address ) && !empty( $value['city'] ) )
						$address .= ', ';
					$address .= $value['state'];
				}
				if ( !empty( $value['zip'] ) ) {
					if ( !empty( $address ) && ( empty( $value['city'] ) && empty( $value['state'] ) ) )
						$address .= '<br>';
					elseif ( !empty( $address ) && ( !empty( $value['city'] ) || !empty( $value['state'] ) ) )
						$address .= ' ';
					$address .= $value['zip'];
				}
				if ( !empty( $value['country'] ) ) {
					if ( !empty( $address ) )
						$address .= '<br>';
					$address .= $value['country'];
				}
				
				$value = html_entity_decode( stripslashes( esc_html( $address ) ), ENT_QUOTES, 'UTF-8' );
			}
			// If name field, build proper output
			elseif ( is_array( $value ) && array_key_exists( 'first', $value ) ) {
				$name = '';
				
				if ( !empty( $value['first'] ) )
					$name .= $value['first'];
				
				if ( !empty( $value['last'] ) ) {
					if ( !empty( $name ) )
						$name .= ' ';
					$name .= $value['last'];
				}
				
				if ( !empty( $value['title'] ) ) {
					if ( !empty( $name ) )
						$name = ' ' . $name;
					$name = $value['title'] . $name;
				}
				
				if ( !empty( $value['suffix'] ) ) {
					if ( !empty( $name ) )
						$name .= ' ';
					$name .= $value['suffix'];
				}
				
				$value = html_entity_decode( stripslashes( esc_html( $name ) ), ENT_QUOTES, 'UTF-8' );
				
				if ( !empty( $sender ) ) {
					$form_settings->form_from_name = wp_kses_data( $value );
					$reply_to_name = $form_settings->form_from_name;
				}
			}
			// If multiple values, build the list
			elseif ( is_array( $value ) ) {
				$value = ( isset( $value['other'] ) ) ? html_entity_decode( stripslashes( esc_html( $value['other'] ) ), ENT_QUOTES, 'UTF-8' ) : esc_html( implode( ', ', $value ) );
				
				if ( isset( $value['other'] ) ) {
					$options_other = maybe_unserialize( $field->field_options_other );
					$options_other['selected'] =  $value;
					$options_other = serialize( $options_other );
				}
			}
			// Lastly, handle single values
			else
				$value = html_entity_decode( stripslashes( esc_html( $value ) ), ENT_QUOTES, 'UTF-8' );
			
			// Setup spam catcher RegEx
			$exploits = '/(content-type|bcc:|cc:|document.cookie|onclick|onload|javascript|alert)/i';
			$profanity = '/(beastial|bestial|blowjob|clit|cock|cum|cunilingus|cunillingus|cunnilingus|cunt|ejaculate|fag|felatio|fellatio|fuck|fuk|fuks|gangbang|gangbanged|gangbangs|hotsex|jism|jiz|kock|kondum|kum|kunilingus|orgasim|orgasims|orgasm|orgasms|phonesex|phuk|phuq|porn|pussies|pussy|spunk|xxx)/i';
			$spamwords = '/(viagra|phentermine|tramadol|adipex|advai|alprazolam|ambien|ambian|amoxicillin|antivert|blackjack|backgammon|texas|holdem|poker|carisoprodol|ciara|ciprofloxacin|debt|dating|porn)/i';
			
			// Add up points for each spam hit
			if ( preg_match( $exploits, $value ) )
				$points += 2;
			elseif ( preg_match( $profanity, $value ) )
				$points += 1;
			elseif ( preg_match( $spamwords, $value ) )
				$points += 1;
			
			//Sanitize input
			$value = $this->sanitize_input( $value, $field->field_type );
			
			// Validate input (skip the verification, if user didn't display it)
			$this->validate_input( $value, $field->field_name, $field->field_type, $field->field_required, $form_verification );
			
			
			// Don't add certain fields to the email
			if ( ! in_array( $field->field_type, array( 'verification', 'secret', 'submit', 'page-break', 'instructions' ) ) ) {
				if ( $field->field_type == 'fieldset' ) {
					// Close each fieldset
					if ( $open_fieldset == true )
						$body .= '</table>' . "\n";
				
					$body .= '<h2 style="font-size: ' . $email_settings->fieldset_font_size . 'px; font-weight: bold; margin: 10px 0 10px 0; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->fieldset_color . '; padding: 0;">' . stripslashes( $field->field_name ) . '</h2>
          							<table cellspacing="0" border="0" cellpadding="0" width="100%">' . "\n";
					
					$open_fieldset = true;
					
					$plain_text .= "\n------------------------------\n" . stripslashes( $field->field_name ) .  "\n------------------------------\n";
				}
				elseif ( $field->field_type == 'section' ) {
					$body .= '<tr><td colspan="2" style="background-color:' . $email_settings->section_color . ';color:' . $email_settings->section_text_color . ';"><h3 style="font-size: ' . $email_settings->section_font_size . 'px; font-weight: bold; margin: 14px 14px 14px 10px; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->section_text_color . '; padding: 0;">' . stripslashes( $field->field_name ) . '</h3></td></tr>' . "\n";
					$plain_text .= "*** " . stripslashes( $field->field_name ) . "***\n";
				}
				else {
					// Convert new lines to break tags for textarea in html
					$display_value = ( 'textarea' == $field->field_type && 'html' == $email_settings->format ) ? nl2br( $value ) : $value;
					
					// If Create Post addon installed, use category name not ID in email
					if ( 'post-category' == $field->field_type )
						$display_value = get_the_category_by_ID( $value );
						
					$body .= '<tr><td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . ';border-bottom:1px solid ' . $email_settings->border_color . ';">
					<p style="font-size: ' . $email_settings->text_font_size . 'px; font-weight: bold; margin: 14px 0 14px 5px; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->text_color . '; padding: 0;">' . stripslashes( $field->field_name ) . ':</p>
					</td><td class="mainbar" align="left" valign="top" width="300" style="' . $alt_row . ';border-bottom:1px solid ' . $email_settings->border_color . ';">
					<p style="font-size: ' . $email_settings->text_font_size . 'px; font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->text_color . '; padding: 0;">' . $display_value . '</p></td></tr>' . "\n";
					
					$plain_text .= stripslashes( $field->field_name ) . ": $value\n";
					
					// Increment our alt row counter
					$i++;
				}
			}
		
			$data[] = array(
				'id'            => $field->field_id,
				'slug'          => $field->field_key,
				'name'          => $field->field_name,
				'type'          => $field->field_type,
				'options'       => $field->field_options,
				'options_other' => $options_other,
				'parent_id'     => $field->field_parent,
				'value'         => esc_html( $value )
			);
		}
		
		// Setup Create User variables
		if ( $field->field_type == 'email' && !isset( $create_user_email ) )
			$create_user_email = $value;
		elseif ( $field->field_type == 'username' && !isset( $create_user_user ) )
			$create_user_user = $value;
		elseif ( $field->field_type == 'password' && !isset( $create_user_password ) )
			$create_user_password = $value;
		
		// Setup Create Post variables
		if ( $field->field_type == 'post-title' && !isset( $post_title ) )
			$post_title = $value;
		elseif ( $field->field_type == 'post-content' && !isset( $post_content ) )
			$post_content = $value;
		elseif ( $field->field_type == 'post-excerpt' && !isset( $post_excerpt ) )
			$post_excerpt = $value;
		elseif ( $field->field_type == 'post-category' && !isset( $post_category ) )
			$post_category = $value;
		elseif ( $field->field_type == 'post-tag' && !isset( $post_tag ) )
			$post_tag = $value;
				
		// Setup Akismet variables
		if ( $field->field_type == 'email' && !isset( $comment_author_email ) )
			$comment_author_email = $value;
		elseif ( $field->field_type == 'name' && !isset( $comment_author ) )
			$comment_author = $value;
		elseif ( $field->field_type == 'url' && !isset( $comment_author_url ) )
			$comment_author_url = $value;
		elseif ( $field->field_type == 'textarea' && !isset( $comment_content ) )
			$comment_content = $value;
			
		// If the user accumulates more than 4 points, it might be spam
		if ( $points > 4 )
			wp_die( __( 'Your responses look too much like spam and could not be sent at this time.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
	}
	
	//$comment_author = 'viagra-test-123';
	// Create an array based off the Akismet values collected
	$akismet_vars = array( 'comment_author_email', 'comment_author', 'comment_author_url', 'comment_content' );
	$akismet_data = compact( $akismet_vars );
	
	// Insert additional Akismet data
	$akismet_data['comment_type'] = 'contact-form';
	$akismet_data['user_ip']      = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
	$akismet_data['user_agent']   = $_SERVER['HTTP_USER_AGENT'];
	$akismet_data['referrer']     = $_SERVER['HTTP_REFERER'];
	$akismet_data['blog']         = get_option( 'home' );
	
	// Run akismet check and set flag to send mail
	$is_spam = ( $this->akismet_check( $akismet_data ) ) ? 'spam' : 1;
	
	// Run Create User add-on
	if ( class_exists( 'VFB_Pro_Create_User' ) ) {
		$vfb_user = new VFB_Pro_Create_User();
		
		// Create an array based off the user values collected
		$user_vars = array( 'create_user_user', 'create_user_password', 'create_user_email' );
		$user_data = compact( $user_vars );
				
		$vfb_user->create_user( $user_data, $form_id );
	}
	
	// Run Create Post add-on
	if ( class_exists( 'VFB_Pro_Create_Post' ) ) {
		$vfb_post = new VFB_Pro_Create_Post();
		
		$post_vars = array( 'post_title', 'post_content', 'post_excerpt', 'post_category', 'post_tag' );
		$post_data = compact( $post_vars );
		
		// Set default user ID to 1
		$user_id = 1;
		
		// If the user is logged in, fill the field in for them
		if ( is_user_logged_in() ) {
			// Get logged in user details
			$user = wp_get_current_user();
			$user_id = !empty( $user->ID ) ? $user->ID : 1;
		}
		
		$vfb_post->create_post( $post_data, $user_id, $form_id );
	}
	
		
	// Setup our entries data
	$entry = array(
		'form_id' 			=> $form_id,
		'data' 				=> serialize( $data ),
		'subject' 			=> $form_settings->form_subject,
		'sender_name' 		=> $form_settings->form_from_name,
		'sender_email' 		=> $form_settings->form_from,
		'emails_to' 		=> serialize( $form_settings->form_to ),
		'date_submitted' 	=> date_i18n( 'Y-m-d G:i:s' ),
		'ip_address' 		=> apply_filters( 'vfb_entries_save_ip', $_SERVER['REMOTE_ADDR'] ),
		'akismet'			=> maybe_serialize( $akismet_data ),
		'entry_approved'	=> $is_spam
	);
	
	// Insert this data into the entries table
	if ( apply_filters( 'vfb_entries_save_new', true, $form_id ) )
		$wpdb->insert( $this->entries_table_name, $entry );
	
	// Setup the link love
	if ( $email_settings->link_love == '' || $email_settings->link_love == 'yes' ) {
		$html_link_love = 'This email was built and sent using <a href="http://vfb.matthewmuro.com" style="font-size: ' . $email_settings->footer_font_size . 'px;font-family: ' . $email_settings->font_family . ';color:' . $email_settings->link_color . ';">Visual Form Builder Pro</a>.';
		$plain_text_link_love = "This email was built and sent using\nVisual Form Builder Pro (http://vfb.matthewmuro.com)";
	}
	
	// Close out the content
	$footer = '</table></td>
						  </tr>
						</table>
						</td>
					</tr>
					<tr>
					  <td class="footer" height="61" align="left" valign="middle" style="background-color: ' . $email_settings->footer_color . '; padding: 0 20px 0 20px; height: 61px; vertical-align: middle;"><p style="font-size: ' . $email_settings->footer_font_size . 'px; font-weight: normal; margin: 0; font-family: ' . $email_settings->font_family . '; line-height: 16px; color: ' . $email_settings->footer_text_color . '; padding: 0;">' . $html_link_love . $email_settings->footer_text . '</p>
					  </td>
					</tr>
				  </table></td>
			  </tr>
			  <tr>
				  <td class="permission" align="center" style="background-color: ' . $email_settings->background_color . '; padding: 20px 20px 20px 20px;">&nbsp;</td>
				</tr>
			</table>
			</body>
			</html>';
	
	$plain_text .= "\n- - - - - - - - - - - -\n$plain_text_link_love\n{$email_settings->footer_text}\n";
	
	// Build complete HTML email
	$html_email = $header . $body . $footer;
	
	// Decode HTML for message so it outputs properly
	$notify_message = ( $form_settings->form_notification_message !== '' ) ? html_entity_decode( $form_settings->form_notification_message ) : '';
	
	// Initialize header filter vars
	$header_from_name = stripslashes( $reply_to_name );
	$header_from = $reply_to_email;
	
	// Set message format and header contenty type
	if ( $email_settings->format == '' || $email_settings->format == 'html' ) {
		$header_content_type = 'text/html';
		$message = $html_email;
		
		// Wrap the notify message in some HTML
		$notify_message = '<p style="font-size: ' . $email_settings->text_font_size . 'px; font-weight: normal; margin: 14px 0 14px 0; font-family: ' . $email_settings->font_family . '; color: ' . $email_settings->text_color . '; padding: 0;">' . $notify_message . '</p>';
		
		// Either prepend the notification message to the submitted entry, or send by itself				
		if ( $form_settings->form_notification_entry !== '' )
			$auto_response_email = $header . $notify_message . $body . $footer;
		else
			$auto_response_email = $header . '<table cellspacing="0" border="0" cellpadding="0" width="100%"><tr><td colspan="2" class="mainbar" align="left" valign="top" width="600" style="' . $alt_row . ';">' . $notify_message . '</td></tr>' . $footer;
	}
	else {
		$header_content_type = 'text/plain';
		$message = $plain_text;
		
		// Strip all HTML out for plain text
		$notify_message = wp_strip_all_tags( $notify_message );
		
		// Either prepend the notification message to the submitted entry, or send by itself				
		if ( $form_settings->form_notification_entry !== '' )
			$auto_response_email = "$notify_message\n\n" . $message;
		else
			$auto_response_email = $notify_message;
	}
	
	// Use a default From name if one has not been set
	$from_name = ( $header_from_name == '' ) ? 'WordPress' : $header_from_name;
	
	// Use the admin_email as the From email
	$from_email = get_site_option( 'admin_email' );
	
	// Get the site domain and get rid of www.
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) == 'www.' )
		$sitename = substr( $sitename, 4 );
	
	// Get the domain from the admin_email
	list( $user, $domain ) = explode( '@', $from_email );
	
	// If site domain and admin_email domain match, use admin_email, otherwise a same domain email must be created
	$from_email = ( $sitename == $domain ) ? $from_email : "wordpress@$sitename";
	
	$reply_to = "\"$header_from_name\" <$header_from>";
	$headers = "Sender: $from_email\r\n" . "From: $reply_to\r\n" . "Content-Type: $header_content_type; charset=\"" . get_option('blog_charset') . "\"\r\n";
	
	// Allow attachments to be unattached in main email
	$attachments = apply_filters( 'vfb_attachments_email', $attachments, $form_id );
	
	do_action( 'vfb_before_email', $form_id );
	
	// Send the mail, if not marked as SPAM by Akismet
	foreach ( $form_settings->form_to as $email ) {
		wp_mail( $email, wp_specialchars_decode( $form_settings->form_subject, ENT_QUOTES ), $message, $headers, $attachments );
	}
	
	do_action( 'vfb_after_email', $form_id );
	
	// Send auto-responder email
	if ( $form_settings->form_notification_setting !== '' ) :
		
		$attachments = ( $form_settings->form_notification_entry !== '' ) ? $attachments : '';
		
		// Allow attachments to be unattached in notification email
		$attachments = apply_filters( 'vfb_attachments_email_notify', $attachments, $form_id );
		
		// Reset headers for notification email
		$reply_name = stripslashes( $form_settings->form_notification_email_name );
		$reply_email = $form_settings->form_notification_email_from;
		$reply_to 	= "\"$reply_name\" <$reply_email>";
		$headers = "Sender: $from_email\r\n" . "From: $reply_to\r\n" . "Content-Type: $header_content_type; charset=\"" . get_option('blog_charset') . "\"\r\n";
		
		do_action( 'vfb_before_notify_email', $form_id );
		
		// Send the mail
		wp_mail( $copy_email, wp_specialchars_decode( $form_settings->form_notification_subject, ENT_QUOTES ), $auto_response_email, $headers, $attachments );
		
		do_action( 'vfb_after_notify_email', $form_id );
		
	endif;
		
endif;
?>