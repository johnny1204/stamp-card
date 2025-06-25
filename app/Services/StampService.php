<?php

namespace App\Services;

use App\Models\Stamp;
use App\Models\Child;
use App\Models\StampType;
use App\Services\StampCardService;
use App\Services\PokemonService;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

/**
 * スタンプ関連のビジネスロジックを担当するサービス
 */
class StampService extends BaseService
{
    public function __construct(
        private PokemonSelectionService $pokemonSelectionService,
        private StampCardService $stampCardService,
        private PokemonService $pokemonService
    ) {}

    /**
     * 新しいスタンプを作成する
     *
     * @param Child $child 対象の子ども
     * @param array<string, mixed> $data スタンプデータ
     * @return array<string, mixed> 作成されたスタンプとカード完成情報
     * @throws \Exception データベースエラー時
     */
    public function createStamp(Child $child, array $data): array
    {
        return $this->sqliteTransaction(function () use ($child, $data) {
            // 総スタンプ数を取得（この後作成されるスタンプを含む）
            $totalStampCount = Stamp::where('child_id', $child->id)->count() + 1;
            
            // 現在のカードの進行状況をチェック
            $currentCard = $this->stampCardService->getOrCreateCurrentCard($child);
            $currentProgress = $currentCard->progress;
            $isCardCompletion = ($currentProgress + 1) >= $currentCard->target_stamps;
            
            // レアポケモン出現システム：特別なポケモンを選択
            $specialPokemon = $this->pokemonService->selectSpecialPokemon($isCardCompletion);
            
            if ($specialPokemon) {
                $pokemon = $specialPokemon;
            } else {
                // 通常のランダム選択
                $pokemon = $this->pokemonSelectionService->selectRandomPokemon();
            }

            $stamp = Stamp::create([
                'child_id' => $child->id,
                'stamp_type_id' => $data['stamp_type_id'],
                'pokemon_id' => $pokemon->id,
                'stamped_at' => now(),
                'comment' => $data['comment'] ?? null,
            ]);

            // スタンプカードサービスを使用してカードに追加（自動カード作成含む）
            $cardInfo = $this->stampCardService->addStampToCard($stamp);

            return [
                'stamp' => $stamp->load('pokemon', 'stampType'),
                'card_info' => $cardInfo,
                'special_pokemon_info' => [
                    'is_special' => $specialPokemon !== null,
                    'reason' => $specialPokemon ? $this->getSpecialPokemonReason($specialPokemon, $isCardCompletion) : null,
                    'total_stamp_count' => $totalStampCount,
                    'is_card_completion' => $isCardCompletion,
                ],
            ];
        });
    }

    /**
     * 特別なポケモンが出現した理由を取得
     *
     * @param \App\Models\Pokemon $pokemon 特別なポケモン
     * @param bool $isCardCompletion カード完成かどうか
     * @return string 出現理由
     */
    private function getSpecialPokemonReason(\App\Models\Pokemon $pokemon, bool $isCardCompletion): string
    {
        if ($pokemon->is_mythical) {
            return "ラッキー！100分の1の確率で幻のポケモンが出現！";
        }
        
        if ($pokemon->is_legendary && $isCardCompletion) {
            return "スタンプカード完成おめでとう！レジェンダリーポケモンが出現！";
        }
        
        return "特別なポケモンが出現！";
    }

    /**
     * 子どもの全スタンプを取得
     *
     * @param Child $child 対象の子ども
     * @param int|null $limit 取得件数制限
     * @return \Illuminate\Database\Eloquent\Collection<int, Stamp>
     */
    public function getChildStamps(Child $child, ?int $limit = null): Collection
    {
        $query = $child->stamps()
            ->with(['pokemon', 'stampType'])
            ->orderBy('stamped_at', 'desc');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * 子どもの今日のスタンプを取得
     *
     * @param Child $child 対象の子ども
     * @return \Illuminate\Database\Eloquent\Collection<int, Stamp>
     */
    public function getTodayStamps(Child $child): Collection
    {
        return $child->stamps()
            ->with(['pokemon', 'stampType'])
            ->today()
            ->orderBy('stamped_at', 'desc')
            ->get();
    }

    /**
     * 子どもの今月のスタンプを取得
     *
     * @param Child $child 対象の子ども
     * @return \Illuminate\Database\Eloquent\Collection<int, Stamp>
     */
    public function getThisMonthStamps(Child $child): Collection
    {
        return $child->stamps()
            ->with(['pokemon', 'stampType'])
            ->thisMonth()
            ->orderBy('stamped_at', 'desc')
            ->get();
    }

    /**
     * 指定期間のスタンプを取得
     *
     * @param Child $child 対象の子ども
     * @param Carbon $startDate 開始日
     * @param Carbon $endDate 終了日
     * @return \Illuminate\Database\Eloquent\Collection<int, Stamp>
     */
    public function getStampsBetweenDates(Child $child, Carbon $startDate, Carbon $endDate): Collection
    {
        return $child->stamps()
            ->with(['pokemon', 'stampType'])
            ->betweenDates($startDate, $endDate)
            ->orderBy('stamped_at', 'desc')
            ->get();
    }

    /**
     * スタンプを削除する
     *
     * @param Stamp $stamp 削除対象のスタンプ
     * @return bool 削除成功可否
     */
    public function deleteStamp(Stamp $stamp): bool
    {
        return $this->sqliteTransaction(function () use ($stamp) {
            return $stamp->delete();
        });
    }

    /**
     * スタンプをIDで取得
     *
     * @param int $id スタンプID
     * @return Stamp|null スタンプ（見つからない場合null）
     */
    public function findStamp(int $id): ?Stamp
    {
        return Stamp::with(['pokemon', 'stampType', 'child'])->find($id);
    }

    /**
     * 子どものスタンプ統計を取得
     *
     * @param Child $child 対象の子ども
     * @return array<string, mixed> 統計データ
     */
    public function getStampStatistics(Child $child): array
    {
        $totalStamps = $child->stamps()->count();
        $todayStamps = $child->stamps()->today()->count();
        $thisMonthStamps = $child->stamps()->thisMonth()->count();

        // 今月の日別スタンプ数
        $dailyStampsThisMonth = $child->stamps()
            ->thisMonth()
            ->selectRaw('DATE(stamped_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // スタンプ種類別の集計
        $stampTypeStats = $child->stamps()
            ->with('stampType')
            ->get()
            ->groupBy('stamp_type_id')
            ->map(function ($stamps) {
                $firstStamp = $stamps->first();
                return [
                    'stamp_type' => [
                        'id' => $firstStamp->stampType->id,
                        'name' => $firstStamp->stampType->name,
                        'icon' => $firstStamp->stampType->icon,
                        'color' => $firstStamp->stampType->color,
                    ],
                    'count' => $stamps->count(),
                ];
            })
            ->values()
            ->toArray();

        return [
            'total_stamps' => $totalStamps,
            'today_stamps' => $todayStamps,
            'this_month_stamps' => $thisMonthStamps,
            'daily_stamps_this_month' => $dailyStampsThisMonth,
            'stamp_type_statistics' => $stampTypeStats,
        ];
    }

    /**
     * スタンプを開封する
     *
     * @param Stamp $stamp 開封対象のスタンプ
     * @return Stamp 開封されたスタンプ
     * @throws \Exception データベースエラー時
     */
    public function openStamp(Stamp $stamp): Stamp
    {
        return $this->sqliteTransaction(function () use ($stamp) {
            if ($stamp->opened_at === null) {
                $stamp->update(['opened_at' => now()]);
            }
            
            return $stamp->fresh(['pokemon', 'stampType']);
        });
    }

    /**
     * 子どもの未開封スタンプ数を取得
     *
     * @param Child $child 対象の子ども
     * @return int 未開封スタンプ数
     */
    public function getUnopenedStampsCount(Child $child): int
    {
        return $child->stamps()
            ->whereNull('opened_at')
            ->count();
    }

    /**
     * スタンプが開封済みかどうか確認
     *
     * @param Stamp $stamp 確認対象のスタンプ
     * @return bool 開封済みならtrue
     */
    public function isStampOpened(Stamp $stamp): bool
    {
        return $stamp->opened_at !== null;
    }

}