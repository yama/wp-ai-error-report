# WP AI Error Report Plugin Build ExecPlan

## Purpose / Big Picture

- Goal: `.docs/wp-ai-error-report-spec.md` に基づき、PoC向け WordPress プラグイン `wp-ai-error-report` を実装するための具体的な実行計画を定義する。
- User value: Fatal系エラーの検知からAI要約メール通知までを、過剰通知を抑えながら短期間で導入できる。
- In scope: プラグイン本体、エラー捕捉、JSONログ保存、マスキング、設定可能な送信間隔判定、OpenAI API連携（`wp_remote_post`）、メール送信、失敗時リトライ方針、設定ファイル構成。
- Out of scope: 管理画面UI、同一エラー集約、中間サーバー連携、ローカルLLM、高度なローテーションや分析機能。

## Progress

- [x] 実装対象ファイルの雛形を作成する（メイン、includes、config sample、logs ディレクトリ保護）
- [x] Fatalエラー捕捉とJSONログ追記処理を実装する
- [x] AI要約送信処理（送信判定・OpenAI呼び出し・メール送信・ログ削除）を実装する
- [x] 失敗時再試行と例外時の安全終了（WPデバッグログ記録）を実装する
- [ ] 構文チェックと動作確認手順を実行し、証跡を残す

## Findings / Context

- Repository state: 現時点で実装コードは未作成で、仕様書とExecPlan基盤のみ存在する。
- Existing constraints:
- PoCとして必要最小限を優先し、過剰な抽象化は避ける。
- 不明点は推測せず確認する方針。
- Relevant files and systems:
- 仕様: `.docs/wp-ai-error-report-spec.md`
- ExecPlan規約: `.agent/PLANS.md`
- 既存計画: `.agent/plans/2026-02-14_empty-plugin-skeleton.md`
- WordPress plugin entry points:
- `wp-ai-error-report.php` で初期化とフック登録のみを担当する。
- `init` でレポート送信判定処理を実行する。
- `register_shutdown_function` でFatal系エラーを捕捉する。

## Decision Log

- Decision: OpenAI呼び出しは `wp_remote_post` を採用する。
- Rationale: PoCで依存追加を避け、WordPress標準APIで導入コストと運用負荷を下げるため。
- Date (UTC): 2026-02-14

- Decision: `config.php` に設定を集約し、モデルは設定可能かつデフォルト `gpt-4.1-mini` を採用する。
- Rationale: 利用者が非エンジニアでも調整しやすく、将来のプロンプト設定追加にも拡張しやすいため。
- Date (UTC): 2026-02-14

- Decision: 通知先メールアドレス未設定時は送信しない（安全にスキップ）。
- Rationale: 意図しない送信を避けるPoC方針に合わせるため。
- Date (UTC): 2026-02-14

- Decision: ログ保存先は `wp-content/uploads/wp-ai-error-report/logs/error.log` を採用する。
- Rationale: 仕様書のセキュリティ推奨に従い、プラグイン配下より公開リスクを下げやすいため。
- Date (UTC): 2026-02-14

- Decision: 通常時/巨大ログ時ともにAI送信対象の末尾行数は同一 `100` 行にする。
- Rationale: ユーザー指定（まずは小さめ）を優先し、API負荷を低く抑えるため。
- Date (UTC): 2026-02-14

- Decision: 1時間間隔判定は `error.log` の `filemtime` ではなく、判定専用 `last_report_attempted_at.touch` の `filemtime` を使用する。
- Rationale: エラーログ追記で `error.log` の更新時刻が進み続けるため、送信タイミング判定が成立しない問題を回避するため。
- Date (UTC): 2026-02-14

- Decision: AIプロンプトは現行実装の初版文面を仕様として記録し、将来調整可能な前提で運用する。
- Rationale: 重要な運用ロジックのブラックボックス化を防ぎ、レビュー可能な状態を維持するため。
- Date (UTC): 2026-02-14

## Risks and Mitigations

- Risk: `uploads` 配下ログへの直接アクセスリスク。
- Mitigation: `logs` 配下に `index.php` と `.htaccess`（Apache向け）を配置し、加えてファイル権限を最小化する。Nginx環境は運用ドキュメントで補足する。
- Owner: 実装担当

- Risk: エラー多発時に外部APIコストが増える。
- Mitigation: `last_report_attempted_at.touch` の `filemtime` による1時間間隔判定を厳守し、送信対象行数を100行に固定する。
- Owner: 実装担当

- Risk: マスキング漏れで機密情報が外部送信される。
- Mitigation: 送信直前に必ず `Masking` クラスを通す共通処理を設け、単体テスト相当の関数検証を実施する。
- Owner: 実装担当

- Risk: API障害・メール送信失敗で通知欠落する。
- Mitigation: 失敗時は `error.log` を削除せず保持し、次回リクエスト時の再試行に委ねる。失敗内容は `error_log()` で記録する。
- Owner: 実装担当

## Implementation Plan

1. プラグイン骨格と設定ファイルを作成する。
- Files:
- `wp-ai-error-report.php`
- `config.php`（実ファイル、VCS除外）
- `config.example.php`（配布用テンプレート）
- `.gitignore`
- `includes/class-error-handler.php`
- `includes/class-report-sender.php`
- `includes/class-masking.php`
- Interface impact: 新規プラグインが追加され、`init` フックで定期判定処理が有効化される。
- Notes:
- `config.php` は `defined('ABSPATH') || exit;` を必須にする。
- `config.php` 設定項目（初版）:
- `api_key`
- `notification_emails`（カンマ区切り）
- `model`（デフォルト `gpt-4.1-mini`）
- `large_log_threshold`（文字列例: `1MB` / `512KB`）
- `max_lines`（通常時/巨大ログ時共通。初期値 `100`）
- `send_interval_minutes`（送信間隔（分）。初期値 `60`、例 `60 * 3`）

2. Fatal捕捉・JSONログ追記を実装する。
- Files:
- `includes/class-error-handler.php`
- `includes/class-masking.php`
- Interface impact: `register_shutdown_function` により `E_ERROR`, `E_PARSE`, `E_CORE_ERROR` のみ捕捉し、1行1JSONで追記される。
- Notes:
- JSON推奨フィールド: `timestamp`, `type`, `message_masked`, `file_masked`, `line`, `site_url`
- マスキング対象: 絶対パス、メールアドレス、APIキー様文字列（32文字以上英数字）
- ログディレクトリは `wp_upload_dir()` 配下に自動生成する。

3. 送信判定とレポート処理を実装する。
- Files:
- `includes/class-report-sender.php`
- `includes/class-error-handler.php`
- Interface impact: `init` 時およびエラー発生時の判定で、`send_interval_minutes` 以上（分換算）経過したログのみAI解析対象になる。
- Notes:
- 判定は `last_report_attempted_at.touch` の `filemtime` を使用する。
- `last_report_attempted_at.touch` が存在しない場合は送信可能として扱い、送信試行時（成功/失敗問わず）に `touch` 更新する。
- ログ読み取り時は末尾最大 `WP_AI_ERROR_REPORT_MAX_LINES` 行を取得する。
- 巨大ログ判定は `WP_AI_ERROR_REPORT_LARGE_LOG_THRESHOLD` をバイト換算して実施する。
- 巨大ログ時は要約本文にファイルサイズ情報を必ず含める。
- OpenAI API失敗時/メール失敗時/読み取り失敗時はいずれも `error.log` を保持する。
- 成功時のみ `error.log` と `last_report_attempted_at.touch` を削除する。

4. OpenAI通信・通知本文生成・送信を実装する。
- Files:
- `includes/class-report-sender.php`
- Interface impact: 管理者宛レポートメール送信の代わりに、`config` 指定の宛先群へ送信される（未設定時は送信スキップ）。
- Notes:
- OpenAI呼び出しは `wp_remote_post('https://api.openai.com/v1/responses', ...)`
- 送信タイムアウト、HTTP失敗、JSONパース失敗をハンドリングする。
- 件名は `[WordPress] エラーレポート - {サイト名}` 固定。
- 本文は平文のみ。
- 宛先が空の場合: 送信処理を行わず、処理は正常終了させる（必要に応じてデバッグログに理由を記録）。

5. 将来拡張ポイントを明示しつつPoC実装を閉じる。
- Files:
- `config.example.php`
- `README.md`（作成する場合）
- Interface impact: 運用者向けに設定方法と制約が明確になる。
- Notes:
- プロンプト詳細は本計画では骨子のみとし、実装時に学習しながら確定する。
- 将来追加候補（プロンプト設定、中間サーバー切替）を TODO として残す。

## Interfaces and Dependencies

- APIs:
- WordPress: `add_action`, `register_shutdown_function`, `wp_upload_dir`, `wp_remote_post`, `wp_mail`, `get_bloginfo`, `site_url`
- OpenAI: `POST /v1/responses`
- Data contracts:
- `error.log` は JSON Lines（1行1JSON）
- `config.php` は `return` 配列ベース
- External services:
- OpenAI API（APIキー必須）
- SMTP/メール送信経路（`wp_mail` が利用する環境）
- Version constraints:
- WordPress 6.x 系を想定
- PHP 8.1+ を想定
- WordPress/PHP requirements:
- 管理画面UIがないため nonce は不要。
- 外部入力を受けるUIがないため `current_user_can` チェックも不要（将来UI追加時に導入）。
- ログ・設定値の取り扱い時は `sanitize_text_field`, `sanitize_email`, `esc_html` 等を利用箇所で適用する。
- Prompt contract (current implementation):
- System: `あなたはWordPress運用アシスタントです。非エンジニアにも理解できる平易な日本語で要約してください。`
- User: Fatalログ説明、要約観点（何が起きているか/想定原因/最初の確認事項）、箇条書き指定、機密情報推測禁止、対象行数、巨大ログ時のサイズ明記指示、`LOG START/END` で囲んだ本文。

## Validation

- Command: `php -l wp-ai-error-report.php`
- Expected result: `No syntax errors detected`
- Evidence location: 実装時ターミナル出力

- Command: `php -l includes/class-error-handler.php`
- Expected result: `No syntax errors detected`
- Evidence location: 実装時ターミナル出力

- Command: `php -l includes/class-report-sender.php`
- Expected result: `No syntax errors detected`
- Evidence location: 実装時ターミナル出力

- Command: `php -l includes/class-masking.php`
- Expected result: `No syntax errors detected`
- Evidence location: 実装時ターミナル出力

- Command: `wp eval 'require_once WP_PLUGIN_DIR . "/wp-ai-error-report/wp-ai-error-report.php"; echo "loaded\n";'`
- Expected result: プラグイン読み込み時にFatalが発生しない
- Evidence location: WP-CLI出力

- Command: （手動）Fatal相当を発生させ、`wp-content/uploads/wp-ai-error-report/logs/error.log` を確認
- Expected result: 1行1JSONで追記され、マスキング済み項目が保存される
- Evidence location: `wp-content/uploads/wp-ai-error-report/logs/error.log`

- Command: （手動）`last_report_attempted_at.touch` の `filemtime` を1時間以上前にしてページアクセス
- Expected result: OpenAI要約処理とメール送信が試行され、成功時に `error.log` が削除される
- Evidence location: メール受信記録、`error.log` / `last_report_attempted_at.touch` 消失確認、WPデバッグログ

- Command: （手動）`last_report_attempted_at.touch` を現在時刻に更新した直後に再アクセス
- Expected result: 1時間未満のため再送されない（外部API呼び出し・追加メール送信なし）
- Evidence location: メール受信記録、WPデバッグログ、`last_report_attempted_at.touch` の時刻

- Command: （手動）OpenAI APIキーを無効値にして送信試行
- Expected result: 送信失敗時も `error.log` は保持され、`last_report_attempted_at.touch` は更新される
- Evidence location: `error.log` 残存確認、`last_report_attempted_at.touch` 更新時刻、WPデバッグログ

## Rollback / Recovery

- Trigger condition: 通知誤送信、API連携不安定、性能影響、マスキング不備が確認された場合。
- Rollback steps:
1. プラグインを無効化する。
2. `wp-ai-error-report` ディレクトリを退避または削除する。
3. `wp-content/uploads/wp-ai-error-report/logs/error.log` と `last_report_attempted_at.touch` を保全し、必要に応じて削除する。
4. `config.php` からAPIキーと宛先設定を無効化する。
- Data recovery notes: `error.log` は一時ファイル運用のため、復旧時は破棄可能。必要なら解析用に別途バックアップして再投入しない。

## Execution Notes (Living Updates)

- 2026-02-14T15:05:00Z 初版ExecPlan作成。未確定事項のうち、ログ保存先はセキュリティ推奨に従い `uploads` 配下を採用。
- 2026-02-14T16:29:49Z プラグイン初版を実装。`wp_remote_post` による OpenAI連携、JSON Linesログ、1時間判定、送信成功時ログ削除、未設定宛先スキップを反映。
- 2026-02-14T16:29:49Z `php -l` による主要PHPファイルの構文チェックは実施済み。WP-CLI実行と手動のFatal再現確認は実環境依存のため未実施。
- 2026-02-14T16:33:18Z 送信間隔判定を `error.log` 依存から `last_report_attempted_at.touch` 依存へ変更。ログ追記で判定不能になる問題を解消。
- 2026-02-14T16:46:52Z プラグイン配下 `logs` を廃止し、`uploads` 側のみを実ログ保存先として明確化。検証手順に再送抑止と失敗時保持を追加。
- 2026-02-14T16:46:52Z `last_report_attempted_at.touch` 未作成時は即時送信可とするよう実装を同期（判定ファイル作成をエラー追記時ではなく送信試行時に限定）。
- 2026-02-14T17:05:00Z 判定ファイル名を `last_report_attempt.touch` から `last_report_attempted_at.touch` へ変更し、実装・仕様書・ExecPlanの表記を統一。
- 2026-02-15T04:41:46Z リポジトリ配下の `wp-ai-error-report/` ディレクトリを廃止し、プラグイン構成をリポジトリ直下（`wp-ai-error-report.php`, `includes/`, `config*.php`）へ移設。
- 2026-02-15T05:26:42Z `debug` 設定を追加。有効時に `debug.log` へ処理ステップ（判定/送信/API/メール失敗点）を出力できるように実装。
- 2026-02-15T05:47:55Z コードベース簡素化のためデバッグログ機構を撤去し、通常運用の最小構成へ戻した。
- 2026-02-15T05:52:47Z 送信間隔設定を `send_interval_minutes`（分指定）へ統一。
- 2026-02-15T05:52:47Z 送信間隔設定を `send_interval_minutes` に変更（デフォルト60分、例: `60 * 3`）。
