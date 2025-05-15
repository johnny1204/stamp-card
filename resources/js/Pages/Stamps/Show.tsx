import { Head, Link } from '@inertiajs/react'
import AppLayout from '../../Layouts/AppLayout'
import { getPokemonImageUrl } from '../../utils/pokemon'

export default function Show({ child, stamp }) {
    const stampData = stamp.data

    return (
        <AppLayout>
            <Head title={`${child.name}のスタンプ詳細`} />
            
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-2xl mx-auto">
                    <div className="bg-white rounded-lg shadow-md p-8">
                        <div className="text-center mb-8">
                            <div className="mb-4 flex justify-center">
                                {stampData.pokemon ? (
                                    <img
                                        src={getPokemonImageUrl(stampData.pokemon.id)}
                                        alt={stampData.pokemon.name}
                                        className="w-32 h-32 object-contain drop-shadow-lg"
                                        onError={(e) => {
                                            const pokemon = stampData.pokemon;
                                            if (pokemon && (pokemon.is_legendary || pokemon.is_mythical)) {
                                                e.currentTarget.src = '/images/master-ball.png';
                                            } else {
                                                e.currentTarget.src = '/images/poke-ball.png';
                                            }
                                        }}
                                    />
                                ) : (
                                    <div className="w-32 h-32 flex items-center justify-center text-8xl">⭐</div>
                                )}
                            </div>
                            <h1 className="text-3xl font-bold text-gray-800 mb-2">
                                {stampData.pokemon ? stampData.pokemon.name : 'ポケモン'}スタンプ
                            </h1>
                            <p className="text-xl text-gray-600">
                                {new Date(stampData.stamped_at).toLocaleDateString('ja-JP')}
                            </p>
                        </div>

                        <div className="space-y-6">
                            <div className="bg-blue-50 rounded-lg p-6">
                                <h3 className="text-lg font-semibold text-blue-800 mb-2">獲得したポケモン</h3>
                                <div className="flex items-center space-x-3">
                                    <div className="flex-shrink-0">
                                        {stampData.pokemon ? (
                                            <img
                                                src={getPokemonImageUrl(stampData.pokemon.id)}
                                                alt={stampData.pokemon.name}
                                                className="w-16 h-16 object-contain"
                                                onError={(e) => {
                                                    const pokemon = stampData.pokemon;
                                                    if (pokemon && (pokemon.is_legendary || pokemon.is_mythical)) {
                                                        e.currentTarget.src = '/images/master-ball.png';
                                                    } else {
                                                        e.currentTarget.src = '/images/poke-ball.png';
                                                    }
                                                }}
                                            />
                                        ) : (
                                            <div className="w-16 h-16 flex items-center justify-center text-2xl">🎮</div>
                                        )}
                                    </div>
                                    <div>
                                        <p className="font-semibold text-blue-700">
                                            {stampData.pokemon ? stampData.pokemon.name : 'ポケモン'}
                                        </p>
                                        {stampData.pokemon && (
                                            <p className="text-sm text-blue-600">
                                                ID: #{stampData.pokemon.id.toString().padStart(3, '0')}
                                            </p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {stampData.comment && (
                                <div className="bg-green-50 rounded-lg p-6">
                                    <h3 className="text-lg font-semibold text-green-800 mb-2">コメント</h3>
                                    <p className="text-green-700">{stampData.comment}</p>
                                </div>
                            )}

                            <div className="bg-gray-50 rounded-lg p-6">
                                <h3 className="text-lg font-semibold text-gray-800 mb-4">スタンプ情報</h3>
                                <div className="space-y-3">
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">子ども:</span>
                                        <span className="font-medium">{child.name}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">取得日時:</span>
                                        <span className="font-medium">
                                            {new Date(stampData.stamped_at).toLocaleString('ja-JP')}
                                        </span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-gray-600">スタンプID:</span>
                                        <span className="font-medium text-gray-500">#{stampData.id}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div className="flex space-x-4 mt-8">
                            <Link 
                                href={`/children/${child.id}/stamps`}
                                className="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 px-4 rounded-lg text-center transition-colors"
                            >
                                スタンプ一覧に戻る
                            </Link>
                            <Link 
                                href={`/children/${child.id}`}
                                className="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 px-4 rounded-lg text-center transition-colors"
                            >
                                子どもの詳細に戻る
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    )
}