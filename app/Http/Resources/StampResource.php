<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * スタンプリソース
 * 
 * APIレスポンス用のスタンプデータフォーマット
 */
class StampResource extends JsonResource
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
            'child_id' => $this->child_id,
            'stamp_type_id' => $this->stamp_type_id,
            'pokemon_id' => $this->pokemon_id,
            'stamped_at' => $this->stamped_at?->toISOString(),
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            
            // リレーション情報
            'child' => ChildResource::make($this->whenLoaded('child')),
            'stamp_type' => $this->whenLoaded('stampType', function () {
                return [
                    'id' => $this->stampType->id,
                    'name' => $this->stampType->name,
                    'icon' => $this->stampType->icon,
                    'color' => $this->stampType->color,
                    'category' => $this->stampType->category,
                ];
            }),
            'pokemon' => $this->whenLoaded('pokemon', function () {
                return [
                    'id' => $this->pokemon->id,
                    'name' => $this->pokemon->name,
                    'image_path' => $this->pokemon->image_path,
                    'rarity' => $this->pokemon->rarity,
                ];
            }),
        ];
    }
}
