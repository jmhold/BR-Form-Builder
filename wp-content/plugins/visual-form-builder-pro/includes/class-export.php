<?php
/**
 * Class that builds our Entries table
 * 
 * @since 1.2
 */
class VisualFormBuilder_Pro_Export {
	
	protected $export_version = '2.0';
	
	public function __construct(){
		global $wpdb;
		
		// CSV delimiter
		$this->delimiter = apply_filters( 'vfb_csv_delimiter', ',' );
		
		// Setup global database table names
		$this->field_table_name 	= $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name 		= $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name 	= $wpdb->prefix . 'vfb_pro_entries';
		
		add_action( 'admin_init', array( &$this, 'display' ) );
		
		$this->process_export_action();
	}
	
	/**
	 * Display the export form
	 *
	 * @since 1.7
	 *
	 */
	public function display(){
		global $wpdb;
		
		// Query to get all forms
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$where = apply_filters( 'vfb_pre_get_forms_export', '' );
		$forms = $wpdb->get_results( "SELECT * FROM $this->form_table_name WHERE 1=1 $where ORDER BY $order" );

		?>
        <form method="post" id="vfb-export">
        	<p><?php _e( 'Backup and save some or all of your Visual Form Builder Pro data.', 'visual-form-builder-pro' ); ?></p>
        	<p><?php _e( 'Once you have saved the file, you will be able to import Visual Form Builder Pro data from this site into another site.', 'visual-form-builder-pro' ); ?></p>
        	<h3><?php _e( 'Choose what to export', 'visual-form-builder-pro' ); ?></h3>
        	
        	<p><label><input type="radio" name="content" value="all" checked="checked" /> <?php _e( 'All data', 'visual-form-builder-pro' ); ?></label></p>
        	<p class="description"><?php _e( 'This will contain all of your forms, fields, entries, and email design settings.', 'visual-form-builder-pro' ); ?></p>
        	
        	<p><label><input type="radio" name="content" value="forms" /> <?php _e( 'Forms', 'visual-form-builder-pro' ); ?></label></p>
        	
        	<ul id="forms-filters" class="vfb-export-filters">
        		<li><p class="description"><?php _e( 'This will contain all of your forms, fields, and email design settings.', 'visual-form-builder-pro' ); ?></p></li>
        		<li>
		        	<label for="form_id"><?php _e( 'Forms', 'visual-form-builder-pro' ); ?>:</label> 
		            <select name="forms_form_id">
		            	<option value="0">All</option>
					<?php
						foreach ( $forms as $form ) {
							echo '<option value="' . $form->form_id . '" id="' . $form->form_key . '">' . stripslashes( $form->form_title ) . '</option>';
						}
					?>
					</select>
        		</li>
        	</ul>
        	
        	<p><label><input type="radio" name="content" value="entries" /> <?php _e( 'Entries', 'visual-form-builder-pro' ); ?></label></p>
        	
        	<ul id="entries-filters" class="vfb-export-filters">
        		<li><p class="description"><?php _e( 'This will export entries in either .csv, .txt, or .xls and cannot be used with the Import.  If you need to import entries on another site, please use the All data option above.', 'visual-form-builder-pro' ); ?></p></li>
        		<li>
        			<label for="format"><?php _e( 'Format', 'visual-form-builder-pro' ); ?>:</label>
        			<select name="format">
        				<option value="csv" selected="selected"><?php _e( 'Comma Separated (.csv)', 'visual-form-builder-pro' ); ?></option>
        				<option value="txt"><?php _e( 'Tab Delimited (.txt)', 'visual-form-builder-pro' ); ?></option>
        				<option value="xls"><?php _e( 'Excel (.xls)', 'visual-form-builder-pro' ); ?></option>
        			</select>
        		</li>
        		<li>
		        	<label for="form_id"><?php _e( 'Form', 'visual-form-builder-pro' ); ?>:</label> 
		            <select name="entries_form_id">
					<?php
						foreach ( $forms as $form ) {
							echo '<option value="' . $form->form_id . '" id="' . $form->form_key . '">' . stripslashes( $form->form_title ) . '</option>';
						}
					?>
					</select>
        		</li>
        		<li>
        			<label><?php _e( 'Date Range', 'visual-form-builder-pro' ); ?>:</label>
        			<select name="entries_start_date">
        				<option value="0">Start Date</option>
        				<?php $this->months_dropdown(); ?>
        			</select>
        			<select name="entries_end_date">
        				<option value="0">End Date</option>
        				<?php $this->months_dropdown(); ?>
        			</select>
        		</li>
        	</ul>
        <?php submit_button( __( 'Download Export File', 'visual-form-builder-pro' ) ); ?>
        </form>
<?php
	}
	
	/**
	 * Export entire form database or just form data as an XML
	 *
	 * @since 1.7
	 *
	 * @param array $args Filters defining what should be included in the export
	 */
	public function export( $args = array() ) {
		global $wpdb;
		
		$defaults = array( 
			'content' 		=> 'all',
			'form_id' 		=> 0,
			'start_date'	=> false, 
			'end_date' 		=> false,
		);
		$args = wp_parse_args( $args, $defaults );
		
		$where = '';
		
		if ( 'forms' == $args['content'] && 0 !== $args['form_id'] )
			$where .= $wpdb->prepare( " AND form_id = %d", $args['form_id'] );
				
		$forms = $wpdb->get_results( "SELECT * FROM $this->form_table_name WHERE 1=1 $where" );
		$fields = $wpdb->get_results( "SELECT * FROM $this->field_table_name WHERE 1=1 $where" );
		$entries = $wpdb->get_results( "SELECT * FROM $this->entries_table_name WHERE 1=1 $where" );
		
		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( ! empty($sitename) ) $sitename .= '.';
		$filename = $sitename . 'vfb-pro.' . date( 'Y-m-d' ) . '.xml';
		
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Content-Type: text/xml; charset=' . get_option( 'blog_charset' ), true );
		
		echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";
		
		// Output the correct generator type
		the_generator( 'export' );
		?>
<!-- This is a Visual Form Builder Pro RSS file generated by WordPress as an export of your forms and/or data. -->
<!-- It contains information about forms, fields, entries, and email design settings from Visual Form Builder Pro. -->
<!-- You may use this file to transfer that content from one site to another. -->

<!-- To import this information into a WordPress site follow these steps: -->
<!-- 1. Log in to that site as an administrator. -->
<!-- 2. Go to Visual Form Builder Pro: Import in the WordPress admin panel. -->
<!-- 3. Select and Upload this file using the form provided on that page. -->
<!-- 4. Visual Form Builder Pro will then import each of the forms, fields, entries, and email design settings -->
<!--    contained in this file into your site. -->
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:vfb="http://matthewmuro.com/export/1.7/"
>
	<channel>
		<title><?php bloginfo_rss( 'name' ); ?></title>
		<link><?php bloginfo_rss( 'url' ); ?></link>
		<description><?php bloginfo_rss( 'description' ); ?></description>
		<pubDate><?php echo date( 'D, d M Y H:i:s +0000' ); ?></pubDate>
		<language><?php bloginfo_rss( 'language' ); ?></language>
		<vfb:export_version><?php echo $this->export_version; ?></vfb:export_version>
		<?php do_action( 'rss2_head' ); ?>
		
		<?php foreach ( $forms as $form ) : ?>
			<vfb:form>
				<vfb:form_id><?php echo $form->form_id; ?></vfb:form_id>
				<vfb:form_key><?php echo $form->form_key; ?></vfb:form_key>
				<vfb:form_title><?php echo $this->cdata( $form->form_title ); ?></vfb:form_title>
				<vfb:form_email_subject><?php echo $this->cdata( $form->form_email_subject ); ?></vfb:form_email_subject>
				<vfb:form_email_to><?php echo $this->cdata( $form->form_email_to ); ?></vfb:form_email_to>
				<vfb:form_email_from><?php echo $this->cdata( $form->form_email_from ); ?></vfb:form_email_from>
				<vfb:form_email_from_name><?php echo $this->cdata( $form->form_email_from_name ); ?></vfb:form_email_from_name>
				<vfb:form_email_from_override><?php echo $form->form_email_from_override; ?></vfb:form_email_from_override>
				<vfb:form_email_from_name_override><?php echo $form->form_email_from_name_override; ?></vfb:form_email_from_name_override>
				<vfb:form_success_type><?php echo $form->form_success_type; ?></vfb:form_success_type>
				<vfb:form_success_message><?php echo $this->cdata( $form->form_success_message ); ?></vfb:form_success_message>
				<vfb:form_notification_setting><?php echo $form->form_notification_setting; ?></vfb:form_notification_setting>
				<vfb:form_notification_email_name><?php echo $this->cdata( $form->form_notification_email_name ); ?></vfb:form_notification_email_name>
				<vfb:form_notification_email_from><?php echo $this->cdata( $form->form_notification_email_from ); ?></vfb:form_notification_email_from>
				<vfb:form_notification_email><?php echo $form->form_notification_email; ?></vfb:form_notification_email>
				<vfb:form_notification_subject><?php echo $this->cdata( $form->form_notification_subject ); ?></vfb:form_notification_subject>
				<vfb:form_notification_message><?php echo $this->cdata( $form->form_notification_message ); ?></vfb:form_notification_message>
				<vfb:form_notification_entry><?php echo $form->form_notification_entry; ?></vfb:form_notification_entry>
				<vfb:form_email_design><?php echo $this->cdata( $form->form_email_design ); ?></vfb:form_email_design>
				<vfb:form_paypal_setting><?php echo $form->form_paypal_setting; ?></vfb:form_paypal_setting>
				<vfb:form_paypal_email><?php echo $this->cdata( $form->form_paypal_email ); ?></vfb:form_paypal_email>
				<vfb:form_paypal_currency><?php echo $form->form_paypal_currency; ?></vfb:form_paypal_currency>
				<vfb:form_paypal_shipping><?php echo $form->form_paypal_shipping; ?></vfb:form_paypal_shipping>
				<vfb:form_paypal_tax><?php echo $form->form_paypal_tax; ?></vfb:form_paypal_tax>
				<vfb:form_paypal_field_price><?php echo $this->cdata( $form->form_paypal_field_price ); ?></vfb:form_paypal_field_price>
				<vfb:form_paypal_item_name><?php echo $this->cdata( $form->form_paypal_item_name ); ?></vfb:form_paypal_item_name>
				<vfb:form_label_alignment><?php echo $form->form_label_alignment; ?></vfb:form_label_alignment>
				<vfb:form_verification><?php echo $form->form_verification; ?></vfb:form_verification>
				<vfb:form_entries_allowed><?php echo $form->form_entries_allowed; ?></vfb:form_entries_allowed>
				<vfb:form_entries_schedule><?php echo $this->cdata( $form->form_entries_schedule ); ?></vfb:form_entries_schedule>
			</vfb:form>
		<?php endforeach; ?>
		
		<?php
		if ( in_array( $args['content'], array( 'all', 'forms' ) ) ) : 
			foreach ( $fields as $field ) :
		?>
			<vfb:field>
				<vfb:field_id><?php echo $field->field_id; ?></vfb:field_id>
				<vfb:form_id><?php echo $field->form_id; ?></vfb:form_id>
				<vfb:field_key><?php echo $field->field_key; ?></vfb:field_key>
				<vfb:field_type><?php echo $field->field_type; ?></vfb:field_type>
				<vfb:field_options><?php echo $this->cdata( $field->field_options ); ?></vfb:field_options>
				<vfb:field_options_other><?php echo $this->cdata( $field->field_options_other ); ?></vfb:field_options_other>
				<vfb:field_description><?php echo $this->cdata( $field->field_description ); ?></vfb:field_description>
				<vfb:field_name><?php echo $this->cdata( $field->field_name ); ?></vfb:field_name>
				<vfb:field_sequence><?php echo $field->field_sequence; ?></vfb:field_sequence>
				<vfb:field_parent><?php echo $field->field_parent; ?></vfb:field_parent>
				<vfb:field_required><?php echo $field->field_required; ?></vfb:field_required>
				<vfb:field_validation><?php echo $field->field_validation; ?></vfb:field_validation>
				<vfb:field_size><?php echo $field->field_size; ?></vfb:field_size>
				<vfb:field_css><?php echo $field->field_css; ?></vfb:field_css>
				<vfb:field_layout><?php echo $field->field_layout; ?></vfb:field_layout>
				<vfb:field_default><?php echo $this->cdata( $field->field_default ); ?></vfb:field_default>
				<vfb:field_rule_setting><?php echo $field->field_rule_setting; ?></vfb:field_rule_setting>
				<vfb:field_rule><?php echo $this->cdata( $field->field_rule ); ?></vfb:field_rule>
			</vfb:field>
		<?php
			endforeach;
		endif;
		?>
		
		<?php
		if ( in_array( $args['content'], array( 'all' ) ) ) : 
			foreach ( $entries as $entry ) :
		?>
			<vfb:entry>
				<vfb:entries_id><?php echo $entry->entries_id; ?></vfb:entries_id>
				<vfb:form_id><?php echo $entry->form_id; ?></vfb:form_id>
				<vfb:data><?php echo $this->cdata( $entry->data ); ?></vfb:data>
				<vfb:subject><?php echo $this->cdata( $entry->subject ); ?></vfb:subject>
				<vfb:sender_name><?php echo $this->cdata( $entry->sender_name ); ?></vfb:sender_name>
				<vfb:sender_email><?php echo $this->cdata( $entry->sender_email ); ?></vfb:sender_email>
				<vfb:emails_to><?php echo $this->cdata( $entry->emails_to ); ?></vfb:emails_to>
				<vfb:date_submitted><?php echo $entry->date_submitted; ?></vfb:date_submitted>
				<vfb:ip_address><?php echo $entry->ip_address; ?></vfb:ip_address>
				<vfb:notes><?php echo $this->cdata( $entry->notes ); ?></vfb:notes>
			</vfb:entry>
		<?php
			endforeach;
		endif;
		?>
	</channel>
</rss>
		<?php
	}
	
	/**
	 * Build the entries export array
	 *
	 * @since 1.7
	 *
	 * @param array $args Filters defining what should be included in the export
	 */
	public function export_entries( $args = array() ) {
		global $wpdb;
		
		$defaults = array( 
			'content' 		=> 'entries',
			'format' 		=> 'csv',
			'form_id' 		=> 0,
			'start_date' 	=> false, 
			'end_date' 		=> false,
		);
		$args = wp_parse_args( $args, $defaults );
		
		$where = '';
		
		if ( 'entries' == $args['content'] ) {
			if ( 0 !== $args['form_id'] )
				$where .= $wpdb->prepare( " AND form_id = %d", $args['form_id'] );
				
			if ( $args['start_date'] )
				$where .= $wpdb->prepare( " AND date_submitted >= %s", date( 'Y-m-d', strtotime( $args['start_date'] ) ) );
				
			if ( $args['end_date'] )
				$where .= $wpdb->prepare( " AND date_submitted < %s", date( 'Y-m-d', strtotime('+1 month', strtotime( $args['end_date'] ) ) ) );
		}
		
		$entries = $wpdb->get_results( "SELECT * FROM $this->entries_table_name WHERE 1=1 $where" );
		$form_key = $wpdb->get_var( $wpdb->prepare( "SELECT form_key, form_title FROM $this->form_table_name WHERE form_id = %d", $args['form_id'] ) );
		$form_title = $wpdb->get_var( null, 1 );
		
		$sitename = sanitize_key( get_bloginfo( 'name' ) );
		if ( ! empty($sitename) ) $sitename .= '.';
		$filename = $sitename . 'vfb-pro.' . "$form_key." . date( 'Y-m-d' ) . ".{$args['format']}";
		
		// Set content type based on file format
		switch ( $args['format'] ) {
			case 'csv' :
				$content_type = 'text/csv';
			break;
			case 'txt' :
				$content_type = 'text/plain';
			break;
			case 'xls' :
				$content_type = 'application/vnd.ms-excel';
			break;
		}
		
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( "Content-Type: $content_type; charset=" . get_option( 'blog_charset' ), true );
			
		// If there's entries returned, do our CSV stuff
		if ( $entries ) :
			
			// Setup our default columns
			$cols = array(
				'entries_id' 		=> array( 'header' => __( 'Entries ID' , 'visual-form-builder-pro'), 'data' => array() ),
				'date_submitted' 	=> array( 'header' => __( 'Date Submitted' , 'visual-form-builder-pro'), 'data' => array() ),
				'ip_address' 		=> array( 'header' => __( 'IP Address' , 'visual-form-builder-pro'), 'data' => array() ),
				'subject' 			=> array( 'header' => __( 'Email Subject' , 'visual-form-builder-pro'), 'data' => array() ),
				'sender_name' 		=> array( 'header' => __( 'Sender Name' , 'visual-form-builder-pro'), 'data' => array() ),
				'sender_email' 		=> array( 'header' => __( 'Sender Email' , 'visual-form-builder-pro'), 'data' => array() ),
				'emails_to' 		=> array( 'header' => __( 'Emailed To' , 'visual-form-builder-pro'), 'data' => array() )
			);
			
			// Initialize row index at 0
			$row = 0;
			
			// Loop through all entries
			foreach ( $entries as $entry ) {
				// Loop through each entry and its fields
				foreach ( $entry as $key => $value ) {
					// Handle each column in the entries table
					switch ( $key ) {
						case 'entries_id':
						case 'date_submitted':
						case 'ip_address':
						case 'subject':
						case 'sender_name':
						case 'sender_email':
							$cols[ $key ][ 'data' ][ $row ] = $value;
						break;
						
						case 'emails_to':
							$cols[ $key ][ 'data' ][ $row ] = implode( ',', maybe_unserialize( $value ) );
						break;
						
						case 'data':
							// Unserialize value only if it was serialized
							$fields = maybe_unserialize( $value );
							
							// Loop through our submitted data
							foreach ( $fields as $field_key => $field_value ) :
								if ( !is_array( $field_value ) ) {

									// Replace quotes for the header
									$header = str_replace( '"', '""', ucwords( $field_key ) );

									// Replace all spaces for each form field name
									$field_key = preg_replace( '/(\s)/i', '', $field_key );
									
									// Find new field names and make a new column with a header
									if ( !array_key_exists( $field_key, $cols ) )
										$cols[ $field_key ] = array( 'header' => $header, 'data' => array() );									
									
									// Get rid of single quote entity
									$field_value = str_replace( '&#039;', "'", $field_value );
									
									// Load data, row by row
									$cols[ $field_key ][ 'data' ][ $row ] = str_replace( '"', '""', stripslashes( html_entity_decode( $field_value ) ) );
								}
								else {
									// Cast each array as an object
									$obj = (object) $field_value;
									
									switch ( $obj->type ) {
										case 'fieldset' :
										case 'section' :
										case 'instructions' :
										case 'page-break' :
										case 'verification' :
										case 'secret' :
										case 'submit' :
										break;
										
										default :
											// Replace quotes for the header
											$header = str_replace( '"', '""', $obj->name );
											
											// Replace all spaces for each form field name
											$field_key = preg_replace( '/(\s)/i', '', strtolower( $obj->name ) );
											
											// Find new field names and make a new column with a header
											if ( !array_key_exists( $field_key, $cols ) )
												$cols[ $field_key ] = array( 'header' => $header, 'data' => array() );									
											
											// Get rid of single quote entity
											$obj->value = str_replace( '&#039;', "'", $obj->value );
											
											// Load data, row by row
											$cols[ $field_key ][ 'data' ][ $row ] = str_replace( '"', '""', stripslashes( html_entity_decode( $obj->value ) ) );

										break;
									}	//end switch
								}	//end if is_array check
							endforeach;	//end fields loop
						break;	//end entries switch
					}	//end entries data loop
				}	//end loop through entries
				
				$row++;
			}//end if entries exists check
			
			if ( in_array( $args['format'], array( 'csv', 'txt' ) ) )
				$this->csv_tab( $cols, $row, $args['format'] );
			elseif ( 'xls' == $args['format'] )
				$this->xls( $cols, $row, $form_title );
			
		endif;
	}
	
	/**
	 * Return the entries data formatted for CSV
	 *
	 * @since 1.7
	 *
	 * @param array $cols The multidimensional array of entries data
	 * @param int $row The row index
	 */
	public function csv_tab( $cols, $row, $format ) {
		
		// Only use quotes for CSV
		$quote = '"';
		
		// Override delimiter if tab separated
		if ( 'txt' == $format ) {
			$this->delimiter = "\t";
			$quote = '';
		}
		
		// Setup our CSV vars
		$csv_headers = NULL;
		$csv_rows = array();
		
		// Loop through each column
		foreach ( $cols as $data ) {
			// End our header row, if needed
			if ( $csv_headers )
				$csv_headers .= $this->delimiter;
			
			// Build our headers
			$csv_headers .= stripslashes( htmlentities( $data['header'] ) );
			
			// Loop through each row of data and add to our CSV
			for ( $i = 0; $i < $row; $i++ ) {
				// End our row of data, if needed
				if ( array_key_exists( $i, $csv_rows ) && !empty( $csv_rows[ $i ] ) )
					$csv_rows[ $i ] .= $this->delimiter;
				elseif ( !array_key_exists( $i, $csv_rows ) )
					$csv_rows[ $i ] = '';
				
				// Add a starting quote for this row's data
				$csv_rows[ $i ] .= $quote;
				
				// If there's data at this point, add it to the row
				if ( array_key_exists( $i, $data[ 'data' ] ) )
					$csv_rows[ $i ] .=  $data[ 'data' ][ $i ];
				
				// Add a closing quote for this row's data
				$csv_rows[ $i ] .= $quote;				
			}			
		}
		
		// Print headers for the CSV
		echo "$csv_headers\n";
		
		// Print each row of data for the CSV
		foreach ( $csv_rows as $row ) {
			echo "$row\n";
		}
	}
	
	/**
	 * Return the entries data formatted for MS Excel (XLS)
	 *
	 * @since 1.7
	 *
	 * @param array $cols The multidimensional array of entries data
	 * @param int $row The row index
	 * @param string $form_title The form title, inserted into the Excel Worksheet tab
	 */
	public function xls( $cols, $row, $form_title ) {
		// Strip out illegal characters and truncate at 31 characters
		$title = preg_replace ( '/[\\\|:|\/|\?|\*|\[|\]]/', '', $form_title );
        $title = substr ( $title, 0, 31 );
        
        // Setup our CSV vars
		$csv_headers = NULL;
		$csv_rows = array();
		
		echo '<?xml version="1.0" encoding="' . get_bloginfo('charset') . "\" ?>\n";
	?>
<Workbook
	xmlns="urn:schemas-microsoft-com:office:spreadsheet"
	xmlns:o="urn:schemas-microsoft-com:office:office"
	xmlns:x="urn:schemas-microsoft-com:office:excel"
	xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
	xmlns:html="http://www.w3.org/TR/REC-html40">
	<Styles> 
		<Style ss:ID="s1">
			<Font ss:Size="14" ss:Bold="1" />
			<Interior ss:Color="#C0C0C0" ss:Pattern="Solid" />
			<Borders>
				<Border ss:Position="Bottom" ss:Color="#000000" ss:Weight="1" ss:LineStyle="Continuous" />
			</Borders>
		</Style>
		<Style ss:ID="s2">
			<Font ss:Size="12" ss:Bold="0" />
		</Style>
		<Style ss:ID="sDT">
			<NumberFormat ss:Format="Short Date" />
		</Style>
		<Style ss:ID="link">
			<Font ss:Color="Blue" />
		</Style>
	</Styles>
	<Worksheet ss:Name="Sheet1">
		<Table x:FullColumns="1" x:FullRows="1" ss:DefaultRowHeight="14">
			<Row ss:StyleID="s1">
			<?php foreach ( $cols as $data ) : ?>
				<Cell><Data ss:Type="String"><?php echo stripslashes( htmlentities( $data['header'] ) ); ?></Data></Cell>
			<?php endforeach; ?>
			</Row>	
		<?php
			foreach ( $cols as $data ) :
			
				// Loop through each row of data and add to our CSV
				for ( $i = 0; $i < $row; $i++ ) {
					// End our row of data, if needed
					if ( array_key_exists( $i, $csv_rows ) && !empty( $csv_rows[ $i ] ) )
						$csv_rows[ $i ] .= '';
					elseif ( !array_key_exists( $i, $csv_rows ) )
						$csv_rows[ $i ] = '';
									
					// If there's data at this point, add it to the row
					if ( array_key_exists( $i, $data[ 'data' ] ) ) {
						// Set type to number if digit is found and no longer than 15 characters
						if( preg_match( "/^-?\d+(?:[.,]\d+)?$/", $data[ 'data' ][ $i ] ) && ( strlen( $data[ 'data' ][ $i ] ) < 15 ) ) {
							$type    = 'Number';
							$style   = 's2';
						}
						// Sniff for valid dates; should look something like 2010-07-14, 7/14/2010, etc
						elseif( preg_match( "/^(\d{1,2}|\d{4})[\/\-]\d{1,2}[\/\-](\d{1,2}|\d{4})([^\d].+)?$/", $data[ 'data' ][ $i ] ) &&
						( $timestamp = strtotime( $data[ 'data' ][ $i ] ) ) && ( $timestamp > 0 ) && ( $timestamp < strtotime( '+500 years' ) ) ) {
							$type    = 'DateTime';
							$item    = strftime( '%Y-%m-%dT%H:%M:%S', $timestamp );
							$style   = 'sDT';
						}
						else {
							$type    = 'String';
							$style   = 's2';
						}
						
						$csv_rows[ $i ] .= sprintf( "<Cell ss:StyleID=\"%s\"><Data ss:Type=\"%s\">%s</Data></Cell>\n", $style, $type, stripslashes( htmlentities( $data[ 'data' ][ $i ], ENT_QUOTES, 'UTF-8' ) ) );
					}
				}
		
			endforeach;
			 
			// Print each row of data for the CSV
			foreach ( $csv_rows as $row ) {
				echo "<Row>$row</Row>\n\t";
			}
		?>
		</Table>
	</Worksheet>
</Workbook>
	<?php
	}
	
	/**
	 * Return the selected export type
	 *
	 * @since 1.7
	 *
	 * @return string|bool The type of export
	 */
	public function export_action() {
		if ( isset( $_REQUEST['content'] ) )
			return $_REQUEST['content'];
	
		return false;
	}
	
	/**
	 * Determine which export process to run
	 *
	 * @since 1.7
	 *
	 */
	public function process_export_action() {
		
		$args = array();
		
		if ( ! isset( $_REQUEST['content'] ) || 'all' == $_REQUEST['content'] )
			$args['content'] = 'all';
		elseif ( 'forms' == $_REQUEST['content'] ) {
			$args['content'] = 'forms';
			
			if ( $_REQUEST['forms_form_id'] )
				$args['form_id'] = (int) $_REQUEST['forms_form_id'];
		}
		elseif ( 'entries' == $_REQUEST['content'] ) {
			$args['content'] = 'entries';
			
			if ( $_REQUEST['format'] )
				$args['format'] = (string) $_REQUEST['format'];
				
			if ( $_REQUEST['entries_form_id'] )
				$args['form_id'] = (int) $_REQUEST['entries_form_id'];
			
			if ( $_REQUEST['entries_start_date'] || $_REQUEST['entries_end_date'] ) {
				$args['start_date'] = $_REQUEST['entries_start_date'];
				$args['end_date'] = $_REQUEST['entries_end_date'];
			}
		}
		
		switch( $this->export_action() ) {
			case 'all' :
			case 'forms' :
				$this->export( $args );
				die(1);
			break;
			
			case 'entries' :
				$this->export_entries( $args );
				die(1);
			break;
		}
	}
	
	/**
	 * Wrap given string in XML CDATA tag.
	 *
	 * @since 1.7
	 *
	 * @param string $str String to wrap in XML CDATA tag.
	 * @return string
	 */
	function cdata( $str ) {
		if ( seems_utf8( $str ) == false )
			$str = utf8_encode( $str );

		$str = '<![CDATA[' . str_replace( ']]>', ']]]]><![CDATA[>', $str ) . ']]>';

		return $str;
	}
	
	/**
	 * Display Year/Month filter
	 * 
	 * @since 1.7
	 */
	public function months_dropdown() {
		global $wpdb, $wp_locale;
		
		$where = apply_filters( 'vfb_pre_get_entries', '' );
		
	    $months = $wpdb->get_results( "
			SELECT DISTINCT YEAR( forms.date_submitted ) AS year, MONTH( forms.date_submitted ) AS month
			FROM $this->entries_table_name AS forms
			WHERE 1=1 $where
			ORDER BY forms.date_submitted DESC
		" );

		$month_count = count( $months );

		if ( !$month_count || ( 1 == $month_count && 0 == $months[0]->month ) )
			return;
		
		$m = isset( $_REQUEST['m'] ) ? (int) $_REQUEST['m'] : 0;
?>
<?php
		foreach ( $months as $arc_row ) {
			if ( 0 == $arc_row->year )
				continue;
			
			$month = zeroise( $arc_row->month, 2 );
			$year = $arc_row->year;

			printf( "<option value='%s'>%s</option>\n",
				esc_attr( $arc_row->year . '-' . $month ),
				sprintf( __( '%1$s %2$d' ), $wp_locale->get_month( $month ), $year )
			);
		}
?>
<?php
	}
}
?>