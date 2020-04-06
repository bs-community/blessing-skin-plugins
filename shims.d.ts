declare class Toast {
  success(message: string): void
  info(message: string): void
  warning(message: string): void
  error(message: string): void
}

declare global {
  let blessing: {
    base_url: string
    debug: boolean
    env: string
    fallback_locale: string
    locale: string
    site_name: string
    timezone: string
    version: string
    route: string
    extra: any
    i18n: object

    fetch: {
      get<T = any>(url: string, params?: object): Promise<T>
      post<T = any>(url: string, data?: object): Promise<T>
      put<T = any>(url: string, data?: object): Promise<T>
      del<T = any>(url: string, data?: object): Promise<T>
    }

    event: {
      on(eventName: string, listener: Function): void
      emit(eventName: string, payload: object): void
    }

    notify: {
      showModal(options?: object): Promise<{ value: string }>
      toast: Toast
    }
  }
}

export default undefined
