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
}
