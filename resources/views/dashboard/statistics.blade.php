@extends('layouts.app')

@section('title', 'Статистика')

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-800">
                <i class="fas fa-chart-bar mr-2 text-indigo-500"></i>
                Статистика системы
            </h1>
            <p class="text-gray-600 mt-1">Аналитика и статистические данные по работе платформы</p>
        </div>

        <!-- Общая статистика -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего пользователей</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_users'] }}</p>
                    </div>
                    <i class="fas fa-users text-4xl text-indigo-400"></i>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    <span class="text-green-600">{{ $stats['users_by_role']['participant'] }}</span> участников |
                    <span class="text-blue-600">{{ $stats['users_by_role']['jury'] }}</span> жюри |
                    <span class="text-purple-600">{{ $stats['users_by_role']['admin'] }}</span> админов
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего конкурсов</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_contests'] }}</p>
                    </div>
                    <i class="fas fa-trophy text-4xl text-green-400"></i>
                </div>
                <div class="mt-2 text-sm text-gray-600">
                    <span class="text-green-600">{{ $stats['active_contests'] }}</span> активных
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего работ</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $stats['total_submissions'] }}</p>
                    </div>
                    <i class="fas fa-file-alt text-4xl text-yellow-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего файлов</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $attachments_total }}</p>
                    </div>
                    <i class="fas fa-paperclip text-4xl text-purple-400"></i>
                </div>
            </div>
        </div>

        <!-- Статистика по статусам (как в admin.blade.php) -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @php
                $statusStats = $submissions_by_status ?? [];
                if(isset($stats['submissions_by_status'])) {
                    $statusStats = $stats['submissions_by_status'];
                }
            @endphp
            @foreach([
                'draft' => ['label' => 'Черновики', 'color' => 'gray'],
                'submitted' => ['label' => 'На проверке', 'color' => 'yellow'],
                'needs_fix' => ['label' => 'Доработка', 'color' => 'orange'],
                'accepted' => ['label' => 'Принято', 'color' => 'green'],
                'rejected' => ['label' => 'Отклонено', 'color' => 'red']
            ] as $status => $info)
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-{{ $info['color'] }}-600 text-2xl font-bold">{{ $statusStats[$status] ?? 0 }}</div>
                    <div class="text-sm text-gray-600">{{ $info['label'] }}</div>
                </div>
            @endforeach
        </div>

        <!-- График работ по дням -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-calendar-alt mr-2 text-indigo-500"></i>
                Динамика подачи работ (последние 30 дней)
            </h2>

            <div class="h-64" id="submissions-chart">
                <canvas id="submissions-chart-canvas"></canvas>
            </div>

            @if(empty($submissions_by_day) || $submissions_by_day->isEmpty())
                <p class="text-center text-gray-500 mt-4">Нет данных за последние 30 дней</p>
            @endif
        </div>

        <!-- Статистика по конкурсам -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- По конкурсам -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                    Распределение работ по конкурсам
                </h2>

                <div class="h-64" id="contests-chart">
                    <canvas id="contests-chart-canvas"></canvas>
                </div>

                @if(empty($submissions_by_contest) || $submissions_by_contest->isEmpty())
                    <p class="text-center text-gray-500 mt-4">Нет данных по конкурсам</p>
                @else
                    <div class="mt-4 space-y-2">
                        @foreach($submissions_by_contest as $item)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600">{{ $item->title ?? 'Без названия' }}</span>
                                <span class="font-medium">{{ $item->submissions_count ?? 0 }} работ</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- По статусам (детальная диаграмма) -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                    Распределение по статусам
                </h2>

                <div class="h-64" id="status-chart">
                    <canvas id="status-chart-canvas"></canvas>
                </div>

                @php
                    $hasStatusData = !empty($statusStats) && array_sum($statusStats) > 0;
                @endphp

                @if(!$hasStatusData)
                    <p class="text-center text-gray-500 mt-4">Нет данных по статусам</p>
                @else
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Черновики</span>
                            <span class="font-medium text-gray-800">{{ $statusStats['draft'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">На проверке</span>
                            <span class="font-medium text-yellow-600">{{ $statusStats['submitted'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Доработка</span>
                            <span class="font-medium text-orange-600">{{ $statusStats['needs_fix'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Принято</span>
                            <span class="font-medium text-green-600">{{ $statusStats['accepted'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Отклонено</span>
                            <span class="font-medium text-red-600">{{ $statusStats['rejected'] ?? 0 }}</span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Дополнительная статистика -->
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Среднее время проверки -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Среднее время проверки</h3>
                <div class="text-3xl font-bold text-gray-800">
                    {{ $avg_response_time->avg_hours ?? 0 }} <span class="text-lg font-normal text-gray-500">часов</span>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    От подачи до принятия решения
                </p>
            </div>

            <!-- Процент принятых работ -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Процент принятых работ</h3>
                @php
                    $total = $stats['total_submissions'] ?? $submissions_total ?? 1;
                    $accepted = $statusStats['accepted'] ?? 0;
                    $percentage = $total > 0 ? round(($accepted / $total) * 100) : 0;
                @endphp
                <div class="text-3xl font-bold text-green-600">
                    {{ $percentage }}%
                </div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>
            </div>

            <!-- Активные пользователи -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase mb-2">Активные пользователи</h3>
                <div class="text-3xl font-bold text-indigo-600">
                    {{ $active_users ?? 0 }}
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    За последние 7 дней
                </p>
            </div>
        </div>
    </div>

    <!-- Chart.js для графиков -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // График работ по дням
            const canvas1 = document.getElementById('submissions-chart-canvas');
            if (canvas1) {
                const ctx1 = canvas1.getContext('2d');

                @if(!empty($submissions_by_day) && $submissions_by_day->isNotEmpty())
                // Подготовка данных для графика на PHP стороне
                const labels1 = {!! json_encode($submissions_by_day->map(function($item) {
                    return $item->date ? \Carbon\Carbon::parse($item->date)->format('d.m') : '';
                })->values()->toArray()) !!};

                const data1 = {!! json_encode($submissions_by_day->pluck('count')->values()->toArray()) !!};

                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: labels1,
                        datasets: [{
                            label: 'Количество работ',
                            data: data1,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                }
                            }
                        }
                    }
                });
                @else
                if(canvas1.parentNode) {
                    canvas1.parentNode.innerHTML = '<p class="text-center text-gray-500">Нет данных для отображения</p>';
                }
                @endif
            }

            // Круговая диаграмма по конкурсам
            const canvas2 = document.getElementById('contests-chart-canvas');
            if (canvas2) {
                const ctx2 = canvas2.getContext('2d');

                @if(!empty($submissions_by_contest) && $submissions_by_contest->isNotEmpty())
                const labels2 = {!! json_encode($submissions_by_contest->pluck('title')->filter()->values()->toArray()) !!};
                const data2 = {!! json_encode($submissions_by_contest->pluck('submissions_count')->values()->toArray()) !!};

                if (labels2.length > 0 && data2.length > 0) {
                    new Chart(ctx2, {
                        type: 'doughnut',
                        data: {
                            labels: labels2,
                            datasets: [{
                                data: data2,
                                backgroundColor: [
                                    '#4f46e5',
                                    '#10b981',
                                    '#f59e0b',
                                    '#ef4444',
                                    '#8b5cf6',
                                    '#ec4899',
                                    '#14b8a6',
                                    '#f97316'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        boxWidth: 12,
                                        padding: 15
                                    }
                                }
                            }
                        }
                    });
                } else {
                    canvas2.parentNode.innerHTML = '<p class="text-center text-gray-500">Нет данных для отображения</p>';
                }
                @else
                if(canvas2.parentNode) {
                    canvas2.parentNode.innerHTML = '<p class="text-center text-gray-500">Нет данных для отображения</p>';
                }
                @endif
            }

            // Круговая диаграмма по статусам
            const canvas3 = document.getElementById('status-chart-canvas');
            if (canvas3) {
                const ctx3 = canvas3.getContext('2d');

                @php
                    $statusStats = $submissions_by_status ?? [];
                    if(isset($stats['submissions_by_status'])) {
                        $statusStats = $stats['submissions_by_status'];
                    }
                    $chartStatusData = [
                        $statusStats['draft'] ?? 0,
                        $statusStats['submitted'] ?? 0,
                        $statusStats['needs_fix'] ?? 0,
                        $statusStats['accepted'] ?? 0,
                        $statusStats['rejected'] ?? 0
                    ];
                    $hasChartData = array_sum($chartStatusData) > 0;
                @endphp

                @if($hasChartData)
                const statusData = {!! json_encode($chartStatusData) !!};

                new Chart(ctx3, {
                    type: 'doughnut',
                    data: {
                        labels: ['Черновики', 'На проверке', 'Доработка', 'Принято', 'Отклонено'],
                        datasets: [{
                            data: statusData,
                            backgroundColor: [
                                '#9ca3af',
                                '#f59e0b',
                                '#f97316',
                                '#10b981',
                                '#ef4444'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 15
                                }
                            }
                        }
                    }
                });
                @else
                if(canvas3.parentNode) {
                    canvas3.parentNode.innerHTML = '<p class="text-center text-gray-500">Нет данных для отображения</p>';
                }
                @endif
            }
        });
    </script>
@endsection
