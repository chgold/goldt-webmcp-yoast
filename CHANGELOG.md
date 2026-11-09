# Changelog

## 1.0.1 — 2026-11-09

- Security: permission ceiling (SEC-00) — per-post reads and all writes now use per-post `edit_post` (Yoast's own metabox rule) instead of global `edit_posts` / `edit_others_posts`; Authors can now edit SEO of their own posts but not others'. setCanonical / setRobotsSettings additionally require Yoast's advanced-metadata permission (`wpseo_edit_advanced_metadata` unless `disableadvanced_meta` is off). listCornerstoneContent and searchBySeoScore omit objects the user cannot edit.
- Security: permission ceiling (SEC-00) — getSitemapSettings requires `wpseo_manage_options` (Yoast settings screens); getSeoOverview is scoped to the user's own posts without `edit_others_posts`, as Yoast's dashboard statistics are.

## 1.0.0

- Initial release.
