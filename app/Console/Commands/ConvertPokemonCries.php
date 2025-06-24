<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ConvertPokemonCries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pokemon:convert-cries 
                            {--start=1 : 開始ポケモンID} 
                            {--end=1025 : 終了ポケモンID}
                            {--force : 既存のMP3ファイルも再変換する}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ポケモンの鳴き声をOGGからMP3に一括変換してローカル保存';

    private const CRY_CACHE_DIR = 'pokemon_cache/cries';
    private const DISK = 'public';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $start = (int) $this->option('start');
        $end = (int) $this->option('end');
        $force = $this->option('force');
        
        $this->info("ポケモン鳴き声変換を開始します");
        $this->info("範囲: ポケモンID {$start} から {$end} まで");
        
        if ($force) {
            $this->warn("--force オプションが指定されています。既存のMP3ファイルも再変換します。");
        }
        
        // ディレクトリが存在しない場合は作成
        if (!Storage::disk(self::DISK)->exists(self::CRY_CACHE_DIR)) {
            Storage::disk(self::DISK)->makeDirectory(self::CRY_CACHE_DIR);
        }
        
        $progressBar = $this->output->createProgressBar($end - $start + 1);
        $progressBar->start();
        
        $successCount = 0;
        $skipCount = 0;
        $errorCount = 0;
        
        for ($pokemonId = $start; $pokemonId <= $end; $pokemonId++) {
            $result = $this->convertPokemonCry($pokemonId, $force);
            
            switch ($result) {
                case 'success':
                    $successCount++;
                    break;
                case 'skip':
                    $skipCount++;
                    break;
                case 'error':
                    $errorCount++;
                    break;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        $this->newLine(2);
        
        $this->info("変換完了！");
        $this->table(
            ['結果', '件数'],
            [
                ['成功', $successCount],
                ['スキップ', $skipCount],
                ['エラー', $errorCount],
                ['合計', $successCount + $skipCount + $errorCount],
            ]
        );
        
        return Command::SUCCESS;
    }
    
    /**
     * 単一ポケモンの鳴き声を変換
     */
    private function convertPokemonCry(int $pokemonId, bool $force): string
    {
        $mp3Filename = "{$pokemonId}.mp3";
        $mp3CachePath = self::CRY_CACHE_DIR . '/' . $mp3Filename;
        $oggFilename = "{$pokemonId}.ogg";
        $oggCachePath = self::CRY_CACHE_DIR . '/' . $oggFilename;
        
        // 既存のMP3ファイルをチェック
        if (!$force && Storage::disk(self::DISK)->exists($mp3CachePath)) {
            return 'skip';
        }
        
        try {
            // OGGファイルをダウンロード
            $cryUrl = "https://raw.githubusercontent.com/PokeAPI/cries/main/cries/pokemon/latest/{$pokemonId}.ogg";
            $response = Http::timeout(30)->get($cryUrl);
            
            if (!$response->successful()) {
                $this->error("ポケモンID {$pokemonId}: OGGファイルのダウンロードに失敗");
                return 'error';
            }
            
            // OGGファイルを一時保存
            Storage::disk(self::DISK)->put($oggCachePath, $response->body());
            
            // MP3に変換
            if ($this->convertOggToMp3($oggCachePath, $mp3CachePath)) {
                // 変換成功、OGGファイルを削除
                Storage::disk(self::DISK)->delete($oggCachePath);
                return 'success';
            } else {
                $this->error("ポケモンID {$pokemonId}: MP3変換に失敗");
                Storage::disk(self::DISK)->delete($oggCachePath);
                return 'error';
            }
            
        } catch (\Exception $e) {
            $this->error("ポケモンID {$pokemonId}: {$e->getMessage()}");
            return 'error';
        }
    }
    
    /**
     * OGGファイルをMP3に変換
     */
    private function convertOggToMp3(string $oggPath, string $mp3Path): bool
    {
        try {
            $oggFullPath = Storage::disk(self::DISK)->path($oggPath);
            $mp3FullPath = Storage::disk(self::DISK)->path($mp3Path);
            
            // ディレクトリが存在することを確認
            $mp3Dir = dirname($mp3FullPath);
            if (!is_dir($mp3Dir)) {
                mkdir($mp3Dir, 0755, true);
            }
            
            // FFmpegコマンドを実行
            $command = sprintf(
                'ffmpeg -i %s -c:a libmp3lame -b:a 128k %s 2>&1',
                escapeshellarg($oggFullPath),
                escapeshellarg($mp3FullPath)
            );
            
            exec($command, $output, $returnVar);
            
            if ($returnVar === 0 && file_exists($mp3FullPath) && filesize($mp3FullPath) > 0) {
                return true;
            } else {
                Log::error("FFmpeg変換失敗: {$oggPath} -> {$mp3Path}", [
                    'command' => $command,
                    'output' => implode("\n", $output),
                    'return_code' => $returnVar
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("FFmpeg変換でエラー: {$oggPath} -> {$mp3Path}", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
