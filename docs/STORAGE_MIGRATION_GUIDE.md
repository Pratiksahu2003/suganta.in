# Storage Migration Guide

## Overview

The storage structure has been updated to organize files better:
- Portfolio **images** now go to: `storage/app/public/portfolios/images/`
- Portfolio **files** (documents) remain in: `storage/app/public/portfolios/`
- Support ticket files remain in: `storage/app/public/support-tickets/`

## For New Installations

No action needed! Just run:
```bash
php artisan storage:link
```

The directories will be created automatically when files are uploaded.

---

## For Existing Installations (Migration)

If you have existing portfolio images that need to be moved to the new structure:

### Option 1: Manual Migration (Recommended for small datasets)

```bash
# Navigate to storage directory
cd storage/app/public/portfolios

# Create images directory if it doesn't exist
mkdir -p images

# Move all image files to the images directory
mv portfolio_image_*.jpg images/ 2>/dev/null
mv portfolio_image_*.jpeg images/ 2>/dev/null
mv portfolio_image_*.png images/ 2>/dev/null
mv portfolio_image_*.gif images/ 2>/dev/null
mv portfolio_image_*.webp images/ 2>/dev/null

echo "Image files moved successfully!"
```

### Option 2: Database Update Script

If you've already moved the files, update the database paths:

```bash
php artisan tinker
```

Then run:
```php
use App\Models\Portfolio;

Portfolio::all()->each(function ($portfolio) {
    if ($portfolio->images) {
        $updated = false;
        $images = $portfolio->images;
        
        foreach ($images as $key => $image) {
            // Check if path needs updating
            if (strpos($image, 'portfolios/portfolio_image_') === 0) {
                // Update path to include images subdirectory
                $images[$key] = str_replace(
                    'portfolios/portfolio_image_',
                    'portfolios/images/portfolio_image_',
                    $image
                );
                $updated = true;
            }
        }
        
        if ($updated) {
            $portfolio->images = $images;
            $portfolio->save();
            echo "Updated portfolio #{$portfolio->id}\n";
        }
    }
});

echo "Migration complete!\n";
```

### Option 3: Artisan Command (For large datasets)

Create a migration command:

```bash
php artisan make:command MigratePortfolioImages
```

Add this to `app/Console/Commands/MigratePortfolioImages.php`:

```php
<?php

namespace App\Console\Commands;

use App\Models\Portfolio;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigratePortfolioImages extends Command
{
    protected $signature = 'portfolio:migrate-images';
    protected $description = 'Migrate portfolio images to new directory structure';

    public function handle()
    {
        $this->info('Starting portfolio image migration...');
        
        $disk = Storage::disk('public');
        $imagesDir = 'portfolios/images';
        
        // Create images directory if it doesn't exist
        if (!$disk->exists($imagesDir)) {
            $disk->makeDirectory($imagesDir);
            $this->info("Created directory: {$imagesDir}");
        }
        
        $portfolios = Portfolio::whereNotNull('images')->get();
        $movedCount = 0;
        $updatedCount = 0;
        
        foreach ($portfolios as $portfolio) {
            $images = $portfolio->images;
            $updated = false;
            
            foreach ($images as $key => $imagePath) {
                // Check if image is in old location
                if (strpos($imagePath, 'portfolios/portfolio_image_') === 0 
                    && strpos($imagePath, 'portfolios/images/') === false) {
                    
                    $oldPath = $imagePath;
                    $filename = basename($imagePath);
                    $newPath = "portfolios/images/{$filename}";
                    
                    // Move file if it exists
                    if ($disk->exists($oldPath)) {
                        if ($disk->move($oldPath, $newPath)) {
                            $images[$key] = $newPath;
                            $updated = true;
                            $movedCount++;
                            $this->line("Moved: {$filename}");
                        } else {
                            $this->error("Failed to move: {$filename}");
                        }
                    } else {
                        // File doesn't exist, just update path
                        $images[$key] = $newPath;
                        $updated = true;
                        $this->warn("File not found, updated path only: {$filename}");
                    }
                }
            }
            
            if ($updated) {
                $portfolio->images = $images;
                $portfolio->save();
                $updatedCount++;
            }
        }
        
        $this->info("\nMigration complete!");
        $this->info("Files moved: {$movedCount}");
        $this->info("Portfolios updated: {$updatedCount}");
        
        return 0;
    }
}
```

Then run:
```bash
php artisan portfolio:migrate-images
```

---

## Verification

After migration, verify the changes:

### 1. Check File System
```bash
# List files in new location
ls -la storage/app/public/portfolios/images/

# Check old location is empty of images
ls -la storage/app/public/portfolios/portfolio_image_*
```

### 2. Check Database
```bash
php artisan tinker
```

```php
use App\Models\Portfolio;

// Check a few portfolios
Portfolio::whereNotNull('images')->take(5)->get()->each(function($p) {
    echo "Portfolio #{$p->id}:\n";
    print_r($p->images);
    echo "\n";
});
```

### 3. Check API Response
```bash
# Test API endpoint
curl http://localhost:8000/api/v1/portfolios/1
```

Verify that image URLs are correct:
```json
{
  "images": [
    {
      "path": "portfolios/images/portfolio_image_5_20260306120000_abc123_screenshot.jpg",
      "url": "http://localhost:8000/storage/portfolios/images/portfolio_image_5_20260306120000_abc123_screenshot.jpg"
    }
  ]
}
```

### 4. Test File Access
```bash
# Test image URL in browser or curl
curl -I http://localhost:8000/storage/portfolios/images/portfolio_image_5_20260306120000_abc123_screenshot.jpg
```

Should return `200 OK`.

---

## Rollback (If Needed)

If you need to rollback:

### 1. Move Files Back
```bash
cd storage/app/public/portfolios/images
mv portfolio_image_* ../ 2>/dev/null
cd ..
rmdir images
```

### 2. Update Database
```bash
php artisan tinker
```

```php
use App\Models\Portfolio;

Portfolio::all()->each(function ($portfolio) {
    if ($portfolio->images) {
        $images = $portfolio->images;
        foreach ($images as $key => $image) {
            $images[$key] = str_replace(
                'portfolios/images/portfolio_image_',
                'portfolios/portfolio_image_',
                $image
            );
        }
        $portfolio->images = $images;
        $portfolio->save();
    }
});
```

---

## Notes

- **New uploads** will automatically use the correct directory structure
- **Existing files** will continue to work even if not migrated (backward compatible)
- **URLs are generated dynamically** from the stored paths
- **Migration is optional** but recommended for consistency
- **Backup your database and storage** before migration

---

## Support

If you encounter issues:
1. Check file permissions: `chmod -R 775 storage/app/public`
2. Verify storage link: `ls -la public/storage`
3. Check Laravel logs: `tail -f storage/logs/laravel.log`
4. Test with a single portfolio first before bulk migration
