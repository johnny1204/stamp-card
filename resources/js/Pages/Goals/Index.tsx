import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import PageHeader from '../../Components/PageHeader';
import { Child } from '../../types';

interface Goal {
    id: number;
    stamp_type_id: number;
    target_count: number;
    period_type: 'weekly' | 'monthly';
    start_date: string;
    end_date: string;
    reward_text?: string;
    is_achieved: boolean;
    achieved_at?: string;
    progress_percentage: number;
    current_count: number;
    remaining_days: number;
    stamp_type?: {
        id: number;
        name: string;
        icon: string;
        color: string;
    };
}

interface ProgressSummary {
    active_goals_count: number;
    achieved_goals_count: number;
    average_progress: number;
    urgent_goals: Goal[];
    active_goals: Goal[];
}

interface IndexProps {
    child: Child;
    children: Child[];
    goals: Goal[];
    progressSummary: ProgressSummary;
    filters: {
        period_type?: string;
        is_achieved?: boolean;
        active_only?: boolean;
    };
}

const Index: React.FC<IndexProps> = ({ child, children, goals, progressSummary, filters }) => {
    return (
        <AppLayout>
            <Head title={`${child.name}の目標管理`} />
            
            <div className="container mx-auto px-4 py-8">
                <PageHeader
                    title={`${child.name}の目標管理`}
                    subtitle="目標を設定して、達成を目指そう！"
                    child={child}
                    children={children}
                    currentPage="goals"
                    actions={
                        <div className="flex space-x-2">
                            <Link 
                                href={`/children/${child.id}/goals/create`}
                                className="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                新しい目標を作成
                            </Link>
                        </div>
                    }
                />

                {/* 進捗サマリー */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">🎯</div>
                        <div className="text-2xl font-bold text-blue-600">{progressSummary.active_goals_count}</div>
                        <div className="text-sm text-gray-600">アクティブな目標</div>
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">🏆</div>
                        <div className="text-2xl font-bold text-green-600">{progressSummary.achieved_goals_count}</div>
                        <div className="text-sm text-gray-600">達成した目標</div>
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">📊</div>
                        <div className="text-2xl font-bold text-purple-600">{progressSummary.average_progress}%</div>
                        <div className="text-sm text-gray-600">平均進捗率</div>
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">⚠️</div>
                        <div className="text-2xl font-bold text-orange-600">{progressSummary.urgent_goals.length}</div>
                        <div className="text-sm text-gray-600">緊急の目標</div>
                    </div>
                </div>

                {/* 緊急の目標があれば表示 */}
                {progressSummary.urgent_goals.length > 0 && (
                    <div className="bg-orange-50 border-l-4 border-orange-400 p-4 mb-6">
                        <div className="flex">
                            <div className="flex-shrink-0">
                                <svg className="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                                </svg>
                            </div>
                            <div className="ml-3">
                                <h3 className="text-sm font-medium text-orange-800">緊急の目標があります</h3>
                                <div className="mt-2 text-sm text-orange-700">
                                    <p>以下の目標は残り時間が少なく、進捗が遅れています：</p>
                                    <ul className="list-disc list-inside mt-1">
                                        {progressSummary.urgent_goals.map((goal) => (
                                            <li key={goal.id}>
                                                {goal.stamp_type?.name} - 残り{goal.remaining_days}日（進捗{goal.progress_percentage}%）
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {goals.length === 0 ? (
                    <div className="text-center py-12">
                        <div className="text-8xl mb-4">🎯</div>
                        <p className="text-gray-500 text-lg mb-4">まだ目標が設定されていません</p>
                        <Link 
                            href={`/children/${child.id}/goals/create`}
                            className="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg transition-colors"
                        >
                            最初の目標を作成する
                        </Link>
                    </div>
                ) : (
                    <div className="bg-white rounded-xl shadow-md overflow-hidden">
                        <div className="bg-gradient-to-r from-blue-500 to-purple-600 p-4">
                            <h3 className="text-xl font-bold text-white text-center">
                                🎯 目標一覧 ({goals.length}件)
                            </h3>
                        </div>
                        <div className="divide-y divide-gray-200">
                            {goals.map((goal) => (
                                <div key={goal.id} className="p-6 hover:bg-gray-50 transition-colors">
                                    <div className="flex items-center justify-between">
                                        <div className="flex-1">
                                            <div className="flex items-center space-x-3 mb-3">
                                                {goal.stamp_type && (
                                                    <span 
                                                        className="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white"
                                                        style={{ backgroundColor: goal.stamp_type.color }}
                                                    >
                                                        {goal.stamp_type.icon} {goal.stamp_type.name}
                                                    </span>
                                                )}
                                                <span className={`px-2 py-1 rounded-full text-xs font-medium ${
                                                    goal.period_type === 'weekly' 
                                                        ? 'bg-green-100 text-green-800' 
                                                        : 'bg-blue-100 text-blue-800'
                                                }`}>
                                                    {goal.period_type === 'weekly' ? '週間目標' : '月間目標'}
                                                </span>
                                                {goal.is_achieved && (
                                                    <span className="bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded-full">
                                                        🏆 達成済み
                                                    </span>
                                                )}
                                            </div>
                                            
                                            <div className="mb-3">
                                                <div className="flex justify-between text-sm text-gray-600 mb-1">
                                                    <span>進捗状況</span>
                                                    <span>{goal.current_count} / {goal.target_count} ({goal.progress_percentage}%)</span>
                                                </div>
                                                <div className="w-full bg-gray-200 rounded-full h-2">
                                                    <div 
                                                        className={`h-2 rounded-full ${
                                                            goal.is_achieved 
                                                                ? 'bg-green-400' 
                                                                : goal.progress_percentage >= 80 
                                                                    ? 'bg-blue-400'
                                                                    : goal.progress_percentage >= 50
                                                                        ? 'bg-yellow-400'
                                                                        : 'bg-red-400'
                                                        }`}
                                                        style={{ width: `${Math.min(goal.progress_percentage, 100)}%` }}
                                                    />
                                                </div>
                                            </div>
                                            
                                            <div className="text-sm text-gray-600 mb-2">
                                                期間: {new Date(goal.start_date).toLocaleDateString('ja-JP')} ～ {new Date(goal.end_date).toLocaleDateString('ja-JP')}
                                                {!goal.is_achieved && (
                                                    <span className="ml-2">
                                                        （残り{goal.remaining_days}日）
                                                    </span>
                                                )}
                                            </div>
                                            
                                            {goal.reward_text && (
                                                <div className="text-sm text-gray-800 bg-gray-50 p-3 rounded border-l-4 border-blue-400">
                                                    🎁 {goal.reward_text}
                                                </div>
                                            )}
                                            
                                            {goal.achieved_at && (
                                                <div className="text-sm text-green-600 mt-2">
                                                    🎉 {new Date(goal.achieved_at).toLocaleDateString('ja-JP')} に達成
                                                </div>
                                            )}
                                        </div>
                                        
                                        {/* アクション */}
                                        <div className="flex-shrink-0 ml-4">
                                            <div className="flex space-x-2">
                                                <Link 
                                                    href={`/children/${child.id}/goals/${goal.id}`}
                                                    className="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors"
                                                >
                                                    詳細
                                                </Link>
                                                {!goal.is_achieved && (
                                                    <Link 
                                                        href={`/children/${child.id}/goals/${goal.id}/edit`}
                                                        className="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-sm transition-colors"
                                                    >
                                                        編集
                                                    </Link>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
};

export default Index;