<?php
/**
 * Long-form editorial content for the comparison pillar pages.
 *
 * Split out of inc/data/comparisons.php, which owns the structure — pillars,
 * matchups, facets. This owns the prose: the intro, the methodology, the
 * explainers, the decision guide and the FAQ that turn a listing into a page
 * worth landing on.
 *
 * Keyed by pillar key. Every section is optional and the template skips what
 * is missing, so a pillar with nothing here renders exactly as it did before.
 * Today only 'infrastructure' is written; the other two are stubs waiting for
 * the same treatment.
 *
 * NOTHING HERE STATES A PRICE OR A VENDOR FEATURE. Those live on the offer
 * posts in wp-admin, where they can be checked and dated. Prose that quotes a
 * price goes stale silently and there is no way to audit it — so the writing
 * here explains how to choose, and the numbers stay in one place.
 *
 * @package ssbd
 */

defined( 'ABSPATH' ) || exit;

return array(
	'infrastructure' => array(

		/*
		 * The H1 and lede override the pillar's own title and description.
		 * "Website Infrastructure" is what the category is called internally;
		 * it is not what anybody types into a search box.
		 */
		'h1'   => 'Web Hosting, Domains & Cloud Compared',

		/*
		 * The <title> and meta description. Written to length rather than
		 * trimmed: the title sits inside 60 characters with the site name
		 * appended, the description inside 160.
		 */
		'seo_title'        => 'Web Hosting Comparisons & Best Hosting Picks',
		'meta_description' => 'Independent web hosting comparisons — shared, VPS, cloud and managed WordPress. Which host suits WordPress, Laravel, a first site or a growing store.',

		'lede' => 'Independent comparisons of the hosting, domain and cloud services a website actually runs on — shared, VPS, cloud and managed WordPress, judged on what they cost to renew, how they hold up under traffic, and who each one genuinely suits.',

		/*
		 * The editorial opener. Written to answer "which kind of hosting do I
		 * need" before the page starts recommending anything, because that is
		 * the question underneath almost every hosting search.
		 */
		'intro' => array(
			'Hosting is the one decision on a website that is expensive to reverse. A theme can be swapped in an afternoon and a plugin in ten minutes, but the host holds the files, the database, the email routing and the DNS — and moving all four to a new provider is a migration, not a settings change. It is worth twenty minutes of reading before the first payment.',

			'The choice is really between five things. **Shared hosting** puts your site on a server with hundreds of others and splits the cost between everyone; it is the cheapest way to be online and it is perfectly adequate for a brochure site, a portfolio or a local business that gets a few hundred visitors a day. **Managed WordPress hosting** is shared or cloud hosting with the WordPress-specific work — updates, caching, staging, security rules — taken off your hands, which is worth paying for when nobody on your side wants to think about WordPress maintenance. **VPS hosting** gives you a guaranteed slice of a server with root access, and with it the entire responsibility for securing and updating that server. **Cloud hosting** is the same idea with resources you can grow without a migration. **Managed cloud** sits on top of a cloud provider and handles the server administration for you, which is the usual answer for a growing store or a Laravel application whose team would rather ship features than patch a kernel.',

			'A beginner launching a first site should almost always start on shared hosting. The resources are enough, the control panels are built for people who have never used SSH, and the money saved is better spent on the site itself. The mistake is not starting cheap — it is staying there after the traffic arrives, because shared plans throttle rather than scale, and the symptom is a slow site rather than an error message telling you why.',

			'A developer deploying Laravel, a queue worker, a custom PHP version or anything that needs a command line has effectively already chosen VPS or cloud. Shared hosting will not give you the access, and fighting it costs more hours than the price difference saves. The real decision at that point is whether you want to administer the server yourself or pay a managed layer to do it.',

			'Price is the worst single criterion, for one specific reason: the advertised figure is almost always a first-term promotional rate, and the renewal is frequently two or three times higher. A plan at $2.99 that renews at $10.99 is a $10.99 plan you get a discount on once. Compare renewal prices, check what the backup and SSL cost when they are not bundled, and treat migration support as part of the price — moving a site yourself is a day of work.',
		),

		/*
		 * Quick picks. Each one names an offer by slug so the card links to the
		 * live offer rather than a hardcoded URL, and says why in a sentence
		 * that is a judgement, not a ranking.
		 */
		'quick_picks' => array(
			array(
				'label' => 'Best for a first website',
				'offer' => 'hostinger',
				'why'   => 'The cheapest credible entry point, with a control panel a non-technical client can be handed. Watch the renewal rate.',
			),
			array(
				'label' => 'Best for registering domains',
				'offer' => 'namecheap',
				'why'   => 'Predictable pricing, WHOIS privacy included, and DNS management that does not fight you when you point a domain elsewhere.',
			),
			array(
				'label' => 'Best for Laravel and growing stores',
				'offer' => 'cloudways',
				'why'   => 'A managed layer over real cloud infrastructure: staging, cloning and PHP versions you control, without administering the server.',
			),
			array(
				'label' => 'Best for developers who want control',
				'offer' => 'digitalocean',
				'why'   => 'Flat pricing, root access and documentation worth reading. Everything — security, updates, backups — is yours to run.',
			),
			array(
				'label' => 'Best for high-traffic WordPress',
				'offer' => 'kinsta',
				'why'   => 'Premium managed WordPress that stays fast under load, with support staffed by people who know WordPress. Priced accordingly.',
			),
		),

		/*
		 * The explainer. Informational intent on a page that is otherwise
		 * commercial — and the section most likely to earn a link.
		 */
		'types' => array(
			array(
				'name'  => 'Shared hosting',
				'what'  => 'Your site sits on one server alongside hundreds of others, sharing its CPU, memory and disk.',
				'who'   => 'First websites, brochure sites, portfolios, local businesses.',
				'pros'  => array( 'Cheapest way to be online', 'Control panels built for non-technical owners', 'Nothing to administer' ),
				'cons'  => array( 'Neighbours on the same server affect your speed', 'Plans throttle instead of scaling', 'Renewal is often several times the intro price' ),
				'use'   => 'A five-page business site that needs to exist, load quickly and cost almost nothing.',
			),
			array(
				'name'  => 'Managed WordPress hosting',
				'what'  => 'Hosting tuned for one application, with WordPress updates, caching, staging and security handled by the host.',
				'who'   => 'Businesses running WordPress with nobody in-house to maintain it.',
				'pros'  => array( 'WordPress-aware caching and support', 'Staging sites and backups included', 'Fewer ways to break the site' ),
				'cons'  => array( 'Costs more than equivalent shared hosting', 'Plugin restrictions on some hosts', 'Rarely includes email hosting' ),
				'use'   => 'A content site or a WooCommerce store where an hour of downtime costs more than a month of hosting.',
			),
			array(
				'name'  => 'VPS hosting',
				'what'  => 'A guaranteed slice of a physical server, with root access and no neighbours competing for your resources.',
				'who'   => 'Developers, custom applications, anything needing a specific stack.',
				'pros'  => array( 'Full root access and any software you need', 'Predictable, dedicated resources', 'Usually cheaper than managed cloud at the same size' ),
				'cons'  => array( 'Security, patching and backups are entirely yours', 'No friendly control panel unless you install one', 'Wrong choice to hand to a non-technical client' ),
				'use'   => 'A Laravel application with queue workers, a scheduler and a PHP version you chose.',
			),
			array(
				'name'  => 'Cloud hosting',
				'what'  => 'Resources drawn from a pool rather than one machine, so capacity can grow without moving the site.',
				'who'   => 'Sites whose traffic is growing or spikes unpredictably.',
				'pros'  => array( 'Scales without a migration', 'Survives the failure of a single machine', 'Pay for what the site actually uses' ),
				'cons'  => array( 'Costs are variable and can surprise you', 'Still needs administering unless managed', 'More concepts to learn than a control panel' ),
				'use'   => 'A campaign site or a store that goes from a hundred visitors a day to ten thousand for a week.',
			),
			array(
				'name'  => 'Managed cloud hosting',
				'what'  => 'A management layer over a cloud provider that handles provisioning, stack configuration, caching and backups for you.',
				'who'   => 'Growing stores, agencies, and teams who would rather ship than patch servers.',
				'pros'  => array( 'Cloud performance without server administration', 'Staging, cloning and one-click stack changes', 'Scales by resizing rather than migrating' ),
				'cons'  => array( 'A markup on top of the underlying cloud bill', 'Usually no domain registration or email hosting', 'Still assumes some technical literacy' ),
				'use'   => 'A WooCommerce store or Laravel app that has outgrown shared hosting but has no sysadmin.',
			),
		),

		/*
		 * The decision guide. Deliberately written as if → then, because that
		 * is the shape of the question a reader arrives with.
		 */
		'decision' => array(
			array( 'if' => 'You are launching your first website', 'then' => 'Shared hosting', 'note' => 'Enough resources, nothing to administer, and the cheapest way to find out whether the site works.' ),
			array( 'if' => 'You run WordPress for a business and nobody maintains it', 'then' => 'Managed WordPress', 'note' => 'You are buying the updates, the caching and someone to call — not just the disk space.' ),
			array( 'if' => 'You are deploying Laravel', 'then' => 'VPS or managed cloud', 'note' => 'You need the command line, a chosen PHP version, queues and a scheduler. Shared hosting gives you none of them.' ),
			array( 'if' => 'Traffic is growing faster than you expected', 'then' => 'Cloud hosting', 'note' => 'Resize rather than migrate. Moving a busy site under pressure is the worst time to move it.' ),
			array( 'if' => 'You want cloud performance without administering a server', 'then' => 'Managed cloud', 'note' => 'A markup over the raw cloud bill, in exchange for not being responsible for the operating system.' ),
			array( 'if' => 'You are a developer who wants full control', 'then' => 'VPS', 'note' => 'Root access, your stack, your rules — and your responsibility for every security update.' ),
			array( 'if' => 'You only need a domain right now', 'then' => 'A dedicated registrar', 'note' => 'Registering the domain away from the host keeps DNS in your hands and makes changing host far easier later.' ),
		),

		/*
		 * The methodology. E-E-A-T, but more usefully: it tells a reader what
		 * the recommendations are and are not based on, which is the honest
		 * thing to publish when you have not lab-tested every provider.
		 */
		'methodology' => array(
			'lede'  => 'We build and maintain sites on these platforms for our own projects and for clients. That is the basis for what follows — practical experience of running production sites, not a benchmark lab.',
			'items' => array(
				array( 'title' => 'Renewal price, not the intro rate', 'desc' => 'The first-term discount is marketing. We compare what the plan costs in year two, and we say when the gap is large.' ),
				array( 'title' => 'Performance under real traffic', 'desc' => 'How the plan behaves once a site has actual visitors, plugins and a database — not how a blank install scores.' ),
				array( 'title' => 'Reliability', 'desc' => 'Published uptime commitments, and whether the provider credits you when they miss them.' ),
				array( 'title' => 'Resources and limits', 'desc' => 'Storage, memory, PHP workers and the point at which the plan starts throttling rather than scaling.' ),
				array( 'title' => 'SSL and backups', 'desc' => 'Whether they are included and automatic, or an add-on that appears at checkout.' ),
				array( 'title' => 'Migration', 'desc' => 'Whether the host will move an existing site for you, how many, and at what cost. This is a day of work otherwise.' ),
				array( 'title' => 'Support that can answer', 'desc' => 'Channels and hours, and whether the first reply understands the stack or reads from a script.' ),
				array( 'title' => 'WordPress fit', 'desc' => 'Caching that understands WordPress, sensible PHP defaults, staging, and no plugin bans that break a normal build.' ),
				array( 'title' => 'Developer access', 'desc' => 'SSH, WP-CLI or Composer, Git deployment, chosen PHP versions, and whether cron is genuinely available.' ),
				array( 'title' => 'Laravel fit', 'desc' => 'Composer, queue workers, a scheduler, environment variables and a document root you can point at /public.' ),
				array( 'title' => 'Scalability', 'desc' => 'Whether growing means resizing a plan or migrating the whole site to a different product.' ),
				array( 'title' => 'Ease of use', 'desc' => 'Whether a non-technical owner can be handed the login without a training session.' ),
				array( 'title' => 'Security', 'desc' => 'Firewalls, isolation between accounts, malware handling, and who is responsible for patching what.' ),
				array( 'title' => 'Value, not cheapness', 'desc' => 'The cheapest plan that fails under your traffic is not value. We weigh price against what the site actually needs.' ),
			),
			'note'  => 'We have not independently benchmarked every provider, and we do not publish numeric scores because we have no methodology that would make them meaningful. Where a claim comes from the vendor rather than our own use, we say so. Affiliate commission never changes what we recommend or the order in which providers appear.',
		),

		/*
		 * Search-intent groupings. `matchups` names matchup post slugs from
		 * inc/data/comparisons.php — the template resolves each to a live link
		 * or a "coming soon" state, so nothing here can produce a dead URL.
		 * `links` are internal pages that already exist.
		 */
		'groups' => array(
			array(
				'title'    => 'Hosting provider comparisons',
				'desc'     => 'Head-to-head write-ups between the hosts we actually use, each answering one buying question.',
				'matchups' => array( 'hostinger-vs-siteground', 'bluehost-vs-namecheap', 'cloudways-vs-digitalocean', 'kinsta-vs-cloudways' ),
			),
			array(
				'title'    => 'Choosing a hosting type',
				'desc'     => 'Shared, VPS, cloud or managed — the comparisons that answer which category you belong in before you pick a brand.',
				'matchups' => array( 'shared-vs-cloud-vs-managed-wordpress-hosting' ),
			),
			array(
				'title'    => 'WordPress hosting',
				'desc'     => 'What to run WordPress on, and the plugin decisions that follow once the hosting is settled.',
				'matchups' => array( 'hostinger-vs-siteground', 'kinsta-vs-cloudways' ),
				'links'    => array(
					array( 'label' => 'Compare WordPress plugins for SEO, speed and security', 'pillar' => 'functionality' ),
					array( 'label' => 'Our WordPress development service', 'service' => 'wordpress-development' ),
				),
			),
			array(
				'title'    => 'Laravel and developer hosting',
				'desc'     => 'Where a Laravel application belongs, and what the managed layer buys you over a bare VPS.',
				'matchups' => array( 'cloudways-vs-digitalocean' ),
				'links'    => array(
					array( 'label' => 'Our Laravel development service', 'service' => 'laravel-development' ),
				),
			),
			array(
				'title'    => 'Domains and DNS',
				'desc'     => 'Registrars, transfers, and why registering the domain away from the host is usually the better call.',
				'matchups' => array( 'bluehost-vs-namecheap' ),
				'links'    => array(
					array( 'label' => 'Have us set up the domain and hosting for you', 'service' => 'domain-hosting' ),
				),
			),
		),

		/*
		 * Answers to what people actually type. Kept short enough to be worth
		 * reading and specific enough to be worth quoting — a passage an answer
		 * engine can lift is the point of the section. These render visibly on
		 * the page, which is what makes the FAQPage markup legitimate.
		 */
		'faqs' => array(
			array(
				'q' => 'What is the best hosting for a new website?',
				'a' => 'For a first site, shared hosting from a mainstream provider is almost always the right answer. It has enough resources for a brochure site or a small business site, nothing to administer, and it costs little enough that a site that does not work out has not cost much. Check the renewal price before you buy — the advertised rate is usually a first-term discount.',
			),
			array(
				'q' => 'Is cheap hosting worth it?',
				'a' => 'For a low-traffic site, yes. Cheap shared hosting is genuinely adequate until a site has real visitors. It stops being worth it at the point where the plan throttles instead of scaling, because the symptom is a slow site rather than a message telling you why — and slow sites lose customers quietly.',
			),
			array(
				'q' => 'What is the difference between shared hosting and cloud hosting?',
				'a' => 'Shared hosting puts your site on one machine alongside many others and splits its fixed resources between everyone. Cloud hosting draws resources from a pool, so capacity can grow without moving the site and the failure of one machine does not take you offline. Shared is cheaper and simpler; cloud scales and costs more.',
			),
			array(
				'q' => 'Which hosting is best for WordPress?',
				'a' => 'Any competent shared host runs WordPress. The question is who maintains it. If somebody on your side handles updates, backups and caching, shared hosting is fine. If nobody does, managed WordPress hosting is worth the difference — you are buying the maintenance and WordPress-aware support, not the disk space.',
			),
			array(
				'q' => 'Which hosting is best for Laravel?',
				'a' => 'A VPS or cloud server. Laravel needs Composer, a document root pointed at /public, environment variables, a scheduler and usually queue workers — shared hosting gives you none of those reliably. Choose a raw VPS if you want to administer the server, or managed cloud if you would rather not.',
			),
			array(
				'q' => 'Is VPS better than shared hosting?',
				'a' => 'Better for different work, not better outright. A VPS gives dedicated resources and root access, and hands you responsibility for security patches, backups and configuration. For a brochure site that is a lot of work bought for nothing; for an application it is the minimum.',
			),
			array(
				'q' => 'Is managed hosting worth the extra cost?',
				'a' => 'It is worth it when the hours it saves cost more than the difference, or when downtime costs real money. For a business with no technical staff, managed hosting replaces a job nobody was doing. For a developer who was going to configure caching anyway, it mostly buys convenience.',
			),
			array(
				'q' => 'What should I check before buying hosting?',
				'a' => 'The renewal price rather than the intro rate; whether SSL, backups and migration are included or billed separately; the resource limits and what happens when you hit them; whether support is staffed at your hours; and whether growing means resizing a plan or migrating the whole site.',
			),
			array(
				'q' => 'Should I buy hosting and a domain from the same company?',
				'a' => 'It is convenient, but registering the domain with a dedicated registrar is usually the better call. Keeping the domain separate means DNS stays in your hands, changing host later is a DNS edit rather than a transfer, and a dispute with your host cannot hold your domain name hostage.',
			),
			array(
				'q' => 'How much should website hosting cost?',
				'a' => 'A small business site runs on shared hosting for a few dollars a month at renewal. Managed WordPress typically starts around the price of several shared plans. A VPS or managed cloud server for an application costs more again. The useful question is what the site needs, not what the cheapest plan costs.',
			),
			array(
				'q' => 'Can I move my website to another host later?',
				'a' => 'Yes, and many hosts will do the migration for you free as part of signing up — check how many sites that covers. Doing it yourself means moving files, database, email and DNS, which is roughly a day of work for a normal site. It is easier before the site is busy than after.',
			),
			array(
				'q' => 'What hosting should a developer choose?',
				'a' => 'A VPS if you want root access, a specific stack and the lowest cost per unit of resource, and you are willing to own security and backups. Managed cloud if you want SSH, Git deployment and chosen PHP versions without being responsible for the operating system underneath.',
			),
		),

		/*
		 * Internal links out of the hub, with anchors that describe the
		 * destination rather than repeating the target keyword. Services
		 * resolve through ssbd_service_url(), so a service without its own
		 * page still lands somewhere real.
		 */
		'related' => array(
			array( 'label' => 'Domain and hosting setup', 'service' => 'domain-hosting', 'desc' => 'If you would rather not choose: we register, configure and hand over the whole thing working.' ),
			array( 'label' => 'WordPress development and maintenance', 'service' => 'wordpress-development', 'desc' => 'Custom themes, plugin work and the performance tuning that hosting alone cannot fix.' ),
			array( 'label' => 'Laravel application development', 'service' => 'laravel-development', 'desc' => 'What we build on the VPS and managed cloud hosting compared above.' ),
			array( 'label' => 'Website maintenance and support', 'service' => 'website-maintenance-support', 'desc' => 'Updates, backups and monitoring — the work managed hosting only partly covers.' ),
			array( 'label' => 'WordPress plugin comparisons', 'pillar' => 'functionality', 'desc' => 'SEO, caching and security plugins — the next decisions once the hosting is settled.' ),
			array( 'label' => 'Business and marketing tool comparisons', 'pillar' => 'growth', 'desc' => 'Email, analytics and CRM platforms for once the site is live and fast.' ),
		),

		/*
		 * Trust signals. No invented credentials: the reviewer is the company,
		 * the basis is our own production use, and the date is set here so it
		 * is honest rather than auto-generated from the file's mtime.
		 */
		'trust' => array(
			'reviewed_by' => 'the Software Service BD engineering team',
			'basis'       => 'Based on hosting we run for our own sites and for client projects.',
			'updated'     => '2026-09-09',
			'prices_note' => 'Hosting prices change often. Every figure on this page is checked against the vendor\'s pricing page when the page is updated, and the date above is when that last happened — always confirm the current price with the provider before buying.',
		),
	),
);
