<?php

namespace App\Http\Controllers;

use App\Models\AssetMovement;
use App\Models\SiteSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * The printable copy of one verified equipment trip, as a PDF shown in the browser (inline, so it can be printed
 * from the viewer). Needs the "Print" permission, and the trip must be acknowledged.
 */
class AssetMovementPrintController extends Controller
{
    public function __invoke(AssetMovement $assetMovement): Response
    {
        abort_unless(Auth::user()?->is_active, 403);
        Gate::authorize('print', $assetMovement);
        abort_unless($assetMovement->isPrintable(), 403, 'Only acknowledged trips can be printed.');

        $assetMovement->load([
            'asset.assetType',
            'asset.type',
            'asset.brand',
            'asset.equipmentModel',
            'asset.location',
            'yardOut',
            'yardIn',
            'manualOutBy',
            'manualInBy',
            'acknowledgedBy',
            'outLog.yard',
            'inLog.yard',
        ]);

        $pdf = Pdf::loadView('reports.asset-movement-trip', [
            'trip' => $assetMovement,
            'siteName' => rescue(fn (): string => (string) SiteSetting::instance()->site_name, config('app.name'), report: false),
            'printedBy' => Auth::user()->full_name ?: Auth::user()->user_email,
            'printedAt' => now(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', false);

        $name = Str::slug('trip-'.($assetMovement->asset?->eqm_prc_code ?? $assetMovement->getKey()).'-'.$assetMovement->getKey());

        return $pdf->stream($name.'.pdf');
    }
}
