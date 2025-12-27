# Localization Rollout Plan (20-40 Locales)

Scope: consent (banner/preferences/DSR portal widgets), DSR, Legal Docs, Analytics dashboards, admin settings, emails/PDFs, widgets. Target 20-40 locales (LTR+RTL) aligned to v3docs winning-features.

## Locale Tiers
- Tier 0 (ship-ready 20): en, es, fr, de, pt-BR, it, nl, sv, da, no, fi, pl, cs, ro, tr, ja, ko, zh-CN, zh-TW, id
- Tier 1 (RTL core): ar, he, fa, ur
- Tier 2 (expansion to 40): el, hu, sk, bg, uk, hr, sr, vi, th, ms, hi, bn, ta, fil, ms (dedupe), ca, eu, gl, et, lv, lt (prune as needed to hit 35-40)

## Workflow
1) String freeze before release candidate; all modules wrap strings with shahi-legalops domain.
2) Regenerate POT:
```bash
wp i18n make-pot . languages/shahi-template.pot --domain=shahi-legalops
```
3) Generate/update POs (scriptable):
```bash
for locale in en es fr de pt_BR it nl sv da no fi pl cs ro tr ja ko zh_CN zh_TW id ar he fa ur; do
  msginit --no-translator --input=languages/shahi-template.pot --locale=$locale --output-file=languages/$locale.po
  msgmerge --update languages/$locale.po languages/shahi-template.pot
  msgfmt languages/$locale.po --output-file=languages/$locale.mo
done
```
4) WPML/Polylang: register dynamic option strings (banner texts, DSR labels) and ensure CPTs use show_in_rest true. Provide wpml-config.xml/polylang notes if needed.
5) JS localization: verify slosConsentI18n and other localized bundles per locale; chunk splitting keeps locale packs small.
6) QA sampling: run flows in Tier 0 + RTL (ar, he) for consent banner, preferences, DSR form, legal doc viewer, analytics filters/export, emails/PDF, widgets. Check plural forms, date/number formatting, truncation, and RTL mirroring.
7) Performance: ensure locale switch or bundle load <200ms overhead; lazy-load locale packs when possible.
8) Packaging: include POT/PO/MO in release; document how to add custom locales; provide fallback to en.

## RTL Checklist
- body[dir="rtl"] flips layout for banner/preferences, DSR portal, dashboards; icons/pagination align; chart labels not clipped.
- CSS uses logical properties where possible; avoid hardcoded left/right.
- Inputs/cursors/text alignment verified in forms and filters; flex direction and ordering mirrored where needed.

## Deliverables
- Updated POT + generated PO/MO for Tier 0 + RTL.
- Automation script snippet for adding locales.
- QA report: locales tested, defects logged/resolved, screenshots for RTL samples.
- Release note entry: supported locales list and how to extend.
