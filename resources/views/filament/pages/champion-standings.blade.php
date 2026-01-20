<x-filament-panels::page>
    <div class="space-y-8">
        {{-- Event Filter Section --}}
        <x-filament::section class="rounded-[32px] border-none shadow-sm dark:bg-gray-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex-1 max-w-lg">
                    <form wire:submit.prevent="calculateStandings">
                        {{ $this->form }}
                    </form>
                </div>

                @if($judgingStandard)
                <div class="flex items-center space-x-3 bg-primary-500/10 dark:bg-primary-500/20 px-6 py-3 rounded-2xl border border-primary-500/20">
                    <x-heroicon-o-shield-check class="w-5 h-5 text-primary-600" />
                    <div>
                        <p class="text-[10px] font-bold text-primary-600 uppercase tracking-widest leading-none">{{ __('messages.fields.standard_used') }}</p>
                        <p class="text-sm font-black text-primary-700 dark:text-primary-400 uppercase mt-1">
                            {{ $judgingStandard === 'sni' ? 'Standar SNI' : 'Standar IBC' }}
                        </p>
                    </div>
                </div>
                @endif
            </div>
        </x-filament::section>

        {{-- Header Summary --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @php
            $topTeam = $teamStandings[0]['name'] ?? '-';
            $topSF = $sfStandings[0]['name'] ?? '-';
            @endphp
            <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-3xl p-6 shadow-xl shadow-amber-500/20 text-white relative overflow-hidden">
                <div class="absolute -right-4 -top-4 opacity-20">
                    <x-heroicon-o-fire class="w-24 h-24" />
                </div>
                <p class="text-xs font-bold uppercase tracking-widest opacity-80">{{ __('messages.fields.team_champion_potential') }}</p>
                <h3 class="text-2xl font-black mt-2 leading-tight">{{ $topTeam }}</h3>
                <div class="flex items-center mt-4 space-x-2">
                    <span class="px-2 py-1 bg-white/20 rounded-lg text-[10px] font-bold">1st PLACE CANDIDATE</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-6 shadow-xl shadow-blue-600/20 text-white relative overflow-hidden">
                <div class="absolute -right-4 -top-4 opacity-20">
                    <x-heroicon-o-user class="w-24 h-24" />
                </div>
                <p class="text-xs font-bold uppercase tracking-widest opacity-80">{{ __('messages.fields.sf_champion_potential') }}</p>
                <h3 class="text-2xl font-black mt-2 leading-tight">{{ $topSF }}</h3>
                <div class="flex items-center mt-4 space-x-2">
                    <span class="px-2 py-1 bg-white/20 rounded-lg text-[10px] font-bold">1st PLACE CANDIDATE</span>
                </div>
            </div>

            <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-6 shadow-xl shadow-emerald-500/20 text-white relative overflow-hidden">
                <div class="absolute -right-4 -top-4 opacity-20">
                    <x-heroicon-o-calculator class="w-24 h-24" />
                </div>
                <p class="text-xs font-bold uppercase tracking-widest opacity-80">{{ __('messages.fields.point_rules') }} ({{ $judgingStandard === 'ibc' ? 'IBC' : 'SNI' }})</p>
                <div class="grid grid-cols-2 gap-x-4 mt-2">
                    <div class="text-[10px] font-semibold">Gold: {{ $judgingStandard === 'ibc' ? '10pt' : '15pt' }}</div>
                    <div class="text-[10px] font-semibold">GC: {{ $judgingStandard === 'ibc' ? '20pt' : '30pt' }}</div>
                    <div class="text-[10px] font-semibold">Silver: {{ $judgingStandard === 'ibc' ? '6pt' : '7pt' }}</div>
                    <div class="text-[10px] font-semibold">Bronze: {{ $judgingStandard === 'ibc' ? '4pt' : '3pt' }}</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mt-4">
            <!-- Team Standings -->
            <x-filament::section class="rounded-3xl overflow-hidden border-none shadow-sm dark:bg-gray-800">
                <x-slot name="heading">
                    <div class="flex items-center space-x-3">
                        <div class="bg-amber-100 dark:bg-amber-500/20 p-2 rounded-xl">
                            <x-heroicon-o-trophy class="w-5 h-5 text-amber-600" />
                        </div>
                        <span class="text-xl font-black tracking-tight uppercase">{{ __('messages.fields.team_champion') }}</span>
                    </div>
                </x-slot>

                <div class="mt-4">
                    <table class="w-full text-sm text-left rtl:text-right">
                        <thead>
                            <tr class="text-[10px] text-gray-400 uppercase tracking-[2px] font-bold border-b dark:border-gray-700">
                                <th class="px-4 py-4">{{ __('messages.fields.rank') }}</th>
                                <th class="px-4 py-4">{{ __('messages.fields.team_name') }}</th>
                                <th class="px-4 py-4 text-center">GC</th>
                                <th class="px-4 py-4 text-center">🥇</th>
                                <th class="px-4 py-4 text-center">🥈</th>
                                <th class="px-4 py-4 text-center">🥉</th>
                                <th class="px-4 py-4 text-right">{{ __('POINTS') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @forelse($teamStandings as $index => $team)
                            <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-5">
                                    @if($index === 0)
                                    <div class="w-10 h-10 bg-amber-400 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-400/30">
                                        <span class="text-white font-black">1</span>
                                    </div>
                                    @elseif($index === 1)
                                    <div class="w-8 h-8 bg-slate-300 rounded-xl flex items-center justify-center">
                                        <span class="text-slate-600 font-bold">2</span>
                                    </div>
                                    @elseif($index === 2)
                                    <div class="w-8 h-8 bg-orange-400/30 rounded-xl flex items-center justify-center border border-orange-400/50">
                                        <span class="text-orange-700 dark:text-orange-400 font-bold">3</span>
                                    </div>
                                    @else
                                    <span class="text-gray-400 font-bold ml-3 italic">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-5 font-bold text-gray-900 dark:text-white uppercase tracking-tight">
                                    {{ $team['name'] }}
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span class="px-2 py-1 bg-amber-100 dark:bg-amber-500/10 text-amber-600 rounded-lg font-black text-xs">{{ $team['gc'] }}</span>
                                </td>
                                <td class="px-4 py-5 text-center font-bold">{{ $team['gold'] }}</td>
                                <td class="px-4 py-5 text-center font-bold text-gray-500">{{ $team['silver'] }}</td>
                                <td class="px-4 py-5 text-center font-bold text-orange-400">{{ $team['bronze'] }}</td>
                                <td class="px-4 py-5 text-right font-black text-lg text-primary-600 dark:text-primary-400">
                                    {{ number_format($team['points']) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-400 italic">
                                    {{ __('Belum ada data pemenang untuk dihitung.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>

            <!-- SF Standings -->
            <x-filament::section class="rounded-3xl overflow-hidden border-none shadow-sm dark:bg-gray-800">
                <x-slot name="heading">
                    <div class="flex items-center space-x-3">
                        <div class="bg-blue-100 dark:bg-blue-500/20 p-2 rounded-xl">
                            <x-heroicon-o-user class="w-5 h-5 text-blue-600" />
                        </div>
                        <span class="text-xl font-black tracking-tight uppercase">{{ __('messages.fields.sf_champion') }}</span>
                    </div>
                </x-slot>

                <div class="mt-4">
                    <table class="w-full text-sm text-left rtl:text-right">
                        <thead>
                            <tr class="text-[10px] text-gray-400 uppercase tracking-[2px] font-bold border-b dark:border-gray-700">
                                <th class="px-4 py-4">{{ __('messages.fields.rank') }}</th>
                                <th class="px-4 py-4">{{ __('messages.fields.participant') }}</th>
                                <th class="px-4 py-4 text-center">GC</th>
                                <th class="px-4 py-4 text-center">🥇</th>
                                <th class="px-4 py-4 text-center">🥈</th>
                                <th class="px-4 py-4 text-center">🥉</th>
                                <th class="px-4 py-4 text-right">{{ __('POINTS') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @forelse($sfStandings as $index => $sf)
                            <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-4 py-5">
                                    @if($index === 0)
                                    <div class="w-10 h-10 bg-indigo-500 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/30">
                                        <span class="text-white font-black">1</span>
                                    </div>
                                    @elseif($index === 1)
                                    <div class="w-8 h-8 bg-slate-300 rounded-xl flex items-center justify-center">
                                        <span class="text-slate-600 font-bold">2</span>
                                    </div>
                                    @elseif($index === 2)
                                    <div class="w-8 h-8 bg-blue-400/30 rounded-xl flex items-center justify-center border border-blue-400/50">
                                        <span class="text-blue-700 dark:text-blue-400 font-bold">3</span>
                                    </div>
                                    @else
                                    <span class="text-gray-400 font-bold ml-3 italic">#{{ $index + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-5 font-bold text-gray-900 dark:text-white uppercase tracking-tight">
                                    {{ $sf['name'] }}
                                </td>
                                <td class="px-4 py-5 text-center">
                                    <span class="px-2 py-1 bg-blue-100 dark:bg-blue-500/10 text-blue-600 rounded-lg font-black text-xs">{{ $sf['gc'] }}</span>
                                </td>
                                <td class="px-4 py-5 text-center font-bold">{{ $sf['gold'] }}</td>
                                <td class="px-4 py-5 text-center font-bold text-gray-500">{{ $sf['silver'] }}</td>
                                <td class="px-4 py-5 text-center font-bold text-orange-400">{{ $sf['bronze'] }}</td>
                                <td class="px-4 py-5 text-right font-black text-lg text-primary-600 dark:text-primary-400">
                                    {{ number_format($sf['points']) }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-gray-400 italic">
                                    {{ __('Belum ada data pemenang untuk dihitung.') }}
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        </div>
    </div>
</x-filament-panels::page>