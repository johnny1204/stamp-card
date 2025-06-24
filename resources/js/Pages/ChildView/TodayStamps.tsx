import React from 'react';
import { Head } from '@inertiajs/react';
import ChildLayout from '@/Layouts/ChildLayout';
import { Child, Stamp } from '@/types';

interface Props {
    child: Child;
    todayStamps: Stamp[];
}

export default function TodayStamps({ child, todayStamps }: Props) {
    const getCurrentTime = () => {
        return new Intl.DateTimeFormat('ja-JP', {
            hour: '2-digit',
            minute: '2-digit'
        }).format(new Date());
    };

    const getTimeOfDay = () => {
        const hour = new Date().getHours();
        if (hour < 12) return { emoji: '🌅', text: 'おはよう' };
        if (hour < 18) return { emoji: '☀️', text: 'お疲れさま' };
        return { emoji: '🌙', text: 'お疲れさま' };
    };

    const formatStampTime = (dateString: string) => {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('ja-JP', {
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    };

    const timeOfDay = getTimeOfDay();

    return (
        <ChildLayout title={`${child.name}の今日のスタンプ`} childName={child.name}>
            <div className="px-4 py-6 space-y-6">
                {/* 今日の情報 */}
                <div className="bg-white/80 backdrop-blur-sm rounded-2xl p-6 text-center shadow-lg">
                    <div className="text-4xl mb-3">{timeOfDay.emoji}</div>
                    <h2 className="text-xl font-bold text-gray-800 mb-2">
                        {timeOfDay.text}、{child.name}！
                    </h2>
                    <p className="text-gray-600">
                        今は {getCurrentTime()} だよ
                    </p>
                    <div className="mt-4 text-3xl font-bold text-blue-600">
                        今日のスタンプ: {todayStamps.length}個
                    </div>
                </div>

                {/* 今日のスタンプ一覧 */}
                <div className="space-y-4">
                    <h3 className="text-lg font-bold text-gray-800 text-center">
                        🎯 今日もらったスタンプ
                    </h3>
                    
                    {todayStamps.length > 0 ? (
                        <div className="space-y-3">
                            {todayStamps.map((stamp, index) => (
                                <div 
                                    key={stamp.id}
                                    className="bg-white/80 backdrop-blur-sm rounded-2xl p-4 shadow-lg transform hover:scale-105 transition-transform"
                                    style={{
                                        animationDelay: `${index * 0.1}s`,
                                        animation: 'fadeInUp 0.5s ease-out forwards'
                                    }}
                                >
                                    <div className="flex items-center space-x-4">
                                        {/* スタンプ番号とアイコン */}
                                        <div className="relative">
                                            <div className="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center text-2xl shadow-lg">
                                                ⭐
                                            </div>
                                            <div className="absolute -top-1 -right-1 w-6 h-6 bg-blue-500 text-white rounded-full flex items-center justify-center text-xs font-bold">
                                                {index + 1}
                                            </div>
                                        </div>
                                        
                                        <div className="flex-1">
                                            <div className="flex items-center justify-between mb-1">
                                                <h4 className="font-bold text-gray-800">
                                                    {stamp.stamp_type?.name || 'スタンプ'}
                                                </h4>
                                                <span className="text-sm font-bold text-blue-600">
                                                    {formatStampTime(stamp.stamped_at)}
                                                </span>
                                            </div>
                                            
                                            {stamp.comment && (
                                                <div className="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg p-3 mt-2">
                                                    <p className="text-sm text-gray-700">
                                                        💭 {stamp.comment}
                                                    </p>
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="text-center py-12">
                            <div className="text-6xl mb-4">🎈</div>
                            <h3 className="text-lg font-bold text-gray-800 mb-2">
                                今日はまだスタンプがないよ
                            </h3>
                            <p className="text-gray-600">
                                何か頑張ったことがあったら<br />
                                スタンプをもらえるかも！
                            </p>
                        </div>
                    )}
                </div>

                {/* 励ましメッセージ */}
                <div className="bg-gradient-to-r from-green-400/20 to-blue-400/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                    <div className="text-3xl mb-3">
                        {todayStamps.length >= 3 ? '🏆' : todayStamps.length >= 1 ? '🌟' : '💪'}
                    </div>
                    <p className="text-gray-700 font-medium">
                        {todayStamps.length >= 3 
                            ? 'すごい！今日はたくさん頑張ったね！'
                            : todayStamps.length >= 1
                                ? 'いい調子！もっと頑張ろう！'
                                : '今日も一日お疲れさま！明日も頑張ろうね！'}
                    </p>
                </div>

                {/* 時間別のアドバイス */}
                <div className="bg-white/60 backdrop-blur-sm rounded-2xl p-4 text-center">
                    <div className="text-2xl mb-2">💡</div>
                    <p className="text-sm text-gray-600">
                        {(() => {
                            const hour = new Date().getHours();
                            if (hour < 9) return '今日も一日頑張ろうね！';
                            if (hour < 12) return 'お手伝いや勉強を頑張ってみよう！';
                            if (hour < 15) return 'お昼の後も元気に過ごそう！';
                            if (hour < 18) return '今日一日お疲れさま！';
                            return '今日も一日頑張ったね！ゆっくり休もう';
                        })()}
                    </p>
                </div>
            </div>

            <style jsx global>{`
                @keyframes fadeInUp {
                    from {
                        opacity: 0;
                        transform: translateY(20px);
                    }
                    to {
                        opacity: 1;
                        transform: translateY(0);
                    }
                }
            `}</style>
        </ChildLayout>
    );
}