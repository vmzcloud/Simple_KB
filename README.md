# Simple Knowledge Base

A comprehensive knowledge base system built with PHP and SQLite, featuring a WYSIWYG editor, file uploads, search functionality, and more.

## Features

- **WYSIWYG Editor**: Rich text editor with formatting options
- **File Uploads**: Support for images, PDFs, and documents
- **Markdown Support**: Full markdown rendering with syntax highlighting
- **Search**: Full-text search with tag filtering
- **Tags System**: Organize articles with tags
- **Tables**: Create and edit tables within articles
- **Code Blocks**: Syntax-highlighted code blocks
- **Responsive Design**: Works on desktop and mobile devices
- **SQLite Database**: Lightweight, serverless database

## Requirements

- PHP 7.4 or higher
- SQLite3 extension
- Web server (Apache, Nginx, or built-in PHP server)

## Installation

1. Clone or download this repository
2. Ensure your web server can execute PHP files
3. Make sure the `data/` and `uploads/` directories are writable
4. Access the application through your web browser

The database will be automatically created on first access.

## Directory Structure

```
Simple_KB/
├── assets/
│   └── css/
│       └── style.css          # Custom styles
├── config/
│   └── database.php           # Database configuration
├── includes/
│   └── functions.php          # Helper functions
├── data/                      # SQLite database storage
├── uploads/                   # Uploaded files storage
├── index.php                  # Homepage
├── create.php                 # Create new article
├── edit.php                   # Edit existing article
├── view.php                   # View article
├── search.php                 # Search functionality
├── delete.php                 # Delete article
└── delete_file.php           # Delete uploaded file
```

## Usage

### Creating Articles

1. Click "New Article" in the navigation
2. Enter a title and content using the WYSIWYG editor
3. Add tags (comma-separated) for organization
4. Upload files or images if needed
5. Save the article
6. You can embed uploaded images or files directly into your article content using the editor's toolbar (e.g., by clicking the image or file icon, or by dragging and dropping into the editor area).

### Editor Features

- **Text Formatting**: Bold, italic, underline
- **Headings**: H1, H2, H3 headers
- **Lists**: Bullet and numbered lists
- **Links**: Insert clickable links
- **Tables**: Create responsive tables
- **Code Blocks**: Add syntax-highlighted code
- **File Uploads**: Drag and drop or click to upload
- **Embed Images/Files**: Insert uploaded images and files directly into your content using the editor

### Search

- Use the search bar to find articles by content
- Filter by specific tags
- Browse popular tags in the sidebar

### File Management

- Upload images, PDFs, and documents
- Files are automatically associated with articles
- Preview images inline
- Download files directly

## Database Schema

The application uses three main tables:

- `articles`: Stores article content, titles, and metadata
- `files`: Stores uploaded file information
- `articles_fts`: Full-text search index

## Security Features

- Input sanitization
- File type validation
- Size limits on uploads
- SQL injection protection via prepared statements

## Customization

### Styling

Edit `assets/css/style.css` to customize the appearance.

### File Upload Limits

Modify the file size and type restrictions in `includes/functions.php`:

```php
$allowedTypes = [...]; // Add or remove file types
if ($file['size'] > 10 * 1024 * 1024) { // Change size limit
```

### Search Configuration

The full-text search can be customized in the database initialization:

```php
CREATE VIRTUAL TABLE articles_fts USING fts5(...);
```

## Troubleshooting

### Database Issues

- Ensure the `data/` directory is writable
- Check PHP SQLite3 extension is enabled

### File Upload Issues

- Verify `uploads/` directory permissions
- Check PHP upload limits in `php.ini`:
  - `upload_max_filesize`
  - `post_max_size`
  - `max_file_uploads`

### Search Not Working

- Rebuild the search index by recreating the `articles_fts` table
- Check that articles have content to index

## License

This project is open source and available under the MIT License.

## Contributing

Feel free to submit issues, fork the repository, and create pull requests for any improvements.

## Support

For support or questions, please create an issue in the repository.
