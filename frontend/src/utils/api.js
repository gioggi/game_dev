/**
 * API utility functions
 */

// Get the API base URL from environment variables
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || '';

/**
 * Constructs the full API URL
 * @param {string} endpoint - The API endpoint (e.g., '/games')
 * @returns {string} The full API URL
 */
export const getApiUrl = (endpoint) => {
  // Make sure endpoint starts with a slash if it's not empty
  const formattedEndpoint = endpoint.startsWith('/') || endpoint === '' 
    ? endpoint 
    : `/${endpoint}`;
  
  // In development, always use relative path for Vite proxy
  if (import.meta.env.DEV) {
    return `/api${formattedEndpoint}`;
  }
  
  // In production, use the full URL from environment variable
  return `${API_BASE_URL}/api${formattedEndpoint}`;
};

/**
 * Makes a GET request to the API
 * @param {string} endpoint - The API endpoint
 * @param {Object} params - Query parameters
 * @returns {Promise} The fetch promise
 */
export const apiGet = async (endpoint, params = {}) => {
  let url = getApiUrl(endpoint);
  
  // Add query parameters
  if (Object.keys(params).length > 0) {
    const searchParams = new URLSearchParams();
    Object.keys(params).forEach(key => {
      if (params[key] !== undefined && params[key] !== null) {
        searchParams.append(key, params[key]);
      }
    });
    url += `?${searchParams.toString()}`;
  }
  
  const response = await fetch(url);
  
  if (!response.ok) {
    throw new Error(`API error: ${response.status}`);
  }
  
  return response.json();
};

/**
 * Makes a POST request to the API
 * @param {string} endpoint - The API endpoint
 * @param {Object} data - The request body
 * @returns {Promise} The fetch promise
 */
export const apiPost = async (endpoint, data = {}) => {
  const response = await fetch(getApiUrl(endpoint), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });
  
  if (!response.ok) {
    throw new Error(`API error: ${response.status}`);
  }
  
  return response.json();
};

/**
 * Makes a PUT request to the API
 * @param {string} endpoint - The API endpoint
 * @param {Object} data - The request body
 * @returns {Promise} The fetch promise
 */
export const apiPut = async (endpoint, data = {}) => {
  const response = await fetch(getApiUrl(endpoint), {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });
  
  if (!response.ok) {
    throw new Error(`API error: ${response.status}`);
  }
  
  return response.json();
};

/**
 * Makes a DELETE request to the API
 * @param {string} endpoint - The API endpoint
 * @returns {Promise} The fetch promise
 */
export const apiDelete = async (endpoint) => {
  const response = await fetch(getApiUrl(endpoint), {
    method: 'DELETE'
  });
  
  if (!response.ok) {
    throw new Error(`API error: ${response.status}`);
  }
  
  // Some DELETE endpoints return no content
  if (response.status === 204) {
    return true;
  }
  
  return response.json();
};