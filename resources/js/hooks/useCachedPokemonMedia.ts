import { useState, useEffect, useCallback } from 'react';
import { getCachedPokemonImageUrlFromServer, getCachedPokemonCryUrlFromServer, getPokemonImageUrl, getPokemonCryUrl } from '../utils/pokemon';

interface CachedMediaState {
    imageUrl: string;
    cryUrl: string;
    isImageLoading: boolean;
    isCryLoading: boolean;
    imageError: string | null;
    cryError: string | null;
}

/**
 * ポケモンの画像と鳴き声をキャッシュ付きで取得するフック
 * @param pokemonId ポケモンID
 * @param preloadCry 鳴き声を事前読み込みするか（デフォルト: false）
 * @returns キャッシュされたメディアURLと状態
 */
export const useCachedPokemonMedia = (pokemonId: number | null, preloadCry: boolean = false) => {
    const [state, setState] = useState<CachedMediaState>({
        imageUrl: '',
        cryUrl: '',
        isImageLoading: false,
        isCryLoading: false,
        imageError: null,
        cryError: null,
    });

    useEffect(() => {
        if (!pokemonId) {
            setState(prev => ({
                ...prev,
                imageUrl: '',
                cryUrl: '',
                isImageLoading: false,
                isCryLoading: false,
                imageError: null,
                cryError: null,
            }));
            return;
        }

        // 画像の取得を開始
        setState(prev => ({
            ...prev,
            isImageLoading: true,
            imageError: null,
            imageUrl: getPokemonImageUrl(pokemonId), // フォールバック用に即座に元URLを設定
        }));

        // サーバーキャッシュから画像を取得
        getCachedPokemonImageUrlFromServer(pokemonId)
            .then(cachedUrl => {
                setState(prev => ({
                    ...prev,
                    imageUrl: cachedUrl,
                    isImageLoading: false,
                }));
            })
            .catch(error => {
                console.error('ポケモン画像キャッシュエラー:', error);
                setState(prev => ({
                    ...prev,
                    imageUrl: getPokemonImageUrl(pokemonId), // フォールバック
                    isImageLoading: false,
                    imageError: error.message,
                }));
            });

        // 鳴き声の事前読み込み
        if (preloadCry) {
            setState(prev => ({
                ...prev,
                isCryLoading: true,
                cryError: null,
                cryUrl: getPokemonCryUrl(pokemonId), // フォールバック用に即座に元URLを設定
            }));

            getCachedPokemonCryUrlFromServer(pokemonId)
                .then(cachedUrl => {
                    setState(prev => ({
                        ...prev,
                        cryUrl: cachedUrl,
                        isCryLoading: false,
                    }));
                })
                .catch(error => {
                    console.error('ポケモン鳴き声キャッシュエラー:', error);
                    setState(prev => ({
                        ...prev,
                        cryUrl: getPokemonCryUrl(pokemonId), // フォールバック
                        isCryLoading: false,
                        cryError: error.message,
                    }));
                });
        }
    }, [pokemonId, preloadCry]);

    /**
     * 鳴き声を遅延読み込みで取得する
     */
    const loadCry = useCallback(async (): Promise<string> => {
        if (!pokemonId) {
            throw new Error('ポケモンIDが指定されていません');
        }

        // 現在のstateを参照するため、stateを依存配列に含めない
        const currentState = state;
        if (currentState.cryUrl && currentState.cryUrl.startsWith('data:')) {
            // 既にキャッシュされている場合
            return currentState.cryUrl;
        }

        setState(prev => ({ ...prev, isCryLoading: true, cryError: null }));

        try {
            const cachedUrl = await getCachedPokemonCryUrlFromServer(pokemonId);
            setState(prev => ({
                ...prev,
                cryUrl: cachedUrl,
                isCryLoading: false,
            }));
            return cachedUrl;
        } catch (error) {
            console.error('ポケモン鳴き声キャッシュエラー:', error);
            const fallbackUrl = getPokemonCryUrl(pokemonId);
            setState(prev => ({
                ...prev,
                cryUrl: fallbackUrl,
                isCryLoading: false,
                cryError: error instanceof Error ? error.message : 'Unknown error',
            }));
            return fallbackUrl;
        }
    }, [pokemonId]);

    return {
        ...state,
        loadCry,
    };
};

/**
 * 複数のポケモンの画像を一括でプリロードするフック
 * @param pokemonIds ポケモンIDの配列
 * @returns プリロードの進行状況
 */
export const usePreloadPokemonImages = (pokemonIds: number[]) => {
    const [preloadProgress, setPreloadProgress] = useState({
        total: 0,
        loaded: 0,
        isLoading: false,
        errors: [] as string[],
    });

    useEffect(() => {
        if (pokemonIds.length === 0) {
            setPreloadProgress({ total: 0, loaded: 0, isLoading: false, errors: [] });
            return;
        }

        setPreloadProgress({
            total: pokemonIds.length,
            loaded: 0,
            isLoading: true,
            errors: [],
        });

        const preloadPromises = pokemonIds.map(async (pokemonId, index) => {
            try {
                await getCachedPokemonImageUrlFromServer(pokemonId);
                setPreloadProgress(prev => ({
                    ...prev,
                    loaded: prev.loaded + 1,
                }));
            } catch (error) {
                console.error(`ポケモン${pokemonId}の画像プリロードエラー:`, error);
                setPreloadProgress(prev => ({
                    ...prev,
                    loaded: prev.loaded + 1,
                    errors: [...prev.errors, `ポケモン${pokemonId}: ${error instanceof Error ? error.message : 'Unknown error'}`],
                }));
            }
        });

        Promise.all(preloadPromises).finally(() => {
            setPreloadProgress(prev => ({
                ...prev,
                isLoading: false,
            }));
        });
    }, [pokemonIds]);

    return preloadProgress;
};