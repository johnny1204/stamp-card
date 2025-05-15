import { Head, useForm } from '@inertiajs/react'
import AppLayout from '../../Layouts/AppLayout'

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        birth_date: '',
        target_stamps: '10',
    })

    const handleSubmit = (e) => {
        e.preventDefault()
        post('/children')
    }

    return (
        <AppLayout>
            <Head title="子ども登録" />
            
            <div className="container mx-auto px-4 py-8">
                <div className="max-w-md mx-auto">
                    <h1 className="text-3xl font-bold text-gray-800 mb-8 text-center">新しい子どもを登録</h1>
                    
                    <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow-md p-6">
                        <div className="mb-6">
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700 mb-2">
                                名前 <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.name ? 'border-red-500' : 'border-gray-300'
                                }`}
                                placeholder="例: 太郎"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-500">{errors.name}</p>
                            )}
                        </div>

                        <div className="mb-6">
                            <label htmlFor="birth_date" className="block text-sm font-medium text-gray-700 mb-2">
                                誕生日 <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                id="birth_date"
                                value={data.birth_date}
                                onChange={(e) => setData('birth_date', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.birth_date ? 'border-red-500' : 'border-gray-300'
                                }`}
                                max={new Date().toISOString().split('T')[0]}
                                min={new Date(Date.now() - 20 * 365 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]}
                            />
                            <p className="mt-1 text-sm text-gray-500">
                                年齢は誕生日から自動で計算されます
                            </p>
                            {errors.birth_date && (
                                <p className="mt-1 text-sm text-red-500">{errors.birth_date}</p>
                            )}
                        </div>

                        <div className="mb-6">
                            <label htmlFor="target_stamps" className="block text-sm font-medium text-gray-700 mb-2">
                                目標スタンプ数 <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="number"
                                id="target_stamps"
                                min="5"
                                max="50"
                                value={data.target_stamps}
                                onChange={(e) => setData('target_stamps', e.target.value)}
                                className={`w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 ${
                                    errors.target_stamps ? 'border-red-500' : 'border-gray-300'
                                }`}
                                placeholder="例: 10"
                            />
                            <p className="mt-1 text-sm text-gray-500">
                                スタンプカード1枚を完成させるのに必要なスタンプ数
                            </p>
                            {errors.target_stamps && (
                                <p className="mt-1 text-sm text-red-500">{errors.target_stamps}</p>
                            )}
                        </div>

                        <div className="flex space-x-4">
                            <button
                                type="submit"
                                disabled={processing}
                                className="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition-colors disabled:opacity-50"
                            >
                                {processing ? '登録中...' : '登録する'}
                            </button>
                            <a
                                href="/children"
                                className="flex-1 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg text-center transition-colors"
                            >
                                キャンセル
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    )
}