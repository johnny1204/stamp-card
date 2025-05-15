import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Child, StampWithRelations } from '@/types';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';

interface DailyData {
    date: string;
    day_name: string;
    stamps: StampWithRelations[];
    stamps_count: number;
    stamp_type_statistics: Array<{
        stamp_type: {
            id: number;
            name: string;
            icon: string;
            color: string;
        };
        count: number;
        stamps: StampWithRelations[];
    }>;
    is_today: boolean;
}

interface Navigation {
    current_date: string;
    previous_date: string;
    next_date: string;
}

interface Props {
    child: Child;
    children: Child[];
    daily_data: DailyData;
    navigation: Navigation;
}

const Daily: React.FC<Props> = ({ child, children, daily_data, navigation }) => {
    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = date.getMonth() + 1;
        const day = date.getDate();
        return `${year}年${month}月${day}日`;
    };

    const formatTime = (dateTimeString: string): string => {
        const date = new Date(dateTimeString);
        const hours = date.getHours().toString().padStart(2, '0');
        const minutes = date.getMinutes().toString().padStart(2, '0');
        return `${hours}:${minutes}`;
    };

    const getStampIcon = (stamp: StampWithRelations): string => {
        if (stamp.pokemon.is_mythical) return '✨';
        if (stamp.pokemon.is_legendary) return '⭐';
        return stamp.stamp_type.icon || '📎';
    };

    const getPokemonRarityBadge = (stamp: StampWithRelations): JSX.Element | null => {
        if (stamp.pokemon.is_mythical) {
            return (
                <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                    幻のポケモン
                </span>
            );
        }
        if (stamp.pokemon.is_legendary) {
            return (
                <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    伝説のポケモン
                </span>
            );
        }
        return null;
    };

    return (
        <AppLayout title={`${child.name}の${formatDate(daily_data.date)}の記録`}>
            <Head title={`${child.name}の${formatDate(daily_data.date)}の記録`} />
            
            <div className="bg-gradient-to-br from-blue-50 to-purple-50 p-4">
                <div className="max-w-6xl mx-auto">
                    <PageHeader
                        title={`${child.name}のカレンダー`}
                        subtitle={`${formatDate(daily_data.date)}の詳細記録`}
                        child={child}
                        children={children}
                        currentPage="calendar"
                        actions={
                            <div className="flex space-x-2">
                                <Link
                                    href={route('children.calendar.monthly', child.id)}
                                    className="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                >
                                    月表示
                                </Link>
                                <Link
                                    href={route('children.calendar.weekly', child.id)}
                                    className="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors"
                                >
                                    週表示
                                </Link>
                                <Link
                                    href={route('children.stamp-cards.index', child.id)}
                                    className="bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 transition-colors"
                                >
                                    スタンプカード
                                </Link>
                            </div>
                        }
                    />

                    {/* 日付ナビゲーション */}
                    <div className="bg-white rounded-lg shadow-md p-6 mb-6">

                        <div className="flex items-center justify-between">
                            <Link
                                href={route('children.calendar.daily', {
                                    child: child.id,
                                    date: navigation.previous_date
                                })}
                                className="flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <span>←</span>
                                <span>前日</span>
                            </Link>

                            <div className="text-center">
                                <h2 className="text-xl font-semibold text-gray-700">
                                    {formatDate(daily_data.date)}
                                </h2>
                                <div className="text-sm text-gray-500">
                                    {daily_data.day_name} {daily_data.is_today && '(今日)'}
                                </div>
                            </div>

                            <Link
                                href={route('children.calendar.daily', {
                                    child: child.id,
                                    date: navigation.next_date
                                })}
                                className="flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <span>翌日</span>
                                <span>→</span>
                            </Link>
                        </div>
                    </div>

                    {/* 統計サマリー */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">スタンプ数</div>
                            <div className="text-2xl font-bold text-blue-600">{daily_data.stamps_count}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">伝説のポケモン</div>
                            <div className="text-2xl font-bold text-yellow-600">
                                {daily_data.stamps.filter(s => s.pokemon.is_legendary).length}
                            </div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">幻のポケモン</div>
                            <div className="text-2xl font-bold text-purple-600">
                                {daily_data.stamps.filter(s => s.pokemon.is_mythical).length}
                            </div>
                        </div>
                    </div>

                    {daily_data.stamps_count === 0 ? (
                        /* スタンプがない場合 */
                        <div className="bg-white rounded-lg shadow-md p-8 text-center">
                            <div className="text-gray-400 text-4xl mb-4">📅</div>
                            <h3 className="text-lg font-medium text-gray-600 mb-2">
                                この日はスタンプがありません
                            </h3>
                            <p className="text-gray-500">
                                頑張ってスタンプを集めましょう！
                            </p>
                        </div>
                    ) : (
                        <div className="space-y-6">
                            {/* スタンプ種類別統計 */}
                            {daily_data.stamp_type_statistics.length > 0 && (
                                <div className="bg-white rounded-lg shadow-md p-6">
                                    <h3 className="text-lg font-semibold text-gray-800 mb-4">
                                        スタンプ種類別統計
                                    </h3>
                                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                        {daily_data.stamp_type_statistics.map((stat, index) => (
                                            <div key={index} className="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                                <span className="text-2xl">{stat.stamp_type.icon}</span>
                                                <div>
                                                    <div className="font-medium text-gray-900">
                                                        {stat.stamp_type.name}
                                                    </div>
                                                    <div className="text-sm text-gray-600">
                                                        {stat.count}個
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* スタンプ詳細リスト */}
                            <div className="bg-white rounded-lg shadow-md p-6">
                                <h3 className="text-lg font-semibold text-gray-800 mb-4">
                                    スタンプ詳細 ({daily_data.stamps_count}個)
                                </h3>
                                <div className="space-y-4">
                                    {daily_data.stamps.map((stamp, index) => (
                                        <div key={index} className="flex items-start space-x-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                            <span className="text-3xl">{getStampIcon(stamp)}</span>
                                            
                                            <div className="flex-1 min-w-0">
                                                <div className="flex items-center space-x-2 mb-1">
                                                    <h4 className="text-lg font-medium text-gray-900">
                                                        {stamp.pokemon.name}
                                                    </h4>
                                                    {getPokemonRarityBadge(stamp)}
                                                </div>
                                                
                                                <div className="flex items-center space-x-4 text-sm text-gray-600 mb-2">
                                                    <span className="flex items-center space-x-1">
                                                        <span>{stamp.stamp_type.icon}</span>
                                                        <span>{stamp.stamp_type.name}</span>
                                                    </span>
                                                    <span>
                                                        {formatTime(stamp.stamped_at)}
                                                    </span>
                                                </div>
                                                
                                                {stamp.comment && (
                                                    <div className="bg-blue-50 p-2 rounded text-sm text-gray-700">
                                                        💬 {stamp.comment}
                                                    </div>
                                                )}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
};

export default Daily;