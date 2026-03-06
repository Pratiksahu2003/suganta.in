# Support Ticket Storage Setup Guide

## Quick Setup (Run These Commands)

### For Development (Windows)
```bash
# 1. Create storage symlink
php artisan storage:link

# 2. Create support-tickets directory
mkdir storage\app\public\support-tickets

# 3. Verify setup
dir storage\app\public\support-tickets
dir public\storage
```

### For Development (Linux/Mac)
```bash
# 1. Create storage symlink
php artisan storage:link

# 2. Create support-tickets directory
mkdir -p storage/app/public/support-tickets

# 3. Set permissions
chmod -R 775 storage
chmod -R 775 public/storage

# 4. Verify setup
ls -la storage/app/public/support-tickets
ls -la public/storage
```

### For Production (Linux/Mac)
```bash
# 1. Create storage symlink
php artisan storage:link

# 2. Create support-tickets directory
mkdir -p storage/app/public/support-tickets

# 3. Set proper ownership (replace www-data with your web server user)
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data public/storage

# 4. Set permissions
chmod -R 775 storage
chmod -R 775 public/storage

# 5. Verify setup
ls -la storage/app/public/support-tickets
ls -la public/storage
```

---

## Verification Checklist

### ✅ Check 1: Symlink Exists
```bash
# Windows
dir public\storage

# Linux/Mac
ls -la public/storage
```

**Expected output**: Should show a symbolic link pointing to `../storage/app/public`

### ✅ Check 2: Directory Exists
```bash
# Windows
dir storage\app\public\support-tickets

# Linux/Mac
ls -la storage/app/public/support-tickets
```

**Expected output**: Directory should exist and be writable

### ✅ Check 3: Test File Upload
```bash
# Create a test file
echo "test" > test.txt

# Upload via API
curl -X POST http://localhost:8000/api/v1/support-tickets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "subject=Test Upload" \
  -F "message=Testing file upload" \
  -F "priority=low" \
  -F "category=technical" \
  -F "attachment=@test.txt"
```

### ✅ Check 4: Verify File Saved
```bash
# Windows
dir storage\app\public\support-tickets

# Linux/Mac
ls -la storage/app/public/support-tickets
```

**Expected output**: Should see uploaded file with pattern `ticket_*_*.txt`

### ✅ Check 5: Test File Download
```bash
curl -X GET http://localhost:8000/api/v1/support-tickets/1/attachment \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o downloaded.txt

# Verify downloaded file
cat downloaded.txt
```

---

## Troubleshooting

### Problem: "Symlink not created"

**Windows Solution:**
```bash
# Run as Administrator
# Delete existing if any
rmdir public\storage

# Create symlink
mklink /D public\storage ..\storage\app\public

# Or use Laravel command
php artisan storage:link
```

**Linux/Mac Solution:**
```bash
# Remove existing symlink
rm public/storage

# Create new symlink
ln -s ../storage/app/public public/storage

# Or use Laravel command
php artisan storage:link
```

### Problem: "Permission denied"

**Solution:**
```bash
# Linux/Mac
sudo chown -R $USER:$USER storage
sudo chown -R $USER:$USER public/storage
chmod -R 775 storage
chmod -R 775 public/storage

# Or set web server user
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data public/storage
```

### Problem: "File not found" when downloading

**Possible Causes:**
1. Symlink not created → Run `php artisan storage:link`
2. File doesn't exist → Check `storage/app/public/support-tickets/`
3. Wrong path in database → Check `attachment_path` column
4. Permission issue → Check file permissions

**Debug:**
```php
// Add to controller temporarily
Log::info('File path', [
    'attachment_path' => $supportTicket->attachment_path,
    'full_path' => storage_path('app/public/' . $supportTicket->attachment_path),
    'exists' => file_exists(storage_path('app/public/' . $supportTicket->attachment_path))
]);
```

### Problem: "File too large"

**Solution 1: Update PHP Configuration**
```ini
# php.ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
memory_limit = 256M
```

**Solution 2: Update Nginx Configuration**
```nginx
# nginx.conf
client_max_body_size 10M;
```

**Solution 3: Update Apache Configuration**
```apache
# .htaccess or httpd.conf
php_value upload_max_filesize 10M
php_value post_max_size 12M
```

### Problem: "Disk space full"

**Check disk usage:**
```bash
# Linux/Mac
du -sh storage/app/public/support-tickets
df -h

# Windows
dir storage\app\public\support-tickets
```

**Clean up old files:**
```bash
# Delete files older than 90 days
find storage/app/public/support-tickets -type f -mtime +90 -delete
```

---

## Maintenance

### Regular Cleanup Script
```php
// app/Console/Commands/CleanupOldTicketAttachments.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SupportTicket;
use Illuminate\Support\Facades\Storage;

class CleanupOldTicketAttachments extends Command
{
    protected $signature = 'tickets:cleanup-attachments {--days=90}';
    protected $description = 'Clean up attachments from resolved tickets older than X days';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = now()->subDays($days);
        
        $tickets = SupportTicket::onlyTrashed()
            ->where('deleted_at', '<', $cutoffDate)
            ->whereNotNull('attachment_path')
            ->get();
        
        $deletedCount = 0;
        foreach ($tickets as $ticket) {
            if (Storage::disk('public')->exists($ticket->attachment_path)) {
                Storage::disk('public')->delete($ticket->attachment_path);
                $deletedCount++;
            }
        }
        
        $this->info("Cleaned up {$deletedCount} old attachments");
    }
}
```

### Schedule Cleanup (app/Console/Kernel.php)
```php
protected function schedule(Schedule $schedule)
{
    // Clean up old attachments every week
    $schedule->command('tickets:cleanup-attachments --days=90')->weekly();
}
```

---

## Storage Monitoring

### Check Storage Usage
```bash
# Linux/Mac
du -sh storage/app/public/support-tickets

# Windows
dir /s storage\app\public\support-tickets
```

### Monitor File Count
```bash
# Linux/Mac
find storage/app/public/support-tickets -type f | wc -l

# Windows
dir /s /b storage\app\public\support-tickets\*.* | find /c ":"
```

### Database Query for Storage Stats
```sql
-- Total attachments
SELECT COUNT(*) FROM support_tickets WHERE attachment_path IS NOT NULL;

-- Attachments by month
SELECT 
    DATE_FORMAT(created_at, '%Y-%m') as month,
    COUNT(*) as total_attachments
FROM support_tickets 
WHERE attachment_path IS NOT NULL
GROUP BY month
ORDER BY month DESC;
```

---

## Migration to Cloud Storage (Optional)

### AWS S3 Configuration
```env
# .env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket
AWS_URL=https://your-bucket.s3.amazonaws.com
```

### Update Controller
```php
// Change from 'public' to 's3'
$path = $file->storeAs('support-tickets', $filename, 's3');
```

---

## Performance Optimization

### 1. Lazy Load Attachments
```php
// Don't load attachment data unless needed
$tickets = SupportTicket::select(['id', 'subject', 'status', 'priority'])
    ->without(['attachment_path'])
    ->get();
```

### 2. Use CDN for File Delivery
```php
// config/filesystems.php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('CDN_URL', env('APP_URL').'/storage'),
    'visibility' => 'public',
],
```

### 3. Implement Caching
```php
// Cache file metadata
Cache::remember("ticket_{$id}_attachment", 3600, function() use ($ticket) {
    return [
        'filename' => $ticket->getAttachmentFilename(),
        'size' => $ticket->getFormattedAttachmentSize(),
        'url' => $ticket->getAttachmentUrl()
    ];
});
```

---

## Backup Strategy

### Daily Backup Script
```bash
#!/bin/bash
# backup-support-tickets.sh

DATE=$(date +%Y%m%d)
BACKUP_DIR="/backups/support-tickets"
SOURCE_DIR="storage/app/public/support-tickets"

# Create backup directory
mkdir -p $BACKUP_DIR

# Create compressed archive
tar -czf $BACKUP_DIR/support-tickets-$DATE.tar.gz $SOURCE_DIR

# Keep only last 30 days
find $BACKUP_DIR -name "support-tickets-*.tar.gz" -mtime +30 -delete

echo "Backup completed: support-tickets-$DATE.tar.gz"
```

### Schedule Backup
```bash
# Add to crontab
0 2 * * * /path/to/backup-support-tickets.sh
```

---

## Summary

✅ **Storage Location**: `storage/app/public/support-tickets/`  
✅ **Public Access**: Via `/storage/support-tickets/{filename}`  
✅ **Max File Size**: 10MB  
✅ **Allowed Types**: Images, PDFs, Documents, Archives  
✅ **Security**: Permission checks, sanitized filenames  
✅ **Logging**: All uploads/downloads logged  
✅ **Error Handling**: Comprehensive try-catch blocks  

The support ticket system is now production-ready with complete file attachment support! 🎉
