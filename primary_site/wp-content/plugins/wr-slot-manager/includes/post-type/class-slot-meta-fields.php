<?php
/**
 * Slot meta field registration.
 *
 * @package WiseRabbit\SlotManager\PostType
 */

namespace WiseRabbit\SlotManager\PostType;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class SlotMetaFields
 */
class SlotMetaFields {

	/**
	 * Meta field definitions.
	 *
	 * @var array
	 */
	const FIELDS = array(
		'wr_sm_description'   => array(
			'type'        => 'string',
			'description' => 'Slot description',
			'default'     => '',
		),
		'wr_sm_star_rating'   => array(
			'type'        => 'number',
			'description' => 'Star rating from 1 to 5',
			'default'     => 0,
		),
		'wr_sm_provider_name' => array(
			'type'        => 'string',
			'description' => 'Game provider name',
			'default'     => '',
		),
		'wr_sm_rtp'           => array(
			'type'        => 'number',
			'description' => 'Return to player percentage',
			'default'     => 0,
		),
		'wr_sm_min_wager'     => array(
			'type'        => 'number',
			'description' => 'Minimum wager amount',
			'default'     => 0,
		),
		'wr_sm_max_wager'     => array(
			'type'        => 'number',
			'description' => 'Maximum wager amount',
			'default'     => 0,
		),
		'wr_sm_image_id'      => array(
			'type'        => 'integer',
			'description' => 'Slot image attachment ID',
			'default'     => 0,
		),
	);

	/**
	 * Register all meta fields for the slot post type.
	 *
	 * @return void
	 */
	public function register(): void {
		foreach ( self::FIELDS as $key => $config ) {
			register_post_meta(
				SlotPostType::POST_TYPE,
				$key,
				array(
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => $config['type'],
					'description'   => $config['description'],
					'default'       => $config['default'],
					'auth_callback' => function () {
						return current_user_can( 'edit_posts' );
					},
				)
			);
		}
	}
}
