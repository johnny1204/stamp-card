import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

interface Category {
    [key: string]: string;
}

interface Props {
    categories: Category;
    colors: string[];
}

interface FormData {
    name: string;
    icon: string;
    color: string;
    category: string;
}

export default function Create({ categories, colors }: Props) {
    const { data, setData, post, processing, errors } = useForm<FormData>({
        name: '',
        icon: '',
        color: colors[0] || '#6B7280',
        category: 'custom',
    });

    const [iconPreview, setIconPreview] = useState('🏷️');

    const commonIcons = [
        '🏷️', '⭐', '🎯', '✅', '🎉', '💪', '🏆', '📚', 
        '🧹', '🤝', '👋', '🦷', '💝', '🏃', '😊', '🎨',
        '🎵', '🍎', '⚽', '🎮', '🚴', '🏊', '🎪', '🎭'
    ];

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('master.stamp-types.store'));
    };

    const handleIconChange = (icon: string) => {
        setData('icon', icon);
        setIconPreview(icon);
    };

    const handleIconInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setData('icon', value);
        setIconPreview(value || '🏷️');
    };

    return (
        <AppLayout title="新しいスタンプ種類を作成">
            <Head title="新しいスタンプ種類を作成" />

            <div className="max-w-2xl mx-auto">
                {/* ヘッダー */}
                <div className="mb-8">
                    <div className="flex items-center space-x-4 mb-4">
                        <Link
                            href={route('master.stamp-types.index')}
                            className="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                        >
                            <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                            </svg>
                            スタンプ種類マスタ管理に戻る
                        </Link>
                    </div>
                    <h1 className="text-2xl font-bold text-gray-900">新しいスタンプ種類を作成</h1>
                    <p className="mt-1 text-sm text-gray-600">
                        家族専用のカスタムスタンプ種類を作成します
                    </p>
                </div>

                {/* プレビュー */}
                <div className="bg-white shadow rounded-lg p-6 mb-8">
                    <h2 className="text-lg font-medium text-gray-900 mb-4">プレビュー</h2>
                    <div className="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <div className="flex items-center space-x-3">
                            <div 
                                className="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl"
                                style={{ backgroundColor: data.color }}
                            >
                                {iconPreview}
                            </div>
                            <div>
                                <h3 className="font-medium text-gray-900">
                                    {data.name || 'スタンプ名'}
                                </h3>
                                <p className="text-sm text-gray-500">
                                    {categories[data.category] || 'カテゴリ'}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {/* フォーム */}
                <div className="bg-white shadow rounded-lg">
                    <form onSubmit={handleSubmit} className="space-y-6 p-6">
                        {/* スタンプ名 */}
                        <div>
                            <label htmlFor="name" className="block text-sm font-medium text-gray-700">
                                スタンプ名 <span className="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                name="name"
                                id="name"
                                required
                                maxLength={100}
                                value={data.name}
                                onChange={(e) => setData('name', e.target.value)}
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="例: お風呂掃除"
                            />
                            {errors.name && (
                                <p className="mt-1 text-sm text-red-600">{errors.name}</p>
                            )}
                        </div>

                        {/* アイコン */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                アイコン
                            </label>
                            
                            {/* よく使うアイコン */}
                            <div className="mb-4">
                                <p className="text-sm text-gray-600 mb-2">よく使うアイコン:</p>
                                <div className="grid grid-cols-8 gap-2">
                                    {commonIcons.map((icon) => (
                                        <button
                                            key={icon}
                                            type="button"
                                            onClick={() => handleIconChange(icon)}
                                            className={`w-10 h-10 rounded-lg border-2 flex items-center justify-center text-lg hover:bg-gray-50 transition-colors ${
                                                data.icon === icon 
                                                    ? 'border-indigo-500 bg-indigo-50' 
                                                    : 'border-gray-200'
                                            }`}
                                        >
                                            {icon}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* カスタムアイコン入力 */}
                            <div>
                                <label htmlFor="icon" className="block text-sm text-gray-600 mb-1">
                                    または直接入力:
                                </label>
                                <input
                                    type="text"
                                    name="icon"
                                    id="icon"
                                    value={data.icon}
                                    onChange={handleIconInputChange}
                                    className="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                    placeholder="絵文字を入力"
                                />
                            </div>
                        </div>

                        {/* 色 */}
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-3">
                                色
                            </label>
                            <div className="grid grid-cols-5 gap-3">
                                {colors.map((color) => (
                                    <button
                                        key={color}
                                        type="button"
                                        onClick={() => setData('color', color)}
                                        className={`w-12 h-12 rounded-lg border-4 transition-all ${
                                            data.color === color 
                                                ? 'border-gray-900 scale-110' 
                                                : 'border-gray-200 hover:border-gray-400'
                                        }`}
                                        style={{ backgroundColor: color }}
                                        title={color}
                                    />
                                ))}
                            </div>
                            <div className="mt-3">
                                <label htmlFor="custom-color" className="block text-sm text-gray-600 mb-1">
                                    または色コードを直接入力:
                                </label>
                                <input
                                    type="color"
                                    name="custom-color"
                                    id="custom-color"
                                    value={data.color}
                                    onChange={(e) => setData('color', e.target.value)}
                                    className="block w-16 h-8 border border-gray-300 rounded cursor-pointer"
                                />
                            </div>
                        </div>

                        {/* カテゴリ */}
                        <div>
                            <label htmlFor="category" className="block text-sm font-medium text-gray-700">
                                カテゴリ <span className="text-red-500">*</span>
                            </label>
                            <select
                                name="category"
                                id="category"
                                required
                                value={data.category}
                                onChange={(e) => setData('category', e.target.value)}
                                className="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                            >
                                {Object.entries(categories).map(([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ))}
                            </select>
                            {errors.category && (
                                <p className="mt-1 text-sm text-red-600">{errors.category}</p>
                            )}
                        </div>

                        {/* ボタン */}
                        <div className="flex justify-end space-x-3 pt-6">
                            <Link
                                href={route('master.stamp-types.index')}
                                className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                キャンセル
                            </Link>
                            <button
                                type="submit"
                                disabled={processing}
                                className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                {processing ? '作成中...' : '作成'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}