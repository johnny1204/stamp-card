import { Head, Link } from '@inertiajs/react'
import { useState, useEffect, useMemo, useCallback } from 'react'
import AppLayout from '../../Layouts/AppLayout'
import PokemonModal from '../../Components/PokemonModal'
import PageHeader from '../../Components/PageHeader'
import { Child, Stamp, PageProps } from '../../types'
import { getPokemonImageUrl } from '../../utils/pokemon'
import { usePreloadPokemonImages } from '../../hooks/useCachedPokemonMedia'

interface IndexProps {
    child: Child;
    children: Child[];
    stamps: { data: Stamp[] };
    flash?: PageProps['flash'];
}

export default function Index({ child, children, stamps, flash }: IndexProps) {
    const [showCelebration, setShowCelebration] = useState(false)
    const [selectedPokemon, setSelectedPokemon] = useState<any>(null)
    const [isModalOpen, setIsModalOpen] = useState(false)
    const [newStampData, setNewStampData] = useState<PageProps['flash']['newStamp'] | null>(null)

    useEffect(() => {
        if (flash?.newStamp) {
            setNewStampData(flash.newStamp)
            setShowCelebration(true)
        }
    }, [flash])

    const handlePokemonClick = (pokemon: any) => {
        setSelectedPokemon(pokemon)
        setIsModalOpen(true)
    }

    const handleCloseModal = useCallback(() => {
        setIsModalOpen(false)
        setSelectedPokemon(null)
    }, [])

    // スタンプを日付順（新しい順）でソート
    const sortedStamps = [...stamps.data].sort((a, b) => 
        new Date(b.stamped_at).getTime() - new Date(a.stamped_at).getTime()
    )

    // ポケモン画像を事前にキャッシュ
    const pokemonIds = useMemo(() => 
        stamps.data
            .filter(stamp => stamp.pokemon)
            .map(stamp => stamp.pokemon!.id), [stamps.data]
    );
    const preloadProgress = usePreloadPokemonImages(pokemonIds)
    return (
        <AppLayout>
            <Head title={`${child.name}のスタンプ一覧`} />
            
            <div className="container mx-auto px-4 py-8">
                <PageHeader
                    title={`${child.name}のスタンプ`}
                    subtitle="これまでに集めたスタンプ"
                    child={child}
                    children={children}
                    currentPage="stamps"
                    actions={
                        <div className="flex space-x-2">
                            <Link 
                                href={`/children/${child.id}/stamps/create`}
                                className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                新しいスタンプ
                            </Link>
                        </div>
                    }
                />

                {stamps.data.length === 0 ? (
                    <div className="text-center py-12">
                        <div className="text-8xl mb-4">📝</div>
                        <p className="text-gray-500 text-lg mb-4">まだスタンプがありません</p>
                        <Link 
                            href={`/children/${child.id}/stamps/create`}
                            className="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors"
                        >
                            最初のスタンプを付ける
                        </Link>
                    </div>
                ) : (
                    <div className="bg-white rounded-xl shadow-md overflow-hidden">
                        <div className="bg-gradient-to-r from-blue-500 to-purple-600 p-4">
                            <h3 className="text-xl font-bold text-white text-center">
                                📋 スタンプ履歴 ({stamps.data.length}件)
                            </h3>
                        </div>
                        <div className="max-h-[600px] overflow-y-auto">
                            {sortedStamps.map((stamp, index) => (
                                <div 
                                    key={stamp.id}
                                    className={`p-4 border-b border-gray-100 hover:bg-gray-50 transition-colors ${
                                        index === 0 ? 'bg-blue-50' : ''
                                    }`}
                                >
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center space-x-4">
                                                {/* ポケモン画像 */}
                                                <div className="w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-200 flex-shrink-0 bg-gray-50">
                                                    {stamp.pokemon ? (
                                                        <img
                                                            src={getPokemonImageUrl(stamp.pokemon.id)}
                                                            alt={stamp.pokemon.name}
                                                            className="w-full h-full object-contain cursor-pointer hover:scale-105 transition-transform"
                                                            onClick={() => handlePokemonClick(stamp.pokemon)}
                                                            onError={(e) => {
                                                                const pokemon = stamp.pokemon;
                                                                if (pokemon && (pokemon.is_legendary || pokemon.is_mythical)) {
                                                                    e.currentTarget.src = '/images/master-ball.png';
                                                                } else {
                                                                    e.currentTarget.src = '/images/poke-ball.png';
                                                                }
                                                            }}
                                                        />
                                                    ) : (
                                                        <div className="w-full h-full flex items-center justify-center text-gray-400">
                                                            🎮
                                                        </div>
                                                    )}
                                                </div>
                                                
                                                {/* スタンプ情報 */}
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center space-x-2 mb-2">
                                                        {stamp.stamp_type && (
                                                            <span 
                                                                className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium text-white"
                                                                style={{ backgroundColor: stamp.stamp_type.color || '#6B7280' }}
                                                            >
                                                                {stamp.stamp_type.icon} {stamp.stamp_type.name}
                                                            </span>
                                                        )}
                                                        {index === 0 && (
                                                            <span className="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                                最新
                                                            </span>
                                                        )}
                                                    </div>
                                                    
                                                    <div className="text-sm text-gray-600 mb-2">
                                                        {new Date(stamp.stamped_at).toLocaleDateString('ja-JP', {
                                                            year: 'numeric',
                                                            month: 'long',
                                                            day: 'numeric',
                                                            weekday: 'short'
                                                        })} {new Date(stamp.stamped_at).toLocaleTimeString('ja-JP', {
                                                            hour: '2-digit',
                                                            minute: '2-digit'
                                                        })}
                                                    </div>
                                                    
                                                    {stamp.comment && (
                                                        <div className="text-sm text-gray-800 bg-gray-50 p-3 rounded border-l-4 border-blue-400">
                                                            💬 {stamp.comment}
                                                        </div>
                                                    )}
                                                </div>
                                                
                                                {/* アクション */}
                                                <div className="flex-shrink-0">
                                                    <Link 
                                                        href={`/children/${child.id}/stamps/${stamp.id}`}
                                                        className="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors"
                                                    >
                                                        詳細
                                                    </Link>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* お祝いモーダル */}
            <PokemonModal
                isOpen={showCelebration}
                onClose={() => setShowCelebration(false)}
                pokemon={newStampData?.pokemon || null}
                childName={newStampData?.child?.name || ''}
            />

            {/* ポケモン詳細モーダル */}
            <PokemonModal
                isOpen={isModalOpen}
                pokemon={selectedPokemon}
                childName={child.name}
                onClose={handleCloseModal}
            />
        </AppLayout>
    )
}