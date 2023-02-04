interface BlessingGlobals {
  base_url: string
  site_name: string
  locale: string
  version: string
  route: string

  t(key: string, params?: object): string

  fetch: {
    get<T = any>(url: string, params?: Record<string, unknown>): Promise<T>
    post<T = any>(url: string, data?: any): Promise<T>
    put<T = any>(url: string, data?: any): Promise<T>
    del<T = any>(url: string, data?: any): Promise<T>
  }

  event: {
    on(
      eventName: string | symbol,
      listener: (...args: any[]) => any,
    ): () => void
    emit(eventName: string | symbol, payload?: object): void
  }

  notify: {
    showModal(options?: object): Promise<{ value: string }>
    toast: Toast
  }
}

declare class Toast {
  success(message: string): void
  info(message: string): void
  warning(message: string): void
  error(message: string): void
}

// `var` is required here,
// otherwise the `blessing` property won't appear in `globalThis` type
declare var blessing: BlessingGlobals

interface Window {
  blessing: BlessingGlobals
}
