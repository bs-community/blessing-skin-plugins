import Description from './Description.svelte'

customElements.define('description-content', HTMLDivElement)

const el = document.querySelector<HTMLDivElement>('#texture-description')
if (el) {
  new Description({ target: el })
}
