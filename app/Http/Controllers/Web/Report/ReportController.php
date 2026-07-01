<?php

namespace App\Http\Controllers\Web\Report;

use App\Domain\Master\Models\Category;
use App\Domain\Master\Models\Location;
use App\Domain\Master\Models\Supplier;
use App\Exports\Reports\GoodsIssueReportExport;
use App\Exports\Reports\GoodsReceiptReportExport;
use App\Exports\Reports\StockReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ReportFilterRequest;
use App\Services\Reports\InventoryReportQuery;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(private readonly InventoryReportQuery $reportQuery) {}

    public function index(): View
    {
        return view('reports.index');
    }

    public function stock(ReportFilterRequest $request): View
    {
        $filters = $request->filters();
        $rows = $this->reportQuery->stock($filters)->paginate(25)->withQueryString();

        return view('reports.stock', ['title' => 'Laporan Stock', 'rows' => $rows, 'filters' => $filters, 'exportRoute' => route('reports.stock.export', $filters), ...$this->filterOptions()]);
    }

    public function goodsIn(ReportFilterRequest $request): View
    {
        $filters = $this->defaultPeriod($request->filters());
        $rows = $this->reportQuery->goodsIn($filters)->paginate(25)->withQueryString();

        return view('reports.goods-in', ['title' => 'Laporan Barang Masuk', 'rows' => $rows, 'filters' => $filters, 'exportRoute' => route('reports.goods-in.export', $filters), ...$this->filterOptions()]);
    }

    public function goodsOut(ReportFilterRequest $request): View
    {
        $filters = $this->defaultPeriod($request->filters());
        $rows = $this->reportQuery->goodsOut($filters)->paginate(25)->withQueryString();

        return view('reports.goods-out', ['title' => 'Laporan Barang Keluar', 'rows' => $rows, 'filters' => $filters, 'exportRoute' => route('reports.goods-out.export', $filters), ...$this->filterOptions()]);
    }

    public function exportStock(ReportFilterRequest $request): BinaryFileResponse
    {
        Gate::authorize('reports.export');

        return Excel::download(new StockReportExport($request->filters()), 'laporan-stock-'.now()->format('Ymd-His').'.xlsx');
    }

    public function exportGoodsIn(ReportFilterRequest $request): BinaryFileResponse
    {
        Gate::authorize('reports.export');
        $filters = $this->defaultPeriod($request->filters());

        return Excel::download(new GoodsReceiptReportExport($filters), 'laporan-barang-masuk-'.now()->format('Ymd-His').'.xlsx');
    }

    public function exportGoodsOut(ReportFilterRequest $request): BinaryFileResponse
    {
        Gate::authorize('reports.export');
        $filters = $this->defaultPeriod($request->filters());

        return Excel::download(new GoodsIssueReportExport($filters), 'laporan-barang-keluar-'.now()->format('Ymd-His').'.xlsx');
    }

    /** @param array<string, mixed> $filters @return array<string, mixed> */
    private function defaultPeriod(array $filters): array
    {
        $filters['date_from'] ??= Carbon::now()->startOfMonth()->toDateString();
        $filters['date_to'] ??= Carbon::now()->endOfMonth()->toDateString();

        return $filters;
    }

    /** @return array<string, mixed> */
    private function filterOptions(): array
    {
        return [
            'locations' => Location::query()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::query()->orderBy('name')->get(['id', 'name']),
        ];
    }
}
