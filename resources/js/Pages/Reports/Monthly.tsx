import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Child } from '@/types/models';
import AppLayout from '@/Layouts/AppLayout';

interface MonthlyReport {
    summary: {
        total_stamps: number;
        active_days: number;
        unique_stamp_types: number;
        unique_pokemons?: number;
        average_per_day: number;
    };
    stamps_by_type: Array<{
        name: string;
        count: number;
        percentage: number;
        color?: string;
    }>;
    pokemon_collection?: Array<{
        name: string;
        count: number;
        rarity: string;
    }>;
    achievements?: Array<{
        title: string;
        achieved_at: string;
    }>;
    activity_trend: Array<{
        period: string;
        count: number;
        unique_types: number;
    }>;
}

interface Props {
    child: Child;
    children: Child[];
    report: MonthlyReport;
    month: string;
    monthName: string;
}

export default function Monthly({ child, children, report, month, monthName }: Props) {
    const [selectedChild, setSelectedChild] = useState(child.id);
    const [selectedMonth, setSelectedMonth] = useState(month);
    
    const { get } = useForm();

    const handleChildChange = (childId: number) => {
        setSelectedChild(childId);
        get(route('children.reports.monthly', { child: childId, month: selectedMonth }));
    };

    const handleMonthChange = (newMonth: string) => {
        setSelectedMonth(newMonth);
        get(route('children.reports.monthly', { child: selectedChild, month: newMonth }));
    };


    return (
        <AppLayout>
            <Head title={`${child.name}の月間レポート - ${monthName}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900">
                            {/* ヘッダー */}
                            <div className="mb-8">
                                <h1 className="text-3xl font-bold text-gray-900">
                                    月間レポート
                                </h1>
                                <p className="text-gray-600 mt-2">
                                    {child.name}の{monthName}の活動記録
                                </p>
                            </div>

                            {/* フィルター */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-3">
                                        子ども選択
                                    </label>
                                    <select
                                        value={selectedChild}
                                        onChange={(e) => handleChildChange(Number(e.target.value))}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-white"
                                    >
                                        {children.map((child) => (
                                            <option key={child.id} value={child.id}>
                                                {child.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-sm font-medium text-gray-700 mb-3">
                                        月選択
                                    </label>
                                    <input
                                        type="month"
                                        value={selectedMonth}
                                        onChange={(e) => handleMonthChange(e.target.value)}
                                        className="w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>

                            {/* サマリー統計 */}
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                                <div className="bg-blue-50 p-6 rounded-lg border border-blue-200">
                                    <div className="text-3xl font-bold text-blue-600">
                                        {report.summary.total_stamps}
                                    </div>
                                    <div className="text-blue-800 font-medium">総スタンプ数</div>
                                </div>

                                <div className="bg-green-50 p-6 rounded-lg border border-green-200">
                                    <div className="text-3xl font-bold text-green-600">
                                        {report.summary.active_days}
                                    </div>
                                    <div className="text-green-800 font-medium">活動日数</div>
                                </div>

                                <div className="bg-purple-50 p-6 rounded-lg border border-purple-200">
                                    <div className="text-3xl font-bold text-purple-600">
                                        {report.summary.unique_stamp_types}
                                    </div>
                                    <div className="text-purple-800 font-medium">スタンプ種類数</div>
                                </div>

                                <div className="bg-yellow-50 p-6 rounded-lg border border-yellow-200">
                                    <div className="text-3xl font-bold text-yellow-600">
                                        {(report.summary.average_per_day || 0).toFixed(1)}
                                    </div>
                                    <div className="text-yellow-800 font-medium">1日平均</div>
                                </div>
                            </div>

                            {/* スタンプ種類別統計 */}
                            <div className="bg-white border border-gray-200 rounded-lg p-6 mb-8">
                                <h2 className="text-xl font-bold text-gray-900 mb-6">
                                    スタンプ種類別統計
                                </h2>
                                
                                <div className="overflow-x-auto">
                                    <table className="min-w-full divide-y divide-gray-200">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    スタンプ種類
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    獲得数
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    割合
                                                </th>
                                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    進捗
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody className="bg-white divide-y divide-gray-200">
                                            {report.stamps_by_type.map((stampType, index) => (
                                                <tr key={index}>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                        {stampType.name}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {stampType.count}
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                        {(stampType.percentage || 0).toFixed(1)}%
                                                    </td>
                                                    <td className="px-6 py-4 whitespace-nowrap">
                                                        <div className="w-full bg-gray-200 rounded-full h-2">
                                                            <div
                                                                className="bg-blue-600 h-2 rounded-full"
                                                                style={{ width: `${stampType.percentage || 0}%` }}
                                                            ></div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {/* ポケモンコレクション */}
                            {report.pokemon_collection && report.pokemon_collection.length > 0 && (
                                <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                                    <h2 className="text-xl font-bold text-gray-900 mb-6">
                                        ✨ 今月出会ったポケモン
                                    </h2>
                                    
                                    <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                        {report.pokemon_collection.map((pokemon, index) => (
                                            <div
                                                key={index}
                                                className="bg-white p-4 rounded-lg border border-yellow-300 text-center"
                                            >
                                                <div className="font-medium text-gray-900">
                                                    {pokemon.name}
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    {pokemon.count}回出会った
                                                </div>
                                                <div className="text-xs text-yellow-600 mt-1">
                                                    {pokemon.rarity === 'legendary' ? '⭐⭐⭐' : 
                                                     pokemon.rarity === 'rare' ? '⭐⭐' : '⭐'}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* 達成した目標 */}
                            {report.achievements && report.achievements.length > 0 && (
                                <div className="bg-green-50 border border-green-200 rounded-lg p-6 mb-8">
                                    <h2 className="text-xl font-bold text-gray-900 mb-6">
                                        🎉 今月達成した目標
                                    </h2>
                                    
                                    <div className="space-y-3">
                                        {report.achievements.map((achievement, index) => (
                                            <div
                                                key={index}
                                                className="bg-white p-4 rounded-lg border border-green-300"
                                            >
                                                <div className="font-medium text-gray-900">
                                                    {achievement.title}
                                                </div>
                                                <div className="text-sm text-gray-600">
                                                    達成日: {achievement.achieved_at}
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            )}

                            {/* 日別活動傾向 */}
                            <div className="bg-white border border-gray-200 rounded-lg p-6">
                                <h2 className="text-xl font-bold text-gray-900 mb-6">
                                    📈 日別活動傾向
                                </h2>
                                
                                <div className="grid grid-cols-7 gap-2 text-center text-xs font-medium text-gray-500 mb-2">
                                    <div>日</div>
                                    <div>月</div>
                                    <div>火</div>
                                    <div>水</div>
                                    <div>木</div>
                                    <div>金</div>
                                    <div>土</div>
                                </div>
                                
                                <div className="grid grid-cols-7 gap-2">
                                    {report.activity_trend.map((day, index) => (
                                        <div
                                            key={index}
                                            className={`
                                                aspect-square rounded border text-center text-xs flex flex-col justify-center
                                                ${day.count > 0 
                                                    ? 'bg-blue-100 border-blue-300 text-blue-800' 
                                                    : 'bg-gray-50 border-gray-200 text-gray-400'
                                                }
                                            `}
                                        >
                                            <div className="font-medium">
                                                {day.period.split('-')[2]}
                                            </div>
                                            {day.count > 0 && (
                                                <div className="text-xs">
                                                    {day.count}
                                                </div>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* ナビゲーション */}
                            <div className="mt-8 flex justify-between">
                                <Link
                                    href={route('children.reports.dashboard', { child: selectedChild })}
                                    className="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    詳細レポートへ
                                </Link>
                                
                                <Link
                                    href={route('children.show', selectedChild)}
                                    className="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    {child.name}のページへ
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}