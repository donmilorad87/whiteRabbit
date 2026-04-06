<?php
/**
 * Slot Grid block server-side render template.
 *
 * @package WiseRabbit\SlotConsumer
 * @var array  $slots           Slot data array.
 * @var int    $columns         Grid columns.
 * @var string $link_mode       'page' or 'popup'.
 * @var string $detail_page_url Detail page URL.
 * @var bool   $show_more_info  Show the More Info button.
 * @var string $style_vars      CSS custom properties string.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( empty( $slots ) ) : ?>
	<div class="wr-sc-slot-grid-empty">
		<p><?php esc_html_e( 'No slots available. Please sync data from the primary site.', 'wr-slot-consumer' ); ?></p>
	</div>
<?php else : ?>
	<div class="wr-sc-slot-grid-wrapper" style="<?php echo esc_attr( $style_vars ); ?>">
		<div class="wr-sc-slot-grid">
			<?php foreach ( $slots as $slot ) : ?>
				<article class="wr-sc-slot-card">
					<?php if ( ! empty( $slot['featured_image'] ) ) : ?>
						<div class="wr-sc-slot-card__image">
							<img src="<?php echo esc_url( $slot['featured_image'] ); ?>" alt="<?php echo esc_attr( $slot['title'] ?? '' ); ?>" loading="lazy" />
						</div>
					<?php endif; ?>

					<div class="wr-sc-slot-card__content">
						<h3 class="wr-sc-slot-card__title"><?php echo esc_html( $slot['title'] ?? '' ); ?></h3>

						<?php if ( ! empty( $slot['star_rating'] ) ) : ?>
							<div class="wr-sc-slot-card__rating" aria-label="<?php echo esc_attr( $slot['star_rating'] ); ?> stars">
								<?php
								$rating      = (float) $slot['star_rating'];
								$border_css  = $star_border_color ? '-webkit-text-stroke:1px ' . esc_attr( $star_border_color ) . ';' : '';
								$size_css    = 'font-size:' . esc_attr( $star_font_size ) . ';';
								$base_style  = $size_css . $border_css;

								for ( $i = 1; $i <= 5; $i++ ) :
									if ( $i <= $rating ) :
										echo '<span class="wr-sc-star" style="' . $base_style . 'color:' . esc_attr( $star_full_color ) . ';">&#9733;</span>';
									elseif ( $i - 0.5 <= $rating ) :
										echo '<span class="wr-sc-star" style="' . $base_style . 'color:' . esc_attr( $star_half_color ) . ';">&#9733;</span>';
									else :
										echo '<span class="wr-sc-star" style="' . $base_style . 'color:' . esc_attr( $star_empty_color ) . ';">&#9734;</span>';
									endif;
								endfor;
								?>
								<span class="wr-sc-slot-card__rating-number"><?php echo esc_html( $rating ); ?>/5</span>
							</div>
						<?php endif; ?>

						<?php if ( ! empty( $slot['provider_name'] ) ) : ?>
							<span class="wr-sc-slot-card__provider"><?php echo esc_html( $slot['provider_name'] ); ?></span>
						<?php endif; ?>

						<?php if ( $show_more_info ) : ?>
							<?php if ( 'popup' === $link_mode ) : ?>
								<button type="button"
									class="wr-sc-slot-card__btn wr-sc-popup-trigger"
									data-slot="<?php echo esc_attr( wp_json_encode( $slot ) ); ?>">
									<?php echo esc_html( $more_info_label ); ?>
								</button>
							<?php else : ?>
								<?php
								$link = ! empty( $detail_page_url )
									? esc_url( trailingslashit( $detail_page_url ) . '?slot_detail=' . ( $slot['id'] ?? '' ) )
									: esc_url( '?slot_detail=' . ( $slot['id'] ?? '' ) );
								?>
								<a href="<?php echo esc_url( $link ); ?>" class="wr-sc-slot-card__btn">
									<?php echo esc_html( $more_info_label ); ?>
								</a>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</article>
			<?php endforeach; ?>
		</div>

		<?php if ( 'pagination' === $pagination_mode && $total_pages > 1 ) : ?>
			<nav class="wr-sc-pagination" aria-label="<?php esc_attr_e( 'Slot pages', 'wr-slot-consumer' ); ?>">
				<?php if ( $current_page > 1 ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'sg_page', $current_page - 1 ) ); ?>" class="wr-sc-pagination__link">&laquo; <?php esc_html_e( 'Prev', 'wr-slot-consumer' ); ?></a>
				<?php endif; ?>
				<?php for ( $p = 1; $p <= $total_pages; $p++ ) : ?>
					<?php if ( $p === $current_page ) : ?>
						<span class="wr-sc-pagination__link wr-sc-pagination__link--active"><?php echo esc_html( $p ); ?></span>
					<?php else : ?>
						<a href="<?php echo esc_url( add_query_arg( 'sg_page', $p ) ); ?>" class="wr-sc-pagination__link"><?php echo esc_html( $p ); ?></a>
					<?php endif; ?>
				<?php endfor; ?>
				<?php if ( $current_page < $total_pages ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'sg_page', $current_page + 1 ) ); ?>" class="wr-sc-pagination__link"><?php esc_html_e( 'Next', 'wr-slot-consumer' ); ?> &raquo;</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

		<?php if ( 'loadmore' === $pagination_mode && ! empty( $remaining_slots ) ) : ?>
			<div class="wr-sc-loadmore"
				data-mode="<?php echo esc_attr( $load_more_type ); ?>"
				data-per-page="<?php echo esc_attr( $per_page ); ?>"
				data-remaining="<?php echo esc_attr( wp_json_encode( $remaining_slots ) ); ?>"
				data-link-mode="<?php echo esc_attr( $link_mode ); ?>"
				data-detail-url="<?php echo esc_attr( $detail_page_url ); ?>"
				data-show-btn="<?php echo esc_attr( $show_more_info ? '1' : '0' ); ?>"
				data-star-full="<?php echo esc_attr( $star_full_color ); ?>"
				data-star-half="<?php echo esc_attr( $star_half_color ); ?>"
				data-star-empty="<?php echo esc_attr( $star_empty_color ); ?>"
				data-star-border="<?php echo esc_attr( $star_border_color ); ?>"
				data-star-size="<?php echo esc_attr( $star_font_size ); ?>">
				<?php if ( 'button' === $load_more_type ) : ?>
					<button type="button" class="wr-sc-loadmore__btn">
						<?php esc_html_e( 'Load More', 'wr-slot-consumer' ); ?>
					</button>
				<?php else : ?>
					<div class="wr-sc-loadmore__sentinel">
						<div class="wr-sc-loadmore__spinner"></div>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( 'popup' === $link_mode ) : ?>
		<dialog class="wr-sc-dialog" id="wr-sc-dialog">
			<form method="dialog" class="wr-sc-dialog__inner">
				<button type="submit" class="wr-sc-dialog__close" aria-label="<?php esc_attr_e( 'Close', 'wr-slot-consumer' ); ?>">&times;</button>
				<div class="wr-sc-dialog__image"></div>
				<div class="wr-sc-dialog__body">
					<h2 class="wr-sc-dialog__title"></h2>
					<div class="wr-sc-dialog__rating"></div>
					<p class="wr-sc-dialog__description"></p>
					<dl class="wr-sc-dialog__meta">
						<div class="wr-sc-dialog__meta-item wr-sc-dialog__provider">
							<dt><?php esc_html_e( 'Provider', 'wr-slot-consumer' ); ?></dt>
							<dd></dd>
						</div>
						<div class="wr-sc-dialog__meta-item wr-sc-dialog__rtp">
							<dt><?php esc_html_e( 'RTP', 'wr-slot-consumer' ); ?></dt>
							<dd></dd>
						</div>
						<div class="wr-sc-dialog__meta-item wr-sc-dialog__wager">
							<dt><?php esc_html_e( 'Wager Range', 'wr-slot-consumer' ); ?></dt>
							<dd></dd>
						</div>
					</dl>
				</div>
			</form>
		</dialog>
	<?php endif; ?>
<?php endif; ?>
