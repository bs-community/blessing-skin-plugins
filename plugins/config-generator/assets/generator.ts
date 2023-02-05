import hljs from 'highlight.js/lib/core'
import json from 'highlight.js/lib/languages/json'

hljs.registerLanguage('json', json)

document.querySelectorAll('pre').forEach((el) => hljs.highlightBlock(el))

document
  .querySelector('#download-extra-list')
  ?.addEventListener('click', () => {
    const content = JSON.stringify({
      name: globalThis.blessing.site_name,
      type: 'CustomSkinAPI',
      root: `${globalThis.blessing.base_url}/csl/`,
    })

    const a = document.createElement('a')
    const blob = new Blob([content], { type: 'application/json' })
    a.download = `${globalThis.blessing.site_name}.json`
    a.href = URL.createObjectURL(blob)
    a.click()
    a.remove()
  })
