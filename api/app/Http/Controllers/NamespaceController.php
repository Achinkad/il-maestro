<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use Illuminate\Http\Request;

class NamespaceController extends Controller
{
    public function showNamespaces(Request $request)
    {
              
        $master=Router::where('id',$request->identifier)->firstOrFail();
           
        
        try{
            $namespaces = Helper::httpClient('GET','interface',$master);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
                   

        return $namespaces;
        
    }
}
