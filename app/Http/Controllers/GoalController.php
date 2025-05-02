<?php

namespace App\Http\Controllers;

use App\Http\Requests\GoalCreateRequest;
use App\Http\Requests\GoalUpdateRequest;
use App\Services\GoalService;
use App\Services\ChildService;
use App\Models\Child;
use App\Models\StampType;
use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 目標管理コントローラー
 *
 * 目標の作成、編集、削除、表示を担当
 */
class GoalController extends Controller
{
    /**
     * @param GoalService $goalService
     * @param ChildService $childService
     */
    public function __construct(
        private readonly GoalService $goalService,
        private readonly ChildService $childService
    ) {}

    /**
     * 子どもの目標一覧を表示
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return Response
     */
    public function index(Request $request, int $childId): Response
    {
        $child = Child::findOrFail($childId);

        // フィルター条件を取得
        $filters = [
            'period_type' => $request->get('period_type'),
            'is_achieved' => $request->boolean('achieved'),
            'active_only' => $request->boolean('active_only', false),
        ];

        $goals = $this->goalService->getGoalsByChild($childId, array_filter($filters));
        $progressSummary = $this->goalService->getProgressSummary($childId);
        $children = $this->childService->getAllChildren();

        return Inertia::render('Goals/Index', [
            'child' => $child,
            'children' => $children,
            'goals' => $goals,
            'progressSummary' => $progressSummary,
            'filters' => $filters,
        ]);
    }

    /**
     * 目標作成フォームを表示
     *
     * @param int $childId 子どもID
     * @return Response
     */
    public function create(int $childId): Response
    {
        $child = Child::findOrFail($childId);
        
        // TODO: 認証機能実装後は現在のログインユーザーの家族IDを取得
        $familyId = 1; // 仮の家族ID
        
        // 家族のスタンプ種類（システムデフォルト + 家族専用）を取得
        $stampTypes = StampType::on('mysql')
            ->where(function ($query) use ($familyId) {
                $query->where('family_id', $familyId)
                      ->orWhere('is_system_default', true);
            })
            ->orderBy('is_system_default', 'desc')
            ->orderBy('name')
            ->get();

        $children = $this->childService->getAllChildren();

        return Inertia::render('Goals/Create', [
            'child' => $child,
            'children' => $children,
            'stampTypes' => $stampTypes,
        ]);
    }

    /**
     * 新しい目標を作成
     *
     * @param GoalCreateRequest $request
     * @param int $childId 子どもID
     * @return RedirectResponse
     */
    public function store(GoalCreateRequest $request, int $childId): RedirectResponse
    {
        try {
            // デバッグ用: 送信されたデータをログに出力
            \Log::info('Goal creation data:', $request->all());
            
            $data = array_merge($request->validated(), ['child_id' => $childId]);
            $goal = $this->goalService->createGoal($data);

            return redirect()
                ->route('children.goals.index', ['child' => $childId])
                ->with('success', '目標を作成しました。');
        } catch (\Exception $e) {
            \Log::error('Goal creation failed:', ['error' => $e->getMessage(), 'data' => $request->all()]);
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '目標の作成に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 目標の詳細を表示
     *
     * @param int $childId 子どもID
     * @param int $goalId 目標ID
     * @return Response
     */
    public function show(int $childId, int $goalId): Response
    {
        $child = Child::findOrFail($childId);
        $goal = Goal::with(['stampType'])
                   ->where('child_id', $childId)
                   ->findOrFail($goalId);

        $children = $this->childService->getAllChildren();

        return Inertia::render('Goals/Show', [
            'child' => $child,
            'children' => $children,
            'goal' => $goal,
        ]);
    }

    /**
     * 目標編集フォームを表示
     *
     * @param int $childId 子どもID
     * @param int $goalId 目標ID
     * @return Response
     */
    public function edit(int $childId, int $goalId): Response
    {
        $child = Child::findOrFail($childId);
        $goal = Goal::with(['stampType'])
                   ->where('child_id', $childId)
                   ->findOrFail($goalId);

        // 達成済みの目標は編集不可
        if ($goal->is_achieved) {
            return redirect()
                ->route('goals.show', ['child' => $childId, 'goal' => $goalId])
                ->with('error', '達成済みの目標は編集できません。');
        }

        // TODO: 認証機能実装後は現在のログインユーザーの家族IDを取得
        $familyId = 1; // 仮の家族ID
        
        // 家族のスタンプ種類（システムデフォルト + 家族専用）を取得
        $stampTypes = StampType::on('mysql')
            ->where(function ($query) use ($familyId) {
                $query->where('family_id', $familyId)
                      ->orWhere('is_system_default', true);
            })
            ->orderBy('is_system_default', 'desc')
            ->orderBy('name')
            ->get();

        $children = $this->childService->getAllChildren();

        return Inertia::render('Goals/Edit', [
            'child' => $child,
            'children' => $children,
            'goal' => $goal,
            'stampTypes' => $stampTypes,
        ]);
    }

    /**
     * 目標を更新
     *
     * @param GoalUpdateRequest $request
     * @param int $childId 子どもID
     * @param int $goalId 目標ID
     * @return RedirectResponse
     */
    public function update(GoalUpdateRequest $request, int $childId, int $goalId): RedirectResponse
    {
        try {
            $goal = $this->goalService->updateGoal($goalId, $request->validated());

            return redirect()
                ->route('goals.show', ['child' => $childId, 'goal' => $goalId])
                ->with('success', '目標を更新しました。');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', '目標の更新に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 目標を削除
     *
     * @param int $childId 子どもID
     * @param int $goalId 目標ID
     * @return RedirectResponse
     */
    public function destroy(int $childId, int $goalId): RedirectResponse
    {
        try {
            $this->goalService->deleteGoal($goalId);

            return redirect()
                ->route('goals.index', ['child' => $childId])
                ->with('success', '目標を削除しました。');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', '目標の削除に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 週間目標を自動作成
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return RedirectResponse
     */
    public function createWeeklyGoal(Request $request, int $childId): RedirectResponse
    {
        $request->validate([
            'stamp_type_id' => 'required|exists:stamp_types,id',
            'target_count' => 'required|integer|min:1',
        ]);

        try {
            $goal = $this->goalService->createWeeklyGoal(
                $childId,
                $request->input('stamp_type_id'),
                $request->input('target_count')
            );

            return redirect()
                ->route('goals.show', ['child' => $childId, 'goal' => $goal->id])
                ->with('success', '今週の目標を作成しました。');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', '週間目標の作成に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 月間目標を自動作成
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return RedirectResponse
     */
    public function createMonthlyGoal(Request $request, int $childId): RedirectResponse
    {
        $request->validate([
            'stamp_type_id' => 'required|exists:stamp_types,id',
            'target_count' => 'required|integer|min:1',
        ]);

        try {
            $goal = $this->goalService->createMonthlyGoal(
                $childId,
                $request->input('stamp_type_id'),
                $request->input('target_count')
            );

            return redirect()
                ->route('goals.show', ['child' => $childId, 'goal' => $goal->id])
                ->with('success', '今月の目標を作成しました。');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', '月間目標の作成に失敗しました: ' . $e->getMessage());
        }
    }

    /**
     * 目標達成をチェック（AJAX）
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAchievements(Request $request, int $childId): \Illuminate\Http\JsonResponse
    {
        try {
            $stampTypeId = $request->input('stamp_type_id');
            $newlyAchieved = $this->goalService->checkGoalAchievements($childId, $stampTypeId);

            return response()->json([
                'success' => true,
                'newly_achieved' => $newlyAchieved,
                'count' => count($newlyAchieved),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
