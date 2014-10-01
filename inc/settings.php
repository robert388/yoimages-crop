<?php
if (! defined ( 'ABSPATH' )) {
	die ( 'No script kiddies please!' );
}
class WprieSettingsPage {
	
	private $options;
	
	public function __construct() {
		add_action ( 'admin_menu', array ( $this, 'add_plugin_page_menu_item' ) );
		add_action ( 'admin_init', array ( $this, 'init_admin_page' ) );
	}
	
	public function add_plugin_page_menu_item() {
		add_options_page( __( 'YoImages settings', WPRIE_DOMAIN ), 'YoImages', 'manage_options', 'wprie-settings', array( $this, 'create_admin_page' ) );
	}
	
	public function create_admin_page() {
		if ( !current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$this->options = get_option( 'wprie_settings' );
		?>
		<div class="wrap">
			<?php screen_icon(); ?>
			<h2><?php _e( 'YoImages settings', WPRIE_DOMAIN ); ?></h2>
			<form method="post" action="options.php">
			<?php
				settings_fields( 'wprie_crop_options_group' );   
				do_settings_sections( 'wprie-settings' );
				submit_button(); 
			?>
			</form>
		</div>
		<?php
	}

	public function init_admin_page() {
		register_setting( 'wprie_crop_options_group', 'wprie_settings', array( $this, 'sanitize' ) );
		
		add_settings_section( 'wprie_crop_options_section', __( 'Cropping', WPRIE_DOMAIN ), array( $this, 'print_crop_options_section_info' ), 'wprie-settings' );
		add_settings_field( 'cropping_is_active', __( 'Active', WPRIE_DOMAIN ), array( $this, 'cropping_is_active_callback' ), 'wprie-settings', 'wprie_crop_options_section' );
		add_settings_field( 'crop_qualities', __( 'Crop qualities', WPRIE_DOMAIN), array( $this, 'crop_qualities_callback' ), 'wprie-settings', 'wprie_crop_options_section' );
		
		add_settings_section( 'wprie_alt_options_section', __( 'SEO for images', WPRIE_DOMAIN ), array( $this, 'print_alt_options_section_info' ), 'wprie-settings' );
		add_settings_field( 'alt_is_active', __( 'Active', WPRIE_DOMAIN ), array( $this, 'alt_is_active_callback' ), 'wprie-settings', 'wprie_alt_options_section' );
		add_settings_field( 'alt_change_image_title', __( 'Change image title', WPRIE_DOMAIN ), array( $this, 'alt_change_image_title_callback' ), 'wprie-settings', 'wprie_alt_options_section' );
		add_settings_field( 'alt_change_image_alt', __( 'Change image alt attribute', WPRIE_DOMAIN ), array( $this, 'alt_change_image_alt_callback' ), 'wprie-settings', 'wprie_alt_options_section' );
		add_settings_field( 'alt_change_image_filename', __( 'Change image file name', WPRIE_DOMAIN ), array( $this, 'alt_change_image_filename_callback' ), 'wprie-settings', 'wprie_alt_options_section' );

	}
	
	public function print_crop_options_section_info() {
		print __('Enter your cropping settings here below', WPRIE_DOMAIN );
	}

	public function print_alt_options_section_info() {
		print __('Enter your images SEO settings here below', WPRIE_DOMAIN );
	}
	
	public function cropping_is_active_callback() {
		printf(
			'<input type="checkbox" id="cropping_is_active" name="wprie_settings[cropping_is_active]" value="TRUE" %s />',
			$this->options['cropping_is_active'] ? 'checked="checked"' : ( WPRIE_DEFAULT_CROP_ENABLED && ! isset( $this->options['cropping_is_active'] ) ? 'checked="checked"' : '' )
		);
	}

	public function crop_qualities_callback() {
		printf(
			'<input type="text" id="crop_qualities" name="wprie_settings[crop_qualities]" value="%s" />
			<p class="description">' . __( 'Comma separated list of crop quality values', WPRIE_DOMAIN ) . '</p>',
			! empty( $this->options['crop_qualities'] ) ? esc_attr( implode( ',', $this->options['crop_qualities'] ) ) : implode( ',', unserialize( WPRIE_DEFAULT_CROP_QUALITIES ) )
		);
	}

	public function alt_is_active_callback() {
		printf(
			'<input type="checkbox" id="alt_is_active" name="wprie_settings[alt_is_active]" value="TRUE" %s />',
			$this->options['alt_is_active'] ? 'checked="checked"' : ( WPRIE_DEFAULT_ALT_ENABLED && ! isset( $this->options['alt_is_active'] ) ? 'checked="checked"' : '' )
		);
	}

	public function alt_change_image_title_callback() {
		printf(
			'<input type="checkbox" id="alt_change_image_title" name="wprie_settings[alt_change_image_title]" value="TRUE" %s />',
			$this->options['alt_change_image_title'] ? 'checked="checked"' : ( WPRIE_DEFAULT_ALT_CHANGE_IMAGE_TITLE && ! isset( $this->options['alt_change_image_title'] ) ? 'checked="checked"' : '' )
		);
	}

	public function alt_change_image_alt_callback() {
		printf(
			'<input type="checkbox" id="alt_change_image_alt" name="wprie_settings[alt_change_image_alt]" value="TRUE" %s />',
			$this->options['alt_change_image_alt'] ? 'checked="checked"' : ( WPRIE_DEFAULT_ALT_CHANGE_IMAGE_ALT && ! isset( $this->options['alt_change_image_alt'] ) ? 'checked="checked"' : '' )
		);
	}
	
	public function alt_change_image_filename_callback() {
		printf(
			'<input type="checkbox" id="alt_change_image_filename" name="wprie_settings[alt_change_image_filename]" value="TRUE" %s />',
			$this->options['alt_change_image_filename'] ? 'checked="checked"' : ( WPRIE_DEFAULT_ALT_CHANGE_IMAGE_FILENAME && ! isset( $this->options['alt_change_image_filename'] ) ? 'checked="checked"' : '' )
		);
	}
	
	public function sanitize( $input ) {
		$new_input = array();
		if( $input['cropping_is_active'] === 'TRUE' || $input['cropping_is_active'] === TRUE ) {
			$new_input['cropping_is_active'] = TRUE;
		} else {
			$new_input['cropping_is_active'] = FALSE;
		}
		if( isset( $input['crop_qualities'] ) ) {
			if ( is_array( $input['crop_qualities'] ) ) {
				$crop_qualities = $input['crop_qualities'];
			} else {
				$crop_qualities = explode( ',', $input['crop_qualities'] );
			}
			$crop_qualities_count = 0;
			foreach ($crop_qualities AS $index => $value) {
				$crop_quality_value = ( int ) $value;
				if ( $crop_quality_value > 0 && $crop_quality_value <= 100 ) {
					$crop_qualities_arr[$crop_qualities_count] = $crop_quality_value;
					$crop_qualities_count++;
				}
			}
			if( empty( $crop_qualities_arr ) ) {
				add_settings_error( 'wprie_crop_options_group', 'crop_qualities', __( 'Crop qualities value is not valid, using default:', WPRIE_DOMAIN ) . ' ' . implode( ',', unserialize( WPRIE_DEFAULT_CROP_QUALITIES ) ), 'error' );
				$new_input['crop_qualities'] = unserialize( WPRIE_DEFAULT_CROP_QUALITIES );
			} else {
				$crop_qualities_arr = array_unique( $crop_qualities_arr );
				rsort( $crop_qualities_arr );
				$new_input['crop_qualities'] = $crop_qualities_arr;
			}
		} else {
			$new_input['crop_qualities'] = unserialize( WPRIE_DEFAULT_CROP_QUALITIES );
		}
		if( $input['alt_is_active'] === 'TRUE' || $input['alt_is_active'] === TRUE ) {
			$new_input['alt_is_active'] = TRUE;
		} else {
			$new_input['alt_is_active'] = FALSE;
		}
		if( $input['alt_change_image_title'] === 'TRUE' || $input['alt_change_image_title'] === TRUE ) {
			$new_input['alt_change_image_title'] = TRUE;
		} else {
			$new_input['alt_change_image_title'] = FALSE;
		}
		if( $input['alt_change_image_alt'] === 'TRUE' || $input['alt_change_image_alt'] === TRUE ) {
			$new_input['alt_change_image_alt'] = TRUE;
		} else {
			$new_input['alt_change_image_alt'] = FALSE;
		}
		if( $input['alt_change_image_filename'] === 'TRUE' || $input['alt_change_image_filename'] === TRUE ) {
			$new_input['alt_change_image_filename'] = TRUE;
		} else {
			$new_input['alt_change_image_filename'] = FALSE;
		}
		return $new_input;
	}
	
}

new WprieSettingsPage ();