import React, { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { Child, StampWithRelations } from '@/types';
import AppLayout from '@/Layouts/AppLayout';
import PageHeader from '@/Components/PageHeader';

interface CalendarDay {
    date: string;
    day: number;
    day_of_week: number;
    is_current_month: boolean;
    is_today: boolean;
    stamps: StampWithRelations[];
    stamps_count: number;
    has_legendary: boolean;
    has_mythical: boolean;
}

interface CalendarData {
    year: number;
    month: number;
    calendar: CalendarDay[][];
    statistics: {
        total_stamps: number;
        legendary_count: number;
        mythical_count: number;
        stamp_type_statistics: Array<{
            stamp_type: {
                id: number;
                name: string;
                icon: string;
                color: string;
            };
            count: number;
        }>;
        daily_statistics: Record<string, number>;
        average_per_day: number;
    };
    total_stamps: number;
}

interface Navigation {
    current: {
        year: number;
        month: number;
        month_name: string;
    };
    previous: {
        year: number;
        month: number;
        month_name: string;
    };
    next: {
        year: number;
        month: number;
        month_name: string;
    };
}

interface Props {
    child: Child;
    children: Child[];
    calendar_data: CalendarData;
    navigation: Navigation;
}

const Monthly: React.FC<Props> = ({ child, children, calendar_data, navigation }) => {
    const [selectedDate, setSelectedDate] = useState<string | null>(null);

    const getDayClassName = (day: CalendarDay): string => {
        const baseClasses = 'min-h-20 p-2 border border-gray-200 cursor-pointer transition-colors duration-200';
        const classes = [baseClasses];

        if (!day.is_current_month) {
            classes.push('bg-gray-50 text-gray-400');
        } else {
            classes.push('bg-white text-gray-900 hover:bg-blue-50');
        }

        if (day.is_today) {
            classes.push('ring-2 ring-blue-500 bg-blue-100');
        }

        if (day.stamps_count > 0) {
            classes.push('bg-green-50');
        }

        if (day.has_mythical) {
            classes.push('bg-purple-100 border-purple-300');
        } else if (day.has_legendary) {
            classes.push('bg-yellow-100 border-yellow-300');
        }

        return classes.join(' ');
    };

    const getStampIcon = (stamp: StampWithRelations): string => {
        if (stamp.pokemon.is_mythical) return '✨';
        if (stamp.pokemon.is_legendary) return '⭐';
        return stamp.stamp_type.icon || '📎';
    };

    const weekDays = ['日', '月', '火', '水', '木', '金', '土'];

    return (
        <AppLayout title={`${child.name}のカレンダー - ${navigation.current.year}年${navigation.current.month}月`}>
            <Head title={`${child.name}のカレンダー - ${navigation.current.year}年${navigation.current.month}月`} />
            
            <div className="bg-gradient-to-br from-blue-50 to-purple-50 p-4">
                <div className="max-w-6xl mx-auto">
                    <PageHeader
                        title={`${child.name}のカレンダー`}
                        subtitle="月間・週間の記録を表示"
                        child={child}
                        children={children}
                        currentPage="calendar"
                        actions={
                            <div className="flex space-x-2">
                                <Link
                                    href={route('children.stamp-cards.index', child.id)}
                                    className="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                >
                                    スタンプカード
                                </Link>
                                <Link
                                    href={route('children.stamps.index', child.id)}
                                    className="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-colors"
                                >
                                    スタンプ一覧
                                </Link>
                            </div>
                        }
                    />

                    {/* 月間ナビゲーション */}
                    <div className="bg-white rounded-lg shadow-md p-4 mb-6">
                        <div className="flex items-center justify-between">
                            <Link
                                href={route('children.calendar.monthly', {
                                    child: child.id,
                                    year: navigation.previous.year,
                                    month: navigation.previous.month
                                })}
                                className="flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <span>←</span>
                                <span>{navigation.previous.month_name}</span>
                            </Link>

                            <h2 className="text-xl font-semibold text-gray-700">
                                {navigation.current.year}年{navigation.current.month}月
                            </h2>

                            <Link
                                href={route('children.calendar.monthly', {
                                    child: child.id,
                                    year: navigation.next.year,
                                    month: navigation.next.month
                                })}
                                className="flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <span>{navigation.next.month_name}</span>
                                <span>→</span>
                            </Link>
                        </div>
                    </div>

                    {/* 統計情報 */}
                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">総スタンプ数</div>
                            <div className="text-2xl font-bold text-blue-600">{calendar_data.statistics.total_stamps}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">伝説のポケモン</div>
                            <div className="text-2xl font-bold text-yellow-600">{calendar_data.statistics.legendary_count}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">幻のポケモン</div>
                            <div className="text-2xl font-bold text-purple-600">{calendar_data.statistics.mythical_count}</div>
                        </div>
                        <div className="bg-white rounded-lg shadow-md p-4">
                            <div className="text-sm text-gray-600">一日平均</div>
                            <div className="text-2xl font-bold text-green-600">{calendar_data.statistics.average_per_day}</div>
                        </div>
                    </div>

                    {/* カレンダー */}
                    <div className="bg-white rounded-lg shadow-md overflow-hidden">
                        {/* 曜日ヘッダー */}
                        <div className="grid grid-cols-7 bg-gray-100">
                            {weekDays.map((day, index) => (
                                <div key={index} className="p-3 text-center font-semibold text-gray-700">
                                    {day}
                                </div>
                            ))}
                        </div>

                        {/* カレンダー本体 */}
                        {calendar_data.calendar.map((week, weekIndex) => (
                            <div key={weekIndex} className="grid grid-cols-7">
                                {week.map((day, dayIndex) => (
                                    <div
                                        key={`${weekIndex}-${dayIndex}`}
                                        className={getDayClassName(day)}
                                        onClick={() => setSelectedDate(day.date)}
                                    >
                                        <div className="flex justify-between items-start mb-1">
                                            <span className={`text-sm font-medium ${day.is_today ? 'text-blue-600' : ''}`}>
                                                {day.day}
                                            </span>
                                            {day.stamps_count > 0 && (
                                                <span className="bg-blue-500 text-white text-xs px-1 rounded-full">
                                                    {day.stamps_count}
                                                </span>
                                            )}
                                        </div>
                                        
                                        {/* スタンプアイコン表示 */}
                                        <div className="flex flex-wrap gap-1">
                                            {day.stamps.slice(0, 6).map((stamp, stampIndex) => (
                                                <span key={stampIndex} className="text-xs" title={stamp.pokemon.name}>
                                                    {getStampIcon(stamp)}
                                                </span>
                                            ))}
                                            {day.stamps.length > 6 && (
                                                <span className="text-xs text-gray-500">+{day.stamps.length - 6}</span>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ))}
                    </div>

                    {/* 選択日の詳細表示 */}
                    {selectedDate && (
                        <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
                            <div className="bg-white rounded-lg max-w-md w-full max-h-96 overflow-y-auto">
                                <div className="p-4">
                                    <div className="flex justify-between items-center mb-4">
                                        <h3 className="text-lg font-semibold">{selectedDate} の詳細</h3>
                                        <button
                                            onClick={() => setSelectedDate(null)}
                                            className="text-gray-500 hover:text-gray-700"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                    
                                    <Link
                                        href={route('children.calendar.daily', {
                                            child: child.id,
                                            date: selectedDate
                                        })}
                                        className="block w-full bg-blue-500 text-white text-center py-2 rounded-lg hover:bg-blue-600 transition-colors"
                                    >
                                        詳細ページを見る
                                    </Link>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* ビューの切り替え */}
                    <div className="mt-6 flex justify-center space-x-4">
                        <span className="bg-blue-500 text-white px-4 py-2 rounded-lg">月表示</span>
                        <Link
                            href={route('children.calendar.weekly', child.id)}
                            className="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                        >
                            週表示
                        </Link>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
};

export default Monthly;