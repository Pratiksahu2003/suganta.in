# Storage Structure Documentation

This document outlines the file storage structure for the SuGanta API.

## Base Storage Path

All files are stored in Laravel's public storage disk:
```
storage/app/public/
```

This is symlinked to:
```
public/storage/
```

## Directory Structure

```
storage/app/public/
├── portfolios/
│   ├── images/                          # Portfolio images
│   │   └── portfolio_image_*.{jpg,png,gif,webp}
│   └── portfolio_file_*.{pdf,doc,xls,etc}  # Portfolio documents
│
└── support-tickets/                     # Support ticket attachments
    ├── ticket_*.{jpg,png,pdf,doc,etc}
    └── reply_*.{jpg,png,pdf,doc,etc}
```

## Portfolio Files

### Images
- **Location**: `storage/app/public/portfolios/images/`
- **Public URL**: `http://domain.com/storage/portfolios/images/filename.jpg`
- **Formats**: jpg, jpeg, png, gif, webp
- **Max Size**: 5MB per image
- **Max Count**: 10 images per portfolio

**Naming Pattern:**
```
portfolio_image_{user_id}_{timestamp}_{random}_{sanitized_name}.{ext}
```

**Example:**
```
portfolio_image_5_20260306120000_a1b2c3d4e5f6g7h8_screenshot.jpg
```

### Documents/Files
- **Location**: `storage/app/public/portfolios/`
- **Public URL**: `http://domain.com/storage/portfolios/filename.pdf`
- **Formats**: pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar
- **Max Size**: 10MB per file
- **Max Count**: 10 files per portfolio

**Naming Pattern:**
```
portfolio_file_{user_id}_{timestamp}_{random}_{sanitized_name}.{ext}
```

**Example:**
```
portfolio_file_5_20260306120000_a1b2c3d4e5f6g7h8_documentation.pdf
```

## Support Ticket Files

### Attachments
- **Location**: `storage/app/public/support-tickets/`
- **Public URL**: `http://domain.com/storage/support-tickets/filename.pdf`
- **Formats**: jpg, jpeg, png, pdf, doc, docx, txt, zip
- **Max Size**: 10MB per file

**Ticket Attachment Naming Pattern:**
```
ticket_{user_id}_{timestamp}_{random}_{sanitized_name}.{ext}
```

**Reply Attachment Naming Pattern:**
```
reply_{user_id}_{timestamp}_{random}_{sanitized_name}.{ext}
```

**Examples:**
```
ticket_5_20260306120000_a1b2c3d4e5f6g7h8_issue_screenshot.jpg
reply_5_20260306120000_a1b2c3d4e5f6g7h8_solution_document.pdf
```

## File Naming Convention

All files follow this naming pattern:
```
{type}_{user_id}_{timestamp}_{random}_{sanitized_name}.{ext}
```

### Components:

1. **Type**: 
   - `portfolio_image` - Portfolio images
   - `portfolio_file` - Portfolio documents
   - `ticket` - Support ticket attachments
   - `reply` - Support ticket reply attachments

2. **User ID**: 
   - The ID of the user uploading the file

3. **Timestamp**: 
   - Format: `YmdHis` (e.g., 20260306120000)
   - Ensures chronological ordering

4. **Random String**: 
   - 16 character hexadecimal string
   - Prevents filename collisions
   - Adds security through obscurity

5. **Sanitized Name**: 
   - Original filename with special characters replaced by underscores
   - Only alphanumeric, hyphens, and underscores allowed
   - Pattern: `/[^a-zA-Z0-9_-]/` replaced with `_`

6. **Extension**: 
   - Original file extension preserved

## API Response Format

When files are returned via API, they include both the storage path and public URL:

### Portfolio Images Response:
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

### Portfolio Files Response:
```json
{
  "files": [
    {
      "path": "portfolios/portfolio_file_5_20260306120000_def456_documentation.pdf",
      "url": "http://localhost:8000/storage/portfolios/portfolio_file_5_20260306120000_def456_documentation.pdf",
      "name": "documentation.pdf"
    }
  ]
}
```

## Storage Setup

### Initial Setup

1. Create the storage symlink:
```bash
php artisan storage:link
```

2. Ensure proper permissions:
```bash
chmod -R 775 storage/app/public
chmod -R 775 public/storage
```

### Directory Creation

Directories are automatically created when files are uploaded. Laravel's `storeAs()` method creates the directory if it doesn't exist.

## File Management

### Upload Process

1. **Validation**: File is validated against size and type constraints
2. **Name Generation**: Unique filename is generated using the pattern above
3. **Storage**: File is stored in the appropriate directory
4. **Database**: File path is saved to the database
5. **Logging**: Upload is logged for audit purposes

### Delete Process

1. **Database Check**: Verify file path exists in database
2. **Storage Check**: Verify file exists in storage
3. **Delete**: Remove file from storage
4. **Database Update**: Update or delete database record
5. **Logging**: Deletion is logged for audit purposes

### Update Process (Portfolios)

When updating portfolios:
- New files are added to existing arrays
- Use `remove_images[]` or `remove_files[]` to delete specific files
- Deleted files are removed from both storage and database

## Security Considerations

1. **File Validation**: 
   - MIME type validation
   - File extension validation
   - File size limits

2. **Filename Sanitization**:
   - Special characters removed
   - Random string prevents guessing
   - Timestamp prevents collisions

3. **Access Control**:
   - Published portfolios: Public access
   - Draft/archived portfolios: Owner only
   - Support tickets: User and admin only

4. **Storage Location**:
   - Files stored outside web root
   - Accessed via symlink
   - Laravel handles file serving

## Disk Space Management

### Monitoring

Monitor disk usage regularly:
```bash
du -sh storage/app/public/portfolios/
du -sh storage/app/public/support-tickets/
```

### Cleanup

Orphaned files (files in storage but not in database) should be cleaned up periodically. Consider creating an artisan command:

```bash
php artisan storage:cleanup
```

### Backup

Include storage directories in your backup strategy:
```
storage/app/public/portfolios/
storage/app/public/support-tickets/
```

## Example URLs

### Development (localhost):
```
http://localhost:8000/storage/portfolios/images/portfolio_image_5_20260306120000_abc123_screenshot.jpg
http://localhost:8000/storage/portfolios/portfolio_file_5_20260306120000_def456_document.pdf
http://localhost:8000/storage/support-tickets/ticket_5_20260306120000_xyz789_issue.jpg
```

### Production:
```
https://api.suganta.com/storage/portfolios/images/portfolio_image_5_20260306120000_abc123_screenshot.jpg
https://api.suganta.com/storage/portfolios/portfolio_file_5_20260306120000_def456_document.pdf
https://api.suganta.com/storage/support-tickets/ticket_5_20260306120000_xyz789_issue.jpg
```

## Troubleshooting

### Files not accessible

1. Check if storage link exists:
```bash
ls -la public/storage
```

2. Recreate storage link:
```bash
php artisan storage:link
```

3. Check permissions:
```bash
chmod -R 775 storage/app/public
```

### Upload fails

1. Check disk space:
```bash
df -h
```

2. Check PHP upload limits in `php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 25M
```

3. Check Laravel config in `.env`:
```env
FILESYSTEM_DISK=public
```

### File not found

1. Verify file exists in storage
2. Check database path matches storage path
3. Verify storage symlink is correct
4. Check file permissions

## Best Practices

1. **Always use Laravel's Storage facade** for file operations
2. **Log all file operations** for audit trail
3. **Validate files** before storage
4. **Clean up files** when deleting records
5. **Monitor disk usage** regularly
6. **Backup storage** regularly
7. **Use environment-specific URLs** (don't hardcode domain)
8. **Handle errors gracefully** with try-catch blocks
