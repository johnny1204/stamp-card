<?php

namespace App\Services;

use App\Models\Child;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * 子ども関連のビジネスロジックを担当するサービス
 */
class ChildService extends BaseService
{
    /**
     * 全ての子どもを取得
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Child>
     */
    public function getAllChildren(): Collection
    {
        return Child::withCount([
            'todayStamps as today_stamps_count',
            'thisMonthStamps as this_month_stamps_count',
            'stamps as total_stamps_count'
        ])->orderBy('name')->get();
    }

    /**
     * 子どもを作成する
     *
     * @param array<string, mixed> $data 子どもデータ
     * @return Child 作成された子ども
     */
    public function createChild(array $data): Child
    {
        return $this->sqliteTransaction(function () use ($data) {
            $avatarPath = null;
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                $avatarPath = $this->saveAvatarImage($data['avatar']);
            }

            return Child::create([
                'name' => $data['name'],
                'birth_date' => $data['birth_date'] ?? null,
                'target_stamps' => $data['target_stamps'] ?? 10,
                'avatar_path' => $avatarPath,
            ]);
        });
    }

    /**
     * 子どもの情報を更新する
     *
     * @param Child $child 更新対象の子ども
     * @param array<string, mixed> $data 更新データ
     * @return Child 更新された子ども
     */
    public function updateChild(Child $child, array $data): Child
    {
        return $this->sqliteTransaction(function () use ($child, $data) {
            $avatarPath = $child->avatar_path;
            
            if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
                // 古いアバター画像を削除
                if ($child->avatar_path) {
                    $this->deleteAvatarImage($child->avatar_path);
                }
                // 新しいアバター画像を保存
                $avatarPath = $this->saveAvatarImage($data['avatar']);
            }

            $child->update([
                'name' => $data['name'] ?? $child->name,
                'birth_date' => $data['birth_date'] ?? $child->birth_date,
                'target_stamps' => $data['target_stamps'] ?? $child->target_stamps,
                'avatar_path' => $avatarPath,
            ]);

            return $child->fresh();
        });
    }

    /**
     * 子どもを削除する
     *
     * @param Child $child 削除対象の子ども
     * @return bool 削除成功可否
     */
    public function deleteChild(Child $child): bool
    {
        return $this->sqliteTransaction(function () use ($child) {
            // アバター画像を削除
            if ($child->avatar_path) {
                $this->deleteAvatarImage($child->avatar_path);
            }
            
            // 関連するスタンプも削除
            $child->stamps()->delete();
            
            return $child->delete();
        });
    }

    /**
     * 子どもをIDで取得
     *
     * @param int $id 子どもID
     * @return Child|null 子ども（見つからない場合null）
     */
    public function findChild(int $id): ?Child
    {
        return Child::find($id);
    }

    /**
     * 子どもの今日のスタンプ数を取得
     *
     * @param Child $child 対象の子ども
     * @return int 今日のスタンプ数
     */
    public function getTodayStampsCount(Child $child): int
    {
        return $child->todayStamps()->count();
    }

    /**
     * 子どもの今月のスタンプ数を取得
     *
     * @param Child $child 対象の子ども
     * @return int 今月のスタンプ数
     */
    public function getThisMonthStampsCount(Child $child): int
    {
        return $child->thisMonthStamps()->count();
    }

    /**
     * アバター画像を保存する
     *
     * @param UploadedFile $file アップロードされたファイル
     * @return string 保存されたファイルのパス
     */
    private function saveAvatarImage(UploadedFile $file): string
    {
        // avatarsディレクトリに一意な名前で保存
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('avatars', $filename, 'public');
        
        // publicディスクのURLを返す
        return Storage::url($path);
    }

    /**
     * アバター画像を削除する
     *
     * @param string $avatarPath アバターファイルのパス
     * @return bool 削除成功可否
     */
    private function deleteAvatarImage(string $avatarPath): bool
    {
        // URLからファイルパスを取得（/storage/avatars/xxx.jpg → avatars/xxx.jpg）
        $relativePath = str_replace('/storage/', '', $avatarPath);
        
        if (Storage::disk('public')->exists($relativePath)) {
            return Storage::disk('public')->delete($relativePath);
        }
        
        return true; // ファイルが存在しない場合も成功とみなす
    }
}