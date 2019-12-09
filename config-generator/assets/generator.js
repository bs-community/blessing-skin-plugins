for (const block of document.querySelectorAll('pre')) {
  hljs.highlightBlock(block)
}

document.querySelector('#download-extra-list')
  .addEventListener('click', () => {
    const content = JSON.stringify({
      name: blessing.site_name,
      type: 'CustomSkinAPI',
      root: blessing.base_url + '/csl/',
    })

    const a = document.createElement('a')
    const blob = new Blob([content])
    a.download = blessing.site_name + '.json'
    a.href = URL.createObjectURL(blob)
    a.click()
    URL.revokeObjectURL(blob)
  })
