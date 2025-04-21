# STR Application Deployment Guide

## Deploying on Coolify

This PHP application can be easily deployed on Coolify, a self-hostable Heroku/Netlify alternative.

### Prerequisites

1. A Coolify server set up and running
2. Git repository containing this application code

### Deployment Steps

1. **Login to your Coolify dashboard**

2. **Create a new resource**:
   - Click on "Create new resource"
   - Select "Application"
   - Choose "Docker" (not Docker Compose) as the source

3. **Connect your Git repository**:
   - Connect to your Git provider where this repository is hosted
   - Select this repository
   - Select the branch you want to deploy (usually `main` or `master`)

4. **Configure the deployment**:
   - Set the build method to "Dockerfile"
   - Ensure the port is set to 80
   - Set the root directory to "public"

5. **Configure environment variables**:
   Set the following environment variables in the Coolify dashboard:
   
   ```
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=default
   DB_USERNAME=mysql
   DB_PASSWORD=your_secure_password
   DB_ROOT_PASSWORD=your_secure_root_password
   ```

6. **Deploy the application**:
   - Click the "Deploy" button
   - Coolify will build and deploy your application using the Dockerfile

### Troubleshooting 403 Forbidden Errors

If you encounter a 403 Forbidden error with the message "nginx/1.24.0", try these solutions:

1. **Ensure the web root is properly set**:
   - In Coolify's dashboard, make sure the root directory is set to "public"

2. **Check file permissions**:
   - The Dockerfile sets appropriate permissions, but you can verify them in the Coolify logs

3. **Test with the test.html file**:
   - Try accessing the test.html file first (e.g., https://your-app.coolify.io/test.html)
   - If this works but PHP files don't, there may be an issue with PHP processing

4. **Check Coolify logs**:
   - In the Coolify dashboard, check the logs for your deployment for any error messages

### Local Development

For local development, you can use PHP's built-in server:

```
php -S localhost:8000 -t public
```

### Maintenance

- **Updating the application**: Push changes to your Git repository, and Coolify will automatically rebuild and redeploy the application
- **Database backups**: Use Coolify's database backup features to regularly backup your MySQL database

## Application Structure

- `app/`: Application core code
- `includes/`: Common includes and initialization scripts
- `public/`: Public web files (entry point)
- `vendor/`: Composer dependencies 