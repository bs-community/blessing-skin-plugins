import * as React from 'react'
import * as ReactDOM from 'react-dom'

type CodeRecord = {
  id: number
  sharer: number
  code: string
  url: string
}

const RegistrationLinksList = () => {
  const [records, setRecords] = React.useState<CodeRecord[]>([])
  const [sharer, setSharer] = React.useState(0)
  const [sharee, setSharee] = React.useState(0)

  React.useEffect(() => {
    const getLinks = async () => {
      const response: {
        records: CodeRecord[]
        sharer: number
        sharee: number
      } = await blessing.fetch.get('/user/reg-links')
      setRecords(response.records)
      setSharer(response.sharer)
      setSharee(response.sharee)
    }
    getLinks()
  }, [])

  const handleDeleteClick = async (record: CodeRecord) => {
    const { id } = record
    await blessing.fetch.del(`/user/reg-links/${id}`)
    setRecords((records) => records.filter((record) => record.id !== id))
  }

  const handleGenerateClick = async () => {
    const {
      data: { record },
    }: { data: { record: CodeRecord } } = await blessing.fetch.post(
      '/user/reg-links',
    )
    setRecords((records) => [...records, record])
  }

  return (
    <div className="card card-primary card-outline">
      <div className="card-header">
        <h3 className="card-title">分享注册链接</h3>
      </div>
      <div className="card-body">
        <p>
          分享注册链接，当新用户使用此链接时，您将获得 {sharer} 积分。
          同时新用户可获得 {sharee} 积分。
        </p>
        {records.length > 0 ? (
          <>
            <p>可用的链接：</p>
            <ul style={{ wordWrap: 'break-word' }}>
              {records.map((record) => (
                <li key={record.id}>
                  <span className="mr-1">{record.url}</span>
                  <a href="#" onClick={() => handleDeleteClick(record)}>
                    删除
                  </a>
                </li>
              ))}
            </ul>
          </>
        ) : (
          '还没有已生成的链接。'
        )}
      </div>
      <div className="card-footer">
        <button className="btn btn-primary" onClick={handleGenerateClick}>
          生成新链接
        </button>
      </div>
    </div>
  )
}

ReactDOM.render(
  <RegistrationLinksList />,
  document.querySelector('#registration-links'),
)
