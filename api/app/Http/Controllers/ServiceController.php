<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Node;
use App\Helper\Helper;

class ServiceController extends Controller
{
    public function getServices(Request $request)
    {
        $nodeMaster = Node::where('id', $request->id)->firstOrFail();

        try {
            $services = Helper::httpClient('GET', '/api/v1/services', $nodeMaster);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        return $services;
    }

    public function createService(Request $request)
    {
        $nodeMaster = Node::where('id', $request->id)->firstOrFail();

        // Body Builder
        $body = array(
            "metadata" => array(
                "name" => $request->name,
            ),
            "spec" => array(
                "ports" => json_decode($request->ports),
            )
        );

        try {
            $service = Helper::httpClient('POST', '/api/v1/namespaces/'. $request->namespace .'/services', $nodeMaster, $body);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        return $service;
    }

    public function deleteService(Request $request)
    {
        $masterNode = Node::where('id', $request->id)->firstOrFail();
        $service = $request->route('name');
        $namespace = $request->namespace;

        try {
            $response = Helper::httpClient('DELETE', '/api/v1/namespaces/'. $namespace .'/services/' . $service, $masterNode);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }
}
