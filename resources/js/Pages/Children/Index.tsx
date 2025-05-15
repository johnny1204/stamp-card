import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';
import { Child, PaginatedResponse } from '../../types';

interface IndexProps {
    children: PaginatedResponse<Child>;
}

const Index: React.FC<IndexProps> = ({ children }) => {
    return (
        <AppLayout>
            <Head title="子ども一覧" />
            
            <div className="bg-gradient-to-br from-blue-50 to-purple-50 p-4">
                <div className="max-w-6xl mx-auto">
                    <PageHeader
                        title="子ども管理"
                        subtitle="登録されている子どもの一覧と管理"
                        child={null}
                        children={children.data}
                        currentPage="children"
                        actions={
                            <Link 
                                href="/children/create"
                                className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                新しい子どもを登録
                            </Link>
                        }
                    />

                    {children.data.length === 0 ? (
                        <div className="bg-white rounded-lg shadow-md p-12 text-center">
                            <div className="text-6xl mb-4">👶</div>
                            <h3 className="text-xl font-semibold text-gray-700 mb-2">まだ子どもが登録されていません</h3>
                            <p className="text-gray-500 mb-6">最初の子どもを登録して、スタンプ帳を始めましょう！</p>
                            <Link 
                                href="/children/create"
                                className="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-lg transition-colors font-medium"
                            >
                                最初の子どもを登録する
                            </Link>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {children.data.map((child) => (
                                <div key={child.id} className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow">
                                    <div className="text-center mb-6">
                                        <div className="text-4xl mb-2">👶</div>
                                        <h3 className="text-xl font-bold text-gray-800">{child.name}</h3>
                                        <div className="text-sm text-gray-500 mt-1">
                                            {child.age !== null && child.age !== undefined ? `${child.age}歳` : '年齢未設定'}
                                        </div>
                                    </div>
                                    
                                    <div className="grid grid-cols-2 gap-4 mb-6">
                                        <div className="bg-blue-50 rounded-lg p-3 text-center">
                                            <div className="text-sm text-blue-600 mb-1">今日</div>
                                            <div className="text-lg font-bold text-blue-800">{child.todayStampsCount || 0}個</div>
                                        </div>
                                        <div className="bg-green-50 rounded-lg p-3 text-center">
                                            <div className="text-sm text-green-600 mb-1">今月</div>
                                            <div className="text-lg font-bold text-green-800">{child.thisMonthStampsCount || 0}個</div>
                                        </div>
                                    </div>
                                    
                                    <div className="space-y-3">
                                        <Link 
                                            href={`/children/${child.id}`}
                                            className="block w-full bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors"
                                        >
                                            👤 詳細・管理
                                        </Link>
                                        
                                        <Link 
                                            href={`/children/${child.id}/stamps/create`}
                                            className="block w-full bg-green-500 hover:bg-green-600 text-white px-4 py-3 rounded-lg text-center font-medium transition-colors"
                                        >
                                            🎯 スタンプを押す
                                        </Link>
                                        
                                        <div className="text-xs text-gray-500 text-center pt-2">
                                            その他の機能は詳細ページから
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
};

export default Index;