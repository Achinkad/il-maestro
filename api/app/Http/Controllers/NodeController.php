<?php

namespace App\Http\Controllers;

use App\Helper\Helper;
use Illuminate\Http\Request;

use App\Models\Node;
use App\Http\Requests\StoreNodeRequest;
use App\Http\Resources\NodeResource;

class NodeController extends Controller
{
    public function getMasterNodes()
    {
        return Node::all();
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
}
