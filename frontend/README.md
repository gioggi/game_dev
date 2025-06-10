# Frontend API Configuration

This document explains how the frontend application is configured to communicate with the backend API in different environments.

## Environment-Based API Configuration

The application uses environment variables to configure the API base URL, allowing it to work seamlessly in both development and production environments.

### Development Environment

In development mode:

1. The API base URL is left empty in `.env.development`:
   ```
   VITE_API_BASE_URL=
   ```

2. Vite's development server uses a proxy configuration to forward API requests to the backend:
   ```javascript
   // vite.config.js
   export default defineConfig({
     server: {
       proxy: {
         '/api': {
           target: 'http://nginx:80',
           changeOrigin: true
         }
       }
     }
   });
   ```

3. This allows developers to make relative API requests (e.g., `/api/games`) that are automatically proxied to the backend service.

### Production Environment

In production mode:

1. The API base URL is set to the actual backend URL in `.env.production`:
   ```
   VITE_API_BASE_URL=https://api.example.com
   ```

2. When the application is built for production, this URL is embedded in the compiled code.

3. API requests are made directly to the full URL (e.g., `https://api.example.com/api/games`).

## How It Works

The application uses a utility module (`src/utils/api.js`) that:

1. Reads the API base URL from environment variables:
   ```javascript
   const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '';
   ```

2. Constructs the full API URL based on the environment:
   ```javascript
   export const getApiUrl = (endpoint) => {
     // If API_BASE_URL is empty (development), use relative path
     if (!API_BASE_URL) {
       return `/api${formattedEndpoint}`;
     }

     // In production, use the full URL from environment variable
     return `${API_BASE_URL}/api${formattedEndpoint}`;
   };
   ```

3. Provides utility functions for making API requests:
   - `apiGet(endpoint, params)` - For GET requests
   - `apiPost(endpoint, data)` - For POST requests
   - `apiPut(endpoint, data)` - For PUT requests
   - `apiDelete(endpoint)` - For DELETE requests

## Deployment Considerations

When deploying to production:

1. Set the correct API base URL in `.env.production` before building the application.

2. For different deployment environments (staging, production, etc.), you can create additional environment files (e.g., `.env.staging`) and specify which one to use during the build process:
   ```
   npm run build -- --mode staging
   ```

3. If the API URL needs to be configurable after building (e.g., for the same build to be deployed to different environments), consider:
   - Using a configuration file that's loaded at runtime
   - Injecting the API URL through a global variable
   - Using a service like Nginx to rewrite API requests
