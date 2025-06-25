<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStampRequest;
use App\Http\Resources\StampResource;
use App\Models\Child;
use App\Models\StampType;
use App\Services\StampService;
use App\Services\PokemonSelectionService;
use App\Services\ChildService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * スタンプ管理コントローラー
 */
class StampController extends Controller
{
    public function __construct(
        private StampService $stampService,
        private PokemonSelectionService $pokemonSelectionService,
        private ChildService $childService
    ) {}

    /**
     * 子どものスタンプ一覧を表示
     *
     * @param Child $child
     * @return Response
     */
    public function index(Child $child): Response
    {
        $stamps = $this->stampService->getChildStamps($child);
        $children = $this->childService->getAllChildren();

        return Inertia::render('Stamps/Index', [
            'child' => $child,
            'children' => $children,
            'stamps' => StampResource::collection($stamps),
        ]);
    }

    /**
     * スタンプ作成画面を表示
     *
     * @param Child $child
     * @return Response
     */
    public function create(Child $child): Response
    {
        $children = $this->childService->getAllChildren();
        $stampTypes = StampType::orderBy('name')->get(['id', 'name', 'icon', 'color', 'category']);

        return Inertia::render('Stamps/Create', [
            'child' => $child,
            'children' => $children,
            'stampTypes' => $stampTypes,
        ]);
    }

    /**
     * スタンプを作成する
     *
     * @param CreateStampRequest $request
     * @param Child $child
     * @return RedirectResponse
     */
    public function store(CreateStampRequest $request, Child $child): RedirectResponse
    {
        $data = $request->validated();
        $result = $this->stampService->createStamp($child, $data);
        $stamp = $result['stamp'];
        $cardInfo = $result['card_info'];

        $flashData = [
            'pokemon' => $stamp->pokemon ? [
                'id' => $stamp->pokemon->id,
                'name' => $stamp->pokemon->name,
                'type1' => $stamp->pokemon->type1,
                'type2' => $stamp->pokemon->type2,
                'genus' => $stamp->pokemon->genus,
                'is_legendary' => $stamp->pokemon->is_legendary,
                'is_mythical' => $stamp->pokemon->is_mythical,
                'image_url' => $this->pokemonSelectionService->getPokemonImageUrl($stamp->pokemon->id),
                'cry_url' => $this->pokemonSelectionService->getPokemonCryUrl($stamp->pokemon->id),
            ] : null,
            'child' => [
                'name' => $child->name,
            ],
            'card_info' => $cardInfo,
        ];

        $successMessage = 'スタンプを付けました！';
        if ($cardInfo['card_completed'] && isset($cardInfo['completed_card_number'])) {
            $successMessage .= " スタンプカード#{$cardInfo['completed_card_number']}が完成しました！🎉";
        }

        return redirect()->route('children.stamp-cards.index', $child)
            ->with('success', $successMessage)
            ->with('newStamp', $flashData);
    }

    /**
     * スタンプ詳細を表示
     *
     * @param Child $child
     * @param int $stampId
     * @return Response
     */
    public function show(Child $child, int $stampId): Response
    {
        $stamp = $this->stampService->findStamp($stampId);
        
        if (!$stamp || $stamp->child_id !== $child->id) {
            abort(404);
        }

        $children = $this->childService->getAllChildren();

        return Inertia::render('Stamps/Show', [
            'child' => $child,
            'children' => $children,
            'stamp' => StampResource::make($stamp),
        ]);
    }
}