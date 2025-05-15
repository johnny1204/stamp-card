import React from 'react';
import { Child } from '../types';
import ChildSwitcher from './ChildSwitcher';
import GlobalMenu from './GlobalMenu';

interface Props {
    title: string;
    subtitle?: string;
    child: Child;
    children: Child[];
    currentPage?: string;
    actions?: React.ReactNode;
    className?: string;
}

export default function PageHeader({ 
    title, 
    subtitle, 
    child, 
    children, 
    currentPage,
    actions,
    className = '' 
}: Props) {
    return (
        <div className={`bg-white rounded-lg shadow-md p-6 mb-6 ${className}`}>
            <div className="flex items-center justify-between">
                <div className="flex items-center space-x-4 flex-1">
                    {/* メインタイトル */}
                    <div>
                        <h1 className="text-2xl font-bold text-gray-800">
                            {title}
                        </h1>
                        {subtitle && (
                            <p className="text-gray-600 mt-1">
                                {subtitle}
                            </p>
                        )}
                    </div>

                    {/* 子ども切り替え */}
                    <ChildSwitcher 
                        currentChild={child}
                        children={children}
                        baseRoute={getBaseRouteFromCurrentPage(currentPage)}
                    />
                </div>

                {/* 右側のアクション */}
                <div className="flex items-center space-x-3">
                    {actions && (
                        <div>
                            {actions}
                        </div>
                    )}
                    
                    {/* グローバルメニュー */}
                    <GlobalMenu 
                        currentChild={child}
                        currentPage={currentPage}
                    />
                </div>
            </div>
        </div>
    );
}

// 現在のページから適切なbaseRouteを決定する関数
function getBaseRouteFromCurrentPage(currentPage?: string): string {
    switch (currentPage) {
        case 'stamp-cards':
            return 'children.stamp-cards.index';
        case 'stamps':
            return 'children.stamps.index';
        case 'calendar':
            return 'children.calendar.monthly';
        case 'statistics':
            return 'children.statistics.dashboard';
        case 'goals':
            return 'children.goals.index';
        case 'reports':
            return 'children.reports.dashboard';
        default:
            return 'children.stamp-cards.index';
    }
}