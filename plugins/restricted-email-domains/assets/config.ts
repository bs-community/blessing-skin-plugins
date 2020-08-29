import ConfigEditor from './ConfigEditor.svelte'

const target = document.querySelector('#config')
if (target) {
  new ConfigEditor({ target })
}
