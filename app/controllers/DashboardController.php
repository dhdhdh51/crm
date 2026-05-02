<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Lead;
use App\Models\SiteVisit;
use App\Models\Booking;
use App\Models\Notification;

class DashboardController extends Controller {

    public function index(): void {
        $leadModel  = new Lead();
        $visitModel = new SiteVisit();
        $bookModel  = new Booking();
        $notifModel = new Notification();
        $user       = Session::user();

        $leadStats    = $leadModel->stats();
        $pendingFups  = $leadModel->pendingFollowups(
            Session::role() === 'sales_executive' ? $user['id'] : null
        );
        $monthlyTrend = $leadModel->monthlyTrend(6);
        $sourceBreak  = $leadModel->sourceBreakdown();
        $convRate     = $leadModel->conversionRate();

        // Chart data
        $chartMonths  = array_column($monthlyTrend, 'month_label');
        $chartLeads   = array_column($monthlyTrend, 'total');
        $chartClosed  = array_column($monthlyTrend, 'closed');

        $sourceLabels = array_column($sourceBreak, 'source');
        $sourceCounts = array_column($sourceBreak, 'cnt');

        $donutLabels = ['New', 'Hot', 'Warm', 'Cold', 'Closed', 'Lost'];
        $donutData   = [
            $leadStats['new_count']    ?? 0,
            $leadStats['hot_count']    ?? 0,
            $leadStats['warm_count']   ?? 0,
            $leadStats['cold_count']   ?? 0,
            $leadStats['closed_count'] ?? 0,
            $leadStats['lost_count']   ?? 0,
        ];

        $this->view('dashboard.index', [
            'title'        => 'Dashboard',
            'leadStats'    => $leadStats,
            'visitCount'   => $visitModel->countThisMonth(),
            'revenue'      => $bookModel->thisMonthRevenue(),
            'totalRevenue' => $bookModel->totalRevenue(),
            'convRate'     => $convRate,
            'pendingFups'  => $pendingFups,
            'unreadCount'  => $notifModel->unreadCount($user['id']),
            'chartMonths'  => json_encode($chartMonths),
            'chartLeads'   => json_encode($chartLeads),
            'chartClosed'  => json_encode($chartClosed),
            'sourceLabels' => json_encode($sourceLabels),
            'sourceCounts' => json_encode($sourceCounts),
            'donutLabels'  => json_encode($donutLabels),
            'donutData'    => json_encode($donutData),
        ]);
    }
}
