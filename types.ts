import { EventEmitter } from 'events'

class Toast {
  success(message: string): void {
    message
  }
  info(message: string): void {
    message
  }
  warning(message: string): void {
    message
  }
  error(message: string): void {
    message
  }
}

export let base_url = '/'
export let locale = 'en'
export let site_name = 'Blessing Skin'
export let version: string
export let route: string

export function t(key: string, params?: object) {
  return `${key}(${JSON.stringify(params)})`
}

export const fetch = {
  async get<T = any>(url: string, params?: Record<string, any>): Promise<T> {
    url
    params
    return {} as T
  },
  async post<T = any>(url: string, data?: any): Promise<T> {
    url
    data
    return {} as T
  },
  async put<T = any>(url: string, data?: any): Promise<T> {
    url
    data
    return {} as T
  },
  async del<T = any>(url: string, data?: any): Promise<T> {
    url
    data
    return {} as T
  },
}

const emitter = new EventEmitter()
export const event = {
  on(eventName: string | symbol, listener: (...args: any[]) => any): () => void {
    emitter.on(eventName, listener)

    return () => {
      emitter.off(eventName, listener)
    }
  },
  emit(eventName: string | symbol, payload?: object): void {
    emitter.emit(eventName, payload)
  },
}

export const notify = {
  showModal(options?: object): Promise<{ value: string }> {
    return Promise.resolve({ value: JSON.stringify(options) })
  },
  toast: new Toast(),
}

declare global {
  let trans: (key: string, params?: object) => string
}
