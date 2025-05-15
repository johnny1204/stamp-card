import { Head, Link } from '@inertiajs/react'
import AppLayout from '../../Layouts/AppLayout'
import PageHeader from '@/Components/PageHeader'

export default function Show({ child, children, todayStampsCount, thisMonthStampsCount, childUrls }) {
    const childData = child.data
    return (
        <AppLayout>
            <Head title={`${childData.name}の詳細`} />
            
            <div className="bg-gradient-to-br from-blue-50 to-purple-50 p-4">
                <div className="max-w-6xl mx-auto">
                    <PageHeader
                        title={`${childData.name}の管理`}
                        subtitle="子どもの基本情報と管理機能"
                        child={childData}
                        children={children}
                        currentPage="children"
                        actions={
                            <div className="flex space-x-2">
                                <Link 
                                    href={`/children/${childData.id}/edit`}
                                    className="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors"
                                >
                                    編集
                                </Link>
                                <Link 
                                    href="/children"
                                    className="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors"
                                >
                                    一覧に戻る
                                </Link>
                            </div>
                        }
                    />

                    <div className="max-w-2xl mx-auto">
                        <div className="bg-white rounded-lg shadow-md p-8">

                            {/* 基本情報 */}
                            <div className="mb-8">
                                <div className="flex items-center justify-between mb-6">
                                    <div>
                                        <h2 className="text-2xl font-bold text-gray-800">{childData.name}</h2>
                                        <div className="text-lg text-gray-600 mt-1">
                                            {childData.age !== null && childData.age !== undefined ? `${childData.age}歳` : '年齢未設定'}
                                            {childData.birth_date && (
                                                <span className="text-sm text-gray-500 ml-3">
                                                    生年月日: {new Date(childData.birth_date).toLocaleDateString('ja-JP')}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                </div>

                                {/* 統計情報 */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
                                    <div className="bg-blue-50 rounded-lg p-6 text-center">
                                        <h3 className="text-lg font-semibold text-blue-800 mb-2">今日のスタンプ</h3>
                                        <p className="text-3xl font-bold text-blue-600">{todayStampsCount}個</p>
                                    </div>
                                    <div className="bg-green-50 rounded-lg p-6 text-center">
                                        <h3 className="text-lg font-semibold text-green-800 mb-2">今月のスタンプ</h3>
                                        <p className="text-3xl font-bold text-green-600">{thisMonthStampsCount}個</p>
                                    </div>
                                </div>
                            </div>

                            {/* 主要なアクション */}
                            <div className="space-y-4">
                                <h3 className="text-lg font-semibold text-gray-800">管理機能</h3>
                                
                                <div className="grid grid-cols-1 gap-3">
                                    <Link 
                                        href={`/children/${childData.id}/stamps/create`}
                                        className="bg-blue-500 hover:bg-blue-600 text-white px-6 py-4 rounded-lg transition-colors text-center font-medium"
                                    >
                                        🎯 スタンプを押す
                                    </Link>
                                    
                                    <Link 
                                        href={`/children/${childData.id}/stamps`}
                                        className="bg-green-500 hover:bg-green-600 text-white px-6 py-4 rounded-lg transition-colors text-center font-medium"
                                    >
                                        📋 スタンプ一覧を見る
                                    </Link>

                                    <Link 
                                        href={`/children/${childData.id}/edit`}
                                        className="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-4 rounded-lg transition-colors text-center font-medium"
                                    >
                                        ✏️ 子どもの情報を編集
                                    </Link>
                                </div>

                                <div className="mt-6 pt-6 border-t">
                                    <p className="text-sm text-gray-600 text-center mb-4">
                                        📱 その他の機能（スタンプカード、カレンダー、統計、目標設定など）は上部のメニューからアクセスできます
                                    </p>
                                </div>
                            </div>

                            {/* 子ども用専用リンク */}
                            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mt-6">
                                <h3 className="text-lg font-semibold text-yellow-800 mb-3">
                                    🔗 子ども用専用リンク（ログイン不要）
                                </h3>
                                <p className="text-sm text-yellow-700 mb-4">
                                    これらのリンクを子どもと共有することで、ログインなしでスタンプを確認できます
                                </p>
                                <div className="space-y-3">
                                    <div>
                                        <label className="block text-sm font-medium text-yellow-800 mb-1">
                                            スタンプカード
                                        </label>
                                        <div className="flex">
                                            <input 
                                                type="text" 
                                                value={childUrls?.stamp_cards || ''} 
                                                readOnly 
                                                className="flex-1 px-3 py-2 border border-yellow-300 rounded-l-md bg-white text-sm"
                                            />
                                            <button 
                                                onClick={() => navigator.clipboard.writeText(childUrls?.stamp_cards || '')}
                                                className="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-r-md text-sm"
                                            >
                                                コピー
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label className="block text-sm font-medium text-yellow-800 mb-1">
                                            スタンプ一覧
                                        </label>
                                        <div className="flex">
                                            <input 
                                                type="text" 
                                                value={childUrls?.stamps || ''} 
                                                readOnly 
                                                className="flex-1 px-3 py-2 border border-yellow-300 rounded-l-md bg-white text-sm"
                                            />
                                            <button 
                                                onClick={() => navigator.clipboard.writeText(childUrls?.stamps || '')}
                                                className="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-r-md text-sm"
                                            >
                                                コピー
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label className="block text-sm font-medium text-yellow-800 mb-1">
                                            今日のスタンプ
                                        </label>
                                        <div className="flex">
                                            <input 
                                                type="text" 
                                                value={childUrls?.today_stamps || ''} 
                                                readOnly 
                                                className="flex-1 px-3 py-2 border border-yellow-300 rounded-l-md bg-white text-sm"
                                            />
                                            <button 
                                                onClick={() => navigator.clipboard.writeText(childUrls?.today_stamps || '')}
                                                className="px-3 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-r-md text-sm"
                                            >
                                                コピー
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    )
}