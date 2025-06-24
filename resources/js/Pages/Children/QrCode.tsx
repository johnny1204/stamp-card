import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Child } from '@/types';

interface QrCodeData {
    type: string;
    label: string;
    svg: string;
    url: string;
}

interface Props {
    child: Child;
    qrCodes: QrCodeData[];
}

export default function QrCode({ child, qrCodes }: Props) {
    const [selectedQrCode, setSelectedQrCode] = useState<QrCodeData>(qrCodes[0]);
    const [showUrl, setShowUrl] = useState(false);

    const handlePrint = () => {
        window.print();
    };

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text).then(() => {
            alert('URLをコピーしました！');
        });
    };

    const downloadQrCode = () => {
        const blob = new Blob([selectedQrCode.svg], { type: 'image/svg+xml' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${child.name}_${selectedQrCode.type}_qrcode.svg`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    return (
        <AppLayout>
            <Head title={`${child.name}のQRコード`} />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* ヘッダー */}
                    <div className="text-center mb-8">
                        <h1 className="text-3xl font-bold text-gray-900 mb-2">
                            {child.name}のQRコード
                        </h1>
                        <p className="text-gray-600">
                            スマホでQRコードを読み取って、{child.name}専用のスタンプ帳にアクセスできます
                        </p>
                    </div>

                    <div className="grid lg:grid-cols-2 gap-8">
                        {/* QRコード選択 */}
                        <div className="space-y-6">
                            <div className="bg-white rounded-xl shadow-lg p-6">
                                <h2 className="text-xl font-semibold text-gray-900 mb-4">
                                    表示するページを選択
                                </h2>
                                <div className="space-y-3">
                                    {qrCodes.map((qrCode) => (
                                        <button
                                            key={qrCode.type}
                                            onClick={() => setSelectedQrCode(qrCode)}
                                            className={`w-full p-4 rounded-lg border-2 transition-all duration-200 text-left ${
                                                selectedQrCode.type === qrCode.type
                                                    ? 'border-blue-500 bg-blue-50 text-blue-900'
                                                    : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'
                                            }`}
                                        >
                                            <div className="font-medium">{qrCode.label}</div>
                                            <div className="text-sm text-gray-500 mt-1">
                                                {qrCode.type === 'stamps' && '全てのスタンプを表示'}
                                                {qrCode.type === 'stamp-cards' && 'スタンプカード形式で表示'}
                                                {qrCode.type === 'today-stamps' && '今日のスタンプのみ表示'}
                                            </div>
                                        </button>
                                    ))}
                                </div>
                            </div>

                            {/* URL表示 */}
                            <div className="bg-white rounded-xl shadow-lg p-6">
                                <div className="flex items-center justify-between mb-3">
                                    <h3 className="text-lg font-semibold text-gray-900">
                                        直接リンク
                                    </h3>
                                    <button
                                        onClick={() => setShowUrl(!showUrl)}
                                        className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                                    >
                                        {showUrl ? '非表示' : '表示'}
                                    </button>
                                </div>
                                {showUrl && (
                                    <div className="space-y-3">
                                        <div className="p-3 bg-gray-50 rounded-lg border text-sm font-mono break-all">
                                            {selectedQrCode.url}
                                        </div>
                                        <button
                                            onClick={() => copyToClipboard(selectedQrCode.url)}
                                            className="w-full py-2 px-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium"
                                        >
                                            📋 URLをコピー
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>

                        {/* QRコード表示 */}
                        <div className="space-y-6">
                            <div className="bg-white rounded-xl shadow-lg p-6 text-center">
                                <h2 className="text-xl font-semibold text-gray-900 mb-4">
                                    {selectedQrCode.label}
                                </h2>
                                
                                {/* QRコード */}
                                <div className="flex justify-center mb-6">
                                    <div 
                                        className="p-4 bg-white rounded-lg border-2 border-gray-200"
                                        dangerouslySetInnerHTML={{ __html: selectedQrCode.svg }}
                                    />
                                </div>

                                {/* 操作ボタン */}
                                <div className="space-y-3">
                                    <button
                                        onClick={handlePrint}
                                        className="w-full py-3 px-6 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                                    >
                                        🖨️ 印刷
                                    </button>
                                    <button
                                        onClick={downloadQrCode}
                                        className="w-full py-3 px-6 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                                    >
                                        💾 SVGダウンロード
                                    </button>
                                </div>
                            </div>

                            {/* 使い方 */}
                            <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-6">
                                <h3 className="text-lg font-semibold text-yellow-800 mb-3">
                                    📱 使い方
                                </h3>
                                <ol className="space-y-2 text-sm text-yellow-700">
                                    <li>1. スマホのカメラでQRコードを読み取る</li>
                                    <li>2. ブラウザで{child.name}専用ページが開く</li>
                                    <li>3. スタンプ帳を楽しく見ることができる</li>
                                </ol>
                                <div className="mt-4 p-3 bg-yellow-100 rounded-lg">
                                    <p className="text-xs text-yellow-600">
                                        💡 QRコードは印刷して冷蔵庫に貼ったり、
                                        {child.name}の机の近くに置いておくと便利です
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* 戻るボタン */}
                    <div className="mt-8 text-center">
                        <a
                            href={route('children.show', child.id)}
                            className="inline-flex items-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors font-medium"
                        >
                            ← {child.name}の詳細に戻る
                        </a>
                    </div>
                </div>
            </div>

            {/* 印刷用スタイル */}
            <style jsx>{`
                @media print {
                    body * {
                        visibility: hidden;
                    }
                    .print-area, .print-area * {
                        visibility: visible;
                    }
                    .print-area {
                        position: absolute;
                        left: 0;
                        top: 0;
                        width: 100%;
                    }
                }
            `}</style>
        </AppLayout>
    );
}