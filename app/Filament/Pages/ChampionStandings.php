<?php

namespace App\Filament\Pages;

use App\Models\ScoreSnapshot;
use App\Models\Fish;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use App\Models\Event;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;

class ChampionStandings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static string $view = 'filament.pages.champion-standings';

    public ?int $eventId = null;
    public ?string $judgingStandard = null;

    public static function getNavigationGroup(): ?string
    {
        return __('messages.navigation.competition');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.pages.champion_standings');
    }

    public static function canAccess(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // Superadmin can always access
        if ($user && $user->isAdmin()) {
            return true;
        }

        // Event admin can access if they manage at least one event
        if ($user && $user->isEventAdmin()) {
            return $user->managed_events()->exists();
        }

        return false;
    }

    public $teamStandings = [];
    public $sfStandings = [];

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('print_standings')
                ->label(__('messages.actions.print_results'))
                ->icon('heroicon-o-printer')
                ->color('success')
                ->url(fn() => route('print.champion-standings', ['event_id' => $this->eventId]))
                ->openUrlInNewTab(),
        ];
    }

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user && $user->isEventAdmin()) {
            $this->eventId = $user->managed_events()->first()?->id;
        } else {
            $this->eventId = Event::first()?->id;
        }

        $this->calculateStandings();
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('eventId')
                ->label(__('messages.resources.events'))
                ->options(function () {
                    /** @var \App\Models\User $user */
                    $user = Auth::user();
                    $query = Event::query();
                    if ($user && $user->isEventAdmin()) {
                        $eventIds = $user->managed_events()->pluck('events.id');
                        $query->whereIn('id', $eventIds);
                    }
                    return $query->pluck('name', 'id');
                })
                ->live()
                ->afterStateUpdated(fn() => $this->calculateStandings())
                ->native(false)
                ->searchable()
                ->required(),
        ];
    }

    protected function calculateStandings(): void
    {
        if (!$this->eventId) {
            $this->teamStandings = [];
            $this->sfStandings = [];
            return;
        }

        $event = Event::find($this->eventId);
        $this->judgingStandard = $event?->judging_standard;

        // 1. Get all fishes with official ranks or winner types
        $query = Fish::query()
            ->where('event_id', $this->eventId)
            ->where(function ($q) {
                $q->whereNotNull('final_rank')
                    ->orWhereNotNull('winner_type');
            })
            ->with(['event', 'bettaClass', 'participant']); // Add participant relation

        $rankedFishes = $query->get();

        $tempTeams = [];
        $tempSF = [];

        foreach ($rankedFishes as $fish) {
            $points = 0;
            $standard = $fish->event->judging_standard ?? 'sni';
            $category = $fish->participant->category ?? 'other';
            $eventModel = $fish->event; // Data event untuk ambil custom points

            if ($eventModel) {
                if ($fish->final_rank == 1) $points += $eventModel->point_rank1;
                elseif ($fish->final_rank == 2) $points += $eventModel->point_rank2;
                elseif ($fish->final_rank == 3) $points += $eventModel->point_rank3;

                if ($fish->winner_type === 'gc') $points += $eventModel->point_gc;
                if ($fish->winner_type === 'bob') $points += $eventModel->point_bob;
            }

            // Aggregate by Team (ONLY IF category is 'team')
            if ($category === 'team' && $fish->team_name) {
                if (!isset($tempTeams[$fish->team_name])) {
                    $tempTeams[$fish->team_name] = [
                        'name' => $fish->team_name,
                        'points' => 0,
                        'gold' => 0,
                        'silver' => 0,
                        'bronze' => 0,
                        'gc' => 0
                    ];
                }
                $tempTeams[$fish->team_name]['points'] += $points;
                if ($fish->final_rank == 1) $tempTeams[$fish->team_name]['gold']++;
                if ($fish->final_rank == 2) $tempTeams[$fish->team_name]['silver']++;
                if ($fish->final_rank == 3) $tempTeams[$fish->team_name]['bronze']++;
                if ($fish->winner_type === 'gc') $tempTeams[$fish->team_name]['gc']++;
            }

            // Aggregate by Single Fighter (ONLY IF category is 'single_fighter')
            if ($category === 'single_fighter' && $fish->participant_name) {
                if (!isset($tempSF[$fish->participant_name])) {
                    $tempSF[$fish->participant_name] = [
                        'name' => $fish->participant_name,
                        'points' => 0,
                        'gold' => 0,
                        'silver' => 0,
                        'bronze' => 0,
                        'gc' => 0
                    ];
                }
                $tempSF[$fish->participant_name]['points'] += $points;
                if ($fish->final_rank == 1) $tempSF[$fish->participant_name]['gold']++;
                if ($fish->final_rank == 2) $tempSF[$fish->participant_name]['silver']++;
                if ($fish->final_rank == 3) $tempSF[$fish->participant_name]['bronze']++;
                if ($fish->winner_type === 'gc') $tempSF[$fish->participant_name]['gc']++;
            }
        }

        // Sort: Points -> GC -> Gold -> Silver -> Bronze
        $sortFn = function ($a, $b) {
            if ($b['points'] !== $a['points']) return $b['points'] <=> $a['points'];
            if ($b['gc'] !== $a['gc']) return $b['gc'] <=> $a['gc'];
            if ($b['gold'] !== $a['gold']) return $b['gold'] <=> $a['gold'];
            if ($b['silver'] !== $a['silver']) return $b['silver'] <=> $a['silver'];
            return $b['bronze'] <=> $a['bronze'];
        };

        usort($tempTeams, $sortFn);
        usort($tempSF, $sortFn);

        $this->teamStandings = array_slice($tempTeams, 0, 10);
        $this->sfStandings = array_slice($tempSF, 0, 10);
    }
}
