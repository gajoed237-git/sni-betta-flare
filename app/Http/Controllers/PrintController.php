<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use App\Models\BettaClass;
use App\Models\ScoreSnapshot;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PrintController extends Controller
{
    public function printLabels(Request $request)
    {
        $fishIds = $request->input('ids', []);
        $user = auth()->user();

        $query = Fish::with(['bettaClass', 'event']);

        // Scope by event admin
        if ($user && $user->isEventAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');
            $query->whereIn('event_id', $eventIds);
        }

        if (!empty($fishIds)) {
            $query->whereIn('id', $fishIds);
        }

        $fishes = $query->get();

        $data = [];
        foreach ($fishes as $fish) {
            $qrCode = base64_encode(QrCode::format('svg')->size(200)->errorCorrection('H')->margin(1)->generate("SBF-JUDGE:{$fish->registration_no}"));
            $data[] = [
                'registration_no' => $fish->registration_no,
                'class_code' => $fish->bettaClass->code ?? '',
                'class_name' => $fish->bettaClass->name ?? '',
                'event_name' => $fish->event->name ?? 'SNI BETTA FLARE',
                'qr_code' => $qrCode,
            ];
        }

        $pdf = Pdf::loadView('print.labels', ['fishes' => $data])
            ->setPaper('a4', 'portrait');

        return $pdf->stream('labels.pdf');
    }

    public function printClassResults($classId)
    {
        $class = BettaClass::with('division')->findOrFail($classId);
        $results = Fish::where('class_id', $classId)
            ->where(function ($q) {
                $q->whereNotNull('final_rank')
                    ->orWhere('is_nominated', true);
            })
            ->with(['snapshot'])
            ->get()
            ->sortBy(function ($fish) {
                return $fish->final_rank ?? 999;
            });

        $pdf = Pdf::loadView('print.class-results', [
            'class' => $class,
            'results' => $results,
            'date' => now()->format('d F Y')
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("results_{$class->code}.pdf");
    }

    public function printChampionStandings()
    {
        $rankedFishes = Fish::where(function ($q) {
            $q->whereNotNull('final_rank')
                ->orWhereNotNull('winner_type');
        })->get();

        $tempTeams = [];
        foreach ($rankedFishes as $fish) {
            $points = 0;
            if ($fish->final_rank == 1) $points += 15;
            elseif ($fish->final_rank == 2) $points += 7;
            elseif ($fish->final_rank == 3) $points += 3;
            if ($fish->winner_type === 'gc') $points += 30;

            if ($fish->team_name) {
                if (!isset($tempTeams[$fish->team_name])) {
                    $tempTeams[$fish->team_name] = ['name' => $fish->team_name, 'points' => 0, 'gold' => 0, 'silver' => 0, 'bronze' => 0, 'gc' => 0];
                }
                $tempTeams[$fish->team_name]['points'] += $points;
                if ($fish->final_rank == 1) $tempTeams[$fish->team_name]['gold']++;
                if ($fish->final_rank == 2) $tempTeams[$fish->team_name]['silver']++;
                if ($fish->final_rank == 3) $tempTeams[$fish->team_name]['bronze']++;
                if ($fish->winner_type === 'gc') $tempTeams[$fish->team_name]['gc']++;
            }
        }
        usort($tempTeams, fn($a, $b) => [$b['points'], $b['gc'], $b['gold']] <=> [$a['points'], $a['gc'], $a['gold']]);

        $pdf = Pdf::loadView('print.champion-standings', [
            'teams' => $tempTeams,
            'date' => now()->format('d F Y')
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("champion_standings.pdf");
    }

    public function downloadImportTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_import_kelas.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            // Add BOM for Excel UTF-8 Compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Using semicolon (;) as it is common for Excel in many regions (like ID)
            // But our importer handles both , and ;
            fputcsv($file, ['division_code', 'division_name', 'class_code', 'class_name'], ';');
            fputcsv($file, ['A', 'REGULAR SHOW', 'A1', 'Red Solid'], ';');
            fputcsv($file, ['A', 'REGULAR SHOW', 'A2', 'Blue Solid'], ';');
            fputcsv($file, ['B', 'JUNIOR SHOW', 'B1', 'Multi Color'], ';');
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
