# File Storage Architecture

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Controllers Layer                         │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌──────────────────────┐      ┌──────────────────────┐    │
│  │ PortfolioController  │      │ SupportTicketController│   │
│  │                      │      │                        │    │
│  │ use HandlesFileStorage│     │ use HandlesFileStorage│    │
│  └──────────┬───────────┘      └──────────┬───────────┘    │
│             │                               │                 │
│             └───────────┬───────────────────┘                │
│                         │                                     │
└─────────────────────────┼─────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────────┐
│                    Traits Layer                              │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│              ┌─────────────────────────┐                    │
│              │  HandlesFileStorage     │                    │
│              │                         │                    │
│              │  • uploadFile()         │                    │
│              │  • uploadMultipleFiles()│                    │
│              │  • deleteFile()         │                    │
│              │  • deleteMultipleFiles()│                    │
│              │  • getFileUrl()         │                    │
│              │  • fileExists()         │                    │
│              │  • getFileSize()        │                    │
│              │  • getFileMetadata()    │                    │
│              │  • moveFile()           │                    │
│              │  • copyFile()           │                    │
│              │  • formatFilePaths()    │                    │
│              │  • cleanupOrphanedFiles()│                   │
│              │  • getStorageDirectory()│                    │
│              └────────────┬────────────┘                    │
│                           │                                  │
└───────────────────────────┼──────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                  Storage Layer (Laravel)                     │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│              Storage::disk('public')                         │
│                     │                                         │
│                     ▼                                         │
│         storage/app/public/                                  │
│         ├── portfolios/                                      │
│         │   ├── images/                                      │
│         │   │   └── portfolio_image_*.{jpg,png,gif,webp}   │
│         │   └── portfolio_file_*.{pdf,doc,xls,etc}         │
│         │                                                    │
│         └── support-tickets/                                │
│             ├── support-ticket_ticket_*.{jpg,pdf,etc}      │
│             └── support-ticket_reply_*.{jpg,pdf,etc}       │
│                                                               │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                   Public Access Layer                        │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│              public/storage/ (symlink)                       │
│                     │                                         │
│                     ▼                                         │
│         Public URLs:                                         │
│         • /storage/portfolios/images/file.jpg               │
│         • /storage/portfolios/file.pdf                      │
│         • /storage/support-tickets/file.jpg                 │
│                                                               │
└─────────────────────────────────────────────────────────────┘
```

---

## Request Flow

### Upload Flow

```
User Request (with file)
    │
    ▼
Controller (PortfolioController, SupportTicketController, etc.)
    │
    ├─ Validation (via FormRequest)
    │
    ▼
HandlesFileStorage::uploadFile()
    │
    ├─ Generate unique filename
    ├─ Determine storage directory (getStorageDirectory)
    ├─ Store file (Laravel Storage)
    ├─ Log operation
    │
    ▼
Return file path
    │
    ▼
Save to Database (Model)
    │
    ▼
Return Response (via Resource)
    │
    ├─ Include file path
    ├─ Include public URL
    ├─ Include metadata
    │
    ▼
JSON Response to User
```

### Delete Flow

```
User Request (delete portfolio/ticket)
    │
    ▼
Controller
    │
    ├─ Authorization check
    │
    ▼
HandlesFileStorage::deleteMultipleFiles()
    │
    ├─ Loop through files
    ├─ Check if exists
    ├─ Delete from storage
    ├─ Log each operation
    │
    ▼
Delete Database Record
    │
    ▼
Return Response
```

---

## Directory Routing Logic

The trait automatically routes files to the correct directory:

```php
protected function getStorageDirectory(string $module, string $type): string
{
    return match ($module) {
        'portfolio' => $type === 'image' 
            ? 'portfolios/images'      // Portfolio images
            : 'portfolios',             // Portfolio documents
        
        'support-ticket' => 'support-tickets',  // All support ticket files
        
        default => $module,             // Custom modules use module name
    };
}
```

**Examples:**
```php
getStorageDirectory('portfolio', 'image')  → 'portfolios/images'
getStorageDirectory('portfolio', 'file')   → 'portfolios'
getStorageDirectory('support-ticket', 'ticket') → 'support-tickets'
getStorageDirectory('support-ticket', 'reply')  → 'support-tickets'
getStorageDirectory('invoice', 'pdf')      → 'invoice'
```

---

## Method Call Comparison

### Upload Operations

**Before:**
```php
// PortfolioController
$imagePaths = [];
foreach ($request->file('images') as $image) {
    $imagePaths[] = $this->handleFileUpload($image, $user->id, 'image');
}

// SupportTicketController
$attachmentPath = $this->handleFileUpload($request->file('attachment'), $user->id);
```

**After:**
```php
// PortfolioController
$imagePaths = $this->uploadMultipleFiles(
    $request->file('images'),
    $user->id,
    'image',
    'portfolio'
);

// SupportTicketController
$attachmentPath = $this->uploadFile(
    $request->file('attachment'),
    $user->id,
    'ticket',
    'support-ticket'
);
```

### Delete Operations

**Before:**
```php
// PortfolioController
foreach ($portfolio->images ?? [] as $imagePath) {
    $this->deleteFile($imagePath);
}
foreach ($portfolio->files ?? [] as $filePath) {
    $this->deleteFile($filePath);
}

// SupportTicketController
if (Storage::disk('public')->exists($path)) {
    Storage::disk('public')->delete($path);
}
```

**After:**
```php
// PortfolioController
if ($portfolio->images) {
    $this->deleteMultipleFiles($portfolio->images);
}
if ($portfolio->files) {
    $this->deleteMultipleFiles($portfolio->files);
}

// SupportTicketController
$this->deleteFile($path);
```

---

## Logging

All operations are automatically logged with consistent format:

### Upload Log
```php
[2026-03-06 12:00:00] local.INFO: File uploaded
{
    "module": "portfolio",
    "type": "image",
    "user_id": 5,
    "filename": "portfolio_image_5_20260306120000_abc123_photo.jpg",
    "path": "portfolios/images/portfolio_image_5_20260306120000_abc123_photo.jpg",
    "size": 1024000,
    "mime_type": "image/jpeg"
}
```

### Delete Log
```php
[2026-03-06 12:00:00] local.INFO: File deleted
{
    "path": "portfolios/images/photo.jpg",
    "disk": "public"
}
```

### Error Log
```php
[2026-03-06 12:00:00] local.ERROR: Failed to delete file
{
    "path": "portfolios/images/photo.jpg",
    "disk": "public",
    "error": "File not found"
}
```

---

## Benefits

### 1. Code Reusability
- Write once, use everywhere
- No duplicate code across controllers
- Easy to add new controllers with file handling

### 2. Maintainability
- Single source of truth
- Update logic in one place
- Consistent behavior across application

### 3. Consistency
- Uniform file naming
- Consistent directory structure
- Standardized error handling
- Unified logging format

### 4. Features
- 13 utility methods available
- Advanced operations (move, copy, metadata)
- Batch operations support
- Orphaned file cleanup

### 5. Error Handling
- Comprehensive try-catch blocks
- Graceful failure handling
- Detailed error logging
- No exceptions thrown to caller

### 6. Extensibility
- Easy to add new modules
- Simple to add new features
- Supports custom storage disks
- Flexible directory routing

---

## Adding New Controllers

To add file handling to a new controller:

### Step 1: Import the Trait
```php
use App\Traits\HandlesFileStorage;

class InvoiceController extends BaseApiController
{
    use HandlesFileStorage;
}
```

### Step 2: Use the Methods
```php
public function store(Request $request)
{
    $user = Auth::user();
    
    // Upload invoice PDF
    $pdfPath = $this->uploadFile(
        $request->file('invoice'),
        $user->id,
        'pdf',
        'invoice'
    );
    
    // Create invoice record
    $invoice = Invoice::create([
        'pdf_path' => $pdfPath,
        // ... other fields
    ]);
    
    return $this->created($invoice);
}

public function destroy(Invoice $invoice)
{
    // Delete file
    $this->deleteFile($invoice->pdf_path);
    
    // Delete record
    $invoice->delete();
    
    return $this->noContent();
}
```

### Step 3: (Optional) Add Custom Directory
```php
// In HandlesFileStorage trait
protected function getStorageDirectory(string $module, string $type): string
{
    return match ($module) {
        'portfolio' => $type === 'image' ? 'portfolios/images' : 'portfolios',
        'support-ticket' => 'support-tickets',
        'invoice' => 'invoices',  // Add your module here
        default => $module,
    };
}
```

Done! Your new controller now has full file handling capabilities.

---

## Testing

### Unit Test Example
```php
use App\Traits\HandlesFileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStorageTest extends TestCase
{
    use HandlesFileStorage;
    
    public function test_upload_and_delete_file()
    {
        Storage::fake('public');
        
        // Upload
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $this->uploadFile($file, 1, 'image', 'portfolio');
        
        // Verify upload
        $this->assertStringContainsString('portfolios/images/', $path);
        Storage::disk('public')->assertExists($path);
        
        // Delete
        $deleted = $this->deleteFile($path);
        
        // Verify deletion
        $this->assertTrue($deleted);
        Storage::disk('public')->assertMissing($path);
    }
    
    public function test_upload_multiple_files()
    {
        Storage::fake('public');
        
        $files = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
            UploadedFile::fake()->image('test3.jpg'),
        ];
        
        $paths = $this->uploadMultipleFiles($files, 1, 'image', 'portfolio');
        
        $this->assertCount(3, $paths);
        foreach ($paths as $path) {
            Storage::disk('public')->assertExists($path);
        }
    }
    
    public function test_delete_multiple_files()
    {
        Storage::fake('public');
        
        // Upload files
        $files = [
            UploadedFile::fake()->image('test1.jpg'),
            UploadedFile::fake()->image('test2.jpg'),
        ];
        
        $paths = $this->uploadMultipleFiles($files, 1, 'image', 'portfolio');
        
        // Delete files
        $result = $this->deleteMultipleFiles($paths);
        
        $this->assertEquals(2, $result['deleted']);
        $this->assertEquals(0, $result['failed']);
        $this->assertEquals(2, $result['total']);
    }
}
```

---

## Performance Considerations

### Batch Operations
The trait provides batch methods that are more efficient than loops:

**Inefficient:**
```php
foreach ($paths as $path) {
    $this->deleteFile($path);
}
```

**Efficient:**
```php
$result = $this->deleteMultipleFiles($paths);
// Returns statistics: deleted, failed, total
```

### File Existence Checks
Use `fileExists()` to avoid unnecessary operations:

```php
if ($this->fileExists($oldPath)) {
    $this->deleteFile($oldPath);
}
```

### Metadata Caching
Cache file metadata when needed multiple times:

```php
$metadata = $this->getFileMetadata($path);
$url = $metadata['url'];
$size = $metadata['size'];
$mimeType = $metadata['mime_type'];
```

---

## Security Features

### 1. Filename Sanitization
```php
$sanitizedName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
```
- Removes special characters
- Prevents directory traversal
- Ensures filesystem compatibility

### 2. Unique Filenames
```php
{module}_{type}_{user_id}_{timestamp}_{random}_{name}.{ext}
```
- Timestamp prevents collisions
- Random string adds entropy
- User ID for tracking
- Prevents overwriting

### 3. Directory Isolation
- Each module has its own directory
- Files organized by type
- Prevents cross-module access issues

### 4. Logging
- All operations logged
- Audit trail for compliance
- Error tracking
- User attribution

---

## Error Handling Strategy

### Graceful Failures
Methods return false/null instead of throwing exceptions:

```php
// Delete returns boolean
if ($this->deleteFile($path)) {
    // Success
} else {
    // Failed - logged automatically
}

// Metadata returns null if file doesn't exist
$metadata = $this->getFileMetadata($path);
if ($metadata) {
    // File exists, use metadata
} else {
    // File doesn't exist
}
```

### Automatic Logging
All errors are automatically logged:

```php
try {
    // Operation
} catch (Exception $e) {
    Log::error('Failed to ...', [
        'path' => $path,
        'error' => $e->getMessage()
    ]);
    return false;
}
```

### Safe Operations
- Check existence before operations
- Handle missing files gracefully
- No exceptions propagated to caller
- Detailed error context in logs

---

## Extending the Trait

### Adding New Methods

```php
/**
 * Resize an image.
 */
protected function resizeImage(string $path, int $width, int $height): bool
{
    try {
        // Image resize logic
        Log::info('Image resized', ['path' => $path]);
        return true;
    } catch (Exception $e) {
        Log::error('Failed to resize image', [
            'path' => $path,
            'error' => $e->getMessage()
        ]);
        return false;
    }
}
```

### Adding New Storage Backends

```php
/**
 * Upload to S3.
 */
protected function uploadToS3(UploadedFile $file, string $path): string
{
    return $file->storeAs($path, $filename, 's3');
}
```

### Adding File Processing

```php
/**
 * Generate thumbnail.
 */
protected function generateThumbnail(string $imagePath): ?string
{
    try {
        // Thumbnail generation logic
        $thumbnailPath = str_replace('.jpg', '_thumb.jpg', $imagePath);
        // ... processing
        return $thumbnailPath;
    } catch (Exception $e) {
        Log::error('Failed to generate thumbnail', [
            'path' => $imagePath,
            'error' => $e->getMessage()
        ]);
        return null;
    }
}
```

---

## Migration from Old Code

### Controllers Already Migrated:
✅ PortfolioController
✅ SupportTicketController

### To Migrate Other Controllers:

1. **Add trait import:**
```php
use App\Traits\HandlesFileStorage;
```

2. **Add trait to class:**
```php
class YourController extends BaseApiController
{
    use HandlesFileStorage;
}
```

3. **Replace old methods:**
```php
// OLD
$path = $this->handleFileUpload($file, $userId);

// NEW
$path = $this->uploadFile($file, $userId, 'type', 'module');
```

4. **Remove duplicate methods:**
```php
// DELETE these from your controller
protected function handleFileUpload() { ... }
protected function deleteFile() { ... }
```

---

## Best Practices

### ✅ DO:
- Always specify module and type for clarity
- Use batch methods for multiple files
- Check return values for success/failure
- Let the trait handle logging
- Use descriptive type names
- Follow the naming conventions

### ❌ DON'T:
- Don't create custom file handling methods
- Don't bypass the trait for file operations
- Don't add redundant logging
- Don't hardcode directory paths
- Don't ignore return values
- Don't throw exceptions from file operations

---

## Monitoring

### Check Storage Usage
```bash
# Total storage
du -sh storage/app/public/

# By module
du -sh storage/app/public/portfolios/
du -sh storage/app/public/portfolios/images/
du -sh storage/app/public/support-tickets/
```

### Check Logs
```bash
# View file operation logs
tail -f storage/logs/laravel.log | grep "File uploaded\|File deleted"

# View errors only
tail -f storage/logs/laravel.log | grep "Failed to"
```

### Database vs Storage Audit
```php
// Find orphaned files
$validPaths = Portfolio::pluck('images')->flatten()->toArray();
$result = $this->cleanupOrphanedFiles('portfolios/images', $validPaths);
```

---

## Future Enhancements

Potential features to add to the trait:

- [ ] Image resizing/thumbnails
- [ ] Image optimization (compress)
- [ ] Video processing
- [ ] File compression (zip)
- [ ] Cloud storage (S3, DigitalOcean Spaces)
- [ ] CDN integration
- [ ] Virus scanning
- [ ] File versioning
- [ ] Automatic backups
- [ ] File encryption
- [ ] Watermarking
- [ ] Format conversion
- [ ] Temporary file handling
- [ ] Chunked uploads for large files

All these features can be added to the trait and will be available to all controllers automatically!

---

## Summary

The `HandlesFileStorage` trait provides:

✅ **13 utility methods** for file operations
✅ **Automatic logging** of all operations
✅ **Consistent error handling** across controllers
✅ **Dynamic directory routing** based on module/type
✅ **Batch operations** for efficiency
✅ **Comprehensive metadata** access
✅ **Safe operations** with graceful failures
✅ **Zero code duplication** across controllers
✅ **Easy extensibility** for new features
✅ **Production-ready** with proper logging and error handling

**Result:** Cleaner, more maintainable, and more powerful file handling! 🚀
