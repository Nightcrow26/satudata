<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ViewTrackerController extends Controller
{
    /**
     * Track view for any model via AJAX
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trackView(Request $request)
    {
        $request->validate([
            'model' => 'required|string|in:Dataset,Publikasi,Indikator',
            'id' => 'required|string'
        ]);

        $modelClass = "App\\Models\\{$request->model}";
        
        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $model = $modelClass::find($request->id);
        
        if (!$model) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        // Check if model uses ViewTracker trait
        if (!method_exists($model, 'incrementViewIfNotSeen')) {
            return response()->json(['error' => 'Model does not support view tracking'], 400);
        }

        $wasIncremented = $model->incrementViewIfNotSeen();
        
        return response()->json([
            'success' => true,
            'view_incremented' => $wasIncremented,
            'current_views' => $model->getViewCount(),
            'message' => $wasIncremented ? 'View counted' : 'Already viewed in this session'
        ]);
    }

    /**
     * Get current view count for a model
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViewCount(Request $request)
    {
        $request->validate([
            'model' => 'required|string|in:Dataset,Publikasi,Indikator',
            'id' => 'required|string'
        ]);

        $modelClass = "App\\Models\\{$request->model}";
        
        if (!class_exists($modelClass)) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $model = $modelClass::find($request->id);
        
        if (!$model) {
            return response()->json(['error' => 'Item not found'], 404);
        }

        if (!method_exists($model, 'getViewCount')) {
            return response()->json(['error' => 'Model does not support view tracking'], 400);
        }

        return response()->json([
            'success' => true,
            'views' => $model->getViewCount(),
            'viewed_in_session' => $model->wasViewedInSession()
        ]);
    }
}