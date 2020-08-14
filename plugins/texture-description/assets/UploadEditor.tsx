import React, { useState, useEffect } from 'react'
import ReactDOM from 'react-dom'
import { event, t } from 'blessing-skin'

const UploadEditor: React.FC = () => {
  const [description, setDescription] = useState('')
  const [maxLength, setMaxLength] = useState(Infinity)

  useEffect(() => {
    return event.on('beforeFetch', (request: { data: FormData }) => {
      request.data.set('description', description)
    })
  }, [description])

  useEffect(() => {
    const input = document.querySelector<HTMLInputElement>('#desc-limit')
    setMaxLength(Number.parseInt(input?.value ?? '0') || Infinity)
  }, [])

  const handleTextareaChange = ({
    target: { value },
  }: React.ChangeEvent<HTMLTextAreaElement>) => {
    setDescription(value)
  }

  return (
    <>
      <label>{t('texture-description.desc')}</label>
      <textarea
        className="form-control"
        rows={9}
        onChange={handleTextareaChange}
      ></textarea>
      {description.length > maxLength && (
        <div className="alert alert-info mt-2">
          {trans('texture-description.exceeded', { max: maxLength })}
        </div>
      )}
    </>
  )
}

event.on('mounted', () => {
  const lastFormGroup = document.querySelector<HTMLDivElement>(
    '#file-input .form-group:nth-child(3)',
  )
  if (lastFormGroup) {
    const formGroup = document.createElement('div')
    formGroup.className = 'form-group'
    lastFormGroup.after(formGroup)
    ReactDOM.render(<UploadEditor />, formGroup)
  }
})
