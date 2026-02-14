# ExecPlan Spec

This reference captures the operational style from Codex Execution Plans for repository use.

## 1) Required repository structure

- `AGENTS.md`: Include a routing rule that tells agents to follow `.agent/PLANS.md` for ExecPlan policy.
- `.agent/PLANS.md`: Define the repository's ExecPlan contract and required sections.
- `.agent/plans/`: Store task-specific ExecPlan files.

## 2) Core principles

- Treat an ExecPlan as a living design and execution document, not a static proposal.
- Write so a new agent can continue work with no prior context.
- Keep assumptions explicit and date-sensitive details concrete.
- Log decisions and tradeoffs as they happen.

## 3) Non-negotiable quality bar

- Self-contained: include all context needed to execute safely.
- Verifiable: include commands, checks, and expected outcomes.
- Concrete: reference exact files, interfaces, data flow, and migration steps.
- Updateable: keep `Progress` and `Decision Log` current during implementation.

## 4) PLANS.md policy to enforce

Repository `PLANS.md` should require each ExecPlan to contain:

1. Purpose and scope
2. Progress checklist
3. Findings/context
4. Decision log
5. Risks and mitigations
6. Implementation steps with concrete file paths
7. Verification strategy and commands
8. Rollback or recovery notes
9. Interfaces/dependencies if applicable

For WordPress custom plugin repositories, require these additional checks in each plan:

- Hook integration points (`add_action`, `add_filter`) and lifecycle behavior.
- Permission model (`current_user_can`) and nonce verification (`check_admin_referer`/`wp_verify_nonce`) where relevant.
- Input handling (`sanitize_*`) and output escaping (`esc_*`, `wp_kses`) decisions.
- Plugin compatibility assumptions (minimum PHP/WordPress versions) when behavior depends on version.

## 5) File naming

- Save plans under `.agent/plans/`.
- Prefix file names with date: `YYYY-MM-DD`.
- Append a short task slug.
- Example: `.agent/plans/2026-02-14_contact-form.md`.

## 6) AGENTS.md routing snippet (example)

Use concise text equivalent to:

"For ExecPlan-style planning and execution, follow `.agent/PLANS.md`. Create task plans in `.agent/plans/` using date-prefixed filenames (`YYYY-MM-DD_slug.md`)."

## 7) Validation guidance for WordPress/PHP

- Include at least one syntax check command (`php -l` on changed files).
- Include unit/integration test commands when test harness exists (for example PHPUnit).
- Include behavior checks aligned to WordPress flows (admin screen action, REST endpoint, shortcode, cron, or hook output).

## 8) Language guidance

- Generate ExecPlan body content in Japanese by default.
- English headings are allowed.
- Keep commands, file paths, and code identifiers unchanged.
