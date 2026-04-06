<?php
/**
 * Slot Page block server-side render template.
 *
 * @package WiseRabbit\SlotConsumer
 * @var array|null $slot             The slot data or null.
 * @var bool       $show_image       Show image section.
 * @var bool       $show_rating      Show star rating.
 * @var bool       $show_description Show description.
 * @var bool       $show_provider    Show provider meta.
 * @var bool       $show_rtp         Show RTP meta.
 * @var bool       $show_wager       Show wager range meta.
 * @var string     $style_vars       CSS custom properties string.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( empty( $slot ) ) : ?>
	<div class="wr-sc-slot-detail-empty">
		<p><?php esc_html_e( 'Please select a slot from the block settings.', 'wr-slot-consumer' ); ?></p>
	</div>
<?php else : ?>
	<div class="wr-sc-slot-detail" style="<?php echo esc_attr( $style_vars ); ?>">

		<?php if ( $show_image && ! empty( $slot['featured_image'] ) ) : ?>
			<div class="wr-sc-slot-detail__image">
				<img src="<?php echo esc_url( $slot['featured_image'] ); ?>" alt="<?php echo esc_attr( $slot['title'] ?? '' ); ?>" loading="lazy" />
			</div>
		<?php endif; ?>

		<div class="wr-sc-slot-detail__body">
			<h1 class="wr-sc-slot-detail__title"><?php echo esc_html( $slot['title'] ?? '' ); ?></h1>

			<?php if ( $show_rating && ! empty( $slot['star_rating'] ) ) : ?>
				<div class="wr-sc-slot-detail__rating" aria-label="<?php echo esc_attr( $slot['star_rating'] ); ?> stars">
					<?php
					$rating = (float) $slot['star_rating'];
					for ( $i = 1; $i <= 5; $i++ ) :
						if ( $i <= $rating ) :
							echo '<span class="wr-sc-slot-detail__star wr-sc-slot-detail__star--full">&#9733;</span>';
						elseif ( $i - 0.5 <= $rating ) :
							echo '<span class="wr-sc-slot-detail__star wr-sc-slot-detail__star--half">&#9733;</span>';
						else :
							echo '<span class="wr-sc-slot-detail__star wr-sc-slot-detail__star--empty">&#9734;</span>';
						endif;
					endfor;
					?>
					<span class="wr-sc-slot-detail__rating-text"><?php echo esc_html( $rating ); ?>/5</span>
				</div>
			<?php endif; ?>

			<?php if ( $show_description && ! empty( $slot['description'] ) ) : ?>
				<div class="wr-sc-slot-detail__description">
					<?php echo wp_kses_post( $slot['description'] ); ?>
				</div>
			<?php endif; ?>

			<?php
			$has_meta = ( $show_provider && ! empty( $slot['provider_name'] ) )
				|| ( $show_rtp && ! empty( $slot['rtp'] ) )
				|| ( $show_wager && ( ! empty( $slot['min_wager'] ) || ! empty( $slot['max_wager'] ) ) );
			?>

			<?php if ( $has_meta ) : ?>
				<dl class="wr-sc-slot-detail__meta">
					<?php if ( $show_provider && ! empty( $slot['provider_name'] ) ) : ?>
						<div class="wr-sc-slot-detail__meta-item">
							<dt><?php esc_html_e( 'Provider', 'wr-slot-consumer' ); ?></dt>
							<dd><?php echo esc_html( $slot['provider_name'] ); ?></dd>
						</div>
					<?php endif; ?>

					<?php if ( $show_rtp && ! empty( $slot['rtp'] ) ) : ?>
						<div class="wr-sc-slot-detail__meta-item">
							<dt><?php esc_html_e( 'RTP', 'wr-slot-consumer' ); ?></dt>
							<dd><?php echo esc_html( $slot['rtp'] ); ?>%</dd>
						</div>
					<?php endif; ?>

					<?php if ( $show_wager && ( ! empty( $slot['min_wager'] ) || ! empty( $slot['max_wager'] ) ) ) : ?>
						<div class="wr-sc-slot-detail__meta-item">
							<dt><?php esc_html_e( 'Wager Range', 'wr-slot-consumer' ); ?></dt>
							<dd><?php echo esc_html( $slot['min_wager'] ); ?> &ndash; <?php echo esc_html( $slot['max_wager'] ); ?></dd>
						</div>
					<?php endif; ?>
				</dl>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>
