import React, { useState, useEffect } from 'react'
import * as ReactDOM from 'react-dom'
import { nanoid } from 'nanoid'
import { useImmer } from 'use-immer'
import { fetch, notify } from 'blessing-skin'

type Item = { id: string; value: string }

const Configuration: React.FC = () => {
  const [isLoading, setIsLoading] = useState(false)
  const [allows, setAllows] = useImmer<Item[]>([])
  const [denies, setDenies] = useImmer<Item[]>([])
  const [isAllowListDirty, setIsAllowListDirty] = useState(false)
  const [isDenyListDirty, setIsDenyListDirty] = useState(false)

  useEffect(() => {
    const fetchList = async () => {
      setIsLoading(true)
      const {
        allow,
        deny,
      }: { allow: string[]; deny: string[] } = await fetch.get(
        '/admin/restricted-email-domains',
      )
      setAllows(() => allow.map((value) => ({ id: nanoid(), value })))
      setDenies(() => deny.map((value) => ({ id: nanoid(), value })))
      setIsLoading(false)
    }
    fetchList()
  }, [])

  const updateAllowItem = (
    { target: { value } }: React.ChangeEvent<HTMLInputElement>,
    index: number,
  ) => {
    setAllows((allows) => {
      allows[index].value = value
    })
    setIsAllowListDirty(true)
  }

  const updateDenyItem = (
    { target: { value } }: React.ChangeEvent<HTMLInputElement>,
    index: number,
  ) => {
    setDenies((denies) => {
      denies[index].value = value
    })
    setIsDenyListDirty(true)
  }

  const addAllowItem = () => {
    setAllows((allows) => {
      allows.push({ id: nanoid(), value: '' })
    })
    setIsAllowListDirty(true)
  }

  const addDenyItem = () => {
    setDenies((denies) => {
      denies.push({ id: nanoid(), value: '' })
    })
    setIsDenyListDirty(true)
  }

  const deleteAllowItem = (item: Item) => {
    setAllows((allows) => allows.filter(({ id }) => id !== item.id))
    setIsAllowListDirty(true)
  }

  const deleteDenyItem = (item: Item) => {
    setDenies((denies) => denies.filter(({ id }) => id !== item.id))
    setIsDenyListDirty(true)
  }

  const saveAllowList = async () => {
    const resp = await fetch.put(
      '/admin/restricted-email-domains/allow',
      allows.map((item) => item.value),
    )
    if (resp === '') {
      notify.toast.success(trans('restricted-email-domains.ok'))
      setIsAllowListDirty(false)
    }
  }

  const saveDenyList = async () => {
    const resp = await fetch.put(
      '/admin/restricted-email-domains/deny',
      denies.map((item) => item.value),
    )
    if (resp === '') {
      notify.toast.success(trans('restricted-email-domains.ok'))
      setIsDenyListDirty(false)
    }
  }

  return (
    <div className="row">
      <div className="col-lg-6">
        <div className="card card-success">
          <div className="card-header">
            <h3 className="card-title">
              {trans('restricted-email-domains.allow.title')}
              {isAllowListDirty && <span className="ml-1">●</span>}
            </h3>
          </div>
          <div className="card-body">
            {isLoading ? (
              <div className="text-center">
                <i className="fas fa-sync fa-spin"></i>
              </div>
            ) : allows.length === 0 ? (
              <div className="text-center">
                <p>{trans('general.noResult')}</p>
              </div>
            ) : (
              allows.map((item, i) => (
                <div className="input-group mb-3" key={item.id}>
                  <div className="input-group-prepend">
                    <span className="input-group-text">@</span>
                  </div>
                  <input
                    type="text"
                    className="form-control"
                    value={item.value}
                    onChange={(e) => updateAllowItem(e, i)}
                  />
                  <div className="input-group-append">
                    <button
                      className="btn btn-secondary"
                      onClick={() => deleteAllowItem(item)}
                    >
                      <i className="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              ))
            )}
            <div className="mt-3">
              <button className="btn btn-primary" onClick={addAllowItem}>
                <i className="fas fa-plus"></i>
              </button>
            </div>
          </div>
          <div className="card-footer">
            <div className="float-right">
              <button className="btn btn-primary" onClick={saveAllowList}>
                {trans('general.submit')}
              </button>
            </div>
          </div>
        </div>
      </div>
      <div className="col-lg-6">
        <div className="card card-danger">
          <div className="card-header">
            <h3 className="card-title">
              {trans('restricted-email-domains.deny.title')}
              {isDenyListDirty && <span className="ml-1">●</span>}
            </h3>
          </div>
          <div className="card-body">
            {isLoading ? (
              <div className="text-center">
                <i className="fas fa-sync fa-spin"></i>
              </div>
            ) : denies.length === 0 ? (
              <div className="text-center">
                <p>{trans('general.noResult')}</p>
              </div>
            ) : (
              denies.map((item, i) => (
                <div className="input-group mb-3" key={item.id}>
                  <div className="input-group-prepend">
                    <span className="input-group-text">@</span>
                  </div>
                  <input
                    type="text"
                    className="form-control"
                    value={item.value}
                    onChange={(e) => updateDenyItem(e, i)}
                  />
                  <div className="input-group-append">
                    <button
                      className="btn btn-secondary"
                      onClick={() => deleteDenyItem(item)}
                    >
                      <i className="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              ))
            )}
            <div className="mt-3">
              <button className="btn btn-primary" onClick={addDenyItem}>
                <i className="fas fa-plus"></i>
              </button>
            </div>
          </div>
          <div className="card-footer">
            <div className="float-right">
              <button className="btn btn-primary" onClick={saveDenyList}>
                {trans('general.submit')}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  )
}

ReactDOM.render(<Configuration />, document.querySelector('#config'))
