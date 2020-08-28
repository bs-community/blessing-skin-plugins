declare class Toast {
  success(message: string): void
  info(message: string): void
  warning(message: string): void
  error(message: string): void
}

export let base_url: string
export let locale: string
export let site_name: string
export let version: string
export let route: string

export let t: (key: string, params?: object) => string

export let fetch: {
  get<T = any>(url: string, params?: object): Promise<T>
  post<T = any>(url: string, data?: object): Promise<T>
  put<T = any>(url: string, data?: object): Promise<T>
  del<T = any>(url: string, data?: object): Promise<T>
}

export let event: {
  on(eventName: string | symbol, listener: Function): void
  emit(eventName: string | symbol, payload: object): void
}

export let notify: {
  showModal(options?: object): Promise<{ value: string }>
  toast: Toast
}

declare global {
  let trans: (key: string, params?: object) => string
}
