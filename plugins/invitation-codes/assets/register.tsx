import React, { useState, useEffect } from 'react'
import * as ReactDOM from 'react-dom'
import { event, t } from 'blessing-skin'

const CodeField: React.FC = () => {
  const [code, setCode] = useState('')

  useEffect(() => {
    const off = event.on(
      'beforeFetch',
      (request: { data: Record<string, string> }) => {
        request.data.invitationCode = code
      },
    )

    return off
  }, [code])

  const handleCodeChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    setCode(event.target.value)
  }

  return (
    <>
      <input
        type="text"
        className="form-control"
        placeholder={t('invitation-codes.placeholder')}
        required
        value={code}
        onChange={handleCodeChange}
      ></input>
      <div className="input-group-append">
        <div className="input-group-text">
          <i className="fas fa-receipt"></i>
        </div>
      </div>
    </>
  )
}

event.on('mounted', () => {
  const div = document.createElement('div')
  div.className = 'input-group mb-3'
  ReactDOM.render(<CodeField />, div)

  setTimeout(() => {
    document.querySelector('.input-group:nth-child(4)')?.after(div)
  }, 0)
})
