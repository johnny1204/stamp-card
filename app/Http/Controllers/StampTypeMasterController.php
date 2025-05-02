<?php

namespace App\Http\Controllers;

use App\Services\StampTypeMasterService;
use App\Models\Family;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * スタンプ種類マスタ管理コントローラー
 *
 * 家族ごとのスタンプ種類マスタのCRUD操作を担当
 */
class StampTypeMasterController extends Controller
{
    /**
     * @param StampTypeMasterService $stampTypeMasterService
     */
    public function __construct(
        private readonly StampTypeMasterService $stampTypeMasterService
    ) {}

    /**
     * スタンプ種類マスタ管理画面を表示
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // TODO: 認証機能実装後は現在のログインユーザーの家族IDを取得
        $familyId = 1; // 仮の家族ID

        $systemStampTypes = $this->stampTypeMasterService->getSystemDefaultStampTypes();
        $customStampTypes = $this->stampTypeMasterService->getCustomStampTypesForFamily($familyId);
        $categories = $this->stampTypeMasterService->getAvailableCategories();
        $colors = $this->stampTypeMasterService->getAvailableColors();

        return Inertia::render('Master/StampTypes/Index', [
            'systemStampTypes' => $systemStampTypes,
            'customStampTypes' => $customStampTypes,
            'categories' => $categories,
            'colors' => $colors,
            'familyId' => $familyId,
        ]);
    }

    /**
     * 新規スタンプ種類作成画面を表示
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        $categories = $this->stampTypeMasterService->getAvailableCategories();
        $colors = $this->stampTypeMasterService->getAvailableColors();

        return Inertia::render('Master/StampTypes/Create', [
            'categories' => $categories,
            'colors' => $colors,
        ]);
    }

    /**
     * 新規スタンプ種類を作成
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'category' => 'required|in:help,lifestyle,behavior,custom',
        ]);

        try {
            // TODO: 認証機能実装後は現在のログインユーザーの家族IDを取得
            $familyId = 1; // 仮の家族ID

            $stampType = $this->stampTypeMasterService->createCustomStampType($familyId, $validated);

            return redirect()->route('master.stamp-types.index')
                ->with('success', "スタンプ種類「{$stampType->name}」を作成しました");

        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * スタンプ種類編集画面を表示
     *
     * @param Request $request
     * @param int $id スタンプ種類ID
     * @return Response
     */
    public function edit(Request $request, int $id): Response
    {
        try {
            // TODO: 認証機能実装後は現在のログインユーザーの家族IDを取得
            $familyId = 1; // 仮の家族ID

            $stampType = $this->stampTypeMasterService->findCustomStampTypeForFamily($familyId, $id);
            $categories = $this->stampTypeMasterService->getAvailableCategories();
            $colors = $this->stampTypeMasterService->getAvailableColors();

            return Inertia::render('Master/StampTypes/Edit', [
                'stampType' => $stampType,
                'categories' => $categories,
                'colors' => $colors,
            ]);

        } catch (\Exception $e) {
            return redirect()->route('master.stamp-types.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * スタンプ種類を更新
     *
     * @param Request $request
     * @param int $id スタンプ種類ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'icon' => 'nullable|string|max:10',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'category' => 'required|in:help,lifestyle,behavior,custom',
        ]);

        try {
            // TODO: 認証機能実装後は現在のログインユーザーの家族IDを取得
            $familyId = 1; // 仮の家族ID

            $stampType = $this->stampTypeMasterService->updateCustomStampType($familyId, $id, $validated);

            return redirect()->route('master.stamp-types.index')
                ->with('success', "スタンプ種類「{$stampType->name}」を更新しました");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * スタンプ種類を削除
     *
     * @param Request $request
     * @param int $id スタンプ種類ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request, int $id)
    {
        try {
            // TODO: 認証機能実装後は現在のログインユーザーの家族IDを取得
            $familyId = 1; // 仮の家族ID

            $this->stampTypeMasterService->deleteCustomStampType($familyId, $id);

            return redirect()->route('master.stamp-types.index')
                ->with('success', 'スタンプ種類を削除しました');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
