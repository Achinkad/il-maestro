<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NamespaceController extends Controller
{
    public function showNamespaces(Request $request)
    {
              
        //$router=Router::where('id',$request->identifier)->firstOrFail();
           
        
        try{
            $namespaces = Helper::httpClient('GET','interface',$router);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
                                   

        return $namespaces;
        
    }
}
