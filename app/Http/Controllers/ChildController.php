<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChildRequest;
use App\Http\Resources\ChildResource;
use App\Models\Child;
use App\Services\ChildService;
use App\Services\ChildUrlService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * 子ども管理コントローラー
 */
class ChildController extends Controller
{
    public function __construct(
        private ChildService $childService,
        private ChildUrlService $childUrlService
    ) {}

    /**
     * 子ども一覧を表示
     *
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        $children = $this->childService->getAllChildren();

        return Inertia::render('Children/Index', [
            'children' => ChildResource::collection($children),
        ]);
    }

    /**
     * 子ども登録画面を表示
     *
     * @return \Inertia\Response
     */
    public function create(): Response
    {
        return Inertia::render('Children/Create');
    }

    /**
     * 子ども登録処理
     *
     * @param StoreChildRequest $request
     * @return RedirectResponse
     */
    public function store(StoreChildRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $child = $this->childService->createChild($data);

        return redirect()->route('children.index')
            ->with('success', "{$child->name}を登録しました");
    }

    /**
     * 子ども詳細画面を表示
     *
     * @param Child $child
     * @return \Inertia\Response
     */
    public function show(Child $child): Response
    {
        $todayStampsCount = $this->childService->getTodayStampsCount($child);
        $thisMonthStampsCount = $this->childService->getThisMonthStampsCount($child);
        $childUrls = $this->childUrlService->generateAllUrls($child);
        $children = $this->childService->getAllChildren();

        return Inertia::render('Children/Show', [
            'child' => ChildResource::make($child),
            'children' => $children,
            'todayStampsCount' => $todayStampsCount,
            'thisMonthStampsCount' => $thisMonthStampsCount,
            'childUrls' => $childUrls,
        ]);
    }

    /**
     * 子ども編集画面を表示
     *
     * @param Child $child
     * @return \Inertia\Response
     */
    public function edit(Child $child): Response
    {
        return Inertia::render('Children/Edit', [
            'child' => ChildResource::make($child),
        ]);
    }

    /**
     * 子ども更新処理
     *
     * @param StoreChildRequest $request
     * @param Child $child
     * @return RedirectResponse
     */
    public function update(StoreChildRequest $request, Child $child): RedirectResponse
    {
        $data = $request->validated();
        $updatedChild = $this->childService->updateChild($child, $data);

        return redirect()->route('children.show', $updatedChild)
            ->with('success', "{$updatedChild->name}の情報を更新しました");
    }

    /**
     * 子ども削除処理
     *
     * @param Child $child
     * @return RedirectResponse
     */
    public function destroy(Child $child): RedirectResponse
    {
        $name = $child->name;
        $this->childService->deleteChild($child);

        return redirect()->route('children.index')
            ->with('success', "{$name}を削除しました");
    }
}
