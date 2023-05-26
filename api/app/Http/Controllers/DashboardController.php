<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;

use App\Models\Node;
use App\Helper\Helper;

class DashboardController extends Controller
{
    public function metrics(Request $request)
    {
        $nodeMaster = Node::where('id', $request->id)->firstOrFail();

        try {
            $metrics = Helper::httpClient('GET', '/metrics', $nodeMaster);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        $fileContent = $metrics;
        $fileName = 'metrics.txt';

        Storage::put('/public/' . $fileName, $fileContent);

        return Storage::download('public/' . $fileName);
    }

    public function logs(Request $request)
    {
        $nodeMaster = Node::where('id', $request->id)->firstOrFail();

        try {
            $logs = Helper::httpClient('GET', '/logs/syslog', $nodeMaster);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        $fileContent = $logs;
        $fileName = 'logs.txt';

        Storage::put('/public/' . $fileName, $fileContent);

        return Storage::download('public/' . $fileName);
    }

    public function resourceUsages(Request $request)
    {
        // Resources usage for CPU + MEMORY
        $CPU_USED = 0; $MEM_USED = 0;
        $CPU_TOTL = 0; $MEM_TOTL = 0;

        foreach ($request->id as $id) {
            $nodeMaster = Node::where('id', $request->id)->firstOrFail();

            try {
                $resourcesUsed = Helper::httpClient('GET', '/apis/metrics.k8s.io/v1beta1/nodes', $nodeMaster);
                $resourcesUsed = json_decode($resourcesUsed);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), $e->getCode());
            }

            try {
                $resourcesTotal = Helper::httpClient('GET', '/api/v1/nodes', $nodeMaster);
                $resourcesTotal = json_decode($resourcesTotal);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), $e->getCode());
            }

            foreach ($resourcesUsed->items as $items) {
                $MEM_USED += $this->convertMemory($items->usage->memory);
                $CPU_USED += $this->convertCPU($items->usage->cpu);
            }

            foreach ($resourcesTotal->items as $items) {
                $MEM_TOTL += $this->convertMemory($items->status->capacity->memory);
                $CPU_TOTL += $items->status->capacity->cpu;
            }

            $MEM_USED = round($MEM_USED * 100 / $MEM_TOTL, 2);
            $CPU_USED = round($CPU_USED * 100 / $CPU_TOTL, 2);
        }

        return json_encode(array('CPU' => $CPU_USED, 'MEM' => $MEM_USED));
    }

    private function convertMemory(String $memory)
    {
        $multiplier = 0;

        if (str_contains($memory, "Ki"))    $multiplier = 0.001024;
        if (str_contains($memory, "Mi"))    $multiplier = 1.048576;
        if (str_contains($memory, "Gi"))    $multiplier = 1073.741824;
        if (str_contains($memory, "Ti"))    $multiplier = 1099511.627776;

        return intval(preg_replace("/[^A-Za-z0-9 ]/", "", $memory)) * $multiplier;
    }

    private function convertCPU(String $cpu)
    {
        return (intval(str_replace("n", "", $cpu)) * 0.000001) / 1000;
    }
}
