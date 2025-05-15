/**
 * ブラウザのローカルストレージを使用したキャッシュ管理ユーティリティ
 */

interface CacheItem {
    data: string; // Base64エンコードされたデータ
    timestamp: number;
    mimeType: string;
}

class MediaCache {
    private static readonly CACHE_PREFIX = 'pokemon_cache_';
    private static readonly MAX_AGE = 7 * 24 * 60 * 60 * 1000; // 7日間（ミリ秒）
    private static readonly MAX_CACHE_SIZE = 50 * 1024 * 1024; // 50MB（バイト）

    /**
     * キャッシュキーを生成する
     */
    private static getCacheKey(url: string): string {
        return this.CACHE_PREFIX + encodeURIComponent(url);
    }

    /**
     * URLからリソースを取得し、キャッシュに保存する
     */
    static async fetchAndCache(url: string): Promise<string> {
        const cacheKey = this.getCacheKey(url);
        
        // キャッシュから取得を試行
        const cached = this.getFromCache(cacheKey);
        if (cached) {
            return cached;
        }

        try {
            // ネットワークから取得
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const blob = await response.blob();
            const base64 = await this.blobToBase64(blob);
            const dataUrl = `data:${blob.type};base64,${base64}`;

            // キャッシュに保存
            const cacheItem: CacheItem = {
                data: base64,
                timestamp: Date.now(),
                mimeType: blob.type
            };

            this.saveToCache(cacheKey, cacheItem);
            return dataUrl;

        } catch (error) {
            console.error('メディアの取得に失敗しました:', url, error);
            // フォールバック: 元のURLを返す
            return url;
        }
    }

    /**
     * キャッシュからデータを取得する
     */
    private static getFromCache(cacheKey: string): string | null {
        try {
            const cached = localStorage.getItem(cacheKey);
            if (!cached) {
                return null;
            }

            const cacheItem: CacheItem = JSON.parse(cached);
            
            // 期限切れチェック
            if (Date.now() - cacheItem.timestamp > this.MAX_AGE) {
                localStorage.removeItem(cacheKey);
                return null;
            }

            return `data:${cacheItem.mimeType};base64,${cacheItem.data}`;
        } catch (error) {
            console.error('キャッシュ読み取りエラー:', error);
            return null;
        }
    }

    /**
     * キャッシュにデータを保存する
     */
    private static saveToCache(cacheKey: string, cacheItem: CacheItem): void {
        try {
            // キャッシュサイズをチェック
            const cacheSize = this.getCacheSize();
            const itemSize = JSON.stringify(cacheItem).length * 2; // UTF-16なので2倍

            if (cacheSize + itemSize > this.MAX_CACHE_SIZE) {
                this.cleanOldCache();
            }

            localStorage.setItem(cacheKey, JSON.stringify(cacheItem));
        } catch (error) {
            console.error('キャッシュ保存エラー:', error);
            
            // ストレージが満杯の場合、古いキャッシュを削除して再試行
            if (error instanceof DOMException && error.name === 'QuotaExceededError') {
                this.cleanOldCache();
                try {
                    localStorage.setItem(cacheKey, JSON.stringify(cacheItem));
                } catch (retryError) {
                    console.error('キャッシュ保存再試行も失敗:', retryError);
                }
            }
        }
    }

    /**
     * BlobをBase64に変換する
     */
    private static blobToBase64(blob: Blob): Promise<string> {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => {
                const result = reader.result as string;
                // data:... プレフィックスを除去してBase64部分のみを取得
                const base64 = result.split(',')[1];
                resolve(base64);
            };
            reader.onerror = reject;
            reader.readAsDataURL(blob);
        });
    }

    /**
     * キャッシュの合計サイズを取得する
     */
    private static getCacheSize(): number {
        let totalSize = 0;
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.CACHE_PREFIX)) {
                const value = localStorage.getItem(key);
                if (value) {
                    totalSize += value.length * 2; // UTF-16なので2倍
                }
            }
        }
        return totalSize;
    }

    /**
     * 古いキャッシュを削除する
     */
    private static cleanOldCache(): void {
        const cacheItems: { key: string; timestamp: number }[] = [];

        // キャッシュアイテムを収集
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.CACHE_PREFIX)) {
                try {
                    const value = localStorage.getItem(key);
                    if (value) {
                        const cacheItem: CacheItem = JSON.parse(value);
                        cacheItems.push({ key, timestamp: cacheItem.timestamp });
                    }
                } catch (error) {
                    // 破損したデータは削除
                    localStorage.removeItem(key);
                }
            }
        }

        // 古い順にソート
        cacheItems.sort((a, b) => a.timestamp - b.timestamp);

        // 古いキャッシュの半分を削除
        const itemsToDelete = Math.ceil(cacheItems.length / 2);
        for (let i = 0; i < itemsToDelete; i++) {
            localStorage.removeItem(cacheItems[i].key);
        }
    }

    /**
     * 期限切れのキャッシュをクリーンアップする
     */
    static cleanExpiredCache(): void {
        const now = Date.now();
        const keysToDelete: string[] = [];

        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.CACHE_PREFIX)) {
                try {
                    const value = localStorage.getItem(key);
                    if (value) {
                        const cacheItem: CacheItem = JSON.parse(value);
                        if (now - cacheItem.timestamp > this.MAX_AGE) {
                            keysToDelete.push(key);
                        }
                    }
                } catch (error) {
                    // 破損したデータは削除対象に追加
                    keysToDelete.push(key);
                }
            }
        }

        // 期限切れキャッシュを削除
        keysToDelete.forEach(key => localStorage.removeItem(key));
    }

    /**
     * 全てのキャッシュをクリアする
     */
    static clearAllCache(): void {
        const keysToDelete: string[] = [];

        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.CACHE_PREFIX)) {
                keysToDelete.push(key);
            }
        }

        keysToDelete.forEach(key => localStorage.removeItem(key));
    }

    /**
     * キャッシュ統計情報を取得する
     */
    static getCacheStats(): { itemCount: number; totalSize: number; maxAge: number } {
        let itemCount = 0;
        let totalSize = 0;

        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.CACHE_PREFIX)) {
                itemCount++;
                const value = localStorage.getItem(key);
                if (value) {
                    totalSize += value.length * 2;
                }
            }
        }

        return {
            itemCount,
            totalSize,
            maxAge: this.MAX_AGE
        };
    }
}

export default MediaCache;