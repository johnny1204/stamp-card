# ==============================================================================
# 子どもスタンプ帳アプリケーション - Makefile
# ==============================================================================

.PHONY: help up down restart build logs shell install migrate seed test clean fresh

# デフォルトターゲット
.DEFAULT_GOAL := help

# ヘルプ表示
help: ## このヘルプメッセージを表示
	@echo "利用可能なコマンド:"
	@echo ""
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

# ==============================================================================
# Docker 関連コマンド
# ==============================================================================

up: ## Dockerコンテナを起動
	docker compose up -d
	@echo "✅ アプリケーションが起動しました"
	@echo "🌐 アプリケーション: http://localhost:18088"
	@echo "🌐 ネットワークアクセス: http://[サーバーIP]:18088"
	@echo "📊 phpMyAdmin: http://localhost:18081"
	@echo "🗄️  MySQL: localhost:13307 (ユーザー: stamp_user, パスワード: stamp_pass)"
	@echo "⚡ Vite開発サーバー: http://localhost:15173"

down: ## Dockerコンテナを停止・削除
	docker compose down
	@echo "✅ コンテナを停止しました"

restart: ## Dockerコンテナを再起動
	docker compose restart
	@echo "✅ コンテナを再起動しました"

build: ## Dockerイメージを再ビルド
	docker compose build --no-cache
	@echo "✅ イメージを再ビルドしました"

rebuild: down build up ## 完全再ビルド（停止→ビルド→起動）

logs: ## ログを表示
	docker compose logs -f

logs-app: ## アプリケーションのログのみ表示
	docker compose logs -f app

logs-db: ## データベースのログのみ表示
	docker compose logs -f mysql

ps: ## 実行中のコンテナ一覧を表示
	docker compose ps

# ==============================================================================
# アプリケーション操作
# ==============================================================================

shell: ## アプリケーションコンテナにログイン
	docker compose exec app bash

shell-root: ## アプリケーションコンテナにrootでログイン
	docker compose exec -u root app bash

mysql: ## MySQLコンテナにログイン
	docker compose exec mysql mysql -u root -p stamp_master

# ==============================================================================
# Laravel関連コマンド
# ==============================================================================

install: ## 依存関係をインストール
	docker compose exec app composer install
	docker compose exec node npm install
	@echo "✅ 依存関係をインストールしました"

migrate: ## マイグレーションを実行
	docker compose exec app php artisan migrate
	@echo "✅ マイグレーションを実行しました"

migrate-fresh: ## マイグレーションをリセットして再実行
	docker compose exec app php artisan migrate:fresh
	@echo "✅ マイグレーションをリフレッシュしました"

seed: ## シーダーを実行
	docker compose exec app php artisan db:seed
	@echo "✅ シーダーを実行しました"

fresh: clean-db ## データベース完全リフレッシュ + シーダー実行

key-generate: ## アプリケーションキーを生成
	docker compose exec app php artisan key:generate
	@echo "✅ アプリケーションキーを生成しました"

cache-clear: ## キャッシュをクリア
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear
	@echo "✅ キャッシュをクリアしました"

# ==============================================================================
# テスト関連
# ==============================================================================

test: ## 全テストを実行
	docker compose exec app php artisan test

test-unit: ## ユニットテストのみ実行
	docker compose exec app php artisan test --testsuite=Unit

test-feature: ## フィーチャーテストのみ実行
	docker compose exec app php artisan test --testsuite=Feature

test-coverage: ## テストカバレッジを表示
	docker compose exec app php artisan test --coverage

test-watch: ## テストをwatch モードで実行
	docker compose exec app php artisan test --watch

# ==============================================================================
# 開発関連
# ==============================================================================

dev: ## 開発サーバーを起動（Vite）
	docker compose exec node npm run dev

build-assets: ## アセットをビルド
	docker compose exec node npm run build

watch: ## アセットをwatch モードでビルド
	docker compose exec node npm run dev

lint: ## コードをLint
	docker compose exec node npm run lint

typecheck: ## TypeScriptの型チェック
	docker compose exec node npm run type-check

# ==============================================================================
# データベース関連
# ==============================================================================

db-reset: ## データベースをリセット（マイグレーション + シーダー）
	docker compose exec app php artisan migrate:fresh --seed
	@echo "✅ データベースをリセットしました"

clean-db: ## データベースを完全クリーンアップ
	docker compose exec mysql mysql -u root -proot_password -e "DROP DATABASE IF EXISTS stamp_master; CREATE DATABASE stamp_master;"
	docker compose exec app rm -f storage/database/database.sqlite
	docker compose exec app mkdir -p storage/database
	docker compose exec app touch storage/database/database.sqlite
	docker compose exec app php artisan migrate:fresh --seed
	@echo "✅ データベースを完全クリーンアップしました"

backup-db: ## データベースをバックアップ
	docker compose exec mysql mysqldump -u root -p stamp_master > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "✅ データベースをバックアップしました"

# ==============================================================================
# セットアップ関連
# ==============================================================================

setup: up install key-generate create-sqlite fix-permissions fresh ## 初回セットアップ（推奨）
	@echo ""
	@echo "🎉 セットアップが完了しました！"
	@echo ""
	@echo "📖 次のステップ:"
	@echo "   1. http://localhost:18088 でアプリケーションにアクセス"
	@echo "   2. 管理者パスワードを設定"
	@echo "   3. 子どもを登録してスタンプを楽しもう！"
	@echo ""
	@echo "🔧 その他の便利なコマンド:"
	@echo "   make dev     # 開発サーバー起動"
	@echo "   make test    # テスト実行"
	@echo "   make logs    # ログ確認"
	@echo ""

create-sqlite: ## SQLiteファイルを作成
	docker compose exec app mkdir -p storage/database
	docker compose exec app touch storage/database/database.sqlite
	@echo "✅ SQLiteファイルを作成しました"

fix-permissions: ## storageディレクトリの権限を修正
	docker compose exec app chmod -R 775 storage bootstrap/cache
	docker compose exec app chown -R www-data:www-data storage bootstrap/cache
	@echo "✅ 権限を修正しました"

# ==============================================================================
# クリーンアップ
# ==============================================================================

clean: ## 不要なファイル・キャッシュを削除
	docker compose down -v
	docker compose rm -f
	docker volume prune -f
	docker network prune -f
	@echo "✅ クリーンアップしました"

clean-all: clean ## 完全クリーンアップ（イメージも削除）
	docker compose down -v --rmi all
	@echo "✅ 完全クリーンアップしました"

# ==============================================================================
# 監視・デバッグ
# ==============================================================================

status: ## システム状態を確認
	@echo "=== Docker Compose サービス状態 ==="
	docker compose ps
	@echo ""
	@echo "=== ディスク使用量 ==="
	docker system df
	@echo ""
	@echo "=== ネットワーク ==="
	docker network ls | grep stamp

health: ## ヘルスチェック
	@echo "📊 アプリケーション ヘルスチェック"
	@curl -f http://localhost:18088 >/dev/null 2>&1 && echo "✅ アプリケーション: 正常" || echo "❌ アプリケーション: 異常"
	@curl -f http://localhost:18081 >/dev/null 2>&1 && echo "✅ phpMyAdmin: 正常" || echo "❌ phpMyAdmin: 異常"

# ==============================================================================
# プロダクション関連
# ==============================================================================

prod-build: ## プロダクション用ビルド
	docker compose exec node npm run build
	docker compose exec app php artisan config:cache
	docker compose exec app php artisan route:cache
	docker compose exec app php artisan view:cache
	@echo "✅ プロダクション用ビルドが完了しました"

prod-optimize: ## プロダクション最適化
	docker compose exec app composer install --optimize-autoloader --no-dev
	docker compose exec app php artisan optimize
	@echo "✅ プロダクション最適化が完了しました"