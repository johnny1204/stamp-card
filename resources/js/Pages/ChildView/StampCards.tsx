import React from 'react';
import { Head } from '@inertiajs/react';
import ChildLayout from '@/Layouts/ChildLayout';
import { Child, StampCard } from '@/types';

interface Props {
    child: Child;
    stampCards: StampCard[];
    targetStamps: number;
}

export default function StampCards({ child, stampCards, targetStamps }: Props) {
    const calculateProgress = (stampCard: StampCard) => {
        if (!stampCard.stamps) return 0;
        return Math.min(stampCard.stamps.length, targetStamps);
    };

    const calculateProgressPercentage = (stampCard: StampCard) => {
        const progress = calculateProgress(stampCard);
        return Math.round((progress / targetStamps) * 100);
    };

    const isCompleted = (stampCard: StampCard) => {
        return calculateProgress(stampCard) >= targetStamps;
    };

    const renderStampSlots = (stampCard: StampCard) => {
        const slots = [];
        const stampsCount = stampCard.stamps?.length || 0;
        
        for (let i = 0; i < targetStamps; i++) {
            const hasStamp = i < stampsCount;
            slots.push(
                <div
                    key={i}
                    className={`
                        w-12 h-12 rounded-full border-2 flex items-center justify-center text-lg font-bold
                        ${hasStamp 
                            ? 'bg-gradient-to-br from-yellow-400 to-orange-500 border-yellow-300 text-white shadow-lg' 
                            : 'bg-gray-100 border-gray-300 text-gray-400'
                        }
                    `}
                >
                    {hasStamp ? '⭐' : i + 1}
                </div>
            );
        }
        return slots;
    };

    return (
        <ChildLayout title={`${child.name}のスタンプカード`} childName={child.name}>
            <div className="px-4 py-6 space-y-6">
                {/* 説明 */}
                <div className="bg-white/80 backdrop-blur-sm rounded-2xl p-6 text-center shadow-lg">
                    <div className="text-4xl mb-3">🎴</div>
                    <h2 className="text-xl font-bold text-gray-800 mb-2">
                        スタンプカード
                    </h2>
                    <p className="text-gray-600">
                        {targetStamps}個スタンプが集まるとカードが完成するよ！
                    </p>
                </div>

                {/* スタンプカード一覧 */}
                <div className="space-y-6">
                    {stampCards.length > 0 ? (
                        stampCards.map((stampCard) => {
                            const progress = calculateProgress(stampCard);
                            const percentage = calculateProgressPercentage(stampCard);
                            const completed = isCompleted(stampCard);

                            return (
                                <div 
                                    key={stampCard.id}
                                    className={`
                                        bg-white/80 backdrop-blur-sm rounded-2xl p-6 shadow-lg
                                        ${completed ? 'ring-4 ring-yellow-300 bg-gradient-to-br from-yellow-50/80 to-orange-50/80' : ''}
                                    `}
                                >
                                    {/* カードヘッダー */}
                                    <div className="flex items-center justify-between mb-4">
                                        <div className="flex items-center space-x-3">
                                            <div className={`
                                                w-12 h-12 rounded-full flex items-center justify-center text-2xl
                                                ${completed 
                                                    ? 'bg-gradient-to-br from-yellow-400 to-orange-500' 
                                                    : 'bg-gradient-to-br from-blue-400 to-purple-500'
                                                }
                                            `}>
                                                {completed ? '🏆' : '🎯'}
                                            </div>
                                            <div>
                                                <h3 className="font-bold text-gray-800">
                                                    カード #{stampCard.card_number}
                                                </h3>
                                                <p className="text-sm text-gray-600">
                                                    {new Date(stampCard.created_at).toLocaleDateString('ja-JP')}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        {completed && (
                                            <div className="text-right">
                                                <div className="text-2xl">🎉</div>
                                                <div className="text-xs text-yellow-600 font-bold">完成!</div>
                                            </div>
                                        )}
                                    </div>

                                    {/* 進捗バー */}
                                    <div className="mb-4">
                                        <div className="flex justify-between items-center mb-2">
                                            <span className="text-sm font-medium text-gray-600">
                                                進捗: {progress} / {targetStamps}
                                            </span>
                                            <span className="text-sm font-bold text-blue-600">
                                                {percentage}%
                                            </span>
                                        </div>
                                        <div className="w-full bg-gray-200 rounded-full h-3">
                                            <div 
                                                className={`
                                                    h-3 rounded-full transition-all duration-500 ease-out
                                                    ${completed 
                                                        ? 'bg-gradient-to-r from-yellow-400 to-orange-500' 
                                                        : 'bg-gradient-to-r from-blue-400 to-purple-500'
                                                    }
                                                `}
                                                style={{ width: `${percentage}%` }}
                                            />
                                        </div>
                                    </div>

                                    {/* スタンプスロット */}
                                    <div className="grid grid-cols-5 gap-3 mb-4">
                                        {renderStampSlots(stampCard)}
                                    </div>

                                    {/* メッセージ */}
                                    <div className={`
                                        text-center p-3 rounded-lg
                                        ${completed 
                                            ? 'bg-yellow-100 text-yellow-800' 
                                            : 'bg-blue-50 text-blue-700'
                                        }
                                    `}>
                                        {completed ? (
                                            <p className="font-bold">
                                                🌟 おめでとう！カードが完成したよ！ 🌟
                                            </p>
                                        ) : (
                                            <p>
                                                あと{targetStamps - progress}個でカード完成！頑張って！
                                            </p>
                                        )}
                                    </div>
                                </div>
                            );
                        })
                    ) : (
                        <div className="text-center py-12">
                            <div className="text-6xl mb-4">🎴</div>
                            <h3 className="text-lg font-bold text-gray-800 mb-2">
                                まだカードがないよ
                            </h3>
                            <p className="text-gray-600">
                                スタンプをもらうと<br />
                                自動でカードが作られるよ！
                            </p>
                        </div>
                    )}
                </div>

                {/* 励ましメッセージ */}
                {stampCards.length > 0 && (
                    <div className="bg-gradient-to-r from-pink-400/20 to-purple-400/20 backdrop-blur-sm rounded-2xl p-6 text-center">
                        <div className="text-3xl mb-3">💪</div>
                        <p className="text-gray-700 font-medium">
                            {stampCards.some(isCompleted)
                                ? 'すごい！完成したカードがあるね！'
                                : 'いい感じ！カード完成まで頑張ろう！'}
                        </p>
                    </div>
                )}
            </div>
        </ChildLayout>
    );
}