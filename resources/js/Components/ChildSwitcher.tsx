import React, { useState } from 'react';
import { router } from '@inertiajs/react';

interface Child {
    id: number;
    name: string;
    birth_date: string;
    avatar_path: string | null;
}

interface Props {
    currentChild: Child | null;
    children: Child[];
    baseRoute: string; // 'children.stamp-cards.index', 'children.calendar.monthly' など
    className?: string;
}

export default function ChildSwitcher({ currentChild, children, baseRoute, className = '' }: Props) {
    const [isOpen, setIsOpen] = useState(false);

    const handleChildChange = (child: Child) => {
        if (currentChild && child.id !== currentChild.id) {
            router.visit(route(baseRoute, child.id));
        }
        setIsOpen(false);
    };

    // 子どもが1人の場合、または現在の子どもが選択されていない場合は表示しない
    if (children.length <= 1 || !currentChild) {
        return null;
    }

    return (
        <div className={`relative ${className}`}>
            <button
                onClick={() => setIsOpen(!isOpen)}
                className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                <div className="flex items-center space-x-2">
                    {currentChild.avatar_path ? (
                        <img 
                            src={currentChild.avatar_path} 
                            alt={currentChild.name}
                            className="w-6 h-6 rounded-full object-cover"
                        />
                    ) : (
                        <div className="w-6 h-6 bg-purple-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                            {currentChild.name[0]}
                        </div>
                    )}
                    <span>{currentChild.name}</span>
                    <svg 
                        className={`w-4 h-4 transition-transform ${isOpen ? 'rotate-180' : ''}`} 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </button>

            {isOpen && (
                <>
                    {/* オーバーレイ */}
                    <div 
                        className="fixed inset-0 z-10" 
                        onClick={() => setIsOpen(false)}
                    />
                    
                    {/* ドロップダウンメニュー */}
                    <div className="absolute z-20 mt-1 w-full min-w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5">
                        <div className="py-1" role="menu">
                            {children.map((child) => (
                                <button
                                    key={child.id}
                                    onClick={() => handleChildChange(child)}
                                    className={`w-full flex items-center space-x-3 px-4 py-2 text-sm text-left hover:bg-gray-100 focus:outline-none focus:bg-gray-100 ${
                                        child.id === currentChild.id 
                                            ? 'bg-indigo-50 text-indigo-700' 
                                            : 'text-gray-700'
                                    }`}
                                    role="menuitem"
                                >
                                    {child.avatar_path ? (
                                        <img 
                                            src={child.avatar_path} 
                                            alt={child.name}
                                            className="w-8 h-8 rounded-full object-cover"
                                        />
                                    ) : (
                                        <div className="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                            {child.name[0]}
                                        </div>
                                    )}
                                    <div className="flex-1">
                                        <div className="font-medium">{child.name}</div>
                                        <div className="text-xs text-gray-500">
                                            {new Date(child.birth_date).toLocaleDateString('ja-JP', {
                                                year: 'numeric',
                                                month: 'long',
                                                day: 'numeric'
                                            })}生まれ
                                        </div>
                                    </div>
                                    {child.id === currentChild.id && (
                                        <svg className="w-4 h-4 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                                        </svg>
                                    )}
                                </button>
                            ))}
                        </div>
                    </div>
                </>
            )}
        </div>
    );
}