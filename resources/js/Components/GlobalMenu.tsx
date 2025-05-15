import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Child } from '../types';

interface Props {
    currentChild: Child | null;
    currentPage?: string;
    className?: string;
}

interface MenuItem {
    label: string;
    icon: string;
    href: string;
    key: string;
}

export default function GlobalMenu({ currentChild, currentPage, className = '' }: Props) {
    const [isOpen, setIsOpen] = useState(false);
    
    console.log('GlobalMenu rendering:', { currentPage, childId: currentChild?.id, childName: currentChild?.name });

    // 現在の子どもが選択されていない場合は表示しない
    if (!currentChild) {
        return null;
    }

    const menuItems: MenuItem[] = [
        {
            label: 'スタンプカード',
            icon: '📋',
            href: `/children/${currentChild.id}/stamp-cards`,
            key: 'stamp-cards'
        },
        {
            label: 'スタンプを押す',
            icon: '⭐',
            href: `/children/${currentChild.id}/stamps/create`,
            key: 'stamps-create'
        },
        {
            label: 'スタンプ一覧',
            icon: '📝',
            href: `/children/${currentChild.id}/stamps`,
            key: 'stamps'
        },
        {
            label: 'カレンダー',
            icon: '📅',
            href: `/children/${currentChild.id}/calendar/monthly`,
            key: 'calendar'
        },
        {
            label: '統計・レポート',
            icon: '📊',
            href: `/children/${currentChild.id}/statistics/dashboard`,
            key: 'statistics'
        },
        {
            label: '目標管理',
            icon: '🎯',
            href: `/children/${currentChild.id}/goals`,
            key: 'goals'
        },
        {
            label: 'レポート',
            icon: '📈',
            href: `/children/${currentChild.id}/reports/dashboard`,
            key: 'reports'
        }
    ];

    const isCurrentPage = (itemKey: string): boolean => {
        return currentPage === itemKey;
    };


    return (
        <div className={`relative ${className}`}>
            {/* メニューボタン */}
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <span className="text-lg mr-2">🏠</span>
                <span>メニュー</span>
                <svg 
                    className={`w-4 h-4 ml-2 transition-transform ${isOpen ? 'rotate-180' : ''}`} 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24"
                >
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                </svg>
            </button>

            {isOpen && (
                <>
                    {/* オーバーレイ */}
                    <div 
                        className="fixed inset-0 z-10" 
                        onClick={() => setIsOpen(false)}
                    />
                    
                    {/* ドロップダウンメニュー */}
                    <div className="absolute z-20 mt-1 w-64 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                        <div className="py-2" role="menu">
                            {/* ホームリンク */}
                            <Link
                                href="/"
                                onClick={() => setIsOpen(false)}
                                className="flex items-center space-x-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 border-b border-gray-200"
                                role="menuitem"
                            >
                                <span className="text-lg">🏠</span>
                                <div>
                                    <div className="font-medium">ホーム</div>
                                    <div className="text-xs text-gray-500">メイン画面に戻る</div>
                                </div>
                            </Link>

                            {/* 子ども関連メニュー */}
                            <div className="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-200">
                                {currentChild.name}のメニュー
                            </div>
                            
                            {menuItems.map((item) => (
                                <Link
                                    key={item.key}
                                    href={item.href}
                                    onClick={() => setIsOpen(false)}
                                    className={`flex items-center space-x-3 px-4 py-3 text-sm hover:bg-gray-100 focus:outline-none focus:bg-gray-100 ${
                                        isCurrentPage(item.key) 
                                            ? 'bg-indigo-50 text-indigo-700 border-r-2 border-indigo-500' 
                                            : 'text-gray-700'
                                    }`}
                                    role="menuitem"
                                >
                                    <span className="text-lg">{item.icon}</span>
                                    <div className="flex-1">
                                        <div className="font-medium">{item.label}</div>
                                    </div>
                                    {isCurrentPage(item.key) && (
                                        <svg className="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                        </svg>
                                    )}
                                </Link>
                            ))}

                            {/* 管理メニュー */}
                            <div className="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-t border-gray-200 mt-2">
                                管理メニュー
                            </div>
                            
                            <Link
                                href="/children"
                                onClick={() => setIsOpen(false)}
                                className="flex items-center space-x-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100"
                                role="menuitem"
                            >
                                <span className="text-lg">👶</span>
                                <div>
                                    <div className="font-medium">子ども管理</div>
                                    <div className="text-xs text-gray-500">お子様の情報管理</div>
                                </div>
                            </Link>
                            
                            <Link
                                href="/master/stamp-types"
                                onClick={() => setIsOpen(false)}
                                className="flex items-center space-x-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100"
                                role="menuitem"
                            >
                                <span className="text-lg">⚙️</span>
                                <div>
                                    <div className="font-medium">マスタ管理</div>
                                    <div className="text-xs text-gray-500">スタンプ種類の設定</div>
                                </div>
                            </Link>
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}