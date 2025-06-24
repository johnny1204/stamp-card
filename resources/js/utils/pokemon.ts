/**
 * ポケモン関連のユーティリティ関数
 */

import MediaCache from './cache';

/**
 * ポケモンの画像URLを取得（キャッシュなし）
 * @param pokemonId ポケモンID
 * @returns 画像URL
 */
export const getPokemonImageUrl = (pokemonId: number): string => {
    return `https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/other/official-artwork/${pokemonId}.png`;
};

/**
 * ポケモンの鳴き声URLを取得（キャッシュなし）
 * @param pokemonId ポケモンID
 * @returns 鳴き声URL
 */
export const getPokemonCryUrl = (pokemonId: number): string => {
    // storage内のMP3ファイルを参照
    return `/storage/pokemon_cache/cries/${pokemonId}.mp3`;
};

/**
 * ポケモンの画像をキャッシュ付きで取得
 * @param pokemonId ポケモンID
 * @returns キャッシュされた画像URL（Promise）
 */
export const getCachedPokemonImageUrl = async (pokemonId: number): Promise<string> => {
    const originalUrl = getPokemonImageUrl(pokemonId);
    return await MediaCache.fetchAndCache(originalUrl);
};

/**
 * ポケモンの画像URLを取得（サーバーキャッシュ経由）
 * @param pokemonId ポケモンID
 * @returns サーバーキャッシュ経由のURL取得Promise
 */
export const getCachedPokemonImageUrlFromServer = async (pokemonId: number): Promise<string> => {
    try {
        const response = await fetch(`/api/pokemon/${pokemonId}/image`);
        const data = await response.json();
        
        if (data.success) {
            return data.url;
        } else {
            console.error('サーバーキャッシュ画像取得失敗:', data.message);
            return getPokemonImageUrl(pokemonId); // フォールバック
        }
    } catch (error) {
        console.error('サーバーキャッシュ画像API呼び出しエラー:', error);
        return getPokemonImageUrl(pokemonId); // フォールバック
    }
};

/**
 * ポケモンの画像URLを取得（キャッシュ優先、同期的）
 * フォールバック用に残す
 */
export const getPokemonImageUrlWithCache = (pokemonId: number): string => {
    // サーバーキャッシュを利用するため、とりあえず外部URLを返す
    // 実際の使用では getCachedPokemonImageUrlFromServer を使用
    return getPokemonImageUrl(pokemonId);
};

/**
 * ポケモンの鳴き声をキャッシュ付きで取得
 * @param pokemonId ポケモンID
 * @returns キャッシュされた鳴き声URL（Promise）
 */
export const getCachedPokemonCryUrl = async (pokemonId: number): Promise<string> => {
    const originalUrl = getPokemonCryUrl(pokemonId);
    return await MediaCache.fetchAndCache(originalUrl);
};

/**
 * ポケモンの鳴き声URLを取得（サーバーキャッシュ経由）
 * @param pokemonId ポケモンID
 * @returns サーバーキャッシュ経由のURL取得Promise
 */
export const getCachedPokemonCryUrlFromServer = async (pokemonId: number): Promise<string> => {
    try {
        const response = await fetch(`/api/pokemon/${pokemonId}/cry`);
        const data = await response.json();
        
        if (data.success) {
            return data.url;
        } else {
            console.error('サーバーキャッシュ鳴き声取得失敗:', data.message);
            return getPokemonCryUrl(pokemonId); // フォールバック
        }
    } catch (error) {
        console.error('サーバーキャッシュ鳴き声API呼び出しエラー:', error);
        return getPokemonCryUrl(pokemonId); // フォールバック
    }
};

/**
 * Pokemon型定義（画像・音声URLを含む拡張版）
 */
export interface PokemonWithUrls {
    id: number;
    name: string;
    type1?: string;
    type2?: string;
    genus?: string;
    is_legendary: boolean;
    is_mythical: boolean;
    image_url: string;
    cry_url: string;
}

/**
 * 基本のPokemon型から画像・音声URLを含む型に変換
 * @param pokemon 基本のポケモンデータ
 * @returns 画像・音声URLを含むポケモンデータ
 */
export const addPokemonUrls = (pokemon: any): PokemonWithUrls => {
    return {
        ...pokemon,
        image_url: getPokemonImageUrl(pokemon.id),
        cry_url: getPokemonCryUrl(pokemon.id),
    };
};