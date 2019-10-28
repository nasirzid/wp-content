<?php
/*
	Plugin Name: Contact Form 7 Storage
	Description: Store all Contact Form 7 submissions (including attachments) in your WordPress dashboard.
	Plugin URI: https://preseto.com/plugins/contact-form-7-storage
	Author: Kaspars Dambis
	Author URI: https://kaspars.net
	Version: 1.6.1
	Tested up to: 4.9.1
	License: GPL2
	Text Domain: cf7-storage
*/


cf7_storage::instance();


class cf7_storage {

	private static $post_type = 'cf7_entry';
	private $query_args = array();
	private $current_entry_id;
	private $uploads_dir;
	private $uploads_dir_url;


	public static function instance() {

		static $instance;

		if ( ! $instance )
			$instance = new self();

		return $instance;

	}


	private function __construct() {

		$this->setup();

		add_action( 'init', array( $this, 'init_l10n' ) );

		// Define storage post type
		add_action( 'init', array( $this, 'storage_init' ) );

		// Wait for CF7 to be loaded to call the correct capture hook
		add_action( 'plugins_loaded', array( $this, 'init_capture' ) );

		// Add admin view
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Return entry ID and entry URL as special tags
		add_filter( 'wpcf7_special_mail_tags', array( $this, 'special_tags_entry' ), 10, 3 );

		// Notify users running old versions of Contact Form 7
		add_action( 'cf7_storage_admin_notices', array( $this, 'cf7_upgrade_notice' ) );

		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );

		// Ensure that [file-*] points to the actual uploaded URL in the body
		add_filter( 'wpcf7_mail_components', array( $this, 'wpcf7_mail_components' ), 10, 2 );

	}


	private function setup() {

		$wp_uploads_dir = wp_upload_dir();

		$this->uploads_dir = sprintf( '%s/cf7-storage', $wp_uploads_dir['basedir'] );
		$this->uploads_dir_url = sprintf( '%s/cf7-storage', $wp_uploads_dir['baseurl'] );

	}


	public function get_storage_file_path( $path, $entry_id = null ) {

		if ( ! isset( $entry_id ) ) {
			$entry_id = $this->current_entry_id;
		}

		return sprintf(
				'%s/%d-%s.%s',
				$this->uploads_dir,
				$entry_id,
				md5( $path ),
				pathinfo( $path, PATHINFO_EXTENSION )
			);

	}

	public function get_storage_file_url( $path, $entry_id = null ) {

		if ( ! isset( $entry_id ) ) {
			$entry_id = $this->current_entry_id;
		}

		return sprintf(
				'%s/%d-%s.%s',
				$this->uploads_dir_url,
				$entry_id,
				md5( $path ),
				pathinfo( $path, PATHINFO_EXTENSION )
			);

	}


	function wpcf7_mail_components( $components, $form ) {

		if ( isset( $components['attachments'] ) && ! empty( $components['attachments'] ) ) {
			$components['body'] = $this->mail_replace_file_urls( $components['body'], $components['attachments'] );
		}

		return $components;

	}


	function mail_replace_file_urls( $body, $attacments ) {

		if ( empty( $attacments ) ) {
			return $body;
		}

		foreach ( $attacments as $attachment_path ) {
			$body = str_replace(
				basename( $attachment_path ),
				$this->get_storage_file_url( $attachment_path ),
				$body
			);
		}

		return $body;

	}


	function init_capture() {

		if ( ! defined( 'WPCF7_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, 'require_cf7' ) );
		} elseif ( class_exists( 'WPCF7_Mail', false ) ) {
			// for CF7 >= 3.9
			add_action( 'wpcf7_before_send_mail', array( $this, 'storage_capture' ), 100 );
		} else {
			// for CF7 < 3.9
			add_action( 'wpcf7_mail_sent', array( $this, 'storage_capture_legacy' ) );
		}

		// Include the automatic updater
		include plugin_dir_path( __FILE__ ) . 'lib/envato-automatic-plugin-update/envato-plugin-update.php';

		PresetoPluginUpdateEnvato::instance()->add_item( array(
				'id' => 7806229,
				'basename' => plugin_basename( __FILE__ )
			) );

	}


	function require_cf7() {

		$notice_screens = array(
			'plugins',
			'dashboard',
		);

		$screen = get_current_screen();

		if ( ! in_array( $screen->id, $notice_screens ) ) {
			return;
		}

		printf(
			'<div class="notice notice-error">
				<p>%s <a href="%s" class="button">%s</a></p>
			</div>',
			esc_html__( 'The Storage for Contact Form 7 plugin requires the Contact Form 7 plugin which currently isn\'t installed and activated.', 'cf7-storage' ),
			esc_url( admin_url( 'plugin-install.php?s=contact+form+7&tab=search' ) ),
			esc_html__( 'Search and Install', 'cf7-storage' )
		);

	}


	function init_l10n() {

		load_plugin_textdomain( 'cf7-storage', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}


	function storage_init() {

		register_post_type(
			self::$post_type,
			array(
				'public' => false,
				'label' => __( 'Entries', 'cf7-storage' ),
				'supports' => false,
			)
		);

	}

	function storage_capture_legacy( $cf7 ) {

		$mail = $cf7->compose_mail(
				$cf7->setup_mail_template( $cf7->mail, 'mail' ),
				false // Don't send
			);

		$entry_id = wp_insert_post( array(
				'post_title' => $mail['sender'],
				'post_type' => self::$post_type,
				'post_status' => 'publish',
				'post_parent' => $cf7->id,
				'post_content' => $mail['body']
			) );

		foreach ( $mail as $mail_field => $mail_value )
			add_post_meta( $entry_id, 'mail_' . $mail_field, $mail_value );

		// Store all the meta data
		foreach ( $cf7->posted_data as $key => $value )
			add_post_meta( $entry_id, 'cf7_' . $key, $value );

		// Store user browser, IP, referer
		$extra_meta = array(
			'http_user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ),
			'remote_addr' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ),
			'http_referer' => sanitize_text_field( $_SERVER['HTTP_REFERER'] ),
		);

		foreach ( $extra_meta as $key => $value )
			add_post_meta( $entry_id, $key, $value );

		// Store uploads permanently
		$uploads_stored = $this->store_uploaded_files( $entry_id, $cf7->uploaded_files );

		do_action( 'cf7_storage_capture', $entry_id, $cf7 );

	}


	function storage_capture( $cf7 ) {

		if ( apply_filters( 'cf7_storage_skip_capture', false, $cf7 ) ) {
			return;
		}

		$submission = WPCF7_Submission::get_instance();

		// Store this entry and get its ID
		$entry_id = wp_insert_post( array(
				'post_type' => self::$post_type,
				'post_status' => 'publish',
				'post_parent' => $cf7->id()
			) );

		do_action( 'cf7_storage_pre_capture', $entry_id, $cf7 );

		// Make this entry ID available to this class
		$this->set_entry_id( $entry_id );

		// Get the mail template and settings of this contact form
		$template = $cf7->prop( 'mail' );

		// Replace all variables with form values
		$mail = wpcf7_mail_replace_tags(
				$template,
				array(
					'html' => $template['use_html'],
					'exclude_blank' => $template['exclude_blank']
				)
			);

		// Ensure that all files point to the uploaded file
		$mail['body'] = $this->mail_replace_file_urls( $mail['body'], $submission->uploaded_files() );

		// Update post title and body content
		wp_update_post( array(
				'ID' => $entry_id,
				'post_title' => $mail['sender'],
				'post_content' => $mail['body']
			) );

		// Store all field values that were mailed
		foreach ( $mail as $mail_field => $mail_value )
			add_post_meta( $entry_id, 'mail_' . $mail_field, $mail_value, true );

		$form_fields = $cf7->scan_form_tags();
		$posted_data_raw = $submission->get_posted_data();
		$posted_fields = array();

		foreach ( $form_fields as $field ) {

			$field_name = $field['name'];

			if ( isset( $posted_data_raw[ $field_name ] ) ) {

				$field_value = $posted_data_raw[ $field_name ];

				// Store the field value
				$posted_fields[ $field_name ] = $field_value;

				// Store the labels of the piped data too
				if ( ! empty( $field['raw_values'] ) ) {

					$piped_options = array();

					// Convert pipe string pairs into value => label mapping
					foreach ( $field['raw_values'] as $piped_option ) {
						$option_pair = explode( '|', $piped_option );

						if ( isset( $option_pair[1] ) ) {
							$piped_options[ $option_pair[1] ] = $option_pair[0];
						} else {
							$piped_options[ $option_pair[0] ] = $option_pair[0];
						}
					}

					// Add the label as an additional field
					if ( is_string( $field_value ) && isset( $piped_options[ $field_value ] ) ) {
						$posted_fields[ $field_name . '-pipe-label' ] = $piped_options[ $field_value ];
					} elseif ( is_array( $field_value ) ) {
						// Support checkboxes, radios, etc.
						$posted_fields[ $field_name . '-pipe-label' ] = array_intersect_key( $piped_options, array_flip( $field_value ) );
					}

				}

			}

		}

		// Store field values
		add_post_meta( $entry_id, 'form_fields', $posted_fields );

		// Store user browser, IP, referer
		$extra_meta = array(
				'http_user_agent' => sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] ),
				'remote_addr' => sanitize_text_field( $_SERVER['REMOTE_ADDR'] ),
				'http_referer' => sanitize_text_field( $_SERVER['HTTP_REFERER'] ),
			);

		$unit_tag = $submission->get_meta( 'unit_tag' );

		// Store the post/page ID where the form was submitted
		if ( $unit_tag && preg_match( '/^wpcf7-f(\d+)-p(\d+)-o(\d+)$/', $unit_tag, $unit_matches ) ) {
			$extra_meta['post_id'] = absint( $unit_matches[2] );
		}

		if ( is_user_logged_in() ) {
			$extra_meta['user_id'] = get_current_user_id();
		}

		foreach ( $extra_meta as $key => $value ) {
			add_post_meta( $entry_id, $key, $value );
		}

		// Store uploads permanently
		$uploads_stored = $this->store_uploaded_files( $entry_id, $submission->uploaded_files() );

		do_action( 'cf7_storage_capture', $entry_id, $cf7 );

	}


	function store_uploaded_files( $entry_id, $files = array() ) {

		// Escape all backslashes so they don't get removed in DB
		foreach ( $files as &$file ) {
			$file = str_replace( '\\', '/', $file );
		}

		// Make sure we store the information about attachments even if they are not being sent via mail
		if ( ! empty( $files ) ) {
			update_post_meta( $entry_id, 'mail_attachments', $files );
		}

		if ( ! is_dir( $this->uploads_dir ) ) {
			@mkdir( $this->uploads_dir );
		}

		$htaccess_file = sprintf( '%s/.htaccess', $this->uploads_dir );

		// Make sure that uploads directory is protected from listing
		if ( ! file_exists( $htaccess_file ) ) {
			file_put_contents( $htaccess_file, 'Options -Indexes' );
		}

		$uploads_stored = array();

		foreach ( $files as $name => $path ) {

			if ( ! isset( $_FILES[ $name ] ) ) {
				continue;
			}

			$destination = $this->get_storage_file_path( $path, $entry_id );

			$uploads_stored[] = $destination;

			// Copy to a permanant storage location
			@copy(
				$path,
				$destination
			);

		}

		// Store information about uploads that were stored
		add_post_meta( $entry_id, 'cf7_storage_uploads_stored', $uploads_stored );

		return $uploads_stored;

	}


	function set_entry_id( $entry_id ) {

		$this->current_entry_id = $entry_id;

	}


	function special_tags_entry( $replaced, $tagname, $html ) {

		switch ( $tagname ) {

			case 'storage_entry_id':

				if ( $this->current_entry_id ) {
					return $this->current_entry_id;
				}

				break;

			case 'storage_entry_url':

				if ( $this->current_entry_id ) {
					return add_query_arg(
						array(
							'page' => 'cf7_storage',
							'action' => 'view',
							'post_id' => $this->current_entry_id
						),
						admin_url( 'admin.php' )
					);
				}

				break;

		}

		return $replaced;

	}


	function admin_menu() {

		// Register a subpage for Contact Form 7
		$cf7_subpage = add_submenu_page(
				'wpcf7',
				__( 'Contact Form Entries', 'cf7-storage' ),
				__( 'Entries', 'cf7-storage' ),
				'wpcf7_read_contact_forms',
				'cf7_storage',
				array( $this, 'admin_page' )
			);

		add_action( 'load-' . $cf7_subpage, array( $this, 'admin_actions_process' ) );

	}


	function admin_actions_process() {

		global $wpdb;

		// Enqueue our admin style and scripts
		wp_enqueue_style( 'cf7s-style', plugins_url( 'assets/css/cf7s-admin.css', __FILE__ ) );
		wp_enqueue_script( 'cf7s-js', plugins_url( 'assets/js/cf7s-admin.js', __FILE__ ), array( 'jquery' ), null, true );

		if ( ! isset( $_REQUEST['action'] ) || empty( $_REQUEST['action'] ) || 'view' === $_REQUEST['action'] ) {
			return;
		}

		// Make sure this is a valid admin request
		check_admin_referer( 'bulk-form-entries' );

		$action = $_REQUEST['action'];

		if ( isset( $_REQUEST['export-entries'] ) ) {
			$action = 'export';
		}

		// Export ALL entries
		if ( 'export' === $action && empty( $_REQUEST['form-entry'] ) && empty( $_REQUEST['post_id'] ) ) {

			// Parse request to select posts for export
			$query_args = $this->get_query_args();

			// Export ALL entries
			$query_args['posts_per_page'] = -1;

			$cf7_storage = cf7_storage::instance();
			$cf7_storage->export_entries( $query_args );

		}

		if ( ! empty( $_REQUEST['s'] ) )
			$action = 'search';

		if ( isset( $_REQUEST['delete_all'] ) )
			$action = 'delete';

		$action_whitelist = array(
				'export',
				'trash',
				'delete',
				'untrash',
				'search',
			);

		if ( ! in_array( $action, $action_whitelist ) )
			return;

		$sendback = esc_url_raw( remove_query_arg(
				array(
					'export-entries',
					'export-csv-delimiter',
					'export',
					'trashed',
					'untrashed',
					'deleted',
					'delete_all',
					'locked',
					'ids',
					'action',
					'action2',
					'post_id',
					'_wp_http_referer',
					'_wpnonce',
				)
			) );

		$post_ids = array();

		// Collect the post IDs we need to act on
		if ( 'delete' == $action && ! isset( $_REQUEST['post_id'] ) ) {

			// Get all posts in trash
			$post_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = %s",
					self::$post_type,
					'trash'
				) );

		} elseif ( isset( $_REQUEST['ids'] ) && ! empty( $_REQUEST['ids'] ) ) {

			$post_ids = explode( ',', $_REQUEST['ids'] );

		} elseif ( isset( $_REQUEST['post_id'] ) && ! empty( $_REQUEST['post_id'] ) ) {

			$post_ids = array( (int) $_REQUEST['post_id'] );

		} elseif ( isset( $_REQUEST['form-entry'] ) && ! empty( $_REQUEST['form-entry'] ) ) {

			$post_ids = array_map( 'intval', $_REQUEST['form-entry'] );

		}

		// No posts have been selected, bail out
		if ( empty( $post_ids ) ) {

			wp_redirect( $sendback );
			exit();

		}

		switch ( $action ) {

			case 'export' :

				// Parse request to select posts for export
				$query_args = $this->get_query_args();

				// Export ALL entries
				$query_args['posts_per_page'] = -1;
				$query_args['post__in'] = $post_ids;

				$cf7_storage = cf7_storage::instance();
				$cf7_storage->export_entries( $query_args );

				break;

			case 'trash' :

				foreach( $post_ids as $post_id ) {

					if ( ! current_user_can( 'delete_post', $post_id ) )
						wp_die( __( 'You are not allowed to move this item to Trash.', 'cf7-storage' ) );

					if ( ! wp_trash_post( $post_id ) )
						wp_die( __( 'Error moving an item to Trash.', 'cf7-storage' ) );

					$sendback = add_query_arg( array(
								'trashed' => true
							),
							$sendback
						);
				}

				break;

			case 'untrash':

				foreach ( $post_ids as $post_id ) {

					if ( ! current_user_can( 'delete_post', $post_id ) )
						wp_die( __( 'You are not allowed to restore this item from Trash.', 'cf7-storage' ) );

					if ( ! wp_untrash_post( $post_id ) )
						wp_die( __( 'Error in restoring an item from Trash.', 'cf7-storage' ) );

				}

				$sendback = add_query_arg(
						'untrashed',
						true,
						$sendback
					);

				break;

			case 'delete' :

				foreach ( $post_ids as $post_id ) {

					if ( ! current_user_can( 'delete_post', $post_id ) )
						wp_die( __( 'You are not allowed to delete this entry.', 'cf7-storage' ) );

					$attachments = get_post_meta( $post_id, 'mail_attachments', true );

					if ( is_array( $attachments ) && ! empty( $attachments ) ) {
						foreach ( $attachments as $attachment_path ) {
							$attachment_file = $this->get_storage_file_path( $attachment_path, $post_id );

							if ( file_exists( $attachment_file ) ) {
								unlink( $attachment_file );
							}
						}
					}

					if ( ! wp_delete_post( $post_id, true ) ) {
						wp_die( __( 'Error in deleting an entry.', 'cf7-storage' ) );
					}

				}

				$sendback = add_query_arg( array(
							'deleted' => true
						),
						$sendback
					);

				break;

		}

		wp_redirect( $sendback );
		exit();

	}


	function set_query_args() {

		$this->query_args = array(
				'post_type' => self::$post_type,
				'orderby' => 'date',
				'order' => 'DESC',
			);

		// Search entries
		if ( isset( $_REQUEST['s'] ) && ! empty( $_REQUEST['s'] ) )
			$this->query_args['s'] = $_REQUEST['s'];

		// Custom order by date
		if ( ! empty( $_REQUEST['order'] ) ) {

			if ( 'asc' == strtolower( $_REQUEST['order'] ) )
				$this->query_args['order'] = 'ASC';
			elseif ( 'desc' == strtolower( $_REQUEST['order'] ) )
				$this->query_args['order'] = 'DESC';

		}

		// Filter by contact form
		if ( isset( $_REQUEST['form_id'] ) && ! empty( $_REQUEST['form_id'] ) )
			$this->query_args['post_parent'] = $_REQUEST['form_id'];

		// Filter by trash
		if ( isset( $_REQUEST['post_status'] ) && ! empty( $_REQUEST['post_status'] ) )
			$this->query_args['post_status'] = $_REQUEST['post_status'];

		// Filter by month of submission
		if ( isset( $_REQUEST['m'] ) && ! empty( $_REQUEST['m'] ) )
			$this->query_args['m'] = $_REQUEST['m'];

	}


	function get_query_args() {

		if ( empty( $this->query_args ) )
			$this->set_query_args();

		return $this->query_args;

	}


	function admin_page() {

		$action = 'index';

		if ( isset( $_REQUEST['action'] ) )
			$action = $_REQUEST['action'];

		switch ( $action ) {

			case 'view' :

				if ( ! isset( $_REQUEST['post_id'] ) )
					wp_die( __( 'Missing entry ID.', 'cf7-storage' ) );

				$post_id = $_REQUEST['post_id'];

				// We are viewing this entry now
				$this->admin_single_entry( $post_id );

				break;

			default :

				// Include our list view
				include plugin_dir_path( __FILE__ ) . 'inc/admin-list-view.php';

				// List of all entries
				$this->admin_entry_index();

				break;

		}

	}

	function admin_entry_index() {

		$list_table = new cf7_storage_list_table( self::$post_type );
		$list_table->prepare_items();

		?>
		<div class="wrap cf7-storage-entries">
			<div class="cf7-storage-header">
				<h1>
				<?php
					esc_html_e( 'Contact Form Entries', 'cf7-storage' );

					if ( ! empty( $_REQUEST['s'] ) ) {

						printf(
							'<span class="subtitle">%s</span>',
							esc_html( sprintf(
								__( 'Search results for "%s"', 'cf7-storage' ),
								$_REQUEST['s']
							) )
						);

					}
				?>
				</h1>

				<div class="cf7-storage-subscribe">
					<?php $current_user = wp_get_current_user(); ?>
					<form action="//preseto.us2.list-manage.com/subscribe/post?u=e8d173fc54c0fc4286a2b52e8&amp;id=0971ff11ca" method="post" target="_blank">
						<label>
							<?php esc_html_e( 'Subscribe to plugin updates', 'cf7-storage' ); ?>
							<input type="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" name="EMAIL" class="input-email" placeholder="Enter your email" required>
						</label>
						<input type="submit" value="<?php esc_attr_e( 'Subscribe', 'cf7-storage' ); ?>" name="subscribe" class="button">
					</form>
				</div>
			</div>

			<?php do_action( 'cf7_storage_admin_notices' ); ?>

			<?php $list_table->views(); ?>

			<form method="get" action="">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>" />
				<?php
					$list_table->search_box( __( 'Search Entries', 'cf7-storage' ), self::$post_type );
					$list_table->display();
				?>
			</form>

		</div>
		<?php

	}


	function admin_single_entry( $post_id ) {

		// Make sure we cast it to an integer value
		$post_id = intval( $post_id );

		$post = get_post( $post_id );

		if ( empty( $post ) )
			wp_die( __( 'This contact form submission doesn\'t exist!', 'cf7-storage' ) );

		if ( $post->post_type !== self::$post_type )
			return;

		// Prepare links to attachments
		$attachments = get_post_meta( $post->ID, 'mail_attachments', true );

		if ( ! empty( $attachments ) ) {

			$attachment_list = array();

			foreach ( $attachments as $url ) {

				$attachment_list[] = sprintf(
					'<li><a href="%s" target="_blank">%s</a></li>',
					esc_url( $this->get_storage_file_url( $url, $post_id ) ),
					esc_html( basename( $url ) )
				);

			}

			$maybe_attachments = sprintf(
					'<ul>%s<ul>',
					implode( '', $attachment_list )
				);

		} else {

			$maybe_attachments = _x( 'None', 'No attachments found', 'cf7-storage' );

		}

		$timestamp = strtotime( $post->post_date );

		$rows = array(
			'form-link' => array(
				'label' => __( 'Contact Form', 'cf7-storage' ),
				'value' => sprintf(
							'<a href="%s">%s</a>',
							admin_url( sprintf( 'admin.php?page=wpcf7&post=%d&action=edit', $post->post_parent ) ),
							esc_html( get_the_title( $post->post_parent ) )
					)
			),
			'from' => array(
				'label' => __( 'From', 'cf7-storage' ),
				'value' => esc_html( get_post_meta( $post->ID, 'mail_sender', true ) )
			),
			'to' => array(
				'label' => __( 'To', 'cf7-storage' ),
				'value' => esc_html( get_post_meta( $post->ID, 'mail_recipient', true ) )
			),
			'date' => array(
				'label' => __( 'Date', 'cf7-storage' ),
				'value' => esc_html( sprintf(
					'%s %s',
					date_i18n( get_option( 'date_format' ), $timestamp ),
					date_i18n( get_option( 'time_format' ), $timestamp )
				) )
			),
			'subject' => array(
				'label' => __( 'Subject', 'cf7-storage' ),
				'value' => esc_html( get_post_meta( $post->ID, 'mail_subject', true ) )
			),
			'body' => array(
				'label' => __( 'Message', 'cf7-storage' ),
				'value' => sprintf(
					'<div class="body-content-wrap">%s</div>',
					apply_filters( 'the_content', $post->post_content )
				)
			),
			'attachments' => array(
				'label' => __( 'Attachments', 'cf7-storage' ),
				'value' => $maybe_attachments
			)
		);

		// Allow other plugins to add more elements to our message view
		$rows = apply_filters( 'cf7_entry_rows', $rows, $post );

		$rows_html = array();

		foreach ( $rows as $row_id => $row_elements ) {

			$rows_html[] = sprintf(
				'<tr class="cf7s-%s">
					<th>%s:</th>
					<td>%s</td>
				</tr>',
				esc_attr( $row_id ),
				esc_html( $row_elements[ 'label' ] ),
				$row_elements[ 'value' ]
			);

		}

		// Get raw form fields
		$form_fields = get_post_meta( $post->ID, 'form_fields', true );
		$fields_html = array();

		if ( is_array( $form_fields ) && ! empty( $form_fields ) ) {

			foreach ( $form_fields as $field_key => $field_value ) {

				if ( is_array( $field_value ) )
					$field_value = implode( ', ', $field_value );

				$fields_html[] = sprintf(
						'<tr class="cf7s-form-field">
							<th>%s:</th>
							<td>%s</td>
						</tr>',
						esc_html( $field_key ),
						esc_html( $field_value )
					);

			}

		} else {

			$fields_html[] = sprintf(
					'<tr class="cf7s-form-fields-empty">
						<td>%s</td>
					</tr>',
					esc_html__( 'No field values were captured.', 'cf7-storage' )
				);

		}

		$meta_rows = array();

		if ( $on_post_id = esc_html( get_post_meta( $post->ID, 'post_id', true ) ) ) {

			$on_post_type_label = __( 'Post', 'cf7-storage' );
			$on_post_type_object = get_post_type_object( get_post_type( $on_post_id ) );

			if ( $on_post_type_object )
				$on_post_type_label = $on_post_type_object->labels->singular_name;

			$meta_rows['referer'] = array(
					'label' => __( 'Referer', 'cf7-storage' ),
					'value' => sprintf(
						'%s: <a href="%s" target="_blank">%s</a>',
						esc_html( $on_post_type_label ),
						get_permalink( $on_post_id ),
						esc_html( get_the_title( $on_post_id ) )
					)
				);

		} else {

			$referer = get_post_meta( $post->ID, 'http_referer', true );

			$meta_rows['referer'] = array(
					'label' => __( 'Referer', 'cf7-storage' ),
					'value' => sprintf(
						'%s &mdash; <a href="%s" target="_blank">%s</a>',
						esc_html( $referer ),
						esc_url( $referer ),
						__( 'Visit', 'cf7-storage' )
					)
				);

		}

		$meta_rows['user-agent'] = array(
				'label' => __( 'User agent', 'cf7-storage' ),
				'value' => esc_html( get_post_meta( $post->ID, 'http_user_agent', true ) )
			);

		$remote_addr = get_post_meta( $post->ID, 'remote_addr', true );

		$meta_rows['remote_addr'] = array(
				'label' => __( 'IP Address', 'cf7-storage' ),
				'value' => sprintf(
					'<a href="http://whois.arin.net/rest/ip/%s" target="_blank">%s</a>',
					esc_attr( $remote_addr ),
					esc_html( $remote_addr )
				)
			);

		// Allow other plugins to add more elements to meta information
		$meta_rows = apply_filters( 'cf7_entry_rows_meta', $meta_rows, $post );

		foreach ( $meta_rows as $row_id => $row_elements ) {

			$meta_html[] = sprintf(
				'<tr class="cf7s-%s">
					<th>%s:</th>
					<td>%s</td>
				</tr>',
				esc_attr( $row_id ),
				esc_html( $row_elements[ 'label' ] ),
				$row_elements[ 'value' ]
			);

		}

		printf(
				'<div class="wrap cfs7-entry-wrap">
					<h2>%s</h2>
					<table class="cf7s-entry">
						%s
					</table>
					<h3>%s</h3>
					<table id="cf7s-entry-fields" class="cf7s-entry">
						%s
					</table>
					<h3>%s</h3>
					<table id="cf7s-entry-meta" class="cf7s-entry">
						%s
					</table>
				</div>',
				esc_html__( 'Form Submission', 'cf7-storage' ),
				implode( '', $rows_html ),
				esc_html__( 'Field Values', 'cf7-storage' ),
				implode( '', $fields_html ),
				esc_html__( 'Submission Details', 'cf7-storage' ),
				implode( '', $meta_html )
			);

	}


	function export_entries( $query_args = array() ) {

		$query = new WP_Query( $query_args );
		$entries = $query->posts;

		$list = array();
		$extras_headers = array();
		$extras = array();

		$rows_default = array(
				'mail-date' => __( 'Date', 'cf7-storage' ),
				'mail-from' => __( 'From', 'cf7-storage' ),
				'mail-to' => __( 'To', 'cf7-storage' ),
				'mail-subject' => __( 'Subject', 'cf7-storage' ),
				'mail-body' => __( 'Body', 'cf7-storage' ),
				'mail-attachments' => __( 'Attachments', 'cf7-storage' ),
				'mail-from-name' => __( 'Form Name', 'cf7-storage' ),
				'entry-id' => __( 'Entry ID', 'cf7-storage' ),
				'entry-url' => __( 'Entry URL', 'cf7-storage' ),
				'http-referer' => __( 'Referer', 'cf7-storage' ),
				'http-user-agent' => __( 'User-agent', 'cf7-storage' ),
				'http-remote-addr' => __( 'IP Address', 'cf7-storage' ),
			);

		$rows_default_keys = array_keys( $rows_default );

		// Add column headers
		$list[0] = $rows_default;

		$format_date = get_option( 'date_format' );
		$format_time = get_option( 'time_format' );

		foreach ( $entries as $post ) {

			$timestamp = strtotime( $post->post_date );

			$attachments = get_post_meta( $post->ID, 'mail_attachments', true );
			$maybe_attachments = array();

			if ( ! empty( $attachments ) ) {

				$attachment_list = array();

				foreach ( $attachments as $url ) {
					$maybe_attachments[] = $this->get_storage_file_url( $url, $post->ID );
				}

			} else {

				$maybe_attachments[] = _x( 'None', 'No attachments found', 'cf7-storage' );

			}

			$entry_url = add_query_arg(
				array(
					'page' => 'cf7_storage',
					'action' => 'view',
					'post_id' => $post->ID
				),
				admin_url( 'admin.php' )
			);

			$row_values = array(
					sprintf(
						'%s %s',
						date_i18n( $format_date, $timestamp ),
						date_i18n( $format_time, $timestamp )
					),
					get_post_meta( $post->ID, 'mail_sender', true ),
					get_post_meta( $post->ID, 'mail_recipient', true ),
					get_post_meta( $post->ID, 'mail_subject', true ),
					$post->post_content,
					implode( "\n", $maybe_attachments ),
					get_the_title( $post->post_parent ),
					$post->ID,
					$entry_url,
					get_post_meta( $post->ID, 'http_referer', true ),
					get_post_meta( $post->ID, 'http_user_agent', true ),
					get_post_meta( $post->ID, 'remote_addr', true ),
				);

			$list[ $post->ID ] = array_combine( $rows_default_keys, $row_values );

			// Append raw field data to the end
			$form_fields = get_post_meta( $post->ID, 'form_fields', true );

			// Add all fields to in own columns
			$extras[ $post->ID ] = array();

			if ( is_array( $form_fields ) && ! empty( $form_fields ) ) {

				foreach ( $form_fields as $field_name => $field_value ) {

					$field_id = 'field-' . $field_name;

					// Store all field keys ever used
					if ( ! in_array( $field_name, $extras_headers ) )
						$extras_headers[ $field_id ] = $field_name;

					if ( is_array( $field_value ) )
						$extras[ $post->ID ][ $field_id ] = implode( ', ', $field_value );
					else
						$extras[ $post->ID ][ $field_id ] = $field_value;

				}

			}

		}

		// Append field labels to header
		$list[0] = array_merge( $list[0], $extras_headers );

		foreach ( $list as $post_id => $row ) {

			// Skip the header row
			if ( ! $post_id )
				continue;

			// Add the field values in the correct columns
			foreach ( $extras_headers as $field_id => $field_name )
				if ( isset( $extras[ $post_id ][ $field_id ] ) )
					$list[ $post_id ][ $field_id ] = $extras[ $post_id ][ $field_id ];
				else
					$list[ $post_id ][ $field_id ] = null;

		}

		// Allow plugins to customize the export columns
		$list = apply_filters( 'cf7_storage_csv_columns', $list );

		// Use the last-used delimiter value, if possible
		$delimiter = get_option( 'cf7_storage_csv_delimiter_used_last', ';' );

		// Allow plugins to specify the delimiter
		$delimiter = apply_filters( 'cf7_storage_csv_delimiter', $delimiter );

		if ( ! empty( $_GET['export-csv-delimiter'] ) ) {
			$delimiter = sanitize_text_field( stripslashes( $_GET['export-csv-delimiter'] ) );
		}

		// Remember the prefered delimiter setting
		update_option( 'cf7_storage_csv_delimiter_used_last', $delimiter );

		// Convert special characters into their string values
		$delimiter = str_replace( array( 'tab' ), array( "\t" ), $delimiter );

		// Ensure the delimiter is 1-char long
		$delimiter = substr( $delimiter, 0, 1 );

		// Ensure there is a delimiter defined
		if ( empty( $delimiter ) ) {
			$delimiter = ',';
		}

		// Send download headers
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( sprintf( 'Content-Disposition: attachment; filename=cf7-entries-%s.csv', date( 'dmY-His' ) ) );
		header( 'Cache-control: private' );

		$df = fopen( 'php://output', 'w' );

		// UTF-8 BOM for Excel
		fputs( $df, "\xEF\xBB\xBF" );

		foreach ( $list as $row ) {
			fputcsv( $df, $row, $delimiter );
		}

		fclose($df);
		die;

	}


	function cf7_upgrade_notice() {

		if ( class_exists( 'WPCF7_Mail' ) )
			return;

		printf(
			'<div class="cf7-storage-notice error">
				<p>%s</p>
			</div>',
			esc_html__( 'Storage for Contact Form 7 requires the latest version of Contact Form 7 plugin.', 'cf7-storage' )
		);

	}


	function plugin_action_links( $links ) {

		$links[] = sprintf(
			'<a href="%s">%s</a>',
			esc_url( menu_page_url( 'cf7_storage', false ) ),
			esc_html__( 'Form Entries', 'cf7-storage' )
		);

		return $links;

	}


}
