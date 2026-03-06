# HandlesFileStorage Trait Documentation

A centralized trait for managing file storage operations across all controllers.

## Location

```
app/Traits/HandlesFileStorage.php
```

## Usage

Add the trait to any controller that needs file handling:

```php
use App\Traits\HandlesFileStorage;

class YourController extends BaseApiController
{
    use HandlesFileStorage;
    
    // Your controller methods...
}
```

---

## Available Methods

### 1. Upload Single File

Upload a single file to storage.

```php
protected function uploadFile(
    UploadedFile $file,
    int $userId,
    string $type = 'file',
    string $module = 'general'
): string
```

**Parameters:**
- `$file` - The uploaded file object
- `$userId` - The ID of the user uploading the file
- `$type` - File type identifier (e.g., 'image', 'file', 'ticket', 'reply')
- `$module` - Module name (e.g., 'portfolio', 'support-ticket')

**Returns:** String - The stored file path

**Example:**
```php
$path = $this->uploadFile(
    $request->file('image'),
    $user->id,
    'image',
    'portfolio'
);
// Returns: "portfolios/images/portfolio_image_5_20260306120000_abc123_photo.jpg"
```

---

### 2. Upload Multiple Files

Upload multiple files at once.

```php
protected function uploadMultipleFiles(
    array $files,
    int $userId,
    string $type = 'file',
    string $module = 'general'
): array
```

**Parameters:**
- `$files` - Array of UploadedFile objects
- `$userId` - The ID of the user uploading the files
- `$type` - File type identifier
- `$module` - Module name

**Returns:** Array - Array of stored file paths

**Example:**
```php
$imagePaths = $this->uploadMultipleFiles(
    $request->file('images'),
    $user->id,
    'image',
    'portfolio'
);
// Returns: ["portfolios/images/file1.jpg", "portfolios/images/file2.jpg"]
```

---

### 3. Delete Single File

Delete a file from storage.

```php
protected function deleteFile(string $path, string $disk = 'public'): bool
```

**Parameters:**
- `$path` - The file path to delete
- `$disk` - Storage disk name (default: 'public')

**Returns:** Boolean - True if deleted, false otherwise

**Example:**
```php
$deleted = $this->deleteFile('portfolios/images/old_image.jpg');
```

---

### 4. Delete Multiple Files

Delete multiple files at once.

```php
protected function deleteMultipleFiles(array $paths, string $disk = 'public'): array
```

**Parameters:**
- `$paths` - Array of file paths to delete
- `$disk` - Storage disk name (default: 'public')

**Returns:** Array with deletion statistics
```php
[
    'deleted' => 5,
    'failed' => 1,
    'total' => 6
]
```

**Example:**
```php
$result = $this->deleteMultipleFiles([
    'portfolios/images/image1.jpg',
    'portfolios/images/image2.jpg',
    'portfolios/file1.pdf'
]);
```

---

### 5. Get File URL

Get the full public URL for a file.

```php
protected function getFileUrl(string $path, string $disk = 'public'): string
```

**Parameters:**
- `$path` - The file path
- `$disk` - Storage disk name (default: 'public')

**Returns:** String - The full URL

**Example:**
```php
$url = $this->getFileUrl('portfolios/images/photo.jpg');
// Returns: "http://localhost:8000/storage/portfolios/images/photo.jpg"
```

---

### 6. Check File Exists

Check if a file exists in storage.

```php
protected function fileExists(string $path, string $disk = 'public'): bool
```

**Parameters:**
- `$path` - The file path
- `$disk` - Storage disk name (default: 'public')

**Returns:** Boolean - True if exists, false otherwise

**Example:**
```php
if ($this->fileExists('portfolios/images/photo.jpg')) {
    // File exists
}
```

---

### 7. Get File Size

Get the size of a file in bytes.

```php
protected function getFileSize(string $path, string $disk = 'public'): int|false
```

**Parameters:**
- `$path` - The file path
- `$disk` - Storage disk name (default: 'public')

**Returns:** Integer (bytes) or false if file doesn't exist

**Example:**
```php
$size = $this->getFileSize('portfolios/images/photo.jpg');
// Returns: 1024000 (bytes)
```

---

### 8. Get Storage Directory

Get the appropriate storage directory based on module and type.

```php
protected function getStorageDirectory(string $module, string $type): string
```

**Parameters:**
- `$module` - Module name ('portfolio', 'support-ticket', etc.)
- `$type` - File type ('image', 'file', 'ticket', 'reply')

**Returns:** String - The directory path

**Example:**
```php
$dir = $this->getStorageDirectory('portfolio', 'image');
// Returns: "portfolios/images"

$dir = $this->getStorageDirectory('portfolio', 'file');
// Returns: "portfolios"

$dir = $this->getStorageDirectory('support-ticket', 'ticket');
// Returns: "support-tickets"
```

---

### 9. Move File

Move a file to a new location.

```php
protected function moveFile(string $oldPath, string $newPath, string $disk = 'public'): bool
```

**Parameters:**
- `$oldPath` - Current file path
- `$newPath` - New file path
- `$disk` - Storage disk name (default: 'public')

**Returns:** Boolean - True if moved successfully

**Example:**
```php
$moved = $this->moveFile(
    'portfolios/old_image.jpg',
    'portfolios/images/new_image.jpg'
);
```

---

### 10. Copy File

Copy a file to a new location.

```php
protected function copyFile(string $sourcePath, string $destinationPath, string $disk = 'public'): bool
```

**Parameters:**
- `$sourcePath` - Source file path
- `$destinationPath` - Destination file path
- `$disk` - Storage disk name (default: 'public')

**Returns:** Boolean - True if copied successfully

**Example:**
```php
$copied = $this->copyFile(
    'portfolios/images/original.jpg',
    'portfolios/images/backup.jpg'
);
```

---

### 11. Get File Metadata

Get comprehensive metadata about a file.

```php
protected function getFileMetadata(string $path, string $disk = 'public'): ?array
```

**Parameters:**
- `$path` - The file path
- `$disk` - Storage disk name (default: 'public')

**Returns:** Array or null if file doesn't exist
```php
[
    'path' => 'portfolios/images/photo.jpg',
    'url' => 'http://localhost:8000/storage/portfolios/images/photo.jpg',
    'name' => 'photo.jpg',
    'size' => 1024000,
    'mime_type' => 'image/jpeg',
    'last_modified' => 1709726400
]
```

**Example:**
```php
$metadata = $this->getFileMetadata('portfolios/images/photo.jpg');
```

---

### 12. Format File Paths

Format an array of file paths to include URLs and names.

```php
protected function formatFilePaths(?array $paths, string $disk = 'public'): array
```

**Parameters:**
- `$paths` - Array of file paths
- `$disk` - Storage disk name (default: 'public')

**Returns:** Array of formatted file data

**Example:**
```php
$formatted = $this->formatFilePaths([
    'portfolios/images/photo1.jpg',
    'portfolios/images/photo2.jpg'
]);

// Returns:
[
    [
        'path' => 'portfolios/images/photo1.jpg',
        'url' => 'http://localhost:8000/storage/portfolios/images/photo1.jpg',
        'name' => 'photo1.jpg'
    ],
    [
        'path' => 'portfolios/images/photo2.jpg',
        'url' => 'http://localhost:8000/storage/portfolios/images/photo2.jpg',
        'name' => 'photo2.jpg'
    ]
]
```

---

### 13. Cleanup Orphaned Files

Clean up files in storage that are not referenced in the database.

```php
protected function cleanupOrphanedFiles(
    string $directory,
    array $validPaths,
    string $disk = 'public'
): array
```

**Parameters:**
- `$directory` - Directory to clean
- `$validPaths` - Array of valid file paths from database
- `$disk` - Storage disk name (default: 'public')

**Returns:** Array with cleanup statistics

**Example:**
```php
$validPaths = Portfolio::pluck('images')->flatten()->toArray();
$result = $this->cleanupOrphanedFiles('portfolios/images', $validPaths);

// Returns:
[
    'total_files' => 100,
    'valid_files' => 95,
    'orphaned_files' => 5,
    'deleted' => 5
]
```

---

## Storage Directory Structure

The trait automatically determines the correct storage directory:

| Module | Type | Directory |
|--------|------|-----------|
| portfolio | image | `portfolios/images` |
| portfolio | file | `portfolios` |
| support-ticket | ticket | `support-tickets` |
| support-ticket | reply | `support-tickets` |
| other | any | `{module}` |

---

## File Naming Convention

All uploaded files follow this pattern:
```
{module}_{type}_{user_id}_{timestamp}_{random}_{sanitized_name}.{ext}
```

**Examples:**
```
portfolio_image_5_20260306120000_a1b2c3d4e5f6g7h8_screenshot.jpg
portfolio_file_5_20260306120000_a1b2c3d4e5f6g7h8_document.pdf
support-ticket_ticket_5_20260306120000_a1b2c3d4e5f6g7h8_issue.jpg
support-ticket_reply_5_20260306120000_a1b2c3d4e5f6g7h8_solution.pdf
```

---

## Logging

All file operations are automatically logged:

### Upload Log:
```php
[
    'module' => 'portfolio',
    'type' => 'image',
    'user_id' => 5,
    'filename' => 'portfolio_image_5_20260306120000_abc123_photo.jpg',
    'path' => 'portfolios/images/portfolio_image_5_20260306120000_abc123_photo.jpg',
    'size' => 1024000,
    'mime_type' => 'image/jpeg'
]
```

### Delete Log:
```php
[
    'path' => 'portfolios/images/photo.jpg',
    'disk' => 'public'
]
```

### Error Log:
```php
[
    'path' => 'portfolios/images/photo.jpg',
    'disk' => 'public',
    'error' => 'File not found'
]
```

---

## Usage Examples

### Example 1: Portfolio Controller

```php
use App\Traits\HandlesFileStorage;

class PortfolioController extends BaseApiController
{
    use HandlesFileStorage;

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Upload images
        $imagePaths = $this->uploadMultipleFiles(
            $request->file('images'),
            $user->id,
            'image',
            'portfolio'
        );
        
        // Upload documents
        $filePaths = $this->uploadMultipleFiles(
            $request->file('files'),
            $user->id,
            'file',
            'portfolio'
        );
        
        // Create portfolio
        $portfolio = Portfolio::create([
            'images' => $imagePaths,
            'files' => $filePaths,
            // ... other fields
        ]);
        
        return $this->created($portfolio);
    }
    
    public function destroy(Portfolio $portfolio)
    {
        // Delete all files
        if ($portfolio->images) {
            $this->deleteMultipleFiles($portfolio->images);
        }
        
        if ($portfolio->files) {
            $this->deleteMultipleFiles($portfolio->files);
        }
        
        $portfolio->delete();
        
        return $this->noContent();
    }
}
```

### Example 2: Support Ticket Controller

```php
use App\Traits\HandlesFileStorage;

class SupportTicketController extends BaseApiController
{
    use HandlesFileStorage;

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Upload attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->uploadFile(
                $request->file('attachment'),
                $user->id,
                'ticket',
                'support-ticket'
            );
        }
        
        // Create ticket
        $ticket = SupportTicket::create([
            'attachment_path' => $attachmentPath,
            // ... other fields
        ]);
        
        return $this->created($ticket);
    }
    
    public function reply(Request $request, SupportTicket $ticket)
    {
        $user = Auth::user();
        
        // Upload reply attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $this->uploadFile(
                $request->file('attachment'),
                $user->id,
                'reply',
                'support-ticket'
            );
        }
        
        // Create reply
        $reply = SupportTicketReply::create([
            'attachment_path' => $attachmentPath,
            // ... other fields
        ]);
        
        return $this->created($reply);
    }
}
```

### Example 3: Custom Module

```php
use App\Traits\HandlesFileStorage;

class DocumentController extends BaseApiController
{
    use HandlesFileStorage;

    public function uploadDocument(Request $request)
    {
        $user = Auth::user();
        
        // Upload to custom module
        $path = $this->uploadFile(
            $request->file('document'),
            $user->id,
            'document',
            'documents'
        );
        
        // Will be stored in: storage/app/public/documents/
        // Filename: documents_document_5_20260306120000_abc123_file.pdf
        
        return $this->success('Document uploaded', ['path' => $path]);
    }
}
```

---

## Advanced Usage

### Check Before Delete

```php
if ($this->fileExists($path)) {
    $this->deleteFile($path);
}
```

### Get File Info

```php
$metadata = $this->getFileMetadata('portfolios/images/photo.jpg');

if ($metadata) {
    echo "File: {$metadata['name']}\n";
    echo "Size: {$metadata['size']} bytes\n";
    echo "Type: {$metadata['mime_type']}\n";
    echo "URL: {$metadata['url']}\n";
}
```

### Batch Operations

```php
// Upload multiple files
$imagePaths = $this->uploadMultipleFiles(
    $request->file('images'),
    $user->id,
    'image',
    'portfolio'
);

// Delete old files
$this->deleteMultipleFiles($oldImagePaths);

// Format for API response
$formatted = $this->formatFilePaths($imagePaths);
```

### Move Files to New Structure

```php
// Migrate old files to new structure
$oldPath = 'portfolios/old_image.jpg';
$newPath = 'portfolios/images/old_image.jpg';

if ($this->moveFile($oldPath, $newPath)) {
    // Update database
    $portfolio->update(['image_path' => $newPath]);
}
```

---

## Error Handling

All methods include comprehensive error handling:

- **Logging**: All operations are logged (info, warning, error)
- **Graceful Failures**: Methods return false/null on failure instead of throwing exceptions
- **Exception Catching**: Internal exceptions are caught and logged
- **Safe Deletion**: Attempting to delete non-existent files won't cause errors

---

## Benefits

✅ **Centralized Logic** - Single source of truth for file operations
✅ **Consistent Naming** - Uniform file naming across all modules
✅ **Automatic Logging** - All operations logged automatically
✅ **Error Handling** - Built-in error handling and recovery
✅ **Flexible** - Works with any module and file type
✅ **Maintainable** - Easy to update storage logic in one place
✅ **Reusable** - Use in any controller with a simple trait import
✅ **Type Safe** - Proper type hints and return types
✅ **Well Documented** - Comprehensive PHPDoc comments

---

## Migration from Old Code

### Before (Old Code):
```php
class PortfolioController extends BaseApiController
{
    protected function handleFileUpload($file, $userId, $type) {
        // 30 lines of code...
    }
    
    protected function deleteFile($path) {
        // 15 lines of code...
    }
}
```

### After (Using Trait):
```php
use App\Traits\HandlesFileStorage;

class PortfolioController extends BaseApiController
{
    use HandlesFileStorage;
    
    // All file methods available automatically!
    // No duplicate code needed
}
```

---

## Adding New Modules

To add a new module with custom storage:

1. **Update `getStorageDirectory()` method** in the trait:

```php
protected function getStorageDirectory(string $module, string $type): string
{
    return match ($module) {
        'portfolio' => $type === 'image' ? 'portfolios/images' : 'portfolios',
        'support-ticket' => 'support-tickets',
        'invoice' => 'invoices',  // New module
        'avatar' => 'avatars',    // New module
        default => $module,
    };
}
```

2. **Use in your controller**:

```php
$path = $this->uploadFile(
    $request->file('invoice'),
    $user->id,
    'pdf',
    'invoice'
);
// Stored in: storage/app/public/invoices/
```

---

## Best Practices

1. **Always specify module and type** for clarity
2. **Use `uploadMultipleFiles()`** for batch uploads
3. **Use `deleteMultipleFiles()`** for batch deletions
4. **Check `fileExists()`** before operations when needed
5. **Use `getFileMetadata()`** for detailed file info
6. **Leverage automatic logging** - don't add redundant logs
7. **Handle return values** - check boolean returns for success/failure
8. **Use consistent naming** - follow the module/type pattern

---

## Testing

### Unit Test Example:

```php
use App\Traits\HandlesFileStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileStorageTest extends TestCase
{
    use HandlesFileStorage;
    
    public function test_upload_file()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('test.jpg');
        
        $path = $this->uploadFile($file, 1, 'image', 'portfolio');
        
        $this->assertStringContainsString('portfolios/images/', $path);
        Storage::disk('public')->assertExists($path);
    }
    
    public function test_delete_file()
    {
        Storage::fake('public');
        
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $this->uploadFile($file, 1, 'image', 'portfolio');
        
        $this->assertTrue($this->deleteFile($path));
        Storage::disk('public')->assertMissing($path);
    }
}
```

---

## Troubleshooting

### Files not uploading
- Check disk space
- Verify PHP upload limits
- Check directory permissions
- Review Laravel logs

### Files not deleting
- Verify file path is correct
- Check file permissions
- Review error logs

### Wrong directory
- Check module and type parameters
- Verify `getStorageDirectory()` logic
- Review upload logs

---

## Related Documentation

- [Storage Structure](StorageStructure.md) - Complete storage documentation
- [Portfolio API](PortfolioApi.md) - Portfolio API documentation
- [Storage Migration Guide](../STORAGE_MIGRATION_GUIDE.md) - Migration guide
