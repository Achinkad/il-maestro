<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use Illuminate\Http\Request;

use App\Models\Node;
use App\Http\Requests\StoreNodeRequest;
use App\Http\Resources\NodeResource;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;

use ZipArchive;

class NodeController extends Controller
{
    public function getMasterNodes()
    {
        $masterNodes = Node::all();

        $client = new Client(['verify' => false]);

        foreach ($masterNodes as $node) {
            $URL = 'https://' . $node->ip_address . ':' . $node->port;

            $headerOptions = [
                'Authorization' => 'Bearer ' . $node->token,
            ];

            try {
                $response = $client->request('GET', $URL, [
                    'headers' => $headerOptions,
                    'timeout' => 0.8
                ]);

                $node->disabled = false;
            } catch (ConnectException $e) {
                $node->disabled = true;
            }
        }

        return $masterNodes;
    }

    public function registerMasterNode(StoreNodeRequest $request)
    {
        $node = new Node;
        $node->fill($request->validated());
        $node->save();
        $currentUser = auth()->guard('api')->user();
        $currentUser->node()->attach($node->id);
        return new NodeResource($node);
    }

    public function getAllNodes(Request $request)
    {
        $nodeMaster = Node::where('id', $request->id)->firstOrFail();

        try {
            $nodes = Helper::httpClient('GET', 'v1/nodes', $nodeMaster);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        return $nodes;
    }

    public function deleteMasterNode(Request $request)
    {
        return Node::where('id', $request->route('id'))->delete();
    }

    public function downloadScript()
    {
        // Specify the file paths that you want to include in the ZIP
        $filePaths = [
            public_path('storage/bearer-token.sh'),
            public_path('storage/create-secret.yaml')
        ];

        // Create a new ZIP archive
        $zip = new ZipArchive();

        // Create a temporary file to store the ZIP archive
        $zipFilePath = tempnam(sys_get_temp_dir(), 'zip');

        // Open the temporary file for writing
        $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Add each file to the ZIP archive
        foreach ($filePaths as $filePath) {

            // Get the filename from the path
            $fileName = basename($filePath);

            // Add the file to the ZIP archive
            $zip->addFile($filePath, $fileName);
        }

        // Close the ZIP archive
        $zip->close();

        // Set the appropriate headers for the download
        $headers = [
            'Content-Type' => 'application/octet-stream',
            'Content-Disposition' => 'attachment; filename="script.zip"',
        ];

        // Create a response with the ZIP file and headers
        return response()->download($zipFilePath, 'script.zip', $headers)->deleteFileAfterSend(true);
    }
}
