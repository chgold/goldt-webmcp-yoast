<?php
/**
 * Yoast SEO insight tools (1.1.0) — effective (rendered) SEO output and
 * index health, read through Yoast's own surfaces and repositories.
 *
 * @package GoldtWebMCP\Yoast
 */

namespace GoldtWebMCP\Modules;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read-only insight tools for the `yoast.*` module:
 * searchContent, getSchema, getSocialPreview, getBreadcrumbs, getIndexationStatus.
 *
 * Requires the host class to provide require_cap(), require_post(),
 * require_edit_post(), limit_param() and success_response().
 */
trait Yoast_Insights {
	// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- the Bridge dispatches tool `x` to method `execute_x`, so handler names must keep the camelCase tool name (as in Yoast_Module).

	/**
	 * Register the insight tools.
	 *
	 * @return void
	 */
	protected function register_insight_tools() {
		$post_only = array(
			'type'       => 'object',
			'required'   => array( 'post_id' ),
			'properties' => array( 'post_id' => array( 'type' => 'integer' ) ),
		);

		$this->register_tool(
			'searchContent',
			array(
				'description'    => 'Search posts/pages in the Yoast index by SEO rank, readability rank, score ranges, focus keyphrase, post type, cornerstone/noindex flags and publish-date range. Ranks follow Yoast: bad 1-40, ok 41-70, good 71-100, na = no focus keyphrase (SEO) / not analysed (readability). Returns only content the user can edit.',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => array(
						'seo_rank'          => array(
							'type'        => 'string',
							'description' => 'bad | ok | good | na',
						),
						'readability_rank'  => array(
							'type'        => 'string',
							'description' => 'bad | ok | good | na',
						),
						'min_seo_score'     => array( 'type' => 'integer' ),
						'max_seo_score'     => array( 'type' => 'integer' ),
						'min_readability'   => array( 'type' => 'integer' ),
						'max_readability'   => array( 'type' => 'integer' ),
						'focus_keyword'     => array(
							'type'        => 'string',
							'description' => 'Substring match on the focus keyphrase',
						),
						'has_focus_keyword' => array( 'type' => 'boolean' ),
						'post_type'         => array(
							'type'        => 'string',
							'description' => 'e.g. post, page, product (default: all)',
						),
						'post_status'       => array(
							'type'        => 'string',
							'description' => 'e.g. publish, draft (default: all)',
						),
						'cornerstone'       => array( 'type' => 'boolean' ),
						'noindex'           => array(
							'type'        => 'boolean',
							'description' => 'true = explicitly noindexed in the post',
						),
						'published_after'   => array(
							'type'        => 'string',
							'description' => 'Date, e.g. 2026-01-01',
						),
						'published_before'  => array(
							'type'        => 'string',
							'description' => 'Date, e.g. 2026-12-31',
						),
						'limit'             => array(
							'type'        => 'integer',
							'description' => 'Max results (default 50, max 200)',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getSchema',
			array(
				'description'    => 'Get the Schema.org structured data (JSON-LD @graph) that Yoast outputs for a post/page — Article, WebPage, BreadcrumbList, Organization/Person, plus any pieces added by blocks (FAQ, How-To) or other plugins.',
				'input_schema'   => array(
					'type'       => 'object',
					'required'   => array( 'post_id' ),
					'properties' => array(
						'post_id' => array( 'type' => 'integer' ),
						'type'    => array(
							'type'        => 'string',
							'description' => 'Only return graph pieces of this @type (e.g. FAQPage, BreadcrumbList)',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getSocialPreview',
			array(
				'description'    => 'Get the effective social preview of a post as Yoast renders it: Open Graph (used by Facebook, LinkedIn, Pinterest) and Twitter/X card title, description, image and URL — including values that come from templates and fallbacks — with which fields are custom-set and simple improvement hints.',
				'input_schema'   => $post_only,
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getBreadcrumbs',
			array(
				'description'    => 'Get the Yoast breadcrumb trail of a post/page (the hierarchy path), the BreadcrumbList schema piece, and the breadcrumb settings (enabled, separator, home text).',
				'input_schema'   => $post_only,
				'required_scope' => 'read',
			)
		);
		$this->register_tool(
			'getIndexationStatus',
			array(
				'description'    => 'Get Yoast SEO data indexation status: whether indexables indexing finished, how many objects are still unindexed, link-counter completeness, post types set to noindex, and counts/list of content explicitly set to noindex. Stale indexation explains outdated SEO overview numbers.',
				'input_schema'   => array(
					'type'       => 'object',
					'properties' => array(
						'list_noindex' => array(
							'type'        => 'boolean',
							'description' => 'Also list explicitly noindexed posts (default false)',
						),
						'limit'        => array(
							'type'        => 'integer',
							'description' => 'Max noindexed posts to list (default 50, max 200)',
						),
					),
				),
				'required_scope' => 'read',
			)
		);
	}

	/**
	 * Normalise a boolean-ish tool parameter (the Bridge accepts "true"/"false"/0/1).
	 *
	 * @param mixed $value Raw value.
	 * @return bool
	 */
	private function bool_param( $value ) {
		if ( is_string( $value ) ) {
			return 'true' === strtolower( $value ) || '1' === $value;
		}
		return (bool) $value;
	}

	/**
	 * Yoast's indexable repository, or null when Yoast's container is unavailable.
	 *
	 * @return \Yoast\WP\SEO\Repositories\Indexable_Repository|null
	 */
	private function indexable_repository() {
		if ( ! function_exists( 'YoastSEO' ) || ! class_exists( '\Yoast\WP\SEO\Repositories\Indexable_Repository' ) ) {
			return null;
		}
		return \YoastSEO()->classes->get( \Yoast\WP\SEO\Repositories\Indexable_Repository::class );
	}

	/**
	 * Score range for a Yoast rank slug (WPSEO_Rank ranges), or null for na / unknown.
	 *
	 * @param string $rank bad | ok | good.
	 * @return array|null [start, end].
	 */
	private function rank_range( $rank ) {
		$ranges = array(
			'bad'  => array( 1, 40 ),
			'ok'   => array( 41, 70 ),
			'good' => array( 71, 100 ),
		);
		return $ranges[ $rank ] ?? null;
	}

	/**
	 * Rank slug for a numeric score.
	 *
	 * @param int|null $score Score 0-100 or null.
	 * @return string
	 */
	private function rank_for_score( $score ) {
		if ( null === $score || '' === $score || 0 === (int) $score ) {
			return 'na';
		}
		$score = (int) $score;
		if ( $score <= 40 ) {
			return 'bad';
		}
		return $score <= 70 ? 'ok' : 'good';
	}

	/**
	 * Parse a date parameter into MySQL datetime format.
	 *
	 * @param string $value Date string.
	 * @param string $name  Parameter name (for the error).
	 * @return string|\WP_Error
	 */
	private function date_param( $value, $name ) {
		$ts = strtotime( (string) $value );
		if ( false === $ts ) {
			return new \WP_Error( 'invalid_parameter', "{$name} is not a valid date", array( 'status' => 400 ) );
		}
		return gmdate( 'Y-m-d H:i:s', $ts );
	}

	/**
	 * Effective Yoast meta (Meta surface) for a post the user may edit.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error [ post_id, meta ].
	 */
	private function require_post_meta_surface( $params ) {
		$id = $this->require_post( $params );
		if ( \is_wp_error( $id ) ) {
			return $id;
		}
		$deny = $this->require_edit_post( $id );
		if ( $deny ) {
			return $deny;
		}
		if ( ! function_exists( 'YoastSEO' ) ) {
			return new \WP_Error( 'yoast_unavailable', 'Yoast SEO surfaces are not available', array( 'status' => 501 ) );
		}
		$meta = \YoastSEO()->meta->for_post( $id );
		if ( ! $meta ) {
			return new \WP_Error( 'no_indexable', 'Yoast has no SEO data (indexable) for this post', array( 'status' => 404 ) );
		}
		return array( $id, $meta );
	}

	/**
	 * Tool handler for `yoast.searchContent`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_searchContent( $params ) {
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		$repo = $this->indexable_repository();
		if ( ! $repo ) {
			return new \WP_Error( 'no_index', 'Yoast indexable repository not available', array( 'status' => 501 ) );
		}
		$limit = min( 200, $this->limit_param( $params, 50 ) );
		$query = $repo->query()->where( 'object_type', 'post' );

		foreach ( array( 'seo_rank', 'readability_rank' ) as $key ) {
			if ( empty( $params[ $key ] ) ) {
				continue;
			}
			$rank   = \sanitize_key( $params[ $key ] );
			$column = 'seo_rank' === $key ? 'primary_focus_keyword_score' : 'readability_score';
			if ( 'na' === $rank ) {
				$query->where_raw( "( {$column} IS NULL OR {$column} = 0 )" );
				continue;
			}
			$range = $this->rank_range( $rank );
			if ( ! $range ) {
				return new \WP_Error( 'invalid_parameter', "{$key} must be one of bad, ok, good, na", array( 'status' => 400 ) );
			}
			$query->where_gte( $column, $range[0] )->where_lte( $column, $range[1] );
		}
		$ranges = array(
			'min_seo_score'   => array( 'primary_focus_keyword_score', 'where_gte' ),
			'max_seo_score'   => array( 'primary_focus_keyword_score', 'where_lte' ),
			'min_readability' => array( 'readability_score', 'where_gte' ),
			'max_readability' => array( 'readability_score', 'where_lte' ),
		);
		foreach ( $ranges as $key => $spec ) {
			if ( isset( $params[ $key ] ) && '' !== $params[ $key ] ) {
				$query->{$spec[1]}( $spec[0], (int) $params[ $key ] );
			}
		}
		if ( isset( $params['focus_keyword'] ) && '' !== $params['focus_keyword'] ) {
			global $wpdb;
			$query->where_like( 'primary_focus_keyword', '%' . $wpdb->esc_like( \sanitize_text_field( $params['focus_keyword'] ) ) . '%' );
		}
		if ( isset( $params['has_focus_keyword'] ) ) {
			if ( $this->bool_param( $params['has_focus_keyword'] ) ) {
				$query->where_not_null( 'primary_focus_keyword' )->where_not_equal( 'primary_focus_keyword', '' );
			} else {
				$query->where_raw( "( primary_focus_keyword IS NULL OR primary_focus_keyword = '' )" );
			}
		}
		if ( ! empty( $params['post_type'] ) ) {
			$query->where( 'object_sub_type', \sanitize_key( $params['post_type'] ) );
		}
		if ( ! empty( $params['post_status'] ) ) {
			$query->where( 'post_status', \sanitize_key( $params['post_status'] ) );
		}
		if ( isset( $params['cornerstone'] ) ) {
			$query->where( 'is_cornerstone', $this->bool_param( $params['cornerstone'] ) ? 1 : 0 );
		}
		if ( isset( $params['noindex'] ) ) {
			if ( $this->bool_param( $params['noindex'] ) ) {
				$query->where( 'is_robots_noindex', 1 );
			} else {
				$query->where_raw( '( is_robots_noindex IS NULL OR is_robots_noindex = 0 )' );
			}
		}
		foreach ( array(
			'published_after'  => 'where_gte',
			'published_before' => 'where_lte',
		) as $key => $method ) {
			if ( ! empty( $params[ $key ] ) ) {
				$date = $this->date_param( $params[ $key ], $key );
				if ( \is_wp_error( $date ) ) {
					return $date;
				}
				$query->{$method}( 'object_published_at', $date );
			}
		}

		$query->order_by_desc( 'object_published_at' )->order_by_asc( 'id' );

		$out    = array();
		$offset = 0;
		// SEC-00: keep only content the user can edit natively (Yoast metabox rule).
		do {
			$page = clone $query;
			$rows = $page->limit( $limit )->offset( $offset )->find_many();
			$rows = is_array( $rows ) ? $rows : array();
			foreach ( $rows as $row ) {
				$id = (int) $row->object_id;
				if ( ! \current_user_can( 'edit_post', $id ) ) {
					continue;
				}
				$seo   = null === $row->primary_focus_keyword_score ? null : (int) $row->primary_focus_keyword_score;
				$read  = null === $row->readability_score ? null : (int) $row->readability_score;
				$out[] = array(
					'post_id'           => $id,
					'title'             => \get_the_title( $id ),
					'post_type'         => $row->object_sub_type,
					'post_status'       => $row->post_status,
					'permalink'         => $row->permalink,
					'focus_keyword'     => $row->primary_focus_keyword,
					'seo_score'         => $seo,
					'seo_rank'          => $this->rank_for_score( $seo ),
					'readability_score' => $read,
					'readability_rank'  => $this->rank_for_score( $read ),
					'is_cornerstone'    => (bool) $row->is_cornerstone,
					'noindex'           => null === $row->is_robots_noindex ? null : (bool) $row->is_robots_noindex,
					'published_at'      => $row->object_published_at,
				);
				if ( count( $out ) >= $limit ) {
					break 2;
				}
			}
			$offset += $limit;
			$fetched = count( $rows );
		} while ( $fetched === $limit && $offset < $limit * 20 );

		return $this->success_response(
			array(
				'results' => $out,
				'count'   => count( $out ),
				'note'    => 'Scores come from the Yoast index; posts never analysed in the editor have no score (rank na).',
			)
		);
	}

	/**
	 * Tool handler for `yoast.getSchema`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSchema( $params ) {
		$res = $this->require_post_meta_surface( $params );
		if ( \is_wp_error( $res ) ) {
			return $res;
		}
		list( $id, $meta ) = $res;
		$schema            = $meta->schema;
		$graph             = ( is_array( $schema ) && isset( $schema['@graph'] ) && is_array( $schema['@graph'] ) ) ? $schema['@graph'] : array();
		$types             = array();
		foreach ( $graph as $piece ) {
			foreach ( (array) ( $piece['@type'] ?? array() ) as $t ) {
				$types[] = (string) $t;
			}
		}
		if ( ! empty( $params['type'] ) ) {
			$want  = (string) $params['type'];
			$graph = array_values(
				array_filter(
					$graph,
					function ( $piece ) use ( $want ) {
						return in_array( $want, (array) ( $piece['@type'] ?? array() ), true );
					}
				)
			);
		}
		return $this->success_response(
			array(
				'post_id'      => $id,
				'types'        => array_values( array_unique( $types ) ),
				'page_type'    => $meta->schema_page_type,
				'article_type' => $meta->schema_article_type,
				'graph'        => $graph,
				'piece_count'  => count( $graph ),
			)
		);
	}

	/**
	 * Tool handler for `yoast.getSocialPreview`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getSocialPreview( $params ) {
		$res = $this->require_post_meta_surface( $params );
		if ( \is_wp_error( $res ) ) {
			return $res;
		}
		list( $id, $meta ) = $res;

		$images = array();
		foreach ( (array) $meta->open_graph_images as $img ) {
			$images[] = array(
				'url'    => $img['url'] ?? '',
				'width'  => isset( $img['width'] ) ? (int) $img['width'] : null,
				'height' => isset( $img['height'] ) ? (int) $img['height'] : null,
				'type'   => $img['type'] ?? null,
			);
		}
		$og      = array(
			'title'          => (string) $meta->open_graph_title,
			'description'    => (string) $meta->open_graph_description,
			'url'            => (string) $meta->open_graph_url,
			'type'           => (string) $meta->open_graph_type,
			'site_name'      => (string) $meta->open_graph_site_name,
			'locale'         => (string) $meta->open_graph_locale,
			'images'         => $images,
			'published_time' => $meta->open_graph_article_published_time ? $meta->open_graph_article_published_time : null,
			'modified_time'  => $meta->open_graph_article_modified_time ? $meta->open_graph_article_modified_time : null,
		);
		$twitter = array(
			'card'        => (string) $meta->twitter_card,
			'title'       => (string) $meta->twitter_title,
			'description' => (string) $meta->twitter_description,
			'image'       => (string) $meta->twitter_image,
			'creator'     => (string) $meta->twitter_creator,
			'site'        => (string) $meta->twitter_site,
		);
		$custom  = array(
			'og_title'            => '' !== (string) \get_post_meta( $id, '_yoast_wpseo_opengraph-title', true ),
			'og_description'      => '' !== (string) \get_post_meta( $id, '_yoast_wpseo_opengraph-description', true ),
			'og_image'            => '' !== (string) \get_post_meta( $id, '_yoast_wpseo_opengraph-image', true ),
			'twitter_title'       => '' !== (string) \get_post_meta( $id, '_yoast_wpseo_twitter-title', true ),
			'twitter_description' => '' !== (string) \get_post_meta( $id, '_yoast_wpseo_twitter-description', true ),
			'twitter_image'       => '' !== (string) \get_post_meta( $id, '_yoast_wpseo_twitter-image', true ),
		);

		$hints = array();
		if ( ! $images ) {
			$hints[] = 'No Open Graph image: Facebook/LinkedIn/Pinterest will show no image or pick one at random. Set a featured image or a custom social image (1200x630 recommended).';
		} elseif ( null !== $images[0]['width'] && $images[0]['width'] < 1200 ) {
			$hints[] = 'Open Graph image is narrower than 1200px; large previews may look blurry.';
		}
		if ( '' === $og['description'] ) {
			$hints[] = 'No social description: add a meta description or a custom Open Graph description.';
		}
		if ( function_exists( 'mb_strlen' ) && mb_strlen( $og['title'] ) > 60 ) {
			$hints[] = 'Open Graph title is longer than 60 characters and may be truncated in previews.';
		}
		if ( '' === $twitter['image'] && ! $images ) {
			$hints[] = 'No Twitter/X image: the card will render without an image.';
		}

		return $this->success_response(
			array(
				'post_id'    => $id,
				'open_graph' => $og,
				'twitter'    => $twitter,
				'custom_set' => $custom,
				'hints'      => $hints,
				'platforms'  => array(
					'facebook'  => 'open_graph',
					'linkedin'  => 'open_graph',
					'pinterest' => 'open_graph',
					'twitter'   => 'twitter (falls back to open_graph)',
				),
			)
		);
	}

	/**
	 * Tool handler for `yoast.getBreadcrumbs`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getBreadcrumbs( $params ) {
		$res = $this->require_post_meta_surface( $params );
		if ( \is_wp_error( $res ) ) {
			return $res;
		}
		list( $id, $meta ) = $res;
		$trail             = array();
		foreach ( (array) $meta->breadcrumbs as $pos => $crumb ) {
			$trail[] = array(
				'position' => $pos + 1,
				'text'     => isset( $crumb['text'] ) ? \wp_strip_all_tags( (string) $crumb['text'] ) : '',
				'url'      => $crumb['url'] ?? null,
				'id'       => isset( $crumb['id'] ) ? (int) $crumb['id'] : null,
			);
		}
		$schema_piece = null;
		$schema       = $meta->schema;
		foreach ( (array) ( $schema['@graph'] ?? array() ) as $piece ) {
			if ( in_array( 'BreadcrumbList', (array) ( $piece['@type'] ?? array() ), true ) ) {
				$schema_piece = $piece;
				break;
			}
		}
		$opt = function ( $key, $fallback ) {
			return class_exists( '\WPSEO_Options' ) ? \WPSEO_Options::get( $key, $fallback ) : $fallback;
		};
		return $this->success_response(
			array(
				'post_id'      => $id,
				'trail'        => $trail,
				'path'         => implode( ' > ', wp_list_pluck( $trail, 'text' ) ),
				'schema_piece' => $schema_piece,
				'settings'     => array(
					'enabled'    => (bool) $opt( 'breadcrumbs-enable', false ),
					'separator'  => (string) $opt( 'breadcrumbs-sep', '' ),
					'home_text'  => (string) $opt( 'breadcrumbs-home', '' ),
					'prefix'     => (string) $opt( 'breadcrumbs-prefix', '' ),
					'show_blog'  => (bool) $opt( 'breadcrumbs-display-blog-page', false ),
					'bold_last'  => (bool) $opt( 'breadcrumbs-boldlast', false ),
					'theme_note' => 'The trail is shown on the page only if the theme calls yoast_breadcrumb() or uses the Yoast breadcrumbs block; the schema piece is output regardless.',
				),
			)
		);
	}

	/**
	 * Tool handler for `yoast.getIndexationStatus`.
	 *
	 * @param array $params Validated tool input.
	 * @return array|\WP_Error
	 */
	public function execute_getIndexationStatus( $params ) {
		// SEC-00: Yoast's own indexing REST routes (Indexing_Route::can_index) require edit_posts.
		$deny = $this->require_cap( 'edit_posts' );
		if ( $deny ) {
			return $deny;
		}
		$repo = $this->indexable_repository();
		if ( ! $repo ) {
			return new \WP_Error( 'no_index', 'Yoast indexable repository not available', array( 'status' => 501 ) );
		}
		$helpers   = \YoastSEO()->helpers;
		$indexing  = $helpers->indexing;
		$unindexed = (int) $indexing->get_filtered_unindexed_count();
		$total     = (int) $repo->query()->count();
		$pct       = ( $total + $unindexed ) > 0 ? round( $unindexed / ( $total + $unindexed ) * 100, 1 ) : 0.0;

		// Same scoping as getSeoOverview / Yoast dashboard statistics.
		$own_only = ! \current_user_can( 'edit_others_posts' );
		$base     = function () use ( $repo, $own_only ) {
			$q = $repo->query()->where( 'object_type', 'post' );
			if ( $own_only ) {
				$q->where( 'author_id', \get_current_user_id() );
			}
			return $q;
		};

		$posts_total      = (int) $base()->count();
		$posts_published  = (int) $base()->where( 'post_status', 'publish' )->count();
		$explicit_noindex = (int) $base()->where( 'is_robots_noindex', 1 )->count();
		$links_missing    = (int) $base()->where( 'post_status', 'publish' )->where_null( 'link_count' )->count();

		$noindex_types = array();
		foreach ( \get_post_types( array( 'public' => true ), 'names' ) as $pt ) {
			if ( class_exists( '\WPSEO_Options' ) && \WPSEO_Options::get( 'noindex-' . $pt, false ) ) {
				$noindex_types[] = $pt;
			}
		}

		$recommendations = array();
		if ( ! $indexing->is_finished_indexables_indexing() || $unindexed > 0 ) {
			$recommendations[] = "Run Yoast SEO data optimization (Yoast SEO > Tools > Optimize SEO data, or `wp yoast index`): {$unindexed} objects are not indexed, so SEO overview and score searches are incomplete.";
		}
		if ( $links_missing > 0 ) {
			$recommendations[] = "{$links_missing} published posts have no link counts; internal-linking data (orphaned content, link counts) is incomplete until optimization runs.";
		}
		if ( $noindex_types ) {
			$recommendations[] = 'Post types set to noindex site-wide: ' . implode( ', ', $noindex_types ) . '. Verify this is intended.';
		}
		if ( ! $helpers->indexable->should_index_indexables() ) {
			$recommendations[] = 'Yoast does not build indexables in this environment (non-production or disabled by filter); data may be stale.';
		}
		if ( '0' === (string) \get_option( 'blog_public' ) ) {
			$recommendations[] = 'Site-wide "Discourage search engines" is on (Settings > Reading): nothing is indexed by search engines.';
		}

		$data = array(
			'indexables_indexing_completed'       => (bool) $indexing->is_finished_indexables_indexing(),
			'unindexed_count'                     => $unindexed,
			'indexed_count'                       => $total,
			'unindexed_percentage'                => $pct,
			'indexing_reason'                     => $indexing->get_reason() ? $indexing->get_reason() : null,
			'indexables_enabled'                  => (bool) $helpers->indexable->should_index_indexables(),
			'search_engines_discouraged'          => '0' === (string) \get_option( 'blog_public' ),
			'scope'                               => $own_only ? 'own_posts' : 'all_posts',
			'posts_indexed'                       => $posts_total,
			'posts_published'                     => $posts_published,
			'posts_explicit_noindex'              => $explicit_noindex,
			'published_posts_missing_link_counts' => $links_missing,
			'noindex_post_types'                  => $noindex_types,
			'recommendations'                     => $recommendations,
		);

		if ( isset( $params['list_noindex'] ) && $this->bool_param( $params['list_noindex'] ) ) {
			$limit  = min( 200, $this->limit_param( $params, 50 ) );
			$list   = array();
			$offset = 0;
			do {
				$rows = $base()->where( 'is_robots_noindex', 1 )->order_by_asc( 'id' )->limit( $limit )->offset( $offset )->find_many();
				$rows = is_array( $rows ) ? $rows : array();
				foreach ( $rows as $row ) {
					$pid = (int) $row->object_id;
					if ( ! \current_user_can( 'edit_post', $pid ) ) {
						continue;
					}
					$list[] = array(
						'post_id'     => $pid,
						'title'       => \get_the_title( $pid ),
						'post_type'   => $row->object_sub_type,
						'post_status' => $row->post_status,
						'permalink'   => $row->permalink,
					);
					if ( count( $list ) >= $limit ) {
						break 2;
					}
				}
				$offset += $limit;
				$fetched = count( $rows );
			} while ( $fetched === $limit && $offset < $limit * 20 );
			$data['noindex_posts'] = $list;
		}

		return $this->success_response( $data );
	}
	// phpcs:enable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
}
