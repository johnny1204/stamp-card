import { Head, useForm } from '@inertiajs/react'
import AppLayout from '../../Layouts/AppLayout'

export default function Create({ child, stampTypes }) {
    const { data, setData, post, processing, errors } = useForm({
        stamp_type_id: '',
        comment: '',
    })
    
    // デバッグモード判定
    const urlParams = new URLSearchParams(window.location.search)
    const isLegendMode = urlParams.get('legend') === '1'

    const handleSubmit = (e) => {
        e.preventDefault()
        
        const url = `/children/${child.id}/stamps${isLegendMode ? '?legend=1' : ''}`
        post(url)
    }

    return (
        <AppLayout>
            <Head title={`${child.name}のスタンプを追加`} />
            
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-md mx-auto">
                    <h1 className="text-3xl font-bold text-gray-800 mb-8 text-center">
                        {child.name}のスタンプを追加
                    </h1>
                    
                    <div className="bg-white rounded-lg shadow-md p-6">
                        {isLegendMode && (
                            <div className="bg-purple-100 border border-purple-300 rounded-lg p-3 mb-4 text-center">
                                <span className="text-purple-800 font-semibold">🌟 デバッグモード: 幻のポケモン確定 🌟</span>
                            </div>
                        )}
                        
                        <div className="text-center mb-6">
                            <div className="text-6xl mb-4">{isLegendMode ? '✨' : '🎉'}</div>
                            <p className="text-gray-600">
                                今日も頑張った{child.name}にスタンプを押してあげましょう！
                                {isLegendMode && (
                                    <>
                                        <br />
                                        <span className="text-purple-600 font-medium">幻のポケモンが出現します！</span>
                                    </>
                                )}
                            </p>
                        </div>
                        
                        <form onSubmit={handleSubmit}>
                            <div className="mb-6">
                                <label htmlFor="stamp_type_id" className="block text-sm font-medium text-gray-700 mb-2">
                                    スタンプの種類 <span className="text-red-500">*</span>
                                </label>
                                <div className="grid grid-cols-2 gap-3">
                                    {stampTypes.map((stampType) => (
                                        <label
                                            key={stampType.id}
                                            className={`border rounded-lg p-3 cursor-pointer transition-all ${
                                                data.stamp_type_id === stampType.id.toString()
                                                    ? 'border-blue-500 bg-blue-50'
                                                    : 'border-gray-300 hover:border-gray-400'
                                            } ${errors.stamp_type_id ? 'border-red-500' : ''}`}
                                        >
                                            <input
                                                type="radio"
                                                name="stamp_type_id"
                                                value={stampType.id}
                                                checked={data.stamp_type_id === stampType.id.toString()}
                                                onChange={(e) => setData('stamp_type_id', e.target.value)}
                                                className="sr-only"
                                            />
                                            <div className="text-center">
                                                <div className="text-2xl mb-1">{stampType.icon}</div>
                                                <div className="text-sm font-medium">{stampType.name}</div>
                                                <div className="text-xs text-gray-500 capitalize">{stampType.category}</div>
                                            </div>
                                        </label>
                                    ))}
                                </div>
                                {errors.stamp_type_id && (
                                    <p className="mt-1 text-sm text-red-500">{errors.stamp_type_id}</p>
                                )}
                            </div>

                            <div className="mb-6">
                                <label htmlFor="comment" className="block text-sm font-medium text-gray-700 mb-2">
                                    コメント (任意)
                                </label>
                                <textarea
                                    id="comment"
                                    value={data.comment}
                                    onChange={(e) => setData('comment', e.target.value)}
                                    className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                        errors.comment ? 'border-red-500' : 'border-gray-300'
                                    }`}
                                    placeholder="今日何を頑張ったかな？"
                                    rows="3"
                                />
                                {errors.comment && (
                                    <p className="mt-1 text-sm text-red-500">{errors.comment}</p>
                                )}
                            </div>

                            <div className="flex space-x-4">
                                <button
                                    type="submit"
                                    disabled={processing || !data.stamp_type_id}
                                    className={`flex-1 ${
                                        isLegendMode 
                                            ? 'bg-purple-500 hover:bg-purple-600' 
                                            : 'bg-blue-500 hover:bg-blue-600'
                                    } text-white py-3 px-4 rounded-lg transition-colors disabled:opacity-50 font-semibold`}
                                >
                                    {processing ? 'スタンプ中...' : (isLegendMode ? '幻のスタンプを押す！✨' : 'スタンプを押す！')}
                                </button>
                                <a
                                    href={`/children/${child.id}/stamps`}
                                    className="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-3 px-4 rounded-lg text-center transition-colors"
                                >
                                    キャンセル
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    )
}