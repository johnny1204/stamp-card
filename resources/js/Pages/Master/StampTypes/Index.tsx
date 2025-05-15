import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

interface StampType {
    id: number;
    name: string;
    icon: string | null;
    color: string | null;
    category: 'help' | 'lifestyle' | 'behavior' | 'custom';
    is_custom: boolean;
    is_system_default: boolean;
}

interface Category {
    [key: string]: string;
}

interface Props {
    systemStampTypes: StampType[];
    customStampTypes: StampType[];
    categories: Category;
    colors: string[];
    familyId: number;
}

export default function Index({
    systemStampTypes,
    customStampTypes,
    categories,
    colors,
    familyId
}: Props) {
    const { delete: destroy } = useForm();

    const handleDelete = (id: number, name: string) => {
        if (confirm(`スタンプ種類「${name}」を削除しますか？`)) {
            destroy(route('master.stamp-types.destroy', id));
        }
    };

    const getCategoryLabel = (category: string) => {
        return categories[category] || category;
    };

    const StampTypeCard = ({ stampType, showActions = false }: { stampType: StampType; showActions?: boolean }) => (
        <div className="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
            <div className="flex items-center justify-between">
                <div className="flex items-center space-x-3">
                    <div 
                        className="w-12 h-12 rounded-lg flex items-center justify-center text-white text-xl"
                        style={{ backgroundColor: stampType.color || '#6B7280' }}
                    >
                        {stampType.icon || '🏷️'}
                    </div>
                    <div>
                        <h3 className="font-medium text-gray-900">{stampType.name}</h3>
                        <p className="text-sm text-gray-500">{getCategoryLabel(stampType.category)}</p>
                    </div>
                </div>
                {showActions && (
                    <div className="flex space-x-2">
                        <Link
                            href={route('master.stamp-types.edit', stampType.id)}
                            className="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-indigo-600 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            編集
                        </Link>
                        <button
                            onClick={() => handleDelete(stampType.id, stampType.name)}
                            className="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-red-600 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                        >
                            削除
                        </button>
                    </div>
                )}
            </div>
        </div>
    );

    return (
        <AppLayout title="スタンプ種類マスタ管理">
            <Head title="スタンプ種類マスタ管理" />

            <div className="space-y-8">
                {/* ヘッダー */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900">スタンプ種類マスタ管理</h1>
                        <p className="mt-1 text-sm text-gray-600">
                            システム標準のスタンプ種類と家族専用のカスタムスタンプ種類を管理します
                        </p>
                    </div>
                    <Link
                        href={route('master.stamp-types.create')}
                        className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                        </svg>
                        新しいスタンプ種類を作成
                    </Link>
                </div>

                {/* システムデフォルトスタンプ */}
                <div className="bg-white shadow rounded-lg">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h2 className="text-lg font-medium text-gray-900">システム標準スタンプ</h2>
                        <p className="mt-1 text-sm text-gray-600">
                            全ての家族で利用できるシステム標準のスタンプ種類です（編集・削除不可）
                        </p>
                    </div>
                    <div className="p-6">
                        {systemStampTypes.length > 0 ? (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {systemStampTypes.map((stampType) => (
                                    <StampTypeCard 
                                        key={stampType.id} 
                                        stampType={stampType} 
                                        showActions={false}
                                    />
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8">
                                <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <h3 className="mt-2 text-sm font-medium text-gray-900">システムスタンプなし</h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    システム標準スタンプが登録されていません
                                </p>
                            </div>
                        )}
                    </div>
                </div>

                {/* カスタムスタンプ */}
                <div className="bg-white shadow rounded-lg">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h2 className="text-lg font-medium text-gray-900">家族専用カスタムスタンプ</h2>
                        <p className="mt-1 text-sm text-gray-600">
                            この家族専用のカスタムスタンプ種類です（編集・削除可能）
                        </p>
                    </div>
                    <div className="p-6">
                        {customStampTypes.length > 0 ? (
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                {customStampTypes.map((stampType) => (
                                    <StampTypeCard 
                                        key={stampType.id} 
                                        stampType={stampType} 
                                        showActions={true}
                                    />
                                ))}
                            </div>
                        ) : (
                            <div className="text-center py-8">
                                <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                <h3 className="mt-2 text-sm font-medium text-gray-900">カスタムスタンプなし</h3>
                                <p className="mt-1 text-sm text-gray-500">
                                    まだカスタムスタンプが作成されていません
                                </p>
                                <div className="mt-6">
                                    <Link
                                        href={route('master.stamp-types.create')}
                                        className="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                        </svg>
                                        最初のカスタムスタンプを作成
                                    </Link>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* 統計情報 */}
                <div className="bg-white shadow rounded-lg">
                    <div className="px-6 py-4 border-b border-gray-200">
                        <h2 className="text-lg font-medium text-gray-900">利用状況</h2>
                    </div>
                    <div className="p-6">
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div className="text-center">
                                <div className="text-2xl font-bold text-indigo-600">{systemStampTypes.length}</div>
                                <div className="text-sm text-gray-500">システム標準</div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-green-600">{customStampTypes.length}</div>
                                <div className="text-sm text-gray-500">家族専用</div>
                            </div>
                            <div className="text-center">
                                <div className="text-2xl font-bold text-purple-600">{systemStampTypes.length + customStampTypes.length}</div>
                                <div className="text-sm text-gray-500">合計利用可能</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}