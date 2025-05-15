import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

interface Child {
    id: number;
    name: string;
}

interface StampType {
    id: number;
    name: string;
    icon: string | null;
    color: string | null;
    category: string;
}

interface Goal {
    id: number;
    stamp_type_id: number;
    target_count: number;
    period_type: 'weekly' | 'monthly';
    start_date: string;
    end_date: string;
    reward_text: string | null;
    is_achieved: boolean;
    current_count: number;
    stamp_type: StampType;
}

interface Props {
    child: Child;
    goal: Goal;
    stampTypes: StampType[];
}

interface FormData {
    stamp_type_id: number;
    target_count: number;
    period_type: 'weekly' | 'monthly';
    start_date: string;
    end_date: string;
    reward_text: string;
}

export default function Edit({ child, goal, stampTypes }: Props) {
    const { data, setData, patch, processing, errors } = useForm<FormData>({
        stamp_type_id: goal.stamp_type_id,
        target_count: goal.target_count,
        period_type: goal.period_type,
        start_date: goal.start_date,
        end_date: goal.end_date,
        reward_text: goal.reward_text || '',
    });

    React.useEffect(() => {
        // 期間タイプが変更されたときに終了日を自動計算
        const startDate = new Date(data.start_date);
        if (data.period_type === 'weekly') {
            const endDate = new Date(startDate);
            endDate.setDate(startDate.getDate() + 6);
            setData('end_date', endDate.toISOString().split('T')[0]);
        } else if (data.period_type === 'monthly') {
            const endDate = new Date(startDate);
            endDate.setMonth(startDate.getMonth() + 1);
            endDate.setDate(endDate.getDate() - 1);
            setData('end_date', endDate.toISOString().split('T')[0]);
        }
    }, [data.start_date, data.period_type]);

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        patch(route('children.goals.update', [child.id, goal.id]));
    };

    const selectedStampType = stampTypes.find(st => st.id === data.stamp_type_id);

    return (
        <AppLayout title={`${child.name}の目標を編集`}>
            <Head title={`${child.name}の目標を編集`} />

            <div className="max-w-2xl mx-auto">
                {/* ヘッダー */}
                <div className="mb-8">
                    <div className="flex items-center space-x-4 mb-4">
                        <Link
                            href={route('children.goals.show', [child.id, goal.id])}
                            className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                        >
                            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                            </svg>
                            目標詳細に戻る
                        </Link>
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900">{child.name}の目標を編集</h1>
                    <p className="mt-1 text-sm text-gray-600">
                        現在の進捗: {goal.current_count}/{goal.target_count}個
                        {goal.is_achieved && <span className="text-green-600 font-medium ml-2">（達成済み）</span>}
                    </p>
                </div>

                {/* 達成済み警告 */}
                {goal.is_achieved && (
                    <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
                        <div className="flex">
                            <svg className="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fillRule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clipRule="evenodd" />
                            </svg>
                            <div>
                                <h3 className="text-sm font-medium text-yellow-800">注意</h3>
                                <p className="text-yellow-700 text-sm mt-1">
                                    この目標は既に達成されています。編集すると達成状態がリセットされる場合があります。
                                </p>
                            </div>
                        </div>
                    </div>
                )}

                {/* プレビュー */}
                <div className="bg-white shadow rounded-lg p-6 mb-8">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">編集後のプレビュー</h2>
                    <div className="bg-gradient-to-r from-blue-50 to-purple-50 border border-gray-200 rounded-lg p-4">
                        <div className="flex items-center space-x-3 mb-3">
                            {selectedStampType && (
                                <div 
                                    className="w-10 h-10 rounded-lg flex items-center justify-center text-white text-lg"
                                    style={{ backgroundColor: selectedStampType.color || '#6B7280' }}
                                >
                                    {selectedStampType.icon || '🏷️'}
                                </div>
                            )}
                            <div>
                                <h3 className="font-medium text-gray-900">
                                    {selectedStampType?.name || 'スタンプ種類'} を {data.target_count}個集める
                                </h3>
                                <p className="text-sm text-gray-500">
                                    期間: {data.start_date} ～ {data.end_date}
                                    ({data.period_type === 'weekly' ? '週間' : '月間'}目標)
                                </p>
                            </div>
                        </div>
                        {data.reward_text && (
                            <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                                <p className="text-sm text-yellow-800">
                                    <span className="font-medium">ご褒美:</span> {data.reward_text}
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* フォーム */}
                <div className="bg-white shadow rounded-lg">
                    <form onSubmit={handleSubmit} className="space-y-6 p-6">
                        {/* スタンプ種類 */}
                        <div>
                            <label htmlFor="stamp_type_id" className="block text-sm font-medium text-gray-700">
                                スタンプ種類 <span className="text-red-500">*</span>
                            </label>
                            <select
                                name="stamp_type_id"
                                id="stamp_type_id"
                                required
                                value={data.stamp_type_id}
                                onChange={(e) => setData('stamp_type_id', parseInt(e.target.value))}
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm px-3 py-2"
                            >
                                {stampTypes.map((stampType) => (
                                    <option key={stampType.id} value={stampType.id}>
                                        {stampType.icon} {stampType.name}
                                    </option>
                                ))}
                            </select>
                            {errors.stamp_type_id && (
                                <p className="mt-1 text-sm text-red-600">{errors.stamp_type_id}</p>
                            )}
                        </div>

                        {/* 目標個数 */}
                        <div>
                            <label htmlFor="target_count" className="block text-sm font-medium text-gray-700">
                                目標個数 <span className="text-red-500">*</span>
                            </label>
                            <div className="mt-1 relative">
                                <input
                                    type="number"
                                    name="target_count"
                                    id="target_count"
                                    required
                                    min="1"
                                    max="100"
                                    value={data.target_count}
                                    onChange={(e) => setData('target_count', parseInt(e.target.value) || 1)}
                                    className="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                />
                                <div className="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span className="text-gray-500 sm:text-sm">個</span>
                                </div>
                            </div>
                            <p className="mt-1 text-xs text-gray-500">
                                現在の進捗: {goal.current_count}個
                                {data.target_count < goal.current_count && (
                                    <span className="text-orange-600 ml-1">
                                        （目標値が現在の進捗より少なくなっています）
                                    </span>
                                )}
                            </p>
                            {errors.target_count && (
                                <p className="mt-1 text-sm text-red-600">{errors.target_count}</p>
                            )}
                        </div>

                        {/* 期間タイプ */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                目標期間 <span className="text-red-500">*</span>
                            </label>
                            <div className="grid grid-cols-2 gap-4">
                                <label className="relative">
                                    <input
                                        type="radio"
                                        name="period_type"
                                        value="weekly"
                                        checked={data.period_type === 'weekly'}
                                        onChange={(e) => setData('period_type', e.target.value as 'weekly' | 'monthly')}
                                        className="sr-only"
                                    />
                                    <div className={`border-2 rounded-lg p-4 cursor-pointer transition-all ${
                                        data.period_type === 'weekly' 
                                            ? 'border-indigo-500 bg-indigo-50' 
                                            : 'border-gray-200 hover:border-gray-300'
                                    }`}>
                                        <div className="text-center">
                                            <div className="text-2xl mb-2">📅</div>
                                            <div className="font-medium">週間目標</div>
                                            <div className="text-sm text-gray-500">1週間で達成</div>
                                        </div>
                                    </div>
                                </label>
                                <label className="relative">
                                    <input
                                        type="radio"
                                        name="period_type"
                                        value="monthly"
                                        checked={data.period_type === 'monthly'}
                                        onChange={(e) => setData('period_type', e.target.value as 'weekly' | 'monthly')}
                                        className="sr-only"
                                    />
                                    <div className={`border-2 rounded-lg p-4 cursor-pointer transition-all ${
                                        data.period_type === 'monthly' 
                                            ? 'border-indigo-500 bg-indigo-50' 
                                            : 'border-gray-200 hover:border-gray-300'
                                    }`}>
                                        <div className="text-center">
                                            <div className="text-2xl mb-2">🗓️</div>
                                            <div className="font-medium">月間目標</div>
                                            <div className="text-sm text-gray-500">1ヶ月で達成</div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {/* 開始日 */}
                        <div>
                            <label htmlFor="start_date" className="block text-sm font-medium text-gray-700">
                                開始日 <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="date"
                                name="start_date"
                                id="start_date"
                                required
                                value={data.start_date}
                                onChange={(e) => setData('start_date', e.target.value)}
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm px-3 py-2"
                            />
                            {errors.start_date && (
                                <p className="mt-1 text-sm text-red-600">{errors.start_date}</p>
                            )}
                        </div>

                        {/* 終了日（自動計算） */}
                        <div>
                            <label htmlFor="end_date" className="block text-sm font-medium text-gray-700">
                                終了日（自動計算）
                            </label>
                            <input
                                type="date"
                                name="end_date"
                                id="end_date"
                                value={data.end_date}
                                readOnly
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 cursor-not-allowed sm:text-sm px-3 py-2"
                            />
                            <p className="mt-1 text-xs text-gray-500">
                                期間タイプと開始日に基づいて自動的に計算されます
                            </p>
                        </div>

                        {/* ご褒美 */}
                        <div>
                            <label htmlFor="reward_text" className="block text-sm font-medium text-gray-700">
                                ご褒美（達成時）
                            </label>
                            <textarea
                                name="reward_text"
                                id="reward_text"
                                rows={3}
                                value={data.reward_text}
                                onChange={(e) => setData('reward_text', e.target.value)}
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm px-3 py-2"
                                placeholder="例: 好きなおやつを買いに行こう！、映画を見に行こう！"
                            />
                            <p className="mt-1 text-xs text-gray-500">
                                目標を達成したときのご褒美を入力してください（任意）
                            </p>
                            {errors.reward_text && (
                                <p className="mt-1 text-sm text-red-600">{errors.reward_text}</p>
                            )}
                        </div>

                        {/* ボタン */}
                        <div className="flex justify-end space-x-3 pt-6">
                            <Link
                                href={route('children.goals.show', [child.id, goal.id])}
                                className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                キャンセル
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                {processing ? '更新中...' : '目標を更新'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}