import axios from 'axios';
import type { AxiosError, AxiosResponse, InternalAxiosRequestConfig } from 'axios';
import { useToast } from '@/composables/useToast';

const { error: toastError, success: toastSuccess } = useToast();

// Request interceptor
axios.interceptors.request.use(
  (config: InternalAxiosRequestConfig) => {
    // You can add auth tokens here if needed, but Laravel Sanctum/Session usually handles it via cookies
    return config;
  },
  (error: AxiosError) => {
    return Promise.reject(error);
  }
);

// Response interceptor
axios.interceptors.response.use(
  (response: AxiosResponse) => {
    // Check for standardized success response structure
    if (response.data && response.data.success === true && response.data.message) {
      // Only show toast if method is not GET, or if explicit success message is present
      // Usually we don't show toast for GET requests unless specific action was taken
      if (response.config.method !== 'get' && response.config.method !== 'GET') {
          toastSuccess(response.data.message);
      }
    }
    return response;
  },
  (error: AxiosError) => {
    const { response } = error;

    if (response) {
      // Handle standardized error response
      if (response.data && (response.data as any).success === false && (response.data as any).message) {
        toastError((response.data as any).message);
      } else if (response.status === 422) {
        // Validation errors
        // Laravel returns { message: "The given data was invalid.", errors: { ... } }
        // We usually just show the main message or the first error
        const data = response.data as any;
        toastError(data.message || 'Validation error');
      } else if (response.status === 401) {
        toastError('Unauthenticated. Please log in again.');
        // Optional: Redirect to login
        // window.location.href = '/login';
      } else if (response.status === 403) {
        toastError('Unauthorized action.');
      } else if (response.status === 404) {
        toastError('Resource not found.');
      } else if (response.status >= 500) {
        toastError('Server error. Please try again later.');
      } else {
          toastError(error.message || 'An error occurred.');
      }
    } else {
      // Network error or other issues
      toastError(error.message || 'Network error. Please check your connection.');
    }

    return Promise.reject(error);
  }
);

export default axios;
