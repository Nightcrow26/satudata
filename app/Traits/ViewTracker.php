<?php

namespace App\Traits;

use Illuminate\Support\Facades\Session;

trait ViewTracker
{
    /**
     * Increment view count for this model, but only once per session
     * 
     * @param string|null $sessionKey Custom session key, defaults to model class and ID
     * @return bool Returns true if view was incremented, false if already viewed in this session
     */
    public function incrementViewIfNotSeen($sessionKey = null): bool
    {
        // Generate unique session key for this model instance
        $sessionKey = $sessionKey ?? $this->getViewSessionKey();
        
        // Check if this item has already been viewed in current session
        if (Session::has($sessionKey)) {
            return false; // Already viewed in this session
        }
        
        // Mark as viewed in session
        Session::put($sessionKey, true);
        
        // Increment view count
        $this->incrementViewCount();
        
        return true; // View was incremented
    }
    
    /**
     * Generate session key for view tracking
     * 
     * @return string
     */
    protected function getViewSessionKey(): string
    {
        $modelClass = class_basename($this);
        return "viewed_{$modelClass}_{$this->getKey()}";
    }
    
    /**
     * Increment the view count - can be overridden by models
     * 
     * @return void
     */
    protected function incrementViewCount(): void
    {
        // Default implementation - increment 'view' or 'views' column
        $viewColumn = $this->getViewColumnName();
        
        if ($this->hasViewColumn($viewColumn)) {
            $this->increment($viewColumn);
        }
    }
    
    /**
     * Get the column name used for view counting
     * 
     * @return string
     */
    protected function getViewColumnName(): string
    {
        // Check if model has 'view' column first, then 'views'
        if ($this->hasViewColumn('view')) {
            return 'view';
        }
        
        return 'views';
    }
    
    /**
     * Check if the model has a specific view column
     * 
     * @param string $column
     * @return bool
     */
    protected function hasViewColumn(string $column): bool
    {
        return in_array($column, $this->getFillable()) || 
               array_key_exists($column, $this->getAttributes()) ||
               $this->hasAttributeMutator($column);
    }
    
    /**
     * Check if item was viewed in current session
     * 
     * @param string|null $sessionKey
     * @return bool
     */
    public function wasViewedInSession($sessionKey = null): bool
    {
        $sessionKey = $sessionKey ?? $this->getViewSessionKey();
        return Session::has($sessionKey);
    }
    
    /**
     * Get current view count
     * 
     * @return int
     */
    public function getViewCount(): int
    {
        $viewColumn = $this->getViewColumnName();
        return (int) $this->getAttribute($viewColumn) ?? 0;
    }
    
    /**
     * Reset view session (useful for testing)
     * 
     * @param string|null $sessionKey
     * @return void
     */
    public function resetViewSession($sessionKey = null): void
    {
        $sessionKey = $sessionKey ?? $this->getViewSessionKey();
        Session::forget($sessionKey);
    }
}