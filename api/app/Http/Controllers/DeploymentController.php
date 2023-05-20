<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Helper\Helper;
use Illuminate\Http\Request;
use App\Http\Controllers\NodeController;

class DeploymentController extends Controller
{
    public function getDeployments(Request $request)
    {
        $deployments= [];

        if($request->id == 0){
            $nodeMaster = (new NodeController)->getMasterNodes();

            
            foreach($nodeMaster as $master){
                if($master->disabled==false){
                    try {
                        array_push($deployments,json_decode(Helper::httpClient('GET', '/apis/apps/v1/deployments', $master)));
                    } catch (\Exception $e) {
                        return response()->json($e->getMessage(), $e->getCode());
                    }
                }
                
            }

        }
        else{
            $nodeMaster = Node::where('id', $request->id)->firstOrFail();

            try {
                $deployments = Helper::httpClient('GET', '/apis/apps/v1/deployments', $nodeMaster);
                
            } catch (\Exception $e) {
                
                return response()->json($e->getMessage(), $e->getCode());
            }
        }

        return $deployments;
        
    }

    

    public function registerDeployment(Request $request)
    {

        $keyLabel=$request->keys()[2];

        $nodeMaster = Node::where('id', $request->id)->firstOrFail();
        
        // Body Builder
        $body = array(
            "metadata" => array(
                "name" => $request->name
            ),
            "spec" => array(
                "selector" => array(
                    "matchLabels" => array(
                        $keyLabel => $request->$keyLabel
                    )
                ),
                "template" => array(
                    "metadata" => array(
                        "labels" => array(
                            $keyLabel => $request->$keyLabel
                        )
                        
                    ),
                    "spec" => array(
                        "containers" => json_decode($request->containers)
                    )
                ),
               
            )
        );
        
        try {
            $deployments = Helper::httpClient('POST', '/apis/apps/v1/namespaces/'.$request->namespace.'/deployments', $nodeMaster,$body);
            
        } catch (\Exception $e) {
 
            return response()->json($e->getMessage(), $e->getCode());
        }

        return $deployments;
        
    }

    public function deleteDeployment(Request $request) 
    {

        $nodeMaster = Node::where('id', $request->id)->firstOrFail();
       
        try{
            Helper::httpClient('DELETE','/apis/apps/v1/namespaces/'.$request->all()['deployment']['metadata']['namespace'].'/deployments/'.$request->all()['deployment']['metadata']['name'],$nodeMaster);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
        
    }
}
