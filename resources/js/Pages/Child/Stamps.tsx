import React, { useState, useCallback } from 'react';
import { Head } from '@inertiajs/react';
import { getPokemonImageUrl } from '../../utils/pokemon';
import PokemonModal from '../../Components/PokemonModal';

interface Pokemon {
    id: number;
    name: string;
    type1?: string;
    type2?: string;
    genus?: string;
    is_legendary: boolean;
    is_mythical: boolean;
}

interface StampType {
    id: number;
    name: string;
    icon: string;
    color: string;
}

interface Stamp {
    id: number;
    stamped_at: string;
    opened_at?: string;
    comment?: string;
    pokemon?: Pokemon;
    stamp_type?: StampType;
}

interface Child {
    id: number;
    name: string;
    age?: number;
}

interface Statistics {
    total_stamps: number;
    today_stamps: number;
    this_month_stamps: number;
}

interface StampsProps {
    child: Child;
    stamps: Stamp[];
    statistics: Statistics;
    isChildView: boolean;
}

const Stamps: React.FC<StampsProps> = ({ child, stamps, statistics, isChildView }) => {
    const [selectedPokemon, setSelectedPokemon] = useState<Pokemon | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [openingStamps, setOpeningStamps] = useState<Set<number>>(new Set());
    const [localStamps, setLocalStamps] = useState(stamps);
    
    const handlePokemonClick = (pokemon: Pokemon) => {
        setSelectedPokemon(pokemon);
        setIsModalOpen(true);
    };
    
    const handleStampClick = async (stamp: Stamp) => {
        // 既に開封済みの場合はポケモンモーダルを表示
        if (stamp.opened_at && stamp.pokemon) {
            handlePokemonClick(stamp.pokemon);
            return;
        }
        
        // 未開封の場合は開封処理
        if (openingStamps.has(stamp.id)) return; // 開封中は無視
        
        setOpeningStamps(prev => new Set([...prev, stamp.id]));
        
        try {
            const response = await fetch(
                `/child/${child.id}/stamps/${stamp.id}/open`,
                {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                }
            );
            
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    // ローカル状態を更新
                    setLocalStamps(prevStamps => 
                        prevStamps.map(s => 
                            s.id === stamp.id 
                                ? { ...s, opened_at: data.stamp.opened_at, pokemon: data.stamp.pokemon, stamp_type: data.stamp.stamp_type }
                                : s
                        )
                    );
                    
                    // 開封アニメーション後にポケモンモーダルを表示
                    setTimeout(() => {
                        if (data.stamp.pokemon) {
                            setSelectedPokemon(data.stamp.pokemon);
                            setIsModalOpen(true);
                        }
                    }, 1000);
                }
            }
        } catch (error) {
            console.error('スタンプ開封エラー:', error);
        } finally {
            setOpeningStamps(prev => {
                const newSet = new Set(prev);
                newSet.delete(stamp.id);
                return newSet;
            });
        }
    };
    
    const handleCloseModal = useCallback(() => {
        setIsModalOpen(false);
        setSelectedPokemon(null);
    }, []);

    return (
        <div className="min-h-screen bg-gradient-to-br from-green-100 via-blue-50 to-purple-100">
            <Head title={`${child.name}のスタンプ一覧`} />
            
            <div className="container mx-auto px-4 py-8">
                <div className="text-center mb-8">
                    <h1 className="text-4xl font-bold text-green-800 mb-2">
                        ⭐ {child.name}のスタンプ ⭐
                    </h1>
                    <p className="text-xl text-green-600">
                        がんばった記録をチェックしよう！
                    </p>
                </div>

                {/* 統計情報 */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">🏆</div>
                        <div className="text-2xl font-bold text-orange-600">{statistics.total_stamps}</div>
                        <div className="text-sm text-gray-600">全部のスタンプ</div>
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">⭐</div>
                        <div className="text-2xl font-bold text-blue-600">{statistics.today_stamps}</div>
                        <div className="text-sm text-gray-600">今日のスタンプ</div>
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">📅</div>
                        <div className="text-2xl font-bold text-purple-600">{statistics.this_month_stamps}</div>
                        <div className="text-sm text-gray-600">今月のスタンプ</div>
                    </div>
                </div>

                {localStamps.length === 0 ? (
                    <div className="text-center py-12">
                        <div className="text-8xl mb-4">🎮</div>
                        <h2 className="text-2xl font-bold text-gray-600 mb-4">
                            まだスタンプがありません
                        </h2>
                        <p className="text-lg text-gray-500">
                            がんばってスタンプを集めよう！
                        </p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {localStamps.map((stamp) => (
                            <div 
                                key={stamp.id}
                                className="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow"
                            >
                                <div className="p-6">
                                    <div className="flex items-center justify-between mb-4">
                                        <div className="text-sm text-gray-500">
                                            {new Date(stamp.stamped_at).toLocaleDateString('ja-JP')}
                                        </div>
                                        {stamp.stamp_type && (
                                            <div 
                                                className="px-2 py-1 rounded-full text-xs text-white font-semibold"
                                                style={{ backgroundColor: stamp.stamp_type.color }}
                                            >
                                                {stamp.stamp_type.icon} {stamp.stamp_type.name}
                                            </div>
                                        )}
                                    </div>

                                    <div className="text-center mb-4">
                                        <div className="relative inline-block">
                                            {stamp.opened_at && stamp.pokemon ? (
                                                <>
                                                    <img
                                                        src={getPokemonImageUrl(stamp.pokemon.id)}
                                                        alt={stamp.pokemon.name}
                                                        className="w-24 h-24 object-contain mx-auto cursor-pointer hover:scale-105 transition-transform"
                                                        onClick={() => handleStampClick(stamp)}
                                                        onError={(e) => {
                                                            const pokemon = stamp.pokemon;
                                                            if (pokemon && (pokemon.is_legendary || pokemon.is_mythical)) {
                                                                e.currentTarget.src = '/images/master-ball.png';
                                                            } else {
                                                                e.currentTarget.src = '/images/poke-ball.png';
                                                            }
                                                        }}
                                                    />
                                                    {(stamp.pokemon.is_legendary || stamp.pokemon.is_mythical) && (
                                                        <div className="absolute -top-1 -right-1 text-yellow-400">
                                                            ✨
                                                        </div>
                                                    )}
                                                    <h3 className="font-bold text-lg text-gray-800 mt-2">
                                                        {stamp.pokemon.name}
                                                    </h3>
                                                    {stamp.pokemon.genus && (
                                                        <p className="text-sm text-gray-600">
                                                            {stamp.pokemon.genus}
                                                        </p>
                                                    )}
                                                    
                                                    {/* ポケモンタイプ表示 */}
                                                    <div className="flex justify-center gap-1 mt-2">
                                                        {stamp.pokemon.type1 && (
                                                            <span className="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">
                                                                {stamp.pokemon.type1}
                                                            </span>
                                                        )}
                                                        {stamp.pokemon.type2 && (
                                                            <span className="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded">
                                                                {stamp.pokemon.type2}
                                                            </span>
                                                        )}
                                                    </div>
                                                </>
                                            ) : (
                                                <div 
                                                    className={`w-24 h-24 bg-gradient-to-br from-yellow-200 to-orange-300 rounded-full mx-auto cursor-pointer hover:scale-105 transition-transform flex items-center justify-center border-4 border-yellow-400 shadow-md ${
                                                        openingStamps.has(stamp.id) ? 'animate-pulse' : ''
                                                    }`}
                                                    onClick={() => handleStampClick(stamp)}
                                                >
                                                    {openingStamps.has(stamp.id) ? (
                                                        <div className="flex flex-col items-center">
                                                            <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-orange-600 mb-1"></div>
                                                            <span className="text-xs text-orange-700 font-bold">開封中...</span>
                                                        </div>
                                                    ) : (
                                                        <div className="flex flex-col items-center">
                                                            <span className="text-4xl text-orange-600 mb-1 animate-bounce">？</span>
                                                            <span className="text-xs text-orange-700 font-bold">タップ！</span>
                                                        </div>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    </div>

                                    {stamp.comment && (
                                        <div className="bg-gray-50 rounded-lg p-3 mt-4">
                                            <p className="text-sm text-gray-700">
                                                💬 {stamp.comment}
                                            </p>
                                        </div>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {localStamps.length > 0 && (
                    <div className="mt-8 text-center">
                        <div className="bg-white rounded-xl p-6 shadow-md inline-block">
                            <h3 className="text-lg font-bold text-gray-800 mb-2">
                                🎉 すごいね！
                            </h3>
                            <p className="text-gray-600">
                                {statistics.total_stamps}個のスタンプを集めました！
                            </p>
                            <p className="text-sm text-gray-500 mt-1">
                                これからもがんばろうね！
                            </p>
                        </div>
                    </div>
                )}
            </div>
            
            {/* ポケモンモーダル */}
            <PokemonModal
                isOpen={isModalOpen}
                pokemon={selectedPokemon}
                childName={child.name}
                onClose={handleCloseModal}
            />
        </div>
    );
};

export default Stamps;