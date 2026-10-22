<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusSurat;
use App\Http\Controllers\Controller;
use App\Models\ScanLog;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $semua = $user->dapatKelolaSemuaSurat();
        $filter = fn ($query) => $query->when(! $semua, fn ($q) => $q->where('created_by', $user->id));
        $mulai = now()->subDays(13)->startOfDay();

        $pemindaian = ScanLog::query()->where('is_bot', false)->where('scanned_at', '>=', $mulai)
            ->when(! $semua, fn ($q) => $q->whereHas('surat', fn ($surat) => $surat->where('created_by', $user->id)))
            ->select(DB::raw('DATE(scanned_at) as hari'), DB::raw('COUNT(*) as total'))
            ->groupBy('hari')->pluck('total', 'hari');

        $grafik = collect(range(0, 13))->map(function (int $i) use ($mulai, $pemindaian) {
            $tanggal = $mulai->copy()->addDays($i);
            return ['tanggal' => $tanggal, 'total' => (int) ($pemindaian[$tanggal->toDateString()] ?? 0)];
        });

        $jumlah = fn (StatusSurat $status) => $filter(Surat::query())->where('status', $status->value)->count();

        return view('admin.dashboard', [
            'total' => $filter(Surat::query())->count(),
            'aktif' => $jumlah(StatusSurat::Publish),
            'draf' => $jumlah(StatusSurat::Draf),
            'dicabut' => $jumlah(StatusSurat::Ditarik),
            'totalScan' => (int) $filter(Surat::query())->sum('scan_count'),
            'grafik' => $grafik,
            'grafikMaks' => max(1, $grafik->max('total')),
            'terbaru' => $filter(Surat::query())->with('jenisSurat')->latest()->limit(5)->get(),
            'terpopuler' => $filter(Surat::query())->where('scan_count', '>', 0)->orderByDesc('scan_count')->limit(5)->get(),
        ]);
    }
}
