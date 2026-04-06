<?php
/**
 * Slot custom post type registration.
 *
 * @package WiseRabbit\SlotManager\PostType
 */

namespace WiseRabbit\SlotManager\PostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotPostType
 */
class SlotPostType {

	/**
	 * Post type slug.
	 *
	 * @var string
	 */
	const POST_TYPE = 'slot';

	/**
	 * Register the custom post type.
	 *
	 * @return void
	 */
	public function register(): void {
		$labels = array(
			'name'                  => __( 'Slots', 'wr-slot-manager' ),
			'singular_name'         => __( 'Slot', 'wr-slot-manager' ),
			'add_new'               => __( 'Add New Slot', 'wr-slot-manager' ),
			'add_new_item'          => __( 'Add New Slot', 'wr-slot-manager' ),
			'edit_item'             => __( 'Edit Slot', 'wr-slot-manager' ),
			'new_item'              => __( 'New Slot', 'wr-slot-manager' ),
			'view_item'             => __( 'View Slot', 'wr-slot-manager' ),
			'search_items'          => __( 'Search Slots', 'wr-slot-manager' ),
			'not_found'             => __( 'No slots found', 'wr-slot-manager' ),
			'not_found_in_trash'    => __( 'No slots found in Trash', 'wr-slot-manager' ),
			'all_items'             => __( 'All Slots', 'wr-slot-manager' ),
			'menu_name'             => __( 'Slot Manager', 'wr-slot-manager' ),
			'name_admin_bar'        => __( 'Slot', 'wr-slot-manager' ),
		);

		$args = array(
			'labels'              => $labels,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 20,
			'menu_icon'           => 'dashicons-games',
			'supports'            => array( 'title', 'editor', 'custom-fields' ),
			'has_archive'         => false,
			'rewrite'             => false,
			'show_in_rest'        => true,
			'rest_base'           => 'slots',
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'template'            => array(
				array( 'wr-slot-manager/slot-fields', array(), array() ),
			),
			'template_lock'       => 'all',
		);

		register_post_type( self::POST_TYPE, $args );

		// Force new slots to private status.
		add_filter( 'wp_insert_post_data', array( $this, 'force_private_status' ), 10, 2 );
	}

	/**
	 * Force slot posts to always be private.
	 *
	 * @param array $data    Slashed, sanitized post data.
	 * @param array $postarr Raw post data.
	 * @return array
	 */
	public function force_private_status( array $data, array $postarr ): array {
		if ( self::POST_TYPE === $data['post_type'] && 'trash' !== $data['post_status'] && 'auto-draft' !== $data['post_status'] ) {
			$data['post_status'] = 'private';
		}
		return $data;
	}
}
