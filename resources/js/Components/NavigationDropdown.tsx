import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import { Child } from '@/types';

interface NavigationDropdownProps {
    children: Child[];
    type: 'calendar' | 'statistics';
    className?: string;
}

const NavigationDropdown: React.FC<NavigationDropdownProps> = ({ children, type, className = '' }) => {
    const [isOpen, setIsOpen] = useState(false);

    const getIcon = () => type === 'calendar' ? '📅' : '📊';
    const getLabel = () => type === 'calendar' ? 'カレンダー' : '統計';
    const getRoute = (childId: number) => 
        type === 'calendar' 
            ? `/children/${childId}/calendar/monthly`
            : `/children/${childId}/statistics/dashboard`;

    // 子どもがいない場合の処理
    if (children.length === 0) {
        return (
            <Link 
                href="/children/create"
                className={className}
            >
                {getIcon()} {getLabel()}
            </Link>
        );
    }

    // 子どもが1人の場合は直接リンク
    if (children.length === 1) {
        return (
            <Link 
                href={getRoute(children[0].id)}
                className={className}
            >
                {getIcon()} {getLabel()}
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
                {getIcon()} {getLabel()} ▼
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
                                    href={getRoute(child.id)}
                                    className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
                                    onClick={() => setIsOpen(false)}
                                >
                                    {child.name}の{getLabel()}
                                </Link>
                            ))}
                        </div>
                    </div>
                </>
            )}
        </div>
    );
};

export default NavigationDropdown;