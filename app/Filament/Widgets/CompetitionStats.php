<?php

namespace App\Filament\Widgets;

use App\Models\Fish;
use App\Models\FishScore;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CompetitionStats extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = auth()->user();
        $fishQuery = Fish::query();
        $scoreQuery = FishScore::query();

        // Scope queries if user is an Event Admin (and not Super Admin)
        if ($user && $user->isEventAdmin() && !$user->isAdmin()) {
            $eventIds = $user->managed_events()->pluck('events.id');

            $fishQuery->whereIn('event_id', $eventIds);

            $scoreQuery->whereHas('fish', function ($q) use ($eventIds) {
                $q->whereIn('event_id', $eventIds);
            });
        }

        // Basic Fish Stats
        $totalFishes = (clone $fishQuery)->count();
        $judgedFishes = (clone $fishQuery)->whereHas('scores')->count();
        $remainingFishes = $totalFishes - $judgedFishes;

        // Participation Stats
        $totalParticipants = (clone $fishQuery)->distinct('participant_name')->count('participant_name');

        $totalTeams = (clone $fishQuery)
            ->whereNotNull('team_name')
            ->where('team_name', '!=', '')
            ->distinct('team_name')
            ->count('team_name');

        $totalSingleFighters = (clone $fishQuery)
            ->where(function ($q) {
                $q->whereNull('team_name')->orWhere('team_name', '');
            })
            ->distinct('participant_name')
            ->count('participant_name');

        $totalScores = $scoreQuery->count();

        return [
            Stat::make(__('messages.fields.stat_total_fish'), $totalFishes)
                ->description(__('messages.fields.stat_desc_registered'))
                ->descriptionIcon('heroicon-m-numbered-list')
                ->color('info'),

            Stat::make(__('messages.fields.stat_judging_progress'), $judgedFishes)
                ->description(__('messages.fields.stat_desc_not_judged', ['count' => $remainingFishes]))
                ->descriptionIcon('heroicon-m-scale')
                ->color($remainingFishes == 0 ? 'success' : 'warning'),

            Stat::make(__('messages.fields.stat_total_participants'), $totalParticipants)
                ->description(__('messages.fields.stat_desc_unique_owners'))
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),

            Stat::make(__('messages.fields.stat_total_teams'), $totalTeams)
                ->description(__('messages.fields.stat_desc_registered_teams'))
                ->descriptionIcon('heroicon-m-flag')
                ->color('success'),

            Stat::make(__('messages.fields.stat_single_fighter'), $totalSingleFighters)
                ->description(__('messages.fields.stat_desc_individual'))
                ->descriptionIcon('heroicon-m-user')
                ->color('gray'),

            Stat::make(__('messages.fields.stat_nomination'), (clone $fishQuery)->where('is_nominated', true)->count())
                ->description(__('messages.fields.stat_desc_ready_rank'))
                ->descriptionIcon('heroicon-m-star')
                ->color('amber'),

            Stat::make(__('messages.fields.stat_champion'), (clone $fishQuery)->whereNotNull('final_rank')->count())
                ->description(__('messages.fields.stat_desc_locked'))
                ->descriptionIcon('heroicon-m-trophy')
                ->color('success'),
        ];
    }
}
