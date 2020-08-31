import Description from './Description.svelte'

customElements.define('description-content', HTMLDivElement)

function getTid(): string {
  const { pathname } = location
  const matches = /(\d+)$/.exec(pathname)

  return matches?.[1] ?? '0'
}

const el = document.querySelector<HTMLDivElement>('#texture-description')
if (el) {
  const { dataset } = el
  new Description({
    target: el,
    props: {
      tid: getTid(),
      canEdit: dataset.canEdit === 'true',
      maxLength: Number.parseInt(dataset.maxLength ?? '0') || Infinity,
    },
  })
}
