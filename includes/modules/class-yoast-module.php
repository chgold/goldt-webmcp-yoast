<?php
/**
 * Yoast SEO module — exposes Yoast SEO metadata as AI tools.
 *
 * @package GoldtWebMCP\Yoast
 */

namespace GoldtWebMCP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast SEO tools module (`yoast.*`).
 */
class Yoast_Module extends Module_Base {

	/**
	 * Module name — the tool-name prefix in the manifest.
	 *
	 * @var string
	 */
	protected $module_name = 'yoast';

	/**
	 * Tool handler for `yoast.tool`.
	 *
	 * @param string $tool_name Tool name (without module prefix).
	 * @param array  $params    Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_tool( $tool_name, $params = array() ) {
		if ( is_array( $params ) ) {
			foreach ( $params as $k => $v ) {
				if ( is_string( $v ) && '' !== $v && isset( $v[0] ) && ( '[' === $v[0] || '{' === $v[0] ) ) {
					$decoded = json_decode( $v, true );
					if ( is_array( $decoded ) ) {
						$params[ $k ] = $decoded;
					}
				}
			}
		}
		return parent::execute_tool( $tool_name, $params );
	}

	/**
	 * Register this module's tools.
	 *
	 * @return void
	 */
	protected function register_tools() {
		// ── Reads (free) ──
		$this->register_tool(
			'getSeoMeta',
			array(
				'description'    => 'Get the Yoast SEO metadata for a post/page: SEO title, meta description, focus keyword, canonical.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array( 'post_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getFocusKeyword',
			array(
				'description'    => 'Get the primary focus keyword of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array( 'post_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getSeoScore',
			array(
				'description'    => 'Get the Yoast SEO analysis score (linkdex, 0-100) of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array( 'post_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getReadabilityScore',
			array(
				'description'    => 'Get the Yoast readability score (0-100) of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array( 'post_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getRobotsSettings',
			array(
				'description'    => 'Get the robots directives (noindex/nofollow) of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array( 'post_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getSocialMeta',
			array(
				'description'    => 'Get the OpenGraph + Twitter social metadata of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array( 'post_id' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'listCornerstoneContent',
			array(
				'description'    => 'List posts/pages flagged as Yoast cornerstone content.',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => array( 'limit' => array( 'type' => 'integer' ) ),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'searchBySeoScore',
			array(
				'description'    => 'Find indexed content within an SEO-score range (uses the Yoast indexable index).',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => array(
						'min_score'   => array(
							'type'        => 'integer',
							'description' => '0-100',
						),
						'max_score'   => array(
							'type'        => 'integer',
							'description' => '0-100',
						),
						'object_type' => array(
							'type'        => 'string',
							'description' => 'post | term | user (default post)',
						),
						'limit'       => array( 'type' => 'integer' ),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getSitemapSettings',
			array(
				'description'    => 'Get the Yoast XML sitemap configuration (enabled, excluded post types/taxonomies) + sitemap URL.',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => (object) array(),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getSeoOverview',
			array(
				'description'    => 'Site-wide SEO overview: counts of noindex / missing-description / low-score / cornerstone content.',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => (object) array(),
				),
				'required_scope' => 'read',
			)
		);

		// ── Writes (pro) ──
		$this->register_tool(
			'updateSeoMeta',
			array(
				'description'    => 'Update the SEO title and/or meta description of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array(
						'post_id'  => array( 'type' => 'integer' ),
						'title'    => array(
							'type'        => 'string',
							'description' => 'SEO title (may use Yoast %%variables%%)',
						),
						'metadesc' => array( 'type' => 'string' ),
					),
				),
				'required_scope' => 'write',
			)
		);
		$this->register_tool(
			'setFocusKeyword',
			array(
				'description'    => 'Set the primary focus keyword of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'keyword' ),
					'properties' => array(
						'post_id' => array( 'type' => 'integer' ),
						'keyword' => array( 'type' => 'string' ),
					),
				),
				'required_scope' => 'write',
			)
		);
		$this->register_tool(
			'setCanonical',
			array(
				'description'    => 'Set the canonical URL of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'canonical' ),
					'properties' => array(
						'post_id'   => array( 'type' => 'integer' ),
						'canonical' => array( 'type' => 'string' ),
					),
				),
				'required_scope' => 'write',
			)
		);
		$this->register_tool(
			'setRobotsSettings',
			array(
				'description'    => 'Set robots directives (noindex/nofollow) of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array(
						'post_id'  => array( 'type' => 'integer' ),
						'noindex'  => array( 'type' => 'boolean' ),
						'nofollow' => array( 'type' => 'boolean' ),
					),
				),
				'required_scope' => 'write',
			)
		);
		$this->register_tool(
			'setCornerstone',
			array(
				'description'    => 'Flag/unflag a post as cornerstone content.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id', 'cornerstone' ),
					'properties' => array(
						'post_id'     => array( 'type' => 'integer' ),
						'cornerstone' => array( 'type' => 'boolean' ),
					),
				),
				'required_scope' => 'write',
			)
		);
		$this->register_tool(
			'setSocialMeta',
			array(
				'description'    => 'Set OpenGraph + Twitter social metadata of a post.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array(
						'post_id'             => array( 'type' => 'integer' ),
						'og_title'            => array( 'type' => 'string' ),
						'og_description'      => array( 'type' => 'string' ),
						'twitter_title'       => array( 'type' => 'string' ),
						'twitter_description' => array( 'type' => 'string' ),
					),
				),
				'required_scope' => 'write',
			)
		);
	}

	/**
	 * Read the optional `limit` parameter (absint), falling back to a default when empty.
	 *
	 * @param array $params   Validated tool input.
	 * @param int   $fallback Limit used when the parameter is missing or 0.
	 * @return int
	 */
	private function limit_param( $params, $fallback ) {
		$limit = \absint( $params['limit'] ?? $fallback );
		return $limit ? $limit : $fallback;
	}

	/**
	 * Deny unless the current user has a capability.
	 *
	 * @param string $cap Capability name.
	 * @return \WP_Error|null
	 */
	private function require_cap( $cap ) {
		if ( ! \current_user_can( $cap ) ) {
			return new \WP_Error( 'forbidden', "{$cap} capability required", array( 'status' => 403 ) );
		}
		return null;
	}

	/**
	 * Resolve and validate the post_id parameter.
	 *
	 * @param array $params Validated tool input.
	 * @return int|\WP_Error
	 */
	private function require_post( $params ) {
		$id = \absint( $params['post_id'] ?? 0 );
		if ( ! $id || ! \get_post( $id ) ) {
			return new \WP_Error( 'post_not_found', 'Post not found', array( 'status' => 404 ) );
		}
		return $id;
	}

	/**
	 * Per-object permission ceiling (SEC-00): the same rule Yoast's own post
	 * metabox uses — the user must be able to edit this specific post.
	 *
	 * @param int $id Post ID.
	 * @return \WP_Error|null
	 */
	private function require_edit_post( $id ) {
		if ( ! \current_user_can( 'edit_post', $id ) ) {
			return new \WP_Error( 'forbidden', 'edit_post capability required for this post', array( 'status' => 403 ) );
		}
		return null;
	}

	/**
	 * Yoast's "advanced" metabox tab (robots, canonical) is only editable with
	 * `wpseo_edit_advanced_metadata`, unless the site turned the
	 * `disableadvanced_meta` security setting off (WPSEO_Metabox / WPSEO_Meta).
	 *
	 * @return \WP_Error|null
	 */
	private function require_advanced_meta() {
		$can      = class_exists( '\WPSEO_Capability_Utils' )
			? \WPSEO_Capability_Utils::current_user_can( 'wpseo_edit_advanced_metadata' )
			: \current_user_can( 'wpseo_edit_advanced_metadata' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Yoast-registered capability.
		$disabled = class_exists( '\WPSEO_Options' ) ? \WPSEO_Options::get( 'disableadvanced_meta' ) : true;
		if ( ! $can && $disabled ) {
			return new \WP_Error( 'forbidden', 'wpseo_edit_advanced_metadata capability required', array( 'status' => 403 ) );
		}
		return null;
	}

	/**
	 * Whether the current user may see an indexable object natively.
	 *
	 * @param string $type post | term | user.
	 * @param int    $id   Object ID.
	 * @return bool
	 */
	private function can_edit_object( $type, $id ) {
		switch ( $type ) {
			case 'post':
				return \current_user_can( 'edit_post', $id );
			case 'term':
				return \current_user_can( 'edit_term', $id );
			case 'user':
				return \current_user_can( 'edit_user', $id );
		}
		return \current_user_can( 'manage_options' );
	}

	/**
	 * Tool handler for `yoast.getSeoMeta`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSeoMeta( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		return $this->success_response(
			array(
				'post_id'       => $id,
				'seo_title'     => \get_post_meta( $id, '_yoast_wpseo_title', true ),
				'metadesc'      => \get_post_meta( $id, '_yoast_wpseo_metadesc', true ),
				'focus_keyword' => \get_post_meta( $id, '_yoast_wpseo_focuskw', true ),
				'canonical'     => \get_post_meta( $id, '_yoast_wpseo_canonical', true ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.getFocusKeyword`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getFocusKeyword( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		return $this->success_response(
			array(
				'post_id'       => $id,
				'focus_keyword' => \get_post_meta( $id, '_yoast_wpseo_focuskw', true ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.getSeoScore`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSeoScore( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$score = \get_post_meta( $id, '_yoast_wpseo_linkdex', true );
		return $this->success_response(
			array(
				'post_id'   => $id,
				'seo_score' => '' === $score ? null : (int) $score,
			)
		);
	}

	/**
	 * Tool handler for `yoast.getReadabilityScore`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getReadabilityScore( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$score = \get_post_meta( $id, '_yoast_wpseo_content_score', true );
		return $this->success_response(
			array(
				'post_id'           => $id,
				'readability_score' => '' === $score ? null : (int) $score,
			)
		);
	}

	/**
	 * Tool handler for `yoast.getRobotsSettings`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getRobotsSettings( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		return $this->success_response(
			array(
				'post_id'  => $id,
				'noindex'  => \get_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', true ) === '1',
				'nofollow' => \get_post_meta( $id, '_yoast_wpseo_meta-robots-nofollow', true ) === '1',
			)
		);
	}

	/**
	 * Tool handler for `yoast.getSocialMeta`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSocialMeta( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		return $this->success_response(
			array(
				'post_id'             => $id,
				'og_title'            => \get_post_meta( $id, '_yoast_wpseo_opengraph-title', true ),
				'og_description'      => \get_post_meta( $id, '_yoast_wpseo_opengraph-description', true ),
				'og_image'            => \get_post_meta( $id, '_yoast_wpseo_opengraph-image', true ),
				'twitter_title'       => \get_post_meta( $id, '_yoast_wpseo_twitter-title', true ),
				'twitter_description' => \get_post_meta( $id, '_yoast_wpseo_twitter-description', true ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.listCornerstoneContent`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_listCornerstoneContent( $params ) {
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		$limit = $this->limit_param( $params, 50 );
		$out   = array();
		$page  = 1;
		// SEC-00: only posts the user can edit natively (Yoast metabox rule).
		do {
			$posts = \get_posts(
				array(
					'post_type'   => 'any',
					'numberposts' => $limit,
					'paged'       => $page,
					'post_status' => 'any',
					'meta_key'    => '_yoast_wpseo_is_cornerstone', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- unchanged query, now paged.
					'meta_value'  => '1', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				)
			);
			foreach ( $posts as $p ) {
				if ( ! \current_user_can( 'edit_post', $p->ID ) ) {
					continue;
				}
				$out[] = array(
					'post_id' => (int) $p->ID,
					'title'   => \get_the_title( $p ),
					'type'    => $p->post_type,
				);
				if ( count( $out ) >= $limit ) {
					break 2;
				}
			}
			++$page;
			$fetched = count( $posts );
		} while ( $fetched === $limit && $page <= 20 );
		return $this->success_response(
			array(
				'cornerstone' => $out,
				'count'       => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.searchBySeoScore`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_searchBySeoScore( $params ) {
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Yoast's own {prefix}yoast_indexable table has no WP API; live data is required; $table is $wpdb->prefix + a constant and every value goes through $wpdb->prepare().
		$min   = isset( $params['min_score'] ) ? (int) $params['min_score'] : 0;
		$max   = isset( $params['max_score'] ) ? (int) $params['max_score'] : 100;
		$type  = \sanitize_key( $params['object_type'] ?? 'post' );
		$limit = $this->limit_param( $params, 50 );
		$table = $wpdb->prefix . 'yoast_indexable';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return new \WP_Error( 'no_index', 'Yoast indexable table not found', array( 'status' => 501 ) );
		}
		$out    = array();
		$offset = 0;
		// SEC-00: drop indexables whose object the user cannot edit natively.
		do {
			$rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT object_id, title, primary_focus_keyword, primary_focus_keyword_score, readability_score, permalink
				 FROM {$table}
				 WHERE object_type = %s AND primary_focus_keyword_score BETWEEN %d AND %d
				 ORDER BY primary_focus_keyword_score ASC, id ASC LIMIT %d OFFSET %d",
					$type,
					$min,
					$max,
					$limit,
					$offset
				),
				ARRAY_A
			);
			$rows = is_array( $rows ) ? $rows : array();
			foreach ( $rows as $row ) {
				if ( ! $this->can_edit_object( $type, (int) $row['object_id'] ) ) {
					continue;
				}
				$out[] = $row;
				if ( count( $out ) >= $limit ) {
					break 2;
				}
			}
			$offset += $limit;
			$fetched = count( $rows );
		} while ( $fetched === $limit && $offset < $limit * 20 );
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $this->success_response(
			array(
				'results' => $out,
				'count'   => count( $out ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.getSitemapSettings`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSitemapSettings( $params ) {
		// SEC-00: Yoast's settings screens (incl. XML sitemaps) require wpseo_manage_options.
		$can = class_exists( '\WPSEO_Capability_Utils' )
			? \WPSEO_Capability_Utils::current_user_can( 'wpseo_manage_options' )
			: \current_user_can( 'wpseo_manage_options' ); // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Yoast-registered capability.
		if ( ! $can ) {
			return new \WP_Error( 'forbidden', 'wpseo_manage_options capability required', array( 'status' => 403 ) );
		}
		$enabled = true;
		if ( class_exists( '\WPSEO_Options' ) ) {
			$enabled = (bool) \WPSEO_Options::get( 'enable_xml_sitemap', true );
		}
		return $this->success_response(
			array(
				'xml_sitemap_enabled' => $enabled,
				'sitemap_url'         => \home_url( '/sitemap_index.xml' ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.getSeoOverview`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSeoOverview( $params ) {
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Yoast's own {prefix}yoast_indexable table has no WP API; live data is required; $table is $wpdb->prefix + a constant and every value goes through $wpdb->prepare().
		$table = $wpdb->prefix . 'yoast_indexable';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return new \WP_Error( 'no_index', 'Yoast indexable table not found', array( 'status' => 501 ) );
		}
		// SEC-00: Yoast's dashboard SEO overview (WPSEO_Statistics) is shown to
		// edit_posts users but scoped to their own posts without edit_others_posts.
		$scope = '';
		if ( ! \current_user_can( 'edit_others_posts' ) ) {
			$scope = $wpdb->prepare( " WHERE object_type = 'post' AND author_id = %d", \get_current_user_id() );
		}
		$row = $wpdb->get_row(
			"SELECT
			  SUM(object_type='post') AS posts_indexed,
			  SUM(is_cornerstone=1) AS cornerstone,
			  SUM(is_robots_noindex=1) AS noindex,
			  SUM(description IS NULL OR description='') AS missing_description,
			  SUM(primary_focus_keyword_score < 50 AND primary_focus_keyword_score IS NOT NULL) AS low_seo_score
			 FROM {$table}{$scope}",
			ARRAY_A
		);
		// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		return $this->success_response(
			array(
				'posts_indexed'       => (int) ( $row['posts_indexed'] ?? 0 ),
				'cornerstone'         => (int) ( $row['cornerstone'] ?? 0 ),
				'noindex'             => (int) ( $row['noindex'] ?? 0 ),
				'missing_description' => (int) ( $row['missing_description'] ?? 0 ),
				'low_seo_score'       => (int) ( $row['low_seo_score'] ?? 0 ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.updateSeoMeta`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_updateSeoMeta( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$updated = array();
		if ( isset( $params['title'] ) ) {
			\update_post_meta( $id, '_yoast_wpseo_title', \sanitize_text_field( $params['title'] ) );
			$updated[] = 'title';
		}
		if ( isset( $params['metadesc'] ) ) {
			\update_post_meta( $id, '_yoast_wpseo_metadesc', \sanitize_textarea_field( $params['metadesc'] ) );
			$updated[] = 'metadesc';
		}
		if ( ! $updated ) {
			return new \WP_Error( 'missing_parameter', 'Provide title and/or metadesc', array( 'status' => 400 ) );
		}
		return $this->success_response(
			array(
				'post_id' => $id,
				'updated' => $updated,
			)
		);
	}

	/**
	 * Tool handler for `yoast.setFocusKeyword`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_setFocusKeyword( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		\update_post_meta( $id, '_yoast_wpseo_focuskw', \sanitize_text_field( $params['keyword'] ?? '' ) );
		return $this->success_response(
			array(
				'post_id'       => $id,
				'focus_keyword' => $params['keyword'] ?? '',
			)
		);
	}

	/**
	 * Tool handler for `yoast.setCanonical`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_setCanonical( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$deny = $this->require_advanced_meta();
		if ( $deny ) {
			return $deny;
		}
		\update_post_meta( $id, '_yoast_wpseo_canonical', \esc_url_raw( $params['canonical'] ?? '' ) );
		return $this->success_response(
			array(
				'post_id'   => $id,
				'canonical' => $params['canonical'] ?? '',
			)
		);
	}

	/**
	 * Tool handler for `yoast.setRobotsSettings`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_setRobotsSettings( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$deny = $this->require_advanced_meta();
		if ( $deny ) {
			return $deny;
		}
		if ( isset( $params['noindex'] ) ) {
			\update_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', ! empty( $params['noindex'] ) ? '1' : '2' );
		}
		if ( isset( $params['nofollow'] ) ) {
			\update_post_meta( $id, '_yoast_wpseo_meta-robots-nofollow', ! empty( $params['nofollow'] ) ? '1' : '0' );
		}
		return $this->success_response(
			array(
				'post_id'  => $id,
				'noindex'  => ! empty( $params['noindex'] ),
				'nofollow' => ! empty( $params['nofollow'] ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.setCornerstone`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_setCornerstone( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		\update_post_meta( $id, '_yoast_wpseo_is_cornerstone', ! empty( $params['cornerstone'] ) ? '1' : '' );
		return $this->success_response(
			array(
				'post_id'     => $id,
				'cornerstone' => ! empty( $params['cornerstone'] ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.setSocialMeta`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_setSocialMeta( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		$map     = array(
			'og_title'            => '_yoast_wpseo_opengraph-title',
			'og_description'      => '_yoast_wpseo_opengraph-description',
			'twitter_title'       => '_yoast_wpseo_twitter-title',
			'twitter_description' => '_yoast_wpseo_twitter-description',
		);
		$updated = array();
		foreach ( $map as $param => $meta ) {
			if ( isset( $params[ $param ] ) ) {
				\update_post_meta( $id, $meta, \sanitize_text_field( $params[ $param ] ) );
				$updated[] = $param;
			}
		}
		if ( ! $updated ) {
			return new \WP_Error( 'missing_parameter', 'Provide at least one social field', array( 'status' => 400 ) );
		}
		return $this->success_response(
			array(
				'post_id' => $id,
				'updated' => $updated,
			)
		);
	}
}
