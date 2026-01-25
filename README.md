# NGO Website

A content management system built with PHP and MongoDB for Non-Governmental Organizations to showcase their mission, manage activities, and maintain their web presence.

## Features

### Public Website
- **Home Page**: Display organization mission and overview
- **About Page**: Detailed information about organization history and goals
- **Activities**: Browse published activities and charity posts with pagination
- **Activity Details**: View full activity content with images
- **Contact**: Contact information and contact form
- **Responsive Design**: Bootstrap-based responsive layout for all devices

### Admin Panel
- **Secure Authentication**: Session-based login system with password hashing
- **Dashboard**: Overview with quick stats and recent activities
- **Activity Management**: Create, edit, and delete activity posts with image uploads
- **Page Management**: Edit static page content (Home, About, Contact)
- **Image Upload**: Secure file upload with validation

## Requirements

### System Requirements
- **PHP**: 8.0 or higher
- **MongoDB**: 6.0 or higher
- **Web Server**: Apache with mod_rewrite enabled (or Nginx with URL rewriting)
- **PHP Extensions**:
  - mongodb (MongoDB PHP Driver)
  - gd or imagick (for image processing)
  - fileinfo (for file type detection)
  - session (for authentication)

### MongoDB PHP Driver Installation

The MongoDB PHP Driver is required to connect to MongoDB. Install it using one of these methods:

#### Method 1: Using PECL (Recommended)
```bash
# Install the MongoDB extension
pecl install mongodb

# Add to your php.ini file
echo "extension=mongodb" >> /path/to/php.ini
```

#### Method 2: Using Package Manager (Ubuntu/Debian)
```bash
# Install PHP MongoDB extension
sudo apt-get install php-mongodb

# Restart web server
sudo systemctl restart apache2
```

#### Method 3: Using Package Manager (CentOS/RHEL)
```bash
# Install PHP MongoDB extension
sudo yum install php-mongodb
# or for newer versions:
sudo dnf install php-mongodb

# Restart web server
sudo systemctl restart httpd
```

#### Method 4: Manual Compilation
```bash
# Download and compile from source
git clone https://github.com/mongodb/mongo-php-driver.git
cd mongo-php-driver
git submodule update --init
phpize
./configure
make all
sudo make install
```

### Verify Installation
Create a test file to verify the MongoDB extension is loaded:
```php
<?php
if (extension_loaded('mongodb')) {
    echo "MongoDB extension is loaded!";
    phpinfo();
} else {
    echo "MongoDB extension is NOT loaded!";
}
?>
```

## Installation

### 1. Download and Setup
```bash
# Clone or download the project
git clone <repository-url> ngo-website
cd ngo-website

# Install Composer dependencies
composer install

# Set proper permissions
chmod 755 uploads/
chmod 644 .htaccess
```

### 2. MongoDB Setup
```bash
# Install MongoDB (Ubuntu/Debian)
sudo apt-get install mongodb

# Start MongoDB service
sudo systemctl start mongodb
sudo systemctl enable mongodb

# Create database (optional - will be created automatically)
mongo
> use ngo_website
> exit
```

### 3. Configuration
```bash
# Copy and edit configuration file
cp includes/config.php.example includes/config.php
```

Edit `includes/config.php` with your settings:
```php
<?php
// MongoDB Configuration
define('MONGODB_URI', 'mongodb://localhost:27017');
define('MONGODB_DATABASE', 'ngo_website');

// Site Configuration
define('SITE_NAME', 'Your NGO Name');
define('SITE_URL', 'http://localhost/ngo-website');

// Email Configuration (for contact form)
define('CONTACT_EMAIL', 'info@your-ngo.org');
define('FROM_EMAIL', 'noreply@your-ngo.org');
?>
```

### 4. Database Initialization
```bash
# Run the database initialization script
php database/init.php
```

This script will:
- Create necessary MongoDB indexes
- Insert default administrator account (username: `admin`, password: `admin123`)
- Create default page content
- Add sample activity data

### 5. Web Server Configuration

#### Apache Configuration
Ensure your Apache virtual host has the following settings:
```apache
<VirtualHost *:80>
    ServerName your-ngo-website.local
    DocumentRoot /path/to/ngo-website
    
    <Directory /path/to/ngo-website>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Optional: Redirect to public directory
    # DocumentRoot /path/to/ngo-website/public
</VirtualHost>
```

#### Nginx Configuration (Alternative)
```nginx
server {
    listen 80;
    server_name your-ngo-website.local;
    root /path/to/ngo-website;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Security: Deny access to sensitive directories
    location ~ ^/(includes|database|vendor|\.kiro) {
        deny all;
    }
}
```

## Usage

### Accessing the Website
- **Public Website**: `http://your-domain.com/public/`
- **Admin Panel**: `http://your-domain.com/admin/`

### Default Admin Credentials
- **Username**: `admin`
- **Password**: `admin123`

**Important**: Change the default password immediately after first login!

### Managing Content

#### Activities
1. Login to admin panel
2. Navigate to Activities → Create New
3. Fill in title, content, and upload image (optional)
4. Set status to "Published" to make it visible on public site
5. Click "Save Activity"

#### Static Pages
1. Login to admin panel
2. Navigate to Pages → Edit Pages
3. Select page from dropdown (Home, About, Contact)
4. Edit title and content
5. Click "Save Changes"

### File Uploads
- Supported formats: JPG, PNG, GIF
- Maximum file size: 5MB
- Files are stored in `uploads/` directory
- Automatic file renaming prevents conflicts

## Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with bcrypt
- **Session Management**: Secure session handling with timeout
- **Input Validation**: Server-side validation for all forms
- **File Upload Security**: Type and size validation for uploads
- **MongoDB Security**: Parameterized queries prevent injection
- **Access Control**: Protected admin directories via .htaccess
- **XSS Prevention**: Output escaping with `htmlspecialchars()`

## Troubleshooting

### Common Issues

#### MongoDB Connection Failed
```bash
# Check if MongoDB is running
sudo systemctl status mongodb

# Check MongoDB logs
sudo tail -f /var/log/mongodb/mongodb.log

# Test connection
mongo --eval "db.adminCommand('ismaster')"
```

#### PHP MongoDB Extension Not Found
```bash
# Check if extension is loaded
php -m | grep mongodb

# Check PHP configuration
php --ini

# Install extension (Ubuntu/Debian)
sudo apt-get install php-mongodb
sudo systemctl restart apache2
```

#### Permission Denied on Uploads
```bash
# Set correct permissions
chmod 755 uploads/
chown www-data:www-data uploads/  # Ubuntu/Debian
chown apache:apache uploads/      # CentOS/RHEL
```

#### .htaccess Not Working
```bash
# Enable mod_rewrite (Ubuntu/Debian)
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check Apache configuration allows .htaccess
# In your virtual host or main config:
# AllowOverride All
```

### Debug Mode
To enable debug mode, add this to your `config.php`:
```php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

## Development

### Project Structure
```
/
├── public/              # Public website files
├── admin/               # Admin panel files
├── includes/            # Shared PHP files and configuration
├── uploads/             # User uploaded files
├── database/            # Database scripts
├── vendor/              # Composer dependencies
└── .kiro/               # Kiro IDE specifications
```

### Adding New Features
1. Update requirements in `.kiro/specs/ngo-website/requirements.md`
2. Modify design in `.kiro/specs/ngo-website/design.md`
3. Add tasks to `.kiro/specs/ngo-website/tasks.md`
4. Implement following the established patterns

### Database Collections
- **administrators**: Admin user accounts
- **activities**: Activity posts and content
- **pages**: Static page content
- **contact_messages**: Contact form submissions (optional)

## License

This project is open source. Please check the LICENSE file for details.

## Support

For support and questions:
1. Check the troubleshooting section above
2. Review MongoDB and PHP documentation
3. Check server error logs for detailed error messages

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**Note**: This is a basic CMS suitable for small to medium NGO websites. For larger organizations with complex requirements, consider using established CMS platforms like WordPress, Drupal, or custom enterprise solutions.