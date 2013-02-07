<?php
/**
 * Class that builds our Entries detail page
 * 
 * @since 1.4
 */
class VisualFormBuilder_Pro_Entries_Detail{
	public function __construct(){
		global $wpdb;
		
		// Setup global database table names
		$this->field_table_name   = $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name    = $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name = $wpdb->prefix . 'vfb_pro_entries';
		
		add_action( 'admin_init', array( &$this, 'entries_detail' ) );
	}
	
	public function entries_detail(){
		global $wpdb;
		
		$entry_id = absint( $_REQUEST['entry'] );
		
		$entries = $wpdb->get_results( $wpdb->prepare( "SELECT forms.form_title, entries.* FROM $this->form_table_name AS forms INNER JOIN $this->entries_table_name AS entries ON entries.form_id = forms.form_id WHERE entries.entries_id  = %d", $entry_id ) );
		
		echo '<p>' . sprintf( '<a href="?page=%s" class="view-entry">&laquo; Back to Entries</a>', $_REQUEST['page'] ) . '</p>';
		
		// Get the date/time format that is saved in the options table
		$date_format = get_option('date_format');
		$time_format = get_option('time_format');
		
		// Loop trough the entries and setup the data to be displayed for each row
		foreach ( $entries as $entry ) {
			$data = unserialize( $entry->data );
?>
			<form id="entry-edit" method="post" action="">
				<input name="action" type="hidden" value="update_entry" />
				<input name="entry_id" type="hidden" value="<?php echo $entry_id; ?>" />
				
				<?php wp_nonce_field( 'update-entry-' . $entry_id ); ?>
			<h3><span><?php echo stripslashes( $entry->form_title ); ?> : <?php echo __( 'Entry' , 'visual-form-builder-pro'); ?> # <?php echo $entry->entries_id; ?></span></h3>
            <div id="vfb-poststuff" class="metabox-holder has-right-sidebar">
				<div id="side-info-column" class="inner-sidebar">
					<div id="side-sortables">
						<div id="submitdiv" class="postbox">
							<h3><span><?php echo __( 'Details' , 'visual-form-builder-pro'); ?></span></h3>
							<div class="inside">
							<div id="submitbox" class="submitbox">
								<div id="minor-publishing">
									<div id="misc-publishing-actions">
										<div class="misc-pub-section">
											<span><strong><?php echo  __( 'Form Title' , 'visual-form-builder-pro'); ?>: </strong><?php echo stripslashes( $entry->form_title ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo  __( 'Date Submitted' , 'visual-form-builder-pro'); ?>: </strong><?php echo date( "$date_format $time_format", strtotime( $entry->date_submitted ) ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'IP Address' , 'visual-form-builder-pro'); ?>: </strong><?php echo $entry->ip_address; ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Email Subject' , 'visual-form-builder-pro'); ?>: </strong><?php echo stripslashes( $entry->subject ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Sender Name' , 'visual-form-builder-pro'); ?>: </strong><?php echo stripslashes( $entry->sender_name ); ?></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Sender Email' , 'visual-form-builder-pro'); ?>: </strong><a href="mailto:<?php echo stripslashes( $entry->sender_email ); ?>"><?php echo stripslashes( $entry->sender_email ); ?></a></span>
										</div>
										<div class="misc-pub-section">
											<span><strong><?php echo __( 'Emailed To' , 'visual-form-builder-pro'); ?>: </strong><?php echo preg_replace('/\b([A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4})\b/i', '<a href="mailto:$1">$1</a>', implode( ',', unserialize( stripslashes( $entry->emails_to ) ) ) ); ?></span>
										</div>
										<div class="misc-pub-section misc-pub-section-last">
											<span><strong><?php echo __( 'Notes' , 'visual-form-builder-pro'); ?>: </strong></span>
											<br>
											<textarea name="entries-notes" style="width:100%;height:7em;"><?php echo stripslashes( $entry->notes ); ?></textarea>
										</div>
										<div class="clear"></div>
									</div> <!--#misc-publishing-actions -->
								</div> <!-- #minor-publishing -->
								
								<div id="major-publishing-actions">
									<?php if ( current_user_can( 'vfb_delete_entries' ) ) : ?>
										<div id="delete-action"><?php echo sprintf( '<a class="submitdelete deletion entry-delete" href="?page=%s&action=%s&entry=%s">Delete</a>', $_REQUEST['page'], 'delete', $entry_id ); ?></div>
									<?php endif; ?>
                                    
                                    <?php if ( current_user_can( 'vfb_edit_entries' ) ) : ?>
	                                    <?php if ( is_array( $data[0] ) ) : ?>
											<div id="publishing-action"><input class="button-primary" type="submit" value="Update Entry" /></div>
	                                    <?php endif; ?>
                                    <?php endif; ?>

									<div class="clear"></div>
								</div> <!-- #major-publishing-actions -->
							</div> <!-- #submitbox -->
							</div> <!-- .inside -->
						</div> <!-- #submitdiv -->
					</div> <!-- #side-sortables -->
				</div> <!-- #side-info-column -->
            <!--</div>  #poststuff -->
			<div id="vfb-entries-body-content">
        <?php
			$count = 0;
			$open_fieldset = $open_section = false;
			
			foreach ( $data as $k => $v ) {
				if ( !is_array( $v ) ) {
					if ( $count == 0 ) {
						echo '<div class="postbox">
							<h3><span>' . $entry->form_title . ' : ' . __( 'Entry' , 'visual-form-builder-pro') .' #' . $entry->entries_id . '</span></h3>
							<div class="inside">';
					}
					
					echo '<h4>' . ucwords( $k ) . '</h4>';
					echo $v;
					$count++;
				}
				else {
					// Cast each array as an object
					$obj = (object) $v;

					// Close each section
					/*if ( $open_section == true ) {
						// If this field's parent does NOT equal our section ID
						if ( $sec_id && $sec_id !== $obj->parent_id ) {
							//echo '</div>';
							$open_section = false;
						}
					}*/
					
					if ( $obj->type == 'fieldset' ) {
						// Close each fieldset
						if ( $open_fieldset == true )
							echo '</table>';
						
						echo '<h3>' . stripslashes( $obj->name ) . '</h3><table class="form-table">';
						
						$open_fieldset = true;
					}
					/*elseif ( $obj->type == 'section' ) {
						// Close each fieldset
						if ( $open_section == true ){
							//echo '</div>';
						}
						echo '<h3>' . stripslashes( $obj->name ) . '</h3>';
						
						// Save section ID for future comparison
						$sec_id = $obj->id;
						$open_section = true;
					}*/
					
					switch ( $obj->type ) {
						case 'fieldset' :
						case 'section' :
						case 'submit' :
						case 'page-break' :
						case 'verification' :
						case 'secret' :
							?>
                            	<input name="field[<?php echo $obj->id; ?>]" type="hidden" value="<?php echo stripslashes( esc_attr( $obj->value ) ); ?>" />
                            <?php
						break;
						
						case 'text' :
						case 'email' :
						case 'url' :
						case 'currency' :
						case 'number' :
						case 'phone' :
						case 'ip-address' :
						case 'credit-card' :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
								<td><input id="field[<?php echo $obj->id; ?>]" name="field[<?php echo $obj->id; ?>]" class="regular-text" type="text" value="<?php echo stripslashes( esc_attr( $obj->value ) ); ?>" /></td>
							</tr>
                        	<?php
						break;
						
						case 'textarea' :
						case 'address' :
						case 'html' :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
                                <td><textarea id="field[<?php echo $obj->id; ?>]" name="field[<?php echo $obj->id; ?>]" class="large-text" cols="25" rows="8" type="text" ><?php echo html_entity_decode( stripslashes( $obj->value ), ENT_QUOTES, 'UTF-8' ); ?></textarea></td>
							</tr>
                        	<?php
						break;
						
						case 'select' :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
								<td>
                                   <select id="field[<?php echo $obj->id; ?>]" name="field[<?php echo $obj->id; ?>]">
                                    <?php
                                        $options = ( is_array( unserialize( $obj->options ) ) ) ? unserialize( $obj->options ) : explode( ',', unserialize( $obj->options ) );
                                        
                                        foreach( $options as $option => $value ) {
                                            echo '<option value="' . stripslashes( esc_attr( $value ) ) . '" ' . selected( $obj->value, $value, 0 ) . '>' . stripslashes( esc_attr( $value ) ) . '</option>';
                                        }
                                    ?>
                                    </select>
								</td>
							</tr>
                        	<?php
						break;
						
						case 'radio' :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
								<td>
                                    <?php
                                        $options = ( is_array( unserialize( $obj->options ) ) ) ? unserialize( $obj->options ) : explode( ',', unserialize( $obj->options ) );
                                        $count = 1;
                                        foreach( $options as $option => $value ) {
                                            echo '<label for="field[' . $obj->id . '][' . $count . ']"><input type="radio" id="field[' . $obj->id . '][' . $count . ']" name="field[' . $obj->id . ']" value="' . stripslashes( esc_attr( $value ) ) . '" ' . checked( $obj->value, stripslashes( $value ), 0) . '> ' . stripslashes( esc_attr( $value ) ) . '</label><br />';
                                            $count++;
                                        }
                                        
                                        // Get 'Allow Other'
										$field_options_other = ( isset( $obj->options_other ) ) ? maybe_unserialize( $obj->options_other ) : '';
										
										// Display 'Other' field
										if ( isset( $field_options_other['setting'] ) && 1 == $field_options_other['setting'] && isset( $field_options_other['selected'] ) ) {
											//$count++;
											echo '<label for="field[' . $obj->id . '][' . $count . ']"><input type="radio" name="field[' . $obj->id . ']" id="field[' . $obj->id . '][' . $count . ']" value="'. stripslashes( esc_attr( $field_options_other['selected'] ) ) . '" ' . checked( $obj->value, $field_options_other['selected'], 0 ) . '> ' . stripslashes( esc_attr( $field_options_other['selected'] ) ) . '</label><br />';
										}
                                    ?>
								</td>
							</tr>
                        	<?php
						break;
						
						case 'checkbox' :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
								<td>
                                    <?php
                                        $options = ( is_array( unserialize( $obj->options ) ) ) ? unserialize( $obj->options ) : explode( ',', unserialize( $obj->options ) );
										
										$vals = explode( ', ', $obj->value ); 
										$count = 1;
                                        foreach( $options as $option => $value ) {	
											$checked = ( in_array( $value, $vals ) || ( strpos( $obj->value, $value ) !== false ) ) ? 'checked="checked" ' : '';
											
                                           	echo '<label for="field[' . $obj->id . '][' . $count . ']"><input type="checkbox" id="field[' . $obj->id . '][' . $count . ']" name="field[' . $obj->id . '][]" value="' . stripslashes( esc_attr( $value ) ) . '" ' . $checked . '> ' . stripslashes( esc_attr( $value ) ) . '</label><br />';
                                           	$count++;
                                        }
                                    ?>
								</td>
							</tr>
                        	<?php
						break;
						
						default :
							?>
							<tr valign="top">
								<th scope="row"><label for="field[<?php echo $obj->id; ?>]"><?php echo stripslashes( $obj->name ); ?></label></th>
								<td><input id="field[<?php echo $obj->id; ?>]" name="field[<?php echo $obj->id; ?>]" class="regular-text" type="text" value="<?php echo stripslashes( $obj->value ); ?>" /></td>
							</tr>
                        	<?php
						break;
					}
				}
			}
			
			if ( $count > 0 )
				echo '</div></div>';
		
			//echo '</div></div></div>';
		}
		echo '</table></div>';
		echo '<br class="clear"></div>';
		
		
		echo '</form>';
	}
}
?>