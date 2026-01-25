# 🚀 Render Deployment Guide for NGO Website

This guide will walk you through deploying your NGO website to Render, a modern cloud platform that supports PHP applications.

## 📋 Prerequisites

Before starting, ensure you have:
- ✅ A GitHub account
- ✅ Your website code ready
- ✅ MongoDB Atlas database set up
- ✅ Admin credentials configured

## 🔧 Step 1: Prepare Your Code for Deployment

### 1.1 Create Required Configuration Files

Create a `render.yaml` file in your project root:

```yaml
services:
  - type: web
    name: ngo-website
    env: php
    buildCommand: composer install --no-dev --optimize-autoloader
    startCommand: php -S 0.0.0.0:$PORT -t public
    envVars:
      - key: MONGODB_URI
        sync: false
      - key: MONGODB_DATABASE
        value: ngo_website
      - key: ADMIN_USERNAME
        sync: false
      - key: ADMIN_PASSWORD
        sync: false
      - key: SESSION_SECRET
        generateValue: true
```

### 1.2 Update composer.json

Ensure your `composer.json` includes all necessary dependencies:

```json
{
    "name": "ngo-website/mongodb-app",
    "description": "NGO Website with MongoDB backend",
    "type": "project",
    "require": {
        "php": ">=8.0",
        "mongodb/mongodb": "^2.0"
    },
    "autoload": {
        "files": [
            "includes/config.php",
            "includes/db.php",
            "includes/functions.php"
        ]
    },
    "config": {
        "optimize-autoloader": true
    },
    "scripts": {
        "post-install-cmd": [
            "mkdir -p data/contact_messages",
            "mkdir -p uploads",
            "chmod 755 data/contact_messages",
            "chmod 755 uploads"
        ]
    }
}
```

### 1.3 Create .htaccess for URL Rewriting

Create a `.htaccess` file in your project root:

```apache
RewriteEngine On

# Redirect to public folder
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L]

# Handle PHP files in public directory
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /public/index.php [L]
```

### 1.4 Update Configuration for Production

Update `includes/config.php` to handle environment variables:

```php
<?php
// Environment-based configuration
$isProduction = isset($_ENV['RENDER']) || isset($_SERVER['RENDER']);

// MongoDB Configuration
define('MONGODB_URI', $_ENV['MONGODB_URI'] ?? $_SERVER['MONGODB_URI'] ?? 'mongodb://localhost:27017');
define('MONGODB_DATABASE', $_ENV['MONGODB_DATABASE'] ?? $_SERVER['MONGODB_DATABASE'] ?? 'ngo_website');

// Admin Configuration
define('ADMIN_USERNAME', $_ENV['ADMIN_USERNAME'] ?? $_SERVER['ADMIN_USERNAME'] ?? 'admin');
define('ADMIN_PASSWORD', $_ENV['ADMIN_PASSWORD'] ?? $_SERVER['ADMIN_PASSWORD'] ?? 'admin123');

// Session Configuration
define('SESSION_SECRET', $_ENV['SESSION_SECRET'] ?? $_SERVER['SESSION_SECRET'] ?? 'your-secret-key');

// File Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Session Settings
define('ADMIN_SESSION_TIMEOUT', 3600); // 1 hour

// Error Reporting
if ($isProduction) {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
```

## 🌐 Step 2: Set Up GitHub Repository

### 2.1 Initialize Git Repository

```bash
git init
git add .
git commit -m "Initial commit: NGO website ready for deployment"
```

### 2.2 Create GitHub Repository

1. Go to [GitHub.com](https://github.com)
2. Click "New repository"
3. Name it `ngo-website` or similar
4. Make it public or private
5. Don't initialize with README (you already have files)
6. Click "Create repository"

### 2.3 Push to GitHub

```bash
git remote add origin https://github.com/YOUR_USERNAME/ngo-website.git
git branch -M main
git push -u origin main
```

## 🚀 Step 3: Deploy to Render

### 3.1 Create Render Account

1. Go to [render.com](https://render.com)
2. Sign up with your GitHub account
3. Authorize Render to access your repositories

### 3.2 Create New Web Service

1. Click "New +" → "Web Service"
2. Connect your GitHub repository
3. Select your `ngo-website` repository
4. Configure the service:

**Basic Settings:**
- **Name:** `ngo-website`
- **Environment:** `PHP`
- **Region:** Choose closest to your users
- **Branch:** `main`

**Build & Deploy:**
- **Build Command:** `composer install --no-dev --optimize-autoloader`
- **Start Command:** `php -S 0.0.0.0:$PORT -t .`

### 3.3 Configure Environment Variables

In the Render dashboard, add these environment variables:

| Key | Value | Notes |
|-----|-------|-------|
| `MONGODB_URI` | `mongodb+srv://username:password@cluster.mongodb.net/` | Your MongoDB Atlas connection string |
| `MONGODB_DATABASE` | `ngo_website` | Your database name |
| `ADMIN_USERNAME` | `admin` | Your admin username |
| `ADMIN_PASSWORD` | `your-secure-password` | Strong admin password |
| `SESSION_SECRET` | `auto-generated` | Let Render generate this |

### 3.4 Deploy

1. Click "Create Web Service"
2. Render will automatically build and deploy your application
3. Wait for the deployment to complete (usually 2-5 minutes)

## 🔧 Step 4: Post-Deployment Configuration

### 4.1 Test Your Website

1. Visit your Render URL (e.g., `https://ngo-website.onrender.com`)
2. Test all pages:
   - Home page
   - About page
   - Activities page
   - Contact form
   - Admin login

### 4.2 Set Up MongoDB Atlas IP Whitelist

1. Go to MongoDB Atlas dashboard
2. Navigate to "Network Access"
3. Add IP Address: `0.0.0.0/0` (allow all IPs)
4. Or add Render's specific IP ranges if available

### 4.3 Configure Custom Domain (Optional)

If you have a custom domain:

1. In Render dashboard, go to your service
2. Click "Settings" → "Custom Domains"
3. Add your domain
4. Update your DNS records as instructed

## 📁 Step 5: File Structure for Render

Your final project structure should look like this:

```
ngo-website/
├── .htaccess
├── render.yaml
├── composer.json
├── composer.lock
├── index.php (redirects to public/)
├── public/
│   ├── index.php
│   ├── about.php
│   ├── activities.php
│   ├── contact.php
│   ├── activity-detail.php
│   └── assets/
├── admin/
│   ├── index.php
│   ├── login.php
│   ├── logout.php
│   └── messages/
├── includes/
│   ├── config.php
│   ├── db.php
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   ├── admin-header.php
│   ├── admin-footer.php
│   └── contact_fallback.php
├── data/
│   └── contact_messages/
└── uploads/
```

## 🔍 Step 6: Troubleshooting

### Common Issues and Solutions

**Issue: "Application failed to start"**
- Check build logs in Render dashboard
- Ensure `composer.json` is valid
- Verify PHP version compatibility

**Issue: "Database connection failed"**
- Check MongoDB Atlas connection string
- Verify IP whitelist includes `0.0.0.0/0`
- Test connection string locally first

**Issue: "File upload not working"**
- Render has ephemeral storage
- Consider using cloud storage (AWS S3, Cloudinary)
- Or implement database-only storage

**Issue: "Admin login not working"**
- Check environment variables are set correctly
- Verify admin credentials
- Check session configuration

## 🔒 Step 7: Security Considerations

### 7.1 Environment Variables
- Never commit sensitive data to Git
- Use Render's environment variables for secrets
- Rotate passwords regularly

### 7.2 Database Security
- Use strong MongoDB Atlas passwords
- Enable database authentication
- Regularly backup your data

### 7.3 Application Security
- Keep dependencies updated
- Use HTTPS (automatic on Render)
- Implement proper input validation

## 📊 Step 8: Monitoring and Maintenance

### 8.1 Monitor Your Application
- Check Render dashboard for uptime
- Monitor error logs
- Set up alerts for downtime

### 8.2 Regular Updates
- Update dependencies regularly
- Monitor security advisories
- Test updates in staging first

## 🎉 Deployment Checklist

Before going live, ensure:

- [ ] All pages load correctly
- [ ] Contact form submits successfully
- [ ] Admin panel is accessible
- [ ] Database connections work
- [ ] File uploads function (if applicable)
- [ ] Environment variables are set
- [ ] Custom domain configured (if applicable)
- [ ] SSL certificate is active
- [ ] Error pages are user-friendly
- [ ] Performance is acceptable

## 📞 Support

If you encounter issues:

1. **Render Documentation:** [render.com/docs](https://render.com/docs)
2. **MongoDB Atlas Support:** [docs.atlas.mongodb.com](https://docs.atlas.mongodb.com)
3. **PHP Documentation:** [php.net](https://php.net)

## 🚀 Going Live

Once everything is tested and working:

1. Update any hardcoded URLs to your production domain
2. Set up monitoring and backups
3. Announce your website launch!
4. Consider setting up analytics (Google Analytics)

Your NGO website is now live and accessible to the world! 🌍

---

**Note:** This guide assumes you're using the free tier of Render. For production websites with high traffic, consider upgrading to a paid plan for better performance and features.