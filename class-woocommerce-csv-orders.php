<?php
/**
 * WcCsvOrders
 *
 * @package   WcCsvOrders
 * @author    Thiago Locks <thiago@zira.com.br>
 * @license   GPL-2.0+
 * @copyright 2013 Thiago Locks
 */


/**
 * WcCsvOrders
 *
 * @package WcCsvOrders
 * @author  Thiago Locks <thiago@zira.com.br>
 */
class WcCsvOrders {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	protected $version = '1.0.0';

	/**
	 * Unique identifier for your plugin.
	 *
	 * Use this value (not the variable name) as the text domain when internationalizing strings of text. It should
	 * match the Text Domain file header in the main plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'wc-csv-orders';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 * @return    void
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Add the bulk action
		add_action( 'load-edit.php', array( $this, 'do_bulk_action' ) );

		// Export
		add_filter( 'admin_footer', array( $this, 'add_bulk_actions' ) );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add extra bulk action options to export orders
	 *
	 * Using Javascript until WordPress core fixes: http://core.trac.wordpress.org/ticket/16031
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	function add_bulk_actions() {
		global $post_type;

		if ( 'shop_order' == $post_type ) {
			?>
			<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('<option>').val('export_csv_orders').text('<?php _e( 'Export to CSV', $this->plugin_slug )?>').appendTo("select[name='action']");
				jQuery('<option>').val('export_csv_orders').text('<?php _e( 'Export to CSV', $this->plugin_slug )?>').appendTo("select[name='action2']");
			});
			</script>
			<?php
		}
	}


	/**
	 * Process the bunk action
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	function do_bulk_action() {
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action = $wp_list_table->current_action();

		if ( $action === 'export_csv_orders' ) {

			$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );
			$csv = fopen( 'php://output', 'w' );

			foreach( $post_ids as $post_id ) {
				$order = new WC_Order( $post_id );
				$fields = array();

				# Order Number
				$fields[] = $order->get_order_number();

				# Order Date
				$fields[] = $order->order_date;

				# Client Name
				$fields[] = trim( $order->billing_first_name . ' ' . $order->billing_last_name );

				# Client E-mail
				$fields[] = $order->billing_email;

				$products = $order->get_items();
				foreach( $products as $product ) {
					$fields_product = $fields;

					# Product Name
					$fields_product[] = $product['name'];

					# Product Category
					$terms = array_map( function($i){ return $i->name; }, wp_get_post_terms( $product['product_id'], 'area' ) );
					$fields_product[] = implode( ', ', $terms );

					$fields_product[] = number_format( $product['line_total'], 2 );

					fputcsv( $csv, $fields_product );
				}

			}

			header( "Content-Type: text/csv;charset=utf-8" );
			header( "Content-Disposition: attachment;filename=\"orders.csv\"" );
			header( "Pragma: no-cache" );
			header( "Expires: 0" );

			die;

		}
		
	}


}