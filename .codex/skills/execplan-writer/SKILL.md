---
name: execplan-writer
description: Create, review, and improve Codex-style ExecPlans as living design documents for repository tasks. Use when users ask to create a new ExecPlan, refine an existing plan, or execute work with an ExecPlan-first workflow.
---

# ExecPlan Writer

Create and maintain repository-local ExecPlan workflow files.
Assume WordPress custom plugin context and PHP-first implementation unless the user states otherwise.

## Modes

Choose one mode per request. If the request is ambiguous, infer from user intent and state the chosen mode briefly.

- `new`: 新規ExecPlanを作成する。
- `improve`: 既存ExecPlanを修正・改善する。
- `review`: 既存ExecPlanをレビューし、修正案を提示する。ユーザーが編集を依頼しない限りファイルは変更しない。

## Workflow

1. Verify repository guidance files.
- Check whether `AGENTS.md`, `.agent/`, and `.agent/plans/` exist.
- Create missing directories as needed.

2. Add PLANS.md entry point in `AGENTS.md`.
- Ensure `AGENTS.md` includes a short rule that points to `.agent/PLANS.md` for ExecPlan policy.
- Add only the minimum text needed to route planning behavior.

3. Define or refresh `.agent/PLANS.md`.
- Write repository policy for ExecPlan behavior using `references/execplan-spec.md`.
- State required sections and update rules for living documents.
- Keep it concise and operational.

4. Run mode-specific flow.

### Mode: `new`

- Copy `assets/execplan-template.md` into `.agent/plans/`.
- Use filename format: `<date>_<slug>.md`.
- Date format: `YYYY-MM-DD`.
- Example: `2026-02-14_contact-form.md`.
- Replace placeholders with repository-specific context and exact implementation steps.
- Include file paths, interfaces, verification steps, and rollback strategy.
- For WordPress plugin tasks, include hooks, capability checks, nonce handling, and sanitization/escaping points.

### Mode: `improve`

- Identify target plan file.
  - If user specifies a file, use it.
  - If unspecified, prefer the latest file in `.agent/plans/` and note the assumption.
- Read target plan and compare against `references/execplan-spec.md` and `.agent/PLANS.md`.
- Use `references/improve-checklist.md` for detailed gap checks and remediation order.
- Improve without changing core intent unless the user requests scope change.
- Prioritize fixes in this order:
  1. Missing required sections
  2. Non-verifiable implementation or verification steps
  3. Ambiguous scope, dependencies, and rollback
  4. Stale progress or decision log
  5. WordPress security and compatibility gaps
- Update the same file by default. Do not fork a new plan file unless requested.
- Add or update a `Decision Log` entry for major plan changes with date and rationale.

### Mode: `review`

- Produce findings first, ordered by severity.
- Map each finding to specific section or line references in the plan.
- Use `assets/execplan-review-template.md` for response structure when a formal review is requested.
- Provide actionable patch suggestions.
- Edit files only when the user explicitly asks to apply the review.

5. Maintain the plan as a living document.
- Update `Progress` and `Decision Log` during execution.
- Record deviations and rationale before changing implementation direction.

## Quality Checklist (for `new` and `improve`)

Confirm all items before finishing:

- Plan is self-contained and executable by another agent.
- Purpose and scope, progress checklist, findings and context, decision log, risks and mitigations are present.
- Implementation steps include concrete file paths and execution order.
- Verification includes commands and expected outcomes.
- Rollback or recovery notes exist.
- Interfaces and dependencies are explicit when relevant.
- WordPress checks are covered where relevant:
  - hook points (`add_action`, `add_filter`)
  - authz and nonce checks
  - sanitization and escaping decisions
  - version assumptions (PHP and WordPress)

## Output Rules

- In `new` mode, produce exactly one ExecPlan file per request unless the user asks for multiple plans.
- In `improve` mode, update exactly one existing plan file unless the user asks for batch updates.
- In `review` mode, return findings first and keep summary short.
- Keep each plan self-contained so another agent can execute it without extra context.
- Prefer explicit absolute or repository-relative paths.
- Include acceptance checks and evidence commands.
- Prefer PHP and WordPress-native verification commands where available (for example `php -l`, PHPUnit, WP-CLI checks).
- Write plan body text in Japanese by default. English headings are acceptable.

## References

- Read `references/execplan-spec.md` when deciding section requirements and quality bar.
- Read `references/improve-checklist.md` in `improve` mode for consistent plan refactoring.
- Use `assets/execplan-template.md` as the starting structure in `new` mode.
- Use `assets/execplan-review-template.md` in `review` mode when structured findings are needed.
