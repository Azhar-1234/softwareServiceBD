Eight service landing-page data files, disabled 2026-09-06
------------------------------------------------------------
wordpress-development.php.bak, web-development.php.bak, app-development.php.bak,
laravel-development.php.bak, software-development.php.bak, ai-integration.php.bak,
domain-hosting.php.bak, website-maintenance-support.php.bak

Each was inc/data/<slug>.php: the rich 5-section landing page (hero copy,
"What We Build" cards, showcase labels) for that service. While a matching
file sat in inc/data/, single-ssbd_service.php rendered that service through
template-parts/services/service-page.php, which builds every visible section
from this file — the service's own wp-admin fields (Short/Full description,
Items, Pills, Icon) were read for their slug only and never shown on the
page. Nothing in the meta box looked different for these eight services, so
editing one and finding the edit invisible was not something a site owner
could have diagnosed from wp-admin alone.

Moved out of inc/data/, and the branch in single-ssbd_service.php that
detects a file here now sits behind a filter that defaults to false — so
this is not "don't put files back here", it is off. Every service, including
one created after this note, renders through the plain layout, which is the
one the wp-admin meta box actually controls.

To bring the rich layout back — for one of these eight, or a new service —
add this to a plugin or a child theme:

    add_filter( 'ssbd_service_landing_pages', '__return_true' );

and put the data file back at inc/data/<slug>.php (move a .bak here back to
its original name and location, or write a new one in the same shape). The
content in every .bak file here is unchanged from when it was live.
