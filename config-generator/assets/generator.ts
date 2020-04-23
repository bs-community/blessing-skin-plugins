document.querySelectorAll('pre').forEach((el) => hljs.highlightBlock(el))

document
  .querySelector('#download-extra-list')
  ?.addEventListener('click', () => {
    const content = JSON.stringify({
      name: blessing.site_name,
      type: 'CustomSkinAPI',
      root: `${blessing.base_url}/csl/`,
    })

    const a = document.createElement('a')
    const blob = new Blob([content], { type: 'application/json' })
    a.download = `${blessing.site_name}.json`
    a.href = URL.createObjectURL(blob)
    a.click()
    a.remove()
  })
