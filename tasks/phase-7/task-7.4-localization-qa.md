# TASK 7.4: Localization QA

Phase: 7 | Effort: 4-6h | Next: 7.5

Validate translations and RTL across 20-40 locales (LTR + RTL) for Consent, DSR, Legal Docs, Analytics, admin, emails, and widgets.
- Target locales (minimum 20): en, es, fr, de, pt-BR, it, nl, sv, da, no, fi, pl, cs, ro, tr, ja, ko, zh-CN, zh-TW, id; plus RTL set: ar, he, fa, ur (expand toward 40 as strings arrive).
- Spot-check UI flows: consent banner/preferences, DSR form/portal, legal doc viewer, analytics dashboard filters/exports, settings pages, emails/PDF outputs. Verify plural forms, date/number formatting, and truncation/overflow.
- RTL: body[dir="rtl"] layouts render correctly; charts/filters/inputs mirror; icons/pagination align.
- Ensure POT/PO/MO updated; WPML/Polylang string registration covers dynamic options; JS localized bundles loaded per locale; follow [DevDocs/localization-rollout-plan.md](../../DevDocs/localization-rollout-plan.md) for locale set and workflow.

Success: No missing strings; RTL passes visual check; 95%+ string coverage for target locales; no layout breakages in sampled flows.
