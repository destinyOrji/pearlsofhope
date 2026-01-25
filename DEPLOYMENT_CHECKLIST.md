# 🚀 Deployment Checklist for Render

## Pre-Deployment Checklist

### ✅ Code Preparation
- [ ] All test files removed
- [ ] Sensitive data moved to environment variables
- [ ] Error reporting configured for production
- [ ] File permissions set correctly
- [ ] Dependencies updated in composer.json

### ✅ Configuration Files
- [ ] `render.yaml` created
- [ ] `.htaccess` configured
- [ ] `composer.json` updated with post-install scripts
- [ ] `includes/config.php` updated for environment variables
- [ ] Root `index.php` created for routing

### ✅ Database Setup
- [ ] MongoDB Atlas cluster created
- [ ] Database user created with proper permissions
- [ ] IP whitelist configured (0.0.0.0/0 for Render)
- [ ] Connection string tested

### ✅ GitHub Repository
- [ ] Repository created on GitHub
- [ ] All files committed and pushed
- [ ] Repository is accessible to Render

## Render Deployment Steps

### 1. Environment Variables to Set in Render

| Variable | Value | Example |
|----------|-------|---------|
| `MONGODB_URI` | Your MongoDB Atlas connection string | `mongodb+srv://user:pass@cluster.mongodb.net/` |
| `MONGODB_DATABASE` | Database name | `ngo_website` |
| `ADMIN_USERNAME` | Admin username | `admin` |
| `ADMIN_PASSWORD` | Strong admin password | `SecurePassword123!` |
| `SESSION_SECRET` | Auto-generate in Render | (let Render generate) |

### 2. Render Service Configuration

- **Service Type:** Web Service
- **Environment:** PHP
- **Build Command:** `composer install --no-dev --optimize-autoloader`
- **Start Command:** `php -S 0.0.0.0:$PORT -t .`
- **Auto-Deploy:** Yes (recommended)

## Post-Deployment Testing

### ✅ Basic Functionality
- [ ] Homepage loads correctly
- [ ] About page displays properly
- [ ] Activities page shows content
- [ ] Contact form submits successfully
- [ ] Admin login works
- [ ] Admin can view messages

### ✅ Database Connectivity
- [ ] MongoDB connection successful
- [ ] Contact messages save to database
- [ ] Admin can view saved messages
- [ ] File fallback works if database fails

### ✅ Security & Performance
- [ ] HTTPS is enabled (automatic on Render)
- [ ] Admin area requires authentication
- [ ] File uploads work (if applicable)
- [ ] Error pages display correctly
- [ ] Site loads within acceptable time

## Troubleshooting Common Issues

### Build Failures
- Check composer.json syntax
- Verify PHP version compatibility
- Review build logs in Render dashboard

### Database Connection Issues
- Verify MongoDB Atlas connection string
- Check IP whitelist settings
- Test connection string format

### File Permission Issues
- Ensure uploads directory is writable
- Check data directory permissions
- Verify post-install scripts run correctly

### Environment Variable Issues
- Confirm all required variables are set
- Check variable names match exactly
- Verify sensitive data is not in code

## Performance Optimization

### ✅ Caching
- [ ] Static assets cached via .htaccess
- [ ] Composer autoloader optimized
- [ ] Database queries optimized

### ✅ Security Headers
- [ ] X-Content-Type-Options set
- [ ] X-Frame-Options configured
- [ ] X-XSS-Protection enabled

## Monitoring & Maintenance

### ✅ Regular Tasks
- [ ] Monitor application logs
- [ ] Check database usage
- [ ] Update dependencies regularly
- [ ] Backup database periodically
- [ ] Monitor site uptime

### ✅ Scaling Considerations
- [ ] Monitor resource usage
- [ ] Consider upgrading Render plan if needed
- [ ] Implement CDN for static assets
- [ ] Optimize database queries

## Final Verification

### ✅ User Experience
- [ ] All forms work correctly
- [ ] Navigation is functional
- [ ] Images load properly
- [ ] Mobile responsiveness maintained
- [ ] Contact information is accurate

### ✅ Admin Experience
- [ ] Admin login is secure
- [ ] Message management works
- [ ] Content editing functions properly
- [ ] File uploads work (if applicable)

## Go-Live Checklist

- [ ] Domain configured (if using custom domain)
- [ ] SSL certificate active
- [ ] Analytics set up (Google Analytics, etc.)
- [ ] Search engine optimization completed
- [ ] Social media links updated
- [ ] Contact information verified
- [ ] Backup procedures established

## Emergency Contacts & Resources

- **Render Support:** [render.com/docs](https://render.com/docs)
- **MongoDB Atlas Support:** [docs.atlas.mongodb.com](https://docs.atlas.mongodb.com)
- **PHP Documentation:** [php.net](https://php.net)

---

**Note:** Keep this checklist handy for future deployments and updates!