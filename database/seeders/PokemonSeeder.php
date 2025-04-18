<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Pokemon;
use Illuminate\Support\Facades\File;

class PokemonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = base_path('all_pokemon_id_name_ja_legendary_mythical.csv');
        
        if (!File::exists($csvPath)) {
            $this->command->error('CSVファイルが見つかりません: ' . $csvPath);
            return;
        }

        $csvData = File::get($csvPath);
        $lines = explode("\n", $csvData);
        $header = str_getcsv(array_shift($lines));

        $this->command->info('ポケモンデータを読み込み中...');
        
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            
            $data = str_getcsv($line);
            
            if (count($data) < 6) {
                continue;
            }
            
            // タイプの解析（カンマ区切りの文字列を分割）
            $types = explode(',', trim($data[4], '"'));
            $type1 = isset($types[0]) ? trim($types[0]) : null;
            $type2 = isset($types[1]) ? trim($types[1]) : null;
            
            Pokemon::create([
                'id' => (int)$data[0],
                'name' => $data[1],
                'is_legendary' => $data[2] === 'true',
                'is_mythical' => $data[3] === 'true',
                'type1' => $type1,
                'type2' => $type2,
                'genus' => $data[5],
            ]);
        }
        
        $count = Pokemon::count();
        $this->command->info("ポケモンデータを{$count}件登録しました。");
    }
}
