<?php

namespace App\Services;

use App\Models\StampType;
use App\Models\Family;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * スタンプ種類マスタ管理サービス
 *
 * 家族ごとのスタンプ種類マスタの管理を担当
 */
class StampTypeMasterService
{
    /**
     * 指定した家族のスタンプ種類一覧を取得
     *
     * @param int $familyId 家族ID
     * @return Collection スタンプ種類一覧（システムデフォルト + 家族専用）
     */
    public function getStampTypesForFamily(int $familyId): Collection
    {
        return StampType::on('mysql')
            ->where(function ($query) use ($familyId) {
                $query->where('family_id', $familyId)
                      ->orWhere('is_system_default', true);
            })
            ->orderBy('is_system_default', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * 家族専用のカスタムスタンプ種類のみを取得
     *
     * @param int $familyId 家族ID
     * @return Collection 家族専用スタンプ種類一覧
     */
    public function getCustomStampTypesForFamily(int $familyId): Collection
    {
        return StampType::on('mysql')
            ->where('family_id', $familyId)
            ->orderBy('name')
            ->get();
    }

    /**
     * システムデフォルトのスタンプ種類一覧を取得
     *
     * @return Collection システムデフォルトスタンプ種類一覧
     */
    public function getSystemDefaultStampTypes(): Collection
    {
        return StampType::on('mysql')
            ->where('is_system_default', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * 新しい家族専用スタンプ種類を作成
     *
     * @param int $familyId 家族ID
     * @param array<string, mixed> $data スタンプ種類データ
     * @return StampType 作成されたスタンプ種類
     * @throws \InvalidArgumentException 不正なデータの場合
     */
    public function createCustomStampType(int $familyId, array $data): StampType
    {
        $this->validateStampTypeData($data);
        
        // 同じ家族内での名前重複チェック
        $this->checkDuplicateName($familyId, $data['name']);

        $stampTypeData = array_merge($data, [
            'family_id' => $familyId,
            'is_custom' => true,
            'is_system_default' => false,
        ]);

        return StampType::on('mysql')->create($stampTypeData);
    }

    /**
     * 家族専用スタンプ種類を更新
     *
     * @param int $familyId 家族ID
     * @param int $stampTypeId スタンプ種類ID
     * @param array<string, mixed> $data 更新データ
     * @return StampType 更新されたスタンプ種類
     * @throws ModelNotFoundException スタンプ種類が見つからない場合
     * @throws \InvalidArgumentException 不正なデータまたは権限がない場合
     */
    public function updateCustomStampType(int $familyId, int $stampTypeId, array $data): StampType
    {
        $stampType = $this->findCustomStampTypeForFamily($familyId, $stampTypeId);
        
        $this->validateStampTypeData($data);
        
        // 名前を変更する場合は重複チェック
        if (isset($data['name']) && $data['name'] !== $stampType->name) {
            $this->checkDuplicateName($familyId, $data['name'], $stampTypeId);
        }

        $stampType->update($data);
        
        return $stampType->fresh();
    }

    /**
     * 家族専用スタンプ種類を削除
     *
     * @param int $familyId 家族ID
     * @param int $stampTypeId スタンプ種類ID
     * @return bool 削除成功フラグ
     * @throws ModelNotFoundException スタンプ種類が見つからない場合
     * @throws \InvalidArgumentException 権限がない場合
     */
    public function deleteCustomStampType(int $familyId, int $stampTypeId): bool
    {
        $stampType = $this->findCustomStampTypeForFamily($familyId, $stampTypeId);
        
        return $stampType->delete();
    }

    /**
     * スタンプ種類データのバリデーション
     *
     * @param array<string, mixed> $data 検証データ
     * @throws \InvalidArgumentException 不正なデータの場合
     */
    private function validateStampTypeData(array $data): void
    {
        if (empty($data['name'])) {
            throw new \InvalidArgumentException('スタンプ種類名は必須です');
        }

        if (isset($data['category']) && !in_array($data['category'], ['help', 'lifestyle', 'behavior', 'custom'])) {
            throw new \InvalidArgumentException('不正なカテゴリです');
        }

        if (isset($data['color']) && !preg_match('/^#[0-9A-Fa-f]{6}$/', $data['color'])) {
            throw new \InvalidArgumentException('色の形式が不正です（#RRGGBB形式で入力してください）');
        }
    }

    /**
     * 同じ家族内でのスタンプ種類名重複チェック
     *
     * @param int $familyId 家族ID
     * @param string $name スタンプ種類名
     * @param int|null $excludeId 除外するスタンプ種類ID（更新時）
     * @throws \InvalidArgumentException 名前が重複する場合
     */
    private function checkDuplicateName(int $familyId, string $name, ?int $excludeId = null): void
    {
        $query = StampType::on('mysql')
            ->where(function ($q) use ($familyId) {
                $q->where('family_id', $familyId)
                  ->orWhere('is_system_default', true);
            })
            ->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new \InvalidArgumentException("スタンプ種類名「{$name}」は既に使用されています");
        }
    }

    /**
     * 家族専用スタンプ種類を取得（権限チェック付き）
     *
     * @param int $familyId 家族ID
     * @param int $stampTypeId スタンプ種類ID
     * @return StampType 見つかったスタンプ種類
     * @throws ModelNotFoundException スタンプ種類が見つからない場合
     * @throws \InvalidArgumentException 権限がない場合
     */
    public function findCustomStampTypeForFamily(int $familyId, int $stampTypeId): StampType
    {
        $stampType = StampType::on('mysql')->find($stampTypeId);
        
        if (!$stampType) {
            throw new ModelNotFoundException('指定されたスタンプ種類が見つかりません');
        }

        if ($stampType->family_id !== $familyId) {
            throw new \InvalidArgumentException('このスタンプ種類を編集する権限がありません');
        }

        if ($stampType->is_system_default) {
            throw new \InvalidArgumentException('システムデフォルトのスタンプ種類は編集・削除できません');
        }

        return $stampType;
    }

    /**
     * 指定されたスタンプ種類IDが家族にアクセス可能かチェック
     *
     * @param int $familyId 家族ID
     * @param int $stampTypeId スタンプ種類ID
     * @return bool アクセス可能フラグ
     */
    public function canFamilyAccessStampType(int $familyId, int $stampTypeId): bool
    {
        return StampType::on('mysql')
            ->where('id', $stampTypeId)
            ->where(function ($query) use ($familyId) {
                $query->where('family_id', $familyId)
                      ->orWhere('is_system_default', true);
            })
            ->exists();
    }

    /**
     * 利用可能なカテゴリ一覧を取得
     *
     * @return array<string, string> カテゴリ一覧（key: value, label）
     */
    public function getAvailableCategories(): array
    {
        return [
            'help' => 'お手伝い',
            'lifestyle' => '生活習慣',
            'behavior' => '行動評価',
            'custom' => 'カスタム',
        ];
    }

    /**
     * 利用可能な色一覧を取得
     *
     * @return array<string> 色一覧（#RRGGBB形式）
     */
    public function getAvailableColors(): array
    {
        return [
            '#10B981', // emerald
            '#3B82F6', // blue
            '#F59E0B', // amber
            '#EF4444', // red
            '#8B5CF6', // violet
            '#06B6D4', // cyan
            '#84CC16', // lime
            '#EC4899', // pink
            '#F97316', // orange
            '#14B8A6', // teal
        ];
    }
}