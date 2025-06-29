import React, { useEffect, useRef, useState } from 'react';
import { getPokemonImageUrl } from '../utils/pokemon';
import { useCachedPokemonMedia } from '../hooks/useCachedPokemonMedia';

// 紙吹雪エフェクト用の型定義
interface Confetti {
    x: number;
    y: number;
    rotation: number;
    scale: number;
    velocity: { x: number; y: number };
    color: string;
}

interface Pokemon {
    id: number;
    name: string;
    type1?: string;
    type2?: string;
    genus?: string;
    is_legendary: boolean;
    is_mythical: boolean;
}

interface PokemonModalProps {
    isOpen: boolean;
    pokemon: Pokemon | null;
    childName: string;
    onClose: () => void;
}

const PokemonModal: React.FC<PokemonModalProps> = ({ isOpen, pokemon, childName, onClose }) => {
    const audioRef = useRef<HTMLAudioElement>(null);
    const canvasRef = useRef<HTMLCanvasElement>(null);
    const animationRef = useRef<number>();
    const [confetti, setConfetti] = useState<Confetti[]>([]);
    
    // キャッシュ付きメディア取得（鳴き声も事前読み込み）
    const { imageUrl, isImageLoading, loadCry, isCryLoading, cryUrl } = useCachedPokemonMedia(
        pokemon?.id || null, 
        true // 鳴き声も事前に読み込む（MP3ファイルなので高速）
    );

    // 紙吹雪を生成する関数
    const createConfetti = () => {
        const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#f0932b', '#eb4d4b', '#6c5ce7'];
        const newConfetti: Confetti[] = [];
        
        for (let i = 0; i < 50; i++) {
            newConfetti.push({
                x: Math.random() * window.innerWidth,
                y: -10,
                rotation: Math.random() * 360,
                scale: Math.random() * 0.5 + 0.5,
                velocity: {
                    x: (Math.random() - 0.5) * 4,
                    y: Math.random() * 3 + 2
                },
                color: colors[Math.floor(Math.random() * colors.length)]
            });
        }
        
        setConfetti(newConfetti);
    };

    // 紙吹雪アニメーション
    const animateConfetti = () => {
        setConfetti(prev => {
            const updated = prev.map(particle => ({
                ...particle,
                x: particle.x + particle.velocity.x,
                y: particle.y + particle.velocity.y,
                rotation: particle.rotation + 5
            })).filter(particle => particle.y < window.innerHeight + 10);
            
            if (updated.length === 0 && animationRef.current) {
                cancelAnimationFrame(animationRef.current);
                return [];
            }
            
            animationRef.current = requestAnimationFrame(animateConfetti);
            return updated;
        });
    };

    useEffect(() => {
        if (isOpen && pokemon) {
            console.log('モーダル開封 - ポケモン:', pokemon.name, pokemon.id);
            
            // UIの応答性を確保するため、重い処理を遅延実行
            setTimeout(() => {
                // 伝説のポケモンまたは幻のポケモンの場合、紙吹雪エフェクトを開始
                if (pokemon.is_legendary || pokemon.is_mythical) {
                    setTimeout(() => {
                        createConfetti();
                        animateConfetti();
                    }, 300);
                }
                
                // 鳴き声の自動読み込みと再生は別のuseEffectで処理
                // ここでは紙吹雪のみ
            }, 100); // 100ms後に重い処理を開始
            
            return () => {
                if (animationRef.current) {
                    cancelAnimationFrame(animationRef.current);
                }
            };
        } else {
            setConfetti([]);
            if (animationRef.current) {
                cancelAnimationFrame(animationRef.current);
            }
        }
    }, [isOpen, pokemon]);

    // 鳴き声URLが取得されたら自動再生を試行
    useEffect(() => {
        if (isOpen && cryUrl && audioRef.current) {
            const audio = audioRef.current;
            audio.src = cryUrl;
            audio.load();
            
            const playAudio = () => {
                audio.play().catch(() => {
                    // 自動再生失敗は無視（手動再生ボタンで再生可能）
                });
            };
            
            // 少し遅延させてから再生を試行
            setTimeout(() => {
                if (audio.readyState >= 3) {
                    playAudio();
                } else {
                    audio.addEventListener('canplaythrough', playAudio, { once: true });
                }
            }, 200);
        }
    }, [isOpen, cryUrl]);

    useEffect(() => {
        // ESCキーでモーダルを閉じる（最優先で設定）
        const handleEscape = (e: KeyboardEvent) => {
            if (e.key === 'Escape') {
                console.log('ESC key pressed, closing modal');
                onClose();
            }
        };

        if (isOpen) {
            // イベントリスナーを即座に登録
            document.addEventListener('keydown', handleEscape);
            document.body.style.overflow = 'hidden';
            console.log('Modal opened, event listeners attached');
        } else {
            document.body.style.overflow = 'unset';
        }

        return () => {
            document.removeEventListener('keydown', handleEscape);
            document.body.style.overflow = 'unset';
            console.log('Modal event listeners cleaned up');
        };
    }, [isOpen]);

    if (!isOpen || !pokemon) {
        return null;
    }

    const getRarityText = () => {
        if (pokemon.is_mythical) return '幻のポケモン';
        if (pokemon.is_legendary) return '伝説ポケモン';
        return 'ポケモン';
    };

    const getRarityColor = () => {
        if (pokemon.is_mythical) return 'from-purple-400 to-pink-400';
        if (pokemon.is_legendary) return 'from-yellow-400 to-orange-400';
        return 'from-blue-400 to-green-400';
    };

    const getBackgroundEffect = () => {
        if (pokemon.is_mythical) return 'bg-gradient-to-br from-purple-100 to-pink-100';
        if (pokemon.is_legendary) return 'bg-gradient-to-br from-yellow-100 to-orange-100';
        return 'bg-gradient-to-br from-blue-100 to-green-100';
    };

    const getTypeColor = (type: string): string => {
        const typeColors: { [key: string]: string } = {
            'ノーマル': '#A8A878',
            'ほのお': '#F08030',
            'みず': '#6890F0',
            'でんき': '#F8D030',
            'くさ': '#78C850',
            'こおり': '#98D8D8',
            'かくとう': '#C03028',
            'どく': '#A040A0',
            'じめん': '#E0C068',
            'ひこう': '#A890F0',
            'エスパー': '#F85888',
            'むし': '#A8B820',
            'いわ': '#B8A038',
            'ゴースト': '#705898',
            'ドラゴン': '#7038F8',
            'あく': '#705848',
            'はがね': '#B8B8D0',
            'フェアリー': '#EE99AC'
        };
        return typeColors[type] || '#68A090';
    };

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            {/* バックドロップ */}
            <div 
                className="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"
                onClick={(e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    console.log('PokemonModal backdrop clicked');
                    onClose();
                }}
            />
            
            {/* モーダルコンテンツ */}
            <div className={`relative max-w-md w-full mx-4 ${getBackgroundEffect()} rounded-2xl shadow-2xl transform transition-all duration-300 scale-100`}>
                {/* ヘッダー */}
                <div className={`bg-gradient-to-r ${getRarityColor()} p-6 rounded-t-2xl text-white text-center relative overflow-hidden`}>
                    {/* 背景パターン */}
                    <div className="absolute inset-0 opacity-20">
                        <div className="absolute inset-0 bg-repeat" style={{
                            backgroundImage: 'radial-gradient(circle at 50% 50%, white 1px, transparent 1px)',
                            backgroundSize: '20px 20px'
                        }} />
                    </div>
                    
                    <button
                        onClick={(e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            console.log('PokemonModal close button clicked');
                            // 即座に実行
                            requestAnimationFrame(() => {
                                onClose();
                            });
                        }}
                        className="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors z-50 bg-black bg-opacity-20 rounded-full p-1 hover:bg-opacity-40"
                        type="button"
                    >
                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    
                    <div className="relative z-10">
                        <h2 className="text-2xl font-bold mb-2">🎉 {getRarityText()}ゲット！</h2>
                        <p className="text-lg opacity-90">{childName}ちゃん、おめでとう！</p>
                    </div>
                </div>

                {/* ポケモン画像 */}
                <div className="p-8 text-center">
                    <div className="relative mb-6">
                        {isImageLoading && (
                            <div className="w-48 h-48 mx-auto flex items-center justify-center absolute inset-0">
                                <img
                                    src={(() => {
                                        console.log('ローディング中プレースホルダー:', {
                                            name: pokemon.name,
                                            is_legendary: pokemon.is_legendary,
                                            is_mythical: pokemon.is_mythical,
                                            ball: pokemon.is_legendary || pokemon.is_mythical ? 'master-ball' : 'poke-ball'
                                        });
                                        return pokemon.is_legendary || pokemon.is_mythical ? '/images/master-ball.png' : '/images/poke-ball.png';
                                    })()}
                                    alt="読み込み中"
                                    className="w-32 h-32 object-contain animate-bounce opacity-70"
                                />
                            </div>
                        )}
                        <img
                            key={pokemon.id}
                            src={imageUrl}
                            alt={pokemon.name}
                            className={`w-48 h-48 mx-auto object-contain drop-shadow-lg transition-opacity duration-300 ${
                                !isImageLoading ? 'opacity-100' : 'opacity-0'
                            }`}
                            onError={(e) => {
                                console.error('画像読み込みエラー:', pokemon.name, pokemon.id, e.currentTarget.src);
                                console.log('ポケモンレアリティ情報:', {
                                    name: pokemon.name,
                                    is_legendary: pokemon.is_legendary,
                                    is_mythical: pokemon.is_mythical,
                                    pokemon: pokemon
                                });
                                e.currentTarget.src = pokemon.is_legendary || pokemon.is_mythical 
                                    ? '/images/master-ball.png' 
                                    : '/images/poke-ball.png';
                            }}
                        />
                        
                        {/* レアリティエフェクト */}
                        {!isImageLoading && (pokemon.is_legendary || pokemon.is_mythical) && (
                            <div className="absolute inset-0 pointer-events-none">
                                <div className={`w-48 h-48 mx-auto rounded-full bg-gradient-to-r ${getRarityColor()} opacity-20 animate-pulse`} />
                            </div>
                        )}
                    </div>

                    {/* ポケモン名 */}
                    <h3 className="text-3xl font-bold text-gray-800 mb-2">{pokemon.name}</h3>
                    
                    {/* 分類 */}
                    {pokemon.genus && (
                        <p className="text-lg text-gray-600 mb-3">{pokemon.genus}</p>
                    )}
                    
                    {/* タイプ */}
                    <div className="flex justify-center space-x-2 mb-4">
                        {pokemon.type1 && (
                            <span 
                                className="px-3 py-1 rounded-full text-white text-sm font-semibold"
                                style={{ backgroundColor: getTypeColor(pokemon.type1) }}
                            >
                                {pokemon.type1}
                            </span>
                        )}
                        {pokemon.type2 && (
                            <span 
                                className="px-3 py-1 rounded-full text-white text-sm font-semibold"
                                style={{ backgroundColor: getTypeColor(pokemon.type2) }}
                            >
                                {pokemon.type2}
                            </span>
                        )}
                    </div>

                    {/* アクションボタン */}
                    <div className="space-y-3">
                        <button
                            onClick={async (e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                if (audioRef.current) {
                                    try {
                                        // 音声を停止してリセット
                                        const audio = audioRef.current;
                                        audio.pause();
                                        audio.currentTime = 0;
                                        
                                        // 事前読み込み済みのcryUrlを使用、なければloadCryで取得
                                        const finalCryUrl = cryUrl || await loadCry();
                                        audio.src = finalCryUrl;
                                        
                                        // 音声を再読み込み
                                        audio.load();
                                        
                                        // 音声が読み込まれるまで待機してから再生
                                        const playWhenReady = () => {
                                            console.log('音声ファイルURL:', cryUrl);
                                            console.log('Audio要素の状態:', {
                                                src: audio.src,
                                                readyState: audio.readyState,
                                                duration: audio.duration,
                                                networkState: audio.networkState
                                            });
                                            
                                            audio.play()
                                                .then(() => {
                                                    console.log('鳴き声再生成功');
                                                })
                                                .catch((playError) => {
                                                    console.error('鳴き声再生エラー詳細:', {
                                                        error: playError,
                                                        audioSrc: audio.src,
                                                        audioReadyState: audio.readyState,
                                                        audioNetworkState: audio.networkState
                                                    });
                                                    
                                                    // より詳細なエラーメッセージ
                                                    const errorMsg = playError instanceof Error ? playError.message : String(playError);
                                                    alert(`音声の再生に失敗しました。\nエラー: ${errorMsg}\n音声URL: ${cryUrl}`);
                                                });
                                        };
                                        
                                        // 音声が準備できたら再生
                                        if (audio.readyState >= 3) { // HAVE_FUTURE_DATA以上
                                            playWhenReady();
                                        } else {
                                            audio.addEventListener('canplaythrough', playWhenReady, { once: true });
                                            
                                            // 5秒でタイムアウト
                                            setTimeout(() => {
                                                audio.removeEventListener('canplaythrough', playWhenReady);
                                                if (audio.readyState < 3) {
                                                    alert('音声ファイルの読み込みがタイムアウトしました。ネットワーク接続を確認してください。');
                                                }
                                            }, 5000);
                                        }
                                        
                                    } catch (error) {
                                        console.error('鳴き声読み込みエラー:', error);
                                        alert('音声ファイルの読み込みに失敗しました。');
                                    }
                                }
                            }}
                            disabled={isCryLoading}
                            className="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold transition-colors flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.617.816l-4.714-3.535A1 1 0 013 12.465v-4.93a1 1 0 01.669-.815l4.714-3.535zM15 5a1 1 0 011 1v8a1 1 0 11-2 0V6a1 1 0 011-1z" clipRule="evenodd" />
                            </svg>
                            <span>{isCryLoading ? '読み込み中...' : '鳴き声を聞く'}</span>
                        </button>
                        
                        <button
                            onClick={(e) => {
                                e.preventDefault();
                                e.stopPropagation();
                                console.log('PokemonModal close button (bottom) clicked');
                                // 即座に実行
                                requestAnimationFrame(() => {
                                    onClose();
                                });
                            }}
                            className="w-full bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 rounded-lg font-semibold transition-colors"
                            type="button"
                        >
                            閉じる
                        </button>
                    </div>
                </div>
            </div>

            {/* 紙吹雪エフェクト */}
            {confetti.length > 0 && (
                <div className="fixed inset-0 pointer-events-none z-60">
                    {confetti.map((particle, index) => (
                        <div
                            key={index}
                            className="absolute w-2 h-2 rounded"
                            style={{
                                left: particle.x,
                                top: particle.y,
                                backgroundColor: particle.color,
                                transform: `rotate(${particle.rotation}deg) scale(${particle.scale})`,
                                transition: 'none'
                            }}
                        />
                    ))}
                </div>
            )}

            {/* 音声要素 */}
            <audio
                ref={audioRef}
                preload="metadata"
                playsInline
                muted={false}
                controls={false}
            />
        </div>
    );
};

export default PokemonModal;