<?php
/**
 * One comparison pillar: /comparisons/hosting-domains/ and its siblings.
 *
 * Two layers. The structural one — comparisons, recommended offers, the
 * side-by-side table, the cross-sell banner — runs for every pillar and is
 * what this template always was. On top of it sit the editorial sections from
 * inc/data/pillar-content.php: the intro, quick picks, the decision guide, the
 * explainer, the methodology, the grouped comparisons and the FAQ.
 *
 * Every editorial section is optional and renders only when its data exists,
 * so a pillar nobody has written for produces exactly the page it produced
 * before any of this was added. That is what keeps one template serving three
 * pages at very different stages of completeness.
 *
 * The heading order is fixed and deliberate: one H1 in the hero, and every
 * section below it an H2 whose text says what the section is rather than which
 * keyword it targets.
 *
 * @package ssbd
 *
 * @var array $args['pillar']
 * @var array $args['comparisons']
 */

defined( 'ABSPATH' ) || exit;

$ssbd_pillar      = $args['pillar'];
$ssbd_comparisons = $args['comparisons'];
$ssbd_facets      = $ssbd_comparisons['facets'];
$ssbd_published   = ssbd_matchups_published( $ssbd_pillar['key'] );
$ssbd_roadmap     = ssbd_matchups_roadmap( $ssbd_pillar['key'] );
$ssbd_offers      = ssbd_offers( $ssbd_pillar['offers'], 6 );
$ssbd_banner      = $ssbd_comparisons['cross_sell'][ $ssbd_pillar['key'] ] ?? array();

$ssbd_content = ssbd_pillar_content( $ssbd_pillar['key'] );
$ssbd_trust   = $ssbd_content['trust'] ?? array();

get_template_part( 'template-parts/components/page-hero', null, array(
	'eyebrow' => __( 'Comparisons', 'ssbd' ),
	// The editorial H1 when there is one: the pillar's own title is the
	// internal name for the category, not a phrase anyone searches for.
	'heading' => $ssbd_content['h1'] ?? $ssbd_pillar['title'],
	'lede'    => $ssbd_content['lede'] ?? $ssbd_pillar['desc'],
	// Three pillar URLs share the Comparisons page, so its featured image
	// would appear on all of them as if it belonged to each.
	'image'   => false,
) );
?>

<?php if ( $ssbd_trust || ! empty( $ssbd_content['intro'] ) ) : ?>
	<section class="section section--tight" aria-labelledby="overview-heading">
		<div class="wrap">
			<h2 id="overview-heading" class="screen-reader-text"><?php esc_html_e( 'Overview', 'ssbd' ); ?></h2>

			<?php if ( $ssbd_trust ) : ?>
				<?php
				/*
				 * Who stands behind the page, what the recommendations rest on,
				 * and when it was last checked. Deliberately plain: no invented
				 * job titles, no claim to have benchmarked anything.
				 */
				?>
				<div class="editorial-meta">
					<?php if ( ! empty( $ssbd_trust['reviewed_by'] ) ) : ?>
						<span class="editorial-meta-item">
							<?php
							printf(
								/* translators: %s: who reviewed the page */
								esc_html__( 'Reviewed by %s', 'ssbd' ),
								esc_html( $ssbd_trust['reviewed_by'] )
							);
							?>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $ssbd_trust['updated'] ) ) : ?>
						<span class="editorial-meta-item">
							<?php esc_html_e( 'Last updated', 'ssbd' ); ?>
							<time datetime="<?php echo esc_attr( $ssbd_trust['updated'] ); ?>">
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $ssbd_trust['updated'] ) ) ); ?>
							</time>
						</span>
					<?php endif; ?>

					<?php if ( ! empty( $ssbd_trust['basis'] ) ) : ?>
						<span class="editorial-meta-item"><?php echo esc_html( $ssbd_trust['basis'] ); ?></span>
					<?php endif; ?>

					<a class="editorial-meta-link" href="#methodology"><?php esc_html_e( 'How we compare', 'ssbd' ); ?></a>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $ssbd_content['intro'] ) ) : ?>
				<div class="editorial-intro">
					<?php foreach ( $ssbd_content['intro'] as $ssbd_para ) : ?>
						<?php
						// **bold** is the only markup allowed through, so the
						// data file stays prose rather than becoming HTML.
						echo '<p>' . wp_kses(
							preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', esc_html( $ssbd_para ) ),
							array( 'strong' => array() )
						) . '</p>';
						?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php if ( ssbd_page_has_affiliate_links() ) : ?>
				<div class="editorial-disclosure"><?php ssbd_affiliate_disclosure(); ?></div>
			<?php endif; ?>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $ssbd_content['quick_picks'] ) && $ssbd_offers ) : ?>
	<section class="section section--soft" id="quick-picks" aria-labelledby="quick-picks-heading">
		<div class="wrap">
			<?php
			ssbd_section_head(
				__( 'Quick picks', 'ssbd' ),
				__( 'Not a ranking. Each one is the provider we would choose for that particular job, and why.', 'ssbd' ),
				array( 'id' => 'quick-picks-heading', 'align' => 'split' )
			);
			?>

			<div class="grid grid--3">
				<?php foreach ( $ssbd_content['quick_picks'] as $ssbd_pick ) : ?>
					<?php
					// A pick naming an offer that is not published simply does
					// not render — no card pointing at nothing.
					$ssbd_offer = ssbd_offer_by_slug( $ssbd_offers, $ssbd_pick['offer'] ?? '' );

					if ( ! $ssbd_offer ) {
						continue;
					}
					?>
					<article class="card card--raised pick-card">
						<span class="pick-label"><?php echo esc_html( $ssbd_pick['label'] ); ?></span>
						<h3><?php echo esc_html( $ssbd_offer['name'] ); ?></h3>
						<p><?php echo esc_html( $ssbd_pick['why'] ); ?></p>

						<?php if ( $ssbd_offer['price'] ) : ?>
							<span class="pick-price"><?php echo esc_html( $ssbd_offer['price'] ); ?></span>
						<?php endif; ?>

						<div class="pick-actions">
							<?php ssbd_offer_button( $ssbd_offer, 'btn btn--sm btn--accent' ); ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $ssbd_content['decision'] ) ) : ?>
	<section class="section" id="which" aria-labelledby="which-heading">
		<div class="wrap">
			<?php
			ssbd_section_head(
				__( 'Which hosting do you need?', 'ssbd' ),
				__( 'Find the line that describes your situation. The type matters more than the brand.', 'ssbd' ),
				array( 'id' => 'which-heading', 'align' => 'split' )
			);
			?>

			<?php // A description list, because that is exactly what it is. ?>
			<dl class="decide">
				<?php foreach ( $ssbd_content['decision'] as $ssbd_row ) : ?>
					<div class="decide-row">
						<dt class="decide-if"><?php echo esc_html( $ssbd_row['if'] ); ?></dt>
						<dd class="decide-then">
							<strong><?php echo esc_html( $ssbd_row['then'] ); ?></strong>
							<span><?php echo esc_html( $ssbd_row['note'] ); ?></span>
						</dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</div>
	</section>
<?php endif; ?>

<?php if ( ! empty( $ssbd_content['types'] ) ) : ?>
	<section class="section section--surface section--bordered" id="types" aria-labelledby="types-heading">
		<div class="wrap">
			<?php
			ssbd_section_head(
				__( 'Hosting types explained', 'ssbd' ),
				__( 'What each kind actually is, who it suits, and where it stops being the right answer.', 'ssbd' ),
				array( 'id' => 'types-heading', 'align' => 'split' )
			);
			?>

			<div class="type-list">
				<?php foreach ( $ssbd_content['types'] as $ssbd_type ) : ?>
					<article class="type-card">
						<h3><?php echo esc_html( $ssbd_type['name'] ); ?></h3>
						<p class="type-what"><?php echo esc_html( $ssbd_type['what'] ); ?></p>

						<div class="type-grid">
							<div>
								<h4><?php esc_html_e( 'Who it is for', 'ssbd' ); ?></h4>
								<p><?php echo esc_html( $ssbd_type['who'] ); ?></p>
							</div>

							<div>
								<h4><?php esc_html_e( 'Strengths', 'ssbd' ); ?></h4>
								<ul class="type-pros">
									<?php foreach ( $ssbd_type['pros'] as $ssbd_item ) : ?>
										<li><?php echo esc_html( $ssbd_item ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>

							<div>
								<h4><?php esc_html_e( 'Trade-offs', 'ssbd' ); ?></h4>
								<ul class="type-cons">
									<?php foreach ( $ssbd_type['cons'] as $ssbd_item ) : ?>
										<li><?php echo esc_html( $ssbd_item ); ?></li>
									<?php endforeach; ?>
								</ul>
							</div>
						</div>

						<p class="type-use">
							<strong><?php esc_html_e( 'Typical use:', 'ssbd' ); ?></strong>
							<?php echo esc_html( $ssbd_type['use'] ); ?>
						</p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<?php if ( $ssbd_offers ) : ?>
	<section class="section" id="picks" aria-labelledby="pillar-picks-heading">
		<div class="wrap">
			<?php
			ssbd_section_head(
				__( 'Providers we recommend', 'ssbd' ),
				__( 'The services we run on our own and client projects. Every one has its downsides listed too.', 'ssbd' ),
				array( 'id' => 'pillar-picks-heading', 'align' => 'split' )
			);
			?>

			<div class="grid grid--3 offer-grid">
				<?php foreach ( $ssbd_offers as $ssbd_offer ) : ?>
					<?php get_template_part( 'template-parts/components/offer-card', null, array( 'offer' => $ssbd_offer ) ); ?>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( count( $ssbd_offers ) > 1 ) : ?>
		<section class="section section--soft" id="table" aria-labelledby="table-heading">
			<div class="wrap">
				<?php
				ssbd_section_head(
					__( 'Side-by-side comparison', 'ssbd' ),
					'',
					array( 'id' => 'table-heading', 'align' => 'left' )
				);

				get_template_part( 'template-parts/components/offer-table', null, array(
					'offers'   => array_slice( $ssbd_offers, 0, 4 ),
					'category' => $ssbd_pillar['offers'],
					'caption'  => __( 'Entry pricing and the included features we have verified for the providers above. Prices are the advertised starting rate, not the renewal rate — confirm both with the vendor before buying.', 'ssbd' ),
				) );
				?>

				<?php if ( ! empty( $ssbd_trust['prices_note'] ) ) : ?>
					<p class="section-note"><?php echo esc_html( $ssbd_trust['prices_note'] ); ?></p>
				<?php endif; ?>
			</div>
		</section>
	<?php endif; ?>
<?php endif; ?>

<?php
/*
 * The methodology. Kept where it is — after the recommendations, before the
 * comparison articles — because it answers the question a reader has only
 * once they have seen what is being recommended.
 */
$ssbd_method = $ssbd_content['methodology'] ?? array();
?>
<section class="section" id="methodology" aria-labelledby="pillar-criteria-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			$ssbd_method ? __( 'How we compare hosting providers', 'ssbd' ) : __( 'How we judge', 'ssbd' ),
			$ssbd_method['lede'] ?? '',
			array( 'id' => 'pillar-criteria-heading', 'align' => $ssbd_method ? 'split' : 'center' )
		);
		?>

		<?php if ( ! empty( $ssbd_method['items'] ) ) : ?>
			<ol class="method-list">
				<?php foreach ( $ssbd_method['items'] as $ssbd_i => $ssbd_item ) : ?>
					<li class="method-item">
						<span class="method-no" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $ssbd_i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
						<div>
							<h3><?php echo esc_html( $ssbd_item['title'] ); ?></h3>
							<p><?php echo esc_html( $ssbd_item['desc'] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>

			<?php if ( ! empty( $ssbd_method['note'] ) ) : ?>
				<p class="method-note"><?php echo esc_html( $ssbd_method['note'] ); ?></p>
			<?php endif; ?>

		<?php else : ?>
			<?php // The original four-card version, for a pillar with no methodology written. ?>
			<div class="grid grid--4">
				<?php foreach ( $ssbd_comparisons['criteria'] as $ssbd_criterion ) : ?>
					<article class="card card--raised">
						<h3 style="font-size:16px"><?php echo esc_html( $ssbd_criterion['title'] ); ?></h3>
						<p><?php echo esc_html( $ssbd_criterion['desc'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>

<section class="section section--soft" id="comparisons" aria-labelledby="pillar-matchups-heading">
	<div class="wrap">
		<?php
		ssbd_section_head(
			__( 'Comparisons in this category', 'ssbd' ),
			$ssbd_published
				? __( 'Head-to-head write-ups, grouped by the question they answer.', 'ssbd' )
				: __( 'The head-to-head write-ups are in progress. What we recommend today is above.', 'ssbd' ),
			array( 'id' => 'pillar-matchups-heading', 'align' => 'split' )
		);
		?>

		<?php if ( ! empty( $ssbd_content['groups'] ) ) : ?>
			<?php
			/*
			 * Grouped by search intent rather than listed flat. Each group names
			 * matchups by post slug and ssbd_matchups_by_slug() resolves them —
			 * so an unwritten comparison arrives with no URL and renders as
			 * planned. Nothing in this block can produce a dead link.
			 */
			?>
			<div class="group-list">
				<?php foreach ( $ssbd_content['groups'] as $ssbd_group ) : ?>
					<?php
					$ssbd_rows  = ssbd_matchups_by_slug( $ssbd_group['matchups'] ?? array(), $ssbd_pillar['key'] );
					$ssbd_links = $ssbd_group['links'] ?? array();

					if ( ! $ssbd_rows && ! $ssbd_links ) {
						continue;
					}
					?>
					<section class="group" aria-labelledby="group-<?php echo esc_attr( sanitize_title( $ssbd_group['title'] ) ); ?>">
						<h3 class="group-title" id="group-<?php echo esc_attr( sanitize_title( $ssbd_group['title'] ) ); ?>">
							<?php echo esc_html( $ssbd_group['title'] ); ?>
						</h3>

						<?php if ( ! empty( $ssbd_group['desc'] ) ) : ?>
							<p class="group-desc"><?php echo esc_html( $ssbd_group['desc'] ); ?></p>
						<?php endif; ?>

						<ul class="group-links">
							<?php foreach ( $ssbd_rows as $ssbd_row ) : ?>
								<li class="group-link<?php echo $ssbd_row['published'] ? '' : ' is-planned'; ?>">
									<?php if ( $ssbd_row['published'] ) : ?>
										<a href="<?php echo esc_url( $ssbd_row['url'] ); ?>">
											<span class="group-link-title"><?php echo esc_html( $ssbd_row['title'] ); ?></span>
											<span class="group-link-intent"><?php echo esc_html( $ssbd_row['intent'] ); ?></span>
										</a>
									<?php else : ?>
										<span class="group-link-title"><?php echo esc_html( $ssbd_row['title'] ); ?></span>
										<span class="group-link-intent"><?php echo esc_html( $ssbd_row['intent'] ); ?></span>
										<span class="group-soon"><?php esc_html_e( 'Coming soon', 'ssbd' ); ?></span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>

							<?php foreach ( $ssbd_links as $ssbd_link ) : ?>
								<?php
								$ssbd_href = '';

								if ( ! empty( $ssbd_link['pillar'] ) ) {
									$ssbd_href = ssbd_pillar_url( $ssbd_link['pillar'] );
								} elseif ( ! empty( $ssbd_link['service'] ) ) {
									$ssbd_service = ssbd_service( $ssbd_link['service'] );
									$ssbd_href    = $ssbd_service ? ssbd_service_url( $ssbd_service ) : '';
								}

								if ( ! $ssbd_href ) {
									continue;
								}
								?>
								<li class="group-link">
									<a href="<?php echo esc_url( $ssbd_href ); ?>">
										<span class="group-link-title"><?php echo esc_html( $ssbd_link['label'] ); ?></span>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					</section>
				<?php endforeach; ?>
			</div>

		<?php else : ?>
			<?php // No groups written: the flat table this page always had. ?>
			<?php get_template_part( 'template-parts/comparisons/table', null, array(
				'matchups'    => $ssbd_published,
				'facets'      => $ssbd_facets,
				'pillars'     => array(),
				'show_pillar' => false,
			) ); ?>
		<?php endif; ?>

		<?php get_template_part( 'template-parts/comparisons/roadmap', null, array(
			'matchups' => $ssbd_roadmap,
		) ); ?>
	</div>
</section>

<?php
if ( ! empty( $ssbd_content['faqs'] ) ) {
	get_template_part( 'template-parts/components/faq', null, array( 'faqs' => $ssbd_content['faqs'] ) );
}
?>

<?php if ( ! empty( $ssbd_content['related'] ) ) : ?>
	<section class="section section--soft" aria-labelledby="related-guides-heading">
		<div class="wrap">
			<?php
			ssbd_section_head(
				__( 'Related guides and services', 'ssbd' ),
				'',
				array( 'id' => 'related-guides-heading', 'align' => 'left' )
			);
			?>

			<div class="grid grid--3">
				<?php foreach ( $ssbd_content['related'] as $ssbd_item ) : ?>
					<?php
					$ssbd_href = '';

					if ( ! empty( $ssbd_item['pillar'] ) ) {
						$ssbd_href = ssbd_pillar_url( $ssbd_item['pillar'] );
					} elseif ( ! empty( $ssbd_item['service'] ) ) {
						$ssbd_service = ssbd_service( $ssbd_item['service'] );
						$ssbd_href    = $ssbd_service ? ssbd_service_url( $ssbd_service ) : '';
					}

					// A link the site cannot resolve is dropped, not guessed at.
					if ( ! $ssbd_href ) {
						continue;
					}
					?>
					<a class="card related-card" href="<?php echo esc_url( $ssbd_href ); ?>">
						<h3 style="font-size:16px"><?php echo esc_html( $ssbd_item['label'] ); ?></h3>
						<?php if ( ! empty( $ssbd_item['desc'] ) ) : ?>
							<p><?php echo esc_html( $ssbd_item['desc'] ); ?></p>
						<?php endif; ?>
						<span class="link-cta"><?php esc_html_e( 'Read more', 'ssbd' ); ?> <span class="arrow" aria-hidden="true">&rarr;</span></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>
<?php endif; ?>

<section class="section">
	<div class="wrap">
		<?php get_template_part( 'template-parts/components/cross-sell', null, array(
			'banner' => $ssbd_banner,
			'url'    => ssbd_pillar_url( $ssbd_banner['target'] ?? '' ),
		) ); ?>

		<?php if ( ssbd_page_has_affiliate_links() ) : ?>
			<div style="margin-top:32px"><?php ssbd_affiliate_disclosure(); ?></div>
		<?php endif; ?>

		<p class="section-note">
			<a href="<?php echo esc_url( ssbd_page_url( 'comparisons' ) ); ?>"><?php esc_html_e( 'All comparison categories', 'ssbd' ); ?></a>
		</p>
	</div>
</section>
