<?php

namespace App\Http\Controllers;

use App\Models\Fish;
use App\Models\BettaClass;
use App\Models\ScoreSnapshot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            $qrCode = base64_encode(QrCode::format('svg')->size(150)->errorCorrection('H')->margin(1)->generate("SIKNUSA-JUDGE:{$fish->id}"));
            $data[] = [
                'registration_no' => $fish->registration_no,
                'class_code' => optional($fish->bettaClass)->code ?? ($fish->class_id ? "CL-{$fish->class_id}" : 'N/A'),
                'class_name' => $fish->bettaClass->name ?? '',
                'event_name' => optional($fish->event)->name ?? '',
                'judging_standard' => strtoupper(optional($fish->event)->judging_standard ?? 'SNI'),
                'qr_code' => $qrCode,
            ];
        }

        // 60mm x 50mm @ 72dpi
        // 1mm = 2.83465 points
        // 60mm = 170.1 pts
        // 50mm = 141.7 pts
        $customPaper = [0, 0, 170.1, 141.7];

        $pdf = Pdf::loadView('print.labels', ['fishes' => $data])
            ->setPaper($customPaper, 'portrait');

        return $pdf->stream('labels.pdf');
    }

    public function printClassResults($classId)
    {
        $class = BettaClass::with('division')->findOrFail($classId);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->canManageEvent($class->event_id)) {
            abort(403, 'Unauthorized');
        }
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

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user || !$user->canManageEvent($eventId)) {
            abort(403, 'Unauthorized');
        }

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
            if ($fish->final_rank == 1)
                $rankPoints = $event->point_rank1;
            elseif ($fish->final_rank == 2)
                $rankPoints = $event->point_rank2;
            elseif ($fish->final_rank == 3)
                $rankPoints = $event->point_rank3;

            $winnerTypes = (array) $fish->winner_type;
            $titlePointsList = [];

            foreach ($winnerTypes as $type) {
                $type = strtolower($type);
                $tp = 0;
                if ($type === 'gc')
                    $tp = $event->point_gc;
                elseif ($type === 'bob')
                    $tp = $event->point_bob;
                elseif ($type === 'bof')
                    $tp = $event->point_bof;
                elseif ($type === 'bod')
                    $tp = $event->point_bod;
                elseif ($type === 'boo')
                    $tp = $event->point_boo;
                elseif ($type === 'bov')
                    $tp = $event->point_bov;
                elseif ($type === 'bos')
                    $tp = $event->point_bos;
                else {
                    // Check Custom Awards
                    if ($event->custom_awards) {
                        foreach ($event->custom_awards as $award) {
                            if (isset($award['key']) && strtolower($award['key']) === $type) {
                                $tp = (int) ($award['points'] ?? 0);
                                break;
                            }
                        }
                    }
                }

                if (($tp ?? 0) > 0) {
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
                    $tempTeams[$fish->team_name] = [
                        'name' => $fish->team_name,
                        'points' => 0,
                        'gold' => 0,
                        'silver' => 0,
                        'bronze' => 0,
                        'gc' => 0,
                        'bob' => 0,
                        'bof' => 0,
                        'bos' => 0,
                        'bod' => 0,
                        'boo' => 0,
                        'bov' => 0,
                        'custom_titles' => []
                    ];
                }
                $tempTeams[$fish->team_name]['points'] += $points;
                if ($fish->final_rank == 1)
                    $tempTeams[$fish->team_name]['gold']++;
                if ($fish->final_rank == 2)
                    $tempTeams[$fish->team_name]['silver']++;
                if ($fish->final_rank == 3)
                    $tempTeams[$fish->team_name]['bronze']++;

                foreach ($winnerTypes as $type) {
                    $type = strtolower($type);
                    if (in_array($type, ['gc', 'bob', 'bof', 'bos', 'bod', 'boo', 'bov'])) {
                        $tempTeams[$fish->team_name][$type]++;
                    } else {
                        // Custom Award tracking
                        if (!isset($tempTeams[$fish->team_name]['custom_titles'][$type])) {
                            $tempTeams[$fish->team_name]['custom_titles'][$type] = 0;
                        }
                        $tempTeams[$fish->team_name]['custom_titles'][$type]++;
                    }
                }

                // Sorting factor: total major titles
                $standardTitles = ['gc', 'bob', 'bof', 'bos', 'bod', 'boo', 'bov'];
                $customKeys = $event->custom_awards ? array_column($event->custom_awards, 'key') : [];
                $allMajorKeys = array_map('strtolower', array_merge($standardTitles, $customKeys));
                $tempTeams[$fish->team_name]['total_major'] = count(array_intersect($allMajorKeys, array_map('strtolower', $winnerTypes)));
            }

            if ($category === 'single_fighter' && $fish->participant_name) {
                if (!isset($tempSF[$fish->participant_name])) {
                    $tempSF[$fish->participant_name] = [
                        'name' => $fish->participant_name,
                        'points' => 0,
                        'gold' => 0,
                        'silver' => 0,
                        'bronze' => 0,
                        'gc' => 0,
                        'bob' => 0,
                        'bof' => 0,
                        'bos' => 0,
                        'bod' => 0,
                        'boo' => 0,
                        'bov' => 0,
                        'custom_titles' => []
                    ];
                }
                $tempSF[$fish->participant_name]['points'] += $points;
                if ($fish->final_rank == 1)
                    $tempSF[$fish->participant_name]['gold']++;
                if ($fish->final_rank == 2)
                    $tempSF[$fish->participant_name]['silver']++;
                if ($fish->final_rank == 3)
                    $tempSF[$fish->participant_name]['bronze']++;

                foreach ($winnerTypes as $type) {
                    $type = strtolower($type);
                    if (in_array($type, ['gc', 'bob', 'bof', 'bos', 'bod', 'boo', 'bov'])) {
                        $tempSF[$fish->participant_name][$type]++;
                    } else {
                        // Custom Award tracking
                        if (!isset($tempSF[$fish->participant_name]['custom_titles'][$type])) {
                            $tempSF[$fish->participant_name]['custom_titles'][$type] = 0;
                        }
                        $tempSF[$fish->participant_name]['custom_titles'][$type]++;
                    }
                }

                // Sorting factor: total major titles
                $standardTitles = ['gc', 'bob', 'bof', 'bos', 'bod', 'boo', 'bov'];
                $customKeys = $event->custom_awards ? array_column($event->custom_awards, 'key') : [];
                $allMajorKeys = array_map('strtolower', array_merge($standardTitles, $customKeys));
                $tempSF[$fish->participant_name]['total_major'] = count(array_intersect($allMajorKeys, array_map('strtolower', $winnerTypes)));
            }
        }

        $sortFn = function ($a, $b) {
            if ($b['points'] !== $a['points'])
                return $b['points'] <=> $a['points'];
            if ($b['total_major'] !== $a['total_major'])
                return $b['total_major'] <=> $a['total_major'];
            if ($b['gold'] !== $a['gold'])
                return $b['gold'] <=> $a['gold'];
            if ($b['silver'] !== $a['silver'])
                return $b['silver'] <=> $a['silver'];
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

    public function printRegistrationForm(Request $request, $eventId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Check authorization - only admin and admin_event
        if (!$user || (!$user->isAdmin() && !$user->isEventAdmin())) {
            abort(403, 'Unauthorized');
        }

        // Scope by event admin if user is event admin
        if ($user->isEventAdmin()) {
            $managedEventIds = $user->managed_events()->pluck('events.id');
            if (!$managedEventIds->contains($eventId)) {
                abort(403, 'You are not authorized to print this event');
            }
        }

        // Get event
        $event = \App\Models\Event::findOrFail($eventId);

        // Get participant name from query parameter or input
        $participantName = $request->query('participant_name') ?: $request->input('participant_name');
        if (!$participantName) {
            abort(400, 'Participant name is required');
        }

        // Get fishes for this participant and event
        $fishes = Fish::where('event_id', $eventId)
            ->where(function ($q) use ($participantName) {
                $q->where('participant_name', 'like', "%{$participantName}%")
                    ->orWhere('team_name', 'like', "%{$participantName}%");
            })
            ->with(['bettaClass'])
            ->orderBy('registration_no')
            ->get();

        // If no fishes found, create empty result
        $registrationData = [];
        foreach ($fishes as $fish) {
            $registrationData[] = [
                'registration_no' => $fish->registration_no,
                'class_code' => optional($fish->bettaClass)->code ?? 'N/A',
                'class_name' => $fish->bettaClass->name ?? '',
            ];
        }

        // Pad with empty rows to reach 25 total
        $totalRows = 25;
        $emptyRowsNeeded = $totalRows - count($registrationData);
        for ($i = 0; $i < $emptyRowsNeeded; $i++) {
            $registrationData[] = [
                'registration_no' => '',
                'class_code' => '',
                'class_name' => '',
            ];
        }

        // F4 size: 215mm x 330mm (8.5" x 13")
        // At 72dpi: 215mm = 612 points, 330mm = 936 points
        $paperF4 = [0, 0, 609.45, 935.43];

        $pdf = Pdf::loadView('print.registration-form', [
            'event' => $event,
            'participantName' => $participantName,
            'fishes' => $registrationData,
            'printDate' => now()->format('d/m/Y H:i'),
            'printedBy' => $user->name ?? 'Admin'
        ])->setPaper($paperF4, 'portrait');

        $fileName = 'Registrasi_' . Str::slug($participantName) . '.pdf';
        return $pdf->stream($fileName);
    }

    public function printFishOut($participantId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $participant = \App\Models\Participant::with(['event', 'user', 'fishes.bettaClass'])->findOrFail($participantId);

        // Authorization check
        if (!$user->isAdmin()) {
            if ($user->isEventAdmin()) {
                if (!$user->managed_events()->where('events.id', $participant->event_id)->exists()) {
                    abort(403);
                }
            } else {
                abort(403);
            }
        }

        $paperF4 = [0, 0, 609.45, 935.43];

        $pdf = Pdf::loadView('print.fish-out', [
            'participant' => $participant,
            'event' => $participant->event,
            'fishes' => $participant->fishes,
            'printDate' => now()->format('d/m/Y H:i'),
            'printedBy' => $user->name ?? 'Admin'
        ])->setPaper($paperF4, 'portrait');

        return $pdf->stream("FishOut_{$participant->name}.pdf");
    }

    public function printCertificate($fishId, Request $request)
    {
        $fish = \App\Models\Fish::with(['bettaClass.event', 'participant'])->findOrFail($fishId);
        $event = $fish->bettaClass->event;

        $type = 'rank';
        $label = "JUARA " . $fish->final_rank;

        // Priority for special titles
        $winnerTypes = (array) $fish->winner_type;
        if (in_array('gc', $winnerTypes)) {
            $type = 'gc';
            $label = $event->label_gc;
        } elseif (in_array('bob', $winnerTypes)) {
            $type = 'bob';
            $label = $event->label_bob;
        } elseif (in_array('bof', $winnerTypes)) {
            $type = 'bof';
            $label = $event->label_bof;
        } elseif (in_array('bos', $winnerTypes)) {
            $type = 'bos';
            $label = $event->label_bos;
        } elseif (in_array('bod', $winnerTypes)) {
            $type = 'bod';
            $label = $event->label_bod;
        } elseif (in_array('boo', $winnerTypes)) {
            $type = 'boo';
            $label = $event->label_boo;
        } elseif (in_array('bov', $winnerTypes)) {
            $type = 'bov';
            $label = $event->label_bov;
        }

        if (!$fish->final_rank && empty($winnerTypes)) {
            abort(404, "Ikan ini tidak memiliki gelar juara.");
        }

        $pdf = Pdf::loadView('print.certificate', [
            'fish' => $fish,
            'event' => $event,
            'type' => $type,
            'label' => $label
        ])->setPaper('a4', 'landscape');

        return $pdf->download("E-Certificate_{$fish->registration_no}.pdf");
    }

    public function printMovedDqFishes(Request $request)
    {
        $eventId = $request->query('event_id');
        $fishIds = $request->query('fish_ids');
        $isBlank = $request->query('blank');

        if ($isBlank) {
            $fishes = collect();
            $event = (object) ['name' => 'Blangko Kosong'];
        } else {
            $query = Fish::whereIn('status', ['disqualified', 'moved'])
                ->with(['bettaClass', 'originalClass', 'event'])
                ->orderBy('status')
                ->orderBy('registration_no');

            if ($fishIds) {
                $ids = explode(',', $fishIds);
                $query->whereIn('id', $ids);
            } elseif ($eventId) {
                $query->where('event_id', $eventId);
            }

            $fishes = $query->get();

            if ($eventId) {
                $event = \App\Models\Event::find($eventId) ?? (object) ['name' => 'Event Not Found'];
            } else {
                $event = (object) [
                    'name' => $fishIds ? 'Hasil Cetak Terpilih' : 'Laporan Gabungan / Seluruh Event'
                ];
            }
        }

        $pdf = Pdf::loadView('print.fish-moved-dq', [
            'event' => $event,
            'fishes' => $fishes
        ])->setPaper('a4', 'portrait');

        return $pdf->stream("Laporan_Pindah_DQ.pdf");
    }

    public function printEmptyRegistration($participantId)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $participant = \App\Models\Participant::with(['event', 'fishes'])->withCount('fishes')->findOrFail($participantId);

        // Authorization check
        if (!$user->isAdmin()) {
            if ($user->isEventAdmin()) {
                if (!$user->managed_events()->where('events.id', $participant->event_id)->exists()) {
                    abort(403);
                }
            } else {
                abort(403);
            }
        }

        $paperF4 = [0, 0, 609.45, 935.43];

        $pdf = Pdf::loadView('print.empty-registration', [
            'participant' => $participant,
            'event' => $participant->event,
            'printedBy' => $user->name ?? 'Admin',
            'printDate' => now()->format('d/m/Y H:i')
        ])->setPaper($paperF4, 'portrait');

        return $pdf->stream("Registrasi_Kosongan_{$participant->name}.pdf");
    }
}
