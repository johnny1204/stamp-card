import { useEffect, useState } from 'react'

export default function PokemonCelebrationModal({ isOpen, onClose, pokemon, child }) {
    const [showConfetti, setShowConfetti] = useState(false)

    useEffect(() => {
        if (isOpen) {
            setShowConfetti(true)
            // Auto close after 5 seconds
            const timer = setTimeout(() => {
                onClose()
            }, 5000)
            return () => clearTimeout(timer)
        } else {
            setShowConfetti(false)
        }
    }, [isOpen])

    if (!isOpen) return null

    const isLegendary = pokemon?.rarity === 'legendary'
    const isRare = pokemon?.rarity === 'rare'

    return (
        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div className={`bg-white rounded-lg shadow-xl max-w-md w-full transform transition-all duration-300 ${
                isOpen ? 'scale-100 opacity-100' : 'scale-75 opacity-0'
            }`}>
                <div className={`text-center p-8 ${
                    isLegendary ? 'bg-gradient-to-b from-yellow-100 to-yellow-200' :
                    isRare ? 'bg-gradient-to-b from-purple-100 to-purple-200' :
                    'bg-gradient-to-b from-blue-100 to-blue-200'
                }`}>
                    {/* Celebration Animation */}
                    {showConfetti && (
                        <div className="absolute inset-0 overflow-hidden pointer-events-none">
                            {[...Array(20)].map((_, i) => (
                                <div
                                    key={i}
                                    className={`absolute animate-bounce ${
                                        i % 4 === 0 ? 'text-yellow-400' :
                                        i % 4 === 1 ? 'text-blue-400' :
                                        i % 4 === 2 ? 'text-green-400' : 'text-red-400'
                                    }`}
                                    style={{
                                        left: `${Math.random() * 100}%`,
                                        top: `${Math.random() * 100}%`,
                                        animationDelay: `${Math.random() * 2}s`,
                                        animationDuration: `${1 + Math.random()}s`
                                    }}
                                >
                                    ⭐
                                </div>
                            ))}
                        </div>
                    )}

                    <div className="relative z-10">
                        <h2 className={`text-2xl font-bold mb-4 ${
                            isLegendary ? 'text-yellow-800' :
                            isRare ? 'text-purple-800' :
                            'text-blue-800'
                        }`}>
                            🎉 おめでとう！ 🎉
                        </h2>

                        <div className="mb-6">
                            <div className={`text-6xl mb-4 ${showConfetti ? 'animate-pulse' : ''}`}>
                                {isLegendary ? '👑' : isRare ? '💎' : '🎮'}
                            </div>
                            <h3 className="text-xl font-semibold text-gray-800 mb-2">
                                {child?.name}が新しいポケモンをゲット！
                            </h3>
                            <div className={`text-3xl font-bold mb-2 ${
                                isLegendary ? 'text-yellow-600 animate-pulse' :
                                isRare ? 'text-purple-600' :
                                'text-blue-600'
                            }`}>
                                {pokemon?.name || 'ミステリーポケモン'}
                            </div>
                            <div className={`text-sm font-medium px-3 py-1 rounded-full inline-block ${
                                isLegendary ? 'bg-yellow-500 text-white' :
                                isRare ? 'bg-purple-500 text-white' :
                                'bg-blue-500 text-white'
                            }`}>
                                {pokemon?.rarity === 'legendary' ? '🌟 でんせつ 🌟' :
                                 pokemon?.rarity === 'rare' ? '💫 レア 💫' :
                                 pokemon?.rarity === 'uncommon' ? '⭐ アンコモン ⭐' :
                                 '✨ コモン ✨'}
                            </div>
                        </div>

                        {isLegendary && (
                            <div className="mb-4 p-3 bg-yellow-500 text-white rounded-lg">
                                <p className="font-bold">すごい！伝説のポケモンだ！</p>
                                <p className="text-sm">とってもレアなポケモンをゲットしたよ！</p>
                            </div>
                        )}

                        {isRare && (
                            <div className="mb-4 p-3 bg-purple-500 text-white rounded-lg">
                                <p className="font-bold">やったね！レアポケモン！</p>
                                <p className="text-sm">めずらしいポケモンをゲットしたよ！</p>
                            </div>
                        )}

                        <button
                            onClick={onClose}
                            className={`px-6 py-3 rounded-lg text-white font-semibold transition-colors ${
                                isLegendary ? 'bg-yellow-600 hover:bg-yellow-700' :
                                isRare ? 'bg-purple-600 hover:bg-purple-700' :
                                'bg-blue-600 hover:bg-blue-700'
                            }`}
                        >
                            やったー！
                        </button>
                    </div>
                </div>
            </div>
        </div>
    )
}