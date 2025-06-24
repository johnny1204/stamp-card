import React from 'react';
import { Head } from '@inertiajs/react';
import ChildLayout from '@/Layouts/ChildLayout';
import { Child, Stamp } from '@/types';

interface Props {
    child: Child;
    stamps: Stamp[];
    totalStamps: number;
    todayStamps: number;
}

export default function Stamps({ child, stamps, totalStamps, todayStamps }: Props) {
    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('ja-JP', {
            month: 'long',
            day: 'numeric',
            weekday: 'short'
        }).format(date);
    };

    return (
        <ChildLayout title={`${child.name}のスタンプ帳`} childName={child.name}>
            <div className="px-4 py-6 space-y-6">
                {/* 統計情報 */}
                <div className="grid grid-cols-2 gap-4">
                    <div className="bg-white/80 backdrop-blur-sm rounded-2xl p-4 text-center shadow-lg">
                        <div className="text-3xl mb-2">🎯</div>
                        <div className="text-2xl font-bold text-blue-600">{todayStamps}</div>
                        <div className="text-sm text-gray-600">今日のスタンプ</div>
                    </div>
                    <div className="bg-white/80 backdrop-blur-sm rounded-2xl p-4 text-center shadow-lg">
                        <div className="text-3xl mb-2">⭐</div>
                        <div className="text-2xl font-bold text-purple-600">{totalStamps}</div>
                        <div className="text-sm text-gray-600">全部のスタンプ</div>
                    </div>
                </div>

                {/* メッセージ */}
                <div className="bg-gradient-to-r from-yellow-400/20 to-orange-400/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                    <div className="text-4xl mb-3">🌟</div>
                    <h2 className="text-xl font-bold text-gray-800 mb-2">
                        {child.name}、よく頑張ったね！
                    </h2>
                    <p className="text-gray-600">
                        {todayStamps > 0 
                            ? `今日も${todayStamps}個のスタンプをもらえたよ！` 
                            : '今日もお疲れさま！明日も頑張ろうね'}
                    </p>
                </div>

                {/* スタンプ一覧 */}
                <div className="space-y-4">
                    <h3 className="text-lg font-bold text-gray-800 text-center">
                        🎁 もらったスタンプ
                    </h3>
                    
                    {stamps.length > 0 ? (
                        <div className="space-y-3">
                            {stamps.map((stamp) => (
                                <div 
                                    key={stamp.id}
                                    className="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg"
                                >
                                    <div className="flex items-center space-x-4">
                                        {/* ポケモン画像またはアイコン */}
                                        <div className="w-16 h-16 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-2xl">
                                            🏆
                                        </div>
                                        
                                        <div className="flex-1">
                                            <div className="flex items-center justify-between mb-1">
                                                <h4 className="font-bold text-gray-800">
                                                    {stamp.stamp_type?.name || 'スタンプ'}
                                                </h4>
                                                <span className="text-xs text-gray-500">
                                                    {formatDate(stamp.stamped_at)}
                                                </span>
                                            </div>
                                            
                                            {stamp.comment && (
                                                <p className="text-sm text-gray-600 bg-gray-50 rounded-lg p-2 mt-2">
                                                    💭 {stamp.comment}
                                                </p>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="text-6xl mb-4">🎈</div>
                            <p className="text-gray-600">
                                まだスタンプがないよ<br />
                                頑張ったらスタンプがもらえるよ！
                            </p>
                        </div>
                    )}
                </div>

                {/* 励ましメッセージ */}
                {stamps.length > 0 && (
                    <div className="bg-gradient-to-r from-green-400/20 to-blue-400/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                        <div className="text-3xl mb-3">🎉</div>
                        <p className="text-gray-700 font-medium">
                            {totalStamps >= 10 
                                ? 'すごい！たくさんスタンプが集まったね！'
                                : totalStamps >= 5
                                    ? 'いい調子！もっと頑張ろう！'
                                    : '良いスタート！続けて頑張ろう！'}
                        </p>
                    </div>
                )}
            </div>
        </ChildLayout>
    );
}