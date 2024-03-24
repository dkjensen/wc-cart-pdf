<?php
/**
 * Settings.
 *
 * @package dkjensen/wc-cart-pdf
 */

/**
 * WC_Cart_PDF_Settings class.
 */
class WC_Cart_PDF_Settings extends WC_Integration {

	/**
	 * Initialize the integration.
	 */
	public function __construct() {
		$this->id                 = 'wc_cart_pdf';
		$this->method_title       = __( 'Cart PDF', 'wc-cart-pdf' );
		$this->method_description = __( 'WC Cart PDF allows customers to download their cart as a PDF.', 'wc-cart-pdf' );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		if ( isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] && isset( $_GET['section'] ) && 'wc_cart_pdf' === $_GET['section'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		// Save settings if the we are in the right section.
		if ( isset( $_POST['section'] ) && $this->id === $_POST['section'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		wp_enqueue_style( 'wc-cart-pdf-admin-settings', WC_CART_PDF_URL . 'assets/css/settings.css', array(), WC_CART_PDF_VER );
		wp_register_script( 'wc-cart-pdf-admin-settings', WC_CART_PDF_URL . 'assets/js/settings.js', array(), WC_CART_PDF_VER, true );
		wp_localize_script(
			'wc-cart-pdf-admin-settings',
			'wc_cart_pdf_settings',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'wc-cart-pdf-preview' ),
				'worker'   => WC_CART_PDF_URL . 'assets/js/worker.js',
			)
		);
		wp_enqueue_script( 'wc-cart-pdf-admin-settings' );
	}

	/**
	 * Output the admin options table.
	 *
	 * @return void
	 */
	public function admin_options() {
		echo '<h2>' . esc_html( $this->get_method_title() ) . '</h2>';
		echo wp_kses_post( wpautop( $this->get_method_description() ) );
		echo '<div><input type="hidden" name="section" value="' . esc_attr( $this->id ) . '" /></div>';
		echo '<div id="wc-cart-pdf-settings">';
		echo '<div><table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table></div>';  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<div><div id="wc-cart-pdf-preview-notices"></div><p><button id="wc-cart-pdf-refresh-preview" class="button button-secondary">' . esc_html__( 'Refresh Preview', 'wc-cart-pdf' ) . '</button></p><canvas id="wc-cart-pdf-preview"></canvas></div>';
		echo '</div>';
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public static function get_settings_keys() {
		return array(
			'button_label',
			'logo',
			'logo_width',
			'logo_alignment',
			'open_pdf',
			'copy_admin',
			'show_checkout',
			'capture_customer',
			'unique_increment',
			'modal_capture',
		);
	}

	/**
	 * Initialise Settings.
	 *
	 * Store all settings and make sure the $settings array is either the default or the settings stored in the database.
	 *
	 * @return void
	 */
	public function init_settings() {
		$settings_keys = self::get_settings_keys();

		$this->settings = array();

		foreach ( $settings_keys as $key ) {
			$this->settings[ $key ] = get_option( 'wc_cart_pdf_' . $key, null );
		}

		$form_fields = $this->get_form_fields();

		// Replace null values with defaults.
		foreach ( $form_fields as $key => $field ) {
			if ( is_null( $this->settings[ $key ] ) ) {
				$this->settings[ $key ] = isset( $field['default'] ) ? $field['default'] : '';
			}
		}
	}

	/**
	 * Processes and saves options.
	 * If there is an error thrown, will continue to save and validate fields, but will leave the erroring field out.
	 *
	 * @return bool was anything saved?
	 */
	public function process_admin_options() {
		$this->init_settings();

		$post_data = $this->get_post_data();
		$saved     = false;

		foreach ( $this->get_form_fields() as $key => $field ) {
			if ( 'title' !== $this->get_field_type( $field ) ) {
				try {
					$this->settings[ $key ] = $this->get_field_value( $key, $field, $post_data );

					$_saved = update_option( $this->get_field_key( $key ), $this->settings[ $key ] );

					if ( $_saved ) {
						$saved = true;
					}
				} catch ( Exception $e ) {
					$this->add_error( $e->getMessage() );
				}
			}
		}

		return $saved;
	}

	/**
	 * Validate Checkbox Field.
	 *
	 * If not set, return "", otherwise return "1".
	 *
	 * @param  string $key Field key.
	 * @param  string $value Posted Value.
	 * @return string
	 */
	public function validate_checkbox_field( $key, $value ) {
		return ! is_null( $value ) ? '1' : '';
	}

	/**
	 * Generate Checkbox HTML.
	 *
	 * @param string $key Field key.
	 * @param array  $data Field data.
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_checkbox_html( $key, $data ) {
		$field_key = $this->get_field_key( $key );
		$defaults  = array(
			'title'             => '',
			'label'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array(),
		);

		$data = wp_parse_args( $data, $defaults );

		if ( ! $data['label'] ) {
			$data['label'] = $data['title'];
		}

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field_key ); ?>"><?php echo wp_kses_post( $data['title'] ); ?> <?php echo $this->get_tooltip_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></label>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
					<label for="<?php echo esc_attr( $field_key ); ?>">
					<input <?php disabled( $data['disabled'], true ); ?> class="<?php echo esc_attr( $data['class'] ); ?>" type="checkbox" name="<?php echo esc_attr( $field_key ); ?>" id="<?php echo esc_attr( $field_key ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" value="1" <?php checked( $this->get_option( $key ), '1' ); ?> <?php echo $this->get_custom_attribute_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> /> <?php echo wp_kses_post( $data['label'] ); ?></label><br/>
					<?php echo $this->get_description_html( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get field key.
	 *
	 * @param  string $key Field key.
	 * @return string
	 */
	public function get_field_key( $key ) {
		return $this->id . '_' . $key;
	}

	/**
	 * Initialize form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'button_label' => array(
				'title'    => __( 'Button label', 'wc-cart-pdf' ),
				'desc'     => __( 'Text that is displayed on the button which generates the PDF.', 'wc-cart-pdf' ),
				'id'       => 'wc_cart_pdf_button_label',
				'type'     => 'text',
				'default'  => __( 'Download Cart as PDF', 'wc-cart-pdf' ),
			),
			'logo' => array(
				'title'    => __( 'Logo URL', 'wc-cart-pdf' ),
				'desc'     => __( 'Image URL of logo for the cart PDF, must live on current server.', 'wc-cart-pdf' ),
				'id'       => 'wc_cart_pdf_logo',
				'type'     => 'text',
				'default'  => get_option( 'woocommerce_email_header_image' ),
			),
			'logo_width' => array(
				'title'    => __( 'Logo width', 'wc-cart-pdf' ),
				'desc'     => __( 'Logo size used for the cart PDF.', 'wc-cart-pdf' ),
				'id'       => 'wc_cart_pdf_logo_width',
				'type'     => 'number',
				'default'  => 400,
				'desc_tip' => true,
			),
			'logo_alignment' => array(
				'title'    => __( 'Logo alignment', 'wc-cart-pdf' ),
				'desc'     => __( 'Alignment of the logo within header of the cart PDF.', 'wc-cart-pdf' ),
				'id'       => 'wc_cart_pdf_logo_alignment',
				'type'     => 'select',
				'default'  => 'center',
				'options'  => array(
					'left'   => __( 'Left', 'wc-cart-pdf' ),
					'center' => __( 'Center', 'wc-cart-pdf' ),
					'right'  => __( 'Right', 'wc-cart-pdf' ),
				),
			),
			'open_pdf' => array(
				'title'    => __( 'Open PDF in new tab instead of downloading', 'wc-cart-pdf' ),
				'desc'     => '',
				'id'       => 'wc_cart_pdf_open_pdf',
				'type'     => 'checkbox',
				'default'  => '',
			),
			'copy_admin' => array(
				'title'    => __( 'Send a copy of PDF to admin via email', 'wc-cart-pdf' ),
				'desc'     => '',
				'id'       => 'wc_cart_pdf_copy_admin',
				'type'     => 'checkbox',
				'default'  => '',
			),
			'show_checkout' => array(
				'title'        => __( 'Show Download Cart as PDF on checkout', 'wc-cart-pdf' ),
				'description'  => 'If using WooCommerce cart and checkout blocks, manually add the "Cart PDF Button" block to the checkout page.',
				'id'           => 'wc_cart_pdf_show_checkout',
				'type'         => 'checkbox',
				'default'      => '',
			),
			'capture_customer' => array(
				'title'    => __( 'Capture customer information on checkout', 'wc-cart-pdf' ),
				'desc'     => '',
				'id'       => 'wc_cart_pdf_capture_customer',
				'type'     => 'checkbox',
				'default'  => '',
			),
			'unique_increment' => array(
				'title'    => __( 'Display unique generated PDF number', 'wc-cart-pdf' ),
				'desc'     => '',
				'id'       => 'wc_cart_pdf_unique_increment',
				'type'     => 'checkbox',
				'default'  => '',
			),
			'modal_capture' => array(
				'title'    => __( 'Require customer to populate their information before downloading PDF', 'wc-cart-pdf' ),
				'desc'     => '',
				'id'       => 'wc_cart_pdf_modal_capture',
				'type'     => 'checkbox',
				'default'  => '',
			),
		);
	}
}