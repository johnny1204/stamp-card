import React from 'react';
import { Head, Link } from '@inertiajs/react';
import { Child, StampWithRelations } from '@/types';

interface WeekDay {
    date: string;
    day_of_week: number;
    day_name: string;
    stamps: StampWithRelations[];
    stamps_count: number;
    is_today: boolean;
}

interface CalendarData {
    start_of_week: string;
    end_of_week: string;
    week_days: WeekDay[];
    total_stamps: number;
}

interface Navigation {
    current_week_start: string;
    previous_week_start: string;
    next_week_start: string;
}

interface Props {
    child: Child;
    calendar_data: CalendarData;
    navigation: Navigation;
}

const Weekly: React.FC<Props> = ({ child, calendar_data, navigation }) => {
    const getStampIcon = (stamp: StampWithRelations): string => {
        if (stamp.pokemon.is_mythical) return '✨';
        if (stamp.pokemon.is_legendary) return '⭐';
        return stamp.stamp_type.icon || '📎';
    };

    const getDayClassName = (day: WeekDay): string => {
        const baseClasses = 'bg-white rounded-lg shadow-md p-4 min-h-40 transition-all duration-200';
        const classes = [baseClasses];

        if (day.is_today) {
            classes.push('ring-2 ring-blue-500 bg-blue-50');
        } else {
            classes.push('hover:shadow-lg');
        }

        if (day.stamps_count > 0) {
            classes.push('border-l-4 border-green-400');
        }

        return classes.join(' ');
    };

    const formatDate = (dateString: string): string => {
        const date = new Date(dateString);
        const month = date.getMonth() + 1;
        const day = date.getDate();
        return `${month}/${day}`;
    };

    const weekDaysJapanese = ['日', '月', '火', '水', '木', '金', '土'];

    return (
        <>
            <Head title={`${child.name}の週間カレンダー`} />
            
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 p-4">
                <div className="max-w-7xl mx-auto">
                    {/* ヘッダー */}
                    <div className="bg-white rounded-lg shadow-md p-6 mb-6">
                        <div className="flex items-center justify-between mb-4">
                            <h1 className="text-2xl font-bold text-gray-800">
                                {child.name}の週間カレンダー
                            </h1>
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
                        </div>

                        {/* ナビゲーション */}
                        <div className="flex items-center justify-between">
                            <Link
                                href={route('children.calendar.weekly', {
                                    child: child.id,
                                    date: navigation.previous_week_start
                                })}
                                className="flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <span>←</span>
                                <span>前週</span>
                            </Link>

                            <div className="text-center">
                                <h2 className="text-xl font-semibold text-gray-700">
                                    {formatDate(calendar_data.start_of_week)} - {formatDate(calendar_data.end_of_week)}
                                </h2>
                                <div className="text-sm text-gray-500">
                                    総スタンプ数: {calendar_data.total_stamps}個
                                </div>
                            </div>

                            <Link
                                href={route('children.calendar.weekly', {
                                    child: child.id,
                                    date: navigation.next_week_start
                                })}
                                className="flex items-center space-x-2 bg-gray-100 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors"
                            >
                                <span>次週</span>
                                <span>→</span>
                            </Link>
                        </div>
                    </div>

                    {/* 週間カレンダー */}
                    <div className="grid grid-cols-1 md:grid-cols-7 gap-4 mb-6">
                        {calendar_data.week_days.map((day, index) => (
                            <div key={index} className={getDayClassName(day)}>
                                {/* 日付ヘッダー */}
                                <div className="flex items-center justify-between mb-3">
                                    <div>
                                        <div className="text-sm font-medium text-gray-600">
                                            {weekDaysJapanese[day.day_of_week]}
                                        </div>
                                        <div className={`text-lg font-bold ${day.is_today ? 'text-blue-600' : 'text-gray-800'}`}>
                                            {formatDate(day.date)}
                                        </div>
                                    </div>
                                    {day.stamps_count > 0 && (
                                        <div className="bg-blue-500 text-white text-sm px-2 py-1 rounded-full">
                                            {day.stamps_count}
                                        </div>
                                    )}
                                </div>

                                {/* スタンプ一覧 */}
                                <div className="space-y-2">
                                    {day.stamps.map((stamp, stampIndex) => (
                                        <div key={stampIndex} className="flex items-center space-x-2 p-2 bg-gray-50 rounded">
                                            <span className="text-lg">{getStampIcon(stamp)}</span>
                                            <div className="flex-1 min-w-0">
                                                <div className="text-sm font-medium text-gray-900 truncate">
                                                    {stamp.pokemon.name}
                                                </div>
                                                <div className="text-xs text-gray-500">
                                                    {stamp.stamp_type.name}
                                                </div>
                                            </div>
                                        </div>
                                    ))}
                                    
                                    {day.stamps_count === 0 && (
                                        <div className="text-center text-gray-400 text-sm py-4">
                                            スタンプなし
                                        </div>
                                    )}
                                </div>

                                {/* 詳細リンク */}
                                {day.stamps_count > 0 && (
                                    <div className="mt-3">
                                        <Link
                                            href={route('children.calendar.daily', {
                                                child: child.id,
                                                date: day.date
                                            })}
                                            className="block w-full text-center bg-blue-100 text-blue-600 py-1 rounded text-sm hover:bg-blue-200 transition-colors"
                                        >
                                            詳細を見る
                                        </Link>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>

                    {/* ビューの切り替え */}
                    <div className="flex justify-center space-x-4">
                        <Link
                            href={route('children.calendar.monthly', child.id)}
                            className="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 transition-colors"
                        >
                            月表示
                        </Link>
                        <span className="bg-blue-500 text-white px-4 py-2 rounded-lg">週表示</span>
                    </div>
                </div>
            </div>
        </>
    );
};

export default Weekly;