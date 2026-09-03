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

class Yoast_Module extends Module_Base {

	protected $module_name = 'yoast';

	public function execute_tool( $tool_name, $params = array() ) {
		if ( is_array( $params ) ) {
			foreach ( $params as $k => $v ) {
				if ( is_string( $v ) && $v !== '' && isset( $v[0] ) && ( $v[0] === '[' || $v[0] === '{' ) ) {
					$decoded = json_decode( $v, true );
					if ( is_array( $decoded ) ) {
						$params[ $k ] = $decoded;
					}
				}
			}
		}
		return parent::execute_tool( $tool_name, $params );
	}

	protected function register_tools() {
		// ── Reads (free) ──
		$this->register_tool( 'getSeoMeta', array(
			'description'  => 'Get the Yoast SEO metadata for a post/page: SEO title, meta description, focus keyword, canonical.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array( 'post_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getFocusKeyword', array(
			'description'  => 'Get the primary focus keyword of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array( 'post_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getSeoScore', array(
			'description'  => 'Get the Yoast SEO analysis score (linkdex, 0-100) of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array( 'post_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getReadabilityScore', array(
			'description'  => 'Get the Yoast readability score (0-100) of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array( 'post_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getRobotsSettings', array(
			'description'  => 'Get the robots directives (noindex/nofollow) of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array( 'post_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getSocialMeta', array(
			'description'  => 'Get the OpenGraph + Twitter social metadata of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array( 'post_id' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'listCornerstoneContent', array(
			'description'  => 'List posts/pages flagged as Yoast cornerstone content.',
			'input_schema' => array( 'type' => 'object', 'properties' => array( 'limit' => array( 'type' => 'integer' ) ) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'searchBySeoScore', array(
			'description'  => 'Find indexed content within an SEO-score range (uses the Yoast indexable index).',
			'input_schema' => array( 'type' => 'object', 'properties' => array(
				'min_score' => array( 'type' => 'integer', 'description' => '0-100' ),
				'max_score' => array( 'type' => 'integer', 'description' => '0-100' ),
				'object_type' => array( 'type' => 'string', 'description' => 'post | term | user (default post)' ),
				'limit' => array( 'type' => 'integer' ),
			) ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getSitemapSettings', array(
			'description'  => 'Get the Yoast XML sitemap configuration (enabled, excluded post types/taxonomies) + sitemap URL.',
			'input_schema' => array( 'type' => 'object', 'properties' => (object) array() ),
			'required_scope' => 'read',
		) );
		$this->register_tool( 'getSeoOverview', array(
			'description'  => 'Site-wide SEO overview: counts of noindex / missing-description / low-score / cornerstone content.',
			'input_schema' => array( 'type' => 'object', 'properties' => (object) array() ),
			'required_scope' => 'read',
		) );

		// ── Writes (pro) ──
		$this->register_tool( 'updateSeoMeta', array(
			'description'  => 'Update the SEO title and/or meta description of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ),
				'title'   => array( 'type' => 'string', 'description' => 'SEO title (may use Yoast %%variables%%)' ),
				'metadesc' => array( 'type' => 'string' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'setFocusKeyword', array(
			'description'  => 'Set the primary focus keyword of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id', 'keyword' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ), 'keyword' => array( 'type' => 'string' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'setCanonical', array(
			'description'  => 'Set the canonical URL of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id', 'canonical' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ), 'canonical' => array( 'type' => 'string' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'setRobotsSettings', array(
			'description'  => 'Set robots directives (noindex/nofollow) of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array(
				'post_id'  => array( 'type' => 'integer' ),
				'noindex'  => array( 'type' => 'boolean' ),
				'nofollow' => array( 'type' => 'boolean' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'setCornerstone', array(
			'description'  => 'Flag/unflag a post as cornerstone content.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id', 'cornerstone' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ), 'cornerstone' => array( 'type' => 'boolean' ),
			) ),
			'required_scope' => 'write',
		) );
		$this->register_tool( 'setSocialMeta', array(
			'description'  => 'Set OpenGraph + Twitter social metadata of a post.',
			'input_schema' => array( 'type' => 'object', 'required' => array( 'post_id' ), 'properties' => array(
				'post_id' => array( 'type' => 'integer' ),
				'og_title' => array( 'type' => 'string' ),
				'og_description' => array( 'type' => 'string' ),
				'twitter_title' => array( 'type' => 'string' ),
				'twitter_description' => array( 'type' => 'string' ),
			) ),
			'required_scope' => 'write',
		) );
	}

	private function require_cap( $cap ) {
		if ( ! \current_user_can( $cap ) ) {
			return new \WP_Error( 'forbidden', "{$cap} capability required", array( 'status' => 403 ) );
		}
		return null;
	}

	private function require_post( $params ) {
		$id = \absint( $params['post_id'] ?? 0 );
		if ( ! $id || ! \get_post( $id ) ) {
			return new \WP_Error( 'post_not_found', 'Post not found', array( 'status' => 404 ) );
		}
		return $id;
	}

	public function execute_getSeoMeta( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		return $this->success_response( array(
			'post_id'   => $id,
			'seo_title' => \get_post_meta( $id, '_yoast_wpseo_title', true ),
			'metadesc'  => \get_post_meta( $id, '_yoast_wpseo_metadesc', true ),
			'focus_keyword' => \get_post_meta( $id, '_yoast_wpseo_focuskw', true ),
			'canonical' => \get_post_meta( $id, '_yoast_wpseo_canonical', true ),
		) );
	}

	public function execute_getFocusKeyword( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		return $this->success_response( array( 'post_id' => $id, 'focus_keyword' => \get_post_meta( $id, '_yoast_wpseo_focuskw', true ) ) );
	}

	public function execute_getSeoScore( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		$score = \get_post_meta( $id, '_yoast_wpseo_linkdex', true );
		return $this->success_response( array( 'post_id' => $id, 'seo_score' => $score === '' ? null : (int) $score ) );
	}

	public function execute_getReadabilityScore( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		$score = \get_post_meta( $id, '_yoast_wpseo_content_score', true );
		return $this->success_response( array( 'post_id' => $id, 'readability_score' => $score === '' ? null : (int) $score ) );
	}

	public function execute_getRobotsSettings( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		return $this->success_response( array(
			'post_id'  => $id,
			'noindex'  => \get_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', true ) === '1',
			'nofollow' => \get_post_meta( $id, '_yoast_wpseo_meta-robots-nofollow', true ) === '1',
		) );
	}

	public function execute_getSocialMeta( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		return $this->success_response( array(
			'post_id'          => $id,
			'og_title'         => \get_post_meta( $id, '_yoast_wpseo_opengraph-title', true ),
			'og_description'   => \get_post_meta( $id, '_yoast_wpseo_opengraph-description', true ),
			'og_image'         => \get_post_meta( $id, '_yoast_wpseo_opengraph-image', true ),
			'twitter_title'    => \get_post_meta( $id, '_yoast_wpseo_twitter-title', true ),
			'twitter_description' => \get_post_meta( $id, '_yoast_wpseo_twitter-description', true ),
		) );
	}

	public function execute_listCornerstoneContent( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$limit = \absint( $params['limit'] ?? 50 ) ?: 50;
		$posts = \get_posts( array(
			'post_type'  => 'any', 'numberposts' => $limit, 'post_status' => 'any',
			'meta_key'   => '_yoast_wpseo_is_cornerstone', 'meta_value' => '1',
		) );
		$out = array();
		foreach ( $posts as $p ) {
			$out[] = array( 'post_id' => (int) $p->ID, 'title' => \get_the_title( $p ), 'type' => $p->post_type );
		}
		return $this->success_response( array( 'cornerstone' => $out, 'count' => count( $out ) ) );
	}

	public function execute_searchBySeoScore( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		global $wpdb;
		$min = isset( $params['min_score'] ) ? (int) $params['min_score'] : 0;
		$max = isset( $params['max_score'] ) ? (int) $params['max_score'] : 100;
		$type = \sanitize_key( $params['object_type'] ?? 'post' );
		$limit = \absint( $params['limit'] ?? 50 ) ?: 50;
		$table = $wpdb->prefix . 'yoast_indexable';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return new \WP_Error( 'no_index', 'Yoast indexable table not found', array( 'status' => 501 ) );
		}
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT object_id, title, primary_focus_keyword, primary_focus_keyword_score, readability_score, permalink
			 FROM {$table}
			 WHERE object_type = %s AND primary_focus_keyword_score BETWEEN %d AND %d
			 ORDER BY primary_focus_keyword_score ASC LIMIT %d",
			$type, $min, $max, $limit
		), ARRAY_A );
		return $this->success_response( array( 'results' => $rows ?: array(), 'count' => is_array( $rows ) ? count( $rows ) : 0 ) );
	}

	public function execute_getSitemapSettings( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		$enabled = true;
		if ( class_exists( '\WPSEO_Options' ) ) {
			$enabled = (bool) \WPSEO_Options::get( 'enable_xml_sitemap', true );
		}
		return $this->success_response( array(
			'xml_sitemap_enabled' => $enabled,
			'sitemap_url'         => \home_url( '/sitemap_index.xml' ),
		) );
	}

	public function execute_getSeoOverview( $params ) {
		$deny = $this->require_cap( 'edit_posts' ); if ( $deny ) return $deny;
		global $wpdb;
		$table = $wpdb->prefix . 'yoast_indexable';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return new \WP_Error( 'no_index', 'Yoast indexable table not found', array( 'status' => 501 ) );
		}
		$row = $wpdb->get_row(
			"SELECT
			  SUM(object_type='post') AS posts_indexed,
			  SUM(is_cornerstone=1) AS cornerstone,
			  SUM(is_robots_noindex=1) AS noindex,
			  SUM(description IS NULL OR description='') AS missing_description,
			  SUM(primary_focus_keyword_score < 50 AND primary_focus_keyword_score IS NOT NULL) AS low_seo_score
			 FROM {$table}",
			ARRAY_A
		);
		return $this->success_response( array(
			'posts_indexed'       => (int) ( $row['posts_indexed'] ?? 0 ),
			'cornerstone'         => (int) ( $row['cornerstone'] ?? 0 ),
			'noindex'             => (int) ( $row['noindex'] ?? 0 ),
			'missing_description' => (int) ( $row['missing_description'] ?? 0 ),
			'low_seo_score'       => (int) ( $row['low_seo_score'] ?? 0 ),
		) );
	}

	public function execute_updateSeoMeta( $params ) {
		$deny = $this->require_cap( 'edit_others_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
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
		return $this->success_response( array( 'post_id' => $id, 'updated' => $updated ) );
	}

	public function execute_setFocusKeyword( $params ) {
		$deny = $this->require_cap( 'edit_others_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		\update_post_meta( $id, '_yoast_wpseo_focuskw', \sanitize_text_field( $params['keyword'] ?? '' ) );
		return $this->success_response( array( 'post_id' => $id, 'focus_keyword' => $params['keyword'] ?? '' ) );
	}

	public function execute_setCanonical( $params ) {
		$deny = $this->require_cap( 'edit_others_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		\update_post_meta( $id, '_yoast_wpseo_canonical', \esc_url_raw( $params['canonical'] ?? '' ) );
		return $this->success_response( array( 'post_id' => $id, 'canonical' => $params['canonical'] ?? '' ) );
	}

	public function execute_setRobotsSettings( $params ) {
		$deny = $this->require_cap( 'edit_others_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		if ( isset( $params['noindex'] ) ) {
			\update_post_meta( $id, '_yoast_wpseo_meta-robots-noindex', ! empty( $params['noindex'] ) ? '1' : '2' );
		}
		if ( isset( $params['nofollow'] ) ) {
			\update_post_meta( $id, '_yoast_wpseo_meta-robots-nofollow', ! empty( $params['nofollow'] ) ? '1' : '0' );
		}
		return $this->success_response( array( 'post_id' => $id, 'noindex' => ! empty( $params['noindex'] ), 'nofollow' => ! empty( $params['nofollow'] ) ) );
	}

	public function execute_setCornerstone( $params ) {
		$deny = $this->require_cap( 'edit_others_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		\update_post_meta( $id, '_yoast_wpseo_is_cornerstone', ! empty( $params['cornerstone'] ) ? '1' : '' );
		return $this->success_response( array( 'post_id' => $id, 'cornerstone' => ! empty( $params['cornerstone'] ) ) );
	}

	public function execute_setSocialMeta( $params ) {
		$deny = $this->require_cap( 'edit_others_posts' ); if ( $deny ) return $deny;
		$id = $this->require_post( $params ); if ( \is_wp_error( $id ) ) return $id;
		$map = array(
			'og_title' => '_yoast_wpseo_opengraph-title',
			'og_description' => '_yoast_wpseo_opengraph-description',
			'twitter_title' => '_yoast_wpseo_twitter-title',
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
		return $this->success_response( array( 'post_id' => $id, 'updated' => $updated ) );
	}
}
