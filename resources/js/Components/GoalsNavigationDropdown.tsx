import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Child } from '@/types';

interface GoalsNavigationDropdownProps {
    children: Child[];
    className?: string;
}

const GoalsNavigationDropdown: React.FC<GoalsNavigationDropdownProps> = ({ children, className = '' }) => {
    const [isOpen, setIsOpen] = useState(false);

    // 子どもがいない場合の処理
    if (children.length === 0) {
        return (
            <Link 
                href="/children/create"
                className={className}
            >
                🎯 目標
            </Link>
        );
    }

    // 子どもが1人の場合は直接リンク
    if (children.length === 1) {
        return (
            <Link 
                href={`/children/${children[0].id}/goals`}
                className={className}
            >
                🎯 目標
            </Link>
        );
    }

    // 複数の子どもがいる場合はドロップダウン
    return (
        <div className="relative">
            <button 
                onClick={() => setIsOpen(!isOpen)}
                className={`${className} cursor-pointer`}
                type="button"
            >
                🎯 目標 ▼
            </button>
            
            {isOpen && (
                <>
                    {/* バックドロップ（クリックで閉じる） */}
                    <div 
                        className="fixed inset-0 z-40"
                        onClick={() => setIsOpen(false)}
                    />
                    
                    <div className="absolute top-full left-0 mt-1 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                        <div className="py-1">
                            {children.map((child) => (
                                <Link
                                    key={child.id}
                                    href={`/children/${child.id}/goals`}
                                    className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                    onClick={() => setIsOpen(false)}
                                >
                                    {child.name}の目標
                                </Link>
                            ))}
                        </div>
                    </div>
                </>
            )}
        </div>
    );
};

export default GoalsNavigationDropdown;