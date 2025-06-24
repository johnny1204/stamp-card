import React from 'react';
import { Head } from '@inertiajs/react';

interface Props {
    child: {
        id: number;
        name: string;
    };
    qrCode: {
        type: string;
        label: string;
        svg: string;
        url: string;
    };
}

export default function SingleQrCode({ child, qrCode }: Props) {
    const handlePrint = () => {
        window.print();
    };

    const downloadQrCode = () => {
        const blob = new Blob([qrCode.svg], { type: 'image/svg+xml' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `${child.name}_${qrCode.type}_qrcode.svg`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    };

    const copyToClipboard = (text: string) => {
        navigator.clipboard.writeText(text).then(() => {
            alert('URLをコピーしました！');
        });
    };

    return (
        <>
            <Head title={`${child.name}の${qrCode.label}QRコード`} />

            <div className="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
                <div className="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                    {/* ヘッダー */}
                    <div className="text-center mb-8">
                        <h1 className="text-3xl font-bold text-gray-900 mb-2">
                            {child.name}の{qrCode.label}
                        </h1>
                        <p className="text-gray-600">
                            スマホでQRコードを読み取ってアクセス
                        </p>
                    </div>

                    {/* QRコード表示 */}
                    <div className="bg-white rounded-xl shadow-lg p-8 text-center mb-6">
                        {/* QRコード */}
                        <div className="flex justify-center mb-6" id="qr-code-area">
                            <div 
                                className="p-6 bg-white rounded-lg border-2 border-gray-200"
                                dangerouslySetInnerHTML={{ __html: qrCode.svg }}
                            />
                        </div>

                        {/* 操作ボタン */}
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-6">
                            <button
                                onClick={handlePrint}
                                className="py-3 px-6 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
                            >
                                🖨️ 印刷
                            </button>
                            <button
                                onClick={downloadQrCode}
                                className="py-3 px-6 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium"
                            >
                                💾 ダウンロード
                            </button>
                            <button
                                onClick={() => copyToClipboard(qrCode.url)}
                                className="py-3 px-6 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium"
                            >
                                📋 URLコピー
                            </button>
                        </div>

                        {/* URL表示 */}
                        <div className="text-left">
                            <label className="block text-sm font-medium text-gray-700 mb-2">
                                直接リンク:
                            </label>
                            <div className="p-3 bg-gray-50 rounded-lg border text-sm font-mono break-all">
                                {qrCode.url}
                            </div>
                        </div>
                    </div>

                    {/* 使い方 */}
                    <div className="bg-yellow-50 border border-yellow-200 rounded-xl p-6 mb-6">
                        <h3 className="text-lg font-semibold text-yellow-800 mb-3">
                            📱 使い方
                        </h3>
                        <ol className="space-y-2 text-sm text-yellow-700">
                            <li>1. スマホのカメラでQRコードを読み取る</li>
                            <li>2. ブラウザで{child.name}専用ページが開く</li>
                            <li>3. {qrCode.label}を楽しく見ることができる</li>
                        </ol>
                    </div>

                    {/* 戻るボタン */}
                    <div className="text-center">
                        <a
                            href={`/children/${child.id}`}
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
                    #qr-code-area, #qr-code-area * {
                        visibility: visible;
                    }
                    #qr-code-area {
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                    }
                }
            `}</style>
        </>
    );
}