<?php

namespace App\Services;

use App\Models\Child;
use App\Models\StampCard;
use App\Models\Stamp;

/**
 * スタンプカード管理サービス
 */
class StampCardService extends BaseService
{
    /**
     * 子どものスタンプカード一覧を取得
     */
    public function getChildStampCards(Child $child): array
    {
        $cards = StampCard::where('child_id', $child->id)
            ->with(['stamps.pokemon'])
            ->orderBy('card_number')
            ->get();

        return $cards->map(function ($card) {
            return [
                'card_number' => $card->card_number,
                'is_completed' => $card->is_completed,
                'stamps' => $card->stamps->map(function ($stamp) {
                    return [
                        'id' => $stamp->id,
                        'stamped_at' => $stamp->stamped_at,
                        'opened_at' => $stamp->opened_at,
                        'comment' => $stamp->comment,
                        'pokemon' => [
                            'id' => $stamp->pokemon->id,
                            'name' => $stamp->pokemon->name,
                            'type1' => $stamp->pokemon->type1,
                            'type2' => $stamp->pokemon->type2,
                            'genus' => $stamp->pokemon->genus,
                            'is_legendary' => $stamp->pokemon->is_legendary,
                            'is_mythical' => $stamp->pokemon->is_mythical,
                        ],
                    ];
                })->toArray(),
                'progress' => $card->progress,
                'target' => $card->target_stamps,
                'completed_at' => $card->completed_at,
            ];
        })->toArray();
    }

    /**
     * 子どもの現在のアクティブカードを取得（未完成の最新カード）
     * カードが存在しない場合のみ作成
     */
    public function getCurrentCard(Child $child): ?StampCard
    {
        return StampCard::where('child_id', $child->id)
            ->where('is_completed', false)
            ->orderBy('card_number', 'desc')
            ->first();
    }

    /**
     * 子どもの現在のアクティブカードを取得または作成
     */
    public function getOrCreateCurrentCard(Child $child): StampCard
    {
        $currentCard = $this->getCurrentCard($child);

        if (!$currentCard) {
            // 初回カード作成
            $currentCard = $this->createNewCard($child, 1);
        }

        return $currentCard;
    }

    /**
     * 新しいスタンプカードを作成
     */
    public function createNewCard(Child $child, ?int $cardNumber = null): StampCard
    {
        if (!$cardNumber) {
            $lastCard = StampCard::where('child_id', $child->id)
                ->orderBy('card_number', 'desc')
                ->first();
            $cardNumber = $lastCard ? $lastCard->card_number + 1 : 1;
        }

        return StampCard::create([
            'child_id' => $child->id,
            'card_number' => $cardNumber,
            'target_stamps' => $child->target_stamps,
        ]);
    }

    /**
     * スタンプをカードに追加（目標達成時に自動でカード作成）
     */
    public function addStampToCard(Stamp $stamp): array
    {
        $child = $stamp->child;
        $currentCard = $this->getOrCreateCurrentCard($child);

        // スタンプをカードに関連付け
        $stamp->update(['stamp_card_id' => $currentCard->id]);

        \Log::info("Added stamp to card", [
            'stamp_id' => $stamp->id,
            'card_id' => $currentCard->id,
            'card_number' => $currentCard->card_number,
            'progress_before' => $currentCard->progress,
            'target' => $currentCard->target_stamps,
        ]);

        // カード完成チェック（目標数に達した場合）
        $cardCompleted = false;
        $newCard = null;

        // progressは動的に計算されるため、リロードして最新状態を取得
        $currentCard->refresh();
        
        if ($currentCard->canComplete()) {
            $currentCard->complete();
            $cardCompleted = true;
            
            \Log::info("Card completed, creating new card", [
                'completed_card_id' => $currentCard->id,
                'completed_card_number' => $currentCard->card_number,
                'final_progress' => $currentCard->progress,
            ]);
            
            // 目標達成時に次のカードを自動作成
            $newCard = $this->createNewCard($child);
            
            \Log::info("New card created", [
                'new_card_id' => $newCard->id,
                'new_card_number' => $newCard->card_number,
                'target_stamps' => $newCard->target_stamps,
            ]);
        }

        return [
            'current_card' => $currentCard,
            'card_completed' => $cardCompleted,
            'completed_card_number' => $cardCompleted ? $currentCard->card_number : null,
            'new_card' => $newCard,
            'progress' => $currentCard->progress,
            'target' => $currentCard->target_stamps,
        ];
    }

    /**
     * 子どものスタンプを仮想カードに変換（レガシー用）
     * 実際のStampCardテーブルを使用せずに、スタンプを仮想的にカード形式で表示
     *
     * @param Child $child 対象の子ども
     * @return array<int, array<string, mixed>> 仮想カード配列
     */
    public function getVirtualStampCards(Child $child): array
    {
        $stamps = $child->stamps()->with('pokemon')->orderBy('stamped_at')->get();
        $totalStamps = $stamps->count();
        $targetStamps = $child->target_stamps;
        
        // target_stampsが設定されていない場合のデフォルト値
        if (!$targetStamps || $targetStamps <= 0) {
            $targetStamps = 10;
        }
        
        $cards = [];
        $cardNumber = 1;
        
        // 完成したカードの数
        $completedCards = intval($totalStamps / $targetStamps);
        
        // 完成したカードを生成
        for ($i = 0; $i < $completedCards; $i++) {
            $cardStamps = $stamps->slice($i * $targetStamps, $targetStamps);
            $cards[] = [
                'card_number' => $cardNumber++,
                'is_completed' => true,
                'stamps' => $cardStamps->toArray(),
                'progress' => $targetStamps,
                'target' => $targetStamps,
            ];
        }
        
        // 進行中のカードを生成（常に1つ表示）
        $remainingStamps = $totalStamps % $targetStamps;
        $startIndex = $completedCards * $targetStamps;
        $cardStamps = $stamps->slice($startIndex, $remainingStamps);
        
        $progressCard = [
            'card_number' => $cardNumber,
            'is_completed' => false,
            'stamps' => $cardStamps->toArray(),
            'progress' => $remainingStamps,
            'target' => $targetStamps,
        ];
        
        $cards[] = $progressCard;
        
        return $cards;
    }

    /**
     * 既存データをマイグレーション
     */
    public function migrateExistingData(Child $child): void
    {
        $stamps = $child->stamps()
            ->whereNull('stamp_card_id')
            ->orderBy('stamped_at')
            ->get();

        if ($stamps->isEmpty()) {
            return;
        }

        $targetStamps = $child->target_stamps;
        $cardNumber = 1;
        $currentCard = null;
        $stampCount = 0;

        foreach ($stamps as $stamp) {
            // 新しいカードが必要な場合
            if (!$currentCard || $stampCount >= $targetStamps) {
                if ($currentCard) {
                    $currentCard->complete();
                }
                
                $currentCard = $this->createNewCard($child, $cardNumber++);
                $stampCount = 0;
            }

            // スタンプをカードに関連付け
            $stamp->update(['stamp_card_id' => $currentCard->id]);
            $stampCount++;
        }

        // 最後のカードの完成チェック
        if ($currentCard && $currentCard->canComplete()) {
            $currentCard->complete();
            // 新しい進行中カードを作成
            $this->createNewCard($child);
        }
    }
}