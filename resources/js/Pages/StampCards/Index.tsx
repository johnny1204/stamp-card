import React, { useState, useEffect, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import PokemonModal from '../../Components/PokemonModal';
import CreateStampModal from '../../Components/CreateStampModal';
import PageHeader from '../../Components/PageHeader';
import { Child, Pokemon } from '../../types';
import { getPokemonImageUrlWithCache } from '../../utils/pokemon';
import { usePreloadPokemonImages } from '../../hooks/useCachedPokemonMedia';

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

interface IndexProps {
    child: Child;
    children: Child[];
    stampCards: StampCard[];
}

const Index: React.FC<IndexProps> = ({ child, children, stampCards }) => {
    const [selectedPokemon, setSelectedPokemon] = useState<Pokemon | null>(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
    const [localStampCards, setLocalStampCards] = useState(stampCards);

    // propsのstampCardsが変更された時にlocalStampCardsを更新
    useEffect(() => {
        setLocalStampCards(stampCards);
    }, [stampCards]);

    // ポケモン画像を事前にキャッシュ
    const pokemonIds = useMemo(() => 
        stampCards.flatMap(card => 
            card.stamps.map(stamp => stamp.pokemon.id)
        ), [stampCards]
    );
    const preloadProgress = usePreloadPokemonImages(pokemonIds);
    
    const handlePokemonClick = (pokemon: Pokemon) => {
        setSelectedPokemon(pokemon);
        setIsModalOpen(true);
    };
    
    const handleStampClick = async (stamp: Stamp) => {
        // 保護者用では開封機能は無効、直接ポケモン表示
        handlePokemonClick(stamp.pokemon);
    };
    
    const handleCloseModal = () => {
        setIsModalOpen(false);
        setSelectedPokemon(null);
    };

    const handleCreateStamp = () => {
        setIsCreateModalOpen(true);
    };

    const handleCreateStampSuccess = () => {
        // Inertia.jsが自動でリダイレクトし、新しいpropsが渡されるので
        // useEffectでlocalStampCardsが自動更新される
    };

    const handleCreateModalClose = () => {
        setIsCreateModalOpen(false);
    };
    
    return (
        <AppLayout title={`${child.name}のスタンプカード`}>
            <Head title={`${child.name}のスタンプカード`} />
            
            <div className="min-h-screen bg-gradient-to-br from-blue-100 via-purple-50 to-pink-100">
                <div className="container mx-auto px-4 py-8">
                    <PageHeader
                        title={`🎉 ${child.name}のスタンプカード 🎉`}
                        subtitle="集めたポケモンをチェックしよう！（保護者用表示：未開封スタンプも表示されます）"
                        child={child}
                        children={children}
                        currentPage="stamp-cards"
                    />

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
                                                        <div className="w-full h-full p-1 sm:p-2 flex flex-col">
                                                            {stamp.opened_at ? (
                                                                <div className="flex flex-col h-full">
                                                                    <div className="flex-1 flex items-center justify-center min-h-0">
                                                                        <img
                                                                            src={getPokemonImageUrlWithCache(stamp.pokemon.id)}
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
                                                                    <div className="hidden md:block text-center text-[10px] text-gray-600 leading-tight px-1 flex-shrink-0">
                                                                        {stamp.pokemon.name}
                                                                    </div>
                                                                </div>
                                                            ) : (
                                                                <div className="relative w-full h-full flex flex-col">
                                                                    <div className="flex-1 flex items-center justify-center min-h-0">
                                                                        <img
                                                                            src={getPokemonImageUrlWithCache(stamp.pokemon.id)}
                                                                            alt={stamp.pokemon.name}
                                                                            className="max-w-full max-h-full object-contain cursor-pointer hover:scale-105 transition-transform opacity-60"
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
                                                                    <div className="hidden md:block text-center text-[10px] text-gray-600 leading-tight px-1 flex-shrink-0">
                                                                        {stamp.pokemon.name}
                                                                    </div>
                                                                    <div className="absolute top-1 right-1 bg-red-500 text-white text-xs px-1 rounded">
                                                                        未開封
                                                                    </div>
                                                                </div>
                                                            )}
                                                        </div>
                                                    ) : (
                                                        <div 
                                                            className="w-full h-full bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg cursor-pointer hover:from-blue-100 hover:to-purple-100 transition-all duration-200 flex flex-col items-center justify-center border-2 border-dashed border-gray-300 hover:border-blue-400"
                                                            onClick={handleCreateStamp}
                                                        >
                                                            <div className="text-3xl sm:text-2xl text-gray-400 hover:text-blue-500 transition-colors">
                                                                ➕
                                                            </div>
                                                            <div className="text-[10px] sm:text-xs text-gray-500 font-medium mt-1 text-center hidden sm:block">
                                                                タップで<br />スタンプ
                                                            </div>
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
                                {localStampCards.reduce((total, card) => total + card.stamps.length, 0)} 個
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

                {/* スタンプ作成モーダル */}
                <CreateStampModal
                    isOpen={isCreateModalOpen}
                    childId={child.id}
                    childName={child.name}
                    onClose={handleCreateModalClose}
                    onSuccess={handleCreateStampSuccess}
                />
            </div>
        </AppLayout>
    );
};

export default Index;