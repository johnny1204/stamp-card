import React, { ReactNode } from 'react';
import { Head } from '@inertiajs/react';

interface Props {
    children: ReactNode;
    title?: string;
    childName?: string;
}

export default function ChildLayout({ children, title, childName }: Props) {
    return (
        <>
            <Head title={title || 'スタンプ帳'} />
            
            <div className="min-h-screen bg-gradient-to-br from-yellow-100 via-pink-50 to-blue-100">
                {/* ヘッダー */}
                <header className="bg-white/80 backdrop-blur-sm border-b border-white/20 sticky top-0 z-50">
                    <div className="px-4 py-3">
                        <div className="flex items-center justify-center space-x-3">
                            <div className="text-2xl">🎯</div>
                            <h1 className="text-xl font-bold text-gray-800">
                                {childName ? `${childName}のスタンプ帳` : 'スタンプ帳'}
                            </h1>
                        </div>
                    </div>
                </header>

                {/* メインコンテンツ */}
                <main className="pb-16">
                    {children}
                </main>

                {/* フッター */}
                <footer className="fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-sm border-t border-white/20 px-4 py-3">
                    <div className="text-center">
                        <p className="text-sm text-gray-600">
                            頑張ったね！ ✨
                        </p>
                    </div>
                </footer>
            </div>

            {/* スマホ最適化用のメタタグとスタイル */}
            <Head>
                <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
                <meta name="theme-color" content="#3B82F6" />
                <meta name="apple-mobile-web-app-capable" content="yes" />
                <meta name="apple-mobile-web-app-status-bar-style" content="default" />
                <meta name="apple-mobile-web-app-title" content="スタンプ帳" />
            </Head>

            <style jsx global>{`
                /* スマホ用の追加スタイル */
                @media (max-width: 768px) {
                    body {
                        font-size: 16px; /* ズーム防止 */
                        -webkit-text-size-adjust: 100%;
                        -webkit-tap-highlight-color: transparent;
                    }
                    
                    * {
                        -webkit-touch-callout: none;
                        -webkit-user-select: none;
                        -khtml-user-select: none;
                        -moz-user-select: none;
                        -ms-user-select: none;
                        user-select: none;
                    }
                    
                    input, textarea {
                        -webkit-user-select: text;
                        -khtml-user-select: text;
                        -moz-user-select: text;
                        -ms-user-select: text;
                        user-select: text;
                    }
                }
                
                /* プルトゥリフレッシュの無効化 */
                body {
                    overscroll-behavior: none;
                }
            `}</style>
        </>
    );
}