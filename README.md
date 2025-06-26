# 子どもスタンプ帳 Web 版

子どもの日常行動や達成事項にスタンプを押して記録・評価するデジタルスタンプ帳システムです。

![image](https://github.com/johnny1204/stamp-card/blob/main/stamp.gif)

## ✨ 特徴

-   🎯 子どもの行動にスタンプを付与
-   🎉 スタンプ獲得時のポケモンお祝いモーダル
-   📊 成長記録とレポート機能
-   👪 複数の子どもに対応
-   📱 レスポンシブデザイン（スマホ・タブレット対応）

## 🛠 技術構成

-   **フロントエンド**: React + Vite + Tailwind CSS v4
-   **バックエンド**: PHP 8.4 + Laravel 12+
-   **統合**: Inertia.js
-   **データベース**: MySQL 8（マスタ） + SQLite（記録）
-   **開発環境**: Docker

## 📋 必要な環境

-   Docker
-   Docker Compose
-   Make（オプション）

## 🚀 ローカル環境構築

### 1. リポジトリのクローン

```bash
git clone <repository-url>
cd stamp
```

### 2. 環境設定

環境変数ファイルを作成：

```bash
cp .env.example .env
```

**ネットワーク内他端末からアクセスする場合の追加設定：**

`.env`ファイルで以下を設定：

```env
# サーバーのIPアドレスに変更（例：192.168.1.100）
APP_URL=http://[サーバーIP]:18088

# Vite HMR設定（開発時のホットリロード用）
VITE_HMR_HOST=[サーバーIP]
```

### 3. Docker 環境の起動

#### Make コマンドを使用する場合（推奨）

```bash
# 初回セットアップ（自動で以下を実行します）
# - Docker コンテナ起動
# - 依存関係インストール
# - アプリケーションキー生成
# - データベースマイグレーション
# - 初期データ投入
make setup
```

#### 手動で実行する場合

```bash
# Docker コンテナ起動
docker compose up -d

# 依存関係インストール
docker compose exec app composer install
docker compose exec node npm install

# アプリケーションキー生成
docker compose exec app php artisan key:generate

# データベースマイグレーション + 初期データ投入
docker compose exec app php artisan migrate:fresh --seed
```

### 4. アクセス確認

セットアップ完了後、以下の URL にアクセスできます：

-   **アプリケーション**: http://localhost:18088
-   **ネットワーク内他端末から**: http://[サーバー IP]:18088
-   **phpMyAdmin**: http://localhost:18081
-   **Vite 開発サーバー**: http://localhost:15173

## 🔧 開発用コマンド

### Make コマンド一覧

```bash
# コンテナ操作
make up          # Docker コンテナ起動
make down        # Docker コンテナ停止
make restart     # Docker コンテナ再起動
make logs        # ログ表示
make shell       # アプリケーションコンテナにログイン

# データベース操作
make migrate     # マイグレーション実行
make seed        # シーダー実行
make db-reset    # データベースリセット
make clean-db    # データベース完全クリーンアップ

# 開発作業
make dev         # Vite開発サーバー起動
make test        # テスト実行
make lint        # コードLint
make typecheck   # TypeScript型チェック

# その他
make help        # 利用可能なコマンド一覧表示
```

### 直接 Docker コマンドを使用する場合

```bash
# フロントエンド開発サーバー起動
docker compose exec node npm run dev

# テスト実行
docker compose exec app php artisan test

# アプリケーションコンテナにログイン
docker compose exec app bash
```

## 🗄️ データベース接続情報

### MySQL（マスタデータ）

-   **ホスト**: localhost
-   **ポート**: 13307
-   **データベース**: stamp_master
-   **ユーザー**: stamp_user
-   **パスワード**: stamp_pass

### SQLite（スタンプ記録）

-   **ファイル**: `storage/database/database.sqlite`

## 🧪 テスト

```bash
# 全テスト実行
make test

# テストカバレッジ表示
docker compose exec app php artisan test --coverage

# ユニットテストのみ
docker compose exec app php artisan test --testsuite=Unit

# フィーチャーテストのみ
docker compose exec app php artisan test --testsuite=Feature
```

## 🐛 トラブルシューティング

### データベース関連のエラーが発生した場合

```bash
# データベースを完全リセット
make clean-db
```

### npm コマンドでエラーが発生した場合

npm コマンドは`node`コンテナで実行してください：

```bash
docker compose exec node npm install
docker compose exec node npm run dev
```

### コンテナが起動しない場合

```bash
# 完全クリーンアップして再構築
make clean-all
make setup
```

### ポート競合エラーの場合

以下のポートが使用されていないか確認してください：

-   18088（アプリケーション）
-   18081（phpMyAdmin）
-   13307（MySQL）
-   15173（Vite）

### ネットワーク内他端末からアクセスできない場合

1. サーバーのファイアウォール設定を確認
2. ポート 18088 が開いているか確認
3. サーバーの IP アドレスを確認：`ip addr show` または `ifconfig`

## 📁 プロジェクト構成

```
stamp/
├── app/                    # Laravel アプリケーション
│   ├── Http/Controllers/   # コントローラー
│   ├── Models/            # Eloquent モデル
│   └── Services/          # ビジネスロジック
├── database/
│   ├── migrations/        # データベースマイグレーション
│   └── seeders/          # 初期データ
├── resources/
│   ├── js/               # React コンポーネント
│   └── css/              # Tailwind CSS
├── storage/database/     # SQLite データベース
├── docker/               # Docker 設定
├── Makefile             # 開発コマンド
└── docker-compose.yml   # Docker Compose 設定
```
