<?php
/**
 * Software Service BD theme bootstrap.
 *
 * Architecture: WordPress/PHP renders every page in full so that search
 * crawlers and answer engines receive complete HTML. React mounts afterwards
 * onto a handful of "islands" (theme toggle, filters, FAQ accordion, contact
 * form) to add interactivity without removing anything from the document.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

define( 'SSBD_VERSION', '1.6.0' );
define( 'SSBD_DIR', get_template_directory() );
define( 'SSBD_URI', get_template_directory_uri() );

require_once SSBD_DIR . '/inc/setup.php';
require_once SSBD_DIR . '/inc/post-types.php';
require_once SSBD_DIR . '/inc/faq-parser.php';
require_once SSBD_DIR . '/inc/meta-boxes.php';
require_once SSBD_DIR . '/inc/seo-links.php';
require_once SSBD_DIR . '/inc/seo-workbench.php';
require_once SSBD_DIR . '/inc/catalogue.php';
require_once SSBD_DIR . '/inc/affiliates.php';
require_once SSBD_DIR . '/inc/comparisons.php';
require_once SSBD_DIR . '/inc/data.php';
require_once SSBD_DIR . '/inc/template-tags.php';
require_once SSBD_DIR . '/inc/navigation.php';
require_once SSBD_DIR . '/inc/islands.php';
require_once SSBD_DIR . '/inc/enqueue.php';
require_once SSBD_DIR . '/inc/delay-scripts.php';
require_once SSBD_DIR . '/inc/seo.php';
require_once SSBD_DIR . '/inc/schema.php';
require_once SSBD_DIR . '/inc/geo.php';
require_once SSBD_DIR . '/inc/front-page.php';
require_once SSBD_DIR . '/inc/customizer.php';
require_once SSBD_DIR . '/inc/patterns.php';
require_once SSBD_DIR . '/inc/ads.php';
require_once SSBD_DIR . '/inc/contact.php';
require_once SSBD_DIR . '/inc/subscribe.php';
require_once SSBD_DIR . '/inc/feedback.php';
require_once SSBD_DIR . '/inc/orders.php';
require_once SSBD_DIR . '/inc/order-tracking.php';
require_once SSBD_DIR . '/inc/jobs.php';
require_once SSBD_DIR . '/inc/partners.php';
require_once SSBD_DIR . '/inc/blog.php';
require_once SSBD_DIR . '/inc/service-content.php';
require_once SSBD_DIR . '/inc/article.php';
require_once SSBD_DIR . '/inc/avatars.php';
require_once SSBD_DIR . '/inc/accounts.php';
// After delay-scripts.php: its footer buffer has to close inside this one.
require_once SSBD_DIR . '/inc/brand-links.php';
