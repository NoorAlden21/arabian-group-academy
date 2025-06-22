<?php

namespace App\Http\Controllers;

use App\Http\Resources\ParentBasicResource;
use App\Http\Resources\ParentFullInfoResource;
use App\Services\ParentService;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    protected $parentService;
    public function __construct(ParentService $parentService){
        $this->parentService = $parentService;
    }

    public function index(){
        try{
            $parents = $this->parentService->getAllParents();
    
            return response()->json([
                'parents' => ParentBasicResource::collection($parents)
            ],200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Failed to fetch the parents',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function show($id)
    {
        try {
            $parent = $this->parentService->getParentById($id);

            if (!$parent || !$parent->parentProfile) {
                return response()->json([
                    'message' => 'Parent not found or profile missing.'
                ], 404);
            }

            $parentProfile = $parent->parentProfile->load('user', 'children.user');

            return response()->json([
                'parent' => new ParentFullInfoResource($parentProfile)
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch parent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
