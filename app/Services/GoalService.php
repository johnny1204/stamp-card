<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\Child;
use App\Models\StampType;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * 目標管理サービス
 *
 * 子どもの目標設定、進捗管理、達成チェック等を担当
 */
class GoalService
{
    /**
     * 子どもの目標一覧を取得
     *
     * @param int $childId 子どもID
     * @param array<string, mixed> $filters フィルター条件
     * @return \Illuminate\Support\Collection
     */
    public function getGoalsByChild(int $childId, array $filters = []): Collection
    {
        $query = Goal::where('child_id', $childId)
                    ->with(['stampType']);

        // フィルター適用
        if (isset($filters['period_type'])) {
            $query->byPeriodType($filters['period_type']);
        }

        if (isset($filters['is_achieved'])) {
            if ($filters['is_achieved']) {
                $query->achieved();
            } else {
                $query->where('is_achieved', false);
            }
        }

        if (isset($filters['active_only']) && $filters['active_only']) {
            $query->active();
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * 現在アクティブな目標を取得
     *
     * @param int $childId 子どもID
     * @return \Illuminate\Support\Collection
     */
    public function getActiveGoals(int $childId): Collection
    {
        return Goal::where('child_id', $childId)
                  ->active()
                  ->with(['stampType'])
                  ->orderBy('end_date', 'asc')
                  ->get();
    }

    /**
     * 新しい目標を作成
     *
     * @param array<string, mixed> $data 目標データ
     * @return Goal 作成された目標
     * @throws \Exception データ検証エラー時
     */
    public function createGoal(array $data): Goal
    {
        // データ検証
        $this->validateGoalData($data);

        // 期間の重複チェック
        $this->checkPeriodOverlap($data['child_id'], $data['stamp_type_id'], $data['start_date'], $data['end_date']);

        // 期間タイプに応じた日付調整
        $adjustedDates = $this->adjustDatesByPeriodType($data['period_type'], $data['start_date'], $data['end_date']);

        $goalData = array_merge($data, $adjustedDates);

        return Goal::create($goalData);
    }

    /**
     * 目標を更新
     *
     * @param int $goalId 目標ID
     * @param array<string, mixed> $data 更新データ
     * @return Goal 更新された目標
     * @throws \Exception 目標が見つからない、またはデータ検証エラー時
     */
    public function updateGoal(int $goalId, array $data): Goal
    {
        $goal = Goal::findOrFail($goalId);

        // 達成済みの目標は更新不可
        if ($goal->is_achieved) {
            throw new \Exception('達成済みの目標は更新できません。');
        }

        // データ検証
        $this->validateGoalData($data, $goalId);

        // 期間が変更される場合の重複チェック
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $startDate = $data['start_date'] ?? $goal->start_date;
            $endDate = $data['end_date'] ?? $goal->end_date;
            $this->checkPeriodOverlap($goal->child_id, $goal->stamp_type_id, $startDate, $endDate, $goalId);
        }

        $goal->update($data);
        $goal->refresh();

        return $goal;
    }

    /**
     * 目標を削除
     *
     * @param int $goalId 目標ID
     * @return bool 削除成功かどうか
     * @throws \Exception 目標が見つからない時
     */
    public function deleteGoal(int $goalId): bool
    {
        $goal = Goal::findOrFail($goalId);
        
        return $goal->delete();
    }

    /**
     * 目標達成をチェックして更新
     *
     * @param int $childId 子どもID
     * @param int|null $stampTypeId 特定のスタンプタイプのみチェック
     * @return array<Goal> 新たに達成された目標のリスト
     */
    public function checkGoalAchievements(int $childId, ?int $stampTypeId = null): array
    {
        $query = Goal::where('child_id', $childId)->active();

        if ($stampTypeId) {
            $query->where('stamp_type_id', $stampTypeId);
        }

        $activeGoals = $query->get();
        $newlyAchieved = [];

        foreach ($activeGoals as $goal) {
            if ($goal->checkAndUpdateAchievement()) {
                $newlyAchieved[] = $goal;
            }
        }

        return $newlyAchieved;
    }

    /**
     * 週間目標を自動作成
     *
     * @param int $childId 子どもID
     * @param int $stampTypeId スタンプタイプID
     * @param int $targetCount 目標回数
     * @return Goal 作成された目標
     */
    public function createWeeklyGoal(int $childId, int $stampTypeId, int $targetCount): Goal
    {
        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        return $this->createGoal([
            'child_id' => $childId,
            'stamp_type_id' => $stampTypeId,
            'target_count' => $targetCount,
            'period_type' => 'weekly',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'reward_text' => '今週の目標達成おめでとう！',
        ]);
    }

    /**
     * 月間目標を自動作成
     *
     * @param int $childId 子どもID
     * @param int $stampTypeId スタンプタイプID
     * @param int $targetCount 目標回数
     * @return Goal 作成された目標
     */
    public function createMonthlyGoal(int $childId, int $stampTypeId, int $targetCount): Goal
    {
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();

        return $this->createGoal([
            'child_id' => $childId,
            'stamp_type_id' => $stampTypeId,
            'target_count' => $targetCount,
            'period_type' => 'monthly',
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'reward_text' => '今月の目標達成おめでとう！',
        ]);
    }

    /**
     * 目標の進捗サマリを取得
     *
     * @param int $childId 子どもID
     * @return array<string, mixed> 進捗サマリ
     */
    public function getProgressSummary(int $childId): array
    {
        $activeGoals = $this->getActiveGoals($childId);
        $achievedGoals = Goal::where('child_id', $childId)->achieved()->count();

        $totalProgress = 0;
        $urgentGoals = [];

        foreach ($activeGoals as $goal) {
            $totalProgress += $goal->progress_percentage;
            
            // 残り3日以内かつ進捗50%未満の目標を緊急とする
            if ($goal->remaining_days <= 3 && $goal->progress_percentage < 50) {
                $urgentGoals[] = $goal;
            }
        }

        $averageProgress = $activeGoals->count() > 0 ? $totalProgress / $activeGoals->count() : 0;

        return [
            'active_goals_count' => $activeGoals->count(),
            'achieved_goals_count' => $achievedGoals,
            'average_progress' => round($averageProgress, 1),
            'urgent_goals' => $urgentGoals,
            'active_goals' => $activeGoals,
        ];
    }

    /**
     * 目標データの検証
     *
     * @param array<string, mixed> $data 検証するデータ
     * @param int|null $excludeGoalId 除外する目標ID（更新時）
     * @throws \Exception 検証エラー時
     */
    private function validateGoalData(array $data, ?int $excludeGoalId = null): void
    {
        // 必須フィールドのチェック
        $requiredFields = ['child_id', 'stamp_type_id', 'target_count', 'period_type', 'start_date', 'end_date'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("必須項目 '{$field}' が不足しています。");
            }
        }

        // 子どもの存在チェック
        if (!Child::find($data['child_id'])) {
            throw new \Exception('指定された子どもが見つかりません。');
        }

        // スタンプタイプの存在チェック
        if (!StampType::on('mysql')->find($data['stamp_type_id'])) {
            throw new \Exception('指定されたスタンプタイプが見つかりません。');
        }

        // 目標回数の検証
        if ($data['target_count'] <= 0) {
            throw new \Exception('目標回数は1以上である必要があります。');
        }

        // 期間タイプの検証
        if (!in_array($data['period_type'], ['weekly', 'monthly'])) {
            throw new \Exception('期間タイプは weekly または monthly である必要があります。');
        }

        // 日付の検証
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate <= $startDate) {
            throw new \Exception('終了日は開始日より後である必要があります。');
        }
    }

    /**
     * 期間の重複チェック
     *
     * @param int $childId 子どもID
     * @param int $stampTypeId スタンプタイプID
     * @param string $startDate 開始日
     * @param string $endDate 終了日
     * @param int|null $excludeGoalId 除外する目標ID
     * @throws \Exception 重複する期間がある時
     */
    private function checkPeriodOverlap(int $childId, int $stampTypeId, string $startDate, string $endDate, ?int $excludeGoalId = null): void
    {
        $query = Goal::where('child_id', $childId)
                     ->where('stamp_type_id', $stampTypeId)
                     ->where(function (Builder $query) use ($startDate, $endDate) {
                         $query->whereBetween('start_date', [$startDate, $endDate])
                               ->orWhereBetween('end_date', [$startDate, $endDate])
                               ->orWhere(function (Builder $subQuery) use ($startDate, $endDate) {
                                   $subQuery->where('start_date', '<=', $startDate)
                                           ->where('end_date', '>=', $endDate);
                               });
                     });

        if ($excludeGoalId) {
            $query->where('id', '!=', $excludeGoalId);
        }

        if ($query->exists()) {
            throw new \Exception('指定された期間に既に目標が設定されています。');
        }
    }

    /**
     * 期間タイプに応じた日付調整
     *
     * @param string $periodType 期間タイプ
     * @param string $startDate 開始日
     * @param string $endDate 終了日
     * @return array<string, string> 調整された日付
     */
    private function adjustDatesByPeriodType(string $periodType, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        if ($periodType === 'weekly') {
            // 週間目標は月曜日から日曜日に調整
            $start = $start->startOfWeek();
            $end = $start->copy()->endOfWeek();
        } elseif ($periodType === 'monthly') {
            // 月間目標は月初から月末に調整
            $start = $start->startOfMonth();
            $end = $start->copy()->endOfMonth();
        }

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }
}