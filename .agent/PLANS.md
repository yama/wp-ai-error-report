# PLANS

This repository uses ExecPlan as a living design-and-execution document.

## Required Structure For Every ExecPlan

1. Purpose / Big Picture
2. Progress checklist
3. Findings / Context
4. Decision Log
5. Risks and Mitigations
6. Implementation Plan (concrete file paths)
7. Interfaces and Dependencies
8. Validation (commands + expected results)
9. Rollback / Recovery
10. Execution Notes (timestamped living updates)

## WordPress / PHP Requirements

- Assume custom WordPress plugin context and PHP-first implementation unless explicitly overridden.
- Include hook integration points where relevant (`add_action`, `add_filter`).
- Include auth/security checks where relevant (`current_user_can`, nonce verification).
- Include sanitization and escaping decisions (`sanitize_*`, `esc_*`, `wp_kses`).
- Include compatibility assumptions (minimum WordPress/PHP versions).

## Coding Principles

- Every ExecPlan and implementation must align with `SOLID`, `DRY`, `YAGNI`, `KISS`, `SSOT`, and `PIE`.
- If a step intentionally deviates, record the reason and tradeoff in `Decision Log`.

## File Naming

- Save plans in `.agent/plans/`.
- Prefix filenames with date format `YYYY-MM-DD`.
- Append short task slug.
- Example: `.agent/plans/2026-02-14_contact-form.md`.

## Living Document Rule

- Update `Progress`, `Decision Log`, and `Execution Notes` while implementing.
- Record deviations before changing implementation direction.

## Language Rule

- Generate ExecPlan body content in Japanese.
- English headings are allowed.
- Keep commands, file paths, and code identifiers in their original notation.
