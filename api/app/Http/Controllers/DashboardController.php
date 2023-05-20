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
}
