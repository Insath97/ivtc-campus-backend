<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Course;
use App\Models\Pathway;
use App\Models\Registration;
use App\Models\Lecturer;
use App\Models\Staff;
use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller implements HasMiddleware
{
    /**
     * Define the middleware for the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:Dashboard View'),
        ];
    }

    /**
     * Retrieve admin dashboard statistics.
     */
    public function index(Request $request)
    {
        try {
            $range = $request->get('range', '30_days'); // 30_days, 6_months, 12_months, lifetime

            // 1. Get summary counts and trends
            $summary = $this->getSummaryStatistics();

            // 2. Get student registration analytics over time
            $analytics = $this->getStudentAnalytics($range);

            // 3. Get registrations breakdown by Pathway
            $pathwayBreakdown = $this->getRegistrationsByPathway();

            // 4. Get registrations breakdown by Status
            $statusBreakdown = $this->getRegistrationsByStatus();

            // 5. Get recent registrations
            $recentRegistrations = Registration::with(['pathway', 'program'])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($registration) {
                    // Temporarily expose hidden fields for dashboard view
                    return $registration->makeVisible(['status', 'remarks']);
                });

            // 6. Get pending contact messages count
            $pendingContactsCount = Contact::where('is_replied', false)->count();

            return response()->json([
                'status' => 'success',
                'message' => 'Dashboard data retrieved successfully',
                'data' => [
                    'summary' => $summary,
                    'analytics' => $analytics,
                    'pathway_breakdown' => $pathwayBreakdown,
                    'status_breakdown' => $statusBreakdown,
                    'recent_registrations' => $recentRegistrations,
                    'pending_contacts_count' => $pendingContactsCount,
                ]
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve dashboard stats',
                'error' => config('app.debug') ? $th->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Calculate summary statistics and growth/trend rates.
     */
    private function getSummaryStatistics(): array
    {
        $now = Carbon::now();

        // --- A. Pathways Stats ---
        $totalPathways = Pathway::count();
        $activePathways = Pathway::active()->count();
        $inactivePathways = $totalPathways - $activePathways;

        $currentMonthPathways = Pathway::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        $prevMonthPathways = Pathway::whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->count();
        $pathwaysGrowth = $this->calculateGrowthPercentage($currentMonthPathways, $prevMonthPathways);

        // --- B. Registrations Stats ---
        $totalRegistrations = Registration::count();
        $pendingRegistrations = Registration::where('status', 'pending')->count();
        $approvedRegistrations = Registration::where('status', 'approved')->count();
        $rejectedRegistrations = Registration::where('status', 'rejected')->count();

        $currentMonthRegistrations = Registration::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        $prevMonthRegistrations = Registration::whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->count();
        $registrationsGrowth = $this->calculateGrowthPercentage($currentMonthRegistrations, $prevMonthRegistrations);

        // --- C. New Students Stats (Last 30 Days) ---
        $newStudentsCount = Registration::where('created_at', '>=', $now->copy()->subDays(30))->count();
        $prevNewStudentsCount = Registration::whereBetween('created_at', [
            $now->copy()->subDays(60),
            $now->copy()->subDays(30)
        ])->count();
        $newStudentsGrowth = $this->calculateGrowthPercentage($newStudentsCount, $prevNewStudentsCount);

        // --- D. Registrations by Program Type ---
        $courseRegistrations = Registration::where('program_type', 'course')->count();
        $programRegistrations = Registration::where('program_type', 'program')->count();

        // --- E. Courses Stats ---
        $totalCourses = Course::count();
        $activeCourses = Course::active()->count();
        $inactiveCourses = $totalCourses - $activeCourses;
        $newCourses = Course::where('is_new', true)->count();

        $currentMonthCourses = Course::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();
        $prevMonthCourses = Course::whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->count();
        $coursesGrowth = $this->calculateGrowthPercentage($currentMonthCourses, $prevMonthCourses);

        // --- F. Batches Stats ---
        $totalBatches = Batch::count();
        $activeBatches = Batch::active()->count();
        $inactiveBatches = $totalBatches - $activeBatches;

        // --- G. Lecturers Stats ---
        $totalLecturers = Lecturer::count();
        $activeLecturers = Lecturer::active()->count();
        $inactiveLecturers = $totalLecturers - $activeLecturers;

        // --- H. Staff Stats ---
        $totalStaff = Staff::count();
        $activeStaff = Staff::active()->count();
        $inactiveStaff = $totalStaff - $activeStaff;

        return [
            'pathways' => [
                'total' => $totalPathways,
                'active' => $activePathways,
                'inactive' => $inactivePathways,
                'growth_rate' => $pathwaysGrowth,
            ],
            'registrations' => [
                'total' => $totalRegistrations,
                'pending' => $pendingRegistrations,
                'approved' => $approvedRegistrations,
                'rejected' => $rejectedRegistrations,
                'growth_rate' => $registrationsGrowth,
                'by_program_type' => [
                    'course' => $courseRegistrations,
                    'program' => $programRegistrations,
                ]
            ],
            'new_students' => [
                'count' => $newStudentsCount,
                'growth_rate' => $newStudentsGrowth,
            ],
            'courses' => [
                'total' => $totalCourses,
                'active' => $activeCourses,
                'inactive' => $inactiveCourses,
                'new' => $newCourses,
                'growth_rate' => $coursesGrowth,
            ],
            'batches' => [
                'total' => $totalBatches,
                'active' => $activeBatches,
                'inactive' => $inactiveBatches,
            ],
            'lecturers' => [
                'total' => $totalLecturers,
                'active' => $activeLecturers,
                'inactive' => $inactiveLecturers,
            ],
            'staff' => [
                'total' => $totalStaff,
                'active' => $activeStaff,
                'inactive' => $inactiveStaff,
            ]
        ];
    }

    /**
     * Helper to compute percentage growth rates.
     */
    private function calculateGrowthPercentage($current, $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }
        return round((($current - $previous) / $previous) * 100, 2);
    }

    /**
     * Retrieve time-series registration data based on selected range.
     */
    private function getStudentAnalytics(string $range): array
    {
        $data = [];
        $driver = DB::connection()->getDriverName();
        $now = Carbon::now();

        switch ($range) {
            case '6_months':
                $startDate = $now->copy()->subMonths(5)->startOfMonth();
                $endDate = $now->copy()->endOfMonth();

                if ($driver === 'sqlite') {
                    $results = Registration::select(
                        DB::raw("strftime('%Y-%m', created_at) as label"),
                        DB::raw("count(id) as count")
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('label')
                    ->orderBy('label', 'asc')
                    ->get();
                } else {
                    $results = Registration::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') as label"),
                        DB::raw("count(id) as count")
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('label')
                    ->orderBy('label', 'asc')
                    ->get();
                }

                $period = Carbon::now()->subMonths(5)->startOfMonth()->monthsUntil(Carbon::now()->endOfMonth());
                $resultsMap = $results->pluck('count', 'label')->toArray();

                foreach ($period as $date) {
                    $key = $date->format('Y-%m');
                    $data[] = [
                        'label' => $date->format('F Y'),
                        'count' => (int) ($resultsMap[$key] ?? 0)
                    ];
                }
                break;

            case '12_months':
                $startDate = $now->copy()->subMonths(11)->startOfMonth();
                $endDate = $now->copy()->endOfMonth();

                if ($driver === 'sqlite') {
                    $results = Registration::select(
                        DB::raw("strftime('%Y-%m', created_at) as label"),
                        DB::raw("count(id) as count")
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('label')
                    ->orderBy('label', 'asc')
                    ->get();
                } else {
                    $results = Registration::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') as label"),
                        DB::raw("count(id) as count")
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('label')
                    ->orderBy('label', 'asc')
                    ->get();
                }

                $period = Carbon::now()->subMonths(11)->startOfMonth()->monthsUntil(Carbon::now()->endOfMonth());
                $resultsMap = $results->pluck('count', 'label')->toArray();

                foreach ($period as $date) {
                    $key = $date->format('Y-%m');
                    $data[] = [
                        'label' => $date->format('M Y'),
                        'count' => (int) ($resultsMap[$key] ?? 0)
                    ];
                }
                break;

            case 'lifetime':
                if ($driver === 'sqlite') {
                    $results = Registration::select(
                        DB::raw("strftime('%Y', created_at) as label"),
                        DB::raw("count(id) as count")
                    )
                    ->groupBy('label')
                    ->orderBy('label', 'asc')
                    ->get();
                } else {
                    $results = Registration::select(
                        DB::raw("DATE_FORMAT(created_at, '%Y') as label"),
                        DB::raw("count(id) as count")
                    )
                    ->groupBy('label')
                    ->orderBy('label', 'asc')
                    ->get();
                }

                foreach ($results as $res) {
                    $data[] = [
                        'label' => $res->label,
                        'count' => (int) $res->count
                    ];
                }
                break;

            case '30_days':
            default:
                $startDate = $now->copy()->subDays(29)->startOfDay();
                $endDate = $now->copy()->endOfDay();

                if ($driver === 'sqlite') {
                    $results = Registration::select(
                        DB::raw("date(created_at) as label"),
                        DB::raw("count(id) as count")
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('label')
                    ->orderBy('label', 'asc')
                    ->get();
                } else {
                    $results = Registration::select(
                        DB::raw("DATE(created_at) as label"),
                        DB::raw("count(id) as count")
                    )
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->groupBy('label')
                    ->orderBy('label', 'asc')
                    ->get();
                }

                $resultsMap = $results->pluck('count', 'label')->toArray();

                for ($i = 29; $i >= 0; $i--) {
                    $date = Carbon::now()->subDays($i);
                    $key = $date->format('Y-m-d');
                    $data[] = [
                        'label' => $date->format('M d'),
                        'count' => (int) ($resultsMap[$key] ?? 0)
                    ];
                }
                break;
        }

        return $data;
    }

    /**
     * Retrieve registration count for each pathway.
     */
    private function getRegistrationsByPathway(): array
    {
        return Pathway::select('pathways.id', 'pathways.name')
            ->selectRaw('count(registrations.id) as registrations_count')
            ->leftJoin('registrations', 'pathways.id', '=', 'registrations.pathway_id')
            ->groupBy('pathways.id', 'pathways.name')
            ->orderBy('registrations_count', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Retrieve breakdown count by registration status.
     */
    private function getRegistrationsByStatus(): array
    {
        $statuses = ['pending', 'approved', 'rejected'];
        $counts = Registration::select('status')
            ->selectRaw('count(id) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $data = [];
        foreach ($statuses as $status) {
            $data[] = [
                'status' => $status,
                'count' => (int) ($counts[$status] ?? 0)
            ];
        }

        return $data;
    }
}
