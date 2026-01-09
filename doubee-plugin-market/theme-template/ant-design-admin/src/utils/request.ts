import axios, { AxiosInstance, AxiosResponse } from "axios";

const request: AxiosInstance = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
  timeout: 10000,
});

request.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

request.interceptors.response.use(
  (response: AxiosResponse) => {
    const { data } = response;

    if (data.code !== 200) {
      return Promise.reject(data);
    }

    return data;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default request;
