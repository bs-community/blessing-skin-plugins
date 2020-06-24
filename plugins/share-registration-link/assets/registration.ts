import { event } from 'blessing-skin'

event.on('beforeFetch', (request: { data: Record<string, string> }) => {
  const search = new URLSearchParams(location.search)
  request.data.share = search.get('share') || ''
})
