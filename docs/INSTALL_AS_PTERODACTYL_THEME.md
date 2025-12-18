# Install as a Pterodactyl Panel Theme

This document explains how to use the built OFA panel assets as a theme for a Pterodactyl panel (v1.x / v2.x). The repository ships a sample theme manifest and a small sample palette to make packaging and install simple.

> NOTE: Pterodactyl itself does not have a single canonical "theme package format" across all installations. The steps below show a general approach that works for self-hosted panels by copying assets to the panel's web root and adding any required overrides in your panel configuration.

## Files included here
- `preview/pterodactyl-theme.json` — sample manifest describing the theme and palettes.
- `resources/css/themes/pterodactyl-default.css` — a sample theme file that sets CSS variables consumed by the OFA styles.

## Quick install (self-hosted panel)
1. Build the package assets locally or in CI:
   - npm ci && npm run build
   - composer install --no-dev --optimize-autoloader (if desired for packaging)

2. Create a theme folder on your panel server, e.g. `/var/www/pterodactyl/public/themes/ofa/` and copy the built CSS file(s):
   - `dist/assets/ofa-theme.css` (or `resources/css/themes/pterodactyl-default.css` if you prefer to include raw CSS)
   - Any JS assets if you want interactive previews

3. If the panel supports custom CSS injection (via admin or config), load the theme's CSS from the theme folder above. Example in an Nginx-served panel:
   - Serve `/themes/ofa/` from the panel's `public` folder and reference with `<link rel="stylesheet" href="/themes/ofa/ofa-theme.css">` in the panel's base layout.

4. Optionally import the palette JSON (from `preview/pterodactyl-theme.json`) into your UI or convert it to a server-managed configuration to allow site-wide switching.

## Packaging/Manifest
The included `preview/pterodactyl-theme.json` is a minimal manifest describing the theme name, version and palettes. It can be used by custom theme-installers or by a panel admin to quickly preview the theme.

## Palettes and customization
- The OFA package uses CSS custom properties (variables) for colors. You can add palette files or JSON and then switch them by loading the appropriate CSS variables or updating a small client-side JSON that maps variable sets.

## Notes for Panel Operators
- This repo aims to make it easy to ship themes (extracted CSS and assets). If you operate multiple sites you may want to convert palettes into per-site JSON that your deployment writes into the panel's config or to a small admin UI extension that toggles a palette file.
- If you want an installer script (uploads to a panel via SSH/rsync), open an issue and I can add a simple helper script to automate copying assets to a target panel.

## Troubleshooting
- If colors don't show correctly, ensure the theme CSS is loaded after the panel's default CSS so the variables override defaults.
- For JS-based previews, confirm the panel's Content Security Policy allows loading the preview scripts or inline script that sets the palette.

---
If you want a strict, installable package format (that automatically registers with specific panel versions), tell me which Pterodactyl version and hosting model you use and I will add an installer script and packaging helpers.