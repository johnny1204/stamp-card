<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 子どもリソース
 * 
 * APIレスポンス用の子どもデータフォーマット
 */
class ChildResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'age' => $this->age,
            'age_group' => $this->age_group,
            'target_stamps' => $this->target_stamps,
            'avatar_path' => $this->avatar_path,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // 統計情報（必要に応じて）
            'todayStampsCount' => $this->today_stamps_count ?? 0,
            'thisMonthStampsCount' => $this->this_month_stamps_count ?? 0,
            'totalStampsCount' => $this->total_stamps_count ?? 0,
            
            // リレーション情報
            'recent_stamps' => StampResource::collection($this->whenLoaded('stamps')),
        ];
    }
}
