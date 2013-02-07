<?php

$action = ( isset( $_REQUEST['form'] ) && $_REQUEST['form'] !== '0' ) ? 'update_form' : 'create_form';

$order = sanitize_sql_orderby( 'form_id DESC' );
$where = apply_filters( 'vfb_pre_get_forms', '' );
$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->form_table_name WHERE form_id = %d $where ORDER BY $order", $form_nav_selected_id ) );

if ( !$form || $form->form_id !== $form_nav_selected_id )
	wp_die( 'You must select a form' );

$form_id 					= $form->form_id;
$form_title 				= stripslashes( $form->form_title );
$form_subject 				= stripslashes( $form->form_email_subject );
$form_email_from_name 		= stripslashes( $form->form_email_from_name );
$form_email_from 			= stripslashes( $form->form_email_from);
$form_email_from_override 	= stripslashes( $form->form_email_from_override);
$form_email_from_name_override = stripslashes( $form->form_email_from_name_override);
$form_email_to = ( is_array( unserialize( $form->form_email_to ) ) ) ? unserialize( $form->form_email_to ) : explode( ',', unserialize( $form->form_email_to ) );
$form_success_type 			= stripslashes( $form->form_success_type );
$form_success_message 		= stripslashes( $form->form_success_message );
$form_notification_setting 	= stripslashes( $form->form_notification_setting );
$form_notification_email_name = stripslashes( $form->form_notification_email_name );
$form_notification_email_from = stripslashes( $form->form_notification_email_from );
$form_notification_email 	= stripslashes( $form->form_notification_email );
$form_notification_subject 	= stripslashes( $form->form_notification_subject );
$form_notification_message 	= stripslashes( $form->form_notification_message );
$form_notification_entry 	= stripslashes( $form->form_notification_entry );

$form_paypal_setting 		= stripslashes( $form->form_paypal_setting );
$form_paypal_email 			= stripslashes( $form->form_paypal_email );
$form_paypal_currency 		= stripslashes( $form->form_paypal_currency );
$form_paypal_shipping 		= stripslashes( $form->form_paypal_shipping );
$form_paypal_tax 			= stripslashes( $form->form_paypal_tax );
$form_paypal_field_price 	= unserialize( $form->form_paypal_field_price );
$form_paypal_item_name 		= stripslashes( $form->form_paypal_item_name );

$form_label_alignment 		= stripslashes( $form->form_label_alignment );
$form_verification 			= stripslashes( $form->form_verification );

$form_entries_allowed		= stripslashes( $form->form_entries_allowed );
$form_entries_schedule 		= unserialize( $form->form_entries_schedule );

// Only show required text fields for the sender name override
$senders = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE form_id = %d AND field_type IN( 'text', 'name' ) AND field_validation = '' AND field_required = 'yes'", $form_nav_selected_id ) );

// Only show required email fields for the email override
$emails = $wpdb->get_results( "SELECT * FROM $this->field_table_name WHERE (form_id = $form_nav_selected_id AND field_type='text' AND field_validation = 'email' AND field_required = 'yes') OR (form_id = $form_nav_selected_id AND field_type='email' AND field_validation = 'email' AND field_required = 'yes')" );

// Only show required email fields for the email override
$paypal_fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE (form_id = %d AND (field_type='text' OR field_type='currency' OR field_type='select' OR field_type='radio' OR field_type='checkbox')) ORDER BY field_sequence ASC", $form_nav_selected_id ) );


$screen = get_current_screen();
$class = 'columns-' . get_current_screen()->get_columns();
?>
<div id="vfb-form-builder-frame" class="metabox-holder <?php echo $class; ?>">
	<div id="vfb-postbox-container-1" class='vfb-postbox-container'>
    	<form id="form-items" class="nav-menu-meta" method="post" action="">
			<input name="action" type="hidden" value="create_field" />
			<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
			<?php
			wp_nonce_field( 'create-field-' . $form_nav_selected_id );			
			do_meta_boxes( 'toplevel_page_visual-form-builder-pro', 'side', null );
			?>
		</form>
	</div> <!-- .vfb-postbox-container -->
    
    <div id="vfb-postbox-container-2" class='vfb-postbox-container'>
	    <div id="vfb-form-builder-main">
	        <div id="vfb-form-builder-management">
	            <div class="form-edit">
	            	<form method="post" id="visual-form-builder-update" action="">
	                	<input name="action" type="hidden" value="<?php echo $action; ?>" />
						<input name="form_id" type="hidden" value="<?php echo $form_nav_selected_id; ?>" />
	                    <?php wp_nonce_field( "$action-$form_nav_selected_id" ); ?>
	                	<div id="form-editor-header">
	                    	<div id="submitpost" class="submitbox">
	                        	<div class="vfb-major-publishing-actions">
	                        		<?php if ( current_user_can( 'vfb_create_forms' ) ) : ?>
	                        		<label for="form-name" class="menu-name-label howto open-label">
	                                    <span class="sender-labels"><?php _e( 'Form Name' , 'visual-form-builder-pro'); ?></span>
	                                    <input type="text" value="<?php echo ( isset( $form_title ) ) ? $form_title : ''; ?>" placeholder="Enter form name here" class="menu-name regular-text menu-item-textbox required" id="form-name" name="form_title" />
	                                </label>
	                        		<?php elseif ( $form_nav_selected_id !== '0' && !current_user_can( 'vfb_create_forms' ) && current_user_can( 'vfb_edit_forms' ) ) : ?>
	                            	<label for="form-name" class="menu-name-label howto open-label">
	                                    <span class="sender-labels"><?php _e( 'Form Name' , 'visual-form-builder-pro'); ?></span>
	                                    <input type="text" value="<?php echo ( isset( $form_title ) ) ? $form_title : ''; ?>" placeholder="Enter form name here" class="menu-name regular-text menu-item-textbox required" id="form-name" name="form_title" />
	                                </label>
	                                <?php endif; ?>
	                                <?php 
										// Display sender details and confirmation message if we're on a form, otherwise just the form name
										if ( $form_nav_selected_id !== '0' ) : 
									?>
	                                <br class="clear" />
	                                
	                                <?php
										// Get the Form Setting drop down and accordion settings, if any
										$user_form_settings = get_user_meta( $user_id, 'vfb-form-settings' );
										
										// Setup defaults for the Form Setting tab and accordion
										$settings_tab = 'closed';
										$settings_accordion = 'general-settings';
										
										// Loop through the user_meta array
										foreach( $user_form_settings as $set ) {
											// If form settings exist for this form, use them instead of the defaults
											if ( isset( $set[ $form_id ] ) ) {
												$settings_tab 		= $set[ $form_id ]['form_setting_tab'];
												$settings_accordion = $set[ $form_id ]['setting_accordion'];
											}
										}
										
										// If tab is opened, set current class
										$opened_tab = ( $settings_tab == 'opened' ) ? 'current' : '';
									?>
	                                
	                                
	                                <div class="button-group">
										<a href="#form-settings" id="form-settings-button" class="vfb-button vfb-first <?php echo $opened_tab; ?>"><?php _e( 'Form Settings' , 'visual-form-builder-pro'); ?><span class="button-icon arrow"></span></a>
										<?php if ( current_user_can( 'vfb_copy_forms' ) ) : ?>
	                                    <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=copy_form&amp;form=' . $form_nav_selected_id ), 'copy-form-' . $form_nav_selected_id ) ); ?>" class="vfb-button vfb-duplicate"><?php _e( 'Duplicate Form' , 'visual-form-builder-pro'); ?><span class="button-icon plus"></span></a>
	                                    <?php endif; ?>
	                                    <?php if ( current_user_can( 'vfb_delete_forms' ) ) : ?>
	                                    <a href="<?php echo esc_url( wp_nonce_url( admin_url('admin.php?page=visual-form-builder-pro&amp;action=delete_form&amp;form=' . $form_nav_selected_id ), 'delete-form-' . $form_nav_selected_id ) ); ?>" class="vfb-button vfb-delete vfb-last menu-delete"><?php _e( 'Delete Form' , 'visual-form-builder-pro'); ?><span class="button-icon delete"></span></a>
	                                    <?php endif; ?>
	                                </div>
	                                
	                                <div id="form-settings" class="<?php echo $opened_tab; ?>">
	                                    <!-- !General settings section -->
	                                    <a href="#general-settings" class="settings-links<?php echo ( $settings_accordion == 'general-settings' ) ? ' on' : ''; ?>">1. General<span class="arrow"></span></a>
	                                    <div id="general-settings" class="form-details<?php echo ( $settings_accordion == 'general-settings' ) ? ' on' : ''; ?>">
	                                        <!-- Label Alignment -->
	                                        <p class="description description-wide">
	                                        <label for="form-label-alignment">
	                                            <?php _e( 'Label Alignment' , 'visual-form-builder-pro'); ?>
	                                            <span class="vfb-tooltip" title="About Label Alignment" rel="Set the field labels for this form to be aligned either on top, to the left, or to the right.  By default, all labels are aligned on top of the inputs.">(?)</span>
	                        					<br />
	                                         </label>
	                                            <select name="form_label_alignment" id="form-label-alignment" class="widefat">
	                                                <option value="" <?php selected( $form_label_alignment, '' ); ?>><?php _e( 'Top Aligned' , 'visual-form-builder-pro'); ?></option>
	                                                <option value="left-label" <?php selected( $form_label_alignment, 'left-label' ); ?>><?php _e( 'Left Aligned' , 'visual-form-builder-pro'); ?></option>
	                                                <option value="right-label" <?php selected( $form_label_alignment, 'right-label' ); ?>><?php _e( 'Right Aligned' , 'visual-form-builder-pro'); ?></option>                                                        
	                                            </select>
	                                        </p>
	                                        <br class="clear" />
	                                        <!-- Display SPAM Verification -->
	                                        <p class="description description-wide">
	                                        <label for="form-verification">
	                                            <?php _e( 'Display SPAM Verification' , 'visual-form-builder-pro'); ?>
	                                            <span class="vfb-tooltip" title="About Display SPAM Verification" rel="The verification section ensures that your form is filled out by actual users and not spammers. It works by asking a simple logic captcha question at the end of your form.">(?)</span>
	                        					<br />
	                                         </label>
	                                            <select name="form_verification" id="form-verification" class="widefat">
	                                                <option value="1" <?php selected( $form_verification, 1 ); ?>><?php _e( 'Yes (Recommended)' , 'visual-form-builder-pro'); ?></option>                                                        
	                                                <option value="0" <?php selected( $form_verification, 0 ); ?>><?php _e( 'No' , 'visual-form-builder-pro'); ?></option>
	                                            </select>
	                                        </p>
	                                        <br class="clear" />
	                                        <!-- Entries Allowed -->
	                                        <p class="description description-wide">
	                                        <label for="form-entries-allowed">
	                                            <?php _e( 'Entries Allowed' , 'visual-form-builder-pro'); ?>
	                                            <span class="vfb-tooltip" title="About Entries Allowed" rel="This is the number of total entries you want your form to accept. Once your form has collected that many entries, the form will stop accepting submissions. Leave this blank if you want unlimited entries.">(?)</span>
	                        					<br />
	                                         </label>
	                                            <input type="text" value="<?php echo stripslashes( $form_entries_allowed ); ?>" class="widefat" id="form-entries-allowed" name="form_entries_allowed" />
	                                        </p>
	                                        <br class="clear" />
	                                        <!-- Schedule Start Date -->
	                                        <p class="description description-thin">
	                                        <label for="form-entries-schedule-start">
	                                            <?php _e( 'Schedule Start Date' , 'visual-form-builder-pro'); ?>
	                                            <span class="vfb-tooltip" title="About Schedule Start Date" rel="Set a date and time to automatically make your form become active. A blank value here means the form will always be active.">(?)</span>
	                        					<br />
	                                         </label>
	                                            <input type="text" value="<?php echo stripslashes( $form_entries_schedule['start'] ); ?>" class="widefat" id="form-entries-schedule-start" name="form_entries_schedule[start]" />
	                                        </p>
	                                        <!-- Schedule End Date -->
	                                        <p class="description description-thin">
	                                        <label for="form-entries-schedule-end">
	                                            <?php _e( 'Schedule End Date' , 'visual-form-builder-pro'); ?>
	                                            <span class="vfb-tooltip" title="About Schedule End Date" rel="Set a date and time to automatically make your form become inactive. A blank value here means the form will always be active, depending on a start date.">(?)</span>
	                        					<br />
	                                         </label>
	                                            <input type="text" value="<?php echo stripslashes( $form_entries_schedule['end'] ); ?>" class="widefat" id="form-entries-schedule-end" name="form_entries_schedule[end]" />
	                                        </p>
	                                        <br class="clear" />
	                                    </div>
	                                    
	                                    <!-- !Email section -->
	                                    <a href="#email-details" class="settings-links<?php echo ( $settings_accordion == 'email-details' ) ? ' on' : ''; ?>">2. Email<span class="arrow"></span></a>
	                                    <div id="email-details" class="form-details<?php echo ( $settings_accordion == 'email-details' ) ? ' on' : ''; ?>">
	                                        
	                                        <p><em><?php _e( 'The forms you build here will send information to one or more email addresses when submitted by a user on your site.  Use the fields below to customize the details of that email.' , 'visual-form-builder-pro'); ?></em></p>
	
	                                        <!-- E-mail Subject -->
	                                        <p class="description description-wide">
	                                        <label for="form-email-subject">
	                                            <?php _e( 'E-mail Subject' , 'visual-form-builder-pro'); ?>
	                                            <span class="vfb-tooltip" title="About E-mail Subject" rel="This option sets the subject of the email that is sent to the emails you have set in the E-mail(s) To field.">(?)</span>
	                        					<br />
	                                            <input type="text" value="<?php echo stripslashes( $form_subject ); ?>" class="widefat" id="form-email-subject" name="form_email_subject" />
	                                        </label>
	                                        </p>
	                                        <br class="clear" />
	
	                                        <!-- Sender Name -->
	                                        <p class="description description-thin">
	                                        <label for="form-email-sender-name">
	                                            <?php _e( 'Your Name or Company' , 'visual-form-builder-pro'); ?>
	                                            <span class="vfb-tooltip" title="About Your Name or Company" rel="This option sets the From display name of the email that is sent to the emails you have set in the E-mail(s) To field.">(?)</span>
	                        					<br />
	                                            <input type="text" value="<?php echo $form_email_from_name; ?>" class="widefat" id="form-email-sender-name" name="form_email_from_name"<?php echo ( $form_email_from_name_override != '' ) ? ' readonly="readonly"' : ''; ?> />
	                                        </label>
	                                        </p>
	                                        <p class="description description-thin">
	                                        	<label for="form_email_from_name_override">
	                                            	<?php _e( "User's Name (optional)" , 'visual-form-builder-pro'); ?>
	                                                <span class="vfb-tooltip" title="About User's Name" rel="Select a required text field from your form to use as the From display name in the email.">(?)</span>
	                        						<br />
	                                            <select name="form_email_from_name_override" id="form_email_from_name_override" class="widefat">
	                                                <option value="" <?php selected( $form_email_from_name_override, '' ); ?>><?php _e( 'Select a required text field' , 'visual-form-builder-pro'); ?></option>
	                                                <?php 
	                                                foreach( $senders as $sender ) {
	                                                    echo '<option value="' . $sender->field_id . '"' . selected( $form_email_from_name_override, $sender->field_id ) . '>' . stripslashes( $sender->field_name ) . '</option>';
	                                                }
	                                                ?>
	                                            </select>
	                                            </label>
	                                        </p>
	                                        <br class="clear" />
	                                        
	                                        <!-- Sender E-mail -->
	                                        <p class="description description-thin">
	                                        <label for="form-email-sender">
	                                            <?php _e( 'Reply-To E-mail' , 'visual-form-builder-pro'); ?>
	                                            <span class="vfb-tooltip" title="About Reply-To Email" rel="Manually set the email address that users will reply to.">(?)</span>
	                        					<br />
	                                            <input type="text" value="<?php echo $form_email_from; ?>" class="widefat" id="form-email-sender" name="form_email_from"<?php echo ( $form_email_from_override != '' ) ? ' readonly="readonly"' : ''; ?> />
	                                        </label>
	                                        </p>
	                                        <p class="description description-thin">
	                                            <label for="form_email_from_override">
	                                            	<?php _e( "User's E-mail (optional)" , 'visual-form-builder-pro'); ?>
	                                                <span class="vfb-tooltip" title="About User's Email" rel="Select a required email field from your form to use as the Reply-To email.">(?)</span>
	                        						<br />
	                                            <select name="form_email_from_override" id="form_email_from_override" class="widefat">
	                                                <option value="" <?php selected( $form_email_from_override, '' ); ?>><?php _e( 'Select a required email field' , 'visual-form-builder-pro'); ?></option>
	                                                <?php 
	                                                foreach( $emails as $email ) {
	                                                    echo '<option value="' . $email->field_id . '"' . selected( $form_email_from_override, $email->field_id ) . '>' . stripslashes( $email->field_name ) . '</option>';
	                                                }
	                                                ?>
	                                            </select>
	                                            </label>
	                                        </p>
	                                        <br class="clear" />
											
	                                        <!-- E-mail(s) To -->
	                                        <?php
	                                            // Basic count to keep track of multiple options
	                                            $count = 1;
	                                            
	                                            // Loop through the options
	                                            foreach ( $form_email_to as $email_to ) {
	                                        ?>
	                                        <div id="clone-email-<?php echo $count; ?>" class="option">
	                                            <p class="description description-wide">
	                                                <label for="form-email-to-<?php echo "$count"; ?>" class="clonedOption">
	                                                <?php _e( 'E-mail(s) To' , 'visual-form-builder-pro'); ?>
	                                                <span class="vfb-tooltip" title="About E-mail(s) To" rel="This option sets single or multiple emails to send the submitted form data to. At least one email is required.">(?)</span>
	                        					<br />
	                                                    <input type="text" value="<?php echo stripslashes( $email_to ); ?>" name="form_email_to[]" class="widefat" id="form-email-to-<?php echo "$count"; ?>" />
	                                                </label>
	                                                
	                                                <a href="#" class="addEmail" title="Add an Email">Add</a> <a href="#" class="deleteEmail" title="Delete Email">Delete</a>
	                                                
	                                            </p>
	                                            <br class="clear" />
	                                        </div>
	                                        <?php 
	                                                $count++;
	                                            }
	                                        ?>
	                                    </div>
	                                    
	                                    <!-- !Confirmation section -->
	                                    <a href="#confirmation" class="settings-links<?php echo ( $settings_accordion == 'confirmation' ) ? ' on' : ''; ?>">3. Confirmation<span class="arrow"></span></a>
	                                    <div id="confirmation-message" class="form-details<?php echo ( $settings_accordion == 'confirmation' ) ? ' on' : ''; ?>">
	                                        <p><em><?php _e( "After someone submits a form, you can control what is displayed. By default, it's a message but you can send them to another WordPress Page or a custom URL." , 'visual-form-builder-pro'); ?></em></p>
	                                        <label for="form-success-type-text" class="menu-name-label open-label">
	                                            <input type="radio" value="text" id="form-success-type-text" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'text' ); ?> />
	                                            <span><?php _e( 'Text' , 'visual-form-builder-pro'); ?></span>
	                                        </label>
	                                        <label for="form-success-type-page" class="menu-name-label open-label">
	                                            <input type="radio" value="page" id="form-success-type-page" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'page' ); ?>/>
	                                            <span><?php _e( 'Page' , 'visual-form-builder-pro'); ?></span>
	                                        </label>
	                                        <label for="form-success-type-redirect" class="menu-name-label open-label">
	                                            <input type="radio" value="redirect" id="form-success-type-redirect" class="form-success-type" name="form_success_type" <?php checked( $form_success_type, 'redirect' ); ?>/>
	                                            <span><?php _e( 'Redirect' , 'visual-form-builder-pro'); ?></span>
	                                        </label>
	                                        <br class="clear" />
	                                        <p class="description description-wide">
	                                        <?php
	                                        $default_text = '';
	                                        
	                                        // If there's no text message, make sure there is something displayed by setting a default
	                                        if ( $form_success_message === '' )
	                                            $default_text = sprintf( '<p id="form_success">%s</p>', __( 'Your form was successfully submitted. Thank you for contacting us.' , 'visual-form-builder-pro') );
	                                        ?>
	                                        <textarea id="form-success-message-text" class="form-success-message<?php echo ( 'text' == $form_success_type ) ? ' active' : ''; ?>" name="form_success_message_text"><?php echo $default_text; ?><?php echo ( 'text' == $form_success_type ) ? $form_success_message : ''; ?></textarea>
	                                        
	                                        <?php
	                                        // Display all Pages
	                                        wp_dropdown_pages( array(
	                                            'name' 				=> 'form_success_message_page', 
	                                            'id' 				=> 'form-success-message-page',
	                                            'class' 			=> 'widefat',
	                                            'show_option_none' 	=> __( 'Select a Page' , 'visual-form-builder-pro'),
	                                            'selected' 			=> $form_success_message
	                                        ));
	                                        ?>
	                                        <input type="text" value="<?php echo ( 'redirect' == $form_success_type ) ? $form_success_message : ''; ?>" id="form-success-message-redirect" class="form-success-message regular-text<?php echo ( 'redirect' == $form_success_type ) ? ' active' : ''; ?>" name="form_success_message_redirect" placeholder="http://" />
	                                        </p>
	                                    <br class="clear" />
	
	                                    </div>
	                                
	                                    <!-- !Notification section -->
	                                    <a href="#notification" class="settings-links<?php echo ( $settings_accordion == 'notification' ) ? ' on' : ''; ?>">4. Notification<span class="arrow"></span></a>
	                                    <div id="notification" class="form-details<?php echo ( $settings_accordion == 'notification' ) ? ' on' : ''; ?>">
	                                        <p><em><?php _e( "When a user submits their entry, you can send a customizable notification email." , 'visual-form-builder-pro'); ?></em></p>
	                                        <label for="form-notification-setting">
	                                            <input type="checkbox" value="1" id="form-notification-setting" class="form-notification" name="form_notification_setting" <?php checked( $form_notification_setting, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
	                                            <?php _e( 'Send Confirmation Email to User' , 'visual-form-builder-pro'); ?>
	                                        </label>
	                                        <br class="clear" />
	                                        <div id="notification-email">
	                                            <p class="description description-wide">
	                                            <label for="form-notification-email-name">
	                                                <?php _e( 'Sender Name or Company' , 'visual-form-builder-pro'); ?>
	                                                <span class="vfb-tooltip" title="About Sender Name or Company" rel="Enter the name you would like to use for the email notification.">(?)</span>
	                        						<br />
	                                                <input type="text" value="<?php echo $form_notification_email_name; ?>" class="widefat" id="form-notification-email-name" name="form_notification_email_name" />
	                                            </label>
	                                            </p>
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                            <label for="form-notification-email-from">
	                                                <?php _e( 'Reply-To E-mail' , 'visual-form-builder-pro'); ?>
	                                                <span class="vfb-tooltip" title="About Reply-To Email" rel="Manually set the email address that users will reply to.">(?)</span>
	                        						<br />
	                                                <input type="text" value="<?php echo $form_notification_email_from; ?>" class="widefat" id="form-notification-email-from" name="form_notification_email_from" />
	                                            </label>
	                                            </p>
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                                <label for="form-notification-email">
	                                                    <?php _e( 'E-mail To' , 'visual-form-builder-pro'); ?>
	                                                    <span class="vfb-tooltip" title="About E-mail To" rel="Select a required email field from your form to send the notification email to.">(?)</span>
	                        							<br />
	                                                    <select name="form_notification_email" id="form-notification-email" class="widefat">
	                                                        <option value="" <?php selected( $form_notification_email, '' ); ?>><?php _e( 'Select a required email field' , 'visual-form-builder-pro'); ?></option>
	                                                        <?php 
	                                                        foreach( $emails as $email ) {
	                                                            echo '<option value="' . $email->field_id . '"' . selected( $form_notification_email, $email->field_id ) . '>' . $email->field_name . '</option>';
	                                                        }
	                                                        ?>
	                                                    </select>
	                                                </label>
	                                            </p>
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                            <label for="form-notification-subject">
	                                               <?php _e( 'E-mail Subject' , 'visual-form-builder-pro'); ?>
	                                               <span class="vfb-tooltip" title="About E-mail Subject" rel="This option sets the subject of the email that is sent to the emails you have set in the E-mail To field.">(?)</span>
	                        						<br />
	                                                <input type="text" value="<?php echo $form_notification_subject; ?>" class="widefat" id="form-notification-subject" name="form_notification_subject" />
	                                            </label>
	                                            </p>
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                            <label for="form-notification-message"><?php _e( 'Message' , 'visual-form-builder-pro'); ?></label>
	                                            <span class="vfb-tooltip" title="About Message" rel="Insert a message to the user. This will be inserted into the beginning of the email body.">(?)</span>
	                        					<br />
	                                            <textarea id="form-notification-message" class="form-notification-message widefat" name="form_notification_message"><?php echo $form_notification_message; ?></textarea>
	                                            </p>
	                                            <br class="clear" />
	                                            <label for="form-notification-entry">
	                                            <input type="checkbox" value="1" id="form-notification-entry" class="form-notification" name="form_notification_entry" <?php checked( $form_notification_entry, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
	                                            <?php _e( "Include a Copy of the User's Entry" , 'visual-form-builder-pro'); ?>
	                                        </label>
	                                        </div>
	                                    </div>
	                                
	                                    <!-- !PayPal section -->
	                                    <a href="#paypal" class="settings-links<?php echo ( $settings_accordion == 'paypal' ) ? ' on' : ''; ?>">5. PayPal<span class="arrow"></span></a>
	                                    <div id="paypal" class="form-details<?php echo ( $settings_accordion == 'paypal' ) ? ' on' : ''; ?>">
	                                    	<?php if ( class_exists( 'VFB_Pro_Payments' ) ) : ?>
	                                    	<p><strong>Payments Add-on detected</strong><br> It is recommended you use the <a href="<?php echo esc_url( add_query_arg( array( 'form_id' => $form_id ), admin_url( 'admin.php?page=vfb-payments' ) ) ); ?>">Payments Add-on</a> to collect payments.</p>
	                                    	<?php endif; ?>
	                                        <p><em><?php _e( 'Forward successful form submissions to PayPal to collect simple payments, such as registration fees.' , 'visual-form-builder-pro'); ?></em></p>
	                                        
	                                        <label for="form-paypal-setting">
	                                            <input type="checkbox" value="1" id="form-paypal-setting" class="form-paypal" name="form_paypal_setting" <?php checked( $form_paypal_setting, '1' ); ?> style="margin-top:-1px;margin-left:0;"/>
	                                            <?php _e( 'Use this form as a PayPal form' , 'visual-form-builder-pro'); ?>
	                                        </label>
	                                        <br class="clear" />
	                                        <div id="paypal-setup">
	                                            <p class="description description-wide">
	                                                <label for="form-paypal-email">
	                                                   <?php _e( 'Account Email' , 'visual-form-builder-pro'); ?>
	                                                   	<span class="vfb-tooltip" title="About Account Email" rel="Insert your PayPal account email. This is not displayed to users.">(?)</span>
	                        							<br />
	                                                    <input type="text" value="<?php echo $form_paypal_email; ?>" class="widefat" id="form-paypal-email" name="form_paypal_email" />
	                                                </label>
	                                            </p>                                             
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                                <label for="form-paypal-currency">
	                                                    <?php _e( 'Currency' , 'visual-form-builder-pro'); ?>
	                                                    <span class="vfb-tooltip" title="About Currency" rel="Change the currency type to your region of choice. By default, it is set to U.S Dollars">(?)</span>
	                        							<br />
	                                                    <?php
	                                                        // Setup currencies array
	                                                        $currencies = array( 'USD' => '&#36; - U.S. Dollar', 'AUD' => 'A&#36; - Australian Dollar', 'BRL' => 'R&#36; - Brazilian Real', 'GBP' => '&#163; - British Pound', 'CAD' => 'C&#36; - Canadaian Dollar', 'CZK' => '&#75;&#269; - Czech Koruny', 'DKK' => '&#107;&#114; - Danish Kroner', 'EUR' => '&#8364; - Euro', 'HKD' => '&#36; - Hong Kong Dollar', 'HUF' => '&#70;&#116; - Hungarian Forint', 'ILS' => '&#8362; - Israeli Sheqel', 'JPY' => '&#165; - Japanese Yen', 'MXN' => '&#36; - Mexican Peso', 'TWD' => 'NT&#36; - Taiwan New Dollars', 'NZD' => 'NZ&#36; - New Zealand Dollar', 'NOK' => '&#107;&#114; - Norwegian Kroner', 'PHP' => '&#80;&#104;&#11; - Philippine Peso', 'PLN' => '&#122;&#322; - Polish Zloty', 'SGD' => 'S&#36; - Singapore Dollar', 'SEK' => '&#107;&#114; - Swedish Kronor', 'CHF' => '&#67;&#72;&#70; - Swiss Francs', 'THB' => '&#3647; - Thai Baht' );
	                                                    ?>
	                                                    <select name="form_paypal_currency" id="form-paypal-currency" class="widefat">
	                                                        <?php foreach( $currencies as $currency => $val ) : ?>
	                                                            <option value="<?php echo $currency; ?>" <?php selected( $form_paypal_currency, $currency, 1 ); ?>><?php echo $val; ?></option>
	                                                        <?php endforeach; ?>
	                                                    </select>
	                                                </label>
	                                            </p>
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                                <label for="form-paypal-shipping">
														<?php _e( 'Shipping' , 'visual-form-builder-pro'); ?>
	                                                    <span class="vfb-tooltip" title="About Shipping" rel="If shipping charges are required for your item, insert the amount here.">(?)</span>
	                        							<br />
	                                                    <input type="text" value="<?php echo $form_paypal_shipping; ?>" class="widefat" id="form-paypal-shipping" name="form_paypal_shipping" />
	                                                </label>
	                                            </p>
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                                <label for="form-paypal-tax">
														<?php _e( 'Tax Rate' , 'visual-form-builder-pro'); ?>
	                                                    <span class="vfb-tooltip" title="About Tax Rate" rel="If you need to charge taxes on your item, insert the tax rate here. The % symbol is not necessary here.">(?)</span>
	                        							<br />
	                                                    <input type="text" value="<?php echo $form_paypal_tax; ?>" class="widefat" id="form-paypal-tax" name="form_paypal_tax" />
	                                                </label>
	                                            </p>
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                                <label for="form-paypal-item-name">
														<?php _e( 'Item Name' , 'visual-form-builder-pro'); ?>
	                                                    <span class="vfb-tooltip" title="About Item Name" rel="This option inserts an item name when the user is checking out through PayPal.">(?)</span>
	                        							<br />
	                                                    <input type="text" value="<?php echo $form_paypal_item_name; ?>" class="widefat" id="form-paypal-item-name" name="form_paypal_item_name" />
	                                                </label>
	                                            </p>
	                                            <br class="clear" />
	                                            <p class="description description-wide">
	                                                <label for="form-paypal-field-price"><?php _e( 'Assign Prices' , 'visual-form-builder-pro'); ?>
	                                                <span class="vfb-tooltip" title="About Assign Prices" rel="Assign prices to a field from your form.  Allowed field types are Text, Select, Radio, and Checkbox. Text inputs will automatically use the amount entered by the user.  Select, Radio, and Checkbox fields will allow you to enter amounts for the different options from those respective fields.">(?)</span>
	                        						<br />
	                                                    <select name="form_paypal_field_price[id]" id="form-paypal-field-price" class="widefat">
	                                                    <option value="" <?php selected( $form_paypal_field_price['id'], '' ); ?>><?php _e( 'Assign Prices to a Field' , 'visual-form-builder-pro'); ?></option>
	                                                    <?php 
	                                                    foreach( $paypal_fields as $paypal ) {
	                                                        echo '<option value="' . $paypal->field_id . '"' . selected( $form_paypal_field_price['id'], $paypal->field_id ) . '>' . stripslashes( $paypal->field_name ) . '</option>';
	                                                    }
	                                                    ?>
	                                                    </select>
	                                                    <img id="paypal-price-switch" alt="" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" class="waiting spinner" />
	                                                </label>
	                                            </p>
	                                            <br class="clear" />
	                                            
	                                            
	                                            <div class="assigned-price">
	                                                <?php
	                                                    if ( $form_paypal_field_price['id'] !== '' ) {
	                                                        $fields = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->field_table_name WHERE field_id = %d", $form_paypal_field_price['id'] ) );
	                                                        $paypal_price_field = unserialize( $wpdb->get_var( $wpdb->prepare( "SELECT form_paypal_field_price FROM $this->form_table_name WHERE form_id = %d", $form_id ) ) );
	                                                        $field_thing = '';
	                                                        foreach ( $fields as $field ) {
	                                                            if ( in_array( $field->field_type, array( 'text', 'currency' ) ) ) {
	                                                                $field_thing = '<p>Amount Based on User Input</p>';
	                                                            }
	                                                            elseif ( in_array( $field->field_type, array( 'select', 'radio', 'checkbox' ) ) ) {
	                                                                $options = ( is_array( unserialize( $field->field_options ) ) ) ? unserialize( $field->field_options ) : explode( ',', unserialize( $field->field_options ) );
	                                                                
	                                                                // Loop through each option and output
	                                                                foreach ( $options as $option => $value ) {
	                                                                    $field_thing .= '<p class="description description-wide"><label>' . stripslashes( $value ) . '<input type="text" value="' . stripslashes( $paypal_price_field['prices'][$option]['amount'] ) . '" name="form_paypal_field_price[prices][' . $option . '][amount]" class="widefat" /></label></p><br>';
	                                                                    echo '<input type="hidden" name="form_paypal_field_price[prices][' . $option . '][id]" value="' . stripslashes( $value ) . '" />';
	                                                                }
	                                                            }
	                                                            echo '<input type="hidden" name="form_paypal_field_price[name]" value="vfb-' . $field->field_id . '" />';
	                                                        }
	                                                        
	                                                        echo $field_thing;
	                                                    }
	                                                ?>
	                                            </div>
	                                            
	                                            <br class="clear" />
	                                        </div>
	                                    </div>
	                                </div>
	                                <?php endif; ?>
	
	                                <div class="publishing-action">
	                                	<?php if ( current_user_can( 'vfb_create_forms' ) ) : ?>
	                                    	<input type="submit" value="<?php echo ( $action == 'create_form' ) ? __( 'Create Form' , 'visual-form-builder-pro') : __( 'Save Form' , 'visual-form-builder-pro'); ?>" class="button-primary menu-save" id="save_form" name="save_form" />
	                                    <?php elseif ( $form_nav_selected_id !== '0' && !current_user_can( 'vfb_create_forms' ) && current_user_can( 'vfb_edit_forms' ) ) : ?>
	                                    	<input type="submit" value="<?php echo __( 'Save Form' , 'visual-form-builder-pro'); ?>" class="button-primary menu-save" id="save_form" name="save_form" />
	                                    <?php endif; ?>
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	                    <div id="post-body">
	                        <div id="post-body-content">
	                        <div id="vfb-fieldset-first-warning" class="error"><?php printf( '<p><strong>%1$s </strong><br>%2$s</p>', __( 'Warning &mdash; Missing Fieldset', 'visual-form-builder-pro' ), __( 'Your form may not function or display correctly. Please be sure to add or move a Fieldset to the beginning of your form.' , 'visual-form-builder-pro') ); ?></div>
	                        <!-- !Field Items output -->
	                        <ul id="menu-to-edit" class="menu ui-sortable droppable">
		                    <?php echo $this->field_output( $form_nav_selected_id ); ?>
							</ul>
	                        </div>
	                        <br class="clear" />
	                     </div>
	                     <br class="clear" />
	                    <div id="form-editor-footer">
	                    	<div class="vfb-major-publishing-actions">
	                            <div class="publishing-action">
	                            	<?php if ( current_user_can( 'vfb_create_forms' ) ) : ?>
	                                	<input type="submit" value="<?php echo ( $action == 'create_form' ) ? __( 'Create Form' , 'visual-form-builder-pro') : __( 'Save Form' , 'visual-form-builder-pro'); ?>" class="button-primary menu-save" id="save_form" name="save_form" />
	                                <?php elseif ( $form_nav_selected_id !== '0' && !current_user_can( 'vfb_create_forms' ) && current_user_can( 'vfb_edit_forms' ) ) : ?>
	                                    <input type="submit" value="<?php echo __( 'Save Form' , 'visual-form-builder-pro'); ?>" class="button-primary menu-save" id="save_form" name="save_form" />
	                                <?php endif; ?>
	                            </div> <!-- .publishing-action -->
	                        </div> <!-- .vfb-major-publishing-actions -->
	                    </div> <!-- #form-editor-footer -->
	                </form>
	            </div> <!-- .form-edit -->
	        </div> <!-- #vfb-form-builder-management -->
	    </div> <!-- vfb-form-builder-main -->
    </div> <!-- .vfb-postbox-container -->
    
    <div id="vfb-postbox-container-3" class='vfb-postbox-container'>
	    <div id="vfb-form-meta-preview">
	    	<iframe frameborder="0" scrolling="auto" src="<?php echo esc_url( add_query_arg( array( 'form' => $form_nav_selected_id ), plugins_url( 'form-preview.php', dirname( __FILE__ ) ) ) ); ?>"></iframe>
	    	<?php
	    	do_meta_boxes( 'toplevel_page_visual-form-builder-pro', 'column_3', null );
	    	?>
	    </div> <!-- .vfb-postbox-container -->
    </div> <!-- #vfb-form-meta-preview -->
    
</div> <!-- #vfb-form-builder-frame -->
<?php
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
?>