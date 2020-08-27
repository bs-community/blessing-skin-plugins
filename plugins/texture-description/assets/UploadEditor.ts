import { event } from 'blessing-skin'
import UploadEditor from './UploadEditor.svelte'

event.on('mounted', () => {
  const lastFormGroup = document.querySelector<HTMLDivElement>(
    '#file-input .form-group:nth-child(3)',
  )
  if (lastFormGroup) {
    const formGroup = document.createElement('div')
    formGroup.className = 'form-group'
    lastFormGroup.after(formGroup)
    new UploadEditor({ target: formGroup })
  }
})
