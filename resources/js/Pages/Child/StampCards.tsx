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
    image_path?: string;
    rarity?: string;
}

interface Stamp {
    id: number;
    stamped_at: string;
    opened_at?: string;
    comment?: string;
    pokemon: Pokemon;
}

interface StampCard {
    card_number: number;
    is_completed: boolean;
    stamps: Stamp[];
    progress: number;
    target: number;
    completed_at?: string;
}

interface Child {
    id: number;
    name: string;
    age?: number;
}

interface StampCardsProps {
    child: Child;
    stampCards: StampCard[];
    isChildView: boolean;
}

const StampCards: React.FC<StampCardsProps> = ({ child, stampCards, isChildView }) => {
    const [selectedPokemon, setSelectedPokemon] = useState<Pokemon | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [openingStamps, setOpeningStamps] = useState<Set<number>>(new Set());
    const [localStampCards, setLocalStampCards] = useState(stampCards);
    const [pendingOpenStamp, setPendingOpenStamp] = useState<{stampId: number, pokemon: Pokemon} | null>(null);
    
    const handlePokemonClick = (pokemon: Pokemon) => {
        setSelectedPokemon(pokemon);
        setIsModalOpen(true);
    };
    
    const handleStampClick = async (stamp: Stamp) => {
        // 既に開封済みの場合はポケモンモーダルを表示
        if (stamp.opened_at) {
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
                    // 開封処理が成功したらまずモーダルを表示（ポケモン画像は表示しない）
                    if (data.stamp.pokemon) {
                        console.log('Opening modal for pokemon:', data.stamp.pokemon.name);
                        setPendingOpenStamp({
                            stampId: stamp.id,
                            pokemon: data.stamp.pokemon
                        });
                        setSelectedPokemon(data.stamp.pokemon);
                        setIsModalOpen(true);
                    }
                }
            }
        } catch (error) {
            console.error('スタンプ開封エラー:', error);
        } finally {
            // 開封処理完了後に状態をクリア
            setOpeningStamps(prev => {
                const newSet = new Set(prev);
                newSet.delete(stamp.id);
                return newSet;
            });
        }
    };
    
    const handleCloseModal = useCallback(() => {
        console.log('StampCards handleCloseModal called');
        setIsModalOpen(false);
        setSelectedPokemon(null);
        
        // モーダルを閉じたときに、保留中の開封済みスタンプがあれば状態を更新
        if (pendingOpenStamp) {
            setLocalStampCards(prevCards => 
                prevCards.map(card => ({
                    ...card,
                    stamps: card.stamps.map(s => 
                        s.id === pendingOpenStamp.stampId 
                            ? { ...s, opened_at: new Date().toISOString(), pokemon: pendingOpenStamp.pokemon }
                            : s
                    )
                }))
            );
            setPendingOpenStamp(null);
        }
    }, [pendingOpenStamp]);
    
    return (
        <div className="min-h-screen bg-gradient-to-br from-blue-100 via-purple-50 to-pink-100">
            <Head title={`${child.name}のスタンプカード`} />
            
            <div className="container mx-auto px-4 py-8">
                <div className="text-center mb-8">
                    <h1 className="text-4xl font-bold text-purple-800 mb-2">
                        🎉 {child.name}のスタンプカード 🎉
                    </h1>
                    <p className="text-xl text-purple-600">
                        集めたポケモンをチェックしよう！
                    </p>
                </div>

                {stampCards.length === 0 ? (
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
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {localStampCards.map((card) => (
                            <div 
                                key={card.card_number}
                                className={`bg-white rounded-2xl shadow-lg p-6 border-4 ${
                                    card.is_completed 
                                        ? 'border-yellow-400 bg-gradient-to-br from-yellow-50 to-orange-50' 
                                        : 'border-blue-300 bg-gradient-to-br from-blue-50 to-purple-50'
                                }`}
                            >
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="text-2xl font-bold text-gray-800">
                                        カード #{card.card_number}
                                    </h3>
                                    {card.is_completed && (
                                        <div className="bg-yellow-400 text-yellow-900 px-3 py-1 rounded-full text-sm font-bold">
                                            ✨ 完成！
                                        </div>
                                    )}
                                </div>

                                <div className="mb-4">
                                    <div className="flex justify-between text-sm text-gray-600 mb-2">
                                        <span>進行状況</span>
                                        <span>{card.progress} / {card.target}</span>
                                    </div>
                                    <div className="w-full bg-gray-200 rounded-full h-3">
                                        <div 
                                            className={`h-3 rounded-full ${
                                                card.is_completed ? 'bg-yellow-400' : 'bg-blue-400'
                                            }`}
                                            style={{ width: `${(card.progress / card.target) * 100}%` }}
                                        />
                                    </div>
                                </div>

                                {card.completed_at && (
                                    <div className="text-sm text-gray-600 mb-4">
                                        完成日: {new Date(card.completed_at).toLocaleDateString('ja-JP')}
                                    </div>
                                )}

                                <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 sm:gap-3">
                                    {Array.from({ length: card.target }, (_, index) => {
                                        const stamp = card.stamps[index];
                                        return (
                                            <div 
                                                key={index} 
                                                className={`aspect-square rounded-lg border-2 flex items-center justify-center ${
                                                    stamp 
                                                        ? 'border-green-300 bg-green-50' 
                                                        : 'border-gray-300 bg-gray-50'
                                                }`}
                                            >
                                                {stamp ? (
                                                    <div className="w-full h-full p-1 flex flex-col gap-1">
                                                        {stamp.opened_at ? (
                                                            <div className="flex flex-col h-full">
                                                                <div className="flex-1 flex items-center justify-center min-h-0">
                                                                    <img
                                                                        src={getPokemonImageUrl(stamp.pokemon.id)}
                                                                        alt={stamp.pokemon.name}
                                                                        className="max-w-full max-h-full object-contain cursor-pointer hover:scale-105 transition-transform"
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
                                                                </div>
                                                                {/* ポケモン名（PCのみ表示） */}
                                                                <div className="hidden sm:block text-[10px] text-center text-gray-700 font-medium leading-tight flex-shrink-0 px-1">
                                                                    {stamp.pokemon.name}
                                                                </div>
                                                            </div>
                                                        ) : (
                                                            <div 
                                                                className={`w-full h-full bg-gradient-to-br from-yellow-200 to-orange-300 rounded-lg cursor-pointer hover:scale-105 transition-transform flex items-center justify-center border-2 border-yellow-400 ${
                                                                    openingStamps.has(stamp.id) ? 'animate-pulse' : ''
                                                                }`}
                                                                onClick={() => handleStampClick(stamp)}
                                                            >
                                                                {openingStamps.has(stamp.id) ? (
                                                                    <div className="flex flex-col items-center">
                                                                        <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-orange-600 mb-1"></div>
                                                                        <span className="text-xs text-orange-700 font-bold">開封中</span>
                                                                    </div>
                                                                ) : (
                                                                    <div className="flex flex-col items-center">
                                                                        <span className="text-2xl text-orange-600 animate-bounce">？</span>
                                                                        <span className="text-xs text-orange-700 font-bold">タップ</span>
                                                                    </div>
                                                                )}
                                                            </div>
                                                        )}
                                                    </div>
                                                ) : (
                                                    <div className="text-gray-400 text-xs">
                                                        ?
                                                    </div>
                                                )}
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                <div className="mt-8 text-center">
                    <div className="bg-white rounded-xl p-6 shadow-md inline-block">
                        <h3 className="text-lg font-bold text-gray-800 mb-2">
                            🏆 スタンプ収集状況
                        </h3>
                        <div className="text-3xl font-bold text-purple-600">
                            {localStampCards.reduce((total, card) => total + card.stamps.filter(s => s.opened_at).length, 0)} 個
                        </div>
                        <div className="text-sm text-gray-600">
                            開封済み: {localStampCards.reduce((total, card) => total + card.stamps.filter(s => s.opened_at).length, 0)} 個
                        </div>
                        <div className="text-sm text-gray-500">
                            未開封: {localStampCards.reduce((total, card) => total + card.stamps.filter(s => !s.opened_at).length, 0)} 個
                        </div>
                    </div>
                </div>
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

export default StampCards;