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

use Illuminate\Support\Facades\Storage;

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

        $nodes=[];
        if($request->id == 0){
            $nodeMaster = (new NodeController)->getMasterNodes();

            foreach($nodeMaster as $master){
                if($master->disabled==false){
                    try {
                        array_push($nodes,json_decode(Helper::httpClient('GET', '/api/v1/nodes', $master)));
                    } catch (\Exception $e) {
                        return response()->json($e->getMessage(), $e->getCode());
                    }
                }
            }
        }
        else{
            $nodeMaster = Node::where('id', $request->id)->firstOrFail();

            try {
                $nodes = Helper::httpClient('GET', '/api/v1/nodes', $nodeMaster);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), $e->getCode());
            }

        }

        /*
        foreach($nodesAux as $node){
            array_push($nodes,json_decode($node));
        }*/

        return $nodes;
    }

    public function deleteMasterNode(Request $request)
    {
        return Node::where('id', $request->route('id'))->delete();
    }

    public function downloadScript()
    {
        return Storage::download('public/maestro.sh');
    }
}
