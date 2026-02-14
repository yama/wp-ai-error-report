---
name: execplan-writer
description: Create and execute Codex-style ExecPlans as living design documents for repository tasks. Use when the user asks for an "ExecPlan", asks to "実施してください" with an ExecPlan, or needs a plan-first workflow that updates AGENTS.md, defines `.agent/PLANS.md`, and creates date-prefixed plan files under `.agent/plans/`.
---

# ExecPlan Writer

Create repository-local ExecPlan workflow files and generate task-specific ExecPlan documents.
Assume WordPress custom plugin context and PHP-first implementation unless the user states otherwise.

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

4. Create a task ExecPlan from template.
- Copy `assets/execplan-template.md` into `.agent/plans/`.
- Use filename format: `<date>_<slug>.md`.
- Date format: `YYYY-MM-DD`.
- Example: `2026-02-14_contact-form.md`.

5. Fill the plan with concrete requirements.
- Replace placeholders with repository-specific context and exact implementation steps.
- Include file paths, interfaces, verification steps, and rollback strategy.
- For WordPress plugin tasks, include hooks, capability checks, nonce handling, and sanitization/escaping points.

6. Maintain the plan as a living document.
- Update `Progress` and `Decision Log` during execution.
- Record deviations and rationale before changing implementation direction.

## Output Rules

- Produce exactly one ExecPlan file per request unless the user asks for multiple plans.
- Keep each plan self-contained so another agent can execute it without extra context.
- Prefer explicit absolute or repository-relative paths.
- Include acceptance checks and evidence commands.
- Prefer PHP and WordPress-native verification commands where available (for example `php -l`, PHPUnit, WP-CLI checks).
- Write plan body text in Japanese by default. English headings are acceptable.

## References

- Read `references/execplan-spec.md` when deciding section requirements and quality bar.
- Use `assets/execplan-template.md` as the starting structure for every new ExecPlan.
