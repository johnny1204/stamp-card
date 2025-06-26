# 子どもスタンプ帳 Web 版 - 機能仕様書

必ず日本語で回答してください。

## 1. システム概要

### 1.1 目的

子どもの日常行動や達成事項にスタンプを押して記録・評価するデジタルスタンプ帳システム

### 1.2 対象ユーザー

-   家庭内での利用
-   子ども（スタンプを見る・もらう）
-   保護者（スタンプを管理・付与）

### 1.3 コーディングルール

#### 密結合の回避

-   `new`を使ったインスタンス生成を原則禁止とする
    -   密結合とテスト時のモック化の問題を避けるため
    -   依存性注入やファクトリーメソッド（`make`など）を使用

#### 可読性の向上

-   if 文のネストは 2 階層までとする
    -   早期リターンなどを利用した可読性の良いコードを書くこと
    -   ガード句の積極的な使用

#### アクセサの記述方法

-   Laravel 9 以降の新しい Attribute 形式を使用
    -   `get〜Attribute()`ではなく`Attribute::make()`を使用
    -   `$appends`プロパティで JSON 出力に含める属性を明示

#### Model 層の責務制限

-   Model はデータベースとのマッピングのみに専念
-   ビジネスロジックは Service 層に記述
-   Model に複雑な処理メソッドを記述することは禁止
-   リレーション定義、アクセサ、スコープ（自テーブル完結）のみ許可

#### デバッグ・問題解決の原則

-   **推測による修正を禁止**: 問題の根本原因を特定してから修正を行う
-   **症状と原因の区別**: 表面的な症状ではなく、真の原因を突き止める
-   **情報収集の徹底**: ユーザーからの情報やデバッグ情報を十分に活用する
-   **段階的アプローチ**: 一度に複数の無関係な修正を行わず、一つずつ検証する
-   **ユーザー指摘の重視**: 「適当な修正」の指摘があった場合は即座に停止し、根本原因の特定に戻る
-   **修正前の影響範囲確認**: 変更がスタイルや他の機能に与える影響を事前に検討する

#### コミットルール

-   **コミットメッセージ**: 日本語で記述し、変更内容を簡潔に説明する
-   **Claude Code署名の禁止**: コミットメッセージに以下の署名を含めてはならない
    -   `🤖 Generated with [Claude Code](https://claude.ai/code)`
    -   `Co-Authored-By: Claude <noreply@anthropic.com>`
-   **コミット単位**: 機能単位または論理的なまとまりで分割する
-   **詳細な説明**: 必要に応じてコミットメッセージの本文で変更理由や影響範囲を説明する

### 1.4 技術構成

-   **フロントエンド**: React + Vite
-   **CSS**: Tailwind CSS v4
-   **バックエンド**: PHP 8.4 + Laravel 12+
-   **統合**: Inertia.js
-   **データベース**:
    -   **マスタ DB**: MySQL 8 系（Docker）- ポケモン情報、スタンプ種類等
    -   **スタンプ帳 DB**: SQLite - 日々のスタンプ記録、子ども情報等
-   **運用環境**: Docker（自宅サーバー）

## 2. 機能要件

### 2.1 ユーザー管理機能

-   **子ども登録**: 名前、誕生日、アバター設定
-   **保護者ログイン**: 簡単な認証（パスワード）
-   **子ども選択**: 複数の子どもに対応

### 2.2 スタンプ管理機能

-   **スタンプ種類マスタ**

    -   お手伝い系（掃除、片付け、料理手伝いなど）
    -   生活習慣系（歯磨き、早起き、宿題など）
    -   行動評価系（優しさ、頑張り、チャレンジなど）
    -   カスタムスタンプ（保護者が自由に作成）

-   **スタンプデザイン**

    -   可愛いアイコン・絵文字
    -   カラフルな色分け
    -   アニメーション効果

-   **お祝いモーダル機能**
    -   スタンプ付与時にポケモン画像を表示
    -   「頑張ったね！」「すごいね！」等の励ましメッセージ
    -   ポケモンはランダム表示（レアポケモンも出現）
    -   アニメーション効果（キラキラ、拍手等）
    -   効果音再生（オプション）

### 2.3 スタンプ帳機能

-   **スタンプ付与**

    -   保護者が子どもにスタンプを押す
    -   日付・時刻の自動記録
    -   コメント付与可能

-   **スタンプ表示**
    -   カレンダー形式での表示
    -   月間・週間・日別表示切り替え
    -   スタンプ数のカウント表示

### 2.4 達成・報酬機能

-   **目標設定**

    -   「○○ のスタンプを △ 個集めたら」の目標設定
    -   期間設定（週間、月間）

-   **報酬管理**
    -   デジタル証書・バッジ
    -   特別なアニメーション
    -   保護者からのメッセージ

### 2.5 統計・レポート機能

-   スタンプ数の集計（種類別、期間別）
-   成長グラフ表示
-   月間レポート自動生成

## 3. 画面設計

### 3.1 子ども向け画面

-   **ホーム画面**: 今日のスタンプ、今月の目標達成状況
-   **スタンプ帳画面**: カレンダー形式でスタンプ表示
-   **達成画面**: バッジ・証書の表示

### 3.2 保護者向け画面

-   **管理ダッシュボード**: 子ども選択、統計サマリー
-   **スタンプ付与画面**: スタンプ選択・付与
-   **設定画面**: スタンプ種類管理、目標設定
-   **レポート画面**: 詳細統計・グラフ

## 4. データベース設計

### 4.1 データベース構成

**マスタ DB（MySQL）**: 変更頻度が低い基本データ
**スタンプ帳 DB（SQLite）**: 日々の記録データ

### 4.2 マスタ DB（MySQL）テーブル構成

```sql
-- ポケモンマスタ
pokemons (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    rarity ENUM('common', 'rare', 'legendary') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- スタンプ種類マスタ
stamp_types (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(100),
    color VARCHAR(7),
    category ENUM('help', 'lifestyle', 'behavior', 'custom') NOT NULL,
    is_custom BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- お祝いメッセージマスタ
celebration_messages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    message TEXT NOT NULL,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 4.3 スタンプ帳 DB（SQLite）テーブル構成

```sql
-- 子どもテーブル
children (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    birth_date DATE,
    avatar_path TEXT,
    target_stamps INTEGER DEFAULT 10,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- スタンプ記録テーブル
stamps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    child_id INTEGER NOT NULL,
    stamp_type_id INTEGER NOT NULL, -- マスタDBのstamp_types.idを参照
    pokemon_id INTEGER NOT NULL,    -- マスタDBのpokemons.idを参照
    stamped_at DATETIME NOT NULL,
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES children(id)
);

-- 目標テーブル
goals (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    child_id INTEGER NOT NULL,
    stamp_type_id INTEGER NOT NULL, -- マスタDBのstamp_types.idを参照
    target_count INTEGER NOT NULL,
    period_type TEXT CHECK(period_type IN ('weekly', 'monthly')),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reward_text TEXT,
    is_achieved BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (child_id) REFERENCES children(id)
);

-- 管理者テーブル
admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    password_hash TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## 5. UI/UX 要件

### 5.1 デザイン方針

-   **子ども向け**: カラフル、大きなボタン、直感的操作
-   **保護者向け**: シンプル、効率的、情報が見やすい
-   **レスポンシブ**: タブレット・スマートフォン対応

### 5.2 お祝いモーダルデザイン

-   **ポケモン画像**: 中央に大きく表示
-   **背景**: キラキラエフェクト、虹色グラデーション
-   **メッセージ**: 大きな文字で「おめでとう！」「頑張ったね！」
-   **アニメーション**:
    -   フェードイン → スケールアップ → バウンス効果
    -   紙吹雪・星・ハートのパーティクル効果
    -   ポケモンのちょっとした動き（瞬き、揺れ等）

### 5.3 操作性

-   タッチ操作対応
-   アニメーション効果でフィードバック
-   音効果（オプション）
-   モーダル自動クローズ（5 秒後）またはタップでクローズ

## 6. セキュリティ要件

### 6.1 認証・認可

-   保護者向け簡単パスワード認証
-   セッション管理
-   CSRF 対策

### 6.2 データ保護

-   個人情報の適切な管理
-   バックアップ機能

## 7. 運用要件

### 7.1 Docker 構成

```yaml
# docker-compose.yml 構成イメージ
services:
    app:
        build:
            context: .
            dockerfile: docker/php/Dockerfile
        volumes:
            - ./:/var/www
            - ./storage/database:/var/www/storage/database # SQLiteファイル用
        environment:
            # Xdebug設定（開発環境）
            XDEBUG_MODE: debug
            XDEBUG_CONFIG: client_host=host.docker.internal client_port=9003
        extra_hosts:
            - "host.docker.internal:host-gateway"
        networks:
            - stamp-network

    web:
        image: nginx:alpine
        ports:
            - "80:80"
        volumes:
            - ./:/var/www
            - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf
        depends_on:
            - app
        networks:
            - stamp-network

    mysql:
        image: mysql:8.0
        environment:
            MYSQL_DATABASE: stamp_master
            MYSQL_USER: stamp_user
            MYSQL_PASSWORD: stamp_pass
            MYSQL_ROOT_PASSWORD: root_password
        volumes:
            - mysql_data:/var/lib/mysql
        ports:
            - "3306:3306"
        command: --default-authentication-plugin=mysql_native_password
        networks:
            - stamp-network

    phpmyadmin:
        image: phpmyadmin/phpmyadmin
        environment:
            PMA_HOST: mysql
            PMA_USER: stamp_user
            PMA_PASSWORD: stamp_pass
        ports:
            - "8080:80"
        depends_on:
            - mysql
        networks:
            - stamp-network

    # Node.js for Vite development server
    node:
        image: node:20-alpine
        working_dir: /app
        volumes:
            - .:/app
        ports:
            - "5173:5173"
        command: sh -c "npm install && npm run dev"
        networks:
            - stamp-network

volumes:
    mysql_data:

networks:
    stamp-network:
        driver: bridge
```

```dockerfile
# docker/php/Dockerfile（PHP + Xdebug設定例）
FROM php:8.4-fpm

# 必要な拡張をインストール
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip

# PHP拡張をインストール
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Xdebugをインストール
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Xdebug設定
RUN echo "xdebug.mode=debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_host=host.docker.internal" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.client_port=9003" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.discover_client_host=1" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Composerをインストール
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
```

### 7.2 データベース接続設定

```php
// config/database.php の設定例
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', 'mysql'),
        'database' => env('DB_DATABASE', 'stamp_master'),
        // マスタデータ用接続
    ],
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => storage_path('database/stamp_records.sqlite'),
        // スタンプ記録用接続
    ],
],
'default' => env('DB_CONNECTION', 'sqlite'),
```

### 7.3 バックアップ戦略

-   **SQLite ファイル**: 日々のスタンプ記録の定期バックアップ（重要度：高）
-   **MySQL データ**: マスタデータのバックアップ（頻度：低）
-   **画像ファイル**: ポケモン画像、アバター画像のバックアップ

### 7.4 開発環境デバッグ設定

-   **Xdebug リモートデバッグ対応**: VS Code、PhpStorm 等の IDE からステップ実行可能
-   **デバッグポート**: 9003（標準）
-   **接続方式**: host.docker.internal 経由でホストマシンの IDE と接続
-   **ブレークポイント**: IDE 上で設定可能、リアルタイムデバッグ実行

#### IDE 設定例（VS Code）

```json
// .vscode/launch.json
{
    "version": "0.2.0",
    "configurations": [
        {
            "name": "Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www": "${workspaceFolder}"
            }
        }
    ]
}
```

### 7.5 データベース利用方針

-   **読み取り頻度の高いマスタデータ**: MySQL でパフォーマンス重視
-   **書き込み頻度の高いスタンプ記録**: SQLite でシンプル運用
-   **Laravel モデルでの接続切り替え**: `$connection` プロパティで使い分け

## 8. 開発優先順位

### Phase 1（MVP）

1. 開発環境構築
    - Docker（PHP 8.4 + Laravel 12+ + MySQL 8 + Node.js + Xdebug）
    - Vite + React + Inertia.js セットアップ
    - Tailwind CSS v4 設定
2. **アーキテクチャ基盤構築**
    - Controller/Service/Model 層の分離
    - FormRequest、Resource クラスの準備
    - 基本的な Service 層パターンの確立
3. **テスト基盤構築**
    - PHPUnit 設定、Factory 作成
    - Feature/Unit テストの基本構造確立
    - テストカバレッジ測定環境構築
4. 簡単な認証機能（テスト含む）
5. 子ども登録・選択機能（テスト含む）
6. 基本的なスタンプ付与・表示機能（テスト含む）
7. **ポケモンお祝いモーダル機能**（テスト含む）

### Phase 2

1. 画像、鳴き声キャッシュ(1 度取得した画像、鳴き声はローカルストレージへキャッシュし外部リクエストを減らす)
2. レアポケモン出現システム（テスト含む）is_legendary=1 カードの最後に 1 回、 is_mythical=100 回に 1 回
3. カレンダー表示機能（テスト含む）
4. 基本的な統計機能（テスト含む）
5. **継続的な 100%カバレッジ維持**

### Phase 3

1. 目標・報酬機能（テスト含む）
2. 詳細レポート機能（テスト含む）
3. UI/UX の改善
4. 効果音・音楽機能
5. **E2E テスト追加**（Cypress 等）

## 9. 技術的考慮事項

### 9.1 Inertia.js 統合

-   Laravel Routes と React コンポーネントの連携
-   データのやり取り（props 経由）
-   フォーム処理（Inertia Form helpers）

### 9.2 フロントエンド

-   **React + Vite**: 高速な開発環境と HMR
-   **Tailwind CSS v4**: 最新の CSS-in-JS アプローチ、Native CSS 機能活用
-   **Inertia.js**: SPA 風の UX とサーバーサイドルーティングの両立
-   **コンポーネント設計**: 再利用可能な UI 部品
-   **アニメーション**: CSS Transitions、Tailwind Animations で実装

### 9.3 バックエンドアーキテクチャ

-   **PHP 8.4**: 最新の言語機能（プロパティフック、非対称可視性等）活用
-   **Laravel 12+**: 最新のフレームワーク機能活用
-   **設計方針**: Laravel ベースの DDD（Domain Driven Design）アプローチ

#### 開発原則

-   **DRY (Don't Repeat Yourself)**: 重複コードの排除、共通処理の抽象化
-   **KISS (Keep It Simple, Stupid)**: シンプルで理解しやすい実装
-   **YAGNI (You Aren't Gonna Need It)**: 現在必要な機能のみ実装、過度な汎用化は避ける
-   **PHPDoc 必須**: 全てのクラス、メソッド、プロパティに適切なドキュメント記述
-   **テスト駆動開発**: 全ての機能に対して適切なテストを作成

#### テスト戦略

**Feature Test（結合テスト）**

-   Controller 層での統合テスト
-   HTTP リクエスト〜レスポンスまでの一連の流れをテスト
-   実際のユーザー操作に近いシナリオテスト
-   データベース、外部サービスとの連携確認

**Unit Test（単体テスト）**

-   Model、Service 層の単体テスト
-   **カバレッジ 100%を目標**とした徹底的なテスト
-   モック、スタブを活用した依存関係の分離
-   境界値、異常系のテストも含む

```php
// テストディレクトリ構成
tests/
├── Feature/
│   ├── StampControllerTest.php
│   ├── ChildControllerTest.php
│   └── AuthTest.php
├── Unit/
│   ├── Services/
│   │   ├── StampServiceTest.php
│   │   ├── ChildServiceTest.php
│   │   └── PokemonServiceTest.php
│   └── Models/
│       ├── StampTest.php
│       ├── ChildTest.php
│       └── PokemonTest.php
└── TestCase.php
```

#### アーキテクチャ層の責務分離

**Controller 層**

-   HTTP リクエスト/レスポンスに関する責務のみ
-   バリデーション（FormRequest 活用）
-   Service への処理委譲
-   Inertia.js レスポンスの生成

**Model 層（Eloquent）**

-   データベースとのマッピング
-   アクセサ、ミューテータの定義
-   リレーションの設定
-   **スコープの制限**: 自分自身のテーブルで完結するもののみ
-   **禁止事項**: 他テーブルと JOIN するようなスコープは禁止

**Service 層**

-   ビジネスロジックの実装
-   複雑なクエリの組み立て
-   複数 Model にまたがる処理の調整
-   トランザクション管理

```php
// ディレクトリ構成例
app/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Models/
├── Services/
│   ├── StampService.php
│   ├── ChildService.php
│   └── PokemonService.php
└── Providers/
```

**実装例**:

```php
/**
 * スタンプ管理Controller
 *
 * HTTPリクエストの処理とInertiaレスポンスの生成を担当
 */
class StampController extends Controller
{
    /**
     * スタンプを作成する
     *
     * @param StampRequest $request バリデーション済みリクエスト
     * @param StampService $stampService スタンプサービス
     * @return \Inertia\Response
     */
    public function store(StampRequest $request, StampService $stampService): \Inertia\Response
    {
        $stamp = $stampService->createStamp($request->validated());

        return Inertia::render('Stamp/Success', [
            'stamp' => $stamp,
            'pokemon' => $stamp->pokemon
        ]);
    }
}

/**
 * スタンプ関連のビジネスロジックを担当するサービス
 */
class StampService
{
    /**
     * 新しいスタンプを作成する
     *
     * @param array<string, mixed> $data スタンプデータ
     * @return Stamp 作成されたスタンプ（リレーション含む）
     * @throws \Exception データベースエラー時
     */
    public function createStamp(array $data): Stamp
    {
        return DB::transaction(function () use ($data) {
            $pokemon = $this->selectRandomPokemon();

            $stamp = Stamp::create([
                'child_id' => $data['child_id'],
                'stamp_type_id' => $data['stamp_type_id'],
                'pokemon_id' => $pokemon->id,
                'stamped_at' => now(),
                'comment' => $data['comment'] ?? null,
            ]);

            $this->checkGoalAchievement($stamp);

            return $stamp->load('pokemon', 'stampType');
        });
    }

    /**
     * ランダムにポケモンを選択する
     *
     * @return Pokemon 選択されたポケモン
     */
    private function selectRandomPokemon(): Pokemon
    {
        // レアリティに基づく重み付きランダム選択
        // ...実装詳細
    }
}

/**
 * スタンプモデル
 *
 * スタンプ記録テーブルとのマッピングを担当
 */
class Stamp extends Model
{
    /** @var string データベース接続 */
    protected $connection = 'sqlite';

    /** @var array<string> 代入可能な属性 */
    protected $fillable = [
        'child_id',
        'stamp_type_id',
        'pokemon_id',
        'stamped_at',
        'comment'
    ];

    /**
     * 関連するポケモンを取得
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function pokemon(): BelongsTo
    {
        return $this->belongsTo(Pokemon::class);
    }

    /**
     * 今日作成されたスタンプのみを取得するスコープ
     * （自テーブル完結のスコープのみ許可）
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('stamped_at', today());
    }
}
```

### 9.4 テスト環境・品質管理

-   **複数 DB 接続**: Laravel の Multiple Database Connections 活用
-   **マスタデータ**: MySQL で安定性・拡張性重視
-   **トランザクションデータ**: SQLite で軽量・バックアップ容易性重視
-   **モデル設計**: 各モデルで適切な connection を指定

**テストカバレッジ管理**:

```bash
# カバレッジレポート生成
php artisan test --coverage --min=100

# HTMLカバレッジレポート
php artisan test --coverage-html coverage
```

**継続的品質管理**:

-   **PHPStan**: 静的解析レベル max
-   **PHP CS Fixer**: コーディングスタイル統一
-   **Larastan**: Laravel 特化の静的解析

```php
// Model設計例（接続分離）
/**
 * ポケモンマスタモデル（MySQL接続）
 */
class Pokemon extends Model
{
    /** @var string データベース接続 */
    protected $connection = 'mysql';

    /** @var array<string> 代入可能な属性 */
    protected $fillable = ['name', 'image_path', 'rarity'];
}
```

## 10. 今後の拡張可能性

-   複数家庭での利用
-   写真付きスタンプ
-   兄弟間での競争要素
-   外部連携（カレンダーアプリ等）
-   **ポケモン図鑑機能**（集めたポケモンの一覧表示）
-   **季節限定ポケモン**（クリスマス、誕生日等）
-   **連続スタンプボーナス**（連続で頑張った時の特別ポケモン）

## 11. ポケモン画像管理

### 11.1 画像取得・管理

-   ポケモン公式サイトから画像を引用
-   画像ファイルは public/images/pokemons ディレクトリに配置
-   ファイル名は統一形式（例：001_pikachu.png）
-   WebP 形式で軽量化推奨
-   レスポンシブ対応（複数サイズ用意）

### 11.2 ポケモンデータ

-   初期データとして人気ポケモン 50-100 体程度を登録
-   レアリティ設定：
    -   ★☆☆（common）: 70% - ピカチュウ、イーブイなど
    -   ★★☆（rare）: 25% - 御三家、準伝説など
    -   ★★★（legendary）: 5% - 伝説ポケモン
-   子どもの好みに応じて随時追加可能
