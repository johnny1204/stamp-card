import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AppLayout from '../Layouts/AppLayout';

interface Child {
    id: number;
    name: string;
    birth_date: string;
    avatar_path: string | null;
    target_stamps: number;
}

interface Props {
    children: Child[];
}

const Home: React.FC<Props> = ({ children = [] }) => {
    const [selectedChildId, setSelectedChildId] = useState<number | null>(
        children.length === 1 ? children[0].id : null
    );

    const handleChildSelect = (childId: number) => {
        setSelectedChildId(childId);
    };

    const selectedChild = children.find(child => child.id === selectedChildId);

    return (
        <AppLayout title="ホーム">
            <div className="space-y-8">
                {/* ヘッダー */}
                <div className="text-center">
                    <h2 className="text-4xl font-bold text-gray-800 mb-4">
                        スタンプ帳管理画面
                    </h2>
                    <p className="text-xl text-gray-600">
                        お子様の成長記録をサポートします
                    </p>
                </div>

                {/* 子ども選択セクション */}
                {children.length > 0 && (
                    <div className="bg-white rounded-lg shadow-md p-6">
                        <h3 className="text-lg font-semibold text-gray-800 mb-4 text-center">
                            🎯 お子様を選択してください
                        </h3>
                        
                        {children.length === 1 ? (
                            // 子どもが1人の場合
                            <div className="text-center">
                                <div className="inline-flex items-center space-x-3 bg-purple-50 rounded-lg p-4">
                                    {children[0].avatar_path ? (
                                        <img 
                                            src={children[0].avatar_path} 
                                            alt={children[0].name}
                                            className="w-12 h-12 rounded-full object-cover"
                                        />
                                    ) : (
                                        <div className="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                            {children[0].name[0]}
                                        </div>
                                    )}
                                    <span className="text-lg font-medium text-purple-700">
                                        {children[0].name}
                                    </span>
                                </div>
                            </div>
                        ) : (
                            // 子どもが複数の場合
                            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                {children.map((child) => (
                                    <button
                                        key={child.id}
                                        onClick={() => handleChildSelect(child.id)}
                                        className={`p-4 rounded-lg border-2 transition-all hover:scale-105 ${
                                            selectedChildId === child.id
                                                ? 'border-purple-500 bg-purple-50 shadow-lg'
                                                : 'border-gray-200 bg-white hover:border-purple-300'
                                        }`}
                                    >
                                        <div className="text-center">
                                            {child.avatar_path ? (
                                                <img 
                                                    src={child.avatar_path} 
                                                    alt={child.name}
                                                    className="w-16 h-16 rounded-full object-cover mx-auto mb-2"
                                                />
                                            ) : (
                                                <div className="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center text-white text-xl font-bold mx-auto mb-2">
                                                    {child.name[0]}
                                                </div>
                                            )}
                                            <p className="font-medium text-gray-800">{child.name}</p>
                                            {selectedChildId === child.id && (
                                                <div className="mt-2">
                                                    <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                        ✨ 選択中
                                                    </span>
                                                </div>
                                            )}
                                        </div>
                                    </button>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* クイックアクションセクション */}
                {selectedChild && (
                    <div className="bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg shadow-md p-6">
                        <h3 className="text-lg font-semibold text-gray-800 mb-4 text-center">
                            🚀 {selectedChild.name}のクイックアクション
                        </h3>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <Link 
                                href={route('children.stamp-cards.index', selectedChild.id)}
                                className="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer border border-purple-200"
                            >
                                <div className="text-center">
                                    <div className="text-3xl mb-2">📋</div>
                                    <h4 className="font-semibold text-purple-700 mb-1">
                                        スタンプカード
                                    </h4>
                                    <p className="text-sm text-gray-600">
                                        獲得したポケモンを確認
                                    </p>
                                </div>
                            </Link>

                            <Link 
                                href={route('children.stamps.create', selectedChild.id)}
                                className="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer border border-green-200"
                            >
                                <div className="text-center">
                                    <div className="text-3xl mb-2">⭐</div>
                                    <h4 className="font-semibold text-green-700 mb-1">
                                        スタンプを押す
                                    </h4>
                                    <p className="text-sm text-gray-600">
                                        達成事項を記録
                                    </p>
                                </div>
                            </Link>

                            <Link 
                                href={route('children.goals.index', selectedChild.id)}
                                className="bg-white rounded-lg shadow-sm p-4 hover:shadow-md transition-shadow cursor-pointer border border-orange-200"
                            >
                                <div className="text-center">
                                    <div className="text-3xl mb-2">🎯</div>
                                    <h4 className="font-semibold text-orange-700 mb-1">
                                        目標を見る
                                    </h4>
                                    <p className="text-sm text-gray-600">
                                        設定した目標の進捗を確認
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </div>
                )}

                {/* メインメニュー */}
                <div>
                    <h3 className="text-lg font-semibold text-gray-800 mb-4 text-center">
                        📚 その他のメニュー
                    </h3>

                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <Link 
                            href="/children"
                            className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer"
                        >
                            <div className="text-4xl mb-4">👶</div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">
                                子ども管理
                            </h3>
                            <p className="text-gray-600">
                                お子様の情報を管理
                            </p>
                        </Link>

                        <Link 
                            href="/go/calendar"
                            className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer"
                        >
                            <div className="text-4xl mb-4">📅</div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">
                                カレンダー
                            </h3>
                            <p className="text-gray-600">
                                月間・週間の記録を表示
                            </p>
                        </Link>

                        <Link 
                            href="/go/statistics"
                            className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer"
                        >
                            <div className="text-4xl mb-4">📊</div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">
                                統計・レポート
                            </h3>
                            <p className="text-gray-600">
                                達成記録の分析・レポート
                            </p>
                        </Link>


                        <Link 
                            href="/master/stamp-types"
                            className="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow cursor-pointer"
                        >
                            <div className="text-4xl mb-4">⚙️</div>
                            <h3 className="text-lg font-semibold text-gray-800 mb-2">
                                マスタ管理
                            </h3>
                            <p className="text-gray-600">
                                スタンプ種類の設定
                            </p>
                        </Link>
                    </div>
                </div>

                {/* 子どもが登録されていない場合のメッセージ */}
                {children.length === 0 && (
                    <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
                        <div className="text-4xl mb-4">👶</div>
                        <h3 className="text-lg font-semibold text-yellow-800 mb-2">
                            お子様の情報を登録してください
                        </h3>
                        <p className="text-yellow-700 mb-4">
                            スタンプ帳をご利用いただくには、お子様の情報を登録していただく必要があります。
                        </p>
                        <Link 
                            href="/children/create"
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500"
                        >
                            お子様を登録する
                        </Link>
                    </div>
                )}
            </div>
        </AppLayout>
    );
};

export default Home;