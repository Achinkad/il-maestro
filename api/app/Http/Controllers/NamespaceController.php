<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Helper\Helper;
use Illuminate\Http\Request;

class NamespaceController extends Controller
{
    public function getNamespaces(Request $request)
    {

        $nodeMaster = Node::where('id', $request->id)->firstOrFail();
        
        try {
            $namespaces = Helper::httpClient('GET', 'v1/namespaces', $nodeMaster);
            
        } catch (\Exception $e) {
            
            return response()->json($e->getMessage(), $e->getCode());
        }

        return $namespaces;
        
    }

    

    public function registerNamespace(Request $request)
    {

        $nodeMaster = Node::where('id', $request->masterID)->firstOrFail();
        unset($request['masterID']);

        $data['metadata']=$request->all();
        
        try {
            $namespaces = Helper::httpClient('POST', 'v1/namespaces', $nodeMaster,$data);
            
        } catch (\Exception $e) {


            
            return response()->json($e->getMessage(), $e->getCode());
        }

        return $namespaces;
        
    }

    public function deleteNamespace(Request $request) 
    {

        $nodeMaster = Node::where('id', $request->id)->firstOrFail();

        try{
            Helper::httpClient('DELETE','v1/namespaces/'.$request->all()['namespace']['metadata']['name'],$nodeMaster);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

    }


}
