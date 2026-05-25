# linkedin-job-scrape
A lightweight, full-stack LinkedIn Job Scraper featuring a native PHP streaming backend API and a responsive, dynamic HTML/CSS/JavaScript single-page dashboard. Extracts real-time job data instantly without authentication or memory crashes.

# Tech StackBackend
Native PHP (cURL, DOMDocument, HTTP Chunked Streaming)
Frontend: Vanilla JS (Fetch Streams API), HTML5, CSS Grid
Data Source: LinkedIn Guest Search API (No Auth)


<img width="1916" height="963" alt="image" src="https://github.com/user-attachments/assets/4c1f05c5-3ce1-4203-a2ab-a1d37b319a32" />


# LinkedIn Job Scraper

A powerful PHP-based web scraper for LinkedIn jobs with a user-friendly dashboard interface. Search for jobs, apply filters, and export results to Excel.

## Features

- 🔍 **Advanced Job Search** - Search jobs by keywords and location
- 🎯 **Smart Filtering** - Filter by experience level, job type, remote work, applicant count, and posting date
- 💾 **Caching System** - Reduces API calls with intelligent caching
- 📊 **Dashboard Interface** - Beautiful web interface to browse and manage job listings
- 📥 **Excel Export** - Export results to Excel using PHPSpreadsheet
- ⚡ **Fast & Efficient** - Optimized for performance with large datasets

---

## 🚀 Quick Start Guide for Beginners

### Prerequisites (What You Need)

Before running this application, make sure you have:

1. **PHP 8.0 or higher** - A server-side scripting language
   - Check your version: Open Command Prompt and run `php -v`
   
2. **Composer** - A package manager for PHP (like npm for Node.js)
   - Download from: https://getcomposer.org/download/
   - Check if installed: Run `composer --version` in Command Prompt

3. **A Local Server** - Like WAMP, XAMPP, or MAMP
   - We recommend WAMP for Windows: https://www.wampserver.com/
   - Or use PHP's built-in server (easier for beginners)

4. **cURL enabled** - Usually enabled by default in PHP

---

## 📋 Step-by-Step Installation

### Step 1: Navigate to Project Directory

Open Command Prompt and go to your project folder:

```bash
cd C:\wamp64\www\linkedIn
```

(Replace the path if your project is in a different location)

### Step 2: Install Dependencies

Run this command to install required PHP packages:

```bash
composer install
```

⏳ This may take 1-2 minutes. Wait for it to complete.

### Step 3: Create Required Folders

The application needs `cache` and `logs` folders. If they don't exist, create them:

```bash
mkdir cache
mkdir logs
```

### Step 4: Update Configuration (Important!)

1. Open the file `config.php` in any text editor
2. Find the line with `"cacert"` - this should point to your PHP's SSL certificate
3. If you're using WAMP, update it to your WAMP PHP installation path:

   ```php
   "cacert" => "C:\\wamp64\\bin\\php\\php8.3.6\\extras\\ssl\\cacert.pem"
   ```

4. Save the file

**Note:** If you're unsure about your PHP path, run this in Command Prompt:
```bash
php -i | grep "cacert"
```

---

## ▶️ How to Run the Application

### Option 1: Using PHP Built-in Server (Easiest for Beginners)

1. Open Command Prompt in your project folder:
   ```bash
   cd C:\wamp64\www\linkedIn
   ```

2. Start the PHP server:
   ```bash
   php -S localhost:8000
   ```

3. Open your browser and go to:
   ```
   http://localhost:8000
   ```

You should see the LinkedIn Job Scraper Dashboard! 🎉

### Option 2: Using WAMP Server

1. Start WAMP from your system tray (click the W icon)
2. Wait until the icon turns green
3. Open your browser and navigate to:
   ```
   http://localhost/linkedIn
   ```
   or
   ```
   http://localhost/linkedIn/index.php
   ```

---

## 💡 Using the Application

### Web Dashboard

Once the application is running:

1. **Search for Jobs**
   - Enter a job title (e.g., "Python Developer")
   - Enter a location (e.g., "New York" or "Remote")
   - Click the "Search" button

2. **Filter Results**
   - Use the filters on the left side to narrow down results
   - Filter by: Experience level, job type, remote work, applicant count, posting date

3. **Export to Excel**
   - Click the "Export" button to download job listings as an Excel file

### Using the API (Advanced)

If you want to use the API directly with commands:

**Fetch Jobs via Command Line**
```bash
curl "http://localhost:8000/api.php?keywords=Product%20Manager&location=San%20Francisco"
```

**Common Parameters:**
- `keywords` - Job title (required) - e.g., "Product Manager"
- `location` - Job location - e.g., "San Francisco" (default: United States)
- `experience` - Level: 1, 2, 3, 4, 5 (1=Internship, 5=Executive)
- `under10Applicants` - Set to `1` to show only jobs with few applicants
- `easyApply` - Set to `1` to show only Easy Apply positions

---

## 🛠️ Main Files Explained

| File | Purpose |
|------|---------|
| `index.php` | Main dashboard interface - start here! |
| `scraper.php` | Core job scraping logic |
| `api.php` | API endpoint for fetching jobs |
| `filter.php` | Handles job filtering and search |
| `export.php` | Exports jobs to Excel format |
| `config.php` | Configuration settings |

---

## 🔧 Configuration

The `config.php` file contains important settings:

```php
"base_url" => "https://www.linkedin.com/jobs-guest/jobs/api/seeMoreJobPostings/search",
"timeout" => 60,              // Request timeout in seconds
"cache_time" => 300,          // Cache duration in seconds (5 minutes)
"cacert" => "path/to/cacert.pem"  // SSL certificate path
```

---

## ❓ Troubleshooting

### Problem: "Command not found: composer"
**Solution:** Install Composer from https://getcomposer.org/download/

### Problem: "No such file or directory: cache"
**Solution:** Create the folders manually:
```bash
mkdir cache logs
```

### Problem: SSL certificate error
**Solution:** Update the `cacert` path in `config.php` to your PHP installation path

### Problem: "Permission denied" on cache/logs folders
**Solution:** Right-click the folders → Properties → Security → Edit → Grant permissions

### Problem: Page won't load
**Solution:** Make sure PHP server is running:
```bash
php -S localhost:8000
```

---

## 📁 File Structure

```
├── cache/              # Cached JSON job data
├── logs/               # Application logs
├── vendor/             # Composer dependencies
├── api.php             # REST API endpoint
├── config.php          # Configuration loader
├── scraper.php         # Core scraping functions
├── filter.php          # Filtering logic
├── export.php          # Excel export functionality
├── fetch_jobs.php      # Job fetching endpoint
├── index.php           # Dashboard interface
└── composer.json       # Project dependencies
```

## API Response Format

```json
{
  "data": [
    {
      "job_id": "123456",
      "title": "Product Manager",
      "company": "Acme Corp",
      "location": "San Francisco, CA",
      "description": "Job description...",
      "linkedin_url": "https://www.linkedin.com/jobs/view/...",
      "posted_date": "2024-01-15T10:30:00Z"
    }
  ],
  "total": 150,
  "start": 0
}
```

## Performance Notes

- Response times vary based on LinkedIn API availability
- Cache is set to 5 minutes by default (configurable)
- Requests timeout after 60 seconds (configurable)
- Designed to handle large job datasets efficiently

## Troubleshooting

### SSL Certificate Errors
If you get SSL certificate errors:
1. Download `cacert.pem` from [cacert.org](https://cacert.org/)
2. Update the path in `.env`
3. Or disable verification (not recommended for production)

### Timeout Issues
Increase `PHP_MAX_EXECUTION_TIME` in `.env` for larger searches:
```
PHP_MAX_EXECUTION_TIME=900
```

### Cache Issues
Clear the cache directory if experiencing stale data:
```bash
rm cache/*.json
```

## Legal Notice

- This tool is for educational and research purposes only
- Respect LinkedIn's Terms of Service and robots.txt
- Use responsibly and don't overload the service
- Implement appropriate delays between requests

## License

MIT License - feel free to use and modify for your needs.

## Contributing

Contributions are welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## Support

For issues, questions, or suggestions, please open a GitHub issue.

## Disclaimer

This project is not affiliated with LinkedIn. Use at your own risk and in accordance with LinkedIn's Terms of Service.
