<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Node;
use App\Helper\Helper;

class PodController extends Controller
{
    public function getPods(Request $request)
    {

        $pods=[];
        if($request->id == 0){
            $nodeMaster = (new NodeController)->getMasterNodes();

            foreach($nodeMaster as $master){
                if($master->disabled==false){
                    try {
                        array_push($pods,json_decode(Helper::httpClient('GET', '/api/v1/pods', $master)));
                    } catch (\Exception $e) {
                        return response()->json($e->getMessage(), $e->getCode());
                    }
                }
            }
            
        }
        else{

            $nodeMaster = Node::where('id', $request->id)->firstOrFail();

            try {
                $pods = Helper::httpClient('GET', '/api/v1/pods', $nodeMaster);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), $e->getCode());
            }
        }
       

        return $pods;
    }

    public function createPod(Request $request)
    {
        $nodeMaster = Node::where('id', $request->id)->firstOrFail();

        // Body Builder
        $body = array(
            "metadata" => array(
                "name" => $request->name,
            ),
            "spec" => array(
                "containers" => json_decode($request->containers),
            )
        );

        try {
            $pod = Helper::httpClient('POST', '/api/v1/namespaces/'. $request->namespace .'/pods', $nodeMaster, $body);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        return $pod;
    }

    public function deletePod(Request $request)
    {
        $masterNode = Node::where('id', $request->id)->firstOrFail();
        $pod = $request->route('name');
        $namespace = $request->namespace;

        try {
            $response = Helper::httpClient('DELETE', '/api/v1/namespaces/'. $namespace .'/pods/' . $pod, $masterNode);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }
}
