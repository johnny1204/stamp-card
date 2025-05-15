import { useEffect } from 'react';
import MediaCache from '../utils/cache';

/**
 * アプリケーション起動時にキャッシュの初期化を行うコンポーネント
 */
const CacheInitializer: React.FC = () => {
    useEffect(() => {
        // アプリ起動時に期限切れキャッシュをクリーンアップ
        MediaCache.cleanExpiredCache();

        // キャッシュ統計をデバッグ出力
        const stats = MediaCache.getCacheStats();
        console.log('ポケモンメディアキャッシュ統計:', {
            アイテム数: stats.itemCount,
            合計サイズ: `${(stats.totalSize / 1024 / 1024).toFixed(2)} MB`,
            最大保存期間: `${stats.maxAge / 1000 / 60 / 60 / 24} 日`,
        });

        // 定期的なクリーンアップ（1時間ごと）
        const cleanupInterval = setInterval(() => {
            MediaCache.cleanExpiredCache();
        }, 60 * 60 * 1000); // 1時間

        return () => {
            clearInterval(cleanupInterval);
        };
    }, []);

    return null; // このコンポーネントは何もレンダリングしない
};

export default CacheInitializer;