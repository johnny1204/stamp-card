<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Services\ChildService;
use App\Models\Child;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * レポートコントローラー
 *
 * 詳細レポートの表示とデータ提供を担当
 */
class ReportController extends Controller
{
    /**
     * @param ReportService $reportService
     * @param ChildService $childService
     */
    public function __construct(
        private readonly ReportService $reportService,
        private readonly ChildService $childService
    ) {}

    /**
     * 子どもの詳細レポートダッシュボードを表示
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return Response
     */
    public function dashboard(Request $request, int $childId): Response
    {
        $child = Child::findOrFail($childId);
        
        // フィルター条件を取得
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date'))
            : now()->subMonths(3)->startOfMonth();
            
        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))
            : now()->endOfMonth();
            
        $groupBy = $request->get('group_by', 'month');
        
        $options = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'group_by' => $groupBy,
            'include_pokemon' => $request->boolean('include_pokemon', true),
            'include_trends' => $request->boolean('include_trends', true),
            'include_achievements' => $request->boolean('include_achievements', true),
        ];
        
        $report = $this->reportService->generateDetailedReport($childId, $options);
        $children = $this->childService->getAllChildren();
        
        return Inertia::render('Reports/Dashboard', [
            'child' => $child,
            'children' => $children,
            'report' => $report,
            'filters' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'group_by' => $groupBy,
                'include_pokemon' => $options['include_pokemon'],
                'include_trends' => $options['include_trends'],
                'include_achievements' => $options['include_achievements'],
            ],
        ]);
    }

    /**
     * 月間レポートを表示
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return Response
     */
    public function monthly(Request $request, int $childId): Response
    {
        $child = Child::findOrFail($childId);
        
        $month = $request->get('month') 
            ? Carbon::parse($request->get('month') . '-01')
            : now();
            
        $report = $this->reportService->generateMonthlyReport($childId, $month);
        $children = $this->childService->getAllChildren();
        
        return Inertia::render('Reports/Monthly', [
            'child' => $child,
            'children' => $children,
            'report' => $report,
            'month' => $month->format('Y-m'),
            'monthName' => $month->format('Y年m月'),
        ]);
    }

    /**
     * レポートデータをJSON形式で取得（AJAX用）
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return JsonResponse
     */
    public function getData(Request $request, int $childId): JsonResponse
    {
        try {
            $reportType = $request->get('type', 'detailed');
            
            if ($reportType === 'monthly') {
                $month = $request->get('month') 
                    ? Carbon::parse($request->get('month') . '-01')
                    : now()->subMonth();
                    
                $report = $this->reportService->generateMonthlyReport($childId, $month);
            } else {
                $startDate = $request->get('start_date') 
                    ? Carbon::parse($request->get('start_date'))
                    : now()->subMonths(3)->startOfMonth();
                    
                $endDate = $request->get('end_date')
                    ? Carbon::parse($request->get('end_date'))
                    : now()->endOfMonth();
                    
                $options = [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'group_by' => $request->get('group_by', 'month'),
                    'include_pokemon' => $request->boolean('include_pokemon', true),
                    'include_trends' => $request->boolean('include_trends', true),
                    'include_achievements' => $request->boolean('include_achievements', true),
                ];
                
                $report = $this->reportService->generateDetailedReport($childId, $options);
            }
            
            return response()->json([
                'success' => true,
                'report' => $report,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * レポートをPDF形式でエクスポート
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request, int $childId)
    {
        $child = Child::findOrFail($childId);
        
        $reportType = $request->get('type', 'monthly');
        
        if ($reportType === 'monthly') {
            $month = $request->get('month') 
                ? Carbon::parse($request->get('month') . '-01')
                : now()->subMonth();
                
            $report = $this->reportService->generateMonthlyReport($childId, $month);
            $filename = "{$child->name}の月間レポート_{$month->format('Y年m月')}.pdf";
        } else {
            $startDate = $request->get('start_date') 
                ? Carbon::parse($request->get('start_date'))
                : now()->subMonths(3)->startOfMonth();
                
            $endDate = $request->get('end_date')
                ? Carbon::parse($request->get('end_date'))
                : now()->endOfMonth();
                
            $options = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => 'month',
            ];
            
            $report = $this->reportService->generateDetailedReport($childId, $options);
            $filename = "{$child->name}の詳細レポート_{$startDate->format('Y-m-d')}_{$endDate->format('Y-m-d')}.pdf";
        }
        
        // PDF生成（将来的にはLaravelのDomPDFなどを使用）
        $html = view('reports.pdf', [
            'child' => $child,
            'report' => $report,
            'type' => $reportType,
        ])->render();
        
        return response($html)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * 比較レポートを表示
     *
     * @param Request $request
     * @param array $childIds 比較する子どもIDの配列
     * @return Response
     */
    public function compare(Request $request, array $childIds): Response
    {
        $children = Child::whereIn('id', $childIds)->get();
        
        if ($children->count() < 2) {
            return redirect()->back()
                ->with('error', '比較には2人以上の子どもを選択してください。');
        }
        
        $startDate = $request->get('start_date') 
            ? Carbon::parse($request->get('start_date'))
            : now()->subMonths(1)->startOfMonth();
            
        $endDate = $request->get('end_date')
            ? Carbon::parse($request->get('end_date'))
            : now()->endOfMonth();
        
        $options = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'group_by' => 'month',
        ];
        
        $reports = [];
        foreach ($children as $child) {
            $reports[$child->id] = $this->reportService->generateDetailedReport($child->id, $options);
        }
        
        return Inertia::render('Reports/Compare', [
            'children' => $children,
            'reports' => $reports,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * 活動傾向チャートデータを取得
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return JsonResponse
     */
    public function getActivityTrend(Request $request, int $childId): JsonResponse
    {
        try {
            $startDate = Carbon::parse($request->get('start_date', now()->subMonths(3)->format('Y-m-d')));
            $endDate = Carbon::parse($request->get('end_date', now()->format('Y-m-d')));
            $groupBy = $request->get('group_by', 'day');
            
            $options = [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'group_by' => $groupBy,
            ];
            
            $report = $this->reportService->generateDetailedReport($childId, $options);
            
            return response()->json([
                'success' => true,
                'data' => $report['activity_trend'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * スタンプタイプ別統計を取得
     *
     * @param Request $request
     * @param int $childId 子どもID
     * @return JsonResponse
     */
    public function getStampsByType(Request $request, int $childId): JsonResponse
    {
        try {
            $startDate = Carbon::parse($request->get('start_date', now()->subMonths(1)->format('Y-m-d')));
            $endDate = Carbon::parse($request->get('end_date', now()->format('Y-m-d')));
            
            $options = [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ];
            
            $report = $this->reportService->generateDetailedReport($childId, $options);
            
            return response()->json([
                'success' => true,
                'data' => $report['stamps_by_type'],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
