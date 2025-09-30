<?php

namespace App\Traits;

use Illuminate\Support\Facades\Session;

trait DownloadTracker
{
    /**
     * Increment download count for this model, but only once per session
     * 
     * @param string|null $sessionKey Custom session key, defaults to model class and ID
     * @return bool Returns true if download was incremented, false if already downloaded in this session
     */
    public function incrementDownloadIfNotSeen($sessionKey = null): bool
    {
        // Generate unique session key for this model instance
        $sessionKey = $sessionKey ?? $this->getDownloadSessionKey();
        
        // Check if this item has already been downloaded in current session
        if (Session::has($sessionKey)) {
            return false; // Already downloaded in this session
        }
        
        // Mark as downloaded in session
        Session::put($sessionKey, true);
        
        // Increment download count
        $this->incrementDownloadCount();
        
        return true; // Download was incremented
    }
    
    /**
     * Generate session key for download tracking
     * 
     * @return string
     */
    protected function getDownloadSessionKey(): string
    {
        $modelClass = class_basename($this);
        return "downloaded_{$modelClass}_{$this->getKey()}";
    }
    
    /**
     * Increment the download count - can be overridden by models
     * 
     * @return void
     */
    protected function incrementDownloadCount(): void
    {
        // Default implementation - increment 'download' or 'downloads' column
        $downloadColumn = $this->getDownloadColumnName();
        
        if ($this->hasDownloadColumn($downloadColumn)) {
            $this->increment($downloadColumn);
        }
    }
    
    /**
     * Get the column name used for download counting
     * 
     * @return string
     */
    protected function getDownloadColumnName(): string
    {
        // Check if model has 'download' column first, then 'downloads'
        if ($this->hasDownloadColumn('download')) {
            return 'download';
        }
        
        return 'downloads';
    }
    
    /**
     * Check if the model has a specific download column
     * 
     * @param string $column
     * @return bool
     */
    protected function hasDownloadColumn(string $column): bool
    {
        return in_array($column, $this->getFillable()) || 
               array_key_exists($column, $this->getAttributes()) ||
               $this->hasAttributeMutator($column);
    }
    
    /**
     * Check if item was downloaded in current session
     * 
     * @param string|null $sessionKey
     * @return bool
     */
    public function wasDownloadedInSession($sessionKey = null): bool
    {
        $sessionKey = $sessionKey ?? $this->getDownloadSessionKey();
        return Session::has($sessionKey);
    }
    
    /**
     * Get current download count
     * 
     * @return int
     */
    public function getDownloadCount(): int
    {
        $downloadColumn = $this->getDownloadColumnName();
        return (int) $this->getAttribute($downloadColumn) ?? 0;
    }
    
    /**
     * Reset download session (useful for testing)
     * 
     * @param string|null $sessionKey
     * @return void
     */
    public function resetDownloadSession($sessionKey = null): void
    {
        $sessionKey = $sessionKey ?? $this->getDownloadSessionKey();
        Session::forget($sessionKey);
    }
}