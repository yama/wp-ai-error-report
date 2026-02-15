# WP AI Error Report (PoC)

WordPress で発生した Fatal 系エラーを検知し、AI で要約してメール通知する PoC プラグインです。

## できること

- Fatal 系エラーを自動で記録
- エラー内容をマスキングして AI 要約
- 過剰通知を抑えたバッチ送信

## セットアップ

1. `config.example.php` を参考に `config.php` を作成
2. API キーと通知先メールアドレスを設定
3. 必要に応じて `send_interval_minutes` を設定（例: `60 * 3`）
4. プラグインを有効化

`config.php` は機密情報を含むため Git 管理対象外です。

## 注意事項

- 本プラグインは PoC（概念実証）版です。
- 詳細仕様・動作条件は `.docs/wp-ai-error-report-spec.md` を参照してください。

## API接続テスト（CLI）

- `config.php` の `api_key` / `model` を使って OpenAI API への疎通確認ができます。
- 実行コマンド:
  - `php tools/test-openai-api.php`
  - `php tools/test-openai-api.php "Reply with exactly: PONG"`
