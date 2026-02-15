# AGENTS

## Gitコミット

- コミットメッセージは簡潔な日本語で書いてください。
- **Conventional Commits形式を必ず使用してください**。以下のプレフィックスを使用：
  - `feat:` 新機能追加
  - `fix:` バグ修正
  - `docs:` ドキュメントのみの変更
  - `refactor:` リファクタリング
  - `test:` テストの追加・修正
  - `chore:` ビルドプロセスやツールの変更
- **コミットメッセージの形式**:
  - タイトル: `prefix: 説明` の形式で20文字程度（prefix含む）
  - 空行を1行（必須）
  - 本文: 箇条書き（`-` または `*`）で変更内容を明記

## ExecPlan Policy

For ExecPlan-style planning and execution, follow `.agent/PLANS.md`.
Create task-specific ExecPlans under `.agent/plans/` using date-prefixed filenames (`YYYY-MM-DD_slug.md`).
Write ExecPlan content in Japanese by default (English headings are acceptable).

## コーディング原則

- 実装・設計・リファクタリングでは、`SOLID`、`DRY`、`YAGNI`、`KISS`、`SSOT`、`PIE` の原則に従ってください。
- 仕様・制約・納期と衝突する場合は、逸脱理由とトレードオフを明示してください。
