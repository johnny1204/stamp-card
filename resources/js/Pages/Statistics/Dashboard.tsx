import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Child, StampWithRelations } from '@/types';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';

interface BasicStatistics {
    total_stamps: number;
    today_stamps: number;
    this_month_stamps: number;
    this_year_stamps: number;
    legendary_count: number;
    mythical_count: number;
    special_pokemon_rate: number;
}

interface StampTypeStatistic {
    stamp_type: {
        id: number;
        name: string;
        icon: string;
        color: string;
        category: string;
    };
    count: number;
    legendary_count: number;
    mythical_count: number;
    percentage: number;
    recent_stamps: StampWithRelations[];
}

interface PokemonStatistics {
    total_unique_pokemons: number;
    common_pokemons: number;
    legendary_pokemons: number;
    mythical_pokemons: number;
    collection_list: Array<{
        pokemon: {
            id: number;
            name: string;
            type1: string;
            type2: string | null;
            genus: string;
            is_legendary: boolean;
            is_mythical: boolean;
        };
        count: number;
        first_encounter: string;
        last_encounter: string;
    }>;
    most_encountered: any;
    rarest_encounters: any[];
}

interface GrowthChartData {
    period: {
        start_date: string;
        end_date: string;
        days: number;
    };
    daily_data: Array<{
        date: string;
        day_name: string;
        total_stamps: number;
        legendary_stamps: number;
        mythical_stamps: number;
        cumulative_total: number;
    }>;
    totals: {
        total_stamps: number;
        average_per_day: number;
        max_daily_stamps: number;
        active_days: number;
    };
}

interface Props {
    child: Child;
    children: Child[];
    basic_statistics: BasicStatistics;
    stamp_type_statistics: StampTypeStatistic[];
    pokemon_statistics: PokemonStatistics;
    growth_chart_data: GrowthChartData;
}

const Dashboard: React.FC<Props> = ({
    child,
    children,
    basic_statistics,
    stamp_type_statistics,
    pokemon_statistics,
    growth_chart_data
}) => {
    const getMaxValue = (data: any[], key: string): number => {
        return Math.max(...data.map(item => item[key]), 0);
    };

    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        const month = date.getMonth() + 1;
        const day = date.getDate();
        return `${month}/${day}`;
    };

    return (
        <AppLayout title={`${child.name}の統計ダッシュボード`}>
            <Head title={`${child.name}の統計ダッシュボード`} />
            
            <div className="bg-gradient-to-br from-blue-50 to-purple-50 p-4">
                <div className="max-w-7xl mx-auto">
                    <PageHeader
                        title={`${child.name}の統計ダッシュボード`}
                        subtitle="達成記録の分析・レポート"
                        child={child}
                        children={children}
                        currentPage="statistics"
                        actions={
                            <div className="flex space-x-2">
                                <Link
                                    href={route('children.calendar.monthly', child.id)}
                                    className="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                >
                                    カレンダー
                                </Link>
                                <Link
                                    href={route('children.stamp-cards.index', child.id)}
                                    className="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors"
                                >
                                    スタンプカード
                                </Link>
                            </div>
                        }
                    />

                    {/* 基本統計 */}
                    <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">総スタンプ数</div>
                            <div className="text-2xl font-bold text-blue-600">{basic_statistics.total_stamps}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">今日</div>
                            <div className="text-2xl font-bold text-green-600">{basic_statistics.today_stamps}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">今月</div>
                            <div className="text-2xl font-bold text-orange-600">{basic_statistics.this_month_stamps}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">今年</div>
                            <div className="text-2xl font-bold text-purple-600">{basic_statistics.this_year_stamps}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">伝説のポケモン</div>
                            <div className="text-2xl font-bold text-yellow-600">{basic_statistics.legendary_count}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">幻のポケモン</div>
                            <div className="text-2xl font-bold text-purple-600">{basic_statistics.mythical_count}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">特別率</div>
                            <div className="text-2xl font-bold text-red-600">{basic_statistics.special_pokemon_rate}%</div>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                        {/* 成長グラフ */}
                        <div className="bg-white rounded-lg shadow-md p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-semibold text-gray-800">
                                    過去30日間の成長チャート
                                </h3>
                                <Link
                                    href={route('children.statistics.detailed', child.id)}
                                    className="text-blue-500 hover:text-blue-600 text-sm"
                                >
                                    詳細を見る →
                                </Link>
                            </div>
                            
                            <div className="space-y-4">
                                <div className="grid grid-cols-3 gap-4 text-center">
                                    <div>
                                        <div className="text-sm text-gray-600">合計</div>
                                        <div className="text-lg font-bold text-blue-600">
                                            {growth_chart_data.totals.total_stamps}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-sm text-gray-600">1日平均</div>
                                        <div className="text-lg font-bold text-green-600">
                                            {growth_chart_data.totals.average_per_day}
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-sm text-gray-600">活動日数</div>
                                        <div className="text-lg font-bold text-orange-600">
                                            {growth_chart_data.totals.active_days}日
                                        </div>
                                    </div>
                                </div>

                                {/* 簡単なバーチャート */}
                                <div className="h-32 flex items-end justify-between space-x-1">
                                    {growth_chart_data.daily_data.slice(-14).map((day, index) => {
                                        const maxValue = getMaxValue(growth_chart_data.daily_data, 'total_stamps');
                                        const height = maxValue > 0 ? (day.total_stamps / maxValue) * 100 : 0;
                                        
                                        return (
                                            <div key={index} className="flex-1 flex flex-col items-center">
                                                <div
                                                    className="w-full bg-blue-500 rounded-t transition-all duration-200 hover:bg-blue-600"
                                                    style={{ height: `${height}%`, minHeight: day.total_stamps > 0 ? '4px' : '0px' }}
                                                    title={`${formatDate(day.date)}: ${day.total_stamps}個`}
                                                />
                                                <div className="text-xs text-gray-500 mt-1 transform rotate-45 origin-left">
                                                    {formatDate(day.date)}
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            </div>
                        </div>

                        {/* ポケモンコレクション統計 */}
                        <div className="bg-white rounded-lg shadow-md p-6">
                            <div className="flex items-center justify-between mb-4">
                                <h3 className="text-lg font-semibold text-gray-800">
                                    ポケモンコレクション
                                </h3>
                            </div>
                            
                            <div className="space-y-4">
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="text-center">
                                        <div className="text-sm text-gray-600">収集済みポケモン</div>
                                        <div className="text-2xl font-bold text-blue-600">
                                            {pokemon_statistics.total_unique_pokemons}種
                                        </div>
                                    </div>
                                    <div className="text-center">
                                        <div className="text-sm text-gray-600">幻のポケモン</div>
                                        <div className="text-2xl font-bold text-purple-600">
                                            {pokemon_statistics.mythical_pokemons}種
                                        </div>
                                    </div>
                                </div>

                                {/* 最も多く出会ったポケモン */}
                                {pokemon_statistics.most_encountered && (
                                    <div className="border-t pt-4">
                                        <div className="text-sm text-gray-600 mb-2">最も多く出会ったポケモン</div>
                                        <div className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                            <div className="text-2xl">
                                                {pokemon_statistics.most_encountered.pokemon.is_mythical ? '✨' : 
                                                 pokemon_statistics.most_encountered.pokemon.is_legendary ? '⭐' : '📎'}
                                            </div>
                                            <div>
                                                <div className="font-medium text-gray-900">
                                                    {pokemon_statistics.most_encountered.pokemon.name}
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    {pokemon_statistics.most_encountered.count}回出会った
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* スタンプ種類別統計 */}
                    <div className="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div className="flex items-center justify-between mb-4">
                            <h3 className="text-lg font-semibold text-gray-800">
                                スタンプ種類別統計
                            </h3>
                            <Link
                                href={route('children.statistics.detailed', child.id)}
                                className="text-blue-500 hover:text-blue-600 text-sm"
                            >
                                詳細を見る →
                            </Link>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {stamp_type_statistics.slice(0, 6).map((stat, index) => (
                                <div key={index} className="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                    <div className="flex items-center space-x-3 mb-2">
                                        <span className="text-2xl">{stat.stamp_type.icon}</span>
                                        <div>
                                            <div className="font-medium text-gray-900">{stat.stamp_type.name}</div>
                                            <div className="text-sm text-gray-600">{stat.count}個 ({stat.percentage}%)</div>
                                        </div>
                                    </div>
                                    
                                    {(stat.legendary_count > 0 || stat.mythical_count > 0) && (
                                        <div className="flex space-x-4 text-sm">
                                            {stat.legendary_count > 0 && (
                                                <span className="text-yellow-600">⭐ {stat.legendary_count}</span>
                                            )}
                                            {stat.mythical_count > 0 && (
                                                <span className="text-purple-600">✨ {stat.mythical_count}</span>
                                            )}
                                        </div>
                                    )}
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* ナビゲーション */}
                    <div className="flex justify-center space-x-4">
                        <Link
                            href={route('children.statistics.detailed', child.id)}
                            className="bg-blue-500 text-white px-6 py-3 rounded-lg hover:bg-blue-600 transition-colors"
                        >
                            詳細統計
                        </Link>
                        <Link
                            href={route('children.statistics.monthly-report', child.id)}
                            className="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition-colors"
                        >
                            月間レポート
                        </Link>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default Dashboard;