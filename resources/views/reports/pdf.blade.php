<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $child->name }}のスタンプレポート</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #4a90e2;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 10px;
        }
        
        .subtitle {
            font-size: 18px;
            color: #666;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #4a90e2;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #4a90e2;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4a90e2;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .stamps-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .stamps-table th,
        .stamps-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        
        .stamps-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #4a90e2;
        }
        
        .stamps-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .pokemon-section {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        
        .pokemon-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        
        .pokemon-item {
            background: white;
            padding: 10px;
            border-radius: 4px;
            text-align: center;
            border: 1px solid #ffc107;
        }
        
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        
        .period-info {
            background: #e3f2fd;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">{{ $child->name }}のスタンプレポート</div>
        <div class="subtitle">
            @if($type === 'monthly')
                {{ $report['period']['month'] ?? '月間レポート' }}
            @else
                詳細レポート
            @endif
        </div>
    </div>

    @if(isset($report['period']))
    <div class="period-info">
        <strong>対象期間:</strong> 
        @if($type === 'monthly')
            {{ $report['period']['month'] ?? '' }}
        @else
            {{ $report['period']['start_date'] ?? '' }} 〜 {{ $report['period']['end_date'] ?? '' }}
        @endif
    </div>
    @endif

    <div class="section">
        <div class="section-title">📊 サマリー</div>
        <div class="summary-stats">
            <div class="stat-card">
                <div class="stat-number">{{ $report['summary']['total_stamps'] ?? 0 }}</div>
                <div class="stat-label">総スタンプ数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $report['summary']['active_days'] ?? 0 }}</div>
                <div class="stat-label">活動日数</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">{{ $report['summary']['unique_stamp_types'] ?? 0 }}</div>
                <div class="stat-label">スタンプ種類数</div>
            </div>
            @if(isset($report['summary']['unique_pokemons']))
            <div class="stat-card">
                <div class="stat-number">{{ $report['summary']['unique_pokemons'] }}</div>
                <div class="stat-label">出会ったポケモン数</div>
            </div>
            @endif
        </div>
    </div>

    @if(isset($report['stamps_by_type']) && count($report['stamps_by_type']) > 0)
    <div class="section">
        <div class="section-title">🏆 スタンプ種類別統計</div>
        <table class="stamps-table">
            <thead>
                <tr>
                    <th>スタンプ種類</th>
                    <th>獲得数</th>
                    <th>割合</th>
                </tr>
            </thead>
            <tbody>
                @foreach($report['stamps_by_type'] as $stampType)
                <tr>
                    <td>{{ $stampType['name'] ?? 'Unknown' }}</td>
                    <td>{{ $stampType['count'] ?? 0 }}</td>
                    <td>{{ number_format(($stampType['percentage'] ?? 0), 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if(isset($report['pokemon_collection']) && count($report['pokemon_collection']) > 0)
    <div class="section">
        <div class="section-title">✨ 出会ったポケモン</div>
        <div class="pokemon-section">
            <div class="pokemon-list">
                @foreach($report['pokemon_collection'] as $pokemon)
                <div class="pokemon-item">
                    <div><strong>{{ $pokemon['name'] ?? 'Unknown' }}</strong></div>
                    <div style="font-size: 12px; color: #666;">
                        {{ $pokemon['count'] ?? 0 }}回出会った
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if(isset($report['achievements']) && count($report['achievements']) > 0)
    <div class="section">
        <div class="section-title">🎉 達成した目標</div>
        @foreach($report['achievements'] as $achievement)
        <div style="background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 4px; border-left: 4px solid #28a745;">
            <strong>{{ $achievement['title'] ?? 'Unknown Achievement' }}</strong>
            @if(isset($achievement['achieved_at']))
            <div style="font-size: 12px; color: #666;">
                達成日: {{ $achievement['achieved_at'] }}
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <div>レポート作成日: {{ now()->format('Y年m月d日') }}</div>
        <div>子どもスタンプ帳システム</div>
    </div>
</body>
</html>