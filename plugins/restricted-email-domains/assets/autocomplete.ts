globalThis.blessing.event.on(
  'emailDomainsSuggestion',
  (domains: Set<string>) => {
    const allowListJSON = document.querySelector('#allowed-email-domains')
    if (!allowListJSON) {
      return
    }

    const allowList: string[] = JSON.parse(allowListJSON.textContent ?? '[]')
    if (allowList.length > 0) {
      domains.clear()
      allowList.forEach((domain) => domains.add(domain))
    }
  },
)
