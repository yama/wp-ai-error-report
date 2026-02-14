# WP AI Error Report (PoC)

WordPress で発生した Fatal 系エラーを検知し、AI で要約してメール通知する PoC プラグインです。

## できること

- Fatal 系エラーを自動で記録
- エラー内容をマスキングして AI 要約
- 過剰通知を抑えたバッチ送信

## セットアップ

1. `wp-ai-error-report/config.example.php` を参考に `wp-ai-error-report/config.php` を作成
2. API キーと通知先メールアドレスを設定
3. プラグインを有効化

`config.php` は機密情報を含むため Git 管理対象外です。

## 注意事項

- 本プラグインは PoC（概念実証）版です。
- 詳細仕様・動作条件は `.docs/wp-ai-error-report-spec.md` を参照してください。
