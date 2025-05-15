import React from 'react';
import { Head, Link } from '@inertiajs/react';
import AppLayout from '../../Layouts/AppLayout';
import PageHeader from '../../Components/PageHeader';
import { Child } from '../../types';

interface StampByType {
    id: number;
    name: string;
    icon: string;
    color: string;
    count: number;
    open_rate: number;
}

interface ActivityTrend {
    period: string;
    date: string;
    count: number;
    unique_types: number;
}

interface PokemonStats {
    total_pokemon: number;
    total_collected: number;
    legendary_count: number;
    mythical_count: number;
    rare_rate: number;
    most_collected: Record<string, number>;
    recent_rare: any[];
}

interface AchievementStats {
    completed_cards: number;
    achievement_rate: number;
    best_streak: number;
    consistency_score: number;
}

interface DailyPatterns {
    by_day_of_week: Record<string, number>;
    by_hour_of_day: Record<string, number>;
    most_active_day: string;
    most_active_hour: string;
}

interface Summary {
    total_stamps: number;
    total_days: number;
    active_days: number;
    activity_rate: number;
    average_per_day: number;
    unique_stamp_types: number;
    opened_stamps: number;
    unopened_stamps: number;
}

interface Report {
    child: Child;
    period: {
        start_date: string;
        end_date: string;
    };
    summary: Summary;
    stamps_by_type: StampByType[];
    activity_trend: ActivityTrend[];
    pokemon_collection?: PokemonStats;
    achievements?: AchievementStats;
    daily_patterns: DailyPatterns;
    monthly_comparison: any[];
}

interface DashboardProps {
    child: Child;
    children: Child[];
    report: Report;
    filters: {
        start_date: string;
        end_date: string;
        group_by: string;
        include_pokemon: boolean;
        include_trends: boolean;
        include_achievements: boolean;
    };
}

const Dashboard: React.FC<DashboardProps> = ({ child, children, report, filters }) => {
    const { summary, stamps_by_type, pokemon_collection, achievements } = report;

    return (
        <AppLayout>
            <Head title={`${child.name}の詳細レポート`} />
            
            <div className="container mx-auto px-4 py-8">
                <PageHeader
                    title={`${child.name}の詳細レポート`}
                    subtitle={`${new Date(report.period.start_date).toLocaleDateString('ja-JP')} ～ ${new Date(report.period.end_date).toLocaleDateString('ja-JP')}`}
                    child={child}
                    children={children}
                    currentPage="reports"
                    actions={
                        <div className="flex space-x-2">
                            <Link 
                                href={`/children/${child.id}/reports/monthly`}
                                className="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                月間レポート
                            </Link>
                            <Link 
                                href={`/children/${child.id}/reports/export/pdf`}
                                className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                PDF出力
                            </Link>
                        </div>
                    }
                />

                {/* サマリー統計 */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">📝</div>
                        <div className="text-2xl font-bold text-blue-600">{summary.total_stamps}</div>
                        <div className="text-sm text-gray-600">総スタンプ数</div>
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">📅</div>
                        <div className="text-2xl font-bold text-green-600">{summary.active_days}</div>
                        <div className="text-sm text-gray-600">活動日数</div>
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">📊</div>
                        <div className="text-2xl font-bold text-purple-600">{summary.activity_rate}%</div>
                        <div className="text-sm text-gray-600">活動率</div>
                    </div>
                    <div className="bg-white rounded-xl p-6 shadow-md text-center">
                        <div className="text-3xl mb-2">⭐</div>
                        <div className="text-2xl font-bold text-orange-600">{summary.average_per_day}</div>
                        <div className="text-sm text-gray-600">日平均スタンプ</div>
                    </div>
                </div>

                {/* スタンプタイプ別統計 */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                    <div className="bg-white rounded-xl shadow-md p-6">
                        <h3 className="text-xl font-bold text-gray-800 mb-4">スタンプタイプ別統計</h3>
                        {stamps_by_type.length === 0 ? (
                            <p className="text-gray-500 text-center py-8">データがありません</p>
                        ) : (
                            <div className="space-y-4">
                                {stamps_by_type.slice(0, 5).map((stampType) => (
                                    <div key={stampType.id} className="flex items-center justify-between">
                                        <div className="flex items-center space-x-3">
                                            <span 
                                                className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium text-white"
                                                style={{ backgroundColor: stampType.color }}
                                            >
                                                {stampType.icon} {stampType.name}
                                            </span>
                                        </div>
                                        <div className="text-right">
                                            <div className="text-lg font-bold text-gray-800">{stampType.count}個</div>
                                            <div className="text-xs text-gray-500">開封率: {Math.round(stampType.open_rate)}%</div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* ポケモン統計 */}
                    {pokemon_collection && (
                        <div className="bg-white rounded-xl shadow-md p-6">
                            <h3 className="text-xl font-bold text-gray-800 mb-4">ポケモンコレクション</h3>
                            <div className="space-y-4">
                                <div className="flex justify-between">
                                    <span className="text-gray-600">図鑑登録数</span>
                                    <span className="font-bold">{pokemon_collection.total_pokemon}匹</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">総捕獲数</span>
                                    <span className="font-bold">{pokemon_collection.total_collected}匹</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">伝説ポケモン</span>
                                    <span className="font-bold text-yellow-600">{pokemon_collection.legendary_count}匹</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">幻ポケモン</span>
                                    <span className="font-bold text-purple-600">{pokemon_collection.mythical_count}匹</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-600">レア率</span>
                                    <span className="font-bold text-orange-600">{pokemon_collection.rare_rate}%</span>
                                </div>
                            </div>
                        </div>
                    )}
                </div>

                {/* 達成統計 */}
                {achievements && (
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                        <div className="bg-white rounded-xl p-6 shadow-md text-center">
                            <div className="text-3xl mb-2">🎴</div>
                            <div className="text-2xl font-bold text-yellow-600">{achievements.completed_cards}</div>
                            <div className="text-sm text-gray-600">完成カード数</div>
                        </div>
                        <div className="bg-white rounded-xl p-6 shadow-md text-center">
                            <div className="text-3xl mb-2">🔥</div>
                            <div className="text-2xl font-bold text-red-600">{achievements.best_streak}</div>
                            <div className="text-sm text-gray-600">最長連続日数</div>
                        </div>
                        <div className="bg-white rounded-xl p-6 shadow-md text-center">
                            <div className="text-3xl mb-2">📈</div>
                            <div className="text-2xl font-bold text-green-600">{achievements.consistency_score}%</div>
                            <div className="text-sm text-gray-600">継続性スコア</div>
                        </div>
                        <div className="bg-white rounded-xl p-6 shadow-md text-center">
                            <div className="text-3xl mb-2">🎯</div>
                            <div className="text-2xl font-bold text-blue-600">{achievements.achievement_rate}%</div>
                            <div className="text-sm text-gray-600">目標達成率</div>
                        </div>
                    </div>
                )}

                {/* 詳細分析へのリンク */}
                <div className="bg-white rounded-xl shadow-md p-6">
                    <h3 className="text-xl font-bold text-gray-800 mb-4">詳細分析</h3>
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <Link 
                            href={`/children/${child.id}/statistics/detailed`}
                            className="bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg p-4 text-center transition-colors"
                        >
                            <div className="text-2xl mb-2">📊</div>
                            <div className="font-semibold text-blue-800">詳細統計</div>
                            <div className="text-sm text-blue-600">時系列分析とグラフ</div>
                        </Link>
                        <Link 
                            href={`/children/${child.id}/calendar/monthly`}
                            className="bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg p-4 text-center transition-colors"
                        >
                            <div className="text-2xl mb-2">📅</div>
                            <div className="font-semibold text-green-800">カレンダー</div>
                            <div className="text-sm text-green-600">日別活動記録</div>
                        </Link>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default Dashboard;