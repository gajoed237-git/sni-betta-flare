<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use App\Models\BettaClass;
use App\Models\ScoreSnapshot;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Auth;

class PrintController extends Controller
{
    public function printLabels(Request $request)
    {
        $fishIds = $request->input('ids', []);
        /** @var \App\Models\User $user */
        $user = Auth::user();

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

    public function printChampionStandings(Request $request)
    {
        $eventId = $request->query('event_id');
        $event = \App\Models\Event::findOrFail($eventId);

        $rankedFishes = Fish::where('event_id', $eventId)
            ->where(function ($q) {
                $q->whereNotNull('final_rank')
                    ->orWhereNotNull('winner_type');
            })
            ->with(['event', 'participant'])
            ->get();

        $tempTeams = [];
        $tempSF = [];

        foreach ($rankedFishes as $fish) {
            $points = 0;
            $category = $fish->participant->category ?? 'other';

            $rankPoints = 0;
            if ($fish->final_rank == 1) $rankPoints = $event->point_rank1;
            elseif ($fish->final_rank == 2) $rankPoints = $event->point_rank2;
            elseif ($fish->final_rank == 3) $rankPoints = $event->point_rank3;

            $winnerTypes = (array) $fish->winner_type;
            $titlePointsList = [];

            foreach ($winnerTypes as $type) {
                $tp = 0;
                if ($type === 'gc') $tp = $event->point_gc;
                elseif ($type === 'bob') $tp = $event->point_bob;
                elseif ($type === 'bod') $tp = $event->point_bod;
                elseif ($type === 'boo') $tp = $event->point_boo;
                elseif ($type === 'bov') $tp = $event->point_bov;
                elseif ($type === 'bos') $tp = $event->point_bos;

                if ($tp > 0) {
                    $titlePointsList[] = $tp;
                }
            }

            $mode = $event->point_accumulation_mode ?? 'highest';

            if ($mode === 'accumulation') {
                $points = $rankPoints + array_sum($titlePointsList);
            } else {
                $allPoints = array_merge([$rankPoints], $titlePointsList);
                $points = count($allPoints) > 0 ? max($allPoints) : 0;
            }

            if ($category === 'team' && $fish->team_name) {
                if (!isset($tempTeams[$fish->team_name])) {
                    $tempTeams[$fish->team_name] = ['name' => $fish->team_name, 'points' => 0, 'gold' => 0, 'silver' => 0, 'bronze' => 0, 'gc' => 0];
                }
                $tempTeams[$fish->team_name]['points'] += $points;
                if ($fish->final_rank == 1) $tempTeams[$fish->team_name]['gold']++;
                if ($fish->final_rank == 2) $tempTeams[$fish->team_name]['silver']++;
                if ($fish->final_rank == 3) $tempTeams[$fish->team_name]['bronze']++;

                $hasMajorTitle = count(array_intersect(['gc', 'bob', 'bos', 'bod', 'boo', 'bov'], $winnerTypes)) > 0;
                if ($hasMajorTitle) $tempTeams[$fish->team_name]['gc']++;
            }

            if ($category === 'single_fighter' && $fish->participant_name) {
                if (!isset($tempSF[$fish->participant_name])) {
                    $tempSF[$fish->participant_name] = ['name' => $fish->participant_name, 'points' => 0, 'gold' => 0, 'silver' => 0, 'bronze' => 0, 'gc' => 0];
                }
                $tempSF[$fish->participant_name]['points'] += $points;
                if ($fish->final_rank == 1) $tempSF[$fish->participant_name]['gold']++;
                if ($fish->final_rank == 2) $tempSF[$fish->participant_name]['silver']++;
                if ($fish->final_rank == 3) $tempSF[$fish->participant_name]['bronze']++;

                $hasMajorTitle = count(array_intersect(['gc', 'bob', 'bos', 'bod', 'boo', 'bov'], $winnerTypes)) > 0;
                if ($hasMajorTitle) $tempSF[$fish->participant_name]['gc']++;
            }
        }

        $sortFn = function ($a, $b) {
            if ($b['points'] !== $a['points']) return $b['points'] <=> $a['points'];
            if ($b['gc'] !== $a['gc']) return $b['gc'] <=> $a['gc'];
            if ($b['gold'] !== $a['gold']) return $b['gold'] <=> $a['gold'];
            if ($b['silver'] !== $a['silver']) return $b['silver'] <=> $a['silver'];
            return $b['bronze'] <=> $a['bronze'];
        };

        usort($tempTeams, $sortFn);
        usort($tempSF, $sortFn);

        $pdf = Pdf::loadView('print.champion-standings', [
            'teams' => array_slice($tempTeams, 0, 10),
            'sfs' => array_slice($tempSF, 0, 10),
            'event' => $event,
            'date' => now()->format('d F Y')
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("champion_standings_{$event->name}.pdf");
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
