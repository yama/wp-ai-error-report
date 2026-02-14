# ExecPlan Improve Checklist

`improve` モードで既存ExecPlanを修正するときの詳細チェック項目です。

## 1. 対象特定

- ユーザー指定ファイルがあれば最優先する。
- 指定がなければ `.agent/plans/` の最新ファイルを候補にし、前提として明示する。

## 2. 必須セクション不足の確認

以下が欠けていれば追加する:

- Purpose and scope
- Progress checklist
- Findings/context
- Decision log
- Risks and mitigations
- Implementation steps with file paths
- Verification commands and expected results
- Rollback or recovery notes
- Interfaces/dependencies (必要時)

## 3. 実行可能性の確認

- 実装手順が曖昧語のみで記載されていないか。
- どのファイルをどう変更するかが明記されているか。
- 手順順序に依存関係の矛盾がないか。

## 4. 検証可能性の確認

- 各主要変更に検証コマンドがあるか。
- 検証結果の期待値が記載されているか。
- 可能なら `php -l` を対象ファイルで明記しているか。
- テスト基盤がある場合は PHPUnit / WP-CLI を活用しているか。

## 5. WordPress固有観点

- フック統合点（`add_action` / `add_filter`）の記載があるか。
- 権限/nonce検証（`current_user_can`, `check_admin_referer`, `wp_verify_nonce`）が必要箇所で扱われているか。
- 入力サニタイズ（`sanitize_*`）と出力エスケープ（`esc_*`, `wp_kses`）方針があるか。
- PHP/WordPress最低バージョン前提が必要時に明記されているか。

## 6. ログの鮮度

- `Progress` が現状に一致しているか。
- 実装方針変更があれば `Decision Log` に日付と理由があるか。
- 既知のリスクが最新化されているか。

## 7. 変更ポリシー

- ユーザー要求がない限り、計画の主目的は変更しない。
- 既存ファイルを上書き改善し、別ファイル分岐は原則しない。
- 大きな方向転換は `Decision Log` に理由を残す。
