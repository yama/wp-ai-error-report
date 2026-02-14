# Empty Plugin Skeleton ExecPlan

## Purpose / Big Picture

- Goal: ランタイム機能を持たない最小構成のWordPressカスタムプラグインを新規作成する。
- User value: 今後の機能追加の土台となる、安全でシンプルな開始点を用意する。
- In scope: プラグインディレクトリ、プラグインヘッダ付きメインPHPファイル、保護用 `index.php` の作成。
- Out of scope: 管理画面、フック実装、RESTルート、DBテーブル、設定UI、業務ロジック。

## Progress

- [ ] プラグインslugと作成先を決定する
- [ ] スケルトンファイルを作成する
- [ ] PHP構文チェックを実行する
- [ ] 生成結果を確認して記録する

## Findings / Context

- 現在のリポジトリにはプラグイン実装ファイルが存在しない。
- プラグイン生成はスキルスクリプトに依存せず、リポジトリ内に直接ファイルを作成する。
- 要求される出力はPHPベースの最小構成である。

## Decision Log

- Decision: 最小プラグインファイルを直接作成する。
- Rationale: 削除済み補助スキルへの依存を避け、手順を単純化するため。
- Date (UTC): 2026-02-14

## Risks and Mitigations

- Risk: slugが不正でWordPress標準から外れた命名になる。
- Mitigation: slugに `^[a-z0-9-]+$` を適用する。
- Owner: 実装担当

- Risk: プラグインヘッダの不備によりWordPressで認識されない。
- Mitigation: 標準ヘッダ項目を保持し、構文チェックを実施する。
- Owner: 実装担当

## Implementation Plan

1. slugと出力先を決定する。
- Files: N/A
- Interface impact: N/A
- Notes: slugは小文字ハイフン形式を使用する。

2. プラグインファイルを作成する。
- Files: `<target>/<slug>/<slug>.php`, `<target>/<slug>/index.php`
- Interface impact: インストール可能なプラグインエントリを追加する。
- Notes: 標準プラグインヘッダと `ABSPATH` ガードを含める。

3. 生成PHPを検証する。
- Files: `<target>/<slug>/<slug>.php`
- Interface impact: なし
- Notes: メインファイルに対して `php -l` を実行する。

## Interfaces and Dependencies

- APIs: WordPressのプラグインヘッダ解析。
- Data contracts: なし。
- External services: なし。
- Version constraints: WordPress実行環境およびPHPランタイムが利用可能であること。
- WordPress/PHP requirements: プロジェクトの基準バージョンに互換性を持たせる。

## Validation

- Command: `php -l <target>/<slug>/<slug>.php`
- Expected result: `No syntax errors detected ...`
- Evidence location: 実装時のターミナル出力。

## Rollback / Recovery

- Trigger condition: slug/パスの誤り、または生成ファイルの不整合。
- Rollback steps: 生成したプラグインディレクトリを削除し、正しい入力で再作成する。
- Data recovery notes: 永続データの移行は伴わない。

## Execution Notes (Living Updates)

- 2026-02-14T14:25:12Z 初版ExecPlanを作成。
