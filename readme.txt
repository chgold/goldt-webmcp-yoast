=== Goldnat for Yoast SEO ===
Contributors: chgold
Tags: yoast, seo, ai, mcp, schema
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.0
License: Proprietary

Read-only AI tools for Yoast SEO: SEO meta, scores, robots, social previews, schema, breadcrumbs, cornerstone content and index health.

== Description ==

Goldnat for Yoast SEO lets AI agents connected through the Goldnat WebMCP Bridge read what Yoast SEO knows about your content.
It needs the Goldnat WebMCP Bridge and Yoast SEO.

Tools (all read-only):

* yoast.getSeoMeta, yoast.getFocusKeyword, yoast.getSeoScore, yoast.getReadabilityScore, yoast.getRobotsSettings, yoast.getSocialMeta
* yoast.listCornerstoneContent, yoast.searchBySeoScore, yoast.searchContent, yoast.getSeoOverview, yoast.getSitemapSettings
* yoast.getSchema, yoast.getSocialPreview, yoast.getBreadcrumbs, yoast.getIndexationStatus

Every tool checks the user's own WordPress / Yoast permission for the object it reads.
Editing tools (SEO title, meta description, focus keyphrase, canonical, robots, social, schema, bulk edit) are in Goldnat for Yoast SEO Pro.

== Changelog ==

= 1.2.0 =
* Breaking: the six write tools moved to Goldnat for Yoast SEO Pro (same tool names). This add-on is now read-only.
* searchBySeoScore and getSeoOverview read Yoast's index through Yoast's Indexable repository instead of direct SQL.

= 1.1.0 =
* Five insight tools: searchContent, getSchema, getSocialPreview, getBreadcrumbs, getIndexationStatus.

= 1.0.1 =
* Per-post permission checks (SEC-00).

= 1.0.0 =
* First release.
