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
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего пользователей</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $users_total ?? 0 }}</p>
                    </div>
                    <i class="fas fa-users text-4xl text-indigo-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего конкурсов</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $contests_total ?? 0 }}</p>
                    </div>
                    <i class="fas fa-trophy text-4xl text-yellow-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего работ</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $submissions_total ?? 0 }}</p>
                    </div>
                    <i class="fas fa-file-alt text-4xl text-green-400"></i>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 uppercase">Всего файлов</p>
                        <p class="text-3xl font-bold text-gray-800">{{ $attachments_total ?? 0 }}</p>
                    </div>
                    <i class="fas fa-paperclip text-4xl text-purple-400"></i>
                </div>
            </div>
        </div>

        <!-- График работ по дням -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">
                <i class="fas fa-calendar-alt mr-2 text-indigo-500"></i>
                Динамика подачи работ (последние 30 дней)
            </h2>

            <div class="h-64" id="submissions-chart"></div>
        </div>

        <!-- Статистика по конкурсам и статусам -->
        <div class="grid md:grid-cols-2 gap-6">
            <!-- По конкурсам -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                    Распределение работ по конкурсам
                </h2>

                <div class="h-64" id="contests-chart"></div>

                <div class="mt-4 space-y-2">
                    @foreach($submissions_by_contest ?? [] as $item)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-600">{{ $item->title }}</span>
                            <span class="font-medium">{{ $item->submissions_count }} работ</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- По статусам -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">
                    <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                    Распределение по статусам
                </h2>

                <div class="h-64" id="status-chart"></div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                        <span class="text-gray-600">Черновики</span>
                        <span class="font-medium text-gray-800">{{ $submissions_by_status['draft'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                        <span class="text-gray-600">На проверке</span>
                        <span class="font-medium text-yellow-600">{{ $submissions_by_status['submitted'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                        <span class="text-gray-600">Доработка</span>
                        <span class="font-medium text-orange-600">{{ $submissions_by_status['needs_fix'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                        <span class="text-gray-600">Принято</span>
                        <span class="font-medium text-green-600">{{ $submissions_by_status['accepted'] ?? 0 }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded">
                        <span class="text-gray-600">Отклонено</span>
                        <span class="font-medium text-red-600">{{ $submissions_by_status['rejected'] ?? 0 }}</span>
                    </div>
                </div>
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
                    $total = $submissions_total ?? 1;
                    $accepted = $submissions_by_status['accepted'] ?? 0;
                    $percentage = round(($accepted / $total) * 100);
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
            const ctx1 = document.getElementById('submissions-chart')?.getContext('2d');
            if (ctx1) {
                new Chart(ctx1, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode($submissions_by_day->pluck('date')->map(function($date) {
                    return \Carbon\Carbon::parse($date)->format('d.m');
                })) !!},
                        datasets: [{
                            label: 'Количество работ',
                            data: {!! json_encode($submissions_by_day->pluck('count')) !!},
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
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }

            // Круговая диаграмма по конкурсам
            const ctx2 = document.getElementById('contests-chart')?.getContext('2d');
            if (ctx2) {
                new Chart(ctx2, {
                    type: 'doughnut',
                    data: {
                        labels: {!! json_encode($submissions_by_contest->pluck('title')) !!},
                        datasets: [{
                            data: {!! json_encode($submissions_by_contest->pluck('submissions_count')) !!},
                            backgroundColor: [
                                '#4f46e5',
                                '#10b981',
                                '#f59e0b',
                                '#ef4444',
                                '#8b5cf6'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });
            }

            // Круговая диаграмма по статусам
            const ctx3 = document.getElementById('status-chart')?.getContext('2d');
            if (ctx3) {
                new Chart(ctx3, {
                    type: 'doughnut',
                    data: {
                        labels: ['Черновики', 'На проверке', 'Доработка', 'Принято', 'Отклонено'],
                        datasets: [{
                            data: [
                                {{ $submissions_by_status['draft'] ?? 0 }},
                                {{ $submissions_by_status['submitted'] ?? 0 }},
                                {{ $submissions_by_status['needs_fix'] ?? 0 }},
                                {{ $submissions_by_status['accepted'] ?? 0 }},
                                {{ $submissions_by_status['rejected'] ?? 0 }}
                            ],
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
                                position: 'bottom'
                            }
                        }
                    }
                });
            }
        });
    </script>
@endsection
