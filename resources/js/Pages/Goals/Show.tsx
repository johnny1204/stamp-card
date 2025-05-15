import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

interface Child {
    id: number;
    name: string;
}

interface StampType {
    id: number;
    name: string;
    icon: string | null;
    color: string | null;
    category: string;
}

interface Goal {
    id: number;
    stamp_type_id: number;
    target_count: number;
    period_type: 'weekly' | 'monthly';
    start_date: string;
    end_date: string;
    reward_text: string | null;
    is_achieved: boolean;
    achieved_at: string | null;
    current_count: number;
    progress_percentage: number;
    stamp_type: StampType;
}

interface Props {
    child: Child;
    goal: Goal;
}

export default function Show({ child, goal }: Props) {
    const { delete: destroy } = useForm();

    const handleDelete = () => {
        if (confirm('この目標を削除しますか？')) {
            destroy(route('children.goals.destroy', [child.id, goal.id]));
        }
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('ja-JP', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    };

    const getDaysRemaining = () => {
        const endDate = new Date(goal.end_date);
        const today = new Date();
        const diffTime = endDate.getTime() - today.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        return diffDays;
    };

    const daysRemaining = getDaysRemaining();

    return (
        <AppLayout title={`${child.name}の目標詳細`}>
            <Head title={`${child.name}の目標詳細`} />

            <div className="max-w-3xl mx-auto">
                {/* ヘッダー */}
                <div className="mb-8">
                    <div className="flex items-center space-x-4 mb-4">
                        <Link
                            href={route('children.goals.index', child.id)}
                            className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                        >
                            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                            </svg>
                            {child.name}の目標一覧に戻る
                        </Link>
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900">{child.name}の目標詳細</h1>
                </div>

                {/* 目標ステータス */}
                <div className="bg-white shadow rounded-lg p-6 mb-8">
                    <div className="flex items-center justify-between mb-6">
                        <div className="flex items-center space-x-4">
                            <div 
                                className="w-16 h-16 rounded-lg flex items-center justify-center text-white text-2xl"
                                style={{ backgroundColor: goal.stamp_type.color || '#6B7280' }}
                            >
                                {goal.stamp_type.icon || '🏷️'}
                            </div>
                            <div>
                                <h2 className="text-xl font-semibold text-gray-900">
                                    {goal.stamp_type.name} を {goal.target_count}個集める
                                </h2>
                                <p className="text-sm text-gray-500">
                                    {goal.period_type === 'weekly' ? '週間' : '月間'}目標
                                </p>
                            </div>
                        </div>
                        
                        {goal.is_achieved ? (
                            <div className="flex items-center space-x-2 bg-green-100 text-green-800 px-4 py-2 rounded-full">
                                <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                </svg>
                                <span className="font-medium">達成済み</span>
                            </div>
                        ) : (
                            <div className="text-right">
                                {daysRemaining > 0 ? (
                                    <div className="text-sm text-gray-500">
                                        あと<span className="font-semibold text-indigo-600">{daysRemaining}日</span>
                                    </div>
                                ) : daysRemaining === 0 ? (
                                    <div className="text-sm text-orange-600 font-medium">今日まで</div>
                                ) : (
                                    <div className="text-sm text-red-600 font-medium">期限切れ</div>
                                )}
                            </div>
                        )}
                    </div>

                    {/* 進捗バー */}
                    <div className="mb-6">
                        <div className="flex justify-between items-center mb-2">
                            <span className="text-sm font-medium text-gray-700">進捗状況</span>
                            <span className="text-sm text-gray-500">
                                {goal.current_count} / {goal.target_count} 個
                            </span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-3">
                            <div 
                                className={`h-3 rounded-full transition-all duration-300 ${
                                    goal.is_achieved 
                                        ? 'bg-green-500' 
                                        : goal.progress_percentage >= 80 
                                            ? 'bg-yellow-500' 
                                            : 'bg-indigo-500'
                                }`}
                                style={{ width: `${Math.min(goal.progress_percentage, 100)}%` }}
                            />
                        </div>
                        <div className="text-right mt-1">
                            <span className="text-sm text-gray-500">
                                {goal.progress_percentage.toFixed(1)}% 完了
                            </span>
                        </div>
                    </div>

                    {/* 達成メッセージ */}
                    {goal.is_achieved && goal.achieved_at && (
                        <div className="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                            <div className="flex items-center">
                                <svg className="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                </svg>
                                <div>
                                    <p className="text-green-800 font-medium">🎉 目標達成しました！</p>
                                    <p className="text-green-700 text-sm">
                                        {formatDate(goal.achieved_at)}に達成しました
                                    </p>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* ご褒美 */}
                    {goal.reward_text && (
                        <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h3 className="text-sm font-medium text-yellow-800 mb-1">🎁 ご褒美</h3>
                            <p className="text-yellow-700">{goal.reward_text}</p>
                        </div>
                    )}
                </div>

                {/* 期間情報 */}
                <div className="bg-white shadow rounded-lg p-6 mb-8">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">期間情報</h3>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt className="text-sm font-medium text-gray-500">開始日</dt>
                            <dd className="mt-1 text-sm text-gray-900">{formatDate(goal.start_date)}</dd>
                        </div>
                        <div>
                            <dt className="text-sm font-medium text-gray-500">終了日</dt>
                            <dd className="mt-1 text-sm text-gray-900">{formatDate(goal.end_date)}</dd>
                        </div>
                        <div>
                            <dt className="text-sm font-medium text-gray-500">期間タイプ</dt>
                            <dd className="mt-1 text-sm text-gray-900">
                                {goal.period_type === 'weekly' ? '週間目標' : '月間目標'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-sm font-medium text-gray-500">目標個数</dt>
                            <dd className="mt-1 text-sm text-gray-900">{goal.target_count}個</dd>
                        </div>
                    </div>
                </div>

                {/* アクションボタン */}
                <div className="bg-white shadow rounded-lg p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">アクション</h3>
                    <div className="flex flex-wrap gap-3">
                        {!goal.is_achieved && (
                            <Link
                                href={route('children.goals.edit', [child.id, goal.id])}
                                className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                目標を編集
                            </Link>
                        )}
                        
                        <Link
                            href={route('children.stamps.create', child.id)}
                            className="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                            </svg>
                            スタンプを押す
                        </Link>

                        <button
                            onClick={handleDelete}
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            目標を削除
                        </button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}