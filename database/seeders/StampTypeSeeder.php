<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\StampType;

class StampTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stampTypes = [
            [
                'name' => '手伝い',
                'icon' => '🤝',
                'color' => '#10B981',
                'category' => 'help',
                'is_custom' => false,
                'is_system_default' => true,
                'family_id' => null,
            ],
            [
                'name' => '勉強',
                'icon' => '📚',
                'color' => '#3B82F6',
                'category' => 'lifestyle',
                'is_custom' => false,
                'is_system_default' => true,
                'family_id' => null,
            ],
            [
                'name' => 'おかたづけ',
                'icon' => '🧹',
                'color' => '#F59E0B',
                'category' => 'help',
                'is_custom' => false,
                'is_system_default' => true,
                'family_id' => null,
            ],
            [
                'name' => '運動',
                'icon' => '🏃',
                'color' => '#EF4444',
                'category' => 'behavior',
                'is_custom' => false,
                'is_system_default' => true,
                'family_id' => null,
            ],
            [
                'name' => 'いいこと',
                'icon' => '😊',
                'color' => '#8B5CF6',
                'category' => 'behavior',
                'is_custom' => false,
                'is_system_default' => true,
                'family_id' => null,
            ],
            [
                'name' => '挨拶',
                'icon' => '👋',
                'color' => '#06B6D4',
                'category' => 'lifestyle',
                'is_custom' => false,
                'is_system_default' => true,
                'family_id' => null,
            ],
            [
                'name' => '歯磨き',
                'icon' => '🦷',
                'color' => '#84CC16',
                'category' => 'lifestyle',
                'is_custom' => false,
                'is_system_default' => true,
                'family_id' => null,
            ],
            [
                'name' => '優しさ',
                'icon' => '💝',
                'color' => '#EC4899',
                'category' => 'behavior',
                'is_custom' => false,
                'is_system_default' => true,
                'family_id' => null,
            ],
        ];

        foreach ($stampTypes as $stampType) {
            StampType::create($stampType);
        }

        $this->command->info('スタンプ種類を' . count($stampTypes) . '件登録しました。');
    }
}
