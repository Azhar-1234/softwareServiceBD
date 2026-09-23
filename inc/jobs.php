<?php
/**
 * Jobs board content type, fields and helpers.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the public jobs board.
 */
function ssbd_register_jobs() {
	register_post_type( 'ssbd_job', array(
		'labels'              => array_merge(
			ssbd_cpt_labels( __( 'International Job', 'ssbd' ), __( 'International Jobs', 'ssbd' ) ),
			array(
				'add_new'      => __( 'Sync international jobs', 'ssbd' ),
				'add_new_item' => __( 'International jobs are added automatically', 'ssbd' ),
			)
		),
		'public'              => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'has_archive'         => 'jobs',
		'query_var'           => true,
		'rewrite'             => array( 'slug' => 'jobs', 'with_front' => false ),
		'menu_icon'           => 'dashicons-businessperson',
		'menu_position'       => 24,
		'capability_type'     => 'post',
		'supports'            => array( 'title', 'editor', 'excerpt', 'thumbnail' ),
		'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'        => true,
	) );

	register_taxonomy( 'ssbd_job_category', 'ssbd_job', array(
		'labels'            => ssbd_tax_labels( __( 'Job category', 'ssbd' ), __( 'Job categories', 'ssbd' ) ),
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'hierarchical'      => true,
		'rewrite'           => array( 'slug' => 'jobs/category', 'with_front' => false ),
		'query_var'         => true,
	) );

	add_rewrite_rule( '^jobs/saved/?$', 'index.php?ssbd_saved_jobs=1', 'top' );
}
add_action( 'init', 'ssbd_register_jobs' );

/**
 * Register the virtual saved-jobs route.
 *
 * @param string[] $vars Public query variables.
 * @return string[]
 */
function ssbd_jobs_query_vars( $vars ) {
	$vars[] = 'ssbd_saved_jobs';
	return $vars;
}
add_filter( 'query_vars', 'ssbd_jobs_query_vars' );

/**
 * Use the dedicated template for /jobs/saved/.
 *
 * @param string $template Resolved template.
 * @return string
 */
function ssbd_saved_jobs_template( $template ) {
	if ( get_query_var( 'ssbd_saved_jobs' ) ) {
		global $wp_query;
		$wp_query->is_404 = false;
		status_header( 200 );
		return SSBD_DIR . '/page-jobs-saved.php';
	}
	return $template;
}
add_filter( 'template_include', 'ssbd_saved_jobs_template' );

/**
 * Set the title for the virtual saved-jobs page.
 *
 * @param string[] $parts Document title parts.
 * @return string[]
 */
function ssbd_saved_jobs_document_title( $parts ) {
	if ( get_query_var( 'ssbd_saved_jobs' ) ) {
		$parts['title'] = __( 'Saved Jobs', 'ssbd' );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'ssbd_saved_jobs_document_title' );

/**
 * Give the public jobs pages descriptive, search-focused document titles.
 *
 * @param string[] $parts Document title parts.
 * @return string[]
 */
function ssbd_jobs_document_title( $parts ) {
	if ( is_post_type_archive( 'ssbd_job' ) ) {
		$parts['title'] = __( 'International & Remote Jobs', 'ssbd' );
	} elseif ( is_tax( 'ssbd_job_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			$parts['title'] = sprintf( __( '%s International & Remote Jobs', 'ssbd' ), $term->name );
		}
	}

	return $parts;
}
add_filter( 'document_title_parts', 'ssbd_jobs_document_title', 20 );

/**
 * Supply unique descriptions for the jobs archive and category archives.
 *
 * @param string $description Current meta description.
 * @return string
 */
function ssbd_jobs_meta_description( $description ) {
	if ( is_post_type_archive( 'ssbd_job' ) ) {
		return __( 'Find current international and remote jobs in software, WordPress, design, marketing, data, AI, support, sales and cloud roles.', 'ssbd' );
	}

	if ( is_tax( 'ssbd_job_category' ) ) {
		$term = get_queried_object();
		if ( $term instanceof WP_Term ) {
			return sprintf(
				/* translators: %s: job category name. */
				__( 'Browse current %s international and remote jobs. Compare employers, locations, employment types and application details.', 'ssbd' ),
				$term->name
			);
		}
	}

	return $description;
}
add_filter( 'ssbd_meta_description', 'ssbd_jobs_meta_description', 20 );

/** Use the jobs artwork for search and social previews of jobs archives. */
function ssbd_jobs_share_image( $image ) {
	if ( ! is_post_type_archive( 'ssbd_job' ) && ! is_tax( 'ssbd_job_category' ) ) {
		return $image;
	}

	return array(
		'url'    => SSBD_URI . '/assets/images/international-remote-jobs.webp',
		'width'  => 1200,
		'height' => 630,
		'alt'    => __( 'Male technology professionals collaborating remotely across countries', 'ssbd' ),
	);
}
add_filter( 'ssbd_share_image', 'ssbd_jobs_share_image' );

/**
 * Seed the high-level job categories once.
 */
function ssbd_seed_job_categories() {
	$terms = array(
		'software-development' => __( 'Software Development', 'ssbd' ),
		'wordpress'            => __( 'WordPress', 'ssbd' ),
		'web-development'      => __( 'Web Development', 'ssbd' ),
		'marketing'            => __( 'Marketing', 'ssbd' ),
		'design'               => __( 'Design', 'ssbd' ),
		'customer-support'     => __( 'Customer Support', 'ssbd' ),
		'data-ai'              => __( 'Data & AI', 'ssbd' ),
		'devops-cloud'         => __( 'DevOps & Cloud', 'ssbd' ),
		'sales'                => __( 'Sales', 'ssbd' ),
	);

	foreach ( $terms as $slug => $name ) {
		if ( ! term_exists( $slug, 'ssbd_job_category' ) ) {
			wp_insert_term( $name, 'ssbd_job_category', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'init', 'ssbd_seed_job_categories', 25 );

/**
 * Job meta keys supported by the theme.
 *
 * @return array<string,string>
 */
function ssbd_job_meta_keys() {
	return array(
		'_ssbd_job_company'         => 'text',
		'_ssbd_job_location'        => 'text',
		'_ssbd_job_country'         => 'text',
		'_ssbd_job_remote'          => 'checkbox',
		'_ssbd_job_employment_type' => 'text',
		'_ssbd_job_salary_min'      => 'number',
		'_ssbd_job_salary_max'      => 'number',
		'_ssbd_job_salary_currency' => 'text',
		'_ssbd_job_salary_period'   => 'text',
		'_ssbd_job_logo_url'        => 'url',
		'_ssbd_job_apply_url'       => 'url',
		'_ssbd_job_external_id'     => 'text',
		'_ssbd_job_source'          => 'text',
		'_ssbd_job_date_posted'     => 'date',
		'_ssbd_job_valid_through'   => 'date',
		'_ssbd_job_last_synced'     => 'text',
	);
}

/**
 * Add the job details box.
 */
function ssbd_add_job_meta_box() {
	add_meta_box( 'ssbd-job-details', __( 'Job details', 'ssbd' ), 'ssbd_render_job_meta_box', 'ssbd_job', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'ssbd_add_job_meta_box' );

/**
 * Render the job details box.
 *
 * @param WP_Post $post Job post.
 */
function ssbd_render_job_meta_box( $post ) {
	wp_nonce_field( 'ssbd_save_job_meta', 'ssbd_job_meta_nonce' );

	$fields = array(
		'_ssbd_job_company'         => __( 'Company', 'ssbd' ),
		'_ssbd_job_location'        => __( 'Location', 'ssbd' ),
		'_ssbd_job_country'         => __( 'Country', 'ssbd' ),
		'_ssbd_job_remote'          => __( 'Remote job', 'ssbd' ),
		'_ssbd_job_employment_type' => __( 'Employment type', 'ssbd' ),
		'_ssbd_job_salary_min'      => __( 'Salary min', 'ssbd' ),
		'_ssbd_job_salary_max'      => __( 'Salary max', 'ssbd' ),
		'_ssbd_job_salary_currency' => __( 'Salary currency', 'ssbd' ),
		'_ssbd_job_salary_period'   => __( 'Salary period', 'ssbd' ),
		'_ssbd_job_logo_url'        => __( 'Company logo URL', 'ssbd' ),
		'_ssbd_job_apply_url'       => __( 'Apply URL', 'ssbd' ),
		'_ssbd_job_external_id'     => __( 'External job ID', 'ssbd' ),
		'_ssbd_job_source'          => __( 'Source', 'ssbd' ),
		'_ssbd_job_date_posted'     => __( 'Date posted', 'ssbd' ),
		'_ssbd_job_valid_through'   => __( 'Valid through', 'ssbd' ),
		'_ssbd_job_last_synced'     => __( 'Last synced', 'ssbd' ),
	);

	echo '<div class="ssbd-admin-grid">';
	foreach ( $fields as $key => $label ) {
		$type  = ssbd_job_meta_keys()[ $key ];
		$value = get_post_meta( $post->ID, $key, true );
		echo '<p><label for="' . esc_attr( $key ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( 'checkbox' === $type ) {
			echo '<label><input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="1" ' . checked( $value, '1', false ) . '> ' . esc_html__( 'Yes', 'ssbd' ) . '</label>';
		} else {
			echo '<input class="widefat" type="' . esc_attr( $type ) . '" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
		}
		echo '</p>';
	}
	echo '</div>';
}

/**
 * Save job details.
 *
 * @param int $post_id Post ID.
 */
function ssbd_save_job_meta( $post_id ) {
	if ( ! isset( $_POST['ssbd_job_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ssbd_job_meta_nonce'] ) ), 'ssbd_save_job_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	foreach ( ssbd_job_meta_keys() as $key => $type ) {
		if ( 'checkbox' === $type ) {
			update_post_meta( $post_id, $key, isset( $_POST[ $key ] ) ? '1' : '0' );
			continue;
		}

		$value = isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
		if ( 'url' === $type ) {
			$value = esc_url_raw( $value );
		}
		update_post_meta( $post_id, $key, $value );
	}
}
add_action( 'save_post_ssbd_job', 'ssbd_save_job_meta' );

/**
 * Read a job meta field.
 *
 * @param string   $key     Short key.
 * @param int|null $post_id Post ID.
 * @return string
 */
function ssbd_job_meta( $key, $post_id = null ) {
	return (string) get_post_meta( $post_id ?: get_the_ID(), '_ssbd_job_' . $key, true );
}

/**
 * Normalized employment type for display/schema.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function ssbd_job_employment_type( $post_id = null ) {
	$type = ssbd_job_meta( 'employment_type', $post_id );
	return $type ?: __( 'Full-time', 'ssbd' );
}

/**
 * Job archive URL.
 *
 * @return string
 */
function ssbd_jobs_url() {
	return get_post_type_archive_link( 'ssbd_job' ) ?: home_url( '/jobs/' );
}

/**
 * Job category counts for the archive.
 *
 * @return WP_Term[]
 */
function ssbd_job_categories() {
	$terms = get_terms( array(
		'taxonomy'   => 'ssbd_job_category',
		'hide_empty' => true,
		'orderby'    => 'name',
	) );

	return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Apply public category and posting-date filters to the jobs archive/search.
 *
 * @param WP_Query $query Main query.
 */
function ssbd_filter_jobs_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$post_type = $query->get( 'post_type' );
	if ( ! $query->is_post_type_archive( 'ssbd_job' ) && 'ssbd_job' !== $post_type ) {
		return;
	}

	$category = isset( $_GET['category'] ) ? sanitize_title( wp_unslash( $_GET['category'] ) ) : '';
	if ( $category && term_exists( $category, 'ssbd_job_category' ) ) {
		$query->set( 'tax_query', array(
			array(
				'taxonomy' => 'ssbd_job_category',
				'field'    => 'slug',
				'terms'    => $category,
			),
		) );
	}

	$days         = isset( $_GET['date'] ) ? absint( $_GET['date'] ) : 0;
	$allowed_days = array( 1, 3, 7, 14, 30 );
	if ( in_array( $days, $allowed_days, true ) ) {
		$query->set( 'date_query', array(
			array(
				'after'     => $days . ' days ago',
				'inclusive' => true,
			),
		) );
	}

	$keyword = isset( $_GET['keyword'] ) ? sanitize_text_field( wp_unslash( $_GET['keyword'] ) ) : '';
	if ( $keyword ) {
		$query->set( 's', $keyword );
	}

	$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'newest';
	if ( 'relevance' !== $sort || ! $keyword ) {
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );
	}
	$query->set( 'posts_per_page', 18 );
}
add_action( 'pre_get_posts', 'ssbd_filter_jobs_archive_query' );

/**
 * Keep temporary job-filter combinations out of the search index.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function ssbd_noindex_job_filters( $robots ) {
	if ( is_post_type_archive( 'ssbd_job' ) && array_intersect( array( 'keyword', 'category', 'date', 'sort' ), array_keys( $_GET ) ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'ssbd_noindex_job_filters' );

/**
 * Keep the visitor-specific saved-jobs view out of search engines.
 *
 * @param array $robots Robots directives.
 * @return array
 */
function ssbd_noindex_saved_jobs( $robots ) {
	if ( get_query_var( 'ssbd_saved_jobs' ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
}
add_filter( 'wp_robots', 'ssbd_noindex_saved_jobs' );

/**
 * Register the public endpoint used by the browser-only saved-jobs list.
 */
function ssbd_register_saved_jobs_endpoint() {
	register_rest_route( 'ssbd/v1', '/saved-jobs', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'ssbd_rest_saved_jobs',
		'permission_callback' => '__return_true',
		'args'                => array(
			'ids' => array( 'sanitize_callback' => 'sanitize_text_field' ),
		),
	) );
}
add_action( 'rest_api_init', 'ssbd_register_saved_jobs_endpoint' );

/**
 * Return public card data for locally saved job IDs.
 *
 * @param WP_REST_Request $request Request object.
 * @return WP_REST_Response
 */
function ssbd_rest_saved_jobs( $request ) {
	$ids = array_slice( array_filter( array_map( 'absint', explode( ',', (string) $request->get_param( 'ids' ) ) ) ), 0, 50 );
	if ( ! $ids ) {
		return rest_ensure_response( array() );
	}

	$posts = get_posts( array(
		'post_type'      => 'ssbd_job',
		'post_status'    => 'publish',
		'post__in'       => $ids,
		'orderby'        => 'post__in',
		'posts_per_page' => 50,
	) );
	$jobs = array();
	foreach ( $posts as $post ) {
		$terms  = get_the_terms( $post->ID, 'ssbd_job_category' );
		$jobs[] = array(
			'id'         => $post->ID,
			'title'      => get_the_title( $post ),
			'url'        => get_permalink( $post ),
			'company'    => ssbd_job_meta( 'company', $post->ID ),
			'location'   => ssbd_job_meta( 'location', $post->ID ) ?: __( 'Remote', 'ssbd' ),
			'type'       => ssbd_job_employment_type( $post->ID ),
			'salary'     => ssbd_job_salary( $post->ID ),
			'logo'       => ssbd_job_meta( 'logo_url', $post->ID ),
			'excerpt'    => wp_trim_words( get_the_excerpt( $post ), 28 ),
			'posted_ago' => sprintf( __( '%s ago', 'ssbd' ), human_time_diff( get_post_time( 'U', true, $post ), current_time( 'timestamp', true ) ) ),
			'categories' => $terms && ! is_wp_error( $terms ) ? array_values( wp_list_pluck( $terms, 'name' ) ) : array(),
		);
	}
	return rest_ensure_response( $jobs );
}

/**
 * Display salary when the feed provides normalized values.
 *
 * @param int|null $post_id Post ID.
 * @return string
 */
function ssbd_job_salary( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$min     = ssbd_job_meta( 'salary_min', $post_id );
	$max     = ssbd_job_meta( 'salary_max', $post_id );
	if ( ! $min && ! $max ) {
		return '';
	}
	$currency = ssbd_job_meta( 'salary_currency', $post_id );
	$period   = ssbd_job_meta( 'salary_period', $post_id );
	$range    = $min && $max ? number_format_i18n( (float) $min ) . ' - ' . number_format_i18n( (float) $max ) : number_format_i18n( (float) ( $min ?: $max ) );
	return trim( $currency . ' ' . $range . ( $period ? ' / ' . $period : '' ) );
}

/**
 * The visible job facts as small pills.
 *
 * @param int|null $post_id Post ID.
 * @return array
 */
function ssbd_job_facts( $post_id = null ) {
	$post_id = $post_id ?: get_the_ID();
	$facts   = array_filter( array(
		ssbd_job_meta( 'company', $post_id ),
		ssbd_job_meta( 'remote', $post_id ) ? __( 'Remote', 'ssbd' ) : ssbd_job_meta( 'location', $post_id ),
		ssbd_job_employment_type( $post_id ),
	) );

	return array_values( array_unique( $facts ) );
}

/**
 * JobPosting schema for individual job pages.
 *
 * @return array|null
 */
function ssbd_schema_job_posting() {
	if ( ! is_singular( 'ssbd_job' ) ) {
		return null;
	}

	$post_id = get_the_ID();
	$company = ssbd_job_meta( 'company', $post_id );
	$posted  = ssbd_job_meta( 'date_posted', $post_id ) ?: get_the_date( 'Y-m-d', $post_id );
	$valid   = ssbd_job_meta( 'valid_through', $post_id );

	$employment_type = explode( ',', ssbd_job_employment_type( $post_id ) );
	$node = array(
		'@type'              => 'JobPosting',
		'@id'                => get_permalink( $post_id ) . '#jobposting',
		'mainEntityOfPage'   => array( '@id' => get_permalink( $post_id ) . '#webpage' ),
		'title'              => get_the_title( $post_id ),
		'description'        => wp_kses_post( apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) ) ),
		'datePosted'         => $posted,
		'employmentType'     => strtoupper( str_replace( array( '-', ' ' ), '_', trim( $employment_type[0] ) ) ),
		'hiringOrganization' => array_filter( array(
			'@type' => 'Organization',
			'name'  => $company ?: __( 'Hiring company', 'ssbd' ),
		) ),
		'jobLocationType'    => ssbd_job_meta( 'remote', $post_id ) ? 'TELECOMMUTE' : null,
	);

	if ( $valid ) {
		$node['validThrough'] = $valid;
	}

	$salary_min      = ssbd_job_meta( 'salary_min', $post_id );
	$salary_max      = ssbd_job_meta( 'salary_max', $post_id );
	$salary_currency = ssbd_job_meta( 'salary_currency', $post_id );
	if ( $salary_currency && ( $salary_min || $salary_max ) ) {
		$node['baseSalary'] = array(
			'@type'    => 'MonetaryAmount',
			'currency' => $salary_currency,
			'value'    => array_filter( array(
				'@type'    => 'QuantitativeValue',
				'minValue' => $salary_min ?: null,
				'maxValue' => $salary_max ?: null,
				'unitText' => strtoupper( ssbd_job_meta( 'salary_period', $post_id ) ?: 'YEAR' ),
			) ),
		);
	}

	$location = ssbd_job_meta( 'location', $post_id );
	$country  = ssbd_job_meta( 'country', $post_id );
	if ( $location || $country ) {
		$node['jobLocation'] = array(
			'@type'   => 'Place',
			'address' => array_filter( array(
				'@type'          => 'PostalAddress',
				'addressLocality'=> $location,
				'addressCountry' => $country,
			) ),
		);
	}

	return array_filter( $node );
}
add_filter( 'ssbd_schema_graph', static function ( $graph ) {
	$job = ssbd_schema_job_posting();
	if ( $job ) {
		$graph[] = $job;
	}
	return $graph;
} );

/**
 * Describe the jobs shown on archive pages as an ordered list.
 *
 * @param array<int,array<string,mixed>> $graph Existing schema graph.
 * @return array<int,array<string,mixed>>
 */
function ssbd_schema_jobs_item_list( $graph ) {
	if ( ! is_post_type_archive( 'ssbd_job' ) && ! is_tax( 'ssbd_job_category' ) ) {
		return $graph;
	}

	global $wp_query;
	if ( empty( $wp_query->posts ) ) {
		return $graph;
	}

	$items = array();
	foreach ( $wp_query->posts as $index => $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$items[] = array(
			'@type'    => 'ListItem',
			'position' => $index + 1,
			'name'     => get_the_title( $post ),
			'url'      => get_permalink( $post ),
		);
	}

	if ( $items ) {
		$graph[] = array(
			'@type'           => 'ItemList',
			'@id'             => ssbd_canonical_url() . '#jobs',
			'name'            => wp_get_document_title(),
			'numberOfItems'   => count( $items ),
			'itemListOrder'   => 'https://schema.org/ItemListOrderDescending',
			'itemListElement' => $items,
		);
	}

	return $graph;
}
add_filter( 'ssbd_schema_graph', 'ssbd_schema_jobs_item_list', 20 );

/**
 * Run the live jobs importer.
 */
function ssbd_jobs_import_event() {
	ssbd_import_jobicy_jobs();
	do_action( 'ssbd_import_jobs' );
}
add_action( 'ssbd_jobs_import_event', 'ssbd_jobs_import_event' );

/**
 * Schedule importer hook twice daily.
 */
function ssbd_schedule_jobs_import() {
	if ( ! wp_next_scheduled( 'ssbd_jobs_import_event' ) ) {
		wp_schedule_event( time() + MINUTE_IN_SECONDS, 'twicedaily', 'ssbd_jobs_import_event' );
	}
}
add_action( 'init', 'ssbd_schedule_jobs_import', 30 );
add_action( 'after_switch_theme', 'ssbd_schedule_jobs_import' );

/**
 * Fill an empty jobs board immediately on the first normal site request.
 */
function ssbd_bootstrap_jobs_import() {
	if ( ! wp_doing_cron() && ! get_option( 'ssbd_jobs_sync_status' ) ) {
		ssbd_import_jobicy_jobs();
	}
}
add_action( 'init', 'ssbd_bootstrap_jobs_import', 31 );

/**
 * Import the latest remote jobs from Jobicy's free public API.
 *
 * @return array|WP_Error Sync result.
 */
function ssbd_import_jobicy_jobs() {
	if ( get_transient( 'ssbd_jobicy_sync_lock' ) ) {
		return new WP_Error( 'ssbd_jobs_syncing', __( 'A jobs sync is already running.', 'ssbd' ) );
	}

	set_transient( 'ssbd_jobicy_sync_lock', '1', 10 * MINUTE_IN_SECONDS );
	$response = wp_safe_remote_get( 'https://jobicy.com/api/v2/remote-jobs?count=200', array(
		'timeout'    => 25,
		'user-agent' => 'SoftwareServiceBD Jobs/' . wp_get_theme()->get( 'Version' ) . '; ' . home_url( '/' ),
	) );

	if ( is_wp_error( $response ) ) {
		delete_transient( 'ssbd_jobicy_sync_lock' );
		update_option( 'ssbd_jobs_sync_status', array( 'time' => time(), 'error' => $response->get_error_message() ), false );
		return $response;
	}

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		$error = new WP_Error( 'ssbd_jobs_api_error', sprintf( __( 'Jobs API returned HTTP %d.', 'ssbd' ), wp_remote_retrieve_response_code( $response ) ) );
		delete_transient( 'ssbd_jobicy_sync_lock' );
		update_option( 'ssbd_jobs_sync_status', array( 'time' => time(), 'error' => $error->get_error_message() ), false );
		return $error;
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $data ) || ! isset( $data['jobs'] ) || ! is_array( $data['jobs'] ) ) {
		$error = new WP_Error( 'ssbd_jobs_invalid_response', __( 'Jobs API returned an invalid response.', 'ssbd' ) );
		delete_transient( 'ssbd_jobicy_sync_lock' );
		update_option( 'ssbd_jobs_sync_status', array( 'time' => time(), 'error' => $error->get_error_message() ), false );
		return $error;
	}

	$created = 0;
	$updated = 0;
	foreach ( $data['jobs'] as $job ) {
		$result = ssbd_upsert_jobicy_job( $job );
		if ( 'created' === $result ) {
			$created++;
		} elseif ( 'updated' === $result ) {
			$updated++;
		}
	}

	$expired = ssbd_expire_stale_api_jobs();
	$status  = array( 'time' => time(), 'created' => $created, 'updated' => $updated, 'expired' => $expired );
	update_option( 'ssbd_jobs_sync_status', $status, false );
	delete_transient( 'ssbd_jobicy_sync_lock' );

	return $status;
}

/**
 * Create or refresh one Jobicy listing.
 *
 * @param array $job API job data.
 * @return string
 */
function ssbd_upsert_jobicy_job( $job ) {
	$external_id = isset( $job['id'] ) ? sanitize_text_field( (string) $job['id'] ) : '';
	$title       = isset( $job['jobTitle'] ) ? sanitize_text_field( $job['jobTitle'] ) : '';
	$url         = isset( $job['url'] ) ? esc_url_raw( $job['url'] ) : '';
	if ( ! $external_id || ! $title || ! $url ) {
		return 'skipped';
	}

	$existing = get_posts( array(
		'post_type'      => 'ssbd_job',
		'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'AND',
			array( 'key' => '_ssbd_job_source', 'value' => 'Jobicy' ),
			array( 'key' => '_ssbd_job_external_id', 'value' => $external_id ),
		),
	) );

	$published = ! empty( $job['pubDate'] ) ? strtotime( $job['pubDate'] ) : time();
	$postarr   = array(
		'ID'           => $existing ? (int) $existing[0] : 0,
		'post_type'    => 'ssbd_job',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_content' => wp_kses_post( isset( $job['jobDescription'] ) ? $job['jobDescription'] : '' ),
		'post_excerpt' => sanitize_textarea_field( isset( $job['jobExcerpt'] ) ? $job['jobExcerpt'] : '' ),
		'post_date'    => wp_date( 'Y-m-d H:i:s', $published ),
		'post_date_gmt'=> gmdate( 'Y-m-d H:i:s', $published ),
	);
	$post_id = wp_insert_post( wp_slash( $postarr ), true );
	if ( is_wp_error( $post_id ) ) {
		return 'skipped';
	}

	$types = isset( $job['jobType'] ) ? (array) $job['jobType'] : array();
	$meta  = array(
		'company'         => isset( $job['companyName'] ) ? sanitize_text_field( $job['companyName'] ) : '',
		'location'        => isset( $job['jobGeo'] ) ? sanitize_text_field( $job['jobGeo'] ) : __( 'Remote', 'ssbd' ),
		'country'         => isset( $job['jobGeo'] ) ? sanitize_text_field( $job['jobGeo'] ) : '',
		'remote'          => '1',
		'employment_type' => $types ? sanitize_text_field( implode( ', ', $types ) ) : __( 'Remote', 'ssbd' ),
		'salary_min'      => isset( $job['salaryMin'] ) ? (float) $job['salaryMin'] : '',
		'salary_max'      => isset( $job['salaryMax'] ) ? (float) $job['salaryMax'] : '',
		'salary_currency' => isset( $job['salaryCurrency'] ) ? sanitize_text_field( $job['salaryCurrency'] ) : '',
		'salary_period'   => isset( $job['salaryPeriod'] ) ? sanitize_text_field( $job['salaryPeriod'] ) : '',
		'logo_url'        => isset( $job['companyLogo'] ) ? esc_url_raw( $job['companyLogo'] ) : '',
		'apply_url'       => $url,
		'external_id'     => $external_id,
		'source'          => 'Jobicy',
		'date_posted'     => gmdate( 'Y-m-d', $published ),
		'last_synced'     => gmdate( 'c' ),
	);
	foreach ( $meta as $key => $value ) {
		update_post_meta( $post_id, '_ssbd_job_' . $key, $value );
	}

	wp_set_object_terms( $post_id, ssbd_jobicy_category_slugs( $job ), 'ssbd_job_category', false );
	return $existing ? 'updated' : 'created';
}

/**
 * Map feed industries and job text to the site's categories.
 *
 * @param array $job API job data.
 * @return string[]
 */
function ssbd_jobicy_category_slugs( $job ) {
	$industries = isset( $job['jobIndustry'] ) ? implode( ' ', (array) $job['jobIndustry'] ) : '';
	$haystack   = strtolower( $industries . ' ' . ( isset( $job['jobTitle'] ) ? $job['jobTitle'] : '' ) );
	$map        = array(
		'wordpress'            => array( 'wordpress', 'woocommerce', 'elementor', 'gutenberg', 'plugin developer', 'theme developer' ),
		'web-development'      => array( 'web', 'frontend', 'front-end', 'full stack', 'full-stack' ),
		'software-development' => array( 'engineering', 'developer', 'software', 'programming', 'mobile' ),
		'marketing'            => array( 'marketing', 'seo', 'growth', 'social media' ),
		'design'               => array( 'design', 'creative', 'multimedia', 'ui', 'ux' ),
		'customer-support'     => array( 'support', 'customer', 'success' ),
		'data-ai'              => array( 'data', 'machine learning', 'artificial intelligence', ' ai ' ),
		'devops-cloud'         => array( 'devops', 'cloud', 'infrastructure', 'security', 'sre' ),
		'sales'                => array( 'sales', 'business development', 'account executive' ),
	);
	$slugs = array();
	foreach ( $map as $slug => $needles ) {
		foreach ( $needles as $needle ) {
			if ( false !== strpos( ' ' . $haystack . ' ', $needle ) ) {
				$slugs[] = $slug;
				break;
			}
		}
	}
	return array_values( array_unique( $slugs ) );
}

/**
 * Unpublish API jobs that have disappeared from the latest feed for 45 days.
 *
 * @return int
 */
function ssbd_expire_stale_api_jobs() {
	$stale = get_posts( array(
		'post_type'      => 'ssbd_job',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'AND',
			array( 'key' => '_ssbd_job_source', 'value' => 'Jobicy' ),
			array( 'key' => '_ssbd_job_last_synced', 'value' => gmdate( 'c', time() - 45 * DAY_IN_SECONDS ), 'compare' => '<', 'type' => 'CHAR' ),
		),
	) );
	foreach ( $stale as $post_id ) {
		wp_update_post( array( 'ID' => $post_id, 'post_status' => 'draft' ) );
	}
	return count( $stale );
}
