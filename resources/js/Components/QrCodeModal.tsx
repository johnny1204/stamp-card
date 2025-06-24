import React from 'react';

interface Props {
    isOpen: boolean;
    onClose: () => void;
    child: {
        id: number;
        name: string;
    };
    qrCode: {
        type: string;
        label: string;
        svg: string;
        url: string;
    } | null;
}

export default function QrCodeModal({ isOpen, onClose, child, qrCode }: Props) {
    if (!isOpen || !qrCode) return null;

    const handlePrint = () => {
        const printWindow = window.open('', '_blank');
        if (printWindow) {
            printWindow.document.write(`
                <html>
                    <head>
                        <title>${child.name}の${qrCode.label}QRコード</title>
                        <style>
                            body { 
                                margin: 0; 
                                padding: 20px; 
                                text-align: center; 
                                font-family: sans-serif; 
                            }
                            .qr-container { 
                                display: inline-block; 
                                padding: 20px; 
                                border: 2px solid #ddd; 
                                border-radius: 10px; 
                            }
                        </style>
                    </head>
                    <body>
                        <h1>${child.name}の${qrCode.label}</h1>
                        <div class="qr-container">
                            ${qrCode.svg}
                        </div>
                        <p>スマホでQRコードを読み取ってアクセス</p>
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
            printWindow.close();
        }
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
        <div 
            className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
            onClick={onClose}
        >
            <div 
                className="bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto"
                onClick={(e) => e.stopPropagation()}
            >
                {/* ヘッダー */}
                <div className="flex items-center justify-between p-6 border-b">
                    <h2 className="text-xl font-bold text-gray-900">
                        {child.name}の{qrCode.label}
                    </h2>
                    <button
                        onClick={onClose}
                        className="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors"
                    >
                        ✕
                    </button>
                </div>

                {/* QRコード表示 */}
                <div className="p-6">
                    <div className="text-center mb-6">
                        <p className="text-gray-600 text-sm mb-4">
                            スマホでQRコードを読み取ってアクセス
                        </p>
                        
                        {/* QRコード */}
                        <div className="flex justify-center mb-6">
                            <div 
                                className="p-4 bg-gray-50 rounded-lg border"
                                dangerouslySetInnerHTML={{ __html: qrCode.svg }}
                            />
                        </div>

                        {/* 操作ボタン */}
                        <div className="grid grid-cols-3 gap-2 mb-6">
                            <button
                                onClick={handlePrint}
                                className="py-2 px-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium"
                            >
                                🖨️ 印刷
                            </button>
                            <button
                                onClick={downloadQrCode}
                                className="py-2 px-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium"
                            >
                                💾 保存
                            </button>
                            <button
                                onClick={() => copyToClipboard(qrCode.url)}
                                className="py-2 px-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium"
                            >
                                📋 コピー
                            </button>
                        </div>
                    </div>

                    {/* URL表示 */}
                    <div className="bg-gray-50 rounded-lg p-4">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            直接リンク:
                        </label>
                        <div className="p-2 bg-white rounded border text-xs font-mono break-all text-gray-600">
                            {qrCode.url}
                        </div>
                    </div>
                </div>

                {/* 使い方 */}
                <div className="bg-yellow-50 border-t p-4">
                    <h3 className="text-sm font-semibold text-yellow-800 mb-2">
                        📱 使い方
                    </h3>
                    <ol className="space-y-1 text-xs text-yellow-700">
                        <li>1. スマホのカメラでQRコードを読み取る</li>
                        <li>2. ブラウザで{child.name}専用ページが開く</li>
                        <li>3. {qrCode.label}を楽しく見ることができる</li>
                    </ol>
                </div>
            </div>
        </div>
    );
}