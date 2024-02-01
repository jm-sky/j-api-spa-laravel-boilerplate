import { DEFAULT_LANGUAGE } from '@/config'
import axios from 'axios'

const axiosInstance = axios.create({
  withCredentials: true,
  headers: {
    Accept: 'application/json',
    'Content-Type': 'application/json',
    'Access-Control-Allow-Origin': '*',
    'Accept-Language': DEFAULT_LANGUAGE,
  },
})

export default axiosInstance
