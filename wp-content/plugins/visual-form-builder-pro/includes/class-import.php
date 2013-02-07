<?php
/**
 * Class that builds our Import page
 * 
 * @since 1.7
 */
class VisualFormBuilder_Pro_Import {
	
	protected $id, $version,
			  $max_version = '2.0',
			  $existing_forms = array(),
			  $forms = array(),
			  $fields = array(),
			  $entries = array();
		
	public function __construct(){
		global $wpdb;
		
		// Setup global database table names
		$this->field_table_name 	= $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name 		= $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name 	= $wpdb->prefix . 'vfb_pro_entries';
		
	}
	
	/**
	 * Display the import form
	 *
	 * @since 1.7
	 *
	 */
	public function display(){		
		$this->dispatch();
		
        wp_import_upload_form( 'admin.php?page=vfb-import&amp;import=vfb&amp;step=1' );
	}
	
	/**
	 * Manages the separate stages of the XML import process
	 *
	 * @since 1.7
	 *
	 */
	public function dispatch() {

		$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
		
		switch ( $step ) {
			case 0:
				echo '<p>' . __( 'Select a Visual Form Builder Pro backup file (.xml), then click Upload file and import.', 'visual-form-builder-pro' ) . '</p>';
			break;
			
			case 1:
				check_admin_referer( 'import-upload' );
				if ( $this->handle_upload() ) {
					$file = get_attached_file( $this->id );
					set_time_limit(0);
					$this->import( $file );
				}
			break;
		}
	}
	
	/**
	 * The main controller for the actual import stage.
	 *
	 * @since 1.7
	 * @param string $file Path to the XML file for importing
	 */
	public function import( $file ) {
		$this->import_start( $file );
		
		wp_suspend_cache_invalidation( true );
		$this->process_forms();
		$this->process_fields();
		$this->process_entries();
		wp_suspend_cache_invalidation( false );
		
		$this->import_end();
	}
	
	/**
	 * Parses the XML file and prepares us for the task of processing parsed data
	 *
	 * @since 1.7
	 * @param string $file Path to the XML file for importing
	 */
	public function import_start( $file ) {
		if ( ! is_file($file) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'visual-form-builder-pro' ) . '</strong><br />';
			echo __( 'The file does not exist, please try again.', 'visual-form-builder-pro' ) . '</p>';
			
			die();
		}

		$import_data = $this->parse( $file );

		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'visual-form-builder-pro' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';
			
			die();
		}
		
		$this->version 	= $import_data['version'];
		$this->forms 	= $import_data['forms'];
		$this->fields 	= $import_data['fields'];
		$this->entries 	= $import_data['entries'];
	}
	
	/**
	 * Performs post-import cleanup of files and the cache
	 *
	 * @since 1.7
	 *
	 */
	public function import_end() {
		wp_import_cleanup( $this->id );

		wp_cache_flush();

		echo '<p>' . __( 'All done.', 'visual-form-builder-pro' ) . ' <a href="' . admin_url( 'admin.php?page=visual-form-builder-pro' ) . '">' . __( 'View Forms', 'visual-form-builder-pro' ) . '</a>' . '</p>';
	}
	
	/**
	 * Process the forms from the XML import
	 *
	 * @since 1.7
	 *
	 */
	public function process_forms() {
		global $wpdb;
		
		if ( empty( $this->forms ) )
			return;
		
		foreach ( $this->forms as $form ) {		
			$data = array(
				'form_id' 						=> $form['form_id'],
				'form_key' 						=> $form['form_key'],
				'form_title' 					=> $form['form_title'],
				'form_email_subject' 			=> $form['form_email_subject'],
				'form_email_to' 				=> $form['form_email_to'],
				'form_email_from' 				=> $form['form_email_from'],
				'form_email_from_name' 			=> $form['form_email_from_name'],
				'form_email_from_override' 		=> $form['form_email_from_override'],
				'form_email_from_name_override' => $form['form_email_from_name_override'],
				'form_success_type' 			=> $form['form_success_type'],
				'form_success_message' 			=> $form['form_success_message'],
				'form_notification_setting' 	=> $form['form_notification_setting'],
				'form_notification_email_name' 	=> $form['form_notification_email_name'],
				'form_notification_email_from' 	=> $form['form_notification_email_from'],
				'form_notification_email' 		=> $form['form_notification_email'],
				'form_notification_subject' 	=> $form['form_notification_subject'],
				'form_notification_message' 	=> $form['form_notification_message'],
				'form_notification_entry' 		=> $form['form_notification_entry'],
				'form_email_design' 			=> $form['form_email_design'],
				'form_label_alignment' 			=> $form['form_label_alignment'],
				'form_verification' 			=> $form['form_verification'],
				'form_entries_allowed' 			=> $form['form_entries_allowed'],
				'form_entries_schedule' 		=> $form['form_entries_schedule']
			);
			
			$form_id = $this->form_exists( $form['form_id'] );
			
			// If the form ID is a duplicate, it can't be used
			if ( $form_id ) {
				$data['form_id'] = '';
				$this->existing_forms[ $form['form_id'] ] = '';
				
				echo '<p><strong>' . stripslashes( $form['form_title'] ) . ': </strong>' . __( 'Form ID already exists. Assigning a new form ID.', 'visual-form-builder-pro' ) . '</p>';
			}
			
			$insert = $wpdb->insert( $this->form_table_name, $data );
			
			if ( !$insert )
				echo '<p>' . sprintf( __( '<strong>Error: </strong> The %s form could not be imported.', 'visual-form-builder-pro' ), stripslashes( $form['form_title'] ) ) . '</p>';
			
			// Save the new form_id(s) to update associated fields
			$this->existing_forms[ $form['form_id'] ] = $wpdb->insert_id;
		}
		
		echo '<p>' . __( 'Form import process complete.', 'visual-form-builder-pro' ) . '</p>';
		
		unset( $this->forms );
	}
	
	/**
	 * Process the fields from the XML import
	 *
	 * @since 1.7
	 *
	 */
	public function process_fields() {
		global $wpdb;
		
		if ( empty( $this->fields ) )
			return;
		
		foreach ( $this->fields as $field ) {
			$form_id = ( array_key_exists( $field['form_id'], $this->existing_forms ) ) ? $this->existing_forms[ $field['form_id'] ] : $field['form_id'];
			$override = $wpdb->get_var( $wpdb->prepare( "SELECT form_email_from_override, form_email_from_name_override, form_notification_email FROM $this->form_table_name WHERE form_id = %d", $form_id ) );
			$from_name = $wpdb->get_var( null, 1 );
			$notify = $wpdb->get_var( null, 2 );
			
			$data = array(
				//'field_id' => $field['field_id'],
				'form_id' 			=> $form_id,
				'field_key' 		=> $field['field_key'],
				'field_type' 		=> $field['field_type'],
				'field_options' 	=> $field['field_options'],
				'field_options_other'=> $field['field_options_other'],
				'field_description' => $field['field_description'],
				'field_name' 		=> $field['field_name'],
				'field_sequence' 	=> $field['field_sequence'],
				'field_parent' 		=> $field['field_parent'],
				'field_validation' 	=> $field['field_validation'],
				'field_required' 	=> $field['field_required'],
				'field_size' 		=> $field['field_size'],
				'field_css' 		=> $field['field_css'],
				'field_layout' 		=> $field['field_layout'],
				'field_default' 	=> $field['field_default'],
				'field_rule_setting'=> $field['field_rule_setting'],
				'field_rule'	 	=> $field['field_rule']
			);
			
			$field_id = $this->field_exists( $field['field_id'] );
			
			// If the field ID is not a duplicate, it can be used
			if ( !$field_id ) {
				$data['field_id'] 	= $field['field_id'];
				$field_id 			= $field['field_id'];
			}
			
			$insert = $wpdb->insert( $this->field_table_name, $data );
			
			// Display error message if the insert fails
			if ( !$insert )
				echo '<p>' . sprintf( __( '<strong>Error: </strong> The %s field could not be imported.', 'visual-form-builder-pro' ), stripslashes( $field['field_name'] ) ) . '</p>';
			
			// Save field IDs so we can update the field rules
			$old_ids[ $field_id ] = $wpdb->insert_id;
				
			// If a parent field, save the old ID and the new ID to update new parent ID
			if ( in_array( $field['field_type'], array( 'fieldset', 'section', 'verification' ) ) )
				$parents[ $field_id ] = $wpdb->insert_id;
				
			if ( $override == $field_id )
				$wpdb->update( $this->form_table_name, array( 'form_email_from_override' => $wpdb->insert_id ), array( 'form_id' => $form_id ) );
			
			if ( $from_name == $field_id )
				$wpdb->update( $this->form_table_name, array( 'form_email_from_name_override' => $wpdb->insert_id ), array( 'form_id' => $form_id ) );
				
			if ( $notify == $field_id )
				$wpdb->update( $this->form_table_name, array( 'form_notification_email' => $wpdb->insert_id ), array( 'form_id' => $form_id ) );
			
			// Loop through our parents and update them to their new IDs
			if ( isset( $parents ) ) {
				foreach ( $parents as $k => $v ) {
					$wpdb->update( $this->field_table_name, array( 'field_parent' => $v ), array( 'form_id' => $form_id, 'field_parent' => $k ) );	
				}
			}
			
			// Loop through all of the IDs and update the rules if a match is found
			foreach ( $old_ids as $k => $v ) {
				$wpdb->query( "UPDATE $this->field_table_name SET field_rule = REPLACE(field_rule, $k, $v)" );
			}
		}
		
		
		
		echo '<p>' . __( 'Field import process complete.', 'visual-form-builder-pro' ) . '</p>';
		
		unset( $this->fields );
	}
	
	/**
	 * Process the entries from the XML import
	 *
	 * @since 1.7
	 *
	 */
	public function process_entries() {
		global $wpdb;
		
		if ( empty( $this->entries ) )
			return;
		
		foreach ( $this->entries as $entry ) {
			$form_id = ( array_key_exists( $entry['form_id'], $this->existing_forms ) ) ? $this->existing_forms[ $entry['form_id'] ] : $entry['form_id'];
				
			$data = array(
				'entries_id' 		=> $entry['entries_id'],
				'form_id' 			=> $form_id,
				'data' 				=> $entry['data'],
				'subject' 			=> $entry['subject'],
				'sender_name' 		=> $entry['sender_name'],
				'sender_email' 		=> $entry['sender_email'],
				'emails_to' 		=> $entry['emails_to'],
				'date_submitted'	=> $entry['date_submitted'],
				'ip_address'		=> $entry['ip_address'],
				'notes' 			=> $entry['notes']
			);
			
			$entry_id = $this->entry_exists( $entry['entries_id'] );
			
			// If the entry ID is a duplicate, it can't be used
			if ( $entry_id )
				$data['entries_id'] = '';
			
			$insert = $wpdb->insert( $this->entries_table_name, $data );
			
			// Display error message if the insert fails
			if ( !$insert )
				echo '<p>' . __( '<strong>Error: </strong> An entry could not be imported.', 'visual-form-builder-pro' ) . '</p>';
		}
		
		echo '<p>' . __( 'Entries import process complete.', 'visual-form-builder-pro' ) . '</p>';
		
		unset( $this->forms );
	}
	
	/**
	 * Check if a form already exists
	 *
	 * @since 1.7
	 *
	 * @param int $form The ID to check
	 * @return mixed Returns 0 or NULL if the form does not exist. Returns the form ID if it exists.
	 */
	public function form_exists( $form ) {
		global $wpdb;
		
		if ( is_int( $form ) ) {
			if ( 0 == $form )
				return 0;
			
			return $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM $this->form_table_name WHERE form_id = %d", $form ) );
		}
	}
	
	/**
	 * Check if a field already exists
	 *
	 * @since 1.7
	 *
	 * @param int $field The ID to check
	 * @return mixed Returns 0 or NULL if the field does not exist. Returns the field ID if it exists.
	 */
	public function field_exists( $field ) {
		global $wpdb;
		
		if ( is_int( $field ) ) {
			if ( 0 == $field )
				return 0;
			
			return $wpdb->get_var( $wpdb->prepare( "SELECT field_id FROM $this->field_table_name WHERE field_id = %d", $field ) );
		}
	}
	
	/**
	 * Check if an entry already exists
	 *
	 * @since 1.7
	 *
	 * @param int $entry The ID to check
	 * @return mixed Returns 0 or NULL if the entry does not exist. Returns the entry ID if it exists.
	 */
	public function entry_exists( $entry ) {
		global $wpdb;
		
		if ( is_int( $entry ) ) {
			if ( 0 == $entry )
				return 0;
			
			return $wpdb->get_var( $wpdb->prepare( "SELECT entries_id FROM $this->entries_table_name WHERE entries_id = %d", $entry ) );
		}
	}
	
	/**
	 * Handles the upload and initial parsing of the file to prepare for
	 *
	 * @since 1.7
	 * 
	 * @return bool False if error uploading or invalid file, true otherwise
	 */
	public function handle_upload() {
		$file = wp_import_handle_upload();

		if ( isset( $file['error'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'visual-form-builder-pro' ) . '</strong><br />';
			echo esc_html( $file['error'] ) . '</p>';
			return false;
		}
		else if ( ! file_exists( $file['file'] ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'visual-form-builder-pro' ) . '</strong><br />';
			printf( __( 'The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'visual-form-builder-pro' ), esc_html( $file['file'] ) );
			echo '</p>';
			return false;
		}

		$this->id = (int) $file['id'];
		$import_data = $this->parse( $file['file'] );
		if ( is_wp_error( $import_data ) ) {
			echo '<p><strong>' . __( 'Sorry, there has been an error.', 'visual-form-builder-pro' ) . '</strong><br />';
			echo esc_html( $import_data->get_error_message() ) . '</p>';
			return false;
		}
		
		$this->version = $import_data['version'];
		if ( $this->version > $this->max_version ) {
			echo '<div class="error"><p><strong>';
			printf( __( 'This Visual Form Builder Pro export file (version %s) may not be supported by this version of the importer. Please consider updating.', 'visual-form-builder-pro' ), esc_html( $import_data['version'] ) );
			echo '</strong></p></div>';
		}

		return true;
	}
	
	/**
	 * Parse an XML file
	 *
	 * @param string $file Path to XML file for parsing
	 * @return array Information gathered from the XML file
	 */
	public function parse( $file ) {
		if ( extension_loaded( 'simplexml' ) ) {
			$parser = new VFB_Parser_SimpleXML();
			$result = $parser->parse( $file );
			
			if ( is_wp_error( $result ) ) {
				echo '<p><strong>' . __( 'Sorry, there has been an error.', 'visual-form-builder-pro' ) . '</strong><br />';
				echo esc_html( $result->get_error_message() ) . '</p>';
				
				die();
			}
			
			// If SimpleXML succeeds or this is an invalid WXR file then return the results
			if ( ! is_wp_error( $result ) || 'SimpleXML_parse_error' != $result->get_error_code() )
				return $result;
		}
	}
}

/**
 * Parser that makes use of the SimpleXML PHP extension.
 */
class VFB_Parser_SimpleXML {
	/**
	 * Parse the XML file and return
	 *
	 * @since 1.7
	 *
	 * @param string $file The uploaded file
	 * @return mixed Returns and error if there are problems. Returns an array of all forms, fields, and entries.
	 */
	public function parse( $file ) {
		$forms = $fields = $entries = array();

		$internal_errors = libxml_use_internal_errors(true);
		$xml = simplexml_load_file( $file );
		
		// halt if loading produces an error
		if ( ! $xml )
			return new WP_Error( 'SimpleXML_parse_error', __( 'There was an error when reading this Visual Form Builder Pro export file', 'visual-form-builder-pro' ), libxml_get_errors() );
		
		$export_version = $xml->xpath('/rss/channel/vfb:export_version');
		if ( ! $export_version )
			return new WP_Error( 'VFB_parse_error', __( 'This does not appear to be an XML file, missing/invalid Visual Form Builder Pro version number', 'visual-form-builder-pro' ) );

		$export_version = (string) trim( $export_version[0] );
		// confirm that we are dealing with the correct file format
		if ( ! preg_match( '/^\d+\.\d+$/', $export_version ) )
			return new WP_Error( 'VFB_parse_error', __( 'This does not appear to be a XML file, missing/invalid Visual Form Builder Pro version number', 'visual-form-builder-pro' ) );
			
		//$base_url = $xml->xpath('/rss/channel/wp:base_site_url');
		//$base_url = (string) trim( $base_url[0] );

		$namespaces = $xml->getDocNamespaces();
		if ( ! isset( $namespaces['vfb'] ) )
			$namespaces['vfb'] = 'http://matthewmuro.com/export/1.9/';
		
		// grab authors
		foreach ( $xml->xpath('/rss/channel/vfb:form') as $form_arr ) {
			$a = $form_arr->children( $namespaces['vfb'] );
			
			$forms[] = array(
				'form_id' 						=> (int) 	$a->form_id,
				'form_key' 						=> (string) $a->form_key,
				'form_title' 					=> (string) $a->form_title,
				'form_email_subject' 			=> (string) $a->form_email_subject,
				'form_email_to' 				=> (string) $a->form_email_to,
				'form_email_from' 				=> (string) $a->form_email_from,
				'form_email_from_name' 			=> (string) $a->form_email_from_name,
				'form_email_from_override' 		=> (string) $a->form_email_from_override,
				'form_email_from_name_override' => (string) $a->form_email_from_name_override,
				'form_success_type' 			=> (string) $a->form_success_type,
				'form_success_message' 			=> (string) $a->form_success_message,
				'form_notification_setting' 	=> (string) $a->form_notification_setting,
				'form_notification_email_name' 	=> (string) $a->form_notification_email_name,
				'form_notification_email_from' 	=> (string) $a->form_notification_email_from,
				'form_notification_email' 		=> (string) $a->form_notification_email,
				'form_notification_subject' 	=> (string) $a->form_notification_subject,
				'form_notification_message' 	=> (string) $a->form_notification_message,
				'form_notification_entry' 		=> (string) $a->form_notification_entry,
				'form_email_design' 			=> (string) $a->form_email_design,
				'form_paypal_setting' 			=> (string) $a->form_paypal_setting,
				'form_paypal_email' 			=> (string) $a->form_paypal_email,
				'form_paypal_currency' 			=> (string) $a->form_paypal_currency,
				'form_paypal_shipping' 			=> (string) $a->form_paypal_shipping,
				'form_paypal_tax' 				=> (string) $a->form_paypal_tax,
				'form_paypal_field_price' 		=> (string) $a->form_paypal_field_price,
				'form_paypal_item_name' 		=> (string) $a->form_paypal_item_name,
				'form_label_alignment' 			=> (string) $a->form_label_alignment,
				'form_verification' 			=> (int) 	$a->form_verification,
				'form_entries_allowed' 			=> (int) 	$a->form_entries_allowed,
				'form_entries_schedule' 		=> (string) $a->form_entries_schedule
			);
		}
		
		
		foreach ( $xml->xpath('/rss/channel/vfb:field') as $field_arr ) {
			$a = $field_arr->children( $namespaces['vfb'] );
			
			$fields[] = array(
				'field_id' 			=> (int) 	$a->field_id,
				'form_id' 			=> (int) 	$a->form_id,
				'field_key' 		=> (string) $a->field_key,
				'field_type' 		=> (string) $a->field_type,
				'field_options' 	=> (string) $a->field_options,
				'field_options_other'=> (string) $a->field_options_other,
				'field_description' => (string) $a->field_description,
				'field_name' 		=> (string) $a->field_name,
				'field_sequence' 	=> (int) 	$a->field_sequence,
				'field_parent' 		=> (int) 	$a->field_parent,
				'field_validation' 	=> (string) $a->field_validation,
				'field_required' 	=> (string) $a->field_required,
				'field_size' 		=> (string) $a->field_size,
				'field_css' 		=> (string) $a->field_css,
				'field_layout' 		=> (string) $a->field_layout,
				'field_default' 	=> (string) $a->field_default,
				'field_rule_setting'=> (int) 	$a->field_rule_setting,
				'field_rule'		=> (string) $a->field_rule
			);
		}
		
		foreach ( $xml->xpath('/rss/channel/vfb:entry') as $entry_arr ) {
			$a = $entry_arr->children( $namespaces['vfb'] );
			
			$entries[] = array(
				'entries_id' 		=> (int) 	$a->entries_id,
				'form_id' 			=> (int) 	$a->form_id,
				'data' 				=> (string) $a->data,
				'subject' 			=> (string) $a->subject,
				'sender_name' 		=> (string) $a->sender_name,
				'sender_email' 		=> (string) $a->sender_email,
				'emails_to' 		=> (string) $a->emails_to,
				'date_submitted' 	=> (string) $a->date_submitted,
				'ip_address' 		=> (string) $a->ip_address,
				'notes' 			=> (string) $a->notes
			);
		}

		return array(
			'forms' 	=> $forms,
			'fields' 	=> $fields,
			'entries' 	=> $entries,
			//'base_url' => $base_url,
			'version' 	=> $export_version
		);
	}
}

?>