<?php
/*
Plugin Name: Visual Form Builder Pro
Plugin URI: http://vfb.matthewmuro.com
Description: Dynamically build forms using a simple interface. Forms include jQuery validation, a basic logic-based verification system, and entry tracking.
Author: Matthew Muro
Author URI: http://matthewmuro.com
Version: 2.1.2
*/

// Set to true to load uncompressed and unminified scripts and stylesheets
define( 'VFB_SCRIPT_DEBUG', false );

/**
 * Template tag function
 * 
 * @since 1.9
 * @echo class function VFB form code
 */
function vfb_pro( $args = '' ){
	// Create new class instance
	$template_tag = new Visual_Form_Builder_Pro();
	
	// Parse the arguments into an array
	$args = wp_parse_args( $args );
	
	// Print the output
	echo $template_tag->form_code( $args );
}

// Add action so themes can call via do_action( 'vfb_pro', $id );
add_action( 'vfb_pro', 'vfb_pro' );

// Instantiate new class
$visual_form_builder_pro = new Visual_Form_Builder_Pro();

// Visual Form Builder class
class Visual_Form_Builder_Pro{
	
	/**
	 * The DB version. Used for SQL install and upgrades.
	 *
	 * Should only be changed when needing to change SQL
	 * structure or custom capabilities.
	 *
	 * @since 1.0
	 * @var string
	 * @access protected
	 */
	protected $vfb_db_version = '2.1';
	
	/**
	 * The plugin API
	 *
	 * @since 1.0
	 * @var string
	 * @access protected
	 */
	protected $api_url = 'http://matthewmuro.com/plugin-api/';
	
	/**
	 * Flag used to add scripts to front-end only once
	 *
	 * @since 1.0
	 * @var string
	 * @access protected
	 */
	protected $add_scripts = false;
	
	/**
	 * An array of countries to be used throughout plugin
	 *
	 * @since 1.0
	 * @var array
	 * @access public
	 */
	public $countries = array( "", "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda", "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia", "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso", "Burundi", "Cambodia", "Cameroon", "Canada", "Cape Verde", "Central African Republic", "Chad", "Chile", "China", "Colombi", "Comoros", "Congo (Brazzaville)", "Congo", "Costa Rica", "Cote d\'Ivoire", "Croatia", "Cuba", "Cyprus", "Czech Republic", "Denmark", "Djibouti", "Dominica", "Dominican Republic", "East Timor (Timor Timur)", "Ecuador", "Egypt", "El Salvador", "Equatorial Guinea", "Eritrea", "Estonia", "Ethiopia", "Fiji", "Finland", "France", "Gabon", "Gambia, The", "Georgia", "Germany", "Ghana", "Greece", "Grenada", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary", "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica", "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia", "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Macedonia", "Madagascar", "Malawi", "Malaysia", "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico", "Micronesia", "Moldova", "Monaco", "Mongolia", "Morocco", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepa", "Netherlands", "New Zealand", "Nicaragua", "Niger", "Nigeria", "Norway", "Oman", "Pakistan", "Palau", "Panama", "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar", "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia", "Saint Vincent", "Samoa", "San Marino", "Sao Tome and Principe", "Saudi Arabia", "Senegal", "Serbia and Montenegro", "Seychelles", "Sierra Leone", "Singapore", "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "Spain", "Sri Lanka", "Sudan", "Suriname", "Swaziland", "Sweden", "Switzerland", "Syria", "Taiwan", "Tajikistan", "Tanzania", "Thailand", "Togo", "Tonga", "Trinidad and Tobago", "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates", "United Kingdom", "United States of America", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City", "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe" );
	
	/**
	 * Constructor. Register core filters and actions.
	 *
	 * @access public
	 */
	public function __construct(){
		global $wpdb;

		// Setup global database table names
		$this->field_table_name 	= $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name 		= $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name 	= $wpdb->prefix . 'vfb_pro_entries';
		
		// Add suffix to load dev files
		$this->load_dev_files = ( defined( 'VFB_SCRIPT_DEBUG' ) && VFB_SCRIPT_DEBUG ) ? '.dev' : '';
		
		// Make sure we are in the admin before proceeding.
		if ( is_admin() ) {	
			// Build options and settings pages.
			add_action( 'admin_menu', array( &$this, 'add_admin' ) );
			add_action( 'admin_init', array( &$this, 'save' ) );
			add_action( 'admin_init', array( &$this, 'additional_plugin_setup' ) );
			
			// Register AJAX functions
			$actions = array(
				// Form Builder
				'sort_field',
				'create_field',
				'delete_field',
				'duplicate_field',
				'bulk_add',
				'conditional_fields',
				'conditional_fields_options',
				'conditional_fields_save',
				'paypal_price',
				'form_settings',
				
				// All Forms list
				'form_order',
				'form_order_type',
				
				// Analytics
				'graphs',
				
				// Media button
				'media_button',
			);
			
			// Add all AJAX functions			
			foreach( $actions as $name ) {
				add_action( "wp_ajax_visual_form_builder_$name", array( &$this, "ajax_$name" ) );	
			}
			
			// Adds additional media button to insert form shortcode
			add_action( 'media_buttons', array( &$this, 'add_media_button' ), 999 );

			// Load the includes files
			add_action( 'plugins_loaded', array( &$this, 'includes' ) );
			add_action( 'load-visual-form-builder-pro_page_vfb-entries', array( &$this, 'include_entries' ) );
			add_action( 'load-visual-form-builder-pro_page_vfb-import', array( &$this, 'include_import_export' ) );
			add_action( 'load-visual-form-builder-pro_page_vfb-export', array( &$this, 'include_import_export' ) );

			// Adds a Screen Options tab to the Entries screen
			add_filter( 'set-screen-option', array( &$this, 'save_screen_options' ), 10, 3 );
			add_filter( 'load-toplevel_page_visual-form-builder-pro', array( &$this, 'screen_options' ) );
			add_filter( 'load-visual-form-builder-pro_page_vfb-entries', array( &$this, 'screen_options' ) );
			
			// Add an Advanced Properties section to the Screen Options tab
			add_filter( 'manage_toplevel_page_visual-form-builder-pro_columns', array( &$this, 'screen_advanced_options' ) );

			// Add meta boxes to the form builder admin page
			add_action( 'load-toplevel_page_visual-form-builder-pro', array( &$this, 'add_meta_boxes' ) );
			
			// Adds a Settings link to the Plugins page
			add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );
			
			// Check the db version and run SQL install, if needed
			add_action( 'plugins_loaded', array( &$this, 'update_db_check' ) );
			
			// All plugin page load hooks
			$current_pages = array(
				'toplevel_page_visual-form-builder-pro',
				'visual-form-builder-pro_page_vfb-add-new',
				'visual-form-builder-pro_page_vfb-entries',
				'visual-form-builder-pro_page_vfb-email-design',
				'visual-form-builder-pro_page_vfb-reports',
				'visual-form-builder-pro_page_vfb-export',
				'visual-form-builder-pro_page_vfb-import'
			);
			
			foreach ( $current_pages as $page ) {
				// Load the jQuery and CSS we need if we're on our plugin page
				add_action( "load-$page", array( &$this, 'admin_scripts' ) );
				
				// Load the Help tab on all pages
				add_action( "load-$page", array( &$this, 'help' ) );
			}
			
			// Display plugin details screen for updating
			add_filter( 'plugins_api', array( &$this, 'api_information' ), 10, 3 );

			// Hook into the plugin update check
			add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'api_check' ) );
			
			// For testing only
			//add_action( 'init', array( &$this, 'delete_transient' ) );
			
			add_action( 'admin_notices', array( &$this, 'admin_notices' ) );
		}
		
		add_shortcode( 'vfb', array( &$this, 'form_code' ) );
		add_action( 'init', array( &$this, 'email' ), 10 );
		add_action( 'init', array( &$this, 'confirmation' ), 12 );
		add_action( 'admin_bar_menu', array( &$this, 'admin_toolbar_menu' ), 999 );
		
		// Add CSS to the front-end
		add_action( 'wp_enqueue_scripts', array( &$this, 'css' ) );
		
		// Load i18n
		add_action( 'plugins_loaded', array( &$this, 'languages' ) );
		
		add_action( 'wp_ajax_visual_form_builder_autocomplete', array( &$this, 'ajax_autocomplete' ) );
		add_action( 'wp_ajax_nopriv_visual_form_builder_autocomplete', array( &$this, 'ajax_autocomplete' ) );
		add_action( 'wp_ajax_visual_form_builder_check_username', array( &$this, 'ajax_check_username' ) );
		add_action( 'wp_ajax_nopriv_visual_form_builder_check_username', array( &$this, 'ajax_check_username' ) );
	}
	
	/**
	 * Allow for additional plugin code to be run during admin_init
	 * which is not available during the plugin __construct()
	 * 
	 * @since 2.1
	 */
	public function additional_plugin_setup() {
		// If first time editing, disable advanced items by default.
		if( false === get_user_option( 'managetoplevel_page_visual-form-builder-procolumnshidden' ) ) {
			$user = wp_get_current_user();
			update_user_option( $user->ID, 'managetoplevel_page_visual-form-builder-procolumnshidden', array( 0 => 'merge-tag' ), true );
		}
	}
	
	/**
	 * Load localization file
	 * 
	 * @since 2.1.2
	 */
	public function languages() {
		load_plugin_textdomain( 'visual-form-builder-pro', false , 'visual-form-builder-pro/languages' );
	}
	
	/**
	 * Delete transients on page load
	 * 
	 * FOR TESTING PURPOSES ONLY
	 *
	 * @since 1.0
	 */
	public function delete_transient() {
		delete_site_transient( 'update_plugins' );
	}

	/**
	 * Check the plugin versions to see if there's a new one
	 * 
	 * @since 1.0
	 */
	public function api_check( $transient ) {
		
		// If no checked transiest, just return its value without hacking it
		if ( empty( $transient->checked ) )
			return $transient;

		// Append checked transient information
		$plugin_slug = plugin_basename( __FILE__ );
		
		// POST data to send to your API
		$args = array(
			'action' 		=> 'update-check',
			'plugin_name' 	=> $plugin_slug,
			'version' 		=> $transient->checked[ $plugin_slug ],
		);
		
		// Send request checking for an update
		$response = $this->api_request( $args );
		
		// If response is false, don't alter the transient
		if ( false !== $response )
			$transient->response[ $plugin_slug ] = $response;
		
		return $transient;
	}
	
	/**
	 * Send a request to the alternative API, return an object
	 * 
	 * @since 1.0
	 */
	public function api_request( $args ) {
	
		// Send request
		$request = wp_remote_post( $this->api_url, array( 'body' => $args ) );
		
		// If request fails, stop
		if ( is_wp_error( $request ) ||	wp_remote_retrieve_response_code( $request ) != 200	)
			return false;
		
		// Retrieve and set response
		$response = maybe_unserialize( wp_remote_retrieve_body( $request ) );
		
		// Read server response, which should be an object
		if ( is_object( $response ) )
			return $response;
		else
			return false;
	}
	
	/**
	 * Return the plugin details for the plugin update screen
	 * 
	 * @since 1.0
	 */
	public function api_information( $false, $action, $args ) {
		
		$plugin_slug = plugin_basename( __FILE__ );
		
		// Check if requesting info
		if ( !isset( $args->slug ) )
			return $false;
			
		// Check if this plugins API is about this plugin
		if ( isset( $args->slug ) && $args->slug != $plugin_slug )
			return $false;
				
		// POST data to send to your API
		$args = array(
			'action' 		=> 'plugin_information',
			'plugin_name' 	=> $plugin_slug,
		);
		
		// Send request for detailed information
		$response = $this->api_request( $args );
		
		// Send request checking for information
		$request = wp_remote_post( $this->api_url, array( 'body' => $args ) );
		
		return $response;
	}
	
	/**
	 * Adds extra include files
	 * 
	 * @since 1.0
	 */
	public function includes(){
		// Load the Email Designer class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-email-designer.php' );
		
		// Load the Analytics class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-analytics.php' );
	}
	
	/**
	 * Include the Entries files later because current_screen isn't available yet
	 * 
	 * @since 1.4
	 */
	public function include_entries(){
		global $entries_list, $entries_detail;
		
		// Load the Entries List class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-entries-list.php' );
		$entries_list = new VisualFormBuilder_Pro_Entries_List();
		
		// Load the Entries Details class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-entries-detail.php' );
		$entries_detail = new VisualFormBuilder_Pro_Entries_Detail();		
	}
	
	/**
	 * Include the Import/Export files later because current_screen isn't available yet
	 * 
	 * @since 1.4
	 */
	public function include_import_export(){
		global $export, $import;
				
		// Load the Export class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-export.php' );
		$export = new VisualFormBuilder_Pro_Export();
		
		// Load the Import class
		require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-import.php' );
		$import = new VisualFormBuilder_Pro_Import();
	}

	/**
	 * Add Settings link to Plugins page
	 * 
	 * @since 1.8 
	 * @return $links array Links to add to plugin name
	 */
	public function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( __FILE__ ) )
			$links[] = '<a href="admin.php?page=visual-form-builder-pro">' . __( 'Settings' , 'visual-form-builder-pro') . '</a>';
	
		return $links;
	}
		
	/**
	 * Adds the media button image
	 * 
	 * @since 1.4
	 */
	public function add_media_button(){
		if ( current_user_can( 'vfb_view_entries' ) )
			echo '<a href="' . add_query_arg( array( 'action' => 'visual_form_builder_media_button', 'width' => '450' ), admin_url( 'admin-ajax.php' ) ) . '" class="thickbox" title="Add Visual Form Builder form"><img width="18" height="18" src="' . plugins_url( 'visual-form-builder-pro/images/vfb_icon.png' ) . '" alt="Add Visual Form Builder form" /></a>';
	}
	
	/**
	 * Register contextual help. This is for the Help tab dropdown
	 * 
	 * @since 1.0
	 */
	public function help(){
		$screen = get_current_screen();

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-general-info',
			'title' => 'General Info',
			'content' => '<ul>
						<li><a href="http://vfb.matthewmuro.com/documentation/installing" target="_blank">Installing the Plugin</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/staying-updated" target="_blank">Staying Updated</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/glossary" target="_blank">Glossary</a></li>
						<li><a href="http://vfb.matthewmuro.com/forum" target="_blank">Support Forums</a></li>
					</ul>'
		) );

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-forms',
			'title' => 'Forms',
			'content' => '<ul>
						<li><a href="http://vfb.matthewmuro.com/documentation/forms-interface" target="_blank">Interface Overview</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/forms-creating" target="_blank">Creating a New Form</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/forms-sorting" target="_blank">Sorting Your Forms</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/forms-building" target="_blank">Building Your Forms</a></li>
					</ul>'
		) );

		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-entries',
			'title' => 'Entries',
			'content' => '<ul>
						<li><a href="http://vfb.matthewmuro.com/documentation/entries-interface" target="_blank">Interface Overview</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/entries-managing" target="_blank">Managing Your Entries</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/entries-searching-filtering" target="_blank">Searching and Filtering Your Entries</a></li>
					</ul>'
		) );
		
		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-email-analytics',
			'title' => 'Email Design &amp; Analytics',
			'content' => '<ul>
						<li><a href="http://vfb.matthewmuro.com/documentation/email-design" target="_blank">Import Interface Overview</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/analytics" target="_blank">Export Interface Overview</a></li>
					</ul>'
		) );
		
		$screen->add_help_tab( array(
			'id' => 'vfb-help-tab-advanced',
			'title' => 'Advanced Topics',
			'content' => '<ul>
						<li><a href="http://vfb.matthewmuro.com/documentation/conditional-logic" target="_blank">Conditional Logic</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/templating" target="_blank">Templating</a></li>
						<li><a href="http://vfb.matthewmuro.com/documentation/custom-capabilities" target="_blank">Custom Capabilities</a></li>
						<li><a href="http://vfb.matthewmuro.com/hooks" target="_blank">Filters and Actions</a></li>
					</ul>'
		) );

	}
	
	/**
	 * Allow for additional plugin code to be run during admin_init
	 * which is not available during the plugin __construct()
	 * 
	 * @since 2.1
	 */
	public function screen_advanced_options() {
		return array(
			'_title'	=> __( 'Show advanced properties', 'visual-form-builder-pro' ),
			'cb'		=> '<input type="checkbox" />',
			'merge-tag'	=> __( 'Merge Tag' ),
		);
	}	
	/**
	 * Adds the Screen Options tab to the Entries screen
	 * 
	 * @since 1.0
	 */
	public function screen_options(){
		$screen = get_current_screen();
		
		switch( $screen->id ) {
			case 'visual-form-builder-pro_page_vfb-entries' :
				add_screen_option( 'per_page', array(
					'label'		=> __( 'Entries per page', 'visual-form-builder-pro' ),
					'default'	=> 20,
					'option'	=> 'vfb_entries_per_page'
				) );
			break;
			
			case 'toplevel_page_visual-form-builder-pro' :
				if ( !isset( $_REQUEST['form'] ) )
					break;
				
				add_screen_option( 'layout_columns', array(
					'max'		=> 3,
					'default'	=> 2
				) );

			break;
		}		
	}
	
	/**
	 * Saves the Screen Options
	 * 
	 * @since 1.0
	 */
	public function save_screen_options( $status, $option, $value ){
		
		if ( $option == 'vfb_entries_per_page' )
				return $value;
	}
	
	/**
	 * Add meta boxes to form builder screen
	 * 
	 * @since 1.8
	 */
	public function add_meta_boxes() {
		global $current_screen;
		
		if ( $current_screen->id == 'toplevel_page_visual-form-builder-pro' && isset( $_REQUEST['form'] ) ) {
			add_meta_box( 'vfb_form_switcher', __( 'Quick Switch', 'visual-form-builder-pro' ), array( &$this, 'meta_box_switch_form' ), 'toplevel_page_visual-form-builder-pro', 'side', 'high' );
			add_meta_box( 'vfb_form_items_meta_box', __( 'Form Items', 'visual-form-builder-pro' ), array( &$this, 'meta_box_form_items' ), 'toplevel_page_visual-form-builder-pro', 'side', 'high' );
			add_meta_box( 'vfb_form_media_button_tip', __( 'Display Forms', 'visual-form-builder-pro' ), array( &$this, 'meta_box_display_forms' ), 'toplevel_page_visual-form-builder-pro', 'side', 'low' );
		}
	}
	
	/**
	 * Output for form Quick Switch meta box
	 * 
	 * @since 1.8
	 */
	public function meta_box_switch_form() {
		global $wpdb;
		
		// Query to get all forms
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$where = apply_filters( 'vfb_pre_get_forms_switcher', '' );
		$forms = $wpdb->get_results( "SELECT * FROM $this->form_table_name WHERE 1=1 $where ORDER BY $order" );
		
		$form_nav_selected_id = ( isset( $_REQUEST['form'] ) ) ? $_REQUEST['form'] : $forms[0]->form_id;
		?>
		<select id="switcher_form">
		<?php
			foreach ( $forms as $form ) {
				echo '<option value="' . $form->form_id . '"' . selected( $form->form_id, $form_nav_selected_id, 0 ) . ' id="' . $form->form_key . '">' . stripslashes( $form->form_title ) . '</option>';
			}
		?>
		</select>
		<?php
	}
	
	/**
	 * Output for Form Items meta box
	 * 
	 * @since 1.8
	 */
	public function meta_box_form_items() {
		$vfb_post = '';
		// Run Create Post add-on
		if ( class_exists( 'VFB_Pro_Create_Post' ) )
			$vfb_post = new VFB_Pro_Create_Post();
	?>
		<div class="taxonomydiv">
			<p><strong><?php _e( 'Click or Drag' , 'visual-form-builder-pro'); ?></strong> <?php _e( 'to Add a Field' , 'visual-form-builder-pro'); ?> <img id="add-to-form" alt="" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting spinner" /></p>
			<ul class="posttype-tabs add-menu-item-tabs" id="vfb-field-tabs">
				<li class="tabs"><a href="#standard-fields" class="nav-tab-link vfb-field-types"><?php _e( 'Standard' , 'visual-form-builder-pro'); ?></a></li>
				<li><a href="#advanced-fields" class="nav-tab-link vfb-field-types"><?php _e( 'Advanced' , 'visual-form-builder-pro'); ?></a></li>
				<?php
					if ( class_exists( 'VFB_Pro_Create_Post' ) && method_exists( $vfb_post, 'form_item_tab' ) )
						$vfb_post->form_item_tab();
				?>
			</ul>
			<div id="standard-fields" class="tabs-panel tabs-panel-active">
				<ul class="vfb-fields-col-1">
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-fieldset">Fieldset</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-text"><b></b>Text</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-checkbox"><b></b>Checkbox</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-select"><b></b>Select</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-datepicker"><b></b>Date</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-url"><b></b>URL</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-digits"><b></b>Number</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-phone"><b></b>Phone</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-file"><b></b>File Upload</a></li>
				</ul>
				<ul class="vfb-fields-col-2">
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-section"><b></b>Section</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-textarea"><b></b>Textarea</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-radio"><b></b>Radio</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-address"><b></b>Address</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-email"><b></b>Email</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-currency"><b></b>Currency</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-time"><b></b>Time</a></li>
					
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-html"><b></b>HTML</a></li>
					
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-instructions"><b></b>Instructions</a></li>
				</ul>
				<div class="clear"></div>
			</div> <!-- #standard-fields -->
			<div id="advanced-fields"class="tabs-panel tabs-panel-inactive">
				<ul class="vfb-fields-col-1">
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-username"><b></b>Username</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-hidden"><b></b>Hidden</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-autocomplete"><b></b>Autocomplete</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-min"><b></b>Min</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-range"><b></b>Range</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-name"><b></b>Name</a></li>
				</ul>
				<ul class="vfb-fields-col-2">
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-password"><b></b>Password</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-color"><b></b>Color Picker</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-ip"><b></b>IP Address</a></li>	
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-max"><b></b>Max</a></li>
					<li><a href="#" class="vfb-draggable-form-items" id="form-element-pagebreak"><b></b>Page Break</a></li>
				</ul>
				<div class="clear"></div>
			</div> <!-- #advanced-fields -->
			<?php
				if ( class_exists( 'VFB_Pro_Create_Post' ) && method_exists( $vfb_post, 'form_items' ) )
					$vfb_post->form_items();
			?>
		</div> <!-- .taxonomydiv -->
		<div class="clear"></div>
	<?php
	}
	
	/**
	 * Output for the Display Forms meta box
	 * 
	 * @since 1.8
	 */
	public function meta_box_display_forms() {
	?>
		<p><?php _e( 'Add forms to your Posts or Pages by locating the icon shown below in the area above your post/page editor.', 'visual-form-builder-pro' ); ?><br>
    		<img src="<?php echo plugins_url( 'visual-form-builder-pro/images/media-button-help.png' ); ?>">
    	</p>
	<?php
	}
	
	/**
	 * Check database version and run SQL install, if needed
	 * 
	 * @since 2.1
	 */
	public function update_db_check() {
		// Add a database version to help with upgrades and run SQL install
		if ( !get_option( 'vfb_pro_db_version' ) ) {
			update_option( 'vfb_pro_db_version', $this->vfb_db_version );
			$this->install_db();
		}
		
		// If database version doesn't match, update and maybe run SQL install
		if ( version_compare( get_option( 'vfb_pro_db_version' ), $this->vfb_db_version, '<' ) ) {
			update_option( 'vfb_pro_db_version', $this->vfb_db_version );
			$this->install_db();
		}
	}
		
	/**
	 * Install database tables
	 * 
	 * @since 1.0 
	 */
	static function install_db() {
		global $wpdb;
		
		$field_table_name 	= $wpdb->prefix . 'vfb_pro_fields';
		$form_table_name 	= $wpdb->prefix . 'vfb_pro_forms';
		$entries_table_name = $wpdb->prefix . 'vfb_pro_entries';
		
		// Explicitly set the character set and collation when creating the tables
		$charset = ( defined( 'DB_CHARSET' && '' !== DB_CHARSET ) ) ? DB_CHARSET : 'utf8';
		$collate = ( defined( 'DB_COLLATE' && '' !== DB_COLLATE ) ) ? DB_COLLATE : 'utf8_general_ci';
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); 
				
		$field_sql = "CREATE TABLE $field_table_name (
				field_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_id BIGINT(20) NOT NULL,
				field_key VARCHAR(255) NOT NULL,
				field_type VARCHAR(25) NOT NULL,
				field_options TEXT,
				field_options_other VARCHAR(255),
				field_description TEXT,
				field_name TEXT NOT NULL,
				field_sequence BIGINT(20) DEFAULT '0',
				field_parent BIGINT(20) DEFAULT '0',
				field_validation VARCHAR(25),
				field_required VARCHAR(25),
				field_size VARCHAR(25) DEFAULT 'medium',
				field_css VARCHAR(255),
				field_layout VARCHAR(255),
				field_default TEXT,
				field_rule_setting TINYINT(1),
				field_rule LONGTEXT,
				PRIMARY KEY  (field_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";

		$form_sql = "CREATE TABLE $form_table_name (
				form_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_key TINYTEXT NOT NULL,
				form_title TEXT NOT NULL,
				form_email_subject TEXT,
				form_email_to TEXT,
				form_email_from VARCHAR(255),
				form_email_from_name VARCHAR(255),
				form_email_from_override VARCHAR(255),
				form_email_from_name_override VARCHAR(255),
				form_success_type VARCHAR(25) DEFAULT 'text',
				form_success_message TEXT,
				form_notification_setting VARCHAR(25),
				form_notification_email_name VARCHAR(255),
				form_notification_email_from VARCHAR(255),
				form_notification_email VARCHAR(25),
				form_notification_subject VARCHAR(255),
				form_notification_message TEXT,
				form_notification_entry VARCHAR(25),
				form_email_design TEXT,
				form_paypal_setting VARCHAR(25),
				form_paypal_email VARCHAR(255),
				form_paypal_currency VARCHAR(25) DEFAULT 'USD',
				form_paypal_shipping VARCHAR(255),
				form_paypal_tax VARCHAR(255),
				form_paypal_field_price TEXT,
				form_paypal_item_name VARCHAR(255),
				form_label_alignment VARCHAR(25),
				form_verification TINYINT(1) DEFAULT '1',
				form_entries_allowed VARCHAR(25),
				form_entries_schedule VARCHAR(100),
				PRIMARY KEY  (form_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";
		
		$entries_sql = "CREATE TABLE $entries_table_name (
				entries_id BIGINT(20) NOT NULL AUTO_INCREMENT,
				form_id BIGINT(20) NOT NULL,
				data LONGTEXT NOT NULL,
				subject TEXT,
				sender_name VARCHAR(255),
				sender_email VARCHAR(255),
				emails_to TEXT,
				date_submitted DATETIME,
				ip_address VARCHAR(25),
				notes TEXT,
				akismet TEXT,
				entry_approved VARCHAR(20) DEFAULT '1',
				PRIMARY KEY  (entries_id)
			) DEFAULT CHARACTER SET $charset COLLATE $collate;";
		
		// Create or Update database tables
		dbDelta( $field_sql );
		dbDelta( $form_sql );
		dbDelta( $entries_sql );
		
		$role = get_role( 'administrator' );
		
		// If the capabilities have not been added, do so here
		if ( !empty( $role ) && !$role->has_cap( 'vfb_import_forms' ) ) {
			// Setup the capabilities for each role that gets access
			$caps = array(
				'administrator' => array(
					'vfb_create_forms',
					'vfb_edit_forms',
					'vfb_copy_forms',
					'vfb_delete_forms',
					'vfb_import_forms',
					'vfb_export_forms',
					'vfb_view_entries',
					'vfb_edit_entries',
					'vfb_delete_entries',
					'vfb_edit_email_design',
					'vfb_view_analytics'
				),
				'editor' => array(
					'vfb_view_entries',
					'vfb_edit_entries',
					'vfb_delete_entries',
					'vfb_view_analytics'
				)
			);
			
			// Assign the appropriate caps to the administrator role
			if ( !empty( $role ) ) {
				foreach ( $caps['administrator'] as $cap ) {
					$role->add_cap( $cap );
				}
			}
			
			// Assign the appropriate caps to the editor role
			$role = get_role( 'editor' );
			if ( !empty( $role ) ) {
				foreach ( $caps['editor'] as $cap ) {
					$role->add_cap( $cap );
				}
			}
		}

	}
	
	/**
	 * Queue plugin scripts and CSS for sorting form fields
	 * 
	 * @since 1.0 
	 */
	public function admin_scripts() {
		wp_enqueue_style( 'jquery-ui-datepicker', plugins_url( '/css/smoothness/jquery-ui-1.9.2.min.css', __FILE__ ) );
		wp_enqueue_style( 'visual-form-builder-style', plugins_url( "/css/visual-form-builder-admin$this->load_dev_files.css", __FILE__ ), array(), '20121201' );
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_style( 'thickbox' );
		
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'farbtastic' );
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'jquery-form-validation', plugins_url( '/js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), '1.9.0', true );
		wp_enqueue_script( 'vfb-admin', plugins_url( "/js/vfb-admin$this->load_dev_files.js", __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '', true );
		wp_enqueue_script( 'nested-sortable', plugins_url( '/js/jquery.ui.nestedSortable.js', __FILE__ ) , array( 'jquery', 'jquery-ui-sortable' ), '', true );
		wp_enqueue_script( 'jquery-ui-timepicker', plugins_url( '/js/jquery.ui.timepicker.js', __FILE__ ) , array( 'jquery', 'jquery-ui-datepicker' ), '', true );
		
		// Only load Google Charts if viewing Analytics to prevent errors
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'vfb-reports' ) ) ) {
			wp_enqueue_script( 'raphael-js', plugins_url( '/js/raphael-2.1.0.min.js', __FILE__ ), array(), '', false );
			wp_enqueue_script( 'morris-js', plugins_url( '/js/morris-0.3.3.min.js', __FILE__ ), array( 'raphael-js' ), '', false );
			wp_enqueue_script( 'vfb-charts', plugins_url( "/js/vfb-charts$this->load_dev_files.js", __FILE__ ), array( 'morris-js' ), '', false );
		}
	}
	
	/**
	 * Queue form validation scripts
	 * 
	 * @since 1.0 
	 */
	public function scripts() {
		// Make sure scripts are only added once via shortcode
		$this->add_scripts = true;
		
		wp_enqueue_script( 'jquery-form-validation', plugins_url( '/js/jquery.validate.min.js', __FILE__ ), array( 'jquery' ), '1.9.0', true );
		wp_enqueue_script( 'vfb-jquery-ui', plugins_url( '/js/jquery-ui.min.js', __FILE__ ), array( 'jquery' ), '1.9.2', true );
		wp_enqueue_script( 'visual-form-builder-validation', plugins_url( "/js/vfb-validation$this->load_dev_files.js", __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '', true );
		wp_enqueue_script( 'visual-form-builder-metadata', plugins_url( '/js/jquery.metadata.js', __FILE__ ) , array( 'jquery', 'jquery-form-validation' ), '', true );
		wp_enqueue_script( 'farbtastic-js', admin_url( 'js/farbtastic.js' ) );

		wp_localize_script( 'visual-form-builder-validation', 'VfbAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
	
	/**
	 * Add form CSS to wp_head
	 * 
	 * @since 1.0 
	 */
	public function css() {
		wp_register_style( 'vfb-jqueryui-css', apply_filters( 'vfb-date-picker-css', plugins_url( '/css/smoothness/jquery-ui-1.9.2.min.css', __FILE__ ) ) );
		wp_register_style( 'visual-form-builder-css', apply_filters( 'visual-form-builder-css', plugins_url( '/css/visual-form-builder.css', __FILE__ ) ) );
		wp_register_script( 'visual-form-builder-quicktags', plugins_url( '/js/js_quicktags.js', __FILE__ ) );
		
		wp_enqueue_style( 'visual-form-builder-css' );
		wp_enqueue_style( 'vfb-jqueryui-css' );
		wp_enqueue_style( 'farbtastic' );
		wp_enqueue_script( 'visual-form-builder-quicktags' );
	}
		
	/**
	 * Actions to save, update, and delete forms/form fields
	 * 
	 * 
	 * @since 1.0
	 */
	public function save() {
		global $wpdb;
				
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'visual-form-builder-pro', 'vfb-add-new', 'vfb-entries', 'vfb-email-design', 'vfb-reports' ) ) && isset( $_REQUEST['action'] ) ) {	
			switch ( $_REQUEST['action'] ) {
				case 'create_form' :
					
					$form_key 		= sanitize_title( $_REQUEST['form_title'] );
					$form_title 	= esc_html( $_REQUEST['form_title'] );
					$form_from_name = esc_html( $_REQUEST['form_email_from_name'] );
					$form_subject 	= esc_html( $_REQUEST['form_email_subject'] );
					$form_from 		= esc_html( $_REQUEST['form_email_from'] );
					$form_to 		= serialize( array_map( 'esc_html', $_REQUEST['form_email_to'] ) );
					
					check_admin_referer( 'create_form' );
					
					$email_design = array(
						'format' 				=> 'html',
						'link_love' 			=> 'yes',
						'footer_text' 			=> '',
						'background_color' 		=> '#eeeeee',
						'header_image' 			=> '',
						'header_color' 			=> '#810202',
						'header_text_color' 	=> '#ffffff',
						'fieldset_color' 		=> '#680606',
						'section_color' 		=> '#5C6266',
						'section_text_color' 	=> '#ffffff',
						'text_color' 			=> '#333333',
						'link_color' 			=> '#1b8be0',
						'row_color' 			=> '#ffffff',
						'row_alt_color' 		=> '#eeeeee',
						'border_color' 			=> '#cccccc',
						'footer_color' 			=> '#333333',
						'footer_text_color' 	=> '#ffffff',
						'font_family' 			=> 'Arial',
						'header_font_size' 		=> 32,
						'fieldset_font_size' 	=> 20,
						'section_font_size' 	=> 15,
						'text_font_size' 		=> 13,
						'footer_font_size' 		=> 11
					);
					
					$newdata = array(
						'form_key' 				=> $form_key,
						'form_title' 			=> $form_title,
						'form_email_from_name'	=> $form_from_name,
						'form_email_subject'	=> $form_subject,
						'form_email_from'		=> $form_from,
						'form_email_to'			=> $form_to,
						'form_email_design' 	=> serialize( $email_design )
					);
										
					// Create the form
					$wpdb->insert( $this->form_table_name, $newdata );
					
					// Get form ID to add our first field
					$new_form_selected = $wpdb->insert_id;
					
					// Setup the initial fieldset
					$initial_fieldset = array(
						'form_id' 			=> $wpdb->insert_id,
						'field_key' 		=> 'fieldset',
						'field_type' 		=> 'fieldset',
						'field_name' 		=> 'Fieldset',
						'field_sequence' 	=> 0
					);
					
					// Add the first fieldset to get things started 
					$wpdb->insert( $this->field_table_name, $initial_fieldset );
					
					/*$verification_fieldset = array(
						'form_id' 			=> $new_form_selected,
						'field_key' 		=> 'verification',
						'field_type' 		=> 'verification',
						'field_name' 		=> 'Verification',
						'field_description' => '(This is for preventing spam)',
						'field_sequence' 	=> 1
					);
					
					// Insert the submit field 
					$wpdb->insert( $this->field_table_name, $verification_fieldset );
					
					$verify_fieldset_parent_id = $wpdb->insert_id;
					
					$secret = array(
						'form_id' 			=> $new_form_selected,
						'field_key' 		=> 'secret',
						'field_type' 		=> 'secret',
						'field_name' 		=> 'Please enter any two digits with no spaces (Example: 12)',
						'field_size' 		=> 'medium',
						'field_required' 	=> 'yes',
						'field_parent' 		=> $verify_fieldset_parent_id,
						'field_sequence' 	=> 2
					);
					
					// Insert the submit field 
					$wpdb->insert( $this->field_table_name, $secret );*/
					
					// Make the submit last in the sequence
					$submit = array(
						'form_id' 			=> $new_form_selected,
						'field_key' 		=> 'submit',
						'field_type' 		=> 'submit',
						'field_name' 		=> 'Submit',
						'field_parent' 		=> $verify_fieldset_parent_id,
						'field_sequence' 	=> 3
					);
					
					// Insert the submit field 
					$wpdb->insert( $this->field_table_name, $submit );
					
					// Redirect to keep the URL clean (use AJAX in the future?)
					wp_redirect( 'admin.php?page=visual-form-builder-pro&form=' . $new_form_selected );
					exit();
					
				break;
				
				case 'update_form' :

					$form_id 						= absint( $_REQUEST['form_id'] );
					$form_key 						= sanitize_title( $_REQUEST['form_title'], $form_id );
					$form_title 					= esc_html( $_REQUEST['form_title'] );
					$form_subject 					= esc_html( $_REQUEST['form_email_subject'] );
					$form_to 						= serialize( array_map( 'sanitize_email', $_REQUEST['form_email_to'] ) );
					$form_from 						= esc_html( sanitize_email( $_REQUEST['form_email_from'] ) );
					$form_from_name 				= esc_html( $_REQUEST['form_email_from_name'] );
					$form_from_override 			= esc_html( $_REQUEST['form_email_from_override'] );
					$form_from_name_override 		= esc_html( $_REQUEST['form_email_from_name_override'] );
					$form_success_type 				= esc_html( $_REQUEST['form_success_type'] );
					$form_notification_setting 		= isset( $_REQUEST['form_notification_setting'] ) ? esc_html( $_REQUEST['form_notification_setting'] ) : '';
					$form_notification_email_name 	= isset( $_REQUEST['form_notification_email_name'] ) ? esc_html( $_REQUEST['form_notification_email_name'] ) : '';
					$form_notification_email_from 	= isset( $_REQUEST['form_notification_email_from'] ) ? sanitize_email( $_REQUEST['form_notification_email_from'] ) : '';
					$form_notification_email 		= isset( $_REQUEST['form_notification_email'] ) ? esc_html( $_REQUEST['form_notification_email'] ) : '';
					$form_notification_subject 		= isset( $_REQUEST['form_notification_subject'] ) ? esc_html( $_REQUEST['form_notification_subject'] ) : '';
					$form_notification_message 		= isset( $_REQUEST['form_notification_message'] ) ? wp_richedit_pre( $_REQUEST['form_notification_message'] ) : '';
					$form_notification_entry 		= isset( $_REQUEST['form_notification_entry'] ) ? esc_html( $_REQUEST['form_notification_entry'] ) : '';
					$form_paypal_setting 			= isset( $_REQUEST['form_paypal_setting'] ) ? esc_html( $_REQUEST['form_paypal_setting'] ) : '';
					$form_paypal_email 				= isset( $_REQUEST['form_paypal_email'] ) ? sanitize_email( $_REQUEST['form_paypal_email'] ) : '';
					$form_paypal_currency 			= isset( $_REQUEST['form_paypal_currency'] ) ? esc_html( $_REQUEST['form_paypal_currency'] ) : '';
					$form_paypal_shipping 			= isset( $_REQUEST['form_paypal_shipping'] ) ? esc_html( $_REQUEST['form_paypal_shipping'] ) : '';
					$form_paypal_tax 				= isset( $_REQUEST['form_paypal_tax'] ) ? esc_html( $_REQUEST['form_paypal_tax'] ) : '';
					$form_paypal_field_price 		= isset( $_REQUEST['form_paypal_field_price'] ) ? serialize( $_REQUEST['form_paypal_field_price'] ) : '';
					$form_paypal_item_name 			= isset( $_REQUEST['form_paypal_item_name'] ) ? esc_html( $_REQUEST['form_paypal_item_name'] ) : '';
					$form_label_alignment 			= esc_html( $_REQUEST['form_label_alignment'] );
					$form_verification 				= esc_html( $_REQUEST['form_verification'] );
					$form_entries_allowed			= isset( $_REQUEST['form_entries_allowed'] ) ? sanitize_text_field( $_REQUEST['form_entries_allowed'] ) : '';
					$form_entries_schedule			= isset( $_REQUEST['form_entries_schedule'] ) ? serialize( array_map( 'sanitize_text_field', $_REQUEST['form_entries_schedule'] ) ) : '';
					
					// Add confirmation based on which type was selected
					switch ( $form_success_type ) {
						case 'text' :
							$form_success_message = wp_richedit_pre( $_REQUEST['form_success_message_text'] );
						break;
						case 'page' :
							$form_success_message = esc_html( $_REQUEST['form_success_message_page'] );
						break;
						case 'redirect' :
							$form_success_message = esc_html( $_REQUEST['form_success_message_redirect'] );
						break;
					}
					
					check_admin_referer( 'update_form-' . $form_id );
					
					$newdata = array(
						'form_key'						=> $form_key,
						'form_title' 					=> $form_title,
						'form_email_subject' 			=> $form_subject,
						'form_email_to' 				=> $form_to,
						'form_email_from' 				=> $form_from,
						'form_email_from_name' 			=> $form_from_name,
						'form_email_from_override' 		=> $form_from_override,
						'form_email_from_name_override' => $form_from_name_override,
						'form_success_type' 			=> $form_success_type,
						'form_success_message' 			=> $form_success_message,
						'form_notification_setting' 	=> $form_notification_setting,
						'form_notification_email_name' 	=> $form_notification_email_name,
						'form_notification_email_from' 	=> $form_notification_email_from,
						'form_notification_email' 		=> $form_notification_email,
						'form_notification_subject' 	=> $form_notification_subject,
						'form_notification_message' 	=> $form_notification_message,
						'form_notification_entry' 		=> $form_notification_entry,
						'form_paypal_setting' 			=> $form_paypal_setting,
						'form_paypal_email' 			=> $form_paypal_email,
						'form_paypal_currency' 			=> $form_paypal_currency,
						'form_paypal_shipping' 			=> $form_paypal_shipping,
						'form_paypal_tax' 				=> $form_paypal_tax,
						'form_paypal_field_price' 		=> $form_paypal_field_price,
						'form_paypal_item_name' 		=> $form_paypal_item_name,
						'form_label_alignment' 			=> $form_label_alignment,
						'form_verification' 			=> $form_verification,
						'form_entries_allowed' 			=> $form_entries_allowed,
						'form_entries_schedule' 		=> $form_entries_schedule
					);
					
					$where = array( 'form_id' => $form_id );
					
					// Update form details
					$wpdb->update( $this->form_table_name, $newdata, $where );
					
					// Initialize field sequence
					$field_sequence = 0;
					
					// Loop through each field and update all at once
					if ( !empty( $_REQUEST['field_id'] ) ) {
						foreach ( $_REQUEST['field_id'] as $id ) {
							$field_name 		= ( isset( $_REQUEST['field_name-' . $id] ) ) ? esc_html( $_REQUEST['field_name-' . $id] ) : '';
							$field_key 			= sanitize_title( $field_name, $id );
							$field_desc 		= ( isset( $_REQUEST['field_description-' . $id] ) ) ? esc_html( $_REQUEST['field_description-' . $id] ) : '';
							$field_options 		= ( isset( $_REQUEST['field_options-' . $id] ) ) ? serialize( array_map( 'esc_html', $_REQUEST['field_options-' . $id] ) ) : '';
							$field_options_other= ( isset( $_REQUEST['field_options_other-' . $id] ) ) ? serialize( array_map( 'esc_html', $_REQUEST['field_options_other-' . $id] ) ) : '';
							$field_validation 	= ( isset( $_REQUEST['field_validation-' . $id] ) ) ? esc_html( $_REQUEST['field_validation-' . $id] ) : '';
							$field_required 	= ( isset( $_REQUEST['field_required-' . $id] ) ) ? esc_html( $_REQUEST['field_required-' . $id] ) : '';
							$field_size 		= ( isset( $_REQUEST['field_size-' . $id] ) ) ? esc_html( $_REQUEST['field_size-' . $id] ) : '';
							$field_css 			= ( isset( $_REQUEST['field_css-' . $id] ) ) ? esc_html( $_REQUEST['field_css-' . $id] ) : '';
							$field_layout 		= ( isset( $_REQUEST['field_layout-' . $id] ) ) ? esc_html( $_REQUEST['field_layout-' . $id] ) : '';
							$field_default 		= ( isset( $_REQUEST['field_default-' . $id] ) ) ? esc_html( $_REQUEST['field_default-' . $id] ) : '';
							
							$field_data = array(
								'field_key' 		=> $field_key,
								'field_name' 		=> $field_name,
								'field_description' => $field_desc,
								'field_options'		=> $field_options,
								'field_options_other'=> $field_options_other,
								'field_validation' 	=> $field_validation,
								'field_required' 	=> $field_required,
								'field_size' 		=> $field_size,
								'field_css' 		=> $field_css,
								'field_layout' 		=> $field_layout,
								'field_sequence' 	=> $field_sequence,
								'field_default' 	=> $field_default
							);
							
							$where = array(
								'form_id' 	=> $_REQUEST['form_id'],
								'field_id' 	=> $id
							);
							
							// Update all fields
							$wpdb->update( $this->field_table_name, $field_data, $where );
							
							$field_sequence++;
						}
						
						// Check if a submit field type exists for backwards compatibility upgrades
						$is_verification = $wpdb->get_var( $wpdb->prepare( "SELECT field_id FROM $this->field_table_name WHERE field_type = 'verification' AND form_id = %d", $form_id ) );
						$is_secret = $wpdb->get_var( $wpdb->prepare( "SELECT field_id FROM $this->field_table_name WHERE field_type = 'secret' AND form_id = %d", $form_id ) );
						$is_submit = $wpdb->get_var( $wpdb->prepare( "SELECT field_id FROM $this->field_table_name WHERE field_type = 'submit' AND form_id = %d", $form_id ) );
						
						// Decrement sequence
						$field_sequence--;
						
						$verification_id = '';
						
						// If this form doesn't have a submit field, add one
						//if ( $is_verification == NULL ) {
						if ( 0 ) {
							// Adjust the sequence
							$verification_fieldset = array(
								'form_id' 			=> $form_id,
								'field_key' 		=> 'verification',
								'field_type' 		=> 'verification',
								'field_name' 		=> 'Verification',
								'field_sequence' 	=> $field_sequence
							);
							
							// Insert the submit field 
							$wpdb->insert( $this->field_table_name, $verification_fieldset );
							
							$verification_id = $wpdb->insert_id;
						}
						
						// If the verification field was inserted, use that ID as a parent otherwise set no parent
						$verify_fieldset_parent_id = ( $verification_id !== false ) ? $verification_id : 0;
						
						// If this form doesn't have a secret field, add one
						//if ( $is_secret == NULL ) {
						if ( 0 ) {	
							// Adjust the sequence
							$secret = array(
								'form_id' 			=> $form_id,
								'field_key' 		=> 'secret',
								'field_type' 		=> 'secret',
								'field_name' 		=> 'Please enter any two digits with no spaces (Example: 12)',
								'field_size' 		=> 'medium',
								'field_required' 	=> 'yes',
								'field_parent' 		=> $verify_fieldset_parent_id,
								'field_sequence' 	=> ++$field_sequence
							);
							
							// Insert the submit field 
							$wpdb->insert( $this->field_table_name, $secret );
						}
						
						// If this form doesn't have a submit field, add one
						if ( $is_submit == NULL ) {
							
							// Make the submit last in the sequence
							$submit = array(
								'form_id' 			=> $form_id,
								'field_key' 		=> 'submit',
								'field_type' 		=> 'submit',
								'field_name' 		=> 'Submit',
								'field_parent' 		=> $verify_fieldset_parent_id,
								'field_sequence' 	=> ++$field_sequence
							);
							
							// Insert the submit field 
							$wpdb->insert( $this->field_table_name, $submit );
						}
						else {
							// Only update the Submit's parent ID if the Verification field is new
							$data = ( $is_verification == NULL ) ? array( 'field_parent' => $verify_fieldset_parent_id, 'field_sequence' => ++$field_sequence ) : array( 'field_sequence' => $field_sequence	);
							
							$where = array(
								'form_id' 	=> $form_id,
								'field_id' 	=> $is_submit
							);
										
							// Update the submit field
							$wpdb->update( $this->field_table_name, $data, $where );
	
						}
					}
				
				break;
				
				case 'delete_form' :
					$id = absint( $_REQUEST['form'] );
					
					check_admin_referer( 'delete-form-' . $id );
					
					// Delete form and all fields
					$wpdb->query( $wpdb->prepare( "DELETE FROM $this->form_table_name WHERE form_id = %d", $id ) );
					$wpdb->query( $wpdb->prepare( "DELETE FROM $this->field_table_name WHERE form_id = %d", $id ) );
					
					// Redirect to keep the URL clean (use AJAX in the future?)
					wp_redirect( add_query_arg( 'action', 'deleted', 'admin.php?page=visual-form-builder-pro' ) );
					exit();
					
				break;
				
				case 'copy_form' :
					$id = absint( $_REQUEST['form'] );
					
					check_admin_referer( 'copy-form-' . $id );
					
					// Get all fields and data for the request form					
					$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d", $id ) );
					$forms = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d", $id ) );
					$override = $wpdb->get_var( $wpdb->prepare( "SELECT form_email_from_override, form_email_from_name_override, form_notification_email FROM $this->form_table_name WHERE form_id = %d", $id ) );
					$from_name = $wpdb->get_var( null, 1 );
					$notify = $wpdb->get_var( null, 2 );
					
					// Copy this form and force the initial title to denote a copy
					foreach ( $forms as $form ) {
						$data = array(
							'form_key' 						=> sanitize_title( $form->form_key . ' copy' ),
							'form_title' 					=> $form->form_title . ' Copy',
							'form_email_subject' 			=> $form->form_email_subject,
							'form_email_to' 				=> $form->form_email_to,
							'form_email_from' 				=> $form->form_email_from,
							'form_email_from_name' 			=> $form->form_email_from_name,
							'form_email_from_override' 		=> $form->form_email_from_override,
							'form_email_from_name_override' => $form->form_email_from_name_override,
							'form_success_type' 			=> $form->form_success_type,
							'form_success_message' 			=> $form->form_success_message,
							'form_notification_setting' 	=> $form->form_notification_setting,
							'form_notification_email_name' 	=> $form->form_notification_email_name,
							'form_notification_email_from' 	=> $form->form_notification_email_from,
							'form_notification_email' 		=> $form->form_notification_email,
							'form_notification_subject' 	=> $form->form_notification_subject,
							'form_notification_message' 	=> $form->form_notification_message,
							'form_notification_entry' 		=> $form->form_notification_entry,
							'form_email_design' 			=> $form->form_email_design,
							'form_paypal_setting' 			=> $form->form_paypal_setting,
							'form_paypal_email' 			=> $form->form_paypal_email,
							'form_paypal_currency' 			=> $form->form_paypal_currency,
							'form_paypal_shipping' 			=> $form->form_paypal_shipping,
							'form_paypal_tax' 				=> $form->form_paypal_tax,
							'form_paypal_field_price' 		=> $form->form_paypal_field_price,
							'form_paypal_item_name' 		=> $form->form_paypal_item_name,
							'form_label_alignment' 			=> $form->form_label_alignment,
							'form_verification' 			=> $form->form_verification,
							'form_entries_allowed' 			=> $form->form_entries_allowed,
							'form_entries_schedule'			=> $form->form_entries_schedule
						);
						
						$wpdb->insert( $this->form_table_name, $data );
					}
					
					// Get form ID to add our first field
					$new_form_selected = $wpdb->insert_id;
					
					// Copy each field and data
					foreach ( $fields as $field ) {
						
						$data = array(
							'form_id' 			=> $new_form_selected,
							'field_key' 		=> $field->field_key,
							'field_type' 		=> $field->field_type,
							'field_name' 		=> $field->field_name,
							'field_description' => $field->field_description,
							'field_options' 	=> $field->field_options,
							'field_options_other'=> $field->field_options_other,
							'field_sequence' 	=> $field->field_sequence,
							'field_validation' 	=> $field->field_validation,
							'field_required' 	=> $field->field_required,
							'field_size' 		=> $field->field_size,
							'field_css' 		=> $field->field_css,
							'field_layout' 		=> $field->field_layout,
							'field_parent' 		=> $field->field_parent,
							'field_default' 	=> $field->field_default,
							'field_rule_setting'=> $field->field_rule_setting,
							'field_rule' 		=> $field->field_rule
						);
						
						$wpdb->insert( $this->field_table_name, $data );
						
						// Save field IDs so we can update the field rules
						$old_ids[ $field->field_id ] = $wpdb->insert_id;
						
						// If a parent field, save the old ID and the new ID to update new parent ID
						if ( in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) )
							$parents[ $field->field_id ] = $wpdb->insert_id;
							
						if ( $override == $field->field_id )
							$wpdb->update( $this->form_table_name, array( 'form_email_from_override' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
						
						if ( $from_name == $field->field_id )
							$wpdb->update( $this->form_table_name, array( 'form_email_from_name_override' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
						
						if ( $notify == $field->field_id )
							$wpdb->update( $this->form_table_name, array( 'form_notification_email' => $wpdb->insert_id ), array( 'form_id' => $new_form_selected ) );
					}
					
					// Loop through our parents and update them to their new IDs
					foreach ( $parents as $k => $v ) {
						$wpdb->update( $this->field_table_name, array( 'field_parent' => $v ), array( 'form_id' => $new_form_selected, 'field_parent' => $k ) );	
					}
					
					// Loop through all of the IDs and update the rules if a match is found
					foreach ( $old_ids as $k => $v ) :
						// Get field key
						$field_key = $wpdb->get_var( $wpdb->prepare( "SELECT field_key FROM $this->field_table_name WHERE form_id = %d AND field_id = %d", $id, $k ) );
						
						// Setup search and replace for IDs
						$search = 's:' . strlen( $k ) .':"' . $k . '"';
						$replace = 's:' . strlen( $v ) .':"' . $v . '"';
						
						$wpdb->query( $wpdb->prepare( "UPDATE $this->field_table_name SET field_rule = REPLACE(field_rule, %s, %s) WHERE form_id = %d", $search, $replace, $new_form_selected ) );
						
						// Assemble field_id_attr
						$key = 'vfb-' . $field_key . '-';
						
						// Setup search and replace for field_id_attr
						$search = 's:' . strlen( $key . $k ) .':"' . $key . $k . '"';
						$replace = 's:' . strlen( $key . $v ) .':"' . $key . $v . '"';
						
						$wpdb->query( $wpdb->prepare( "UPDATE $this->field_table_name SET field_rule = REPLACE(field_rule, %s, %s) WHERE form_id = %d", $search, $replace, $new_form_selected ) );
					endforeach;
					
				break;
				
				case 'email_design' :
					$form_id = absint( $_REQUEST['form_id'] );
					
					$email = unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT form_email_design FROM $this->form_table_name WHERE form_id = %d", $form_id ) ) );
					
					$header_image = ( !empty( $email['header_image'] ) ) ? $email['header_image'] : '';
					
					if ( isset( $_FILES['header_image'] ) ) {						
						$value = $_FILES['header_image'];
						
						if ( $value['size'] > 0 ) {
							// Handle the upload using WP's wp_handle_upload function. Takes the posted file and an options array
							$uploaded_file = wp_handle_upload( $value, array( 'test_form' => false ) );
							
							@list( $width, $height, $type, $attr ) = getimagesize( $uploaded_file['file'] );
							
							if ( $width == 600 && $height == 137 )
								$header_image = ( isset( $uploaded_file['file'] ) ) ? $uploaded_file['url'] : '';
							elseif ( $width > 600 ) {
								$oitar = $width / 600;
								
								$image = wp_crop_image( $uploaded_file['file'], 0, 0, $width, $height, 600, $height / $oitar, false, str_replace( basename( $uploaded_file['file'] ), 'vfb-header-img-' . basename( $uploaded_file['file'] ), $uploaded_file['file'] ) );
								
								if ( is_wp_error( $image ) )
									wp_die( __( 'Image could not be processed.  Please go back and try again.' ), __( 'Image Processing Error' ) );
								$header_image = str_replace( basename( $uploaded_file['url'] ), basename( $image ), $uploaded_file['url'] );	
							}
							else {
								$dst_width = 600;
								$dst_height = absint( $height * ( 600 / $width ) );
								
								$cropped = wp_crop_image( $uploaded_file['file'], 0, 0, $width, $height, $dst_width, $dst_height, false, str_replace( basename( $uploaded_file['file'] ), 'vfb-header-img-' . basename( $uploaded_file['file'] ), $uploaded_file['file'] ) );
								
								if ( is_wp_error( $cropped ) )
									wp_die( __( 'Image could not be processed.  Please go back and try again.' ), __( 'Image Processing Error' ) );
								$header_image = str_replace( basename( $uploaded_file['url'] ), basename( $cropped ), $uploaded_file['url'] );
							}
						}
					}
					
					//$color_scheme 		= esc_html( $_REQUEST['color_scheme'] );
					$format 			= esc_html( $_REQUEST['format'] );
					$link_love 			= esc_html( $_REQUEST['link_love'] );
					$footer_text 		= esc_html( $_REQUEST['footer_text'] );
					$background_color 	= esc_html( $_REQUEST['background_color'] );
					$header_color 		= esc_html( $_REQUEST['header_color'] );
					$header_text_color 	= esc_html( $_REQUEST['header_text_color'] );
					$fieldset_color 	= esc_html( $_REQUEST['fieldset_color'] );
					$section_color 		= esc_html( $_REQUEST['section_color'] );
					$section_text_color = esc_html( $_REQUEST['section_text_color'] );
					$text_color 		= esc_html( $_REQUEST['text_color'] );
					$link_color 		= esc_html( $_REQUEST['link_color'] );
					$row_color 			= esc_html( $_REQUEST['row_color'] );
					$row_alt_color 		= esc_html( $_REQUEST['row_alt_color'] );
					$border_color 		= esc_html( $_REQUEST['border_color'] );
					$footer_color 		= esc_html( $_REQUEST['footer_color'] );
					$footer_text_color 	= esc_html( $_REQUEST['footer_text_color'] );
					$font_family 		= esc_html( $_REQUEST['font_family'] );
					$header_font_size 	= esc_html( $_REQUEST['header_font_size'] );
					$fieldset_font_size = esc_html( $_REQUEST['fieldset_font_size'] );
					$section_font_size 	= esc_html( $_REQUEST['section_font_size'] );
					$text_font_size 	= esc_html( $_REQUEST['text_font_size'] );
					$footer_font_size 	= esc_html( $_REQUEST['footer_font_size'] );
					
					check_admin_referer( 'update-design-' . $form_id );
					
					$email_design = array(
						//'color_scheme' 			=> $color_scheme,
						'format' 				=> $format,
						'link_love' 			=> $link_love,
						'footer_text' 			=> $footer_text,
						'background_color' 		=> $background_color,
						'header_image' 			=> $header_image,
						'header_color' 			=> $header_color,
						'header_text_color' 	=> $header_text_color,
						'fieldset_color' 		=> $fieldset_color,
						'section_color' 		=> $section_color,
						'section_text_color' 	=> $section_text_color,
						'text_color' 			=> $text_color,
						'link_color' 			=> $link_color,
						'row_color' 			=> $row_color,
						'row_alt_color' 		=> $row_alt_color,
						'border_color' 			=> $border_color,
						'footer_color' 			=> $footer_color,
						'footer_text_color' 	=> $footer_text_color,
						'font_family' 			=> $font_family,
						'header_font_size' 		=> $header_font_size,
						'fieldset_font_size' 	=> $fieldset_font_size,
						'section_font_size' 	=> $section_font_size,
						'text_font_size' 		=> $text_font_size,
						'footer_font_size' 		=> $footer_font_size
					);
					
					$newdata = array( 'form_email_design' => serialize( $email_design ) );
					
					$where = array( 'form_id' => $form_id );
					
					// Update form details
					$wpdb->update( $this->form_table_name, $newdata, $where );
				break;
				
				case 'email_delete_header' :
					$form_id = absint( $_REQUEST['form'] );
					
					check_admin_referer( 'delete-header-img-' . $form_id );
					
					$email = unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT form_email_design FROM $this->form_table_name WHERE form_id = %d", $form_id ) ) );
					
					foreach( $email as $field => &$value ) {
						$value = ( 'header_image' !== $field ) ? $value : '';
					}
					
					$newdata = array( 'form_email_design' => serialize( $email ) );
					
					$where = array( 'form_id' => $form_id );
					
					// Update form details
					$wpdb->update( $this->form_table_name, $newdata, $where );
					
					// Redirect to keep the URL clean (use AJAX in the future?)
					wp_redirect( 'admin.php?page=vfb-email-design' );
					exit();
					
				break;
				
				case 'upgrade' :
					
					// Set database names of free version
					$vfb_fields = $wpdb->prefix . 'visual_form_builder_fields';
					$vfb_forms = $wpdb->prefix . 'visual_form_builder_forms';
					$vfb_entries = $wpdb->prefix . 'visual_form_builder_entries';
					
					// Get all forms, fields, and entries
					$forms = $wpdb->get_results( "SELECT * FROM $vfb_forms ORDER BY form_id" );
					
					// Truncate the tables in case any forms or fields have been added
					$wpdb->query( "TRUNCATE TABLE $this->form_table_name" );
					$wpdb->query( "TRUNCATE TABLE $this->field_table_name" );
					$wpdb->query( "TRUNCATE TABLE $this->entries_table_name" );
					
					// Setup email design defaults
					$email_design = array(
						'format' 				=> 'html',
						'link_love' 			=> 'yes',
						'footer_text' 			=> '',
						'background_color' 		=> '#eeeeee',
						'header_image' 			=> '',
						'header_color' 			=> '#810202',
						'header_text_color' 	=> '#ffffff',
						'fieldset_color' 		=> '#680606',
						'section_color' 		=> '#5C6266',
						'section_text_color' 	=> '#ffffff',
						'text_color' 			=> '#333333',
						'link_color' 			=> '#1b8be0',
						'row_color' 			=> '#ffffff',
						'row_alt_color' 		=> '#eeeeee',
						'border_color' 			=> '#cccccc',
						'footer_color' 			=> '#333333',
						'footer_text_color' 	=> '#ffffff',
						'font_family' 			=> 'Arial',
						'header_font_size' 		=> 32,
						'fieldset_font_size' 	=> 20,
						'section_font_size' 	=> 15,
						'text_font_size' 		=> 13,
						'footer_font_size' 		=> 11
					);
					
					// Migrate all forms, fields, and entries
					foreach ( $forms as $form ) :
						$data = array(
							'form_id' 						=> $form->form_id,
							'form_key' 						=> $form->form_key,
							'form_title' 					=> $form->form_title,
							'form_email_subject' 			=> $form->form_email_subject,
							'form_email_to' 				=> $form->form_email_to,
							'form_email_from' 				=> $form->form_email_from,
							'form_email_from_name' 			=> $form->form_email_from_name,
							'form_email_from_override' 		=> $form->form_email_from_override,
							'form_email_from_name_override' => $form->form_email_from_name_override,
							'form_success_type' 			=> $form->form_success_type,
							'form_success_message' 			=> $form->form_success_message,
							'form_notification_setting' 	=> $form->form_notification_setting,
							'form_notification_email_name' 	=> $form->form_notification_email_name,
							'form_notification_email_from' 	=> $form->form_notification_email_from,
							'form_notification_email' 		=> $form->form_notification_email,
							'form_notification_subject' 	=> $form->form_notification_subject,
							'form_notification_message' 	=> $form->form_notification_message,
							'form_notification_entry' 		=> $form->form_notification_entry,
							'form_email_design' 			=> serialize( $email_design ),
							'form_label_alignment' 			=> '',
							'form_verification' 			=> 1,
							'form_entries_allowed' 			=> '',
							'form_entries_schedule'			=> ''
						);
						
						$wpdb->insert( $this->form_table_name, $data );
						
						$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $vfb_fields WHERE form_id = %d ORDER BY field_id", $form->form_id ) );
						// Copy each field and data
						foreach ( $fields as $field ) {

							$data = array(
								'field_id' 			=> $field->field_id,
								'form_id' 			=> $field->form_id,
								'field_key' 		=> $field->field_key,
								'field_type' 		=> $field->field_type,
								'field_name' 		=> $field->field_name,
								'field_description' => $field->field_description,
								'field_options' 	=> $field->field_options,
								'field_options_other'=> $field->field_options_other,
								'field_sequence' 	=> $field->field_sequence,
								'field_validation' 	=> $field->field_validation,
								'field_required' 	=> $field->field_required,
								'field_size' 		=> $field->field_size,
								'field_css' 		=> $field->field_css,
								'field_layout' 		=> $field->field_layout,
								'field_parent' 		=> $field->field_parent
							);
							
							$wpdb->insert( $this->field_table_name, $data );						
						}
						
						$entries = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $vfb_entries WHERE form_id = %d ORDER BY entries_id", $form->form_id ) );
						
						// Copy each entry
						foreach ( $entries as $entry ) {

							$data = array(
								'form_id' 			=> $entry->form_id,
								'data' 				=> $entry->data,
								'subject' 			=> $entry->subject,
								'sender_name' 		=> $entry->sender_name,
								'sender_email' 		=> $entry->sender_email,
								'emails_to' 		=> $entry->emails_to,
								'date_submitted' 	=> $entry->date_submitted,
								'ip_address'	 	=> $entry->ip_address
							);
							
							$wpdb->insert( $this->entries_table_name, $data );
						}

					endforeach;
					
					// Automatically deactivate free version of Visual Form Builder, if active
					if ( is_plugin_active( 'visual-form-builder/visual-form-builder.php' ) )
						deactivate_plugins( '/visual-form-builder/visual-form-builder.php' );
					
					// Set upgrade as complete so admin notice closes
					update_option( 'vfb_db_upgrade', 1 );
					
				break;
				
				case 'update_entry' :
					$entry_id = absint( $_REQUEST['entry_id'] );

					check_admin_referer( 'update-entry-' . $entry_id );
					
					// Get this entry's data
					$entry = $wpdb->get_var( $wpdb->prepare( "SELECT data FROM $this->entries_table_name WHERE entries_id = %d", $entry_id ) );
					
					$data = unserialize( $entry );
					
					// Loop through each field in the update form and save in a way we can use
					foreach ( $_REQUEST['field'] as $key => $value ) {
						$fields[] = array(
							'key' 	=> $key,
							'value' => $value
						);
					}
					
					// Loop through the entry data and replace the old values with the new
					foreach ( $data as $key => $value ) {
						
						// If it's an array, that's the only way we update
						if ( is_array( $value ) ) {
							// Cast each array as an object
							$obj = (object) $value;
							
							// Handle Checkboxes
							if ( isset( $fields[ $key ][ 'value' ] ) && is_array( $fields[ $key ][ 'value' ] ) )
								$fields[ $key ][ 'value' ] = implode( ', ', $fields[ $key ][ 'value' ] );
							
							// If the entry's field ID matches our $_REQUEST
							if ( $obj->id == $fields[ $key ]['key'] ) {
								
								$newdata[] = array(
									'id'               => $obj->id,
									'slug'             => $obj->slug,
									'name'             => $obj->name,
									'type'             => $obj->type,
									'options'          => $obj->options,
									'options_other'    => $obj->options_other,
									'parent_id'        => $obj->parent_id,
									'value'            => $fields[ $key ]['value']
								);
							}
							else
								$newdata[] = $value;
						}
					}
					
					$where = array( 'entries_id' => $entry_id );
					// Update entry data
					$wpdb->update( $this->entries_table_name, array( 'data' => serialize( $newdata ), 'notes' => esc_html( $_REQUEST['entries-notes'] ) ), $where );
					
				break;
			}
		}
	}
	
	/**
	 * The jQuery field sorting callback
	 * 
	 * @since 1.0
	 */
	public function ajax_sort_field() {
		global $wpdb;
		
		$data = array();

		foreach ( $_REQUEST['order'] as $k ) {
			if ( 'root' !== $k['item_id'] ) {
				$data[] = array(
					'field_id' 	=> $k['item_id'],
					'parent' 	=> $k['parent_id']
					);
			}
		}

		foreach ( $data as $k => $v ) {
			// Update each field with it's new sequence and parent ID
			$wpdb->update( $this->field_table_name, array( 'field_sequence' => $k, 'field_parent' => $v['parent'] ), array( 'field_id' => $v['field_id'] ) );
		}

		die(1);
	}

	/**
	 * The jQuery create field callback
	 * 
	 * @since 1.9
	 */
	public function ajax_create_field() {
		global $wpdb;
		
		$data = array();
		$field_options = $field_validation = $parent = $previous = '';
		
		foreach ( $_REQUEST['data'] as $k ) {
			$data[ $k['name'] ] = $k['value'];
		}

		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'toplevel_page_visual-form-builder-pro' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_create_field' ) {
			
			$form_id     = absint( $data['form_id'] );
			$field_key   = sanitize_title( $_REQUEST['field_type'] );
			$field_type  = strtolower( sanitize_title( $_REQUEST['field_type'] ) );
			
			$parent      = ( isset( $_REQUEST['parent'] ) && $_REQUEST['parent'] > 0 ) ? $_REQUEST['parent'] : 0;
			$previous    = ( isset( $_REQUEST['previous'] ) && $_REQUEST['previous'] > 0 ) ? $_REQUEST['previous'] : 0;
			
			// If a Page Break, the default name is Next, otherwise use the field type
			$field_name = ( 'page-break' == $field_type ) ? 'Next' : esc_html( $_REQUEST['field_type'] );

			// Set defaults for validation
			switch ( $field_type ) {
				case 'select' :
				case 'radio' :
				case 'checkbox' :
					$field_options = serialize( array( 'Option 1', 'Option 2', 'Option 3' ) );
				break;
				case 'email' :
				case 'url' :
				case 'phone' :
					$field_validation = $field_type;
				break;
				case 'currency' :
					$field_validation = 'number';
				break;
				case 'number' :
					$field_validation = 'digits';
				break;
				case 'min' :
				case 'max' :
					$field_validation = 'digits';
					$field_options = serialize( array( '10' ) );
				break;
				case 'range' :
					$field_validation = 'digits';
					$field_options = serialize( array( '1', '10' ) );
				break;
				case 'time' :
					$field_validation = 'time-12';
				break;
				case 'file-upload' :
					$field_options = serialize( array( 'png|jpe?g|gif' ) );
				break;
				case 'ip-address' :
					$field_validation = 'ipv6';
				break;
				case 'hidden' :
					$field_options = serialize( array( '' ) );
				break;
				case 'autocomplete' :
					$field_validation = 'auto';
					$field_options = serialize( array( 'Option 1', 'Option 2', 'Option 3' ) );
				break;
				case 'name' :
					$field_options = serialize( array( 'normal' ) );
				break;
			}

			check_ajax_referer( 'create-field-' . $data['form_id'], 'nonce' );
			
			// Get fields info
			$all_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d ORDER BY field_sequence ASC", $form_id ) );
			$field_sequence = 0;
			
			// We only want the fields that FOLLOW our parent or previous item						
			if ( $parent > 0 || $previous > 0 ) {				
				$cut_off = ( $previous > 0 ) ? $previous : $parent;
										
				foreach( $all_fields as $field_index => $field ) {
					if ( $field->field_id == $cut_off ) {
						$field_sequence = $field->field_sequence + 1;
						break;
					}
					else
						unset( $all_fields[ $field_index ] );
				}
				array_shift( $all_fields );
				
				// If the previous had children, we need to remove them so our item is placed correctly
				if ( !$parent && $previous > 0 ) {
					foreach( $all_fields as $field_index => $field ) {
						if ( !$field->field_parent )
							break;
						else {
							$field_sequence = $field->field_sequence + 1;
							unset( $all_fields[ $field_index ] );
						}
					}
				}
			}
			
			// Create the new field's data
			$newdata = array(
				'form_id' 			=> absint( $data['form_id'] ),
				'field_key' 		=> $field_key,
				'field_name' 		=> $field_name,
				'field_type' 		=> $field_type,
				'field_options' 	=> $field_options,
				'field_sequence' 	=> $field_sequence,
				'field_validation' 	=> $field_validation,
				'field_parent' 		=> $parent
			);
			
			// Create the field
			$wpdb->insert( $this->field_table_name, $newdata );
			$insert_id = $wpdb->insert_id;
			
			// VIP fields			
			$vip_fields = array( 'verification', 'secret', 'submit' );
			
			// Rearrange the fields that follow our new data
			foreach( $all_fields as $field_index => $field ) {
				if ( !in_array( $field->field_type, $vip_fields ) ) {
					$field_sequence++;
					// Update each field with it's new sequence and parent ID
					$wpdb->update( $this->field_table_name, array( 'field_sequence' => $field_sequence ), array( 'field_id' => $field->field_id ) );
				}
			}
			
			// Move the VIPs			
			foreach ( $vip_fields as $update ) {
				$field_sequence++;
				$where = array(
					'form_id' 		=> absint( $data['form_id'] ),
					'field_type' 	=> $update
				);				
				$wpdb->update( $this->field_table_name, array( 'field_sequence' => $field_sequence ), $where );
				
			}
			
			echo $this->field_output( $data['form_id'], $insert_id );
		}
		
		die(1);
	}

	
	/**
	 * The jQuery delete field callback
	 * 
	 * @since 1.9
	 */
	public function ajax_delete_field() {
		global $wpdb;

		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'toplevel_page_visual-form-builder-pro' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_delete_field' ) {
			$form_id     = absint( $_REQUEST['form'] );
			$field_id    = absint( $_REQUEST['field'] );
			
			check_ajax_referer( 'delete-field-' . $form_id, 'nonce' );
			
			if ( isset( $_REQUEST['child_ids'] ) ) {
				foreach ( $_REQUEST['child_ids'] as $children ) {
					$parent = absint( $_REQUEST['parent_id'] );
					
					// Update each child item with the new parent ID
					$wpdb->update( $this->field_table_name, array( 'field_parent' => $parent ), array( 'field_id' => $children ) );
				}
			}
			
			// Delete the field
			$wpdb->query( $wpdb->prepare( "DELETE FROM $this->field_table_name WHERE field_id = %d", $field_id ) );
		}
		
		die(1);
	}

	/**
	 * The jQuery create field callback
	 * 
	 * @since 1.9
	 */
	public function ajax_duplicate_field() {
		global $wpdb;
		
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'toplevel_page_visual-form-builder-pro' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_duplicate_field' ) {
			
			$form_id     = absint( $_REQUEST['form'] );
			$field_id    = absint( $_REQUEST['field'] );

			check_ajax_referer( 'duplicate-field-' . $form_id, 'nonce' );
			
			// Get fields info
			$this_field = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE field_id = %d", $field_id ) );
			$all_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d ORDER BY field_sequence ASC", $form_id ) );
			$field_sequence = 0;
						
			// We only want the fields that FOLLOW our field
			foreach( $all_fields as $field_index => $field ) {
				if ( $field->field_id == $field_id ) {
					$field_sequence = $field->field_sequence + 1;
					break;
				}
				else
					unset( $all_fields[ $field_index ] );
			}
			array_shift( $all_fields );
			
			foreach ( $this_field as $field ) {
				$field_key              = $field->field_key;
				$field_type             = $field->field_type;
				$field_options          = $field->field_options;
				$field_options_other    = $field->field_options_other;
				$field_description		= $field->field_description;
				$field_name				= $field->field_name;
				$field_parent			= $field->field_parent;
				$field_validation		= $field->field_validation;
				$field_required			= $field->field_required;
				$field_size				= $field->field_size;
				$field_css				= $field->field_css;
				$field_layout			= $field->field_layout;
				$field_default			= $field->field_default;
			}
						
			// Create the new field's data
			$newdata = array(
				'form_id'               => $form_id,
				'field_key'             => $field_key,
				'field_type'            => $field_type,
				'field_options'         => $field_options,
				'field_options_other'   => $field_options_other,
				'field_description'     => $field_description,
				'field_name'            => $field_name,
				'field_sequence'        => $field_sequence,
				'field_parent'          => $field_parent,
				'field_validation'      => $field_validation,
				'field_required'        => $field_required,
				'field_size'            => $field_size,
				'field_css'             => $field_css,
				'field_layout'          => $field_layout,
				'field_default'         => $field_default,
			);
			
			// Create the field
			$wpdb->insert( $this->field_table_name, $newdata );
			$insert_id = $wpdb->insert_id;
			
			// VIP fields			
			$vip_fields = array( 'verification', 'secret', 'submit' );
			
			// Rearrange the fields that follow our new data
			foreach( $all_fields as $field_index => $field ) {
				if ( !in_array( $field->field_type, $vip_fields ) ) {
					$field_sequence++;
					// Update each field with it's new sequence and parent ID
					$wpdb->update( $this->field_table_name, array( 'field_sequence' => $field_sequence ), array( 'field_id' => $field->field_id ) );
				}
			}
			
			// Move the VIPs			
			foreach ( $vip_fields as $update ) {
				$field_sequence++;
				$where = array(
					'form_id' 		=> $form_id,
					'field_type' 	=> $update
				);				
				$wpdb->update( $this->field_table_name, array( 'field_sequence' => $field_sequence ), $where );
				
			}
			
			echo $this->field_output( $form_id, $insert_id );
		}
		
		die(1);
	}
	
	/**
	 * Display Bulk Add Options pop-up
	 * 
	 * Activated by the Bulk Add Options link button which references the AJAX name
	 *
	 * @since 1.6
	 */
	public function ajax_bulk_add(){
	?>
		<div id="vfb_bulk_add">
			<form id="vfb_bulk_add_options" class="media-upload-form type-form validate">
				<h3 class="media-title">Bulk Add Options</h3>
				<ol>
					<li>Select from the predefined categories</li>
					<li>If needed, customize the options. Place each option on a new line.</li>
					<li>Add to your field</li>
				</ol>
				<?php
					$bulk_options = $days = $years = array();
					
					// Build Days array
					for ( $i = 1; $i <= 31; ++$i ) {
						$days[] = $i;
					}
					
					//Build Years array
					for ( $i = date( 'Y' ); $i >= 1925; --$i ) {
						$years[] = $i;
					}
					
					$bulk_options = array(
						'U.S. States'		=> array( 'Alabama', 'Alaska', 'Arizona', 'Arkansas', 'California', 'Colorado', 'Connecticut', 'Delaware', 'Florida', 'Georgia', 'Hawaii', 'Idaho', 'Illinois', 'Indiana', 'Iowa', 'Kansas', 'Kentucky', 'Louisiana', 'Maine', 'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Mississippi', 'Missouri', 'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey', 'New Mexico', 'New York', 'North Carolina', 'North Dakota', 'Ohio', 'Oklahoma', 'Oregon', 'Pennsylvania', 'Rhode Island', 'South Carolina', 'South Dakota', 'Tennessee', 'Texas', 'Utah', 'Vermont', 'Virginia', 'Washington', 'West Virginia', 'Wisconsin', 'Wyoming' ), 
						
						'U.S. States Abbreviations'	=> array( 'AK','AL','AR','AS','AZ','CA','CO','CT','DC','DE','FL','GA','GU','HI','IA','ID', 'IL','IN','KS','KY','LA','MA','MD','ME','MH','MI','MN','MO','MS','MT','NC','ND','NE','NH','NJ','NM','NV','NY', 'OH','OK','OR','PA','PR','PW','RI','SC','SD','TN','TX','UT','VA','VI','VT','WA','WI','WV','WY' ),
						
						'Countries'			=> $this->countries, 
						'Days of the Week'	=> array( 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ),
						'Days'				=> $days,
						'Months'			=> array( 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December' ),
						'Years'				=> $years, 
						'Gender'			=> array( 'Male', 'Female', 'Prefer not to answer' ),
						'Age Range'			=> array( 'Under 18', '18 - 24', '25 - 34', '35 - 44', '45 - 54', '55 - 64', '65 or older', 'Prefer not to answer' ),
						'Marital Status'	=> array( 'Single', 'Married', 'Divorced', 'Separated', 'Widowed', 'Domestic Partner', 'Unmarried Partner', 'Prefer not to answer' ),
						'Ethnicity'			=> array( 'American Indian/Alaskan Native', 'Asian', 'Native Hawaiian or Other Pacific Islander', 'Black or African-American', 'White', 'Not disclosed' ), 
						'Prefix'			=> array( 'Mr.', 'Mrs.', 'Ms.', 'Miss', 'Dr.' ),
						'Suffix'			=> array( 'Sr.', 'Jr.', 'Ph.D', 'M.D' ),
						'Agree'				=> array( 'Strongly Agree', 'Agree', 'Neutral', 'Disagree', 'Strongly Disagree', 'N/A' ),
						'Education'			=> array( 'Some High School', 'High School/GED', 'Some College', 'Associate\'s Degree', 'Bachelor\'s Degree', 'Master\'s Degree', 'Doctoral Degree', 'Professional Degree' )
					);
					
					$more_options = apply_filters( 'vfb_bulk_add_options', array() );
					
					// Merge our pre-defined bulk options with possible additions via filter
					$bulk_options = array_merge( $bulk_options, $more_options );
				?>
				<div id="bulk-options-left">
					<ul>
					<?php foreach ( $bulk_options as $name => $values ) : ?>
						<li>
							<a id="<?php echo $name; ?>" class="vfb-bulk-options" href="#"><?php echo $name; ?></a>
							<ul style="display:none;">
							<?php foreach ( $values as $value ) : ?>
								<li><?php echo $value; ?></li>
							<?php endforeach; ?>
							</ul>
						</li>
					<?php endforeach; ?>
					</ul>
				</div>
				<div id="bulk-options-right">
					<textarea id="choicesText" class="textarea" name="choicesText"></textarea>
					<p><input type="submit" class="button-primary" value="Add Options" /></p>
				</div>
			</form>
		</div>
		
	<?php
		die(1);
	}

	/**
	 * Display the conditional fields builder
	 * 
	 * @since 1.9
	 */
	public function ajax_conditional_fields() {
		global $wpdb;
		
		$form_id = absint( $_REQUEST['form_id'] );
		$field_id = absint( $_REQUEST['field_id'] );
		
		// Get the field name and cache the query for the other variables
		$field_name = $wpdb->get_var( $wpdb->prepare( "SELECT field_name, field_key, field_rule_setting, field_rule FROM $this->field_table_name WHERE field_id = %d AND form_id = %d ORDER BY field_sequence ASC", $field_id, $form_id ) );
		$field_key	 		= $wpdb->get_var( null, 1 );
		$field_rule_setting = $wpdb->get_var( null, 2 );
		$rules 		= unserialize( $wpdb->get_var( null, 3 ) );
		
		// Only get checkbox, select, and radio for list of options
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE field_type IN('checkbox', 'select', 'radio') AND form_id = %d ORDER BY field_sequence ASC", $form_id ) );
		$field_options = $wpdb->get_var( null, 4 );
		
		// Display the conditional rules if setting is on
		$display = ( $field_rule_setting ) ? ' class="show-fields"' : '';
		
		// Count the number of rules for our index
		$num_fields = count( $rules['rules'] );
		
	?>
		<div id="vfb-conditional-fields">
			<form id="vfb-add-conditional-fields" class="media-upload-form type-form validate">
				<h3 class="media-title">Conditional Field Rules</h3>
					<label for="vfb-conditional-setting">
						<input type="checkbox" name="conditional_setting" id="vfb-conditional-setting" value="1" <?php checked( $field_rule_setting, '1' ); ?> /> 
						<?php _e( 'Enable Conditional Rule for this field', 'visual-form-builder-pro' ); ?>
					</label>
					
					<div id="vfb-build-conditional-fields-container"<?php echo $display; ?>>
							<?php if ( 1 == $field_rule_setting ) : ?>
							<p><select name="conditional_show">
								<option value="show" <?php selected( $rules['conditional_show'], 'show' ); ?>><?php _e( 'Show', 'visual-form-builder-pro' ); ?></option>
								<option value="hide" <?php selected( $rules['conditional_show'], 'hide' ); ?>><?php _e( 'Hide', 'visual-form-builder-pro' ); ?></option>
							</select> the <strong><?php echo esc_html( $field_name ); ?></strong> field based on
							<select name="conditional_logic">
								<option value="all" <?php selected( $rules['conditional_logic'], 'all' ); ?>><?php _e( 'all', 'visual-form-builder-pro' ); ?></option>
								<option value="any" <?php selected( $rules['conditional_logic'], 'any' ); ?>><?php _e( 'any', 'visual-form-builder-pro' ); ?></option>
							</select>
							 of the following rules:
							</p>
							
							<?php for ( $i = 0; $i < $num_fields; $i++ ) : ?>
							
							<div class="vfb-conditional-fields-data">
								if <select name="rules[<?php echo $i; ?>][field]" class="vfb-conditional-other-fields">
									<?php foreach ( $fields as $field ) : ?>
										<option value="<?php echo $field->field_id; ?>" <?php selected( $rules['rules'][ $i ]['field'], $field->field_id ); ?>><?php echo esc_html( $field->field_name ); ?></option>
									<?php endforeach; ?>
								</select>
								
								<select name="rules[<?php echo $i; ?>][condition]>" class="vfb-conditional-condition">
									<option value="is" <?php selected( $rules['rules'][ $i ]['condition'], 'is' ); ?>><?php _e( 'is', 'visual-form-builder-pro' ); ?></option>
									<option value="isnot" <?php selected( $rules['rules'][ $i ]['condition'], 'isnot' ); ?>><?php _e( 'is not', 'visual-form-builder-pro' ); ?></option>
								</select>
								<?php
									$these_opts = $wpdb->get_var( $wpdb->prepare( "SELECT field_options FROM $this->field_table_name WHERE field_id = %d ORDER BY field_sequence ASC", $rules['rules'][ $i ]['field'] ) );
								?>
								<select name="rules[<?php echo $i; ?>][option]" class="vfb-conditional-other-fields-options">
									<?php
									if ( !empty( $these_opts ) ) {
										$options = maybe_unserialize( $these_opts );
										
										foreach ( $options as $option ) { ?>
											<option value="<?php echo $option; ?>" <?php selected( $rules['rules'][ $i ]['option'], esc_html( $option ) ); ?>><?php echo esc_html( $option ); ?></option>
										<?php }
									} 
									?>
								</select>
								
								<a href="#" class="addCondition" title="Add Condition"><?php _e( 'Add', 'visual-form-builder-pro' ); ?></a> <a href="#" class="deleteCondition" title="Delete Condition"><?php _e( 'Delete', 'visual-form-builder-pro' ); ?></a>
							</div> <!-- #vfb-conditional-fields-data -->
							<?php endfor; ?>
							
							<?php else: ?>
							
							<p><select name="conditional_show">
								<option value="show"><?php _e( 'Show', 'visual-form-builder-pro' ); ?></option>
								<option value="hide"><?php _e( 'Hide', 'visual-form-builder-pro' ); ?></option>
							</select> the <strong><?php echo esc_html( $field_name ); ?></strong> field based on 
							<select name="conditional_logic">
								<option value="all"><?php _e( 'all', 'visual-form-builder-pro' ); ?></option>
								<option value="any"><?php _e( 'any', 'visual-form-builder-pro' ); ?></option>
							</select> of the following rules:
							</p>
							
							<div class="vfb-conditional-fields-data">
								if <select name="rules[0][field]" class="vfb-conditional-other-fields">
									<?php foreach ( $fields as $field ) : ?>
										<option value="<?php echo $field->field_id; ?>"><?php echo esc_html( $field->field_name ); ?></option>
									<?php endforeach; ?>
								</select>
								<select name="rules[0][condition]>" class="vfb-conditional-condition">
									<option value="is"><?php _e( 'is', 'visual-form-builder-pro' ); ?></option>
									<option value="isnot"><?php _e( 'is not', 'visual-form-builder-pro' ); ?></option>
								</select>
								<select name="rules[0][option]" class="vfb-conditional-other-fields-options">
								<?php
									if ( !empty( $field_options ) ) {
										$options = maybe_unserialize( $field_options );
										
										foreach ( $options as $option ) {
											echo '<option value="' . $option . '">' . esc_html( $option ) . '</option>';
										}
									}
								?>
								</select>
								<a href="#" class="addCondition" title="Add Condition"><?php _e( 'Add', 'visual-form-builder-pro' ); ?></a> <a href="#" class="deleteCondition" title="Delete Condition"><?php _e( 'Delete', 'visual-form-builder-pro' ); ?></a>
							
							<?php endif; ?>
					</div> <!-- #vfb-build-conditional-fields-container -->
				<input type="hidden" name="field_id" value="<?php echo $field_id; ?>" />
				<input type="hidden" name="field_id_attr" value="<?php echo 'vfb-' . esc_html( $field_key ) . '-' . $field_id; ?>" />
				<p><input type="submit" class="button-primary" value="Save" /></p>
				
			</form>
		</div>
		
	<?php
		die(1);
	}

	/**
	 * AJAX callback for the conditional fields options
	 * 
	 * @since 1.9
	 */
	public function ajax_conditional_fields_options() {
		global $wpdb;
		
		$field_id = absint( $_REQUEST['field_id'] );
		
		$field_options = $wpdb->get_var( $wpdb->prepare( "SELECT field_options FROM $this->field_table_name WHERE field_id = %d ORDER BY field_sequence ASC", $field_id ) );
		
		$first_options = '';
		if ( !empty( $field_options ) ) {
			$options = maybe_unserialize( $field_options );
			
			foreach ( $options as $option ) {
				$first_options .= '<option value="' . $option . '">' . esc_html( $option ) . '</option>';
			}
		}
		
		echo $first_options;
		
		die(1);
	}
	
	/**
	 * Save the conditional fields
	 * 
	 * @since 1.9
	 */
	public function ajax_conditional_fields_save() {
		global $wpdb;
		
		parse_str( $_REQUEST['data'], $data );
		
		// Reset the array index in case it's become mangled during cloning
		$conditions = array_values( $data['rules'] );
		
		// Reload the rules back into our $data array
		$data['rules'] = $conditions;		
		
		if ( isset( $_REQUEST['page'] ) && $_REQUEST['page'] == 'toplevel_page_visual-form-builder-pro' && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_conditional_fields_save' ) {
			
			$field_id 		= absint( $data['field_id'] );
			$rule_setting 	= absint( $data['conditional_setting'] );
			$rules 			= ( 1 == $rule_setting ) ? serialize( $data ) : '';
			
			$new_data = array(
				'field_rule_setting'	=> $rule_setting,
				'field_rule' 			=> $rules,
			);
			
			$wpdb->update( $this->field_table_name, $new_data, array( 'field_id' => $field_id ) );
		}
		
		die(1);
	}

	/**
	 * The jQuery PayPal Assign Price to Fields callback
	 * 
	 * @since 1.0
	 */
	public function ajax_paypal_price() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_paypal_price' ) {
			$form_id = absint( $_REQUEST['form_id'] );
			$field_id = absint( $_REQUEST['field_id'] );
			
			$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d AND field_id = %d", $form_id, $field_id ) );
			$paypal_price_field = unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT form_paypal_field_price FROM $this->form_table_name WHERE form_id = %d", $form_id ) ) );
			
			$price_option = '';
			
			foreach ( $fields as $field ) {
				// If a text input field, only display a message
				if ( in_array( $field->field_type, array( 'text', 'currency' ) ) )
					$price_option = '<p>Amount Based on User Input</p>';
				// If field has options, let user assign prices to inputs
				elseif ( in_array( $field->field_type, array( 'select', 'radio', 'checkbox' ) ) ) {
					$options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
					
					// Loop through each option and output
					foreach ( $options as $option => $value ) {
						$price_field_amount = ( isset( $paypal_price_field['prices'] ) ) ? stripslashes( $paypal_price_field['prices'][$option]['amount'] ) : '';
						
						$price_option .= '<p class="description description-wide"><label>' . stripslashes( $value ) . '<input class="widefat" type="text" value="' . $price_field_amount . '" name="form_paypal_field_price[prices][' . $option . '][amount]" /></label><br></p>';
						
						echo '<input type="hidden" name="form_paypal_field_price[prices][' . $option . '][id]" value="' . stripslashes( $value ) . '" />';
					}
				}
				
				// Store the name as vfb-field_key-field_id for comparison when setting up PayPal form redirection
				echo '<input type="hidden" name="form_paypal_field_price[name]" value="vfb-' . $field->field_id . '" />';
			}
			
			echo $price_option;
		}

		die(1);
	}

	public function ajax_form_settings() {
		global $current_user;
		get_currentuserinfo();
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_form_settings' ) {
			$form_id     = absint( $_REQUEST['form'] );
			$status      = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'opened';
			$accordion   = isset( $_REQUEST['accordion'] ) ? $_REQUEST['accordion'] : 'general-settings';
			$user_id     = $current_user->ID;
			
			$form_settings = get_user_meta( $user_id, 'vfb-form-settings', true );
			
			$array = array(
				'form_setting_tab' 	=> $status,
				'setting_accordion' => $accordion
			);
			
			// Set defaults if meta key doesn't exist	
			if ( !$form_settings || $form_settings == '' ) {
				$meta_value[ $form_id ] = $array;
				
				update_user_meta( $user_id, 'vfb-form-settings', $meta_value );
			}
			else {
				$form_settings[ $form_id ] = $array;
				
				update_user_meta( $user_id, 'vfb-form-settings', $form_settings );
			}
		}
		
		die(1);
	}
	
	/**
	 * Form sorting callback
	 * 
	 * @since 1.8
	 */
	public function ajax_form_order() {
		global $wpdb, $current_user;
		
		get_currentuserinfo();
		
		$data = array();
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_form_order' ) {
			$user_id = $current_user->ID;
			
			$form_order = get_user_meta( $user_id, 'vfb-form-order', true );
			
			foreach ( $_REQUEST['order'] as $k ) {
				preg_match( '/(\d+$)/', $k, $matches );
				$data[] = $matches[1];
			}
			
			// Set defaults if meta key doesn't exist	
			if ( !$form_order || $form_order == '' ) {
				$meta_value = $data;
				
				update_user_meta( $user_id, 'vfb-form-order', $meta_value );
			}
			else {
				$form_order = $data;
				
				update_user_meta( $user_id, 'vfb-form-order', $form_order );
			}
		}

		die(1);
	}
	
	/**
	 * Form order type callback
	 * 
	 * @since 1.8
	 */
	public function ajax_form_order_type() {
		global $current_user;
		
		get_currentuserinfo();
		
		$data = array();
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_form_order_type' ) {
			$user_id = $current_user->ID;
			
			$type = get_user_meta( $user_id, 'vfb-form-order-type', true );
			
			if ( isset( $_REQUEST['type'] ) ) {
				$meta_value = ( in_array( $_REQUEST['type'], array( 'order', 'list' ) ) ) ? esc_attr( $_REQUEST['type'] ) : '';
				update_user_meta( $user_id, 'vfb-form-order-type', $meta_value );
			}
		}
		
		echo $this->all_forms();
		
		die(1);
	}
	
	/**
	 * The Google Chart bar chart callback
	 * 
	 * @since 1.0
	 */
	public function ajax_graphs() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_graphs' ) {
			$form_id 	= absint( $_REQUEST['form'] );
			$view 		= esc_html( $_REQUEST['view'] );
			$start 		= esc_html( $_REQUEST['date_start'] );
			$end 		= esc_html( $_REQUEST['date_end'] );
			
			$date_where = $avg = '';
			$date = $rows = array();
			$i = 1;
			
			switch( $view ) {
				case 'monthly' :
					$where = 'Year, Month';
					$d = 'Y-m';
				break;
				
				case 'weekly' :
					$where = 'Year, Week';
					$d = 'Y \WW';
				break;
				
				case 'daily' :
					$where = 'Year, Month, Day';
					$d = 'Y-m-d';
				break;
			}
			
			if ( $start !== '0' )
				$date_where .= $wpdb->prepare( " AND date_submitted >= %s", date( 'Y-m-d', strtotime( $start ) ) );
			if ( $end !== '0' )
				$date_where .= $wpdb->prepare( " AND date_submitted < %s", date( 'Y-m-d', strtotime('+1 month', strtotime( $end ) ) ) );
			
			// Get counts of the entries based on the Date/view set above
			$entries = $wpdb->get_results( $wpdb->prepare( "SELECT DAY( date_submitted ) AS Day, MONTH( date_submitted ) as Month, WEEK( date_submitted ) as Week, YEAR( date_submitted ) as Year, COUNT(*) as Count FROM $this->entries_table_name WHERE form_id = %d $date_where GROUP BY $where ORDER BY $where", $form_id ) );
			
			// Send back empty values if nothing found
			if ( !$entries ) {
				echo '{"entries": [{"date": "0", "count": 0}]}';
				die(1);
			}
			
			// Loop through entries and setup our array for JSON output
			foreach ( $entries as $entry ) {
				$date[] = array(
					'date' 		=> date( $d, mktime( 0, 0, 0, $entry->Month, $entry->Day, $entry->Year ) ),
					'count' 	=> $entry->Count
				);
			}			
			
			// Setup our JSON output array
			foreach ( $date as $val ) {
				$avg += $val[ 'count' ];
				$daily_average = round( ( $avg / $i ), 2 );
				
				$rows[] = '{"date": "' . $val['date'] . '", "count": ' . $val['count'] . ', "avg": ' . $daily_average . '}';
								
				$i++;
			}
			
			// Comma separate each row
			echo '{"entries": [' . implode( ',', $rows ) . ']}';
		}
		
		die(1);
	}
	
	/**
	 * Display the additional media button
	 * 
	 * Used for inserting the form shortcode with desired form ID
	 *
	 * @since 1.4
	 */
	public function ajax_media_button(){
		global $wpdb;
		
		// Sanitize the sql orderby
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$forms = $wpdb->get_results( "SELECT form_id, form_title FROM $this->form_table_name ORDER BY $order" );
	?>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
				$( '#add_vfb_form' ).submit(function(e){
			        e.preventDefault();
			        
			        window.send_to_editor( '[vfb id=' + $( '#vfb_forms' ).val() + ']' );
			        
			        window.tb_remove();
			    });
			});
	    </script>
		<div id="vfb_form">
			<form id="add_vfb_form" class="media-upload-form type-form validate">
				<h3 class="media-title">Insert Visual Form Builder Form</h3>
				<p>Select a form below to insert into any Post or Page.</p>
				<select id="vfb_forms" name="vfb_forms">
					<?php foreach( $forms as $form ) : ?>
						<option value="<?php echo $form->form_id; ?>"><?php echo $form->form_title; ?></option>
					<?php endforeach; ?>
				</select>
				<p><input type="submit" class="button-primary" value="Insert Form" /></p>
			</form>
		</div>
	<?php
		die(1);
	}
	
	/**
	 * The jQuery field autocomplete callback
	 * 
	 * @since 1.0
	 */
	public function ajax_autocomplete() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_autocomplete' ) {
			$term 		= esc_html( $_REQUEST['term'] );
			$form_id 	= absint( $_REQUEST['form'] );
			$field_id 	= absint( $_REQUEST['field'] );
			
			$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d AND field_id = %d ORDER BY field_sequence ASC", $form_id, $field_id ) );
	
			$suggestions = array();
			
			foreach ( $fields as $field ) {
				$options = unserialize( $field->field_options );
				
				foreach ( $options as $opts ){
					// Find a match in our list of options
					$pos = stripos( $opts, $term );
					
					// If a match was found, add it to the suggestions
					if ( $pos !== false )
						$suggestions[] = array( 'value' => $opts );
				}
				
				// Send a JSON-encoded array to our AJAX call
				echo json_encode( $suggestions );
			}
		}
		
		die(1);
	}
	
	/**
	 * The jQuery unique username callback
	 * 
	 * @since 1.0
	 */
	public function ajax_check_username() {
		global $wpdb;
		
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'visual_form_builder_check_username' ) {
			$username 	= esc_html( $_REQUEST['username'] );
			$users 		= get_users();
			$valid 		= 'true';
			
			// Loop through each WP user
			foreach( $users as $user ) {
				// If the WP username matches what's entered on the form
				if ( $user->user_login == $username )
					$valid = 'false';
			}
			
			echo $valid;
		}
		
		die(1);
	}
	
	/**
	 * All Forms output in admin
	 * 
	 * @since 1.9
	 */
	public function all_forms() {
		global $wpdb, $current_user;
		
		get_currentuserinfo();
		
		// Save current user ID
		$user_id = $current_user->ID;
				
		// Get the Form Order type settings, if any
		$user_form_order_type = get_user_meta( $user_id, 'vfb-form-order-type', true );
		
		// Get the Form Order settings, if any
		$user_form_order = get_user_meta( $user_id, 'vfb-form-order' );
		foreach ( $user_form_order as $form_order ) {
			$form_order = implode( ',', $form_order );
		}
		
		// Query to get all forms
		if ( in_array( $user_form_order_type, array( 'order', '' ) ) )
			$order = ( isset( $form_order ) ) ? "FIELD( form_id, $form_order )" : sanitize_sql_orderby( 'form_id DESC' );
		else
			$order = sanitize_sql_orderby( 'form_title ASC' );
		
		$where = apply_filters( 'vfb_pre_get_forms', '' );
		$forms = $wpdb->get_results( "SELECT * FROM $this->form_table_name WHERE 1=1 $where ORDER BY $order" );
			
		$a = array();
		
		if ( !$forms )
			echo '<div class="vfb-form-alpha-list"><h3 id="vfb-no-forms">You currently do not have any forms.  Click on the <a href="' . esc_url( admin_url( 'admin.php?page=vfb-add-new' ) ) . '">New Form</a> button to get started.</h3></div>';
	?>	
				<?php
				if ( in_array( $user_form_order_type, array( 'order', '' ) ) ) :
					// Loop through each for and build the tabs
					foreach ( $forms as $form ) {
						$form_id 	= $form->form_id;
						$form_title = stripslashes( $form->form_title );
						$form_paypal_setting = $form->form_paypal_setting;
						$entries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->entries_table_name WHERE form_id = %d", $form_id ) );
						$entries_count_today = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->entries_table_name WHERE form_id = %d AND date_submitted >= curdate()", $form_id ) );
					?>
						<div class="vfb-box form-boxes" id="vfb-form-<?php echo $form_id; ?>">
							<div class="vfb-form-meta-actions">
								<h2 title="Drag to reorder" class="form-boxes-title"><?php echo $form_title; ?></h2>
								
								<div class="vfb-form-meta-entries">
									<ul class="vfb-meta-entries-list">
										<li><a  class="vfb-meta-entries-header" href="<?php echo esc_url( add_query_arg( array( 'form-filter' => $form_id ), admin_url( 'admin.php?page=vfb-entries' ) ) ); ?>">Entries</a></li>
										<li><a class="vfb-meta-entries-total" href="<?php echo esc_url( add_query_arg( array( 'form-filter' => $form_id ), admin_url( 'admin.php?page=vfb-entries' ) ) ); ?>"><span class="entries-count"><?php echo $entries_count; ?></span></a> Total</li>
										<li><a class="vfb-meta-entries-total-today" href="<?php echo esc_url( add_query_arg( array( 'form-filter' => $form_id, 'today' => 1 ), admin_url( 'admin.php?page=vfb-entries' ) ) ); ?>"><span class="entries-count"><?php echo $entries_count_today; ?></span></a> Today</li>
									</ul>
								</div>
								
								<div class="vfb-form-meta-other">
									<ul>
										<li><a href="<?php echo esc_url( add_query_arg( array( 'form_id' => $form_id ), admin_url( 'admin.php?page=vfb-email-design' ) ) ); ?>">Email Design</a></li>
										<li><a href="<?php echo esc_url( add_query_arg( array( 'form_id' => $form_id ), admin_url( 'admin.php?page=vfb-reports' ) ) ); ?>">Analytics</a></li>
										
										<?php if ( class_exists( 'VFB_Pro_Payments' ) ) : ?>
										
										<li><a href="<?php echo esc_url( add_query_arg( array( 'form_id' => $form_id ), admin_url( 'admin.php?page=vfb-payments' ) ) ); ?>">Payments</a></li>
										
										<?php endif; ?>
										
									</ul>
									<?php echo ( $form_paypal_setting ) ? '<p class="paypal">' . __( 'Forwards to PayPal', 'visual-form-builder-pro' ) . '</p>' : ''; ?>
								</div>
							</div>
							<div class="clear"></div>
							<div class="vfb-publishing-actions">
	                            <p>
	                            <?php if ( current_user_can( 'vfb_edit_forms' ) ) : ?>	
	                            	<a class="" href="<?php echo esc_url( add_query_arg( array( 'form' => $form->form_id ), admin_url( 'admin.php?page=visual-form-builder-pro' ) ) ); ?>">
	                            	<strong><?php _e( 'Edit Form', 'visual-form-builder-pro' ); ?></strong>
	                            	</a> |
	                            <?php endif; ?>
	                            <?php if ( current_user_can( 'vfb_delete_forms' ) ) : ?>
	                            	<a class="submitdelete menu-delete" href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=delete_form&amp;form=' . $form_id ), 'delete-form-' . $form_id ) ); ?>" class=""><?php _e( 'Delete' , 'visual-form-builder-pro'); ?></a> |
	                            <?php endif; ?>
	                            <?php if ( current_user_can( 'vfb_edit_forms' ) ) : ?>
	                            	<a href="<?php echo esc_url( add_query_arg( array( 'form' => $form_id, 'preview' => 1 ), plugins_url( 'visual-form-builder-pro/form-preview.php' ) ) ); ?>" class="" target="_blank" title="<?php _e( 'Preview the Form', 'visual-form-builder-pro' ); ?>"><?php _e( 'Preview', 'visual-form-builder-pro' ); ?></a>
	                            <?php endif; ?>
	                            </p>                            
							</div> <!-- .vfb-publishing-actions -->
						</div> <!-- .vfb-box -->
					<?php
					}					
				?>
					<div class="vfb-empty-container ui-state-disabled"></div>
					<!--</div>  .vfb-form-order-type .type-order -->
				<?php
				else :
					// Loop through each for and build the tabs
					foreach ( $forms as $form ) {
						$form_id 	= $form->form_id;
						$form_title = stripslashes( $form->form_title );
						$form_paypal_setting = $form->form_paypal_setting;
						$entries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $this->entries_table_name WHERE form_id = %d", $form_id ) );
						$sort = substr( strtoupper( $form_title ), 0, 1 );
						
						if ( preg_match( '/[0-9]/i', $sort ) )
							$sort = '0-9';
						
						$a[ $sort ][] = array(
							'id'			=> $form_id,
							'title' 		=> $form_title,
							'paypal'		=> $form_paypal_setting,
							'entries_count'	=> $entries_count
						);		
					}
				?>
					<div class="vfb-form-alpha-list">
						<hr>
							<?php
							foreach ( $a as $alpha => $value ) :
							?>
							<div class="vfb-form-alpha-group">
								<h2 class='letter'><?php echo $alpha; ?></h2>
								<?php
								foreach ( $value as $alphaForm ) {
								?>
								
								<div class="vfb-form-alpha-form">
									<h3><a class="" href="<?php echo esc_url( add_query_arg( array( 'form' => $alphaForm['id'] ), admin_url( 'admin.php?page=visual-form-builder-pro' ) ) ); ?>"><?php echo $alphaForm['title']; ?></a></h3>
									<div class="vfb-form-meta">
									<p>
										<a href="<?php echo esc_url( add_query_arg( array( 'form-filter' => $alphaForm['id'] ), admin_url( 'admin.php?page=vfb-entries' ) ) ); ?>"><?php echo $alphaForm['entries_count']; ?> Entries</a> | 
										<a href="<?php echo esc_url( add_query_arg( array( 'form_id' => $alphaForm['id'] ), admin_url( 'admin.php?page=vfb-email-design' ) ) ); ?>">Email Design</a> | 
										<a href="<?php echo esc_url( add_query_arg( array( 'form_id' => $alphaForm['id'] ), admin_url( 'admin.php?page=vfb-reports' ) ) ); ?>">Analytics</a>
										
										<?php if ( class_exists( 'VFB_Pro_Payments' ) ) : ?>
										
										| <a href="<?php echo esc_url( add_query_arg( array( 'form_id' => $alphaForm['id'] ), admin_url( 'admin.php?page=vfb-payments' ) ) ); ?>">Payments</a>
										
										<?php endif; ?>
									</p>
									<?php echo ( $alphaForm['paypal'] ) ? '<p class="paypal">' . __( 'Forwards to PayPal', 'visual-form-builder-pro' ) . '</p>' : ''; ?>
									</div>
									<div class="vfb-publishing-actions">
			                            <p>
			                            <?php if ( current_user_can( 'vfb_edit_forms' ) ) : ?>	
			                            	<a class="" href="<?php echo esc_url( add_query_arg( array( 'form' => $alphaForm['id'] ), admin_url( 'admin.php?page=visual-form-builder-pro' ) ) ); ?>">
			                            	<strong><?php _e( 'Edit Form', 'visual-form-builder-pro' ); ?></strong>
			                            	</a> |
			                            <?php endif; ?>
			                            <?php if ( current_user_can( 'vfb_delete_forms' ) ) : ?>
			                            	<a class="submitdelete menu-delete" href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=delete_form&amp;form=' . $alphaForm['id'] ), 'delete-form-' . $alphaForm['id'] ) ); ?>" class=""><?php _e( 'Delete' , 'visual-form-builder-pro'); ?></a> |
			                            <?php endif; ?>
			                            <?php if ( current_user_can( 'vfb_edit_forms' ) ) : ?>
			                            	<a href="<?php echo esc_url( add_query_arg( array( 'form' => $alphaForm['id'], 'preview' => 1 ), plugins_url( 'visual-form-builder-pro/form-preview.php' ) ) ); ?>" class="" target="_blank" title="<?php _e( 'Preview the Form', 'visual-form-builder-pro' ); ?>"><?php _e( 'Preview', 'visual-form-builder-pro' ); ?></a>
			                            <?php endif; ?>
			                            </p>                            
									</div> <!-- .vfb-publishing-actions -->
								</div>
								<div class="clear"></div>
								<?php	
								}
						?>
							</div> <!-- .vfb-form-alpha-group -->
							<hr>
						<?php endforeach; ?>
					</div> <!-- .vfb-form-alpha-list -->
				<?php
				endif;
	}
	
	/**
	 * Build field output in admin
	 * 
	 * @since 1.9
	 */
	public function field_output( $form_nav_selected_id, $field_id = NULL ) {
		global $wpdb;
		
		$field_where = ( isset( $field_id ) && !is_null( $field_id ) ) ? "AND field_id = $field_id" : '';
		$fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d $field_where ORDER BY field_sequence ASC", $form_nav_selected_id ) );
		
		$depth = 1;
		$parent = $last = 0;
		
		// Loop through each field and display
		foreach ( $fields as $field ) :		

			// If we are at the root level
			if ( !$field->field_parent && $depth > 1 ) {
				// If we've been down a level, close out the list
				while ( $depth > 1 ) {
					echo '</li></ul>';
					$depth--;
				}
				
				// Close out the root item
				echo '</li>';
			}
			// first item of <ul>, so move down a level
			elseif ( $field->field_parent && $field->field_parent == $last ) {
				echo '<ul class="parent">';
				$depth++;				
			}
			// Close up a <ul> and move up a level
			elseif ( $field->field_parent && $field->field_parent != $parent ) {
				echo '</li></ul></li>';
				$depth--;
			}
			// Same level so close list item
			elseif ( $field->field_parent && $field->field_parent == $parent )
				echo '</li>';
			
			// Store item ID and parent ID to test for nesting										
			$last = $field->field_id;
			$parent = $field->field_parent;
	?>
			<li id="form_item_<?php echo $field->field_id; ?>" class="form-item<?php echo ( in_array( $field->field_type, array( 'submit', 'secret', 'verification' ) ) ) ? ' ui-state-disabled' : ''; ?><?php echo ( !in_array( $field->field_type, array( 'fieldset', 'section', 'verification' ) ) ) ? ' ui-nestedSortable-no-nesting' : ''; ?>">
					<dl class="menu-item-bar">
						<dt class="menu-item-handle<?php echo ( $field->field_type == 'fieldset' ) ? ' fieldset' : ''; ?>">
							<span class="item-title"><?php echo stripslashes( esc_attr( $field->field_name ) ); ?><?php echo ( $field->field_required == 'yes' ) ? ' <span class="is-field-required">*</span>' : ''; ?></span>
                            <span class="item-controls">
                            	<?php echo ( 1 == $field->field_rule_setting ) ? '<span class="item-conditional-icon"></span>' : '' ?>
								<span class="item-type"><?php echo strtoupper( str_replace( '-', ' ', $field->field_type ) ); ?></span>
								<a href="#" title="<?php _e( 'Edit Field Item' , 'visual-form-builder-pro'); ?>" id="edit-<?php echo $field->field_id; ?>" class="item-edit"><?php _e( 'Edit Field Item' , 'visual-form-builder-pro'); ?></a>
							</span>
						</dt>
					</dl>
		
					<div id="form-item-settings-<?php echo $field->field_id; ?>" class="menu-item-settings field-type-<?php echo $field->field_type; ?>" style="display: none;">
						<?php if ( in_array( $field->field_type, array( 'fieldset', 'section', 'page-break', 'verification' ) ) ) : ?>
						
							<p class="description description-wide">
								<label for="edit-form-item-name-<?php echo $field->field_id; ?>"><?php echo ( in_array( $field->field_type, array( 'fieldset', 'verification' ) ) ) ? 'Legend' : 'Name'; ?>
                                	<span class="vfb-tooltip" rel="For Fieldsets, a Legend is simply the name of that group. Use general terms that describe the fields included in this Fieldset." title="About Legend">(?)</span>
                                    <br />
									<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
								</label>
                                
							</p>
							<!-- CSS Classes -->
                            <p class="description description-wide">
                                <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
                                    <?php _e( 'CSS Classes' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" rel="For each field, you can insert your own CSS class names which can be used in your own stylesheets." title="About CSS Classes">(?)</span>
                                    <br />
                                    <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
                                </label>
                            </p>
						
						<?php elseif( $field->field_type == 'instructions' ) : ?>
							<!-- Instructions -->
							<p class="description description-wide">
								<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
										<?php _e( 'Name' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Name" rel="A field's name is the most visible and direct way to describe what that field is for.">(?)</span>
                                    	<br />
										<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
								</label>
							</p>
							<!-- Description -->
							<p class="description description-wide">
								<label for="edit-form-item-description-<?php echo $field->field_id; ?>">
									<?php _e( 'Description (HTML tags allowed)', 'visual-form-builder-pro' ); ?>
                                	<span class="vfb-tooltip" title="About Instructions Description" rel="The Instructions field allows for long form explanations, typically seen at the beginning of Fieldsets or Sections. HTML tags are allowed.">(?)</span>
                                    <br />
									<textarea name="field_description-<?php echo $field->field_id; ?>" class="widefat edit-menu-item-description" cols="20" rows="3" id="edit-form-item-description-<?php echo $field->field_id; ?>"><?php echo stripslashes( $field->field_description ); ?></textarea>
								</label>
							</p>
						
                        <?php elseif( $field->field_type == 'hidden' ) : ?>
							<!-- Hidden -->
							<p class="description description-wide">
								<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
										<?php _e( 'Name' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Name" rel="A field's name is the most visible and direct way to describe what that field is for.">(?)</span>
                                    	<br />
										<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
								</label>
							</p>
                            <!-- Dynamic Variable -->
                            <p class="description description-wide">
                                <label for="edit-form-item-dynamicvar">
                                    <?php _e( 'Dynamic Variable' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" title="About Dynamic Variable" rel="A Dynamic Variable will use a pre-populated value that is determined either by the form, the user, or the post/page viewed.">(?)</span>
                                   	<br />
                                    <?php
                                    $opts_vals = array();
									// If the options field isn't empty, unserialize and build array
									if ( !empty( $field->field_options ) ) {
										if ( is_serialized( $field->field_options ) )
											$opts_vals = unserialize( $field->field_options );
									}
									?>
                                   <select name="field_options-<?php echo $field->field_id; ?>[]" class="widefat hidden-option" id="edit-form-item-dynamicvar-<?php echo $field->field_id; ?>">
                                        <option value="" <?php selected( $opts_vals[0], '' ); ?>><?php _e( 'Select a Variable or Custom to create your own' , 'visual-form-builder-pro'); ?></option>
                                        <option value="form_id" <?php selected( $opts_vals[0], 'form_id' ); ?>><?php _e( 'Form ID' , 'visual-form-builder-pro'); ?></option>
                                        <option value="form_title" <?php selected( $opts_vals[0], 'form_title' ); ?>><?php _e( 'Form Title' , 'visual-form-builder-pro'); ?></option>
                                        <option value="ip" <?php selected( $opts_vals[0], 'ip' ); ?>><?php _e( 'IP Address' , 'visual-form-builder-pro'); ?></option>
                                        <option value="uid" <?php selected( $opts_vals[0], 'uid' ); ?>><?php _e( 'Unique ID' , 'visual-form-builder-pro'); ?></option>
                                        <option value="post_id" <?php selected( $opts_vals[0], 'post_id' ); ?>><?php _e( 'Post/Page ID' , 'visual-form-builder-pro'); ?></option>
                                        <option value="post_title" <?php selected( $opts_vals[0], 'post_title' ); ?>><?php _e( 'Post/Page Title' , 'visual-form-builder-pro'); ?></option>
                                        <option value="custom" <?php selected( $opts_vals[0], 'custom' ); ?>><?php _e( 'Custom' , 'visual-form-builder-pro'); ?></option>
                                    </select>
                                </label>
                            </p>
                            <!-- Static Variable -->
                            <p class="description description-wide static-vars-<?php echo ( $opts_vals[0] == 'custom' ) ? 'active' : 'inactive'; ?>" id="static-var-<?php echo $field->field_id; ?>">
								<label for="edit-form-item-staticvar-<?php echo $field->field_id; ?>">
									<?php _e( 'Static Variable' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" title="About Static Variable" rel="A Static Variable will always use the value that you enter.">(?)</span>
                                   	<br />
									<input type="text" value="<?php echo stripslashes( esc_attr( $opts_vals[1] ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-staticvar-<?php echo $field->field_id; ?>"<?php echo ( $opts_vals[0] !== 'custom' ) ? ' disabled="disabled"' : ''; ?> />
								</label>
							</p>
                            <?php unset( $opts_vals ); ?>

						<?php else: ?>
							
							<!-- Name -->
							<p class="description description-wide">
								<label for="edit-form-item-name-<?php echo $field->field_id; ?>">
									<?php _e( 'Name' , 'visual-form-builder-pro'); ?>
                                    <span class="vfb-tooltip" title="About Name" rel="A field's name is the most visible and direct way to describe what that field is for.">(?)</span>
                                    <br />
									<input type="text" value="<?php echo stripslashes( esc_attr( $field->field_name ) ); ?>" name="field_name-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-name-<?php echo $field->field_id; ?>" maxlength="255" />
								</label>
							</p>
							<?php if ( $field->field_type == 'submit' ) : ?>
								<!-- CSS Classes -->
	                            <p class="description description-wide">
	                                <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
	                                    <?php _e( 'CSS Classes' , 'visual-form-builder-pro'); ?>
	                                    <span class="vfb-tooltip" rel="For each field, you can insert your own CSS class names which can be used in your own stylesheets." title="About CSS Classes">(?)</span>
	                                    <br />
	                                    <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" />
	                                </label>
	                            </p>
							<?php elseif ( $field->field_type !== 'submit' ) : ?>
								<!-- Description -->
								<p class="description description-wide">
									<label for="edit-form-item-description-<?php echo $field->field_id; ?>">
										<?php _e( 'Description' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Description" rel="A description is an optional piece of text that further explains the meaning of this field. Descriptions are displayed below the field. HTML tags are allowed.">(?)</span>
                                    	<br />
                                    	<textarea name="field_description-<?php echo $field->field_id; ?>" class="widefat edit-menu-item-description" cols="20" rows="3" id="edit-form-item-description-<?php echo $field->field_id; ?>"><?php echo stripslashes( $field->field_description ); ?></textarea>
									</label>
								</p>
								
								<?php
									// Display the Options input only for radio, checkbox, select, and autocomplete fields
									if ( in_array( $field->field_type, array( 'radio', 'checkbox', 'select', 'autocomplete' ) ) ) :
								?>
									<!-- Options -->
									<p class="description description-wide">
										<?php _e( 'Options' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Options" rel="This property allows you to set predefined options to be selected by the user.  Use the plus and minus buttons to add and delete options.  At least one option must exist.">(?)</span>
                                    	<br />
									<?php
										// If the options field isn't empty, unserialize and build array
										if ( !empty( $field->field_options ) ) {
											if ( is_serialized( $field->field_options ) )
												$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
										}
										// Otherwise, present some default options
										else
											$opts_vals = array( 'Option 1', 'Option 2', 'Option 3' );
										
										// Basic count to keep track of multiple options
										$count = 1;
										
										// Loop through the options
										foreach ( $opts_vals as $options ) {
									?>
									<div id="clone-<?php echo $field->field_id . '-' . $count; ?>" class="option">
											<input type="radio" value="<?php echo esc_attr( $count ); ?>" name="field_default-<?php echo $field->field_id; ?>" <?php checked( $field->field_default, $count ); ?> <?php echo ( $field->field_type == 'autocomplete' ) ? 'disabled="disabled"' : ''; ?> />
<label for="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" class="clonedOption">
											<input type="text" value="<?php echo stripslashes( esc_attr( $options ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" />
										</label>
										
										<a href="#" class="addOption" title="Add an Option">Add</a> <a href="#" class="deleteOption" title="Delete Option">Delete</a>
									</div>
									   <?php 
											$count++;
										}
										?>
									<a href="<?php echo add_query_arg( array( 'action' => 'visual_form_builder_bulk_add', 'field_id' => $field->field_id, 'width' => '640' ), admin_url( 'admin-ajax.php' ) ); ?>" class="thickbox vfb-bulking" title="Bulk Add Options"><?php _e( 'Bulk Add Options', 'visual-form-builder-pro' ); ?></a>
									</p>
								<?php
									// Unset the options for any following radio, checkboxes, or selects
									unset( $opts_vals );
									endif;
								?>
                                
                                <?php if ( in_array( $field->field_type, array( 'radio' ) ) ) : ?>
									<!-- Allow Other -->
									<p class="description description-wide">
									<?php $field_options_other = maybe_unserialize( $field->field_options_other ); ?>
										<label for="edit-form-item-options-other-<?php echo $field->field_id; ?>">
											<span class="vfb-tooltip" title="About Allow Other" rel="Select this option if you want the last option to be a text field.">(?)</span>
	                                    	
											<input type="checkbox" value="1" name="field_options_other-<?php echo $field->field_id; ?>[setting]" class="vfb-options-other" id="edit-form-item-options-other-<?php echo $field->field_id; ?>"<?php echo ( isset( $field_options_other['setting'] ) && 1 == $field_options_other['setting'] ) ? 'checked="checked"' : ''; ?> /> <?php _e( 'Allow Other', 'visual-form-builder-pro' ); ?>
										</label>
										<?php
											$other_display 	= ( isset( $field_options_other['setting'] ) && 1 == $field_options_other['setting'] ) ? 'show' : 'hide';
											$other_value	= ( isset( $field_options_other['other'] ) && !empty( $field_options_other['other'] ) ) ? $field_options_other['other'] : 'Other';
										?>
												<div id="options-other-<?php echo $field->field_id; ?>" class="options-other-<?php echo $other_display; ?>">
												<input type="radio" value="<?php echo esc_attr( $count ); ?>" name="field_default-<?php echo $field->field_id; ?>" <?php checked( $field->field_default, $count ); ?> />
	<label for="edit-form-item-other-options-<?php echo $field->field_id; ?>">
												<input type="text" value="<?php echo stripslashes( esc_attr( $other_value ) ); ?>" name="field_options_other-<?php echo $field->field_id; ?>[other]" class="widefat" id="edit-form-item-other-options-<?php echo $field->field_id; ?>" />
											</label>
										</div>
									</p>
								<?php endif; ?>
								
                                <?php if ( in_array( $field->field_type, array( 'textarea' ) ) ) : ?>
                                	<!-- Textarea word count -->
									<p class="description description-thin">
										<?php
											$opts_vals = maybe_unserialize( $field->field_options );
											$min = ( isset( $opts_vals['min'] ) ) ? $opts_vals['min'] : '';
											$max = ( isset( $opts_vals['max'] ) ) ? $opts_vals['max'] : '';
										?>
										<label for="edit-form-item-textarea-min-<?php echo $field->field_id; ?>">
											<?php _e( 'Minimum Words', 'visual-form-builder-pro' ); ?>
											<span class="vfb-tooltip" title="About Minimum Word Count" rel="Set a minimum number of words allowed in this field. For an unlimited number, leave blank or set to zero.">(?)</span>
											<br />
											<input type="text" value="<?php echo esc_attr( $min ); ?>" name="field_options-<?php echo $field->field_id; ?>[min]" class="widefat" id="edit-form-item-textarea-min-<?php echo $field->field_id; ?>" />
										</label>
                                    </p>
                                    <p class="description description-thin">
                                    	<label for="edit-form-item-textarea-max-<?php echo $field->field_id; ?>">
                                    		<?php _e( 'Maximum Words', 'visual-form-builder-pro' ); ?>
                                    		<span class="vfb-tooltip" title="About Maximum Word Count" rel="Set a maximum number of words allowed in this field. For an unlimited number, leave blank or set to zero.">(?)</span>
                                    		<br />
											<input type="text" value="<?php echo esc_attr( $max ); ?>" name="field_options-<?php echo $field->field_id; ?>[max]" class="widefat" id="edit-form-item-textarea-max-<?php echo $field->field_id; ?>" />
										</label>
                                    </p>
                                <?php
									// Unset the options for any following radio, checkboxes, or selects
									unset( $opts_vals );
									endif;
								?>
                                
                                <?php if ( in_array( $field->field_type, array( 'min', 'max', 'range' ) ) ) : ?>
                                	<!-- Min, Max, and Range -->
									<p class="description description-wide">
                                        <?php
										if ( 'min' == $field->field_type )
											_e( 'Minimum Value' , 'visual-form-builder-pro');
										elseif ( 'max' == $field->field_type )
											_e( 'Maximum Value' , 'visual-form-builder-pro');

										// If the options field isn't empty, unserialize and build array
										if ( !empty( $field->field_options ) ) {
											if ( is_serialized( $field->field_options ) )
												$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
										}
										else
											$opts_vals = ( in_array( $field->field_type, array( 'min', 'max' ) ) ) ? array( '10' ) : array( '1', '10' );

										$ranged = false;
										// Loop through the options
										foreach ( $opts_vals as $options ) {
											if ( 'range' == $field->field_type ) {
												if ( !$ranged )
													_e( 'Minimum Value' , 'visual-form-builder-pro');
												else
													_e( 'Maximum Value' , 'visual-form-builder-pro');
												
												$ranged = true;
											}
									?>
                                    	<span class="vfb-tooltip" title="About Minimum/Maxium Value" rel="Set a minimum and/or maximum value users must enter in order to successfully complete the field.">(?)</span>
                                    	<br />
										<label for="edit-form-item-options-<?php echo $field->field_id; ?>">
											<input type="text" value="<?php echo stripslashes( esc_attr( $options ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id . "-$count"; ?>" />
										</label>
									   <?php 
										}
										?>
                                    </p>
                                <?php
									// Unset the options for any following radio, checkboxes, or selects
									unset( $opts_vals );
									endif;
								?>
                                
                                <?php if ( in_array( $field->field_type, array( 'file-upload' ) ) ) : ?>
                                	<!-- File Upload Accepts -->
									<p class="description description-wide">
                                    	<?php _e( 'Accepted File Extensions' , 'visual-form-builder-pro'); ?>
                                        <?php
										$opts_vals = array( '' );
										
										// If the options field isn't empty, unserialize and build array
										if ( !empty( $field->field_options ) ) {
											if ( is_serialized( $field->field_options ) )
												$opts_vals = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : unserialize( $field->field_options );
										}
										
										// Loop through the options
										foreach ( $opts_vals as $options ) {
									?>
                                    	<span class="vfb-tooltip" title="About Accepted File Extensions" rel="Control the types of files allowed.  Enter file extentsions separated by commas.">(?)</span>
                                    	<br />
										<label for="edit-form-item-options-<?php echo $field->field_id; ?>">
											<input type="text" value="<?php echo stripslashes( esc_attr( $options ) ); ?>" name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id; ?>" />
										</label>
                                    </p>
                                <?php
										}
									// Unset the options for any following radio, checkboxes, or selects
									unset( $opts_vals );
									endif;
								?>
								
								<?php if ( in_array( $field->field_type, array( 'name' ) ) ) : ?>
                                	<!-- Name -->
									<p class="description description-wide">
										<label for="edit-form-item-options">
                                    		<?php _e( 'Name Format' , 'visual-form-builder-pro'); ?>
                                    		<?php
                                    			$opts_vals = maybe_unserialize( $field->field_options );
                                    		?>
                                    		<span class="vfb-tooltip" title="About Name Format" rel="Choose from either a simple name format with only a First and Last Name or a more complex format that adds a Title and Suffix.">(?)</span>
                                    	<br />
                                    	<select name="field_options-<?php echo $field->field_id; ?>[]" class="widefat" id="edit-form-item-options-<?php echo $field->field_id; ?>">
                                    		<option value="normal" <?php selected( $opts_vals[0], 'normal' ); ?>><?php _e( 'Normal' , 'visual-form-builder-pro'); ?></option>
											<option value="extra" <?php selected( $opts_vals[0], 'extra' ); ?>><?php _e( 'Extra' , 'visual-form-builder-pro'); ?></option>
                                    	</select>
										</label>
									</p>
								<?php endif; ?>
								
								<!-- Validation -->
								<p class="description description-thin">
									<label for="edit-form-item-validation">
										<?php _e( 'Validation' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Validation" rel="Ensures user-entered data is formatted properly. For more information on Validation, refer to the Help tab at the top of this page.">(?)</span>
                                    	<br />
									   <select name="field_validation-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-validation-<?php echo $field->field_id; ?>"<?php echo ( in_array( $field->field_type, array( 'radio', 'select', 'checkbox', 'address', 'date', 'textarea', 'html', 'file-upload', 'autocomplete', 'color-picker', 'secret' ) ) ) ? ' disabled="disabled"' : ''; ?>>
											<?php if ( $field->field_type == 'time' ) : ?>
											<option value="time-12" <?php selected( $field->field_validation, 'time-12' ); ?>><?php _e( '12 Hour Format' , 'visual-form-builder-pro'); ?></option>
											<option value="time-24" <?php selected( $field->field_validation, 'time-24' ); ?>><?php _e( '24 Hour Format' , 'visual-form-builder-pro'); ?></option>
											<?php elseif ( $field->field_type == 'ip-address' ) : ?>
                                            <option value="ipv4" <?php selected( $field->field_validation, 'ipv4' ); ?>><?php _e( 'IPv4' , 'visual-form-builder-pro'); ?></option>
                                            <option value="ipv6" <?php selected( $field->field_validation, 'ipv6' ); ?>><?php _e( 'IPv6' , 'visual-form-builder-pro'); ?></option>
											<?php elseif ( in_array( $field->field_type, array( 'min', 'max', 'range' ) ) ) : ?>
                                            <option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php _e( 'Number' , 'visual-form-builder-pro'); ?></option>
											<option value="digits" <?php selected( $field->field_validation, 'digits' ); ?>><?php _e( 'Digits' , 'visual-form-builder-pro'); ?></option>
											<?php else : ?>
											<option value="" <?php selected( $field->field_validation, '' ); ?>><?php _e( 'None' , 'visual-form-builder-pro'); ?></option>
											<option value="email" <?php selected( $field->field_validation, 'email' ); ?>><?php _e( 'Email' , 'visual-form-builder-pro'); ?></option>
											<option value="url" <?php selected( $field->field_validation, 'url' ); ?>><?php _e( 'URL' , 'visual-form-builder-pro'); ?></option>
											<option value="date" <?php selected( $field->field_validation, 'date' ); ?>><?php _e( 'Date' , 'visual-form-builder-pro'); ?></option>
											<option value="number" <?php selected( $field->field_validation, 'number' ); ?>><?php _e( 'Number' , 'visual-form-builder-pro'); ?></option>
											<option value="digits" <?php selected( $field->field_validation, 'digits' ); ?>><?php _e( 'Digits' , 'visual-form-builder-pro'); ?></option>
											<option value="phone" <?php selected( $field->field_validation, 'phone' ); ?>><?php _e( 'Phone' , 'visual-form-builder-pro'); ?></option>
											<?php endif; ?>
										</select>
									</label>
								</p>
								
								<!-- Required -->
								<p class="field-link-target description description-thin">
									<label for="edit-form-item-required">
										<?php _e( 'Required' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Required" rel="Requires the field to be completed before the form is submitted. By default, all fields are set to No.">(?)</span>
                                    	<br />
										<select name="field_required-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-required-<?php echo $field->field_id; ?>">
											<option value="no" <?php selected( $field->field_required, 'no' ); ?>><?php _e( 'No' , 'visual-form-builder-pro'); ?></option>
											<option value="yes" <?php selected( $field->field_required, 'yes' ); ?>><?php _e( 'Yes' , 'visual-form-builder-pro'); ?></option>
										</select>
									</label>
								</p>
							   
								<?php if ( !in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ) ) ) : ?>
									<!-- Size -->
									<p class="description description-thin">
										<label for="edit-form-item-size">
											<?php _e( 'Size' , 'visual-form-builder-pro'); ?>
                      <span class="vfb-tooltip" title="About Size" rel="Control the size of the field.  By default, all fields are set to 100%.">(?)</span>
                                    		<br />
											<select name="field_size-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-size-<?php echo $field->field_id; ?>">
												<option value="span12" <?php selected( $field->field_size, 'span12' ); ?>><?php _e( '100%' , 'visual-form-builder-pro'); ?></option>
												<option value="span3" <?php selected( $field->field_size, 'span3' ); ?>><?php _e( '25%' , 'visual-form-builder-pro'); ?></option>
												<option value="span4" <?php selected( $field->field_size, 'span4' ); ?>><?php _e( '33%' , 'visual-form-builder-pro'); ?></option>
												<option value="span6" <?php selected( $field->field_size, 'span6' ); ?>><?php _e( '50%' , 'visual-form-builder-pro'); ?></option>
												<option value="span8" <?php selected( $field->field_size, 'span8' ); ?>><?php _e( '66%' , 'visual-form-builder-pro'); ?></option>
												<option value="span9" <?php selected( $field->field_size, 'span9' ); ?>><?php _e( '75%' , 'visual-form-builder-pro'); ?></option>
												
                        
												<?php apply_filters( 'vfb_admin_field_size', $field->field_size ); ?>
											</select>
										</label>
									</p>
                                <?php elseif ( in_array( $field->field_type, array( 'radio', 'checkbox', 'time' ) ) ) : ?>
									<!-- Options Layout -->
									<p class="description description-thin">
										<label for="edit-form-item-size">
											<?php _e( 'Options Layout' , 'visual-form-builder-pro'); ?>
                      <span class="vfb-tooltip" title="About Options Layout" rel="Control the layout of radio buttons or checkboxes.  By default, options are arranged in One Column.">(?)</span>
                                    		<br />
											<select name="field_size-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-size-<?php echo $field->field_id; ?>"<?php echo ( $field->field_type == 'time' ) ? ' disabled="disabled"' : ''; ?>>
												<option value="" <?php selected( $field->field_size, '' ); ?>><?php _e( 'One Column' , 'visual-form-builder-pro'); ?></option>
                        <option value="two-column" <?php selected( $field->field_size, 'two-column' ); ?>><?php _e( 'Two Columns' , 'visual-form-builder-pro'); ?></option>
												<option value="three-column" <?php selected( $field->field_size, 'three-column' ); ?>><?php _e( 'Three Columns' , 'visual-form-builder-pro'); ?></option>
                        <option value="auto-column" <?php selected( $field->field_size, 'auto-column' ); ?>><?php _e( 'Auto Width' , 'visual-form-builder-pro'); ?></option>
											</select>
										</label>
									</p>
                                
								<?php endif; ?>
									<!-- Field Layout -->
									<p class="description description-thin">
										<label for="edit-form-item-layout">
											<?php _e( 'Field Layout' , 'visual-form-builder-pro'); ?>
                                            <span class="vfb-tooltip" title="About Field Layout" rel="Used to create advanced layouts. Align fields side by side in various configurations.">(?)</span>
	                                    <br />
											<select name="field_layout-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-layout-<?php echo $field->field_id; ?>">
                                            	
												<option value="" <?php selected( $field->field_layout, '' ); ?>><?php _e( 'Default' , 'visual-form-builder-pro'); ?></option>
                                                <optgroup label="------------">
                                                <option value="left-half" <?php selected( $field->field_layout, 'left-half' ); ?>><?php _e( 'Left Half' , 'visual-form-builder-pro'); ?></option>
                                                <option value="right-half" <?php selected( $field->field_layout, 'right-half' ); ?>><?php _e( 'Right Half' , 'visual-form-builder-pro'); ?></option>
                                                </optgroup>
                                                <optgroup label="------------">
												<option value="left-third" <?php selected( $field->field_layout, 'left-third' ); ?>><?php _e( 'Left Third' , 'visual-form-builder-pro'); ?></option>
                                                <option value="middle-third" <?php selected( $field->field_layout, 'middle-third' ); ?>><?php _e( 'Middle Third' , 'visual-form-builder-pro'); ?></option>
                                                <option value="right-third" <?php selected( $field->field_layout, 'right-third' ); ?>><?php _e( 'Right Third' , 'visual-form-builder-pro'); ?></option>
                                                </optgroup>
                                                <optgroup label="------------">
                                                <option value="left-two-thirds" <?php selected( $field->field_layout, 'left-two-thirds' ); ?>><?php _e( 'Left Two Thirds' , 'visual-form-builder-pro'); ?></option>
                                                <option value="right-two-thirds" <?php selected( $field->field_layout, 'right-two-thirds' ); ?>><?php _e( 'Right Two Thirds' , 'visual-form-builder-pro'); ?></option>
                                                </optgroup>
                                                <?php apply_filters( 'vfb_admin_field_layout', $field->field_layout ); ?>
											</select>
										</label>
									</p>
								<?php if ( !in_array( $field->field_type, array( 'radio', 'select', 'checkbox', 'time', 'address' ) ) ) : ?>
								<!-- Default Value -->
								<p class="description description-wide">
                                    <label for="edit-form-item-default-<?php echo $field->field_id; ?>">
                                        <?php _e( 'Default Value' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Default Value" rel="Set a default value that will be inserted automatically.">(?)</span>
                                    	<br />
                                        <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_default ) ); ?>" name="field_default-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-default-<?php echo $field->field_id; ?>" maxlength="255" />
                                    </label>
								</p>
								<?php elseif( in_array( $field->field_type, array( 'address' ) ) ) : ?>
								<!-- Default Country -->
								<p class="description description-wide">
                                    <label for="edit-form-item-default-<?php echo $field->field_id; ?>">
                                        <?php _e( 'Default Country' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About Default Country" rel="Select the country you would like to be displayed by default.">(?)</span>
                                    	<br />
                                        <select name="field_default-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-default-<?php echo $field->field_id; ?>">
                                        <?php
                                        foreach ( $this->countries as $country ) {
											echo '<option value="' . esc_attr( $country ) . '" ' . selected( $field->field_default, $country, 0 ) . '>' . $country . '</option>';
										}
										?>
										</select>
                                    </label>
								</p>
								<?php endif; ?>
								<!-- CSS Classes -->
								<p class="description description-wide">
                                    <label for="edit-form-item-css-<?php echo $field->field_id; ?>">
                                        <?php _e( 'CSS Classes' , 'visual-form-builder-pro'); ?>
                                        <span class="vfb-tooltip" title="About CSS Classes" rel="For each field, you can insert your own CSS class names which can be used in your own stylesheets.">(?)</span>
                                    	<br />
                                        <input type="text" value="<?php echo stripslashes( esc_attr( $field->field_css ) ); ?>" name="field_css-<?php echo $field->field_id; ?>" class="widefat" id="edit-form-item-css-<?php echo $field->field_id; ?>" maxlength="255" />
                                    </label>
								</p>
							<?php endif; ?>
						<?php endif; ?>
						
						<?php if ( !in_array( $field->field_type, array( 'fieldset', 'section', 'page-break', 'verification' ) ) ) : ?>
							<!-- Merge Tag -->
							<div class="vfb-item-merge-tag description description-wide">
								<p id="edit-form-item-merge-tag-<?php echo $field->field_id; ?>" class="vfb-merge-tag">Merge Tag: <code>{vfb-<?php echo $field->field_id; ?>}</code></p>
							</div>
						<?php endif; ?>
						
						<?php if ( !in_array( $field->field_type, array( 'verification', 'secret', 'submit' ) ) ) : ?>
							<div class="vfb-item-actions">
								<!-- Delete link -->
								<a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=delete_field&amp;form=' . $form_nav_selected_id . '&amp;field=' . $field->field_id ), 'delete-field-' . $form_nav_selected_id ) ); ?>" class="vfb-button vfb-delete item-delete submitdelete deletion"><?php _e( 'Delete Field' , 'visual-form-builder-pro'); ?><span class="button-icon delete"></span></a>
								
								<?php if ( !in_array( $field->field_type, array( 'fieldset', 'section' ) ) ) { ?>
								<!-- Duplicate Field link -->
								<a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=duplicate_field&amp;form=' . $form_nav_selected_id . '&amp;field=' . $field->field_id ), 'duplicate-field-' . $form_nav_selected_id ) ); ?>" class="vfb-button vfb-duplicate vfb-duplicate-field" title="Duplicate Field"><?php _e( 'Duplicate Field' , 'visual-form-builder-pro'); ?><span class="button-icon plus"></span></a>
								<?php } ?>
								
								<!-- Conditional Logic link -->
								<a href="<?php echo add_query_arg( array( 'action' => 'visual_form_builder_conditional_fields', 'field_id' => $field->field_id, 'form_id' => $form_nav_selected_id, 'width' => '640' ), admin_url( 'admin-ajax.php' ) ); ?>" class="vfb-button thickbox vfb-conditional-fields" title="Add Conditions"><?php _e( 'Conditional Logic' , 'visual-form-builder-pro'); ?><span class="button-icon conditional"></span></a>
							</div>
						<?php endif; ?>
						
					<input type="hidden" name="field_id[<?php echo $field->field_id; ?>]" value="<?php echo $field->field_id; ?>" />
					</div>
	<?php
		endforeach;
		
		// This assures all of the <ul> and <li> are closed
		if ( $depth > 1 ) {
			while( $depth > 1 ) {
				echo '</li></ul>';
				$depth--;
			}
		}
		
		// Close out last item
		echo '</li>';
	}
	
	/**
	 * Add a menu to the WP admin toolbar
	 * 
	 * @since 1.7
	 * @param object $wp_admin_bar
	 */
	public function admin_toolbar_menu( $wp_admin_bar ) {
		// Only display VFB toolbar if on a page with the shortcode
		if ( !is_admin() && current_user_can( 'vfb_edit_forms' ) ) {
			$post_to_check = get_post( get_the_ID() );
			
			// Finds content with the vfb shortcode
			if ( stripos( $post_to_check->post_content, '[vfb' ) !== false ) { 
				preg_match_all( '/id=(\d+)/', $post_to_check->post_content, $matches );
				
				// If more than one form, display a new toolbar item with dropdown
				if ( count( $matches[1] ) > 1 ) {
					global $wpdb;
					
					$wp_admin_bar->add_node( array(
						'id' 		=> 'vfb_admin_toolbar_edit_main',
						'title'		=> 'Edit Forms',
						'parent'	=> false,
						'href'		=> admin_url( 'admin.php?page=visual-form-builder-pro' )
						)
					);
					
					// Loop through the forms
					foreach ( $matches[1] as $form_id ) {
						$name = $wpdb->get_var( $wpdb->prepare( "SELECT form_title FROM $this->form_table_name WHERE form_id = %d", $form_id ) );
						$wp_admin_bar->add_node( array(
							'id' 		=> 'vfb_admin_toolbar_edit_' . $form_id,
							'title'		=> 'Edit ' . stripslashes( $name ),
							'parent'	=> 'vfb_admin_toolbar_edit_main',
							'href'		=> admin_url( 'admin.php?page=visual-form-builder-pro&amp;form=' . $form_id )
							)
						);
					}
				}
				else {
					// A new toolbar item
					$wp_admin_bar->add_node( array(
						'id' 		=> 'vfb_admin_toolbar_edit_main',
						'title'		=> 'Edit Form',
						'parent'	=> false,
						'href'		=> admin_url( 'admin.php?page=visual-form-builder-pro&amp;form=' . $matches[1][0] )
						)
					);
					// An item added to the main VFB Pro menu
					$wp_admin_bar->add_node( array(
						'id' 		=> 'vfb_admin_toolbar_edit',
						'title'		=> 'Edit Form',
						'parent'	=> 'vfb_admin_toolbar',
						'href'		=> admin_url( 'admin.php?page=visual-form-builder-pro&amp;form=' . $matches[1][0] )
						)
					);
				}				
			}
		}
		
		// Entire menu will be hidden if user does not have vfb_edit_forms cap
		if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'visual-form-builder-pro' ) ) && isset( $_REQUEST['form'] ) && current_user_can( 'vfb_edit_forms' ) ) {
			$wp_admin_bar->add_node( array(
				'id' 		=> 'vfb_admin_toolbar_preview_form',
				'title'		=> 'Preview Form',
				'parent'	=> false,
				'href'		=> esc_url( add_query_arg( array( 'form' => $_REQUEST['form'], 'preview' => 1 ), plugins_url( 'visual-form-builder-pro/form-preview.php' ) ) ),
				'meta'		=> array( 'target' => '_blank' )
				)
			);
		}
		
		// Entire menu will be hidden if user does not have vfb_edit_forms cap
		if ( current_user_can( 'vfb_edit_forms' ) ) {
			$wp_admin_bar->add_node( array(
				'id' 		=> 'vfb_admin_toolbar',
				'title'		=> 'VFB Pro',
				'parent'	=> false,
				'href'		=> admin_url( 'admin.php?page=visual-form-builder-pro' )
				)
			);
		}
		
		// Add New Form
		if ( current_user_can( 'vfb_create_forms' ) ) {
			$wp_admin_bar->add_node( array(
				'id' 		=> 'vfb_admin_toolbar_add',
				'title'		=> __( 'Add New Form', 'visual-form-builder-pro' ),
				'parent'	=> 'vfb_admin_toolbar',
				'href'		=> admin_url( 'admin.php?page=vfb-add-new' )
				)
			);
		}
		
		// Entries
		if ( current_user_can( 'vfb_view_entries' ) ) {
			$wp_admin_bar->add_node( array(
				'id' 		=> 'vfb_admin_toolbar_entries',
				'title'		=> __( 'Entries', 'visual-form-builder-pro' ),
				'parent'	=> 'vfb_admin_toolbar',
				'href'		=> admin_url( 'admin.php?page=vfb-entries' )
				)
			);
		}
		
		// Email Design
		if ( current_user_can( 'vfb_edit_email_design' ) ) {
			$wp_admin_bar->add_node( array(
				'id' 		=> 'vfb_admin_toolbar_email',
				'title'		=> __( 'Email Design', 'visual-form-builder-pro' ),
				'parent'	=> 'vfb_admin_toolbar',
				'href'		=> admin_url( 'admin.php?page=vfb-email-design' )
				)
			);
		}
		
		// Analytics
		if ( current_user_can( 'vfb_view_analytics' ) ) {
			$wp_admin_bar->add_node( array(
				'id' 		=> 'vfb_admin_toolbar_analytics',
				'title'		=> __( 'Analytics', 'visual-form-builder-pro' ),
				'parent'	=> 'vfb_admin_toolbar',
				'href'		=> admin_url( 'admin.php?page=vfb-reports' )
				)
			);
		}
		
		// Import
		if ( current_user_can( 'vfb_import_forms' ) ) {
			$wp_admin_bar->add_node( array(
				'id' 		=> 'vfb_admin_toolbar_import',
				'title'		=> __( 'Import', 'visual-form-builder-pro' ),
				'parent'	=> 'vfb_admin_toolbar',
				'href'		=> admin_url( 'admin.php?page=vfb-import' )
				)
			);
		}
		
		// Export
		if ( current_user_can( 'vfb_export_forms' ) ) {
			$wp_admin_bar->add_node( array(
				'id' 		=> 'vfb_admin_toolbar_export',
				'title'		=> __( 'Export', 'visual-form-builder-pro' ),
				'parent'	=> 'vfb_admin_toolbar',
				'href'		=> admin_url( 'admin.php?page=vfb-export' )
				)
			);
		}
	}
	
	/**
	 * Display admin notices
	 * 
	 * @since 1.0
	 */
	public function admin_notices(){
		if ( isset( $_REQUEST['action'] ) ) {
			switch( $_REQUEST['action'] ) {
				case 'create_form' :
					echo '<div id="message" class="updated"><p>' . __( 'The form has been successfully created.' , 'visual-form-builder-pro') . '</p></div>';
				break;
				case 'update_form' :
					echo '<div id="message" class="updated"><p>' . sprintf( __( 'The %s form has been updated. <a href="%s" target="_blank">View the Preview</a> to see how it looks.' , 'visual-form-builder-pro'), '<strong>' . $_REQUEST['form_title'] . '</strong>', add_query_arg( array( 'form' => $_REQUEST['form_id'], 'preview' => 1 ), plugins_url( 'visual-form-builder-pro/form-preview.php' ) ) ) . '</p></div>';
				break;
				case 'deleted' :
					echo '<div id="message" class="updated"><p>' . __( 'The form has been successfully deleted.' , 'visual-form-builder-pro') . '</p></div>';
				break;
				case 'copy_form' :
					echo '<div id="message" class="updated"><p>' . __( 'The form has been successfully duplicated.' , 'visual-form-builder-pro') . '</p></div>';
				break;
				case 'ignore_notice' :
					update_option( 'vfb_ignore_notice', 1 );
				break;
				case 'upgrade' :
					echo '<div id="message" class="updated"><p>' . __( 'You have successfully migrated to Visual Form Builder Pro!' , 'visual-form-builder-pro') . '</p></div>';
				break;
				case 'email_design' :
					echo '<div id="message" class="updated"><p>' . sprintf( __( 'Email design has been updated. <a href="%s" target="_blank">View the Preview</a> to see how it looks.' , 'visual-form-builder-pro'), add_query_arg( 'form', $_REQUEST['form_id'], plugins_url( 'visual-form-builder-pro/email-preview.php' ) ) ) . '</p></div>';
				break;
				case 'update_entry' :
					echo '<div id="message" class="updated"><p>' . __( 'Entry has been successfully updated.' , 'visual-form-builder-pro') . '</p></div>';
				break;
			}
			
		}
		
		// If the free version of VFB is detected and the user is an admin, display the notice
		if ( get_option( 'vfb_db_version' ) && current_user_can( 'install_plugins' ) ) {
			// If they have upgraded, don't display
			if ( ! get_option( 'vfb_db_upgrade' ) ) {
				// If they've dismissed the notice, don't display
				if ( ! get_option( 'vfb_ignore_notice' ) ) {
					echo '<div class="updated"><p>';
					echo sprintf( __( 'A version of Visual Form Builder has been detected. To copy your forms and data to Visual Form Builder Pro, <a href="%1$s">click here</a>.<br><br><strong>Note</strong>: It is recommended that you perform this action <em>before</em> you begin adding forms. Migrating <em>after</em> you have added forms to Visual Form Builder Pro will delete those forms.', 'visual-form-builder-pro' ), 'admin.php?page=visual-form-builder-pro&action=upgrade' );
					echo sprintf( __( '<a style="float:right;" href="%1$s">Dismiss</a>' , 'visual-form-builder-pro' ), '?page=visual-form-builder-pro&action=ignore_notice' );
					echo '</p></div>';
				}
			}
			
			// If the free version is active and user has upgraded or dismissed notice
			if ( is_plugin_active( 'visual-form-builder/visual-form-builder.php' ) ) {
				if ( get_option( 'vfb_db_upgrade' ) || get_option( 'vfb_ignore_notice' ) )
					echo '<div id="message" class="error"><p>' . __( 'The free version of Visual Form Builder is still active. In order for Visual Form Builder Pro to function and render correctly, you must deactivate the free version.' , 'visual-form-builder-pro') . '</p></div>';
			}
		}
		
	}
	
	/**
	 * Add options page to Settings menu
	 * 
	 * 
	 * @since 1.0
	 * @uses add_menu_page() Creates a menu item in the top level menu.
	 * @uses add_submenu_page() Creates a submenu item under the parent menu.
	 */
	public function add_admin() {
		add_menu_page( __( 'Visual Form Builder Pro', 'visual-form-builder-pro' ), __( 'Visual Form Builder Pro', 'visual-form-builder-pro' ), 'vfb_edit_forms', 'visual-form-builder-pro', array( &$this, 'admin' ), plugins_url( 'visual-form-builder-pro/images/vfb_icon.png' ) );
		
		add_submenu_page( 'visual-form-builder-pro', __( 'Visual Form Builder Pro', 'visual-form-builder-pro' ), __( 'All Forms', 'visual-form-builder-pro' ), 'vfb_edit_forms', 'visual-form-builder-pro', array( &$this, 'admin' ) );
		
		add_submenu_page( 'visual-form-builder-pro', __( 'Add New Form', 'visual-form-builder-pro' ), __( 'Add New', 'visual-form-builder-pro' ), 'vfb_create_forms', 'vfb-add-new', array( &$this, 'admin' ) );
		add_submenu_page( 'visual-form-builder-pro', __( 'Entries', 'visual-form-builder-pro' ), __( 'Entries', 'visual-form-builder-pro' ), 'vfb_view_entries', 'vfb-entries', array( &$this, 'admin' ) );
		add_submenu_page( 'visual-form-builder-pro', __( 'Email Design', 'visual-form-builder-pro' ), __( 'Email Design', 'visual-form-builder-pro' ), 'vfb_edit_email_design', 'vfb-email-design', array( &$this, 'admin' ) );
		add_submenu_page( 'visual-form-builder-pro', __( 'Analytics', 'visual-form-builder-pro' ), __( 'Analytics', 'visual-form-builder-pro' ), 'vfb_view_analytics', 'vfb-reports', array( &$this, 'admin' ) );
		add_submenu_page( 'visual-form-builder-pro', __( 'Import', 'visual-form-builder-pro' ), __( 'Import', 'visual-form-builder-pro' ), 'vfb_import_forms', 'vfb-import', array( &$this, 'admin' ) );
		add_submenu_page( 'visual-form-builder-pro', __( 'Export', 'visual-form-builder-pro' ), __( 'Export', 'visual-form-builder-pro' ), 'vfb_export_forms', 'vfb-export', array( &$this, 'admin' ) );	
	}
	
	/**
	 * Builds the options settings page
	 * 
	 * @since 1.0
	 */
	public function admin() {
		global $wpdb, $current_user, $entries_list, $entries_detail, $export, $import;
		
		get_currentuserinfo();
		
		// Save current user ID
		$user_id = $current_user->ID;
				
		// Get the Form Order type settings, if any
		$user_form_order_type = get_user_meta( $user_id, 'vfb-form-order-type', true );
		
		$form_nav_selected_id = ( isset( $_REQUEST['form'] ) ) ? $_REQUEST['form'] : '0';
		
		// Page titles
		$pages = array(
    		'visual-form-builder-pro'	=> __( 'Visual Form Builder Pro', 'visual-form-builder-pro' ),
    		'vfb-add-new'				=> __( 'Add New Form', 'visual-form-builder-pro' ),
    		'vfb-entries'         		=> __( 'Entries', 'visual-form-builder-pro' ),
    		'vfb-email-design'    		=> __( 'Email Design', 'visual-form-builder-pro' ),
    		'vfb-reports'         		=> __( 'Analytics', 'visual-form-builder-pro' ),
    		'vfb-import'          		=> __( 'Import', 'visual-form-builder-pro' ),
    		'vfb-export'          		=> __( 'Export', 'visual-form-builder-pro' ),
    	);
	?>
	<div class="wrap">
		<?php screen_icon( 'options-general' ); ?>
		<h2>
		<?php
			// Output the page titles
			echo ( isset( $_REQUEST['page'] ) && array_key_exists( $_REQUEST['page'], $pages ) ) ? esc_html( $pages[ $_REQUEST['page' ] ] ) : '';
			
			// If searched, output the query
			echo ( isset( $_REQUEST['s'] ) && !empty( $_REQUEST['s'] ) && in_array( $_REQUEST['page'], array( 'vfb-entries' ) ) ) ? '<span class="subtitle">' . sprintf( __( 'Search results for "%s"' , 'visual-form-builder-pro'), $_REQUEST['s'] ) : '';
		?>
		</h2>        
        	<?php
			// Display the Entries
			if ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'vfb-entries' ) ) && current_user_can( 'vfb_view_entries' ) ) : 
				
				if ( isset( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'view', 'edit', 'update_entry' ) ) ) :
					$entries_detail->entries_detail();
				else :
					$entries_list->entries_errors();
					$entries_list->views();
					$entries_list->prepare_items();
			?>
            	<form id="entries-filter" method="post" action="">             
                <?php
                	$entries_list->search_box( 'search', 'search_id' );
                	$entries_list->display();
                ?>
                </form>
            <?php
				endif;
			
			elseif ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'vfb-email-design' ) )  && current_user_can( 'vfb_edit_email_design' ) ) : 
				$design = new VisualFormBuilder_Pro_Designer();
				$design->design_options();
			elseif ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'vfb-reports' ) ) && current_user_can( 'vfb_view_analytics' ) ) : 
				$analytics = new VisualFormBuilder_Pro_Analytics();
				$analytics->display();
			elseif ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'vfb-export' ) ) && current_user_can( 'vfb_export_forms' ) ) : 
				$export->display();
			elseif ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'vfb-import' ) ) && current_user_can( 'vfb_import_forms' ) ) : 
				$import->display();				
			elseif ( isset( $_REQUEST['page'] ) && in_array( $_REQUEST['page'], array( 'vfb-add-new' ) ) && current_user_can( 'vfb_create_forms' ) ) :
					include_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-new-form.php' );
			elseif ( current_user_can( 'vfb_edit_forms' ) ) :
				if ( empty( $form_nav_selected_id ) ) :
				?>
				<div id="vfb-form-list">
					<div id="vfb-sidebar">
						<div id="new-form" class="vfb-box">
	                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=vfb-add-new' ) ); ?>">
	                        	<img src="<?php echo plugins_url( 'visual-form-builder-pro/images/plus-sign.png' ); ?>" width="50" height="50" />
	                        	<h3><?php _e( 'New Form', 'visual-form-builder-pro' ); ?></h3>
	                        </a>
						</div> <!-- #new-form -->
						<ul id="vfb-form-order-type">
							<li><a href="#order" title="<?php _e( 'Custom Order', 'visual-form-builder-pro' ); ?>" class="vfb-order-type order<?php echo ( in_array( $user_form_order_type, array( 'order', '' ) ) ) ? ' on' : ''; ?>">title="<?php _e( 'Custom Order', 'visual-form-builder-pro' ); ?>"</a></li>
							<li><a href="#list" title="<?php _e( 'Alphabetical List', 'visual-form-builder-pro' ); ?>" class="vfb-order-type list<?php echo ( in_array( $user_form_order_type, array( 'list' ) ) ) ? ' on' : ''; ?>">title="<?php _e( 'Custom Order', 'visual-form-builder-pro' ); ?>"</a></li>
						</ul>
						<div class="clear"></div>
					</div> <!-- #vfb-sidebar -->
				<div id="vfb-main" class="vfb-order-type-<?php echo ( in_array( $user_form_order_type, array( 'order', '' ) ) ) ? 'order' : 'list'; ?>">
				<?php
					$this->all_forms();
				?>
				</div> <!-- #vfb-main -->
				</div> <!-- #vfb-form-list -->
				<?php
			?>
				
				<?php
				elseif ( !empty( $form_nav_selected_id ) && $form_nav_selected_id !== '0' ) :
					include_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/admin-form-creator.php' );
				endif;
				?>       
			
	</div>
	<?php
			endif;
	}
	
	/**
	 * Handle confirmation when form is submitted
	 * 
	 * @since 1.3
	 */
	function confirmation(){
		global $wpdb;
		
		$form_id = ( isset( $_REQUEST['form_id'] ) ) ? (int) esc_html( $_REQUEST['form_id'] ) : '';
		
		if ( isset( $_REQUEST['visual-form-builder-submit'] ) && wp_verify_nonce( $_REQUEST['_wpnonce'], 'visual-form-builder-nonce' ) ) {
			
			do_action( 'vfb_confirmation', $form_id );
						
			// Get forms
			$order = sanitize_sql_orderby( 'form_id DESC' );
			$forms 	= $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d ORDER BY $order", $form_id ) );
			
			foreach ( $forms as $form ) {
				
				// If user wants this to redirect to PayPal
				if ( $form->form_paypal_setting ) {
					
					// The Assign Prices
					$paypal_field = unserialize( $form->form_paypal_field_price );
					
					// By default, amount based on user input
					$amount = ( is_array( $_REQUEST[ $paypal_field['name'] ] ) ) ? $_REQUEST[ $paypal_field['name'] ][0] : stripslashes( $_REQUEST[ $paypal_field['name' ] ] );
					
					// If multiple PayPal prices are set, loop through them
					if ( $paypal_field['prices'] && is_array( $paypal_field['prices'] ) ) {
						// Loop through prices and if multiple, amount is from select/radio/checkbox
						foreach ( $paypal_field['prices'] as $prices ) {
							// If it's a checkbox, account for that
							$name = ( is_array( $_REQUEST[ $paypal_field['name'] ] ) ) ? $_REQUEST[ $paypal_field['name'] ][0] : $_REQUEST[ $paypal_field['name'] ];
							
							if ( $prices['id'] == $name )
								$amount = $prices['amount'];
						}
					}
					
					// Make sure jQuery is included
					wp_enqueue_script( 'jquery' );
					
					// Output the jQuery that will submit our hidden PayPal form
					$paypal = '<script type="text/javascript">
						jQuery(window).load( function() {
							jQuery("#processPayPal").submit();
						});
						</script>';
					
					// The hidden PayPal form that sends our data
					$paypal .= '<form id="processPayPal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
							<input type="hidden" name="cmd" value="_xclick">
							<input type="hidden" name="item_name" value="' . stripslashes( $form->form_paypal_item_name ) . '">
							<input type="hidden" name="amount" value="' . $amount . '">
							<input type="hidden" name="currency_code" value="' . stripslashes( $form->form_paypal_currency ) . '">
							<input type="hidden" name="tax_rate" value="' . stripslashes( $form->form_paypal_tax ) . '">
							<input type="hidden" name="shipping" value="' . stripslashes( $form->form_paypal_shipping ) . '">
							<input type="hidden" name="business" value="' . stripslashes( $form->form_paypal_email ) . '">';
					$paypal .= apply_filters( 'vfb_paypal_extra_vars', '', $form_id );
					$paypal .= '</form>';
					
					// Message that replaces the usual success message
					$paypal .= '<p><strong>' . __( 'Please wait while you are redirected to PayPal...', 'visual-form-builder-pro' ) . '</strong></p>';

					return $paypal;
				}
				
				// Allow templating within confirmation message
				$form->form_success_message = $this->templating( $form->form_success_message );
				
				// If text, return output and format the HTML for display
				if ( 'text' == $form->form_success_type )
					return stripslashes( html_entity_decode( wp_kses_stripslashes( $form->form_success_message ) ) );
				// If page, redirect to the permalink
				elseif ( 'page' == $form->form_success_type ) {
					$page = get_permalink( $form->form_success_message );
					wp_redirect( $page );
					exit();
				}
				// If redirect, redirect to the URL
				elseif ( 'redirect' == $form->form_success_type ) {
					wp_redirect( $form->form_success_message );
					exit();
				}
			}
		}
	}
	
	/**
	 * Get conditional fields for the selected form
	 * 
	 * @since 1.9
	 * @return json encoded array || false if no rules found
	 */
	private function get_conditional_fields( $form_id ) {
		global $wpdb;
		
		$rules = $wpdb->get_results( $wpdb->prepare( "SELECT field_id, field_rule FROM $this->field_table_name WHERE field_rule_setting = 1 AND field_rule != '' AND form_id = %d", $form_id ) );
		
		if ( !$rules )
			return false;
			
		$conditions = array();
		
		foreach ( $rules as $rule ) {
			$conditions[] = unserialize( $rule->field_rule );
		}
		
		return json_encode( $conditions );
	}
		
	/**
	 * Output form via shortcode
	 * 
	 * @since 1.0
	 */
	public function form_code( $atts, $output = '' ) {
		
		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/form-output.php' );
		
		return $output;
	}
	
	/**
	 * Handle emailing the content
	 * 
	 * @since 1.0
	 * @uses wp_mail() E-mails a message
	 */
	public function email() {
		require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/email.php' );
	}
	
	/**
	 * Validate the input
	 * 
	 * @since 1.3
	 */
	public function validate_input( $data, $name, $type, $required, $form_verification ) {
		// Skip the validation if the verification is off and secret is still required
		if ( 'yes' == $required && 'secret' == $type && 0 == $form_verification )
			return true;
				
		if ( strlen( $data ) > 0 ) :
			switch( $type ) {
				case 'email' :
					if ( !is_email( $data ) )
						wp_die( "<h1>$name</h1><br>" . __( 'Not a valid email address', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				case 'number' :
				case 'currency' :
					if ( !is_numeric( $data ) )
						wp_die( "<h1>$name</h1><br>" . __( 'Not a valid number', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				case 'phone' :
					if ( strlen( $data ) > 9 && preg_match( '/^((\+)?[1-9]{1,2})?([-\s\.])?((\(\d{1,4}\))|\d{1,4})(([-\s\.])?[0-9]{1,12}){1,2}$/', $data ) )
						return true; 
					else
						wp_die( "<h1>$name</h1><br>" . __( 'Not a valid phone number. Most US/Canada and International formats accepted.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				case 'url' :
					if ( !preg_match( '|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $data ) )
						wp_die( "<h1>$name</h1><br>" . __( 'Not a valid URL.', 'visual-form-builder-pro' ), '', array( 'back_link' => true ) );
				break;
				
				default :
					return true;
				break;
			}
		endif;
	}
	
	/**
	 * Sanitize the input
	 * 
	 * @since 1.9
	 */
	public function sanitize_input( $data, $type ) {
		if ( strlen( $data ) > 0 ) :
			switch( $type ) {
				case 'text' :
					return sanitize_text_field( $data );
				break;
				
				case 'textarea' :
					return wp_strip_all_tags( $data );
				break;
				
				case 'email' :
					return sanitize_email( $data );
				break;
				
				case 'username' :
					return sanitize_user( $data );
				break;
				
				case 'html' :
					return wp_kses_data( force_balance_tags( $data ) );
				break;
				
				case 'min' :
				case 'max' :
				case 'number' :
					return intval( $data );
				break;
				
				default :
					return wp_kses_data( $data );
				break;
			}
		endif;
	}
	
	
	protected function akismet_check( $data ) {
		if ( !function_exists( 'akismet_http_post' ) )
			return false;
		
		global $akismet_api_host, $akismet_api_port;
		
		$query_string = '';
		$result       = false;
		
		foreach ( array_keys( $data ) as $k ) {
			$query_string .= $k . '=' . urlencode( $data[ $k ] ) . '&';
		}
		
		$response = akismet_http_post( $query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );
		
		// Only return true if a response is available
		if ( $response ) {
			if ( 'true' == trim( $response[1] ) )
				$result = true;
		}
		
		return $result;
	}
	
	/**
	 * Make sure the User Agent string is not a SPAM bot
	 * 
	 * @since 1.3
	 */
	public function isBot() {
		$bots = apply_filters( 'vfb_blocked_spam_bots', array( 'archiver', 'binlar', 'casper', 'checkprivacy', 'clshttp', 'cmsworldmap', 'comodo', 'curl', 'diavol', 'dotbot', 'email', 'extract', 'feedfinder', 'flicky',  'grab', 'harvest', 'httrack', 'ia_archiver', 'jakarta', 'kmccrew', 'libwww', 'loader', 'miner', 'nikto', 'nutch', 'planetwork', 'purebot', 'pycurl', 'python', 'scan', 'skygrid', 'sucker', 'turnit', 'vikspider', 'wget', 'winhttp', 'youda', 'zmeu', 'zune' ) );
		
		$isBot = false;
		
		$user_agent = wp_kses_data( $_SERVER['HTTP_USER_AGENT'] );
		
		foreach ( $bots as $bot ) {
			if ( stripos( $user_agent, $bot ) !== false )
				$isBot = true;
		}
	 
		if ( empty( $user_agent ) || $user_agent == ' ' )
			$isBot = true;
	 
		return $isBot;
	}
	
	/**
	 * Replace variables surrounded with {} brackets with $_POST values
	 * 
	 * @since 1.9
	 * @return replaced values || original value if no brackets found
	 */
	public function templating( $key ) {
		$search = preg_match_all( '/{(.*?)}/', $key, $matches );
			
		if ( $search ) {
			foreach ( $matches[1] as $match ) {
				$key = str_ireplace( "{{$match}}", html_entity_decode( stripslashes( esc_html( $_POST[ $match ] ) ), ENT_QUOTES, 'UTF-8' ), $key );
			}
		}
		
		return $key;
	}
}

// On plugin activation, install the databases and add/update the DB version
register_activation_hook( __FILE__, array( 'Visual_Form_Builder_Pro', 'install_db' ) );

// The VFB Pro widget
require( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/class-widget.php' );
?>