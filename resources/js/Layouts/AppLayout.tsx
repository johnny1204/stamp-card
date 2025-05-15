import React from 'react';
import { Head, usePage, Link } from '@inertiajs/react';
import FlashNotification from '../Components/FlashNotification';
import CacheInitializer from '../Components/CacheInitializer';
import NavigationDropdown from '../Components/NavigationDropdown';
import GoalsNavigationDropdown from '../Components/GoalsNavigationDropdown';
import ReportsNavigationDropdown from '../Components/ReportsNavigationDropdown';
import { PageProps } from '../types';

interface AppLayoutProps {
    children: React.ReactNode;
    title?: string;
}

export default function AppLayout({ children, title = 'スタンプ帳' }: AppLayoutProps) {
    const { flash, children: childrenList = [] } = usePage<PageProps>().props
    return (
        <>
            <Head title={title} />
            <CacheInitializer />
            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50">
                <header className="bg-white shadow-sm">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex justify-between h-16">
                            <div className="flex items-center">
                                <Link href="/" className="text-2xl font-bold text-purple-600 hover:text-purple-700">
                                    🎨 スタンプ帳管理システム
                                </Link>
                            </div>
                            <div className="hidden md:flex items-center space-x-2 lg:space-x-4">
                                <Link 
                                    href="/children" 
                                    className="text-gray-600 hover:text-purple-600 px-2 lg:px-3 py-2 rounded-md text-sm font-medium"
                                >
                                    お子様管理
                                </Link>
                                <NavigationDropdown
                                    children={childrenList}
                                    type="calendar"
                                    className="text-gray-600 hover:text-purple-600 px-2 lg:px-3 py-2 rounded-md text-sm font-medium cursor-pointer"
                                />
                                <NavigationDropdown
                                    children={childrenList}
                                    type="statistics"
                                    className="text-gray-600 hover:text-purple-600 px-2 lg:px-3 py-2 rounded-md text-sm font-medium cursor-pointer"
                                />
                                <GoalsNavigationDropdown
                                    children={childrenList}
                                    className="text-gray-600 hover:text-purple-600 px-2 lg:px-3 py-2 rounded-md text-sm font-medium cursor-pointer"
                                />
                                <ReportsNavigationDropdown
                                    children={childrenList}
                                    className="text-gray-600 hover:text-purple-600 px-2 lg:px-3 py-2 rounded-md text-sm font-medium cursor-pointer"
                                />
                                <Link 
                                    href="/master/stamp-types" 
                                    className="text-gray-600 hover:text-purple-600 px-2 lg:px-3 py-2 rounded-md text-sm font-medium"
                                >
                                    マスタ管理
                                </Link>
                            </div>
                            
                            {/* モバイル用ドロップダウンメニュー */}
                            <div className="md:hidden">
                                <details className="relative">
                                    <summary className="text-gray-600 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium cursor-pointer">
                                        ☰ メニュー
                                    </summary>
                                    <div className="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                        <div className="py-1">
                                            <Link 
                                                href="/children" 
                                                className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            >
                                                お子様管理
                                            </Link>
                                            <div className="px-4 py-2">
                                                <NavigationDropdown
                                                    children={childrenList}
                                                    type="calendar"
                                                    className="block text-sm text-gray-700 hover:text-gray-900"
                                                />
                                            </div>
                                            <div className="px-4 py-2">
                                                <NavigationDropdown
                                                    children={childrenList}
                                                    type="statistics"
                                                    className="block text-sm text-gray-700 hover:text-gray-900"
                                                />
                                            </div>
                                            <div className="px-4 py-2">
                                                <GoalsNavigationDropdown
                                                    children={childrenList}
                                                    className="block text-sm text-gray-700 hover:text-gray-900"
                                                />
                                            </div>
                                            <div className="px-4 py-2">
                                                <ReportsNavigationDropdown
                                                    children={childrenList}
                                                    className="block text-sm text-gray-700 hover:text-gray-900"
                                                />
                                            </div>
                                            <Link 
                                                href="/master/stamp-types" 
                                                className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            >
                                                マスタ管理
                                            </Link>
                                        </div>
                                    </div>
                                </details>
                            </div>
                        </div>
                    </div>
                </header>

                <main className="py-8">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        {children}
                    </div>
                </main>
                
                <FlashNotification flash={flash} />
            </div>
        </>
    );
}