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

const base_url = '/'
const locale = 'en'
const site_name = 'Blessing Skin'

function t(key: string, params?: object): string {
  const data = params ? `(${JSON.stringify(params)})` : ''

  return key + data
}

const fetch = {
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
const event = {
  on(
    eventName: string | symbol,
    listener: (...args: any[]) => any,
  ): () => void {
    emitter.on(eventName, listener)

    return () => {
      emitter.off(eventName, listener)
    }
  },
  emit(eventName: string | symbol, payload?: object): void {
    emitter.emit(eventName, payload)
  },
}

const notify = {
  showModal(options?: object): Promise<{ value: string }> {
    return Promise.resolve({ value: JSON.stringify(options) })
  },
  toast: new Toast(),
}

Object.assign(globalThis, {
  blessing: {
    base_url,
    locale,
    site_name,
    t,
    fetch,
    event,
    notify,
  },
})
