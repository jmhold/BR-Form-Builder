<?php

add_action( 'widgets_init', 'vfb_pro_register_widgets' );

function vfb_pro_register_widgets() {
	register_widget( 'VisualFormBuilder_Pro_Widget' );
}

/**
 * Class that builds our Import page
 * 
 * @since 1.7
 */
class VisualFormBuilder_Pro_Widget extends WP_Widget {
	public function __construct(){
		global $wpdb;
		
		/* Setup global database table names */
		$this->field_table_name 	= $wpdb->prefix . 'vfb_pro_fields';
		$this->form_table_name 		= $wpdb->prefix . 'vfb_pro_forms';
		$this->entries_table_name 	= $wpdb->prefix . 'vfb_pro_entries';
		
		$args = array(
			'classname' 	=> 'vfb_pro_widget_class',
			'description' 	=> 'Visual Form Builder Pro widget'
		);
		
		$this->WP_Widget( 'vfb_widget', 'Visual Form Builder Pro', $args );
	}
	
	public function form( $instance ) {
		global $wpdb;
		
		// Query to get all forms
		$order = sanitize_sql_orderby( 'form_id ASC' );
		$where = apply_filters( 'vfb_pre_get_forms_widget', '' );
		$forms = $wpdb->get_results( "SELECT * FROM $this->form_table_name WHERE 1=1 $where ORDER BY $order" );
		
		$instance = wp_parse_args( (array) $instance ); 
		?>
		<select name="<?php echo $this->get_field_name( 'id' ); ?>">
		<?php
			foreach ( $forms as $form ) {
				echo '<option value="' . $form->form_id . '" id="' . $form->form_key . '"' . selected( $form->form_id, $instance['id'], 1 ) . '>' . stripslashes( $form->form_title ) . '</option>';
			}
		?>
		</select>
		<?php
	}
	
	public function widget( $args, $instance ) {
		extract( $args );
		
		echo $before_widget;
		
		// Create new class instance
		$template_tag = new Visual_Form_Builder_Pro();
		
		// Parse the arguments into an array
		$atts = wp_parse_args( $instance );
		
		// Print the output
		echo $template_tag->form_code( $atts );
		
		echo $after_widget;
	}
	
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		
		$instance['id'] = $new_instance['id'];
		
		return $instance;
	}
}
?>