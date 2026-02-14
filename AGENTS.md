# AGENTS

## Gitコミット

- コミットメッセージは日本語で書いてください。
- コミットメッセージは、Conventional Commitsの形式に従うことが望ましい。
- コミットメッセージは、変更内容を簡潔に説明するものにしてください。

## ExecPlan Policy

For ExecPlan-style planning and execution, follow `.agent/PLANS.md`.
Create task-specific ExecPlans under `.agent/plans/` using date-prefixed filenames (`YYYY-MM-DD_slug.md`).
Write ExecPlan content in Japanese by default (English headings are acceptable).

## コーディング原則

- 実装・設計・リファクタリングでは、`SOLID`、`DRY`、`YAGNI`、`KISS`、`SSOT`、`PIE` の原則に従ってください。
- 仕様・制約・納期と衝突する場合は、逸脱理由とトレードオフを明示してください。
