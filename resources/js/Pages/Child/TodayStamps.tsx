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

interface TodayStampsProps {
    child: Child;
    todayStamps: Stamp[];
    todayCount: number;
    monthlyCount: number;
    isChildView: boolean;
}

const TodayStamps: React.FC<TodayStampsProps> = ({ 
    child, 
    todayStamps, 
    todayCount, 
    monthlyCount, 
    isChildView 
}) => {
    const [selectedPokemon, setSelectedPokemon] = useState<Pokemon | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [openingStamps, setOpeningStamps] = useState<Set<number>>(new Set());
    const [localStamps, setLocalStamps] = useState(todayStamps);
    
    // デバッグ: スタンプデータの確認
    console.log('TodayStamps received:', todayStamps);
    console.log('First stamp opened_at:', todayStamps[0]?.opened_at);
    
    const currentHour = new Date().getHours();
    const getGreeting = () => {
        if (currentHour < 12) return 'おはよう';
        if (currentHour < 18) return 'こんにちは';
        return 'こんばんは';
    };
    
    const handlePokemonClick = (pokemon: Pokemon) => {
        setSelectedPokemon(pokemon);
        setIsModalOpen(true);
    };
    
    const handleStampClick = async (stamp: Stamp) => {
        console.log('Stamp clicked:', stamp);
        console.log('stamp.opened_at:', stamp.opened_at);
        console.log('stamp.pokemon:', stamp.pokemon);
        
        // 既に開封済みの場合はポケモンモーダルを表示
        if (stamp.opened_at && stamp.pokemon) {
            handlePokemonClick(stamp.pokemon);
            return;
        }
        
        // 未開封の場合は開封処理
        if (openingStamps.has(stamp.id)) return; // 開封中は無視
        
        console.log('Starting opening process for stamp:', stamp.id);
        setOpeningStamps(prev => new Set([...prev, stamp.id]));
        
        try {
            const response = await fetch(
                `/child/${child.id}/stamps/${stamp.id}/open`,
                {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
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
        <div className="min-h-screen bg-gradient-to-br from-orange-100 via-yellow-50 to-pink-100">
            <Head title={`${child.name}の今日のスタンプ`} />
            
            <div className="container mx-auto px-4 py-8">
                <div className="text-center mb-8">
                    <h1 className="text-4xl font-bold text-orange-800 mb-2">
                        ☀️ {getGreeting()}、{child.name}！ ☀️
                    </h1>
                    <p className="text-xl text-orange-600">
                        今日もがんばったね！ 
                    </p>
                </div>

                {/* 今日の成果 */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div className="bg-white rounded-xl p-6 shadow-lg text-center">
                        <div className="text-4xl mb-3">⭐</div>
                        <div className="text-3xl font-bold text-orange-600 mb-2">{todayCount}</div>
                        <div className="text-lg text-gray-700">今日のスタンプ</div>
                        {todayCount > 0 && (
                            <div className="text-sm text-green-600 mt-1">すごいね！</div>
                        )}
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-lg text-center">
                        <div className="text-4xl mb-3">📅</div>
                        <div className="text-3xl font-bold text-purple-600 mb-2">{monthlyCount}</div>
                        <div className="text-lg text-gray-700">今月のスタンプ</div>
                        <div className="text-sm text-gray-500 mt-1">がんばってる！</div>
                    </div>
                </div>

                {todayCount === 0 ? (
                    <div className="text-center py-12">
                        <div className="text-8xl mb-6">🌟</div>
                        <h2 className="text-2xl font-bold text-gray-600 mb-4">
                            今日はまだスタンプがないよ
                        </h2>
                        <p className="text-lg text-gray-500 mb-4">
                            いいことをして、スタンプをもらおう！
                        </p>
                        <div className="bg-white rounded-xl p-6 shadow-md inline-block">
                            <h3 className="font-bold text-gray-800 mb-2">今日できること:</h3>
                            <ul className="text-gray-600 space-y-1">
                                <li>🧹 お部屋の片付け</li>
                                <li>🦷 歯磨き</li>
                                <li>📚 宿題</li>
                                <li>🤝 お手伝い</li>
                                <li>😊 優しくする</li>
                            </ul>
                        </div>
                    </div>
                ) : (
                    <>
                        <div className="text-center mb-8">
                            <h2 className="text-2xl font-bold text-gray-800 mb-4">
                                🎉 今日ゲットしたポケモン！
                            </h2>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                            {localStamps.map((stamp, index) => (
                                <div 
                                    key={stamp.id}
                                    className="bg-white rounded-xl shadow-lg overflow-hidden transform hover:scale-105 transition-transform"
                                >
                                    <div className="p-6">
                                        <div className="flex items-center justify-between mb-4">
                                            <div className="text-sm font-semibold text-gray-500">
                                                スタンプ #{index + 1}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {new Date(stamp.stamped_at).toLocaleTimeString('ja-JP', {
                                                    hour: '2-digit',
                                                    minute: '2-digit'
                                                })}
                                            </div>
                                        </div>

                                        {stamp.stamp_type && (
                                            <div className="mb-4">
                                                <div 
                                                    className="inline-block px-3 py-1 rounded-full text-sm text-white font-semibold"
                                                    style={{ backgroundColor: stamp.stamp_type.color }}
                                                >
                                                    {stamp.stamp_type.icon} {stamp.stamp_type.name}
                                                </div>
                                            </div>
                                        )}

                                        <div className="text-center mb-4">
                                            <div className="relative inline-block">
                                                {stamp.opened_at && stamp.pokemon ? (
                                                    <>
                                                        <img
                                                            src={getPokemonImageUrl(stamp.pokemon.id)}
                                                            alt={stamp.pokemon.name}
                                                            className="w-32 h-32 object-contain mx-auto cursor-pointer hover:scale-105 transition-transform"
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
                                                            <div className="absolute -top-2 -right-2">
                                                                <div className="bg-yellow-400 text-yellow-900 rounded-full w-8 h-8 flex items-center justify-center text-sm font-bold">
                                                                    ✨
                                                                </div>
                                                            </div>
                                                        )}
                                                        <h3 className="font-bold text-xl text-gray-800 mt-3">
                                                            {stamp.pokemon.name}
                                                        </h3>
                                                        {stamp.pokemon.genus && (
                                                            <p className="text-sm text-gray-600 mt-1">
                                                                {stamp.pokemon.genus}
                                                            </p>
                                                        )}
                                                        {(stamp.pokemon.is_legendary || stamp.pokemon.is_mythical) && (
                                                            <div className="mt-2">
                                                                <span className="inline-block bg-gradient-to-r from-yellow-400 to-orange-400 text-white px-3 py-1 rounded-full text-xs font-bold">
                                                                    {stamp.pokemon.is_mythical ? '幻のポケモン' : '伝説ポケモン'}
                                                                </span>
                                                            </div>
                                                        )}
                                                    </>
                                                ) : (
                                                    <div 
                                                        className={`w-32 h-32 bg-gradient-to-br from-yellow-200 to-orange-300 rounded-full mx-auto cursor-pointer hover:scale-105 transition-transform flex items-center justify-center border-4 border-yellow-400 shadow-lg ${
                                                            openingStamps.has(stamp.id) ? 'animate-pulse' : ''
                                                        }`}
                                                        onClick={() => handleStampClick(stamp)}
                                                    >
                                                        {openingStamps.has(stamp.id) ? (
                                                            <div className="flex flex-col items-center">
                                                                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-600 mb-2"></div>
                                                                <span className="text-xs text-orange-700 font-bold">開封中...</span>
                                                            </div>
                                                        ) : (
                                                            <div className="flex flex-col items-center">
                                                                <span className="text-6xl text-orange-600 mb-1 animate-bounce">？</span>
                                                                <span className="text-xs text-orange-700 font-bold">タップして開封！</span>
                                                            </div>
                                                        )}
                                                    </div>
                                                )}
                                            </div>
                                        </div>

                                        {stamp.comment && (
                                            <div className="bg-blue-50 rounded-lg p-3 mt-4">
                                                <p className="text-sm text-blue-800">
                                                    💬 {stamp.comment}
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="text-center">
                            <div className="bg-white rounded-xl p-6 shadow-lg inline-block">
                                <h3 className="text-xl font-bold text-gray-800 mb-2">
                                    🎊 おめでとう！
                                </h3>
                                <p className="text-gray-600 mb-2">
                                    今日は{localStamps.filter(s => s.opened_at).length}個のスタンプを開封しました！
                                </p>
                                {localStamps.some(s => !s.opened_at) && (
                                    <p className="text-sm text-orange-600 mb-2">
                                        まだ{localStamps.filter(s => !s.opened_at).length}個の未開封スタンプがあるよ！
                                    </p>
                                )}
                                <p className="text-sm text-gray-500">
                                    明日もがんばろうね！
                                </p>
                            </div>
                        </div>
                    </>
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

export default TodayStamps;