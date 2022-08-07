<?php

namespace App\Http\Controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function plugin_details(Request $request)
    {

        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $pluginSlug = $request->plugin;

        $dateArray = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            (new DateTime($endDate))->modify('+1 day')
        );

        $dateRange = [];
        foreach ($dateArray as $key => $value) {
            $dateRange[$value->format('Y-m-d')] = 0;
        }

        $endPoints = $this->getApiEndpoints($pluginSlug);


        $downloadsJsonRes = Cache::get($pluginSlug . '-downloads');

        if(!$downloadsJsonRes) {
            $downloadsJsonRes = Http::get($endPoints['downloads']);
            $downloadsJsonRes = $downloadsJsonRes->body();
            Cache::put($pluginSlug . '-downloads', $downloadsJsonRes, now()->addDay());
        }

        $dayWiseDownloadsArray = json_decode($downloadsJsonRes, true);




        $versionData = Http::get($endPoints['activeVersions']);
        $versionData = json_decode($versionData->body(), true);




        $versionRes = Cache::get($pluginSlug . '-version');

        if(!$versionRes) {
            $versionRes = Http::get($endPoints['activeVersions']);
            $versionRes = $versionRes->body();
            Cache::put($pluginSlug . '-version', $versionRes, now()->addDay());
        }

        $versionArray = json_decode($versionRes, true);


        $version['number'] = [];
        $version['percentage'] = [];
        foreach ($versionArray as $number => $percentage) {
            array_push($version['number'], $number);
            array_push($version['percentage'], round($percentage, 2));
        }


        $selectedDateRangeDownloadData = array_intersect_key($dayWiseDownloadsArray,$dateRange);

        $download['date'] = [];
        $download['value'] = [];
        foreach ($selectedDateRangeDownloadData as $date => $value) {
            array_push($download['date'], $date);
            array_push($download['value'], $value);
        }

        return ['version' => $version, 'download' => $download];
    }

    private function getApiEndPoints($pluginName)
    {
        $downloads = "https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug=" . $pluginName;
        $activeVersions = "https://api.wordpress.org/stats/plugin/1.0/" . $pluginName;

        return ['downloads' => $downloads, 'activeVersions' => $activeVersions];
    }
}
