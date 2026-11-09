# Changelog

## 1.2.0 — 2026-11-09

- Breaking: write tools moved to Goldnat for Yoast SEO Pro (`goldt-webmcp-yoast-pro`, PM slug `yoast-ai-connect-pro`) — `yoast.updateSeoMeta`, `yoast.setFocusKeyword`, `yoast.setCanonical`, `yoast.setRobotsSettings`, `yoast.setCornerstone`, `yoast.setSocialMeta`. Install the Pro plugin to keep them; the tool names are unchanged.
- The free add-on is now read-only (15 tools, all `read` scope), per Coverage v1.2 (no write/delete in Free).
- `yoast.searchBySeoScore` and `yoast.getSeoOverview` read Yoast's index through Yoast's Indexable repository instead of direct SQL (numbers are returned as integers).
- Added readme.txt.

## 1.1.0 — 2026-11-09

- New (free, read): `yoast.searchContent` — search the Yoast index by SEO rank, readability rank, score ranges, focus keyphrase, post type/status, cornerstone/noindex flags and publish-date range (only content the user can edit).
- New (free, read): `yoast.getSchema` — the Schema.org JSON-LD graph Yoast outputs for a post, optionally filtered by @type.
- New (free, read): `yoast.getSocialPreview` — effective Open Graph + Twitter/X card values as rendered (templates and fallbacks included), which fields are custom-set, and improvement hints.
- New (free, read): `yoast.getBreadcrumbs` — breadcrumb trail, BreadcrumbList schema piece and breadcrumb settings.
- New (free, read): `yoast.getIndexationStatus` — indexables indexing progress, unindexed count, link-count completeness, noindexed post types and explicitly noindexed content.
- Per-post tools require `edit_post` on the post; search/status tools require `edit_posts` and filter results per object (SEC-00). Data is read through Yoast's Meta surface and Indexable repository, not raw postmeta.

## 1.0.1 — 2026-11-09

- Security: permission ceiling (SEC-00) — per-post reads and all writes now use per-post `edit_post` (Yoast's own metabox rule) instead of global `edit_posts` / `edit_others_posts`; Authors can now edit SEO of their own posts but not others'. setCanonical / setRobotsSettings additionally require Yoast's advanced-metadata permission (`wpseo_edit_advanced_metadata` unless `disableadvanced_meta` is off). listCornerstoneContent and searchBySeoScore omit objects the user cannot edit.
- Security: permission ceiling (SEC-00) — getSitemapSettings requires `wpseo_manage_options` (Yoast settings screens); getSeoOverview is scoped to the user's own posts without `edit_others_posts`, as Yoast's dashboard statistics are.

## 1.0.0

- Initial release.
