# Software Service BD — WordPress theme

The full site, ported from the design-canvas artboards in `design-source/` into a
real WordPress theme.

## Architecture

**PHP renders everything. React only adds interactivity.**

WordPress outputs the complete page — every service card, every FAQ answer, every
form field — and the React bundle then attaches to a handful of "islands" to make
that markup interactive. Nothing that matters for search or for AI answer engines
exists only in JavaScript.

Concretely, the filter tabs set `hidden` on cards that PHP already rendered rather
than rendering the cards themselves; the FAQ accordion reads its questions out of
the server markup instead of receiving a duplicate JSON copy; and the inquiry form
keeps its `admin-post.php` action so it still submits with JavaScript switched off.

```
softwareServiceBD/
├── style.css                  theme header
├── functions.php              bootstrap
├── front-page.php             home
├── index.php page.php single.php archive.php search.php 404.php
├── header.php footer.php searchform.php
│
├── inc/
│   ├── setup.php              supports, menus, first-run scaffolding + seeding
│   ├── post-types.php         Projects / Services / Products CPTs
│   ├── meta-boxes.php         their edit screens
│   ├── catalogue.php          reading CPTs back in the data files' shape
│   ├── data.php               dataset accessors (all filterable)
│   ├── template-tags.php      icons, brand, section heads, breadcrumbs
│   ├── islands.php            server-rendered React mount points
│   ├── enqueue.php            styles + deferred island bundle
│   ├── seo.php                title, description, canonical, OG, Twitter, robots
│   ├── geo.php                local geo meta + generative-engine layer + /llms.txt
│   ├── schema.php             one connected JSON-LD @graph
│   ├── customizer.php         contact details, share image
│   ├── contact.php            inquiry validation, REST route, no-JS fallback
│   └── data/                  all page content, as PHP arrays
│
├── page-templates/            one per site section
├── template-parts/
│   ├── sections/              front-page sections
│   └── components/            faq, cta, page-hero, cards, inquiry form
│
├── src/                       React island source
├── assets/
│   ├── css/theme.css          the design system
│   ├── css/fonts.css          @font-face rules, inlined into <head>
│   ├── fonts/                 self-hosted variable woff2 (OFL licensed)
│   └── js/dist/app.js         built bundle (committed; rebuild with npm run build)
├── tests/                     jsdom tests for the islands
└── design-source/             the original .dc.html artboards, kept for reference
```

## Installing

1. **Appearance → Themes → Activate "Software Service BD".**

   Activation runs once and creates the twelve site pages with the right template
   assigned to each, builds the Primary / Footer / Legal menus, sets the front page
   and blog page, and flushes rewrite rules so `/llms.txt` resolves.

   Existing pages with a matching slug are reused, never overwritten, and a menu you
   have already populated is left alone.

2. **Settings → Permalinks → Save** if `/llms.txt` 404s (a manual flush; rarely needed).

3. **Appearance → Customize → Business & SEO** to set the contact email, phone,
   WhatsApp link, inquiry recipient and default share image.

## Customizing the front page

**Appearance → Customize → Front Page.** Three sections:

- **Hero** — eyebrow, headline, lede, both button labels and links, and a
  toggle for the orbit graphic (off gives a full-width hero). Live preview via
  selective refresh, so edits appear without a page reload.
- **Sections** — drag to reorder, untick to hide. Covers Services, Why us,
  Recent work, Resources, From the blog, FAQ and the closing CTA. The hero is
  always first, so it is not listed.
- **Section headings** — the heading and supporting line for each section.
- **Recent work & stats** — the three counters above the project grid (value and
  caption for up to four), and how many projects the section shows.

Every field is empty by default and falls back to the theme's own copy, so a
fresh install needs nothing filled in, and clearing a field restores the
original rather than blanking the page. To remove something entirely — a
supporting line, or one of the stat counters — enter a single dash; empty
already means "use the default", so it cannot also mean "delete".

The hero lede is worth one note: it is also the homepage meta description, the
`ai:summary` tag and the JSON-LD `abstract`. Editing it in the Customizer moves
all four, which is the point — they are one statement from one source. It
accepts the same `{name}` / `{email}` / `{location}` / `{hours}` / `{response}`
placeholders as `inc/data/answers.php`.

Sections are registered in `inc/front-page.php`. Adding one means adding an
entry there and a matching template part; it then appears in the Customizer
list automatically.

## Customizing the About page

**Appearance → Customize → About page** holds the two photographs shown beside
the opening paragraphs. Upload a WebP, JPEG or PNG — the theme serves any of
them as WebP, since `inc/setup.php` converts every generated size. Around
720×900 suits the portrait pair.

The pictures resolve in one order (`ssbd_about_photos()` in `inc/data.php`):

1. **The Customizer controls.** Setting either one replaces both bundled
   defaults rather than mixing a chosen photo with a shipped one.
2. **The files bundled with the theme**, at `assets/images/about-1.webp` and
   `assets/images/about-2.webp`. Neither is required; drop them in and a fresh
   install has its default pictures with nothing to configure.
3. **Neither** — the section becomes one full-width column of copy, so a site
   with no photographs still looks deliberate.

Two pictures sit side by side as portraits, one fills the column on its own; the
count decides the shape. A bundled file gets an empty `alt`, because the theme
will not invent a description for a photograph it cannot see — upload an image
to the Media Library, where its alt text lives, if it needs one, or set `alt`
through the `ssbd_about_photos` filter.

The About page's **featured image** is separate and still belongs to the hero,
exactly as on every other page.

## Reader accounts, login and comments

Readers never see `wp-login.php`. **Login** and **Register** are ordinary pages
running `page-templates/template-login.php` and `template-register.php`, so the
site header and footer are around the form. Both are `noindex`, and both carry a
`redirect_to` so signing in returns the reader to the article they were on
rather than the home page. The header shows a **Sign in** button when logged
out and an avatar menu when logged in.

Accounts created through either route — the form or Google — are **Subscribers**,
regardless of WordPress's own "New User Default Role". That setting governs
wp-admin; a public sign-up form must not inherit it.

**Settings → Login & Accounts** holds the switches:

- **Registration** — off hides the sign-up form and stops Google creating new
  accounts. Existing account holders can still sign in.
- **Require an account to comment** — on by default. It drives WordPress's own
  `comment_registration` option, so core enforces it in `wp-comments-post.php`
  rather than the theme re-implementing the check. Logged-out readers get an
  invitation to sign in where the comment box would be.

### Sign in with Google

1. In the Google Cloud console, create an **OAuth 2.0 Client ID** of type
   *Web application*.
2. Copy the **Authorised redirect URI** shown on the settings screen into
   Google, exactly as printed. A mismatch here is what produces
   `redirect_uri_mismatch`; it is a fixed `admin-post.php` URL rather than a
   permalink so it never moves.
3. Paste the Client ID and secret back into the settings screen and tick
   **Enable**. The button only appears once both are filled in.

The secret is kept when the field is submitted empty, so saving the page from a
screen showing a masked value cannot wipe it. The round trip is guarded by a
`state` parameter: a random key travels to Google while the value it protects
stays in a transient here, single-use, expiring after fifteen minutes. The
profile is read from Google's userinfo endpoint rather than decoded out of the
`id_token`, which avoids verifying a JWT signature against rotating keys, and an
address Google has not marked verified is refused.

Google's `picture` claim is deliberately ignored — see `inc/avatars.php` for why
this theme draws avatars locally instead of fetching one per reader.

## Editing content in wp-admin

Projects, Services and Products are custom post types, so the three things
this site accumulates more of are added and edited from the admin, not in
code:

- **Projects** — title, description, tech stack, badge, tag, live URL,
  featured image, and categories that drive the Portfolio filter tabs.
- **Services** — short and full descriptions, filter group (Build / Automate /
  Grow), icon, technology pills, an included-items list, and the link label.
  The 01–06 numbering follows list position, so reordering renumbers the page.
- **Products** — description, category label, status, and categories that
  drive the Products filter tabs.

Use the **Order** field in the Page Attributes box to control sequence; the
admin lists sort by it rather than by date.

Filter tabs are built from the taxonomy terms that actually have posts, so a
tab can never lead to an empty grid.

### How the fallback works

Each accessor prefers wp-admin and falls back to the matching file in
`inc/data/` when the post type is empty:

```php
$projects = ssbd_catalogue_projects() ?? ssbd_data( 'projects' );
```

That means the theme's shipped copy is also its seed data. On activation the
catalogue is seeded from those files, so the admin screens open already
populated and ready to edit rather than as three empty lists — and a site that
never touches them still renders exactly as it always did.

The post types are registered `public => false` on purpose. They are blocks
assembled into the Portfolio, Services and Products pages, not pages in their
own right; giving each one a URL would publish a lot of thin, near-duplicate
pages, which is the opposite of what this site needs for search.

## Editing content

Page content lives in `inc/data/` as plain PHP arrays. Each one is filterable:

```php
add_filter( 'ssbd_data_services', function ( $services ) {
    $services[] = array(
        'no' => '07', 'slug' => 'mobile-apps', 'title' => 'Mobile Apps',
        'short' => '…', 'desc' => '…', 'group' => 'build',
        'icon' => 'code', 'pills' => array( 'React Native' ),
        'items' => array( 'iOS', 'Android' ), 'cta' => 'Explore Mobile',
    );
    return $services;
} );
```

Business facts — address, coordinates, service areas, hours — are all in
`inc/data/site.php`, which is the single source feeding the meta tags, the JSON-LD
and the visible footer address. Change a fact once and it updates everywhere.

Posts, categories, menus and the featured image for social previews are managed in
wp-admin as normal.

## SEO

Handled in `inc/seo.php`, and it stands down automatically if Yoast, Rank Math,
SEOPress or AIOSEO is active.

- Canonical URLs that respect pagination (page 2 does not canonicalize onto page 1).
- Meta description with a sensible cascade: per-post field → the page's answer
  summary → excerpt → content → site description.
- Open Graph and Twitter Card, including `article:*` on posts.
- `robots` with `max-snippet:-1` and `max-image-preview:large` — deliberate, so
  Google and AI Overviews may quote a full passage rather than a clipped one.
- `rel=prev` / `rel=next` on paginated archives.
- Per-post **meta description** and **direct answer summary** fields (Posts → edit →
  "SEO & Answer Engine").
- One connected JSON-LD `@graph` rather than disconnected blobs: Organization +
  ProfessionalService, WebSite with SearchAction, WebPage, BreadcrumbList,
  BlogPosting, FAQPage, Service and ItemList nodes, all cross-referenced by `@id`.

FAQ markup is only emitted for questions that are actually visible on the page.

## GEO

Two different things go by that name, and the theme does both.

**Geographic / local SEO** (`ssbd_geo_meta`): `geo.region`, `geo.placename`,
`geo.position`, `ICBM`, Open Graph place tags, plus the LocalBusiness address,
`GeoCoordinates`, `areaServed` and `openingHoursSpecification` in the JSON-LD. The
same address is rendered as visible text in the footer, which is what local rankings
actually reward.

**Generative Engine Optimization** (`ssbd_geo_ai_meta`): making the site quotable by
AI answer engines.

- **The lede paragraph is the answer passage.** Each page opens with a
  self-contained 30–55 word statement that names the entity and states facts
  plainly, written to be quoted verbatim. Defined in `inc/data/answers.php`.

  It is deliberately *just the lede* and not a bordered callout. An earlier
  version rendered it as a card below the H1, which meant every interior page
  opened by saying the same thing three times — H1, lede, then card — with the
  card using the accent left-border treatment that normally signals an aside.
  Answer engines extract from rendered text, not from chrome, so the box bought
  nothing and cost the page its visual hierarchy. The question-shaped headings
  it used to carry are already handled by each page's FAQ section, which is
  what emits FAQPage anyway.

  One consequence worth knowing: the lede, the meta description, the
  `ai:summary` tag and the JSON-LD `abstract` are now all the same sentence
  from the same source, so they cannot disagree.
- The same text mirrored into the page's JSON-LD as `abstract`, and into an
  `ai:summary` meta tag.
- **`/llms.txt`** — a plain-text, link-dense map of the site written for language
  models: services with descriptions, every key page with its answer summary,
  recent articles, the full FAQ set, and an explicit attribution licence.
- **robots.txt** explicitly allowing GPTBot, OAI-SearchBot, ChatGPT-User, ClaudeBot,
  PerplexityBot, Google-Extended, Applebot-Extended, CCBot and others, and pointing
  at both the sitemap and the LLM manifest. Opt out with
  `add_filter( 'ssbd_allow_ai_crawlers', '__return_false' );`
- `citation_publisher` / `citation_author` / `last-reviewed` so a source card can be
  labelled and dated.
- Comparison data rendered as a real `<table>`, which answer engines parse far more
  reliably than a grid of divs.

## Working on the React bundle

```bash
npm install
npm run build      # → assets/js/dist/app.js
npm run dev        # rebuild on change
npm test           # jsdom tests for every island
```

`assets/js/dist/app.js` is committed, so the theme works without a build step.
Rebuild it after editing anything in `src/`.

The tests run against HTML fixtures captured from the real site
(`tests/fixtures/`), so they exercise the markup PHP actually produces. They assert
the thing that matters most about this architecture: that hydration **adds**
behaviour without removing content — all six service cards stay in the DOM when a
filter is applied, and every FAQ answer survives verbatim.

Recapture the fixtures after changing templates:

```bash
curl -s 'http://software-service-bd/'         -o tests/fixtures/home.html
curl -s 'http://software-service-bd/contact/' -o tests/fixtures/contact.html
```

## Performance

The critical path is kept deliberately short. Lighthouse mobile originally
measured ~1,650ms of render-blocking requests across three files; all but one
are now gone.

- **Fonts are self-hosted** in `assets/fonts/`. Loading them from
  fonts.googleapis.com meant the browser had to fetch a stylesheet from a second
  origin before it could even discover the font files on a *third* origin. All
  three families are variable fonts, so one file per subset covers every weight
  — six files, not eighteen.
- **The `@font-face` rules are inlined** into `<head>` (~3KB), so there is no
  request at all just to tell the browser where the fonts live. With
  `font-display: swap`, the font files themselves never block rendering.
- **The display font is preloaded.** The H1 is the LCP element on nearly every
  page and it is set in Bricolage Grotesque, so that one file is fetched in
  parallel with the stylesheet rather than after it.
- **`style.css` is not enqueued on the front end** unless a child theme is
  active. It holds the theme header and nothing else — 860 bytes of comments
  that were costing a render-blocking round trip.
- **Core's block styles are unhooked** on pages with no block content, which is
  every page template. `wp_enqueue_global_styles()` has to be removed from its
  actions rather than dequeued, since it prints inline CSS directly; that alone
  is ~9KB off every page.
- **The emoji polyfill is removed** — a script, a stylesheet and a DNS lookup
  to s.w.org for something every supported browser does natively.
- **`assets/.htaccess`** sets a one-year immutable cache for static assets.
  That is safe because every asset URL carries a `?ver=` derived from the
  file's mtime, so changing a file changes its URL.

What is left on the critical path is one stylesheet, `assets/css/theme.css`.
It is kept as a linked file rather than inlined so it stays cached across page
views.

`Reduce unused JavaScript` will still flag `app.js`: it is React, and about
22KB of it goes unused on any single page. That is the cost of the island
architecture, it is deferred, and Total Blocking Time measures 0ms — so it is
not worth splitting until it actually shows up in a metric.

## Dark mode

Driven by `data-theme` on `<html>`. An inline script in `header.php` resolves it
before first paint, so there is no flash of the wrong theme; the React toggle in the
header is the same logic, shared via `src/theme.js`. With no stored preference the
site follows the operating system and keeps following it until the visitor picks a
side.

## Known gaps

- `inc/data/site.php` has a placeholder WhatsApp number (`8801XXXXXXXXX`) and no
  phone number or social profile URLs. Fill these in — the WhatsApp link and the
  `sameAs` entity signals depend on them.
- Portfolio projects have no `url` or screenshot; the cards render a text
  placeholder. Adding real URLs also populates the `CreativeWork` schema.
- The Resources page's six "free tools" are described but not built; their buttons
  currently point at the contact page.
- Comparison tables carry placeholder providers ("Provider A/B/C") and `—` values,
  as they did in the design source.
