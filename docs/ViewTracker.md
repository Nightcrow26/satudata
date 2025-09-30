# ViewTracker Trait Documentation

## Overview
The `ViewTracker` trait provides session-based view counting functionality for Laravel models. It prevents duplicate view counting from the same user session, ensuring accurate view statistics.

## Features
- ✅ Session-based duplicate prevention
- ✅ Automatic view column detection (`view` or `views`)
- ✅ Customizable session keys
- ✅ View count retrieval
- ✅ Session reset functionality
- ✅ Support for multiple models

## Installation

### 1. Add Trait to Model
```php
<?php

namespace App\Models;

use App\Traits\ViewTracker;
use Illuminate\Database\Eloquent\Model;

class Dataset extends Model
{
    use ViewTracker;
    
    protected $fillable = [
        // ... other fields
        'view', // or 'views'
    ];
    
    protected $casts = [
        'view' => 'integer',
    ];
}
```

### 2. Database Requirements
Ensure your model's table has a view counting column:
```sql
-- For 'view' column
ALTER TABLE datasets ADD COLUMN view INT DEFAULT 0;

-- OR for 'views' column  
ALTER TABLE datasets ADD COLUMN views INT DEFAULT 0;
```

## Usage

### Basic View Tracking
```php
// In your controller or Livewire component
$dataset = Dataset::find($id);

// Increment view count (only once per session)
$wasIncremented = $dataset->incrementViewIfNotSeen();

if ($wasIncremented) {
    // View was counted
    echo "New view recorded!";
} else {
    // Already viewed in this session
    echo "Already viewed in this session";
}
```

### Get View Count
```php
$dataset = Dataset::find($id);
$totalViews = $dataset->getViewCount(); // Returns integer
```

### Check if Viewed in Current Session
```php
$dataset = Dataset::find($id);
$wasViewed = $dataset->wasViewedInSession(); // Returns boolean
```

### Custom Session Keys
```php
$dataset = Dataset::find($id);
$customKey = "special_view_dataset_{$id}";
$wasIncremented = $dataset->incrementViewIfNotSeen($customKey);
```

### Reset Session (Useful for Testing)
```php
$dataset = Dataset::find($id);
$dataset->resetViewSession(); // Now can be viewed again
```

## Integration Examples

### Livewire Component
```php
<?php

namespace App\Livewire\Public\Data;

use Livewire\Component;
use App\Models\Dataset;

class DetailContent extends Component
{
    public Dataset $dataset;
    
    public function mount($datasetId)
    {
        $this->dataset = Dataset::findOrFail($datasetId);
        
        // Track view automatically when component loads
        $this->dataset->incrementViewIfNotSeen();
    }
    
    public function render()
    {
        return view('livewire.public.data.detail-content');
    }
}
```

### Controller
```php
<?php

namespace App\Http\Controllers;

use App\Models\Dataset;

class DataController extends Controller
{
    public function show($id)
    {
        $dataset = Dataset::findOrFail($id);
        
        // Track view
        $dataset->incrementViewIfNotSeen();
        
        return view('data.show', compact('dataset'));
    }
}
```

### AJAX View Tracking
```php
// Route in web.php
Route::post('/track-view', [ViewTrackerController::class, 'trackView']);

// JavaScript
fetch('/track-view', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        model: 'Dataset',
        id: datasetId
    })
})
.then(response => response.json())
.then(data => {
    if (data.view_incremented) {
        console.log('View counted!', data.current_views);
    } else {
        console.log('Already viewed in session');
    }
});
```

## Configuration

### Column Name Detection
The trait automatically detects view columns in this order:
1. `view` (if exists in fillable or attributes)
2. `views` (fallback)

### Custom Column Override
```php
class Dataset extends Model
{
    use ViewTracker;
    
    protected function getViewColumnName(): string
    {
        return 'custom_view_column';
    }
}
```

## Session Keys
Session keys are automatically generated as:
```
viewed_{ModelClass}_{ModelId}
```

Examples:
- `viewed_Dataset_123`
- `viewed_Publikasi_456`
- `viewed_Article_789`

## Testing
Run the included test command:
```bash
php artisan test:view-tracker
```

This will test:
- ✅ View increment on first access
- ✅ Duplicate prevention on subsequent access
- ✅ Session tracking functionality
- ✅ Session reset capability

## Multiple Models
The trait can be used on multiple models:

```php
// Dataset model
class Dataset extends Model
{
    use ViewTracker;
    protected $fillable = ['view'];
}

// Publikasi model  
class Publikasi extends Model
{
    use ViewTracker;
    protected $fillable = ['view'];
}

// Usage
$dataset = Dataset::find(1);
$publikasi = Publikasi::find(1);

$dataset->incrementViewIfNotSeen();    // tracked separately
$publikasi->incrementViewIfNotSeen();  // tracked separately
```

## Security Considerations
- Session-based tracking prevents basic view inflation
- Not immune to cookie clearing or different browsers
- For more robust tracking, consider adding IP-based or user-based tracking
- Consider rate limiting for view tracking endpoints

## Performance Notes
- Minimal database impact (single UPDATE query per unique view)
- Session storage is lightweight
- No additional database tables required
- Automatic cleanup when session expires